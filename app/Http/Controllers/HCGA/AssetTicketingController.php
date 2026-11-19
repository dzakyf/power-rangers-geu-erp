<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\AssetTicketingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetTicketingController extends Controller
{
    /** @var AssetTicketingService */
    protected $ticketingService;

    public function __construct(AssetTicketingService $ticketingService)
    {
        $this->ticketingService = $ticketingService;
    }

    public function index(Request $request)
    {
        $assets = DB::table('asset_list')
            ->where(function ($q) {
                $q->whereNull('Status')->orWhere('Status', '!=', 'DISPOSED');
            })
            ->select('Asset_No', 'Product_Description', 'Asset_Location', 'Kondisi_Aset', 'Status', 'Buy_Val', 'SN', 'User_Responsible')
            ->orderBy('Asset_No', 'ASC')
            ->limit(1000)
            ->get();

        $prefillAssetNo = trim((string) $request->input('asset_no', ''));
        $prefillAction = strtoupper(trim((string) $request->input('action', '')));

        return view('humanresources.asset-ticketing.index', compact('assets', 'prefillAssetNo', 'prefillAction'));
    }

    public function data(Request $request)
    {
        $data = $this->ticketingService->getDashboardData([
            'type'     => $request->input('type', 'ALL'),
            'search'   => $request->input('search', ''),
            'asset_no' => $request->input('asset_no', ''),
        ]);

        return response()->json([
            'status'  => 'success',
            'items'   => $data['items'],
            'summary' => $data['summary'],
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Sesi login berakhir.'], 401);
        }

        $actionType = strtoupper(trim((string) $request->input('action_type', '')));

        try {
            if ($actionType === 'DAMAGE') {
                $ticket = $this->ticketingService->createDamageTicket($request->all(), $user);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Tiket kerusakan aset ' . ($ticket->Ticket_No ?? '') . ' berhasil dibuat.',
                    'data'    => $ticket,
                ]);
            }

            if ($actionType === 'PROCUREMENT') {
                $res = $this->ticketingService->createProcurementRequest($request->all(), $user);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Pengajuan pengadaan ' . ($res['pr_no'] ?? '') . ' berhasil dikirim ke approval.',
                    'data'    => $res,
                ]);
            }

            if ($actionType === 'MOVING') {
                $res = $this->ticketingService->createMovingRequest($request->all(), $user);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Pengajuan pemindahan ' . ($res['request_no'] ?? '') . ' berhasil dikirim ke approval.',
                    'data'    => $res,
                ]);
            }

            if ($actionType === 'DISPOSAL') {
                $res = $this->ticketingService->createDisposalRequest($request->all(), $user);
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Pengajuan pemusnahan ' . ($res['disposal_no'] ?? '') . ' berhasil dikirim ke approval.',
                    'data'    => $res,
                ]);
            }

            return response()->json(['status' => 'error', 'message' => 'Tipe pengajuan tidak valid.'], 422);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function escalate(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Sesi login berakhir.'], 401);
        }

        try {
            $res = $this->ticketingService->escalateDamageTicket(
                (int) $request->input('ticket_id'),
                (string) $request->input('target_type'),
                $request->all(),
                $user
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Eskalasi berhasil dibuat (' . ($res['request_no'] ?? $res['pr_no'] ?? '') . ').',
                'data'    => $res,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function detail(Request $request)
    {
        $category = strtoupper(trim((string) $request->input('category', '')));
        $id = (int) $request->input('id', 0);

        $detail = $this->ticketingService->getDetail($category, $id);
        return view('humanresources.asset-ticketing.detail', [
            'detail' => $detail,
        ]);
    }
}
