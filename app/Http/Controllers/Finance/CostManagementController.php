<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CostManagementController extends Controller
{
    public function pickupCostIndex(Request $r)
    {
        return view('finance.ops.cost.index');
    }

    public function approvePickupCost(Request $r)
    {
        $id = $r->input('id');
        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID is required'], 400);
        }

        DB::transaction(function () use ($r, $id) {
            DB::table('pickup_routes')->where('ID', $id)->update([
                'Status'                 => 'PENDING_SETTLEMENT',
                'Transfer_Bank_Account'   => $r->input('rekening'),
                'Transfer_Nominal'        => $r->input('nominal'),
            ]);

            if ($r->hasFile('bukti_transfer') && $r->file('bukti_transfer')->isValid()) {
                $file  = $r->file('bukti_transfer');
                $uuid  = \Illuminate\Support\Str::uuid()->toString();
                $ext   = $file->getClientOriginalExtension();
                $year  = date('Y');
                $month = date('m');
                $filename = "$uuid.$ext";

                $file->move(public_path("filevendor/$year/$month"), $filename);

                DB::table('meta_files')->insert([
                    'Filename'    => $uuid,
                    'Path'        => "filevendor/$year/$month/$filename",
                    'Extension'   => $ext,
                    'Description' => 'Bukti Transfer',
                    'Topic'       => 'pickup-cost',
                    'Map_To'      => $id,
                    'Created_At'  => now(),
                ]);
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Route approved successfully']);
    }
}
