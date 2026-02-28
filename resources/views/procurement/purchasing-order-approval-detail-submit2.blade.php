<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$src = $_POST['data'];
$LSP = Auth::user()->Location;
$saya =  Auth::user()->username;
$Nama =  Auth::user()->Name;

function CekVendorWin($ven, $taskid)
{
	$vendor = "V" . $ven . "_Win";
	$TaskID = $_POST['TaskID'];
	$cekV  = DB::table('purchase_summary_detail')->selectRaw("count(ID) as jumlah")
		->where('PR_Related', '=', "$taskid")
		->where('Task_ID', '=', "$TaskID")
		->where("$vendor", 1)->get();
	foreach ($cekV as $key) {
		$jum = $key->jumlah;
	}
	$rowV = $jum;
	if ($rowV >= 1) {
		$jumlah = 1;
	} else {
		$jumlah = 0;
	}
	return $jumlah;
}


$Note = $_POST['Note'];
$PO_No = $_POST['PO_No'];
$TaskID = $_POST['TaskID'];

$taskid = $_POST['ID'];
if ($_POST['kurs1'] <> '')	$kurs1 = $_POST['kurs1'];
else $kurs1 = "Rp";
if ($_POST['kurs2'] <> '')	$kurs2 = $_POST['kurs2'];
else $kurs2 = "Rp";
if ($_POST['kurs3'] <> '')	$kurs3 = $_POST['kurs3'];
else $kurs3 = "Rp";
if ($_POST['kurs4'] <> '')	$kurs4 = $_POST['kurs4'];
else $kurs4 = "Rp";
if ($_POST['kurs5'] <> '')	$kurs5 = $_POST['kurs5'];
else $kurs5 = "Rp";
$ars = "";
$ax = "";
$isi = "";
foreach ($_POST as $key => $value) {

	if ($key != 'TaskID' && $key != 'Note' && $key != 'undefined') {
		if ($key == 'kurs1') $key = "V1_Cur";
		if ($key == 'kurs2') $key = "V2_Cur";
		if ($key == 'kurs3') $key = "V3_Cur";
		if ($key == 'kurs4') $key = "V4_Cur";
		if ($key == 'kurs5') $key = "V5_Cur";
		if ($value == 'on') $value = '1';
		if ($value == 'off') $value = '0';

		$ars .= "$key = '$value',";
		$ax .= "$key,";
		$isi .= "'$value',";
	}
}
$cekx = DB::table("purchase_summary")->where("Task_ID", $_POST['TaskID'])->first();
$totalnya = $cekx->V1_Total + $cekx->V2_Total + $cekx->V3_Total + $cekx->V4_Total + $cekx->V5_Total;
$status = $cekx->Status;


$subjek = "IMPORTANT : PURCHASE ORDER APPROVAL SUMMARY NO $_POST[TaskID] # , Priority : $cekx->Priority :: Request By : $cekx->Created_By";



if ($status == 'UPDATED') {
	$cekmail = DB::table("purchase_mail")->where("Status", "PROPOSED")->first();
	$nstatus = 'PROPOSED';
} elseif ($status == 'PROPOSED') {
	$cekmail = DB::table("purchase_mail")->where("Status", "VERIFIED")->first();
	$nstatus = 'VERIFIED';
} elseif ($status == 'VERIFIED') {
	if ($totalnya <= 25000000) {
		$nstatus = 'CLOSED';
	} else {
		$nstatus = 'DIRECTOR_APPROVAL';
		$cekmail = DB::table("purchase_mail")->where("Status", "DIRECTOR_APPROVAL")->first();
	}
} elseif ($status == 'DIRECTOR_APPROVAL' || $status == 'VPAPPROVE') {
	$nstatus = 'CLOSED';
}

DB::insert(DB::raw("INSERT INTO purchase_log(Location,Task_ID,Category,Transaction,Status,User_Trx,Remark)values('$LSP','$_POST[TaskID]','PURCHASE ORDER','PO Approval','$nstatus','$saya','$_POST[Note]')"));



if ($cekmail) {
	$linkkey = md5("YMhpod" . date("mdY") . "000019393");
	$badan = "<div style=\"font-family:arial;font-size:12px;\">Kepada Yth.<br><b>$cekmail->Name</b><Br><br>
		
					Mohon dilakukan approval untuk Purchase Request tersebut. persetujuan dapat dilakukan melalui system ERP pada menu Procurement -> Purchase Orders ->Approval<br><br>
					
					No PS : $_POST[TaskID]<br><br>
					Reference PR : $cekx->PR_No<br><br>
					Jenis Request : $cekx->Purchase_Category // $cekx->Purchase_Sub_Category // $cekx->Purchase_Type<br><br>
					Date : $cekx->Purchase_Date<br>
					Note : $cekx->Note<br>
					
					<br><br>
					Anda dapat melakukan approval melalui link berikut ini :<a href=\"https://erp.ksps.co.id/erpksps/public/approvalpo?ID=$_POST[TaskID]&linkkey=$linkkey&saya=$cekmail->NIK\" >KLIK DISINI</a><br><br>
					Terima kasih<br>
					PT GREEN ENERGI UTAMA
					
		</div>";
	DB::insert(DB::raw("INSERT INTO mail_logs(Email_To,Subject,Contents,Status,Inserted_Date,Send_Date) VALUES('$cekmail->Email_To','$subjek','$badan','UNSENT',NOW(),NOW())"));
}


if ($nstatus == 'CLOSED') {
	$skr = date("Y");
	$JUMX = DB::table("purchase_order")->where("PO_Date", "LIKE", "$skr%")->get();
	$scjum = count($JUMX);

	$date = date('Y-m-d');
	$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "$date%")->get();
	$CQuery = count($query);
	$NoR = $CQuery[0] + 1;
	if ($NoR < 10) $NoR = "0$NoR";
	$dateR = date('Ymd');
	$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
	$totalx = 0;
	$subtotalx = 0;
	$taskid = $cekx->PR_No;

	$qr = DB::table("purchase_summary")->where("PR_No", $taskid)->get();;

	foreach ($qr as $gg) {
		$sup1 = $gg->V1_ID;
		$sup2 = $gg->V2_ID;
		$sup3 = $gg->V3_ID;
		$sup4 = $gg->V4_ID;
		$sup5 = $gg->V5_ID;


		$sels = DB::table("purchase_summary_detail")->where("PR_Related", $taskid)->where("V1_Win", 1)->get();
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V1_TOP;
				$subtotalx += $task->V1_TOP;
				DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
			Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No)
			VALUES('$lsp','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
			'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
			'$task->Proposed','$task->Unit_Name','$task->V1_Propose','$task->V1_TOP',0,'$task->V1_TOP','" . addslashes($task->V1_Note) . "','$task->PR_Related')"));
				DB::update(DB::raw("UPDATE purchase_request set PO_No='$PONO',Processing_Status='PO APPROVED',Processing_Date=NOW() WHERE PR_No='$task->PR_Related'"));
			}
			if ($gg->V1_Discount != 0) {
				$totalx -= $gg->V1_Discount;
			}

			if ($gg->V1_Fee_Mgt != 0) {
				if ($gg->V1_Fee_VAT != 0) {
					$totalx += $gg->V1_Fee_Mgt;
					$ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);
					$totalx += $ppn1;
				} else {
					$ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);;
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
			$scjum += 1;
			$poksps = sprintf("%'.03d", $scjum) . "/PO/KSPS/$skr";
			DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
				PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,PO_No_KSPS) 
				SELECT '$lsp','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V1_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V1_Note,
				NOW(),'$saya',Task_ID,V1_DC,V1_Fee_Mgt,V1_PPH,V1_Fee_VAT,V1_Discount,'$taskid','$poksps' FROM purchase_summary where PR_No='$taskid'"));
		}
	}

	if ($sup2 != '') {
		$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "'$date%'")->get();
		$CQuery = count($query);
		$NoR = $CQuery[0] + 1;
		if ($NoR < 10) $NoR = "0$NoR";
		$dateR = date('Ymd');
		$totalx = 0;
		$subtotalx = 0;
		$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
		$sels = DB::table("purchase_summary_detail")->where("PR_Related", $taskid)->where("V2_Win", 1)->get();
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V2_TOP;
				$subtotalx += $task->V2_TOP;
				DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
			Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No)
			VALUES('$lsp','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
			'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
			'$task->Proposed','$task->Unit_Name','$task->V2_Propose','$task->V2_TOP',0,'$task->V2_TOP','" . addslashes($task->V2_Note) . "','$task->PR_Related')"));
				DB::update(DB::raw("UPDATE purchase_request set PO_No='$PONO',Processing_Status='PO APPROVED',Processing_Date=NOW() WHERE PR_No='$task->PR_Related'"));
			}
			if ($gg->V2_Discount != 0) {
				$totalx -= $gg->V2_Discount;
			}

			if ($gg->V2_Fee_Mgt != 0) {
				if ($gg->V2_Fee_VAT != 0) {
					$totalx += $gg->V2_Fee_Mgt;
					$ppn1 = round(($totalx * $gg->V2_VAT) / 100, 0);
					$totalx += $ppn1;
				} else {
					$ppn1 = round(($totalx * $gg->V2_VAT) / 100, 0);
					$totalx += $ppn1;
					$totalx += $gg->V2_Fee_Mgt;
				}
			} else {
				$ppn1 = round(($totalx * $gg->V2_VAT) / 100, 0);;
				$totalx += $ppn1;
			}
			if ($gg->V2_DC != 0) {
				$totalx += $gg->V2_DC;
			}
			$scjum += 1;
			$poksps = sprintf("%'.03d", $scjum) . "/PO/KSPS/$skr";

			DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
				PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,PO_No_KSPS) 
				SELECT '$lsp','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V2_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V2_Note,
				NOW(),'$saya',Task_ID,V2_DC,V2_Fee_Mgt,V2_PPH,V2_Fee_VAT,V2_Discount,'$taskid','$poksps' FROM purchase_summary where PR_No='$taskid'"));
		}
	}

	if ($sup3 != '') {
		$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "'$date%'")->get();
		$CQuery = count($query);
		$NoR = $CQuery[0] + 1;
		if ($NoR < 10) $NoR = "0$NoR";
		$dateR = date('Ymd');
		$totalx = 0;
		$subtotalx = 0;
		$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
		$sels = DB::table("purchase_summary_detail")->where("PR_Related", $taskid)->where("V3_Win", 1)->get();
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V3_TOP;
				$subtotalx += $task->V3_TOP;
				DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
			Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No)
			VALUES('$lsp','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
			'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
			'$task->Proposed','$task->Unit_Name','$task->V3_Propose','$task->V3_TOP',0,'$task->V3_TOP','" . addslashes($task->V3_Note) . "','$task->PR_Related')"));
				DB::update(DB::raw("UPDATE purchase_request set PO_No='$PONO',Processing_Status='PO APPROVED',Processing_Date=NOW() WHERE PR_No='$task->PR_Related'"));
			}
			if ($gg->V3_Discount != 0) {
				$totalx -= $gg->V3_Discount;
			}

			if ($gg->V3_Fee_Mgt != 0) {
				if ($gg->V3_Fee_VAT != 0) {
					$totalx += $gg->V3_Fee_Mgt;
					$ppn1 = round(($totalx * $gg->V3_VAT) / 100, 0);
					$totalx += $ppn1;
				} else {
					$ppn1 =  round(($totalx * $gg->V3_VAT) / 100, 0);
					$totalx += $ppn1;
					$totalx += $gg->V3_Fee_Mgt;
				}
			} else {
				$ppn1 =  round(($totalx * $gg->V3_VAT) / 100, 0);
				$totalx += $ppn1;
			}
			if ($gg->V3_DC != 0) {
				$totalx += $gg->V3_DC;
			}

			$scjum += 1;
			$poksps = sprintf("%'.03d", $scjum) . "/PO/KSPS/$skr";

			DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
				PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,PO_No_KSPS) 
				SELECT '$lsp','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V3_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V3_Note,
				NOW(),'$saya',Task_ID,V3_DC,V3_Fee_Mgt,V3_PPH,V3_Fee_VAT,V3_Discount,'$taskid','$poksps' FROM purchase_summary where PR_No='$taskid'"));
		}
	}

	if ($sup4 != '') {
		$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "'$date%'")->get();
		$CQuery = count($query);
		$NoR = $CQuery[0] + 1;
		if ($NoR < 10) $NoR = "0$NoR";
		$dateR = date('Ymd');
		$totalx = 0;
		$subtotalx = 0;
		$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
		$sels = DB::table("purchase_summary_detail")->where("PR_Related", $taskid)->where("V4_Win", 1)->get();
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V4_TOP;
				$subtotalx += $task->V4_TOP;
				DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
			Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No)
			VALUES('$lsp','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
			'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
			'$task->Proposed','$task->Unit_Name','$task->V4_Propose','$task->V4_TOP',0,'$task->V4_TOP','" . addslashes($task->V4_Note) . "','$task->PR_Related')"));
				DB::update(DB::raw("UPDATE purchase_request set PO_No='$PONO',Processing_Status='PO APPROVED',Processing_Date=NOW() WHERE PR_No='$task->PR_Related'"));
			}

			if ($gg->V4_Discount != 0) {
				$totalx -= $gg->V4_Discount;
			}

			if ($gg->V4_Fee_Mgt != 0) {
				if ($gg->V4_Fee_VAT != 0) {
					$totalx += $gg->V4_Fee_Mgt;
					$ppn1 =  round(($totalx * $gg->V4_VAT) / 100, 0);
					$totalx += $ppn1;
				} else {
					$ppn1 = round(($totalx * $gg->V4_VAT) / 100, 0);
					$totalx += $ppn1;
					$totalx += $gg->V4_Fee_Mgt;
				}
			} else {
				$ppn1 = round(($totalx * $gg->V4_VAT) / 100, 0);
				$totalx += $ppn1;
			}
			if ($gg->V4_DC != 0) {
				$totalx += $gg->V4_DC;
			}

			$scjum += 1;
			$poksps = sprintf("%'.03d", $scjum) . "/PO/KSPS/$skr";

			DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
				PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,PO_No_KSPS) 
				SELECT '$lsp','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V4_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V4_Note,
				NOW(),'$saya',Task_ID,V4_DC,V4_Fee_Mgt,V4_PPH,V4_Fee_VAT,V4_Discount,'$taskid','$poksps' FROM purchase_summary where PR_No='$taskid'"));
		}
	}

	if ($sup5 != '') {
		$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "'$date%'")->get();
		$CQuery = count($query);
		$NoR = $CQuery[0] + 1;
		if ($NoR < 10) $NoR = "0$NoR";
		$dateR = date('Ymd');
		$totalx = 0;
		$subtotalx = 0;
		$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
		$sels = DB::table("purchase_summary_detail")->where("PR_Related", $taskid)->where("V5_Win", 1)->get();
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V5_TOP;
				DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
			Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No)
			VALUES('$lsp','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
			'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
			'$task->Proposed','$task->Unit_Name','$task->V5_Propose','$task->V5_TOP',0,'$task->V5_TOP','" . addslashes($task->V5_Note) . "','$task->PR_Related')"));
				DB::update(DB::raw("UPDATE purchase_request set PO_No='$PONO',Processing_Status='PO APPROVED',Processing_Date=NOW() WHERE PR_No='$task->PR_Related'"));
				$subtotalx += $task->V5_TOP;
			}

			if ($gg->V5_Discount != 0) {
				$totalx -= $gg->V5_Discount;
			}

			if ($gg->V5_Fee_Mgt != 0) {
				if ($gg->V5_Fee_VAT != 0) {
					$totalx += $gg->V5_Fee_Mgt;
					$ppn1 = round(($totalx * $gg->V5_VAT) / 100, 0);
					$totalx += $ppn1;
				} else {
					$ppn1 =  round(($totalx * $gg->V5_VAT) / 100, 0);
					$totalx += $ppn1;
					$totalx += $gg->V5_Fee_Mgt;
				}
			} else {
				$ppn1 =  round(($totalx * $gg->V5_VAT) / 100, 0);
				$totalx += $ppn1;
			}
			if ($gg->V5_DC != 0) {
				$totalx += $gg->V5_DC;
			}

			$scjum += 1;
			$poksps = sprintf("%'.03d", $scjum) . "/PO/KSPS/$skr";

			DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
				PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,PO_No_KSPS) 
				SELECT '$lsp','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V5_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V5_Note,
				NOW(),'$saya',Task_ID,V5_DC,V5_Fee_Mgt,V5_PPH,V5_Fee_VAT,V5_Discount,'$taskid','$poksps' FROM purchase_summary where PR_No='$taskid'"));
		}
	}
}
$notes = date("Y-m-d H:i:s") . " : User  $saya => Approve Purchase Order # $_POST[Note]";


DB::update(DB::raw("UPDATE purchase_summary SET " . str_replace("NaN", "0", $ars) . " Status='$nstatus',Note = CONCAT('$notes <br>',Note) WHERE Task_ID='$TaskID'"));
echo "success";
