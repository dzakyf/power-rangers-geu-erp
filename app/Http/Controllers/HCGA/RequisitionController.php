<?php

namespace App\Http\Controllers\HCGA;

use App\Exceptions\ApprovalWorkflowException;
use App\Http\Controllers\Controller;
use App\Services\Approval\RequisitionApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

    protected $approval;

    public function __construct(RequisitionApprovalService $approval)
    {
        $this->approval = $approval;
    }

    public function store(Request $request)
    {
        $request->validate([
            'Job_Position'  => 'required|integer|exists:master_position,ID',
            'Placement'     => 'required|string|max:255',
            'Description'   => 'required|string',
            'Requirement'   => 'required|string',
            'Priority'      => 'required|in:NORMAL,HIGH,URGENT',
            'Allocation'    => 'required|in:NEW,REPLACEMENT',
            'Request_Type'  => 'required|string',
            'Request_Desc'  => 'nullable|required_if:Request_Type,OTHER|string|max:255',
            'Work_Schedule' => 'required|integer|exists:work_calendars,ID',
            'Qty'           => 'required|integer|min:1',
            'Start_Date'    => 'required|date',
            'Task_ID'       => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $LSP = $user->Location;
        $Saya = $user->username;

        $jobPosition = DB::table('master_position')->where('ID', $request->input('Job_Position'))->first();

        $startDate = date('Y-m-d', strtotime($request->input('Start_Date')));
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

        $arr = [
            'Task_ID'          => $request->input('Task_ID'),
            'Job_Position'     => $jobPosition->Position,
            'Department'       => $jobPosition->Department,
            'Subject'          => $request->input('Subject') ?? '',
            'Placement'        => $request->input('Placement'),
            'Description'      => $request->input('Description'),
            'Requirement'      => $request->input('Requirement'),
            'Priority'         => $request->input('Priority'),
            'Allocation'       => $request->input('Allocation'),
            'Request_Type'     => $request->input('Request_Type'),
            'Request_Desc'     => $request->input('Request_Desc'),
            'Work_Schedule'    => $request->input('Work_Schedule'),
            'Qty'              => $request->input('Qty'),
            'Start_Date'       => $startDate,
            'End_Date'         => $endDate,
            'Created_By'       => $Saya,
            'Location'         => $LSP,
            'Created_Date'     => now(),
            'Status'           => 'CREATED',
            'Requisition_Type' => 'NON_PO',
        ];

        // Cegah Task_ID ganda (double submit)
        $dupTask = DB::table('requisition')->where('Task_ID', $request->input('Task_ID'))->first();
        if ($dupTask) {
            return 'exist';
        }

        // Cegah PTK terbuka ganda untuk posisi + departemen yang sama
        $openReq = DB::table('requisition')
            ->where('Job_Position', $jobPosition->Position)
            ->where('Department', $jobPosition->Department)
            ->where('Allocation', $request->input('Allocation'))
            ->whereNotIn('Status', ['CANCELED', 'CLOSED', 'REJECTED'])
            ->orderBy('ID', 'DESC')
            ->first();
        if ($openReq && strtotime($openReq->End_Date) >= strtotime(date('Y-m-d'))) {
            return 'exist';
        }

        // requisition dan requisition_log adalah MyISAM, jadi transaksi tidak
        // bisa rollback baris tersebut. Validasi policy dilakukan SEBELUM
        // insert agar error POLICY_NOT_FOUND tidak meninggalkan data PTK.
        $draftRequisition = (object) [
            'ID' => 0,
            'Task_ID' => $request->input('Task_ID'),
            'Location' => $LSP,
            'Department' => $jobPosition->Department,
        ];

        $reqId = null;

        try {
            $this->approval->assertCanSubmit($draftRequisition, $jobPosition, $user);

            DB::transaction(function () use ($arr, $jobPosition, $user, &$reqId) {
                $reqId = DB::table('requisition')->insertGetId($arr);

                $logArr = $arr;
                $logArr['Action_By'] = $user->username;
                $logArr['Action_Date'] = now();
                $logArr['Action_Status'] = 'CREATE FROM REQUISITION';
                $logArr['Last_ID'] = $reqId;

                DB::table('requisition_log')->insert($logArr);

                // Buat approval_request + task dari policy yang aktif.
                $requisition = DB::table('requisition')->where('ID', $reqId)->first();
                $this->approval->submit($requisition, $jobPosition, $user);
            });
        } catch (ApprovalWorkflowException $e) {
            if ($reqId !== null) {
                $this->approval->cleanupFailedSubmission($request->input('Task_ID'), (int) $reqId);
            }

            Log::warning('Requisition approval policy gagal: ' . $e->getMessage(), [
                'code' => $e->getErrorCode(),
                'Task_ID' => $request->input('Task_ID'),
            ]);

            return response()->json([
                'message' => $e->getMessage(),
                'code' => $e->getErrorCode(),
            ], $e->getStatusCode());
        } catch (\Throwable $e) {
            if ($reqId !== null) {
                $this->approval->cleanupFailedSubmission($request->input('Task_ID'), (int) $reqId);
            }

            Log::error('Requisition submit gagal: ' . $e->getMessage(), [
                'Task_ID' => $request->input('Task_ID'),
            ]);

            return response()->json([
                'message' => 'Requisition gagal diajukan: ' . $e->getMessage(),
            ], 500);
        }

        return 'success';
    }
}
