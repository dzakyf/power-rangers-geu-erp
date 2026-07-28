<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\User;
use App\WarehousePartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class FacilityController extends Controller
{
    public function index()
    {
        return view('partner.facility');
    }

    public function indexXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
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
        }else {
            $get = WarehousePartner::get();
        }
        
        foreach ($get as $key => $Fetch) {
            echo "<row id=\"$Fetch->warehouse_task_id\">";
            echo "<cell>$nomor</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_location) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id]) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->capacity) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_type == '' ? 'booth' : $Fetch->warehouse_type) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function indexDetailBooth(Request $request)
    {
        $task = $request->ID;
        $wh = WarehousePartner::where("warehouse_task_id", $task)->first();
        $partner = DB::table('opportunity_partner')->where("lead_id", $wh->partner_task_id)->first();
        if (!$partner) {
            $partner  = new stdClass;
            $partner->id = 0;
        }
        return view('partner.facility-detail-booth', compact('wh','partner'));
    }

    public function indexDetailBoothInventoryXml(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";
        $nomor = 1;

        $task = $request->Task;
        $wh = WarehousePartner::where("warehouse_task_id", $task)->first();
        $partner = DB::table('opportunity_partner')->where("lead_id", $wh->partner_task_id)->first();
        
        if (!$partner) {
            $partner  = new stdClass;
            $partner->id = 0;
        }

        $getChecked = DB::table('opportunity_partner_facility')
            ->where('warehouse_task_id', $task)
            ->where('Partner_ID', $partner->id)
            ->get();
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
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                echo "<cell>".htmlspecialchars($facility->asset_type)."</cell>";
                if (isset($getCheckedBySales[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Chckd'] ?? 0)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ? '1' : '0')."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
                $cekDtl = DB::table('opportunity_partner_facility_dtl')->where("Facility_ID", $facility->id)->where('Partner_ID', $partner->id)->first();
                if ($cekDtl) {
                    echo "<cell>".htmlspecialchars($cekDtl->Qty)."</cell>";
                    echo "<cell>".htmlspecialchars($cekDtl->Price)."</cell>";
                    echo "<cell>".htmlspecialchars($cekDtl->Total)."</cell>";
                    echo "<cell>".htmlspecialchars($cekDtl->Order_Date)."</cell>";
                }else {
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
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $task = $request->Task;
        $wh = WarehousePartner::where("warehouse_task_id", $task)->first();
        $partner = DB::table('opportunity_partner')->where("lead_id", $wh->partner_task_id)->first();
        
        if (!$partner) {
            $partner  = new stdClass;
            $partner->id = 0;
        }

        $getChecked = DB::table('opportunity_partner_facility')
            ->where('warehouse_task_id', $task)
            ->where('Partner_ID', $partner->id)
            ->get();
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
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                $facility->children = json_decode($facility->children);
                $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
                echo "<cell>".htmlspecialchars($facility->children)."</cell>";
                if (isset($getCheckedBySales[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Chckd'] ?? 0)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ? '1' : '0')."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailBoothSupportXml(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $task = $request->Task;
        $wh = WarehousePartner::where("warehouse_task_id", $task)->first();
        $partner = DB::table('opportunity_partner')->where("lead_id", $wh->partner_task_id)->first();
        
        if (!$partner) {
            $partner  = new stdClass;
            $partner->id = 0;
        }

        $getChecked = DB::table('opportunity_partner_facility')
            ->where('warehouse_task_id', $task)
            ->where('Partner_ID', $partner->id)
            ->get();
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
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                $facility->children = json_decode($facility->children);
                $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
                echo "<cell>".htmlspecialchars($facility->children)."</cell>";
                if (isset($getCheckedBySales[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Chckd'] ?? 0)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ? '1' : '0')."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailMobil()
    {
        return view('partner.facility-detail-mobil');
    }

    public function indexDetailMobilInventoryXml(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $task = $request->Task;
        $wh = WarehousePartner::where("warehouse_task_id", $task)->first();
        $partner = DB::table('opportunity_partner')->where("lead_id", $wh->partner_task_id)->first();
        
        if (!$partner) {
            $partner  = new stdClass;
            $partner->id = 0;
        }

        $getChecked = DB::table('opportunity_partner_facility')
            ->where('warehouse_task_id', $task)
            ->where('Partner_ID', $partner->id)
            ->get();
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
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                $facility->children = json_decode($facility->children);
                $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
                if(is_object($facility->children)) {
                    $child = "";
                    foreach ($facility->children as $key => $value) {
                        $child .= "$key : " .  (is_array($value) ? implode(", ", $value) : json_encode($value)) . ", ";
                    }
                    $child = substr($child, 0, -2);
                    $facility->children = $child;
                }
                echo "<cell>".htmlspecialchars($facility->children)."</cell>";
                if (isset($getCheckedBySales[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Chckd'] ?? 0)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ? '1' : '0')."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
                echo "<cell>".htmlspecialchars($facility->asset_type)."</cell>";
                
                $cekDtl = DB::table('opportunity_partner_facility_dtl')->where("Facility_ID", $facility->id)->where('Partner_ID', $partner->id)->first();
                if ($cekDtl) {
                    echo "<cell>".htmlspecialchars($cekDtl->Qty)."</cell>";
                    echo "<cell>".htmlspecialchars($cekDtl->Price)."</cell>";
                    echo "<cell>".htmlspecialchars($cekDtl->Total)."</cell>";
                    echo "<cell>".htmlspecialchars($cekDtl->Order_Date)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell></cell>";
                    echo "<cell></cell>";
                    echo "<cell></cell>";
                }
                echo "<cell>".htmlspecialchars($facility->asset_type)."</cell>";
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailMobilAccessXml(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $task = $request->Task;
        $wh = WarehousePartner::where("warehouse_task_id", $task)->first();
        $partner = DB::table('opportunity_partner')->where("lead_id", $wh->partner_task_id)->first();
        
        if (!$partner) {
            $partner  = new stdClass;
            $partner->id = 0;
        }

        $getChecked = DB::table('opportunity_partner_facility')
            ->where('warehouse_task_id', $task)
            ->where('Partner_ID', $partner->id)
            ->get();
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
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                $facility->children = json_decode($facility->children);
                $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
                echo "<cell>".htmlspecialchars($facility->children)."</cell>";
                if (isset($getCheckedBySales[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Chckd'] ?? 0)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ? '1' : '0')."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailMobilSupportXml(Request $request)
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $task = $request->Task;
        $wh = WarehousePartner::where("warehouse_task_id", $task)->first();
        $partner = DB::table('opportunity_partner')->where("lead_id", $wh->partner_task_id)->first();
        
        if (!$partner) {
            $partner  = new stdClass;
            $partner->id = 0;
        }

        $getChecked = DB::table('opportunity_partner_facility')
            ->where('warehouse_task_id', $task)
            ->where('Partner_ID', $partner->id)
            ->get();
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
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                $facility->children = json_decode($facility->children);
                $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
                echo "<cell>".htmlspecialchars($facility->children)."</cell>";
                if (isset($getCheckedBySales[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedBySales[$facility->id]['Chckd'] ?? 0)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ? '1' : '0')."</cell>";
                }else {
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
        echo('<?xml version="1.0" encoding="iso-8859-1"?>'); 
        echo "<data>";

        $request_body = urldecode(file_get_contents('php://input'));
        $Saya = Auth::user()->username;
        $LSP = Auth::user()->Location;

        $ids = explode("&",$request_body);
        foreach($ids as $datax){
            
            $idenya = explode("=",$datax);
            if (isset($idenya[1])) {
                $param[$idenya[0]] = $idenya[1];
            }
            
        }
        $checkids = explode(",",$param["ids"]);

        for ($i=0; $i < sizeof($checkids); $i++) { 
            $rowId = $checkids[$i]; //id or row which was updated 
            if ($rowId != 'customPayload') {
                $newId = $rowId; //will be used for insert operation	
                $mode = $param[$rowId."_!nativeeditor_status"]; 
            
                    switch($mode){
                        case "updated":
                            $action = $this->detailFacilityUpdatekan($rowId,$param);
                            break;
                    case "deleted":
                            $action = $this->detailFacilityDeletekan($rowId,$param);
                            break;
                    case "inserted":
                            $action = $this->detailFacilityInsertkan($rowId,$param);
                            break;
                    }
                    
                echo "<action type='".$action."' sid='".$rowId."' tid='".$newId."'/>";	
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
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name
        $d = $param[$rowId."_c4"]; // transport
        $e = $param[$rowId."_c5"]; // service
        $f = $param[$rowId."_c6"] ?? ""; // treshold
        $g = $param[$rowId."_c7"] ?? ""; // treshold
        $h = $param[$rowId."_c8"] ?? ""; // treshold
        $ix = $param[$rowId."_c9"] ?? ""; // treshold
        $j = $param[$rowId."_c10"] ?? ""; // treshold
        $k = $param[$rowId."_c11"] ?? ""; // treshold

        $Saya = auth()->user()->username;

        if ((isset($param[$rowId."_c6"]) ? $d : $c) == 1) {
            $cekDB = DB::table('opportunity_partner_facility')
                    ->where('Facility_ID', $rowId)
                    ->where("Partner_ID", $_GET['Task'])
                    ->where("warehouse_task_id", $_GET['wh'])
                    ->first();
            if ($cekDB) {
                DB::table('opportunity_partner_facility')
                    ->where('ID', $cekDB->ID)
                    ->update([
                        'Checked_Sales' => isset($param[$rowId."_c6"]) ? $d : $c,
                        'Checked_Sales_By' => $Saya,
                        'Checked_Sales_Date' => now(),
                        'Send_Date' => isset($param[$rowId."_c6"]) ? $c : $b,
                    ]);
            }else {
                DB::table('opportunity_partner_facility')
                    ->insert([
                        'Facility_ID' => $rowId,
                        'warehouse_task_id' => $_GET['wh'],
                        'Partner_ID' => $_GET['Task'],
                        'Checked_Sales' => isset($param[$rowId."_c6"]) ? $d : $c,
                        'Checked_Sales_By' => $Saya,
                        'Checked_Sales_Date' => now(),
                        'Send_Date' => isset($param[$rowId."_c6"]) ? $c : $b,
                    ]);
            }
        }

        if ($f == 1) {
            $cekDB = DB::table('opportunity_partner_facility')
                    ->where('Facility_ID', $rowId)
                    ->where("Partner_ID", $_GET['Task'])
                    ->where('warehouse_task_id', $_GET['wh'])
                    ->first();
            if ($cekDB) {
                DB::table('opportunity_partner_facility')
                    ->where('ID', $cekDB->ID)
                    ->update([
                        'Checked_Partner' => $f,
                        'Checked_Partner_By' => $Saya,
                        'Checked_Partner_Date' => now(),
                        'Received_Date' => $e,
                    ]);
            }else {
                DB::table('opportunity_partner_facility')
                    ->insert([
                        'Facility_ID' => $rowId,
                        'Partner_ID' => $_GET['Task'],
                        'warehouse_task_id' => $_GET['wh'],
                        'Checked_Partner' => $f,
                        'Checked_Partner_By' => $Saya,
                        'Checked_Partner_Date' => now(),
                        'Received_Date' => $e,
                    ]);
            }
        }

        if (isset($param[$rowId."_c11"])) {
            if ($h != '' && $ix != '' && $j != '') {
                $cekDtl = DB::table('opportunity_partner_facility_dtl')->where("Facility_ID", $rowId)->where('Partner_ID', $_GET['Task'])
                ->where('warehouse_task_id', $_GET['wh'])->first();
                if ($cekDtl) {
                    DB::table('opportunity_partner_facility_dtl')
                        ->where('ID', $cekDtl->ID)
                        ->update([
                            'Qty' => $h,
                            'Price' => $ix,
                            'Total' => $j,
                            'Order_Date' => $k
                        ]);
                }else {
                    DB::table('opportunity_partner_facility_dtl')
                        ->insert([
                            'Facility_ID' => $rowId,
                            'Partner_ID' => $_GET['Task'],
                            'warehouse_task_id' => $_GET['wh'],
                            'Qty' => $h,
                            'Price' => $ix,
                            'Total' => $j,
                            'Order_Date' => $k,
                            'Created_By' => $Saya,
                            'Created_Date' => now(),
                        ]);
                }
            }
    
            if ($k != '') {
                $cekDtl = DB::table('opportunity_partner_facility_dtl')->where("Facility_ID", $rowId)->where('Partner_ID', $_GET['Task'])
                ->where('warehouse_task_id', $_GET['wh'])->first();
                if ($cekDtl) {
                    DB::table('opportunity_partner_facility_dtl')
                        ->where('ID', $cekDtl->ID)
                        ->update([
                            'Order_Date' => $k
                        ]);
                }else {
                    DB::table('opportunity_partner_facility_dtl')
                        ->insert([
                            'Facility_ID' => $rowId,
                            'Partner_ID' => $_GET['Task'],
                            'Order_Date' => $k,
                            'Created_By' => $Saya,
                            'Created_Date' => now(),
                        ]);
                }
            }
        }else {
            if ($g != '' && $h != '' && $ix != '') {
                $cekDtl = DB::table('opportunity_partner_facility_dtl')
                ->where("Facility_ID", $rowId)
                ->where('Partner_ID', $_GET['Task'])
                ->where('warehouse_task_id', $_GET['wh'])
                ->first();
                if ($cekDtl) {
                    DB::table('opportunity_partner_facility_dtl')
                        ->where('ID', $cekDtl->ID)
                        ->update([
                            'Qty' => $g,
                            'Price' => $h,
                            'Total' => $ix,
                            'Order_Date' => $j
                        ]);
                }else {
                    DB::table('opportunity_partner_facility_dtl')
                        ->insert([
                            'Facility_ID' => $rowId,
                            'Partner_ID' => $_GET['Task'],
                            'warehouse_task_id' => $_GET['wh'],
                            'Qty' => $g,
                            'Price' => $h,
                            'Total' => $ix,
                            'Order_Date' => $j,
                            'Created_By' => $Saya,
                            'Created_Date' => now(),
                        ]);
                }
            }
    
            if ($j != '') {
                $cekDtl = DB::table('opportunity_partner_facility_dtl')->where("Facility_ID", $rowId)->where('Partner_ID', $_GET['Task'])->first();
                if ($cekDtl) {
                    DB::table('opportunity_partner_facility_dtl')
                        ->where('ID', $cekDtl->ID)
                        ->update([
                            'Order_Date' => $j
                        ]);
                }else {
                    DB::table('opportunity_partner_facility_dtl')
                        ->insert([
                            'Facility_ID' => $rowId,
                            'warehouse_task_id' => $_GET['wh'],
                            'Partner_ID' => $_GET['Task'],
                            'Order_Date' => $j,
                            'Created_By' => $Saya,
                            'Created_Date' => now(),
                        ]);
                }
            }
        }

        

        return 'update';
    }

    public function detailFacilityDeletekan($rowId, $param)
    {

    }

    public function detailFacilityInsertkan($rowId, $param)
    {

    }
}
