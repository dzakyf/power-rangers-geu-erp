<?php

namespace App\Services\HCGA;

use App\Models\HCGA\StorageDocument;
use App\Models\HCGA\StorageDocumentVersion;
use App\Services\Approval\ApprovalWorkflowService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StorageApprovalService
{
    protected $workflow;

    public function __construct(ApprovalWorkflowService $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * Submit a storage document version into the Approval Engine (1-step HCGA Manager).
     */
    public function submitVersionForApproval(StorageDocumentVersion $version, $user = null)
    {
        $user = $user ?: Auth::user();
        $requesterId = $user ? (int) $user->id : 0;
        $doc = $version->document ?: StorageDocument::findOrFail($version->document_id);

        $docCode = $doc->document_number ?: ('SM-' . $doc->id);
        $reqNo = mb_substr($docCode . '-' . strtoupper($version->version_label), 0, 50);

        $context = [
            'Request_Type' => 'STORAGE_DOCUMENT',
            'Department'   => 'HCGA',
            'Location'     => $user->Location ?? null,
            'Company_ID'   => $user->Company_ID ?? null,
            'Amount'       => 0,
        ];

        $req = $this->workflow->submit([
            'Request_No'  => $reqNo,
            'Description' => '[' . $doc->module_type . ' - ' . StorageDocument::getSubTypeLabel($doc->sub_type) . '] ' . $version->title . ' (' . $version->version_label . ')',
        ], $context, $requesterId, [
            'Revision_No' => (int) $version->version_number,
        ]);

        DB::table('approval_request')->where('ID', (int) $req->ID)->update([
            'Map_To' => (int) $version->id,
        ]);

        $version->approval_request_id = (int) $req->ID;
        $version->approval_status = $req->Status;
        $version->version_status = StorageDocumentVersion::STATUS_WAITING_APPROVAL;
        $version->save();

        // Handle auto-approved cases (e.g. if requester is MHCGA or On_No_Higher_Level=AUTO_APPROVE)
        if (strtoupper((string) $req->Status) === 'APPROVED') {
            $this->syncApprovalStatus((int) $version->id, $user ? ($user->username ?? 'system') : 'system');
        }

        return $req;
    }

    /**
     * Idempotent sync called from SyncModuleApprovalStatus when ApprovalRequestUpdated fires.
     */
    public function syncApprovalStatus($versionId, $actor = 'system')
    {
        $version = StorageDocumentVersion::find($versionId);
        if (!$version || empty($version->approval_request_id)) {
            return null;
        }

        $approvalReq = DB::table('approval_request')->where('ID', (int) $version->approval_request_id)->first();
        if (!$approvalReq) {
            return null;
        }

        $statusUpper = strtoupper((string) $approvalReq->Status);
        $now = Carbon::now();

        if (in_array($statusUpper, ['APPROVED', 'COMPLETED'], true)) {
            if ($version->version_status !== StorageDocumentVersion::STATUS_ACTIVE) {
                app(StorageManagementService::class)->finalizeVersionPublication($version, $actor);
            }
            return $version->fresh();
        }

        if (in_array($statusUpper, ['REJECTED', 'RETURNED'], true)) {
            $version->version_status = StorageDocumentVersion::STATUS_REJECTED;
            $version->approval_status = $statusUpper;
            $version->updated_by = $actor;
            $version->updated_date = $now;
            $version->save();

            $doc = StorageDocument::find($version->document_id);
            if ($doc && $doc->status === StorageDocument::STATUS_WAITING_APPROVAL) {
                $doc->status = StorageDocument::STATUS_DRAFT;
                $doc->updated_by = $actor;
                $doc->updated_date = $now;
                $doc->save();
            }
            return $version;
        }

        if ($statusUpper === 'CANCELLED') {
            $version->version_status = StorageDocumentVersion::STATUS_DRAFT;
            $version->approval_status = 'CANCELLED';
            $version->updated_by = $actor;
            $version->updated_date = $now;
            $version->save();

            $doc = StorageDocument::find($version->document_id);
            if ($doc && $doc->status === StorageDocument::STATUS_WAITING_APPROVAL) {
                $doc->status = StorageDocument::STATUS_DRAFT;
                $doc->updated_by = $actor;
                $doc->updated_date = $now;
                $doc->save();
            }
        }

        return $version;
    }
}
