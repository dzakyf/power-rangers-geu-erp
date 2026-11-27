<?php

namespace App\Services\HCGA;

use App\Services\Approval\ApprovalWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use SimpleSoftwareIO\QrCode\Facade as QrCode;

/**
 * Generator BAST Sistem (PDF via mPDF) + QR Verifikasi HMAC untuk Handover & Return.
 */
class AssetHandoverBastPdfService
{
    public const DOC_TYPE_HANDOVER = 'HANDOVER';
    public const DOC_TYPE_RETURN   = 'RETURN';

    public function normalizeDocType(?string $docType): string
    {
        $type = strtoupper(trim((string) $docType));

        return $type === self::DOC_TYPE_RETURN ? self::DOC_TYPE_RETURN : self::DOC_TYPE_HANDOVER;
    }

    public function buildBastNumber(string $handoverNo, string $docType): string
    {
        $type = $this->normalizeDocType($docType);
        $prefix = $type === self::DOC_TYPE_RETURN ? 'BAST/RET/' : 'BAST/HO/';

        return $prefix . $handoverNo;
    }

    public function makeSignature(string $handoverNo, string $docType): string
    {
        $type = $this->normalizeDocType($docType);
        $key = (string) config('app.key');

        return hash_hmac('sha256', 'ASSET_HANDOVER_BAST|' . $type . '|' . trim($handoverNo), $key);
    }

    public function verifySignature(string $handoverNo, string $docType, string $signature): bool
    {
        $sig = trim($signature);
        if ($sig === '' || trim($handoverNo) === '') {
            return false;
        }

        return hash_equals($this->makeSignature($handoverNo, $docType), $sig);
    }

    public function buildVerificationUrl(string $handoverNo, string $docType): string
    {
        $type = $this->normalizeDocType($docType);

        return url('public-asset-handover-bast-verify')
            . '?Handover_No=' . rawurlencode($handoverNo)
            . '&doc_type=' . rawurlencode($type)
            . '&sig=' . $this->makeSignature($handoverNo, $type);
    }

    public function buildDocumentData(string $handoverNo, string $docType): array
    {
        $type = $this->normalizeDocType($docType);
        $handover = DB::table('asset_handover')->where('Handover_No', $handoverNo)->first();
        if (!$handover) {
            abort(404, 'Data Handover tidak ditemukan.');
        }

        $itemsQuery = DB::table('asset_handover_dtl as d');
        if (Schema::hasColumn('asset_handover_dtl', 'Asset_No') && Schema::hasTable('asset_list')) {
            $itemsQuery->leftJoin('asset_list as al', 'al.Asset_No', '=', 'd.Asset_No')
                ->select('d.*', 'al.SN as Asset_SN', 'al.Asset_Location as Asset_Loc');
        } else {
            $itemsQuery->select('d.*');
        }
        $items = $itemsQuery->where('d.Handover_No', $handoverNo)->orderBy('d.ID', 'ASC')->get();

        $reqId = $type === self::DOC_TYPE_RETURN
            ? (int) ($handover->Return_Approval_Request_ID ?? 0)
            : (int) ($handover->Approval_Request_ID ?? 0);

        $approvalReq = null;
        $approvalTrail = [];
        if ($reqId > 0) {
            $workflow = app(ApprovalWorkflowService::class);
            $approvalReq = $workflow->getRequest($reqId);
            $approvalTrail = $workflow->buildApprovalTrail($reqId);
        }

        $statusUpper = strtoupper((string) ($handover->Status ?? 'CREATED'));
        $reqStatusUpper = $approvalReq ? strtoupper((string) $approvalReq->Status) : '';

        if ($type === self::DOC_TYPE_RETURN) {
            $hasReturnedDtl = $items->contains(function ($it) {
                return strtoupper((string) ($it->Status ?? '')) === 'RETURNED';
            });
            $isOfficial = ($statusUpper === 'RETURNED') || ($reqStatusUpper === 'APPROVED') || $hasReturnedDtl;
        } else {
            $isOfficial = in_array($statusUpper, ['APPROVED', 'RETURNED'], true) || ($reqStatusUpper === 'APPROVED');
        }

        $niks = array_values(array_unique(array_filter([
            trim((string) ($handover->User_NIK ?? '')),
            trim((string) ($handover->Lead_NIK ?? '')),
            trim((string) ($handover->PIC_NIK ?? '')),
            trim((string) ($handover->Return_Lead_NIK ?? '')),
            trim((string) ($handover->Return_Receiver_NIK ?? '')),
        ])));

        $employees = [];
        if (!empty($niks) && Schema::hasTable('employment')) {
            $employees = DB::table('employment')
                ->whereIn('NIK', $niks)
                ->get()
                ->keyBy('NIK')
                ->all();
        }

        $verifyUrl = $this->buildVerificationUrl($handoverNo, $type);
        $qrSvg = (string) QrCode::format('svg')->size(110)->margin(1)->generate($verifyUrl);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

        return [
            'handover'      => $handover,
            'docType'       => $type,
            'bastNo'        => $this->buildBastNumber($handoverNo, $type),
            'items'         => $items,
            'approvalReq'   => $approvalReq,
            'approvalTrail' => $approvalTrail,
            'isOfficial'    => $isOfficial,
            'employees'     => $employees,
            'verifyUrl'     => $verifyUrl,
            'qrBase64'      => $qrBase64,
            'generatedAt'   => now()->format('d M Y H:i:s'),
        ];
    }

    public function ensureMpdfTempDir(): string
    {
        $candidates = [
            storage_path('app/mpdf'),
            rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'hcga_mpdf',
        ];
        foreach ($candidates as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            if (is_dir($dir) && is_writable($dir)) {
                return $dir;
            }
        }

        return sys_get_temp_dir();
    }

    public function renderPdf(string $handoverNo, string $docType): array
    {
        $data = $this->buildDocumentData($handoverNo, $docType);
        $html = view('humanresources.asset-handover.pdf.bast', $data)->render();

        $tempDir = $this->ensureMpdfTempDir();

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'orientation'   => 'P',
            'margin_left'   => 12,
            'margin_right'  => 12,
            'margin_top'    => 12,
            'margin_bottom' => 14,
            'tempDir'       => $tempDir,
        ]);

        $mpdf->SetTitle($data['bastNo']);
        if (!$data['isOfficial']) {
            $mpdf->SetWatermarkText('DRAFT');
            $mpdf->showWatermarkText = true;
            $mpdf->watermarkTextAlpha = 0.14;
        }

        $mpdf->WriteHTML($html);

        $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $data['bastNo']);
        $filename = $safeCode . '.pdf';

        return [
            'binary'   => $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            'filename' => $filename,
        ];
    }
}
