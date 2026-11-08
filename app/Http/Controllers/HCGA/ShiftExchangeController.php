<?php

namespace App\Http\Controllers\HCGA;

use App\Http\Controllers\Controller;
use App\Models\HCGA\MasterShift;
use App\Services\Attendance\ShiftExchangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftExchangeController extends Controller
{
    /**
     * @var ShiftExchangeService
     */
    protected $shiftExchangeService;

    public function __construct(ShiftExchangeService $shiftExchangeService)
    {
        $this->shiftExchangeService = $shiftExchangeService;
    }

    /**
     * API: Ambil jadwal shift yang berlaku untuk tanggal tertentu
     */
    public function getScheduleByDate(Request $request)
    {
        $user = Auth::user();
        $date = $request->query('date', date('Y-m-d'));
        $targetNik = $request->query('nik', $user->Emp_ID);

        $sched = $this->shiftExchangeService->getScheduleDetail($targetNik, $date);

        return response()->json([
            'status' => 'success',
            'data'   => $sched,
        ]);
    }

    /**
     * API: Submit Pengajuan Change / Exchange Shift
     */
    public function submitChangeShift(Request $request)
    {
        $request->validate([
            'request_mode'          => 'required|in:CHANGE,EXCHANGE',
            'original_date'         => 'required|date_format:Y-m-d',
            'target_shift_id'       => 'required|integer',
            'target_date'           => 'nullable|date_format:Y-m-d',
            'peer_nik'              => 'nullable|string|max:50',
            'peer_target_shift_id'  => 'nullable|integer',
            'reason'                => 'required|string|max:255',
            'attachment_file'       => 'nullable|file|max:5120',
        ]);

        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sesi login telah berakhir. Silakan login kembali.',
                ], 401);
            }

            $uploaded = $request->hasFile('attachment_file') ? $request->file('attachment_file') : null;
            $result = $this->shiftExchangeService->submitChangeShiftRequest($user, $request->all(), $uploaded);

            return response()->json([
                'status'  => 'success',
                'message' => $result['message'],
                'data'    => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

