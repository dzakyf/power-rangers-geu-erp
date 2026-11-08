<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Services\HCGA\SppdMobileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SppdMobileController extends Controller
{
    /**
     * @var SppdMobileService
     */
    protected $mobileService;

    public function __construct(SppdMobileService $mobileService)
    {
        $this->mobileService = $mobileService;
    }

    /**
     * View Utama Mobile Webview SPPD (Daftar & Status)
     */
    public function mobileIndex(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status', 'ALL');
        $sppds = $this->mobileService->getUserSppdList($user->id, $status, 30);

        return view('humanresources.sppd.mobile.index', compact('user', 'sppds', 'status'));
    }

    /**
     * View Form Pengajuan SPPD Mobile Webview
     */
    public function mobileForm(Request $request)
    {
        $user = Auth::user();
        $departments = DB::table('master_department')->orderBy('Department', 'ASC')->get();
        $locations = DB::table('location')->orderBy('Name', 'ASC')->get();
        $fleetList = CarRentController::fleetOptions();

        $typeCounts = [
            'NORMAL'     => DB::table('sppd')->where('Sppd_Type', 'NORMAL')->count(),
            'PERBANTUAN' => DB::table('sppd')->where('Sppd_Type', 'PERBANTUAN')->count(),
            'RELOKASI'   => DB::table('sppd')->where('Sppd_Type', 'RELOKASI')->count(),
        ];
        $ymd = date('ymd');
        $SPPD_No = SppdController::nextSppdNumber('NORMAL');

        // Deteksi job level
        $jobLevel = 'pelaksana';
        $empData = DB::table('employment')->where('NIK', $user->Emp_ID)->first();
        if ($empData && !empty($empData->Position)) {
            $pos = DB::table('master_position')->where("Initial", $empData->Position)->first();
            $grade = $pos->Level ?? 0;
            if ($grade >= 1 && $grade <= 3) {
                $jobLevel = 'management';
            } elseif ($grade >= 4 && $grade <= 6) {
                $jobLevel = 'senior_middle';
            } elseif ($grade >= 7 && $grade <= 8) {
                $jobLevel = 'junior_middle';
            } else {
                $jobLevel = 'pelaksana';
            }
        }

        return view('humanresources.sppd.mobile.form', compact('user', 'departments', 'locations', 'fleetList', 'typeCounts', 'ymd', 'SPPD_No', 'jobLevel'));
    }

    /**
     * View Detail SPPD Mobile Webview
     */
    public function mobileDetail(Request $request)
    {
        $user = Auth::user();
        $id = $request->query('id', $request->query('ID'));
        if (!$id) {
            abort(404, 'ID SPPD tidak ditemukan');
        }

        $detail = $this->mobileService->getDetail($id, $user);
        if (!$detail) {
            abort(404, 'Data SPPD tidak ditemukan');
        }

        return view('humanresources.sppd.mobile.detail', array_merge($detail, ['user' => $user]));
    }

    /**
     * View Antrean Approval SPPD Mobile Webview
     */
    public function mobileApproval(Request $request)
    {
        $user = Auth::user();
        $queue = $this->mobileService->getApproverQueue($user, 30);

        return view('humanresources.sppd.mobile.approval', compact('user', 'queue'));
    }

    // ==========================================
    // REST API ENDPOINTS (JSON RESPONSE)
    // ==========================================

    /**
     * API: Get List SPPD Pemohon
     */
    public function apiList(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status', 'ALL');
        $limit = (int) $request->input('limit', 20);

        $data = $this->mobileService->getUserSppdList($user->id, $status, $limit);

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar SPPD berhasil dimuat',
            'data'    => $data,
        ], 200);
    }

    /**
     * API: Get Antrean Approval SPPD
     */
    public function apiApprovalQueue(Request $request)
    {
        $user = Auth::user();
        $limit = (int) $request->input('limit', 20);

        $data = $this->mobileService->getApproverQueue($user, $limit);

        return response()->json([
            'status'  => 'success',
            'message' => 'Antrean approval SPPD berhasil dimuat',
            'data'    => $data,
        ], 200);
    }

    /**
     * API: Detail SPPD
     */
    public function apiDetail($id)
    {
        $user = Auth::user();
        $detail = $this->mobileService->getDetail($id, $user);

        if (!$detail) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data SPPD tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail SPPD berhasil dimuat',
            'data'    => $detail,
        ], 200);
    }

    /**
     * API: Submit Pengajuan SPPD Baru
     */
    public function apiSubmit(Request $request)
    {
        $user = Auth::user();

        try {
            $sppd = $this->mobileService->submitNewSppd($request->all(), $user);

            return response()->json([
                'status'  => 'success',
                'message' => 'Pengajuan SPPD berhasil dibuat',
                'data'    => [
                    'id'      => (int) $sppd->ID,
                    'sppd_no' => $sppd->SPPD_No,
                    'status'  => $sppd->Status,
                ],
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API SPPD Submit Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem saat memproses pengajuan SPPD',
                'debug'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * API: Submit Keputusan Approval SPPD
     */
    public function apiProcessApproval(Request $request)
    {
        $user = Auth::user();
        $id = $request->input('id', $request->input('ID'));
        $action = $request->input('action', $request->input('Approval', '1'));
        $note = $request->input('note', $request->input('Note', ''));

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID SPPD wajib disertakan'], 422);
        }

        try {
            $result = $this->mobileService->processApproval($id, $action, $note, $user);

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'data'    => $result,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('API SPPD Approval Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses approval: ' . $e->getMessage(),
            ], 500);
        }
    }
}

