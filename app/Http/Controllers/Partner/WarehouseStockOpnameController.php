<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\WarehouseStockOpname;
use App\WarehouseStockAdjustment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class WarehouseStockOpnameController extends Controller
{
    const ADJUSTMENT_THRESHOLD = 0.03;

    public function indexStockOpname()
    {
        return view('partner.warehouse.stock-opname');
    }

    public function getStockOpnameData(Request $r)
    {
        $status = $r->query('status');

        $query = WarehouseStockOpname::query()
            ->leftJoin('warehouse_partner as wp', 'warehouse_stock_opnames.Warehouse_ID', '=', 'wp.id')
            ->select('warehouse_stock_opnames.*', 'wp.warehouse_task_id', 'wp.warehouse_location')
            ->orderBy('warehouse_stock_opnames.Created_At', 'desc');

        if ($status) {
            $query->where('warehouse_stock_opnames.Status', $status);
        }

        $data = $query->get();

        if ($r->query('format') === 'xml') {
            $xml = new SimpleXMLElement('<rows/>');
            foreach ($data as $i => $d) {
                $row = $xml->addChild('row');
                $row->addAttribute('ID', $d->ID);
                $row->addChild('cell', $i + 1);
                $row->addChild('cell', htmlspecialchars((string) $d->Opname_Number));
                $row->addChild('cell', htmlspecialchars((string) $d->warehouse_task_id));
                $row->addChild('cell', htmlspecialchars((string) $d->warehouse_location));
                $row->addChild('cell', (string) $d->System_Qty);
                $row->addChild('cell', (string) $d->Physical_Qty);
                $row->addChild('cell', (string) $d->Difference);
                $row->addChild('cell', htmlspecialchars((string) $d->Status));
                $row->addChild('cell', $d->Created_At ?? '');
            }
            return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
        }

        return response()->json(['data' => $data]);
    }

    public function createStockOpname()
    {
        return view('partner.warehouse.stock-opname-new');
    }

    public function submitStockOpname(Request $r)
    {
        DB::beginTransaction();
        try {
            $highest = WarehouseStockOpname::max('ID') ?? 0;
            $opnameNumber = 'WSO-' . date('Y') . '/' . str_pad(date('m'), 2, '0', STR_PAD_LEFT) . '-' . str_pad($highest + 1, 4, '0', STR_PAD_LEFT);

            // Determine system quantity for this warehouse
            $systemQty = $r->system_qty ?? 0;

            $opname = WarehouseStockOpname::create([
                'Opname_Number' => $opnameNumber,
                'Warehouse_ID'  => $r->warehouse_id,
                'Opname_Date'   => $r->opname_date ?? now()->toDateString(),
                'System_Qty'    => $systemQty,
                'Physical_Qty'  => 0,
                'Difference'    => 0,
                'Status'        => 'DRAFT',
                'Notes'         => $r->notes,
                'Counted_By'    => auth()->user()->Name ?? auth()->user()->username ?? '',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'id' => $opname->ID, 'opname_number' => $opnameNumber]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function detailStockOpname(Request $r)
    {
        $data = WarehouseStockOpname::findOrFail($r->ID);
        return view('partner.warehouse.stock-opname-detail', compact('data'));
    }

    public function updateStockOpname(Request $r)
    {
        DB::beginTransaction();
        try {
            $opname = WarehouseStockOpname::findOrFail($r->id);
            $physicalQty = $r->physical_qty;
            $difference = $physicalQty - $opname->System_Qty;

            $opname->update([
                'Physical_Qty' => $physicalQty,
                'Difference'   => $difference,
                'Status'       => 'COUNTED',
                'Counted_By'   => $r->counted_by ?? $opname->Counted_By,
                'Notes'        => $r->notes ?? $opname->Notes,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'difference' => $difference]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function finalizeStockOpname(Request $r)
    {
        DB::beginTransaction();
        try {
            $opname = WarehouseStockOpname::findOrFail($r->id);

            if ($opname->Status !== 'COUNTED') {
                return response()->json(['success' => false, 'message' => 'Opname must be in COUNTED status to finalize'], 400);
            }

            $difference = $opname->Difference;
            $adjustmentType = $difference >= 0 ? 'SURPLUS' : 'SHORTAGE';

            // Calculate threshold
            $ratio = $opname->System_Qty > 0
                ? abs($difference) / $opname->System_Qty
                : 1;
            $approvalStatus = $ratio < self::ADJUSTMENT_THRESHOLD ? 'AUTO' : 'PENDING';

            // Create adjustment record if there's a difference
            if ((float) $difference !== 0.0) {
                $adjustment = WarehouseStockAdjustment::create([
                    'Opname_ID'      => $opname->ID,
                    'Warehouse_ID'   => $opname->Warehouse_ID,
                    'Adjustment_Type' => $adjustmentType,
                    'Qty_Before'     => $opname->System_Qty,
                    'Qty_After'      => $opname->Physical_Qty,
                    'Difference'     => abs($difference),
                    'Reason'         => $r->reason ?? 'Stock opname adjustment',
                    'Approval_Status' => $approvalStatus,
                ]);

                if ($approvalStatus === 'AUTO') {
                    $adjustment->update([
                        'Approved_By' => 'system',
                        'Approved_At' => now(),
                    ]);
                }
            }

            $opname->update([
                'Status'      => $approvalStatus === 'AUTO' ? 'CLOSED' : 'CLOSED_PENDING',
                'Approved_By' => $r->approved_by ?? auth()->user()->username ?? '',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Opname finalized successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function actionAdjustment(Request $r)
    {
        $adjustment = WarehouseStockAdjustment::findOrFail($r->id);

        if ($adjustment->Approval_Status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'Adjustment is not in PENDING status'], 400);
        }

        if ($r->action === 'approve') {
            DB::beginTransaction();
            try {
                $adjustment->update([
                    'Approval_Status' => 'APPROVED',
                    'Approved_By'     => auth()->user()->Name ?? auth()->user()->username ?? 'ops',
                    'Approved_At'     => now(),
                    'Approval_Note'   => $r->note,
                ]);

                $opname = WarehouseStockOpname::find($adjustment->Opname_ID);
                if ($opname && $opname->Status === 'CLOSED_PENDING') {
                    $newPhysical = $adjustment->Qty_After;
                    $newDifference = $newPhysical - $opname->System_Qty;
                    $opname->update([
                        'Physical_Qty' => $newPhysical,
                        'Difference'   => $newDifference,
                        'Status'       => 'CLOSED',
                    ]);
                }

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Adjustment approved']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        } elseif ($r->action === 'reject') {
            $adjustment->update([
                'Approval_Status' => 'REJECTED',
                'Approved_By'     => auth()->user()->Name ?? auth()->user()->username ?? 'ops',
                'Approved_At'     => now(),
                'Approval_Note'   => $r->note,
            ]);
            return response()->json(['success' => true, 'message' => 'Adjustment rejected']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action'], 400);
    }



    public function addAdjustment(Request $r)
    {
        DB::beginTransaction();
        try {
            $opname = WarehouseStockOpname::findOrFail($r->opname_id);

            $newPhysical = $r->physical_qty;
            $difference = $newPhysical - $opname->System_Qty;
            $adjustmentType = $difference >= 0 ? 'SURPLUS' : 'SHORTAGE';

            WarehouseStockAdjustment::create([
                'Opname_ID'       => $opname->ID,
                'Warehouse_ID'    => $opname->Warehouse_ID,
                'Adjustment_Type' => $adjustmentType,
                'Qty_Before'      => $opname->Physical_Qty,
                'Qty_After'       => $newPhysical,
                'Difference'      => abs($difference),
                'Reason'          => $r->reason ?? 'Additional adjustment',
                'Approval_Status' => 'PENDING',
            ]);

            $opname->update(['Status' => 'CLOSED_PENDING']);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Adjustment submitted for approval']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getOpnameAdjustments(Request $r)
    {
        $adjustments = WarehouseStockAdjustment::where('Opname_ID', $r->opname_id)
            ->orderBy('Created_At', 'desc')
            ->get();

        return response()->json(['data' => $adjustments]);
    }
}
