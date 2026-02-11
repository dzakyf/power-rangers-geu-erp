<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;



?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Reimbursement Individual</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Approval</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="Reimbursement" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
	<input type="button" id="exportGridCash" name="exportGridCash" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="Reimbursement.toExcel('./export/generate.php');" />
</div>
	<div id="divlistProjectReimbursement"></div>

</div>
<script>
	function ReimbursementDoubleClick() {

		ReimbursementID = Reimbursement.getSelectedId();
		tipe = Reimbursement.cells(Reimbursement.getSelectedId(), 2).getValue();
		tgl = Reimbursement.cells(Reimbursement.getSelectedId(), 8).getValue();
		if (ReimbursementID != '') {
			LoadM('divlistProjectReimbursement', 'finance-reimbursement2-approval-detail?ID=' + ReimbursementID);
		}
		return true;
	}

	Reimbursement = new dhtmlXGridObject('Reimbursement');
	Reimbursement.setImagePath("./dhtmlx/imgs/");
	Reimbursement.setHeader("No.,Reimbursement No,Date,Account No,Reference No,Department,Total,Responsibility,Date_Submit,Aging,Status,Currency,Note");
	Reimbursement.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#combo_filter,#text_filter");
	Reimbursement.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,,,,,,,,,");
	Reimbursement.setInitWidths("70,180,100,180,100,100,100,100,100,110,100,100,200");
	Reimbursement.setColTypes("ro,ro,ro,ro,ed,ro,edn,ro,ro,ro,ro,ro,ed");

	Reimbursement.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center,left");
	Reimbursement.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	Reimbursement.setDateFormat("%Y-%m-%d");

	Reimbursement.setEditable(false);
	Reimbursement.setNumberFormat("0,000", 6, ".", ",");
	Reimbursement.attachEvent("onRowDblClicked", ReimbursementDoubleClick);

	Reimbursement.setSkin("dhx_skyblue");
	Reimbursement.init();
	refreshdata();

	function refreshdata() {
		Reimbursement.clearAndLoad("xml-finance-reimbursement2-approval");

	}
</script>
@endsection