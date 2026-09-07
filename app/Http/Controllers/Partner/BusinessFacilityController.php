<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\User;
use App\WarehousePartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BusinessFacilityController extends Controller
{
    public function index()
    {
        return view('partner.business-facility');
    }

    public function indexXml()
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $priv = auth()->user()->Priv_ID;
        $getUsers = User::get();
        $usname = [];
        foreach ($getUsers as $key => $value) {
            $usname[$value->username] = $value->Name;
            $usname[$value->Emp_ID] = $value->Name;
        }
        $getPartner = DB::table('opportunity_partner')->get();
        foreach ($getPartner as $key => $value) {
            $usname[$value->lead_id] = $value->full_name;
        }
        $nomor = 1;

        if ($priv == 'MITRA') {
            $get = WarehousePartner::where('partner_task_id', auth()->user()->Emp_ID)->get();
        } else {
            $get = WarehousePartner::get();
        }

        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id] ?? $Fetch->partner_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->capacity) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function indexDetailBooth(Request $request)
    {
        $wh = $this->resolveWarehouse($request);

        $partner = DB::table('opportunity_partner')->where('lead_id', $wh->partner_task_id)->first();
        if (!$partner) {
            abort(404, 'Partner not found for this warehouse');
        }

        $id = $partner->id;
        return view('partner.business-facility-detail-booth', compact('id', 'wh', 'partner'));
    }

    public function indexDetailBoothInventoryXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $partnerId = $request->Task;
        $whTaskId  = $request->wh;

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $partnerId)->when($whTaskId, function ($q) use ($whTaskId) {
            $q->where('warehouse_task_id', $whTaskId);
        })->get();
        $getCheckedBySales = [];
        foreach ($getChecked as $key => $value) {
            $getCheckedBySales[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Sales,
                'Date' => $value->Send_Date
            ];
            $getCheckedByPartner[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Partner,
                'Date' => $value->Received_Date
            ];
        }

        // Sample data, replace with actual database query
        $masterFacility = DB::table('master_facility')->where('type', 'booth')->where('category', 'inventory')->where('is_deleted', 0)->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($facility->description) . "</cell>";
            if (isset($getCheckedByPartner[$facility->id])) {
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Date']) . "</cell>";
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell>0</cell>";
            }
            echo "<cell>" . htmlspecialchars($facility->asset_type) . "</cell>";
            $cekDtl = DB::table('opportunity_partner_facility_dtl')->where("Facility_ID", $facility->id)->where('Partner_ID', $partnerId)->when($whTaskId, function ($q) use ($whTaskId) {
                $q->where('warehouse_task_id', $whTaskId);
            })->first();
            if ($cekDtl) {
                echo "<cell>" . htmlspecialchars($cekDtl->Qty) . "</cell>";
                echo "<cell>" . htmlspecialchars($cekDtl->Price) . "</cell>";
                echo "<cell>" . htmlspecialchars($cekDtl->Total) . "</cell>";
                echo "<cell>" . htmlspecialchars($cekDtl->Order_Date) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailBoothAccessXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $partnerId = $request->Task;
        $whTaskId  = $request->wh;

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $partnerId)->when($whTaskId, function ($q) use ($whTaskId) {
            $q->where('warehouse_task_id', $whTaskId);
        })->get();
        $getCheckedBySales = [];
        foreach ($getChecked as $key => $value) {
            $getCheckedBySales[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Sales,
                'Date' => $value->Send_Date
            ];
            $getCheckedByPartner[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Partner,
                'Date' => $value->Received_Date
            ];
        }

        // Sample data, replace with actual database query
        $masterFacility = DB::table('master_facility')->where('type', 'booth')->where('category', 'access')->where('is_deleted', 0)->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($facility->description) . "</cell>";
            $facility->children = json_decode($facility->children);
            $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
            echo "<cell>" . htmlspecialchars($facility->children) . "</cell>";
            if (isset($getCheckedByPartner[$facility->id])) {
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Date']) . "</cell>";
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell>0</cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailBoothSupportXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $partnerId = $request->Task;
        $whTaskId  = $request->wh;

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $partnerId)->when($whTaskId, function ($q) use ($whTaskId) {
            $q->where('warehouse_task_id', $whTaskId);
        })->get();
        $getCheckedBySales = [];
        foreach ($getChecked as $key => $value) {
            $getCheckedBySales[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Sales,
                'Date' => $value->Send_Date
            ];
            $getCheckedByPartner[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Partner,
                'Date' => $value->Received_Date
            ];
        }

        // Sample data, replace with actual database query
        $masterFacility = DB::table('master_facility')->where('type', 'booth')->where('category', 'support')->where('is_deleted', 0)->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($facility->description) . "</cell>";
            $facility->children = json_decode($facility->children);
            $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
            echo "<cell>" . htmlspecialchars($facility->children) . "</cell>";
            if (isset($getCheckedByPartner[$facility->id])) {
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Date']) . "</cell>";
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell>0</cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailMobil(Request $request)
    {
        $wh = $this->resolveWarehouse($request);

        $partner = DB::table('opportunity_partner')->where('lead_id', $wh->partner_task_id)->first();
        if (!$partner) {
            abort(404, 'Partner not found for this warehouse');
        }

        $id = $partner->id;
        return view('partner.business-facility-detail-mobil', compact('id', 'wh', 'partner'));
    }

    public function indexDetailMobilInventoryXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $partnerId = $request->Task;
        $whTaskId  = $request->wh;

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $partnerId)->when($whTaskId, function ($q) use ($whTaskId) {
            $q->where('warehouse_task_id', $whTaskId);
        })->get();
        $getCheckedBySales = [];
        foreach ($getChecked as $key => $value) {
            $getCheckedBySales[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Sales,
                'Date' => $value->Send_Date
            ];
            $getCheckedByPartner[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Partner,
                'Date' => $value->Received_Date
            ];
        }

        // Sample data, replace with actual database query
        $masterFacility = DB::table('master_facility')->where('type', 'mobil')->where('category', 'inventory')->where('is_deleted', 0)->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($facility->description) . "</cell>";
            $facility->children = json_decode($facility->children);
            $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
            if (is_object($facility->children)) {
                $child = "";
                foreach ($facility->children as $key => $value) {
                    $child .= "$key : " .  (is_array($value) ? implode(", ", $value) : json_encode($value)) . ", ";
                }
                $child = substr($child, 0, -2);
                $facility->children = $child;
            }
            echo "<cell>" . htmlspecialchars($facility->children) . "</cell>";
            if (isset($getCheckedByPartner[$facility->id])) {
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Date']) . "</cell>";
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell>0</cell>";
            }
            echo "<cell>" . htmlspecialchars($facility->asset_type) . "</cell>";

            $cekDtl = DB::table('opportunity_partner_facility_dtl')->where("Facility_ID", $facility->id)->where('Partner_ID', $partnerId)->when($whTaskId, function ($q) use ($whTaskId) {
                $q->where('warehouse_task_id', $whTaskId);
            })->first();
            if ($cekDtl) {
                echo "<cell>" . htmlspecialchars($cekDtl->Qty) . "</cell>";
                echo "<cell>" . htmlspecialchars($cekDtl->Price) . "</cell>";
                echo "<cell>" . htmlspecialchars($cekDtl->Total) . "</cell>";
                echo "<cell>" . htmlspecialchars($cekDtl->Order_Date) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailMobilAccessXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $partnerId = $request->Task;
        $whTaskId  = $request->wh;

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $partnerId)->when($whTaskId, function ($q) use ($whTaskId) {
            $q->where('warehouse_task_id', $whTaskId);
        })->get();
        $getCheckedBySales = [];
        foreach ($getChecked as $key => $value) {
            $getCheckedBySales[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Sales,
                'Date' => $value->Send_Date
            ];
            $getCheckedByPartner[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Partner,
                'Date' => $value->Received_Date
            ];
        }

        // Sample data, replace with actual database query
        $masterFacility = DB::table('master_facility')->where('type', 'mobil')->where('category', 'access')->where('is_deleted', 0)->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($facility->description) . "</cell>";
            $facility->children = json_decode($facility->children);
            $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
            echo "<cell>" . htmlspecialchars($facility->children) . "</cell>";
            if (isset($getCheckedByPartner[$facility->id])) {
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Date']) . "</cell>";
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell>0</cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailMobilSupportXml(Request $request)
    {
        if (stristr($_SERVER["HTTP_ACCEPT"] ?? "", "application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml");
        } else {
            header("Content-type: text/xml");
        }
        echo ("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $partnerId = $request->Task;
        $whTaskId  = $request->wh;

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $partnerId)->when($whTaskId, function ($q) use ($whTaskId) {
            $q->where('warehouse_task_id', $whTaskId);
        })->get();
        $getCheckedBySales = [];
        foreach ($getChecked as $key => $value) {
            $getCheckedBySales[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Sales,
                'Date' => $value->Send_Date
            ];
            $getCheckedByPartner[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Partner,
                'Date' => $value->Received_Date
            ];
        }

        // Sample data, replace with actual database query
        $masterFacility = DB::table('master_facility')->where('type', 'mobil')->where('category', 'support')->where('is_deleted', 0)->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($facility->description) . "</cell>";
            $facility->children = json_decode($facility->children);
            $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
            echo "<cell>" . htmlspecialchars($facility->children) . "</cell>";
            if (isset($getCheckedByPartner[$facility->id])) {
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Date']) . "</cell>";
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell>0</cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function detailFacilityUpdate()
    {
        header("Content-type: text/xml");
        echo ('<?xml version="1.0" encoding="iso-8859-1"?>');
        echo "<data>";

        $request_body = urldecode(file_get_contents('php://input'));
        $Saya = Auth::user()->username;
        $LSP = Auth::user()->Location;

        $ids = explode("&", $request_body);
        foreach ($ids as $datax) {

            $idenya = explode("=", $datax);
            if (isset($idenya[1])) {
                $param[$idenya[0]] = $idenya[1];
            }
        }
        $checkids = explode(",", $param["ids"]);

        for ($i = 0; $i < sizeof($checkids); $i++) {
            $rowId = $checkids[$i]; //id or row which was updated 
            if ($rowId != 'customPayload') {
                $newId = $rowId; //will be used for insert operation	
                $mode = $param[$rowId . "_!nativeeditor_status"];

                switch ($mode) {
                    case "updated":
                        $action = $this->detailFacilityUpdatekan($rowId, $param);
                        break;
                    case "deleted":
                        $action = $this->detailFacilityDeletekan($rowId, $param);
                        break;
                    case "inserted":
                        $action = $this->detailFacilityInsertkan($rowId, $param);
                        break;
                }

                echo "<action type='" . $action . "' sid='" . $rowId . "' tid='" . $newId . "'/>";
            }
        }
        echo "</data>";

        $opportunityId = $_GET['Task'] ?? null;
        if ($opportunityId) {
            app(\App\Http\Controllers\ApprovalController::class)
                ->completeSetupIfReady($opportunityId);
        }
    }

    public function detailFacilityUpdatekan($rowId, $param)
    {
        $b = $param[$rowId . "_c2"] ?? ''; // booth: received date
        $c = $param[$rowId . "_c3"] ?? ''; // booth: receipt checkbox / mobil+access: received date
        $d = $param[$rowId . "_c4"] ?? ''; // mobil+access: receipt checkbox
        $e = $param[$rowId . "_c5"] ?? ''; // booth: Qty
        $f = $param[$rowId . "_c6"] ?? ''; // mobil: Qty / booth: Price
        $g = $param[$rowId . "_c7"] ?? ''; // mobil: Price / booth: Total
        $h = $param[$rowId . "_c8"] ?? ''; // mobil: Total / booth: Order_Date
        $ix = $param[$rowId . "_c9"] ?? ''; // mobil: Order_Date
        $Saya = auth()->user()->username;

        $partnerId = $_GET['Task'] ?? null;
        $whTaskId  = $_GET['wh'] ?? null;

        // The Checked_Partner checkbox and Received_Date column positions differ
        // per grid layout:
        //   mobil inventory (c9 present): checkbox=c4, date=c3, Qty=c6, Price=c7, Total=c8, Order_Date=c9
        //   booth inventory  (c6 present, no c9): checkbox=c3, date=c2, Qty=c5, Price=c6, Total=c7, Order_Date=c8
        //   access/support   (no c6): checkbox=c4, date=c3 (no dtl columns)
        if (isset($param[$rowId . "_c9"])) {
            $checkPartner = $d;
            $receivedDate = $c;
            $qty = $f;
            $price = $g;
            $total = $h;
            $orderDateIn = $ix;
        } elseif (isset($param[$rowId . "_c6"])) {
            $checkPartner = $c;
            $receivedDate = $b;
            $qty = $e;
            $price = $f;
            $total = $g;
            $orderDateIn = $h;
        } else {
            $checkPartner = $d;
            $receivedDate = $c;
            $qty = null;
            $price = null;
            $total = null;
            $orderDateIn = null;
        }

        if ($checkPartner == 1) {
            // Receipt confirmation without a picked date falls back to today
            // (mirrors the sales side storing Send_Date => now()).
            $receivedDate = $receivedDate ?: now()->format('Y-m-d');
            $cekDB = DB::table('opportunity_partner_facility')
                ->where('Facility_ID', $rowId)
                ->where("Partner_ID", $partnerId)
                ->when($whTaskId, function ($q) use ($whTaskId) {
                    $q->where('warehouse_task_id', $whTaskId);
                })
                ->first();
            if ($cekDB) {
                DB::table('opportunity_partner_facility')
                    ->where('ID', $cekDB->ID)
                    ->update([
                        'Checked_Partner' => $checkPartner,
                        'Checked_Partner_By' => $Saya,
                        'Checked_Partner_Date' => now(),
                        'Received_Date' => $receivedDate,
                    ]);
            } else {
                DB::table('opportunity_partner_facility')
                    ->insert([
                        'Facility_ID' => $rowId,
                        'Partner_ID' => $partnerId,
                        'warehouse_task_id' => $whTaskId,
                        'Checked_Partner' => $checkPartner,
                        'Checked_Partner_By' => $Saya,
                        'Checked_Partner_Date' => now(),
                        'Received_Date' => $receivedDate,
                        'Created_By' => $Saya,
                        'Created_Date' => now(),
                    ]);
            }
        }

        if ($qty !== null) {
            // Persist Qty/Price/Total whenever any of them is filled; the
            // order date falls back to today when left blank.
            if (trim((string) $qty) !== '' || trim((string) $price) !== '' || trim((string) $total) !== '') {
                $orderDate = $orderDateIn ?: now()->format('Y-m-d');
                $cekDtl = DB::table('opportunity_partner_facility_dtl')->where("Facility_ID", $rowId)->where('Partner_ID', $partnerId)->when($whTaskId, function ($q) use ($whTaskId) {
                    $q->where('warehouse_task_id', $whTaskId);
                })->first();
                if ($cekDtl) {
                    DB::table('opportunity_partner_facility_dtl')
                        ->where('ID', $cekDtl->ID)
                        ->update([
                            'Qty' => $qty,
                            'Price' => $price,
                            'Total' => $total,
                            'Order_Date' => $orderDate,
                        ]);
                } else {
                    DB::table('opportunity_partner_facility_dtl')
                        ->insert([
                            'Facility_ID' => $rowId,
                            'Partner_ID' => $partnerId,
                            'warehouse_task_id' => $whTaskId,
                            'Qty' => $qty,
                            'Price' => $price,
                            'Total' => $total,
                            'Order_Date' => $orderDate,
                            'Created_By' => $Saya,
                            'Created_Date' => now(),
                        ]);
                }
            }

            if (trim((string) $orderDateIn) !== '') {
                $cekDtl = DB::table('opportunity_partner_facility_dtl')->where("Facility_ID", $rowId)->where('Partner_ID', $partnerId)->when($whTaskId, function ($q) use ($whTaskId) {
                    $q->where('warehouse_task_id', $whTaskId);
                })->first();
                if ($cekDtl) {
                    DB::table('opportunity_partner_facility_dtl')
                        ->where('ID', $cekDtl->ID)
                        ->update([
                            'Order_Date' => $orderDateIn
                        ]);
                } else {
                    DB::table('opportunity_partner_facility_dtl')
                        ->insert([
                            'Facility_ID' => $rowId,
                            'Partner_ID' => $partnerId,
                            'warehouse_task_id' => $whTaskId,
                            'Order_Date' => $orderDateIn,
                            'Created_By' => $Saya,
                            'Created_Date' => now(),
                        ]);
                }
            }
        }

        return 'update';
    }

    public function detailFacilityDeletekan($rowId, $param) {}

    public function detailFacilityInsertkan($rowId, $param) {}

    /**
     * Resolve warehouse ID from request or current user.
     * Accepts warehouse_partner.id or warehouse_task_id in ?ID=,
     * or derives the first matching warehouse for the logged-in user.
     */
    private function resolveWarehouse(Request $request): WarehousePartner
    {
        $task = $request->ID;

        // If ID is provided, look it up directly (accepts id or warehouse_task_id)
        if ($task) {
            $wh = WarehousePartner::where('id', $task)->orWhere('warehouse_task_id', $task)->first();
            if ($wh) {
                return $wh;
            }
        }

        // Derive from current user
        $user = auth()->user();
        if ($user->Priv_ID === 'MITRA') {
            // MITRA: their Emp_ID = partner_task_id
            $wh = WarehousePartner::where('partner_task_id', $user->Emp_ID)->first();
        } else {
            // Internal: find opportunity_partner by user_row_id, then warehouse by lead_id
            $opp = DB::table('opportunity_partner')
                ->where('user_row_id', $user->id)
                ->orWhere('lead_id', $user->Emp_ID)
                ->first();
            if ($opp) {
                $wh = WarehousePartner::where('partner_task_id', $opp->lead_id)->first();
            }
        }

        if (!isset($wh) || !$wh) {
            abort(404, 'Warehouse not found for this user. Please contact admin.');
        }

        return $wh;
    }
}
