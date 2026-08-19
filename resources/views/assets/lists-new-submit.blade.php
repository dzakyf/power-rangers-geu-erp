<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location ?? 'GEU-HO';
$NIK = Auth::user()->Emp_ID ?? '';
$Saya = Auth::user()->username ?? 'admin';

try {
    $assetCategory = $_POST['Asset_Category'] ?? '';
    $assetSubCategory = $_POST['Asset_Sub_Category'] ?? '';
    $assetType = $_POST['Asset_Type'] ?? '';

    $prod1 = DB::table("master_product_group")
        ->where("Product_Group", $assetCategory)
        ->first();
    $prod2 = DB::table("master_product_sub_group")
        ->where("Product_Group", $assetCategory)
        ->where("Sub_Group", $assetSubCategory)
        ->first();
    $prod3 = DB::table("master_product_sub_sub_group")
        ->where("Product_Group", $assetCategory)
        ->where("Sub_Group", $assetSubCategory)
        ->where("Sub_Sub_Group", $assetType)
        ->first();

    $P1 = $prod1->Initial ?? 'FA';
    $P2 = $prod2->Initial ?? '01';
    $P3 = $prod3->Initial ?? '01';

    $headno = "01";
    $gab = "$P1-$P2-$P3-$headno-";

    $itemsToInsert = [];

    // Jika input berasal dari PO (bisa memiliki multiple unit items)
    if (isset($_POST['Incoming_Type']) && $_POST['Incoming_Type'] === 'PO' && !empty($_POST['PO_No'])) {
        $poNo = $_POST['PO_No'];
        $productCode = $_POST['PO_Item_Select'] ?? '';
        $receivedQtyCount = isset($_POST['items']) && is_array($_POST['items']) ? count($_POST['items']) : (int)($_POST['Item_Qty_Select'] ?? 1);
        $referenceNo = $_POST['Reference'] ?? '';
        $noteRemark = $_POST['Remark'] ?? ($_POST['Note'] ?? '');
        $incomingDate = date('Y-m-d');
        
        // 1. Generate GR_No (Contoh: GR/FA/LSP/date-counter atau AP_No jika dikirim)
        $apNo = $_POST['AP_No'] ?? ("GR/FA/" . $LSP . "/" . date('ymdHis'));

        // 2. Loop insert data asset_list
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $index => $itemData) {
                $countExisting = DB::table("asset_list")->where("Asset_No", "LIKE", "$gab%")->count();
                $nextNo = $countExisting + 1 + $index;
                $assetno = $gab . sprintf("%'04d", $nextNo);

                $dataInsert = [
                    'Asset_No' => $assetno,
                    'PO_No' => $poNo,
                    'Product_Code' => $productCode,
                    'Asset_Category' => $assetCategory,
                    'Asset_Sub_Category' => $assetSubCategory,
                    'Asset_Type' => $assetType,
                    'Location' => $LSP,
                    'Created_By' => $Saya,
                    'Created_Date' => now(),
                ];

                foreach ($itemData as $k => $v) {
                    if (!in_array($k, ['_token', 'Task_ID', 'Created_Date', 'Created_By', 'Location'])) {
                        $dataInsert[$k] = $v;
                    }
                }
				$dataInsert['Status'] = 'NEW';
                $itemsToInsert[] = $dataInsert;
            }
        }

        // 3. Update purchase_order_dtl (Qty_Receive & Qty_Remain)
        $poDtl = DB::table('purchase_order_dtl')->where('PO_No', $poNo);
        if ($productCode != '') {
            $poDtl->where('Product_Code', $productCode);
        }
        $targetDtl = $poDtl->first();

        if ($targetDtl) {
            $currentRec = (int)($targetDtl->Qty_Receive ?? 0);
            $totalOrdered = (int)($targetDtl->Qty ?? 0);
            $newRec = $currentRec + $receivedQtyCount;
            $newRemain = max(0, $totalOrdered - $newRec);

            DB::table('purchase_order_dtl')
                ->where('ID', $targetDtl->ID)
                ->update([
                    'GR_No' => $apNo,
                    'Qty_Receive' => $newRec,
                    'Qty_Remain' => $newRemain,
                ]);
        } else {
            DB::table('purchase_order_dtl')
                ->where('PO_No', $poNo)
                ->update(['GR_No' => $apNo]);
        }

        // 4. Hitung akumulasi penerimaan seluruh detail item pada PO ini untuk menentukan Status_Receive (PARTIAL / RECEIVED / CLOSED)
        $allDetails = DB::table('purchase_order_dtl')->where('PO_No', $poNo)->get();
        $totalPOQty = $allDetails->sum('Qty');
        $totalPORec = $allDetails->sum('Qty_Receive');

        $statusReceive = 'PARTIAL';
        if ($totalPORec >= $totalPOQty && $totalPOQty > 0) {
            $statusReceive = 'RECEIVED';
        } elseif ($totalPORec == 0) {
            $statusReceive = 'WAITING';
        }

        // 5. Update purchase_order (GR_No, GR_Date, Received_Date, Status_Receive)
        DB::table('purchase_order')
            ->where('PO_No', $poNo)
            ->update([
                'GR_No' => $apNo,
                'GR_Date' => now(),
                'Received_Date' => $incomingDate,
                'Status_Receive' => $statusReceive,
            ]);

        // 6. Insert ke tabel good_receive (sesuai template inventory-incoming-new-submit.blade.php)
        $poHeader = DB::table('purchase_order')->where('PO_No', $poNo)->first();
        if ($poHeader) {
            DB::table('good_receive')->insert([
                'GR_No' => $apNo,
                'PR_No' => $poHeader->PR_No ?? '',
                'PO_No' => $poNo,
                'PO_No_KSPS' => $poHeader->PO_No_KSPS ?? '',
                'Reference' => $referenceNo,
                'Remark' => $noteRemark,
                'Location' => $LSP,
                'Status' => 'CREATED',
                'Created_By' => $Saya,
                'Date_Created' => now(),
                'Purchase_Category' => $poHeader->Purchase_Category ?? '',
                'Purchase_Sub_Category' => $poHeader->Purchase_Sub_Category ?? '',
                'Purchase_Type' => $poHeader->Purchase_Type ?? '',
                'Incoming_Date' => $incomingDate,
            ]);
        }
    } else {
        // Input Manual (single item)
        $countExisting = DB::table("asset_list")->where("Asset_No", "LIKE", "$gab%")->count();
        $nextNo = $countExisting + 1;
        $assetno = $gab . sprintf("%'04d", $nextNo);

        $dataInsert = [
            'Asset_No' => $assetno,
            'Location' => $LSP,
            'Created_By' => $Saya,
            'Created_Date' => now(),
        ];

        foreach ($_POST as $key => $value) {
            if (!in_array($key, ['_token', 'Task_ID', 'Created_Date', 'Created_By', 'Location', 'Incoming_Type', 'PO_No', 'PO_Item_Select', 'Item_Qty_Select'])) {
                $dataInsert[$key] = $value;
            }
        }
		$dataInsert['Status'] = 'NEW';
        $itemsToInsert[] = $dataInsert;

        // Buat record Good Receive (GR) untuk Input Manual
        $grNoManual = $_POST['AP_No'] ?? ("GR/FA/" . $LSP . "/" . date('ymdHis'));
        $incomingDateManual = date('Y-m-d');
        $referenceManual = $_POST['Registration_No'] ?? ($_POST['Part_No'] ?? 'Manual');
        $noteManual = $_POST['Specification'] ?? ($_POST['Product_Description'] ?? 'Manual Input Asset');

        DB::table('good_receive')->insert([
            'GR_No' => $grNoManual,
            'PR_No' => 'Manual',
            'PO_No' => 'Manual',
            'PO_No_KSPS' => 'Manual',
            'Reference' => $referenceManual,
            'Remark' => $noteManual,
            'Location' => $LSP,
            'Status' => 'CREATED',
            'Created_By' => $Saya,
            'Date_Created' => now(),
            'Purchase_Category' => 'ASSET',
            'Purchase_Sub_Category' => $assetCategory,
            'Purchase_Type' => $assetType,
            'Incoming_Date' => $incomingDateManual,
        ]);
    }

    $success = true;
    foreach ($itemsToInsert as $singleAsset) {
        $res = DB::table('asset_list')->insert($singleAsset);
        if (!$res) {
            $success = false;
        }
    }

    if ($success) {
        echo "success";
    } else {
        echo "error";
    }
} catch (\Throwable $th) {
    Log::error($th);
    echo "error: " . $th->getMessage();
}
?>