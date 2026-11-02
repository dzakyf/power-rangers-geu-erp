<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facade as QrCode;

class QrPointController extends Controller
{
    use GaXml;

    public function index()
    {
        abort_unless(can_menu('SI01013', 'view'), 403);

        $points = DB::table('ga_qr_point')->orderBy('Name')->get();

        return view('humanresources.ga.qr-point.index', compact('points'));
    }

    public function data()
    {
        abort_unless(can_menu('SI01013', 'view'), 403);

        $rows = DB::table('ga_qr_point')->orderBy('Name')->get();

        $this->xmlHeader();
        echo '<rows>';
        $no = 1;
        foreach ($rows as $row) {
            echo '<row id="' . (int) $row->ID . '">';
            echo '<cell>' . $no . '</cell>';
            echo '<cell>' . $this->xml($row->QR_Code) . '</cell>';
            echo '<cell>' . $this->xml($row->Name) . '</cell>';
            echo '<cell>' . $this->xml($row->Point_Type) . '</cell>';
            echo '<cell>' . $this->xml($row->Building ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Floor ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Room ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($row->Status) . '</cell>';
            echo '</row>';
            $no++;
        }
        echo '</rows>';
    }

    public function save(Request $request)
    {
        $action = $request->filled('ID') ? 'edit' : 'create';
        abort_unless(can_menu('SI01013', $action), 403);

        $data = $request->validate([
            'ID' => 'nullable|integer',
            'QR_Code' => 'required|string|max:100',
            'Name' => 'required|string|max:150',
            'Point_Type' => 'required|in:LOCATION,ROOM,CHECKPOINT,POST',
            'Building' => 'nullable|string|max:100',
            'Floor' => 'nullable|string|max:50',
            'Room' => 'nullable|string|max:100',
            'Status' => 'nullable|in:ACTIVE,INACTIVE',
        ]);

        try {
            $now = Carbon::now();
            $user = Auth::user();
            $payload = [
                'Location' => $user->Location,
                'Company_ID' => $user->Company_ID,
                'QR_Code' => strtoupper(trim((string) $data['QR_Code'])),
                'Name' => trim((string) $data['Name']),
                'Point_Type' => strtoupper((string) $data['Point_Type']),
                'Building' => $data['Building'] ?? null,
                'Floor' => $data['Floor'] ?? null,
                'Room' => $data['Room'] ?? null,
                'Status' => $data['Status'] ?? 'ACTIVE',
                'Updated_By' => (string) $user->username,
                'Updated_At' => $now,
            ];

            if (!empty($data['ID'])) {
                DB::table('ga_qr_point')->where('ID', (int) $data['ID'])->update($payload);
                $id = (int) $data['ID'];
            } else {
                $payload['Created_By'] = (string) $user->username;
                $payload['Created_At'] = $now;
                $id = DB::table('ga_qr_point')->insertGetId($payload);
            }

            return response()->json(['status' => 'success', 'message' => 'QR point disimpan.', 'id' => $id]);
        } catch (\Throwable $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 422);
        }
    }

    public function delete(Request $request)
    {
        abort_unless(can_menu('SI01013', 'delete'), 403);

        $data = $request->validate(['ID' => 'required|integer']);
        DB::table('ga_qr_point')->where('ID', (int) $data['ID'])->update([
            'Status' => 'INACTIVE',
            'Updated_By' => (string) Auth::user()->username,
            'Updated_At' => Carbon::now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'QR point dinonaktifkan.']);
    }

    public function image(Request $request)
    {
        abort_unless(can_menu('SI01013', 'view'), 403);

        $code = trim((string) $request->query('code', ''));
        abort_if($code === '', 404);

        $target = url('hrd-ga-ticket-qr?code=' . urlencode($code));
        $svg = QrCode::format('svg')->size(320)->margin(1)->generate($target);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function printSheet(Request $request)
    {
        abort_unless(can_menu('SI01013', 'view'), 403);

        $points = DB::table('ga_qr_point')->where('Status', 'ACTIVE')->orderBy('Name')->get();

        $idsParam = $request->query('ids', []);
        if (!is_array($idsParam)) {
            $idsParam = trim((string) $idsParam) === '' ? [] : explode(',', (string) $idsParam);
        }
        $selectedIds = array_values(array_unique(array_filter(array_map('intval', $idsParam))));

        $filter = $request->query('filter');
        $hasFilter = $request->has('ids') || (is_scalar($filter) && (string) $filter === '1');
        $codeParam = $request->query('code');
        if (is_scalar($codeParam) && trim((string) $codeParam) !== '') {
            $code = trim((string) $codeParam);
            $selectedIds = $points->filter(function ($point) use ($code) {
                return strcasecmp((string) $point->QR_Code, $code) === 0;
            })->pluck('ID')->map(function ($id) {
                return (int) $id;
            })->all();
            $hasFilter = true;
        }

        if ($hasFilter) {
            $selectedPoints = $points->filter(function ($point) use ($selectedIds) {
                return in_array((int) $point->ID, $selectedIds, true);
            })->values();
        } else {
            $selectedPoints = $points;
        }

        $action = strtolower(trim((string) $request->query('action', '')));
        $layout = strtolower(trim((string) $request->query('layout', 'card'))); // 'card' (lengkap) atau 'compact' (hanya QR)
        $download = $request->boolean('download') || $action === 'download';
        $generatePdf = $action === 'print' || $action === 'download' || $download;

        if (!$generatePdf) {
            return view('humanresources.ga.qr-point.print', compact('points', 'selectedPoints', 'selectedIds', 'hasFilter', 'layout'));
        }

        if ($selectedPoints->isEmpty()) {
            return view('humanresources.ga.qr-point.print', [
                'points' => $points,
                'selectedPoints' => $selectedPoints,
                'selectedIds' => $selectedIds,
                'hasFilter' => $hasFilter,
                'layout' => $layout,
                'warning' => 'Pilih minimal satu QR point sebelum mencetak PDF.',
            ]);
        }

        $qrImages = [];
        foreach ($selectedPoints as $point) {
            $qrImages[(int) $point->ID] = $this->qrDataUri($point->QR_Code);
        }

        $logo = $this->logoDataUri(public_path('assets/images/small-logo.png'));

        $viewName = $layout === 'compact'
            ? 'humanresources.ga.qr-point.print-pdf-compact'
            : 'humanresources.ga.qr-point.print-pdf';

        $html = view($viewName, compact('selectedPoints', 'qrImages', 'logo'))->render();

        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $margin = $layout === 'compact' ? 6 : 8;

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => $margin,
            'margin_right' => $margin,
            'margin_top' => $margin,
            'margin_bottom' => $margin,
            'tempDir' => $tempDir,
        ]);

        $docTitle = $layout === 'compact' ? 'Stiker QR Point GA' : 'Kartu QR Point GA';
        $mpdf->SetTitle($docTitle);
        $mpdf->WriteHTML($html);

        $prefix = $layout === 'compact' ? 'QR-Sticker-' : 'QR-Card-';
        $filename = $prefix . Carbon::now()->format('YmdHis') . '.pdf';
        $disposition = $download ? 'attachment' : 'inline';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
            ]
        );
    }

    protected function logoDataUri($path, $scale = 3)
    {
        if (!is_file($path) || !function_exists('imagecreatefromstring')) {
            return null;
        }

        $src = @imagecreatefromstring(file_get_contents($path));
        if (!$src) {
            return null;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $dst = imagecreatetruecolor($w * $scale, $h * $scale);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w * $scale, $h * $scale, $w, $h);

        ob_start();
        imagepng($dst);
        $png = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        return 'data:image/png;base64,' . base64_encode($png);
    }

    protected function qrDataUri($code)
    {
        $target = url('hrd-ga-ticket-qr?code=' . urlencode($code));
        $svg = QrCode::format('svg')->size(320)->margin(1)->generate($target);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
