<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
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
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        // Sample data, replace with actual database query
        $partners = DB::table('opportunity_partner')->get();
        foreach ($partners as $key => $partner) {
            echo "<row id=\"$partner->id\" >";
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($partner->register_as)."</cell>";
                if ($partner->register_as == 'individual') {
                    echo "<cell>".htmlspecialchars($partner->full_name)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->address)."</cell>";
                }else {
                    echo "<cell>".htmlspecialchars($partner->company_official_name)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->head_office_address)."</cell>";
                }
                echo "<cell>".htmlspecialchars($partner->company_warehouse_count)."</cell>";
                echo "<cell>".htmlspecialchars($partner->company_experience)."</cell>";
                if ($partner->register_as == 'individual') {
                    echo "<cell>".htmlspecialchars($partner->q1)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->q2)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->q3)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->q4)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->q5)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->q6)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->package_option)."</cell>";
                }else {
                    echo "<cell>".htmlspecialchars($partner->c_q1)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->c_q2)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->c_q3)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->c_q4)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->c_q5)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->c_q6)."</cell>";
                    echo "<cell>".htmlspecialchars($partner->company_package)."</cell>";
                }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailBooth()
    {
        $getOppt = DB::table('opportunity_partner')->where("Lead_ID", auth()->user()->Emp_ID)->first();
        if (!$getOppt) {
            abort(403, 'Unauthorized access');
        }
        // dd($getOppt);
        $id = $getOppt->id;
        return view('partner.business-facility-detail-booth', compact('id'));
    }

    public function indexDetailBoothInventoryXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $_GET['Task'])->get();
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
        $masterFacility = DB::table('master_facility')->where('type', 'booth')->where('category', 'inventory')->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailBoothAccessXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $_GET['Task'])->get();
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
        $masterFacility = DB::table('master_facility')->where('type', 'booth')->where('category', 'access')->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                $facility->children = json_decode($facility->children);
                $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
                echo "<cell>".htmlspecialchars($facility->children)."</cell>";
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailBoothSupportXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $_GET['Task'])->get();
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
        $masterFacility = DB::table('master_facility')->where('type', 'booth')->where('category', 'support')->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                $facility->children = json_decode($facility->children);
                $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
                echo "<cell>".htmlspecialchars($facility->children)."</cell>";
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0)."</cell>";
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
        $getOppt = DB::table('opportunity_partner')->where("Lead_ID", auth()->user()->Emp_ID)->first();
        if (!$getOppt) {
            abort(403, 'Unauthorized access');
        }
        $id = $getOppt->id;
        return view('partner.business-facility-detail-mobil', compact('id'));
    }

    public function indexDetailMobilInventoryXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $_GET['Task'])->get();
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
        $masterFacility = DB::table('master_facility')->where('type', 'mobil')->where('category', 'inventory')->get();
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
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailMobilAccessXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $_GET['Task'])->get();
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
        $masterFacility = DB::table('master_facility')->where('type', 'mobil')->where('category', 'access')->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                $facility->children = json_decode($facility->children);
                $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
                echo "<cell>".htmlspecialchars($facility->children)."</cell>";
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0)."</cell>";
                }else {
                    echo "<cell></cell>";
                    echo "<cell>0</cell>";
                }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailMobilSupportXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml")) {
            header("Content-type: application/xhtml+xml"); } else {
            header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");

        echo "<rows>";

        $getChecked = DB::table('opportunity_partner_facility')->where('Partner_ID', $_GET['Task'])->get();
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
        $masterFacility = DB::table('master_facility')->where('type', 'mobil')->where('category', 'support')->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
                echo "<cell>".($key + 1)."</cell>";
                echo "<cell>".htmlspecialchars($facility->description)."</cell>";
                $facility->children = json_decode($facility->children);
                $facility->children = is_array($facility->children) ? implode(", ", $facility->children) : $facility->children;
                echo "<cell>".htmlspecialchars($facility->children)."</cell>";
                if (isset($getCheckedByPartner[$facility->id])) {
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Date'])."</cell>";
                    echo "<cell>".htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0)."</cell>";
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

    }

    public function detailFacilityUpdatekan($rowId, $param)
    {
        $a = $param[$rowId."_c1"]; // origin
        $b = $param[$rowId."_c2"]; // destination
        $c = $param[$rowId."_c3"]; // destination name
        $d = $param[$rowId."_c4"] ?? ''; // transport
        $Saya = auth()->user()->username;

        if ((isset($param[$rowId."_c6"]) ? $d : $c) == 1) {
            $cekDB = DB::table('opportunity_partner_facility')
                    ->where('Facility_ID', $rowId)
                    ->where("Partner_ID", $_GET['Task'])
                    ->first();
            if ($cekDB) {
                DB::table('opportunity_partner_facility')
                    ->where('ID', $cekDB->ID)
                    ->update([
                        'Checked_Partner' => isset($param[$rowId."_c6"]) ? $d : $c,
                        'Checked_Partner_By' => $Saya,
                        'Checked_Partner_Date' => now(),
                        'Received_Date' => isset($param[$rowId."_c6"]) ? $c : $b,
                    ]);
            }else {
                DB::table('opportunity_partner_facility')
                    ->insert([
                        'Facility_ID' => $rowId,
                        'Partner_ID' => $_GET['Task'],
                        'Checked_Partner' => isset($param[$rowId."_c6"]) ? $d : $c,
                        'Checked_Partner_By' => $Saya,
                        'Checked_Partner_Date' => now(),
                        'Received_Date' => isset($param[$rowId."_c6"]) ? $c : $b,
                    ]);
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
