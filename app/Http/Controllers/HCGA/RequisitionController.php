<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class RequisitionController extends Controller
{
    public function index()
    {
        //
    }

    public function indexXml()
    {
        //
    }

    public function print(Request $request)
    {
        // ── Ambil data (sesuaikan dengan model Anda) ──────────────────────
        $ptk = DB::table('requisition')->where("Task_ID", $request->Task)->first();
        $position = DB::table('master_position')->where('Position', $ptk->Job_Position)->where('Department', $ptk->Department)->orderBy('Position', 'asc')->get();
        $workCalendar = DB::table('work_calendars')->get();

        return view('humanresources.recruitment.prints.requisition', compact(
            'ptk', 'position', 'workCalendar'
        ));
 
        // ── Render HTML dari Blade view ───────────────────────────────────
        $html = view('humanresources.recruitment.prints.requisition', compact(
            'ptk', 'position', 'workCalendar'
        ))->render();
 
        // ── Header & Footer HTML ──────────────────────────────────────────
        // $letterhead = public_path('assets/images/large-logo.png');
        // $footerImg  = public_path($companies[$shipment->Shipper]->Footer);
 
        // Konversi ke base64 agar mPDF bisa render gambar
        // $headerB64 = base64_encode(file_get_contents($letterhead));
        // $footerB64 = base64_encode(file_get_contents($footerImg));
        // $headerExt = strtolower(pathinfo($letterhead, PATHINFO_EXTENSION));
        // $footerExt = strtolower(pathinfo($footerImg,  PATHINFO_EXTENSION));
 
        // $headerHtml = '
        //     <div style="margin:0;padding:0;line-height:0;">
        //         <img src="data:image/' . $headerExt . ';base64,' . $headerB64 . '"
        //              style="width:100%;display:block;">
        //     </div>';
 
        // $footerHtml = '
        //     <div style="margin:0;padding:0;line-height:0;">
        //         <img src="data:image/' . $footerExt . ';base64,' . $footerB64 . '"
        //              style="width:100%;display:block;">
        //     </div>';
 
        // ── Inisialisasi mPDF ─────────────────────────────────────────────
        $mpdf = new Mpdf([
            'format'        => 'A4',
            'tempDir'       => storage_path('app/mpdf'),
            'margin_top'    => 5,   // ← sesuaikan dengan tinggi header image (mm)
            'margin_bottom' => 0,   // ← sesuaikan dengan tinggi footer image (mm)
            'margin_left'   => 5,
            'margin_right'  => 5,
            // penting
            'margin_header' => 0,
            'margin_footer' => 0,
            // penting
            'setAutoTopMargin'    => 'stretch',
            'setAutoBottomMargin' => 'stretch',
        ]);
        
        $mpdf->splitTableBorderWidth = 1;
 
        // $mpdf->SetHTMLHeader($headerHtml);
        // $mpdf->SetHTMLFooter($footerHtml);
 
        $mpdf->WriteHTML($html);
 
        // ── Output ────────────────────────────────────────────────────────
        $filename = 'PTK_' . ($ptk->Task_ID);
        $mpdf->SetTitle($filename);
 
        // 'I' = tampil di browser, 'D' = download, 'F' = simpan ke file
        return response($mpdf->Output($filename . '.pdf', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
