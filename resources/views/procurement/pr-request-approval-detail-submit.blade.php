	<?php
	error_reporting(0);

	use Illuminate\Http\Request;
	use Illuminate\Http\Custom_Function;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Database\Eloquent\Builder;
	use Illuminate\Support\Facades\Auth;
	use App\Services\NotificationService;

	$ars = "";
	$ax = "";
	$isi = "";
	foreach ($_POST as $key => $value) {

		if ($key != 'DelPRNewDetail' && $key != 'SubmitPRNewDetail' && $key != 'AddPRNewDetail') {
			$ars .= "$key = '$value',";
			$ax .= "$key,";
			$isi .= "'$value',";
		}
	}
	$Saya = Auth::User()->username;
	$LSP = Auth::user()->Location;
	$dept = Auth::user()->Department;
	$cek = DB::table("purchase_request")->where("ID", $_POST['ID'])->first();
	$dept = DB::table("master_product_sub_sub_group")->where("Product_Group", $cek->Category)->where("Sub_Group", $cek->Sub_Category)->where("Sub_Sub_Group", $cek->Supplies_Type)->first();


	$ars = substr($ars, 0, -1);
	$ax = substr($ax, 0, -1);
	$isi = substr($isi, 0, -1);
	$nik = "";
	switch ($_POST['Status']) {
		case "CREATED":
			$nstatus = "PROPOSED";
			break;
		case "APPROVAL_UPPER":
			$nstatus = 'APPROVAL_GA';

			$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", 'HCGA')->first();
			$nik = $getupper->NIK;

			break;
		case "APPROVAL_FINANCE":

			$nstatus = "APPROVED";

			break;
		case "APPROVAL_GA":

			$nstatus = "APPROVAL_FINANCE";

			$getupper = DB::table("organization_structure")->where("Level3", 1)->where("Department", 'FIN')->first();
			$nik = $getupper->NIK;

			break;
		default:
			$nstatus = 'CREATED';
			break;
	}

	$data = [
		'Note' => $_POST['Note'],
		'Reference' => $_POST['Reference'],
		'Status' => $nstatus,
	];

	$pr = DB::table("purchase_request")->where("ID",$_POST['ID'])->first();
	if ($nstatus == 'APPROVAL_FINANCE') {
		$PO_No = createPurchaseSummary($pr);
		$PONO = createPurchaseOrder($pr, $PO_No);
	}
	
	DB::table('purchase_request')->where("ID", $_POST['ID'])->update($data);
	
	if ($nik != '') {
		$us = DB::table('users')->where("Emp_ID", $getupper->NIK)->select("id")->first();
		(new NotificationService)->makeNotification("PR APPROVAL", "PR {$_POST['PR_No']} Need to be Approved", 'approval', Auth::user()->id ?? null, $us->id ?? null);
	}

	DB::insert(DB::raw("INSERT INTO purchase_log (Location, Task_ID, Category, Sub_Category, Purchase_Type, Transaction, Status, Remark, Date_Trx, User_Trx) VALUES ('$LSP', '$_POST[PR_No]', '$_POST[Category]', '$_POST[Sub_Category]', '$_POST[Supplies_Type]', 'PURCHASE-REQUEST', '$nstatus', '$_POST[Note]', NOW(), '$Saya')"));

	$subjek = "IMPORTANT : PURCHASE REQUEST APPROVAL REQUEST PR NO $_POST[PR_No] # , Request By : $Name";
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$getupper->Name</b><Br><br>
		
					Mohon dilakukan approval untuk Purchase Request tersebut. persetujuan dapat dilakukan melalui system ERP pada menu Procurement -> Request ->Approval<br><br>
					
					No PR : $_POST[PR_No]<br><br>
					Reference : $_POST[Reference]<br><br>
					Jenis Request : $_POST[Category] // $_POST[Sub_Category] // $_POST[Supplies_Type]<br><br>
					Note : $_POST[Note]<br>
					
					<br><br>
					Anda dapat melakukan approval melalui link berikut ini :<a href=\'https://erp.ksps.co.id/erpksps/public/approvalpr?ID=$_POST[PR_No]&linkkey=$linkkey&saya=$Saya\' >KLIK DISINI</a><br><br>
					
					Terima kasih<br>
					PT GREEN ENERGI UTAMA
					
		</div>";
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$getupper->Email','$subjek','$badan','UNSENT',NOW(),NOW())"));


	echo "success";

	function createPurchaseSummary($pr)
	{
		$date = date('Y-m-d');
		$LSP = Auth::user()->Location;
		$Saya = Auth::user()->username;
		$ps = DB::select(DB::raw("SELECT COUNT(ID) as jumlah FROM purchase_summary WHERE Location = '$LSP' and Purchase_Date LIKE '$date%'"));
			
		$NoR = $ps->jumlah + 1;
		if ( $NoR < 10 ) $NoR = "0$NoR";
		$dateR = date('YmdHis');
		$PO_No = "PS/".$LSP."/$dateR-$NoR";

		DB::table('purcahse_summary')->insert([
			'Location' => $LSP ?? '',
			'PR_No' => $pr->PR_No ?? '',
			'Task_ID' => $PO_No ?? '',
			'Purchase_Category' => $pr->Category ?? '',
			'Purchase_Sub_Category' => $pr->Sub_Category ?? '',
			'Purchase_Type' => $pr->Supplies_Type ?? '',
			'Purchase_Date' => now(),
			'V1_ID' => $pr->Supplier_ID ?? '',
			'V1_Cur' => 'RP',
			'V1_Discount' => 0,
			'V1_Fee_Mgt' => 0,
			'V1_VAT' => 0,
			'V1_DC' => 0,
			'V2_ID' => '',
			'V3_ID' => '',
			'V4_ID' => '',
			'V5_ID' => '',
			'Created_By' => $Saya,
			'Note' => $pr->Remark ?? '',
			'Priority' => $pr->Priority_Status ?? '',
		]);

		$prds = DB::table('purchase_request_dtl')->where("PR_No", $pr->PR_No)->get();
		foreach ($prds as $key => $value) {
			DB::table('purchase_summary_detail')->insert([
				'PR_Related' => $value->PR_No,
				'Task_ID' => $PO_No,
				'Purchase_Category' => $value->Purchase_Category,
				'Purchase_Sub_Category' => $value->Purchase_Type,
				'Purchase_Type' => $value->Category,
				'Product_Code' => $value->Product_Code,
				'Qty' => $value->Qty,
				'Product_Desc' => $value->Product_Desc,
				'Specification' => $value->Specification,
				'Recommended_Spec' => $value->Specification,
				'Created_By' => $Saya,
				'Location' => $LSP,
				'Proposed' => $value->Qty,
				'Unit_Name' => $value->Unit,
				'Default_Price' => $value->Default_Price,
				'V1_Propose' => $value->Default_Price,
				'V1_TOP' => $value->Default_Price * $value->Qty,
				'V1_Win' => 1,
			]);

			DB::table('purchase_request_dtl')->where("ID", $value->ID)->update([
				'PO_Task_No' => $PO_No,
				'PO_Task_Date' => now(),
			]);
		}

		DB::table('purchase_request')->where('ID', $pr->ID)->update([
			'PO_Task_No' => $PO_No,
			'Processing_Status' => 'CREATED TO PO',
			'Processing_Date' => now(),
		]);

		DB::table('purchase_log')->insert([
			'Location' => $LSP,
			'Task_ID' => $pr->PR_No,
			'Category' => $pr->Category,
			'Sub_Category' => $pr->Sub_Category,
			'Purchase_Type' => $pr->Supplies_Type,
			'Transaction' => 'PURCHASE-REQUEST-TO-PO',
			'Status' => 'CREATE TO PO',
			'Remark' => $pr->Note,
			'Date_Trx' => now(),
			'User_Trx' => $Saya,
		]);

		DB::table('purchase_log')->insert([
			'Location' => $LSP,
			'Task_ID' => $pr->PR_No,
			'Category' => $pr->Category,
			'Sub_Category' => $pr->Sub_Category,
			'Purchase_Type' => $pr->Supplies_Type,
			'Transaction' => 'PURCHASE-ORDER',
			'Status' => 'NEW PO PROCESS',
			'Remark' => $pr->Note,
			'Date_Trx' => now(),
			'User_Trx' => $Saya,
		]);

		return $PO_No;
	}

	function createPurchaseOrder($pr, $ps)
	{
		$LSP = Auth::user()->Location;
		$Saya = Auth::user()->username;
		$date = date('Y');
		$gg = DB::table('purchase_summary')->where('PR_No', $pr->PR_No)->where('Task_ID', $ps)->first();
		$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "$date%")->get();
		$CQuery = count($query);
		$NoR = $CQuery + 1;
		if ($NoR < 10) $NoR = "0$NoR";
		$dateR = date('Ymd');
		$totalx = 0;
		$subtotalx = 0;
		$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
		$sels = DB::table('purchase_summary_detail')->where('PR_Related', $pr->PR_No)->where('V1_Win', 1)->where('Task_ID', $ps)->get();
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V1_TOP;
				$subtotalx += $task->V1_TOP;
				DB::table('purchase_order_dtl')->insert([
					'Location' => $LSP,
					'PO_No' => $PONO,
					'Category' => $task->Purchase_Category,
					'Sub_Category' => $task->Purchase_Sub_Category,
					'Purchase_Type' => $task->Purchase_Type,
					'Product_Code' => $task->Product_Code,
					'Barcode' => $task->Product_Code,
					'Product_Desc' => $task->Product_Desc,
					'Product_Type' => $task->Product_Code,
					'Qty' => $task->Qty,
					'Unit' => $task->Unit_Name,
					'Price' => $task->V1_Propose,
					'Subtotal' => $task->V1_TOP,
					'PPN' => 0,
					'Total' => $task->V1_TOP,
					'Remark' => addslashes($task->V1_Note),
					'PR_No' => $task->PR_Related,
					'Currency' => 'RP',
				]);
			}

			DB::table('purchase_request')->where("PR_No", $task->PR_Related)->update([
				'PO_No' => $PONO,
				'Processing_Status' => 'PO APPROVED',
				'Processing_Date' => now(),
			]);
			if ($gg->V1_Discount != 0) {
				$totalx -= $gg->V1_Discount;
			}

			if ($gg->V1_Fee_Mgt != 0) {
				if ($gg->V1_Fee_VAT != 0) {
					$totalx += $gg->V1_Fee_Mgt;
					$ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);
					$totalx += $ppn1;
				} else {
					$ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);
					$totalx += $ppn1;
					$totalx += $gg->V1_Fee_Mgt;
				}
			} else {
				$ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);;
				$totalx += $ppn1;
			}
			if ($gg->V1_DC != 0) {
				$totalx += $gg->V1_DC;
			}

			DB::table('purchase_order')->insert([
				'Location' => $LSP,
				'Process_By' => $Saya,
				'Purchase_Type' => $gg->Purchase_Type,
				'Purchase_Category' => $gg->Purchase_Category,
				'Purchase_Sub_Category' => $gg->Purchase_Sub_Category,
				'PO_No' => $PONO,
				'PO_Date' => now(),
				'PO_Method' => 'POSTPAID',
				'Supplier' => $gg->V1_ID,
				'Subtotal' => $subtotalx,
				'PPN' => $ppn1,
				'Total' => $totalx,
				'Status' => 'CLOSED',
				'Remark' => $gg->V1_Note,
				'Date_Trx' => now(),
				'User_Trx' => $Saya,
				'Task_ID' => $gg->Task_ID,
				'Delivery_Fee' => $gg->V1_DC,
				'Management_Fee' => $gg->V1_Fee_Mgt,
				'PPH' => $gg->V1_PPH,
				'Management_Fee_Exclude_PPN' => $gg->V1_Fee_VAT,
				'Discount' => $gg->V1_Discount,
				'PR_No' => $pr->PR_No,
				'Currency' => $gg->V1_Cur,
			]);
		}

		return $PONO;
	}

	?>