<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\User;
use App\WarehousePartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class AssetManagementController extends Controller
{
    public function index()
    {
        return view('partner.asset-management');
    }

    public function indexXml()
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"] ?? "","application/xhtml+xml")) {
            if (!headers_sent()) header("Content-type: application/xhtml+xml"); } else {
            if (!headers_sent()) header("Content-type: text/xml");
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
            echo "<cell>" . htmlspecialchars($usname[$Fetch->partner_task_id] ?? $Fetch->partner_task_id) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->capacity) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_type == '' ? 'booth' : $Fetch->warehouse_type) . "</cell>";
            echo "<cell>" . htmlspecialchars($Fetch->warehouse_status) . "</cell>";
            echo "<cell>" . htmlspecialchars($usname[$Fetch->Created_By]) . "</cell>";
            echo "</row>";

            $nomor++;
        }
        echo "</rows>";
    }

    public function indexDetail(Request $request)
    {
        $task = $request->ID;
        $wh = WarehousePartner::where("warehouse_task_id", $task)->first();
        $partner = DB::table('opportunity_partner')->where("lead_id", $wh->partner_task_id)->first();
        if (!$partner) {
            $partner = new stdClass;
            $partner->id = 0;
        }
        $type = ($wh->warehouse_type === 'mobil') ? 'mobil' : 'booth';
        return view('partner.asset-management-detail', compact('wh', 'partner', 'type'));
    }

    private function resolveWarehouseContext(Request $request): array
    {
        $task = $request->Task;
        $wh = WarehousePartner::where("warehouse_task_id", $task)->first();
        $partner = DB::table('opportunity_partner')->where("lead_id", $wh->partner_task_id)->first();
        if (!$partner) {
            $partner = new stdClass;
            $partner->id = 0;
        }

        $getChecked = DB::table('opportunity_partner_facility')
            ->where('warehouse_task_id', $task)
            ->where('Partner_ID', $partner->id)
            ->get();
        $getCheckedBySales = [];
        $getCheckedByPartner = [];
        foreach ($getChecked as $value) {
            $getCheckedBySales[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Sales,
                'Date' => $value->Send_Date,
            ];
            $getCheckedByPartner[$value->Facility_ID] = [
                'Chckd' => $value->Checked_Partner,
                'Date' => $value->Received_Date,
            ];
        }

        return [$partner, $getCheckedBySales, $getCheckedByPartner];
    }

    private function facilityChildrenText($facility): string
    {
        $children = json_decode($facility->children);
        if (is_object($children)) {
            $child = "";
            foreach ($children as $k => $v) {
                $child .= "$k : " . (is_array($v) ? implode(", ", $v) : json_encode($v)) . ", ";
            }
            return substr($child, 0, -2);
        }
        return is_array($children) ? implode(", ", $children) : (string) $children;
    }

    private function xmlHeader(): void
    {
        if ( stristr($_SERVER["HTTP_ACCEPT"] ?? "","application/xhtml+xml")) {
            if (!headers_sent()) header("Content-type: application/xhtml+xml"); } else {
            if (!headers_sent()) header("Content-type: text/xml");
        }
        echo("<?xml version='1.0' encoding='iso-8859-1'?>\n");
    }

    /**
     * Unified inventory grid cells (asset-management-detail.blade.php):
     *   c0 No, c1 Deskripsi, c2 Detail, c3 Tipe Asset, c4 Qty, c5 Price,
     *   c6 Total, c7 Ceklist Ketersediaan, c8 Tanggal Order,
     *   c9 Tanggal Diterima, c10 Status Diterima
     */
    public function indexDetailInventoryXml(Request $request, string $type)
    {
        $this->xmlHeader();
        echo "<rows>";

        [$partner, $getCheckedBySales, $getCheckedByPartner] = $this->resolveWarehouseContext($request);
        $task = $request->Task;

        $masterFacility = DB::table('master_facility')
            ->where('type', $type)
            ->where('category', 'inventory')
            ->where('is_deleted', 0)
            ->get();
        foreach ($masterFacility as $key => $facility) {
            // Warehouse-scoped so multi-warehouse partners read the row they
            // will write to.
            $cekDtl = DB::table('opportunity_partner_facility_dtl')
                ->where("Facility_ID", $facility->id)
                ->where('Partner_ID', $partner->id)
                ->where('warehouse_task_id', $task)
                ->orderBy('ID')
                ->first();
            echo "<row id=\"$facility->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($facility->description) . "</cell>";
            echo "<cell>" . htmlspecialchars($this->facilityChildrenText($facility)) . "</cell>";
            echo "<cell>" . htmlspecialchars($facility->asset_type) . "</cell>";
            // Amounts mirror the pre-existing order-equipment submission
            // (opportunity_partner_facility_dtl) only - master_facility.price
            // is never used here and unsubmitted rows stay blank.
            if ($cekDtl) {
                echo "<cell>" . htmlspecialchars($cekDtl->Qty) . "</cell>";
                echo "<cell>" . htmlspecialchars($cekDtl->Price) . "</cell>";
                echo "<cell>" . htmlspecialchars($cekDtl->Total) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell></cell>";
                echo "<cell></cell>";
            }
            echo "<cell>" . htmlspecialchars($getCheckedBySales[$facility->id]['Chckd'] ?? 0) . "</cell>";
            $orderDate = $cekDtl->Order_Date ?? '';
            echo "<cell>" . htmlspecialchars(($orderDate == '0000-00-00') ? '' : $orderDate) . "</cell>";
            if (isset($getCheckedByPartner[$facility->id])) {
                $partnerDate = $getCheckedByPartner[$facility->id]['Date'];
                echo "<cell>" . htmlspecialchars(($partnerDate == '0000-00-00') ? '' : $partnerDate) . "</cell>";
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell>0</cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailBoothInventoryXml(Request $request)
    {
        return $this->indexDetailInventoryXml($request, 'booth');
    }

    public function indexDetailMobilInventoryXml(Request $request)
    {
        return $this->indexDetailInventoryXml($request, 'mobil');
    }

    /**
     * Unified access/support grid cells:
     *   c0 No, c1 Deskripsi, c2 Detail, c3 Ceklist Ketersediaan,
     *   c4 Tanggal Diterima, c5 Status Diterima
     */
    public function indexDetailCategoryXml(Request $request, string $type, string $category)
    {
        $this->xmlHeader();
        echo "<rows>";

        [$partner, $getCheckedBySales, $getCheckedByPartner] = $this->resolveWarehouseContext($request);

        $masterFacility = DB::table('master_facility')
            ->where('type', $type)
            ->where('category', $category)
            ->where('is_deleted', 0)
            ->get();
        foreach ($masterFacility as $key => $facility) {
            echo "<row id=\"$facility->id\" >";
            echo "<cell>" . ($key + 1) . "</cell>";
            echo "<cell>" . htmlspecialchars($facility->description) . "</cell>";
            echo "<cell>" . htmlspecialchars($this->facilityChildrenText($facility)) . "</cell>";
            echo "<cell>" . htmlspecialchars($getCheckedBySales[$facility->id]['Chckd'] ?? 0) . "</cell>";
            if (isset($getCheckedByPartner[$facility->id])) {
                $partnerDate = $getCheckedByPartner[$facility->id]['Date'];
                echo "<cell>" . htmlspecialchars(($partnerDate == '0000-00-00') ? '' : $partnerDate) . "</cell>";
                echo "<cell>" . htmlspecialchars($getCheckedByPartner[$facility->id]['Chckd'] ?? 0) . "</cell>";
            } else {
                echo "<cell></cell>";
                echo "<cell>0</cell>";
            }
            echo "</row>";
        }

        echo "</rows>";
    }

    public function indexDetailBoothAccessXml(Request $request)
    {
        return $this->indexDetailCategoryXml($request, 'booth', 'access');
    }

    public function indexDetailBoothSupportXml(Request $request)
    {
        return $this->indexDetailCategoryXml($request, 'booth', 'support');
    }

    public function indexDetailMobilAccessXml(Request $request)
    {
        return $this->indexDetailCategoryXml($request, 'mobil', 'access');
    }

    public function indexDetailMobilSupportXml(Request $request)
    {
        return $this->indexDetailCategoryXml($request, 'mobil', 'support');
    }

    public function detailFacilityUpdate()
    {
        if (!headers_sent()) header("Content-type: text/xml");
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

        $opportunityId = $_GET['Task'] ?? request()->query('Task');
        if ($opportunityId) {
            app(\App\Http\Controllers\ApprovalController::class)
                ->completeSetupIfReady($opportunityId);
        }

    }

    public function detailFacilityUpdatekan($rowId, $param)
    {
        $Saya = auth()->user()->username;
        $isPartner = auth()->user()->Priv_ID === 'MITRA';
        $partnerId = $_GET['Task'] ?? request()->query('Task');
        $whTaskId = $_GET['wh'] ?? request()->query('wh');

        // Fixed unified layouts (see asset-management-detail.blade.php):
        //   inventory (c6 present): c4=Qty, c5=Price, c6=Total,
        //     c7=Ceklist Ketersediaan, c8=Tanggal Order, c9=Tanggal Diterima,
        //     c10=Status Diterima
        //   access/support (no c6): c3=Ceklist Ketersediaan,
        //     c4=Tanggal Diterima, c5=Status Diterima
        if (isset($param[$rowId."_c6"])) {
            $qty = $param[$rowId."_c4"] ?? '';
            $price = $param[$rowId."_c5"] ?? '';
            $total = $param[$rowId."_c6"] ?? '';
            $checkedSales = $param[$rowId."_c7"] ?? '';
            $orderDateIn = $param[$rowId."_c8"] ?? '';
            $receivedDateIn = $param[$rowId."_c9"] ?? '';
            $checkPartner = $param[$rowId."_c10"] ?? '';
        } else {
            $qty = null;
            $price = null;
            $total = null;
            $orderDateIn = null;
            $checkedSales = $param[$rowId."_c3"] ?? '';
            $receivedDateIn = $param[$rowId."_c4"] ?? '';
            $checkPartner = $param[$rowId."_c5"] ?? '';
        }

        // Availability checklist is internal-only; receipt confirmation is
        // partner-only - role gating keeps one side from re-stamping the
        // other side's audit columns.
        if (!$isPartner && $checkedSales != '') {
            $cek = DB::table('opportunity_partner_facility')
                ->where("Facility_ID", $rowId)
                ->where('Partner_ID', $partnerId)
                ->where('warehouse_task_id', $whTaskId)
                ->first();
            if ($cek) {
                DB::table('opportunity_partner_facility')
                    ->where('ID', $cek->ID)
                    ->update([
                        'Checked_Sales' => $checkedSales,
                        'Checked_Sales_By' => $Saya,
                        'Checked_Sales_Date' => now(),
                        'Send_Date' => now(),
                    ]);
            }else {
                DB::table('opportunity_partner_facility')
                    ->insert([
                        'Facility_ID' => $rowId,
                        'warehouse_task_id' => $whTaskId,
                        'Partner_ID' => $partnerId,
                        'Checked_Sales' => $checkedSales,
                        'Checked_Sales_By' => $Saya,
                        'Checked_Sales_Date' => now(),
                        'Send_Date' => now(),
                        'Created_By' => $Saya,
                        'Created_Date' => now(),
                    ]);
            }
        }

        // Receipt confirmation (Status Diterima) can be ticked/unticked by
        // both internal users and the partner. Only act when the posted value
        // differs from the stored one, so unrelated edits (qty/price/
        // availability) never re-stamp the receipt audit columns.
        if ($checkPartner != '') {
            $currentFacility = DB::table('opportunity_partner_facility')
                ->where("Facility_ID", $rowId)
                ->where('Partner_ID', $partnerId)
                ->where("warehouse_task_id", $whTaskId)
                ->first();
            $currentChecked = $currentFacility ? (int) $currentFacility->Checked_Partner : 0;

            if ((int) $checkPartner !== $currentChecked) {
                if ((int) $checkPartner === 1) {
                    // Ticking without a picked date falls back to today
                    // (mirrors the sales side storing Send_Date => now()).
                    $receivedDate = $receivedDateIn ?: now()->format('Y-m-d');
                    if ($currentFacility) {
                        DB::table('opportunity_partner_facility')
                            ->where('ID', $currentFacility->ID)
                            ->update([
                                'Checked_Partner' => 1,
                                'Checked_Partner_By' => $Saya,
                                'Checked_Partner_Date' => now(),
                                'Received_Date' => $receivedDate,
                            ]);
                    }else {
                        DB::table('opportunity_partner_facility')
                            ->insert([
                                'Facility_ID' => $rowId,
                                'warehouse_task_id' => $whTaskId,
                                'Partner_ID' => $partnerId,
                                'Checked_Partner' => 1,
                                'Checked_Partner_By' => $Saya,
                                'Checked_Partner_Date' => now(),
                                'Received_Date' => $receivedDate,
                                'Created_By' => $Saya,
                                'Created_Date' => now(),
                            ]);
                    }
                } else {
                    // Unticking keeps the last Received_Date on file
                    if ($currentFacility) {
                        DB::table('opportunity_partner_facility')
                            ->where('ID', $currentFacility->ID)
                            ->update([
                                'Checked_Partner' => 0,
                                'Checked_Partner_By' => $Saya,
                                'Checked_Partner_Date' => now(),
                            ]);
                    }
                }
            }
        }

        if ($qty !== null && (trim((string) $qty) !== '' || trim((string) $price) !== '' || trim((string) $total) !== '')) {
            // The order date falls back to today when left blank (a blank
            // date used to silently discard the amounts).
            $orderDate = $orderDateIn ?: now()->format('Y-m-d');
            $cekDtl = DB::table('opportunity_partner_facility_dtl')
                ->where("Facility_ID", $rowId)
                ->where('Partner_ID', $partnerId)
                ->where('warehouse_task_id', $whTaskId)
                ->first();
            if ($cekDtl) {
                DB::table('opportunity_partner_facility_dtl')
                    ->where('ID', $cekDtl->ID)
                    ->update([
                        'Qty' => $qty,
                        'Price' => $price,
                        'Total' => $total,
                        'Order_Date' => $orderDate,
                    ]);
            }else {
                DB::table('opportunity_partner_facility_dtl')
                    ->insert([
                        'Facility_ID' => $rowId,
                        'Partner_ID' => $partnerId,
                        'warehouse_task_id' => $whTaskId,
                        'Order_Date' => $orderDate,
                        'Qty' => $qty,
                        'Price' => $price,
                        'Total' => $total,
                        'Created_By' => $Saya,
                        'Created_Date' => now(),
                    ]);
            }
        }

        return 'update';
    }

    // These grids never emit inserted/deleted transactions; stubs keep the
    // dispatcher from fataling if a client ever sends one.
    public function detailFacilityDeletekan($rowId, $param)
    {
        return 'update';
    }

    public function detailFacilityInsertkan($rowId, $param)
    {
        return 'update';
    }
}
