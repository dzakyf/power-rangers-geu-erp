<?php

namespace App\Services\Approval;

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\OnboardingController;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Force-approve bypass for BDP approval requests.
 *
 * The approval request is finalized like a normal final approval, then the
 * same business side effects are applied for the request type.
 */
class ForceApproveService
{
    public const ALLOWED_POSITIONS = ['Administrator', 'BOD', 'SUPERADMIN'];

    /**
     * @return array{success:bool,message:string,status:int}
     */
    public function forceApprove(int $requestId): array
    {
        $session = app(UserService::class)->getCurrentSessionUserInfo();

        if (!in_array($session->Position ?? null, self::ALLOWED_POSITIONS, true)) {
            return ['success' => false, 'message' => 'Unauthorized', 'status' => 403];
        }

        $request = DB::table('approval_request')->where('ID', $requestId)->first();
        if (!$request) {
            return ['success' => false, 'message' => 'Approval request not found', 'status' => 404];
        }

        if ($request->Status !== 'NEED_APPROVAL') {
            return [
                'success' => false,
                'message' => 'Pengajuan ini sudah diproses (' . $request->Status . ')',
                'status'  => 422,
            ];
        }

        DB::transaction(function () use ($request, $session) {
            DB::table('approval_request')->where('ID', $request->ID)->update([
                'Status' => 'APPROVED',
            ]);

            DB::table('approval_log')->insert([
                'Approver_Id'  => $session->id,
                'Notes'        => 'Force approved (bypass)',
                'Action'       => 'APPROVED',
                'Request_Id'   => $request->ID,
                'Level_Number' => $request->Current_Level,
                'Next_Level'   => $request->Current_Level + 1,
            ]);
        });

        try {
            $this->applySideEffects($request);
        } catch (\Throwable $e) {
            Log::error('Force approve side effect failed for ' . $request->Request_Type . ' #' . $request->ID . ': ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Approval tersimpan, namun proses lanjutan gagal: ' . $e->getMessage(),
                'status'  => 500,
            ];
        }

        return ['success' => true, 'message' => 'Force approve success', 'status' => 200];
    }

    private function applySideEffects($request): void
    {
        $type = (string) $request->Request_Type;

        if ($type === 'ONBOARDING') {
            app(OnboardingController::class)->ensureMitraAccountForForceApprove($request->Opportunity_Partner_Id);
            return;
        }

        if ($type === 'SURVEY') {
            if (!$request->Map_To) {
                return;
            }

            DB::table('survey_location')->where('ID', $request->Map_To)->update([
                'Status' => 'DONE_SURVEY',
            ]);

            $survey = DB::table('survey_location')->where('ID', $request->Map_To)->first();
            if ($survey && $survey->Opportunity_Partner_Id) {
                app(OnboardingController::class)->advanceOnboardingStage(
                    $survey->Opportunity_Partner_Id,
                    'SURVEY',
                    'SETUP',
                    'Survey disetujui'
                );
            }
            return;
        }

        if (in_array($type, ['RENEWAL', 'TERMINATION', 'TRANSFER_MEMBERSHIP'], true)) {
            app(ApprovalController::class)->applyFlowSideEffects($type, (int) $request->ID);
        }
    }
}
