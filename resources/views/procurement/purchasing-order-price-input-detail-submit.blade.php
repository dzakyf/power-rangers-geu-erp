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


$taskid = $_POST['ID'];
$TaskID = $_POST['TaskID'];
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

$status = $_POST['Status'];

if ($status == 'CREATED') { //Ratna
	$nstatus = 'PROPOSED';
} elseif ($status == 'PROPOSED') { //p adi
	$nstatus = 'VERIFIED';
} elseif ($status == 'VERIFIED') {
	if ($totalnya <= 100000000) {
		$nstatus = 'CLOSED';
	} else {
		$nstatus = 'DIRECTOR_APPROVAL';
	}
} elseif ($status == 'DIRECTOR_APPROVAL' || $status == 'VERIFIED') {
	$nstatus = 'CLOSED';
}



$src = rtrim($src, "@");
$trx = explode("@", $src);
foreach ($trx as $v) {
	list($no, $code, $proposed, $systemprice, $price1, $total1, $rec1, $price2, $total2, $rec2, $price3, $total3, $rec3, $price4, $total4, $rec4, $price5, $total5, $rec5, $idpr, $desc) = explode("#", $v);
	$price4 += 0;
	$price5 += 0;
	$price2 += 0;
	$price3 += 0;
	$total4 += 0;
	$total5 += 0;
	$total3 += 0;
	$total2 += 0;

	if (($rec2 == 0) || ($rec3 == 0) || ($rec4 == 0) || ($rec5 == 0)) {
		$rec1 = 1;
	}

	DB::update(DB::raw(str_replace("NaN", "", "UPDATE purchase_summary_detail
				SET Default_Price='$systemprice', Proposed='$proposed',
				V1_Propose='$price1',V1_TOP='$total1',V1_Win='$rec1',
				V2_Propose='$price2',V2_TOP='$total2',V2_Win='$rec2',
				V3_Propose='$price3',V3_TOP='$total3',V3_Win='$rec3',
				V4_Propose='$price4',V4_TOP='$total4',V4_Win='$rec4',
				V5_Propose='$price5',V5_TOP='$total5',V5_Win='$rec5',
				Recommended_Spec='" . $desc . "',Specification='" . $desc . "' 
				WHERE PR_Related='$idpr' AND ID=$no")));
}




$totalV = 0;
$urut = 0;
for ($ven = 1; $ven <= 5; $ven++) {
	$cekTotalV = CekVendorWin($ven, $taskid, $urut);
	if ($cekTotalV == 1) {
		$urut++;
		$kumV .= $urut . "#V" . $ven . "_Win" . "|";
	}
	$totalV += $cekTotalV;
}

$kumV = rtrim($kumV, "|");

$zz = $TaskID;

if ($totalV > 1) {

	for ($copas = 2; $copas <= $totalV; $copas++) {
		$VendorTaskID = $zz . "-V" . $copas;
		$sqlCopas = DB::insert(DB::raw("INSERT INTO purchase_summary
	(Task_ID, PR_No, Purchase_Category, Purchase_Sub_Category, Purchase_Type, Purchase_Date, Status, V1_ID, V1_Cur, V1_Fee_Mgt, V1_VAT, V1_Fee_VAT, V1_PPH, V1_DC, V1_Discount, V1_Note, V1_Total, V2_ID, V2_Cur, V2_Fee_Mgt, V2_VAT, V2_Fee_VAT, V2_PPH, V2_DC, V2_Discount, V2_Note, V2_Total, V3_ID, V3_Cur, V3_Fee_Mgt, V3_VAT, V3_Fee_VAT, V3_PPH, V3_DC, V3_Discount, V3_Note, V3_Total, V4_ID, V4_Cur, V4_Fee_Mgt, V4_VAT, V4_Fee_VAT, V4_PPH, V4_DC, V4_Discount, V4_Note, V4_Total, V5_ID, V5_Cur, V5_Fee_Mgt, V5_VAT, V5_Fee_VAT, V5_PPH, V5_DC, V5_Discount, V5_Note, V5_Total, Created_Date, Created_By, Location, Note, Priority, Priority_Note, Priority_By, Update_By, Active_Progress, TimeOut_Progress, print_count, Qty_Request, Qty_Acc, Received_Status, note_perbaikan)
	
	SELECT '$VendorTaskID' AS Task_ID, PR_No, Purchase_Category, Purchase_Sub_Category, Purchase_Type, Purchase_Date, Status, V1_ID, V1_Cur, V1_Fee_Mgt, V1_VAT, V1_Fee_VAT, V1_PPH, V1_DC, V1_Discount, V1_Note, V1_Total, V2_ID, V2_Cur, V2_Fee_Mgt, V2_VAT, V2_Fee_VAT, V2_PPH, V2_DC, V2_Discount, V2_Note, V2_Total, V3_ID, V3_Cur, V3_Fee_Mgt, V3_VAT, V3_Fee_VAT, V3_PPH, V3_DC, V3_Discount, V3_Note, V3_Total, V4_ID, V4_Cur, V4_Fee_Mgt, V4_VAT, V4_Fee_VAT, V4_PPH, V4_DC, V4_Discount, V4_Note, V4_Total, V5_ID, V5_Cur, V5_Fee_Mgt, V5_VAT, V5_Fee_VAT, V5_PPH, V5_DC, V5_Discount, V5_Note, V5_Total, Created_Date, Created_By, Location, Note, Priority, Priority_Note, Priority_By, Update_By, Active_Progress, TimeOut_Progress, print_count, Qty_Request, Qty_Acc, Received_Status, note_perbaikan
	FROM purchase_summary WHERE PR_No='$taskid'
	"));
	}


	if (strlen($kumV) > 0) $trx = explode("|", $kumV);
	foreach ($trx as $v) {
		list($urut, $ve) = explode("#", $v);
		if ($urut > 1) {
			$nTaskID = $zz  . "-V" . $urut;
			DB::update(DB::raw("UPDATE purchase_summary_detail SET Task_ID='$nTaskID' WHERE PR_Related='$taskid' AND $ve='1'"));
		}
	}
}





DB::update(DB::raw("UPDATE purchase_request SET Processing_Status='$nstatus',Processing_Date=NOW(),PO_Status='$nstatus' WHERE PR_No='$taskid'"));

if ($nstatus == 'CLOSED') {
	$date = date('Y');
	$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "$date%")->get();
	$CQuery = count($query);
	$NoR = $CQuery + 1;
	if ($NoR < 10) $NoR = "0$NoR";
	$dateR = date('Ymd');
	$totalx = 0;
	$subtotalx = 0;
	$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";

	$qr = DB::select(DB::raw("SELECT * FROM purchase_summary WHERE PR_No='$taskid'"));

	foreach ($qr as $gg) {
		$sup1 = $gg->V1_ID;
		$sup2 = $gg->V2_ID;
		$sup3 = $gg->V3_ID;
		$sup4 = $gg->V4_ID;
		$sup5 = $gg->V5_ID;

		$kurs1 = strtoupper($gg->V1_Cur);
		$kurs2 = strtoupper($gg->V2_Cur);
		$kurs3 = strtoupper($gg->V3_Cur);
		$kurs4 = strtoupper($gg->V4_Cur);
		$kurs5 = strtoupper($gg->V5_Cur);


		// $sels = DB::select(DB::raw("SELECT * FROM purchase_summary_detail where PR_Related='$taskid' AND V1_Win=1 AND Task_ID='$TaskID'"));
		// $nums = count($sels);

		// if ($nums != 0) {
		// 	foreach ($sels as $task) {
		// 		$totalx += $task->V1_TOP;
		// 		$subtotalx += $task->V1_TOP;
		// 		DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
		// 	Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No,Currency)
		// 	VALUES('$LSP','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
		// 	'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
		// 	'$task->Proposed','$task->Unit_Name','$task->V1_Propose','$task->V1_TOP',0,'$task->V1_TOP','" . addslashes($task->V1_Note) . "','$task->PR_Related','$kurs1')"));
		// 		DB::update(DB::raw("UPDATE purchase_request set PO_No='$PONO',Processing_Status='PO APPROVED',Processing_Date=NOW() WHERE PR_No='$task->PR_Related'"));
		// 	}
		// 	if ($gg->V1_Discount != 0) {
		// 		$totalx -= $gg->V1_Discount;
		// 	}

		// 	if ($gg->V1_Fee_Mgt != 0) {
		// 		if ($gg->V1_Fee_VAT != 0) {
		// 			$totalx += $gg->V1_Fee_Mgt;
		// 			$ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);
		// 			$totalx += $ppn1;
		// 		} else {
		// 			$ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);;
		// 			$totalx += $ppn1;
		// 			$totalx += $gg->V1_Fee_Mgt;
		// 		}
		// 	} else {
		// 		$ppn1 = round(($totalx * $gg->V1_VAT) / 100, 0);;
		// 		$totalx += $ppn1;
		// 	}
		// 	if ($gg->V1_DC != 0) {
		// 		$totalx += $gg->V1_DC;
		// 	}


		// 	DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
		// 		PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,Currency) 
		// 		SELECT '$LSP','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V1_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V1_Note,
		// 		NOW(),'$saya',Task_ID,V1_DC,V1_Fee_Mgt,V1_PPH,V1_Fee_VAT,V1_Discount,'$taskid',V1_Cur FROM purchase_summary where PR_No='$taskid' AND Task_ID='$TaskID'"));
		// }
	}

	if ($sup1 != '') {
		$date = date('Y');
		$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "$date%")->get();
		$CQuery = count($query);
		$NoR = $CQuery + 1;
		if ($NoR < 10) $NoR = "0$NoR";
		$dateR = date('Ymd');
		$totalx = 0;
		$subtotalx = 0;
		$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
		$sels = DB::select(DB::raw("SELECT * FROM purchase_summary_detail where PR_Related='$taskid' AND V1_Win =1 AND Task_ID='$TaskID'"));
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V1_TOP;
				$subtotalx += $task->V1_TOP;
				DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
			Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No,Currency)
			VALUES('$LSP','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
			'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
			'$task->Proposed','$task->Unit_Name','$task->V1_Propose','$task->V1_TOP',0,'$task->V1_TOP','" . addslashes($task->V1_Note) . "','$task->PR_Related','$kurs1')"));
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

			DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
				PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,Currency) 
				SELECT '$LSP','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V1_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V1_Note,
				NOW(),'$saya',Task_ID,V1_DC,V1_Fee_Mgt,V1_PPH,V1_Fee_VAT,V1_Discount,'$taskid',V1_Cur FROM purchase_summary where PR_No='$taskid' AND Task_ID='$TaskID'"));
		}
	}

	if ($sup2 != '') {
		$date = date('Y');
		$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "$date%")->get();
		$CQuery = count($query);
		$NoR = $CQuery + 1;
		if ($NoR < 10) $NoR = "0$NoR";
		$dateR = date('Ymd');
		$totalx = 0;
		$subtotalx = 0;
		$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
		$sels = DB::select(DB::raw("SELECT * FROM purchase_summary_detail where PR_Related='$taskid' AND V2_Win =1 AND Task_ID='$TaskID'"));
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V2_TOP;
				$subtotalx += $task->V2_TOP;
				DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
			Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No,Currency)
			VALUES('$LSP','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
			'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
			'$task->Proposed','$task->Unit_Name','$task->V2_Propose','$task->V2_TOP',0,'$task->V2_TOP','" . addslashes($task->V2_Note) . "','$task->PR_Related','$kurs2')"));
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

			DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
				PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,Currency) 
				SELECT '$LSP','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V2_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V2_Note,
				NOW(),'$saya',Task_ID,V2_DC,V2_Fee_Mgt,V2_PPH,V2_Fee_VAT,V2_Discount,'$taskid',V2_Cur FROM purchase_summary where PR_No='$taskid' AND Task_ID='$TaskID'"));
		}
	}

	if ($sup3 != '') {
		$date = date('Y');
		$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "$date%")->get();
		$CQuery = count($query);
		$NoR = $CQuery + 1;
		if ($NoR < 10) $NoR = "0$NoR";
		$dateR = date('Ymd');
		$totalx = 0;
		$subtotalx = 0;
		$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
		$sels = DB::select(DB::raw("SELECT * FROM purchase_summary_detail where PR_Related='$taskid' AND V3_Win =1 AND Task_ID='$TaskID'"));
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V3_TOP;
				$subtotalx += $task->V3_TOP;
				DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
					Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No,Currency)
					VALUES('$LSP','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
					'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
					'$task->Proposed','$task->Unit_Name','$task->V3_Propose','$task->V3_TOP',0,'$task->V3_TOP','" . addslashes($task->V3_Note) . "','$task->PR_Related','$kurs3')"));
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

			DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
				PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,Currency) 
				SELECT '$LSP','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V3_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V3_Note,
				NOW(),'$saya',Task_ID,V3_DC,V3_Fee_Mgt,V3_PPH,V3_Fee_VAT,V3_Discount,'$taskid',V3_Cur FROM purchase_summary where PR_No='$taskid' AND Task_ID='$TaskID'"));
		}
	}

	if ($sup4 != '') {
		$date = date('Y');
		$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "$date%")->get();
		$CQuery = count($query);
		$NoR = $CQuery + 1;
		if ($NoR < 10) $NoR = "0$NoR";
		$dateR = date('Ymd');
		$totalx = 0;
		$subtotalx = 0;
		$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
		$sels = DB::select(DB::raw("SELECT * FROM purchase_summary_detail where PR_Related='$taskid' AND V4_Win =1 AND Task_ID='$TaskID'"));
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V4_TOP;
				$subtotalx += $task->V4_TOP;
				DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
			Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No,Currency)
			VALUES('$LSP','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
			'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
			'$task->Proposed','$task->Unit_Name','$task->V4_Propose','$task->V4_TOP',0,'$task->V4_TOP','" . addslashes($task->V4_Note) . "','$task->PR_Related','$kurs4')"));
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

			DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
				PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,Currency) 
				SELECT '$LSP','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V4_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V4_Note,
				NOW(),'$saya',Task_ID,V4_DC,V4_Fee_Mgt,V4_PPH,V4_Fee_VAT,V4_Discount,'$taskid',V4_Cur FROM purchase_summary where PR_No='$taskid' AND Task_ID='$TaskID'"));
		}
	}

	if ($sup5 != '') {
		$date = date('Y');
		$query = DB::table("purchase_order")->where("PO_Date", "LIKE", "$date%")->get();
		$CQuery = count($query);
		$NoR = $CQuery + 1;
		if ($NoR < 10) $NoR = "0$NoR";
		$dateR = date('Ymd');
		$totalx = 0;
		$subtotalx = 0;
		$PONO = "PO/" . $LSP . "/INV/$dateR-$NoR";
		$sels = DB::select(DB::raw("SELECT * FROM purchase_summary_detail where PR_Related='$taskid' AND V5_Win =1 AND Task_ID='$TaskID'"));
		$nums = count($sels);

		if ($nums != 0) {
			foreach ($sels as $task) {
				$totalx += $task->V5_TOP;
				DB::insert(DB::raw("INSERT INTO purchase_order_dtl(Location,PO_No,Category,Sub_Category,Purchase_Type,Product_Code,Barcode,
					Product_Desc,Product_Type,Qty,Unit,Price,Subtotal,PPN,Total,Remark,PR_No,Currency)
					VALUES('$LSP','$PONO','$task->Purchase_Category','$task->Purchase_Sub_Category','$task->Purchase_Type',
					'$task->Product_Code','$task->Product_Code','$task->Specification','$task->Product_Code',
					'$task->Proposed','$task->Unit_Name','$task->V5_Propose','$task->V5_TOP',0,'$task->V5_TOP','" . addslashes($task->V5_Note) . "','$task->PR_Related','$kurs5')"));
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

			DB::insert(DB::raw("INSERT INTO purchase_order(Location,Process_by,Purchase_Type,Purchase_Category,Purchase_Sub_Category,PO_No,
				PO_Date,PO_Method,Supplier,Subtotal,PPN,Total,Status,Remark,Date_Trx,User_Trx,Task_ID,Delivery_Fee,Management_Fee,PPH,Management_Fee_Exclude_PPN,Discount,PR_No,Currency) 
				SELECT '$LSP','$saya',Purchase_Type,Purchase_Category,Purchase_Sub_Category,'$PONO',NOW(),'POSTPAID',V5_ID,'$subtotalx','$ppn1','$totalx','CLOSED',V5_Note,
				NOW(),'$saya',Task_ID,V5_DC,V5_Fee_Mgt,V5_PPH,V5_Fee_VAT,V5_Discount,'$taskid',V5_Cur FROM purchase_summary where PR_No='$taskid' AND Task_ID='$TaskID'"));
		}
	}
}


$rows = DB::select(DB::raw("SELECT Purchase_Category FROM purchase_summary WHERE PR_No='$taskid'"));


$tb = " AND Task_ID='$TaskID'";
DB::update(DB::raw("UPDATE purchase_summary SET Status='$nstatus' WHERE PR_No='$taskid' $tb"));
DB::insert(DB::raw("INSERT INTO purchase_log(Location,Task_ID,Category,Transaction,Status,User_Trx,Remark)values('$LSP','$TaskID','Purchase Order','PO Price Input','$status','$saya','$_POST[Note]')"));
echo "success";
