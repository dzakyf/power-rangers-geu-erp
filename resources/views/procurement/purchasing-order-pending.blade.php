	<?php
	error_reporting(0);

	use Illuminate\Http\Request;
	use Illuminate\Http\Custom_Function;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Database\Eloquent\Builder;
	use Illuminate\Support\Facades\Auth;

	$nama = Auth::user()->Name;

	$Saya = Auth::User()->username;
	$LSP = Auth::user()->Location;

	$notes = $_GET['Note'];
	$notes = date('Y-m-d H:i') . " : PENDING >> $nama => $notes";

	DB::update(DB::raw(("UPDATE purchase_summary set Status='PENDING', Note = CONCAT('$notes <br>',Note) WHERE Task_ID='$Task_ID'")));
	DB::update(DB::raw(("UPDATE purchase_summary_detail set Status_Request='PENDING', Note_Request = '$notes' WHERE Task_ID='$Task_ID'")));
	DB::update(DB::raw(("UPDATE purchase_request r,purchase_summary_detail g set r.PO_Status='PENDING', r.PO_Status_Request  = CONCAT('$notes <br>',r.PO_Status_Request) WHERE r.PR_No=g.PR_Related AND g.Task_ID='$Task_ID'")));
	DB::insert(DB::raw("INSERT INTO purchase_log(Location,Task_ID,Category,Transaction,Status,User_Trx) VALUES('$LSP','$Task_ID','','PO_PENDING','PENDING','$Saya')"));
	echo "success";

	?>