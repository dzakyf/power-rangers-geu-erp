<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;



?>

@extends('layout.mainlayout-finance')

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
			<span class="active">History</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="Reimbursement" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div id="divlistProjectReimbursement"></div>

</div>

<script>
	function ReimbursementDoubleClick() {

		ReimbursementID = Reimbursement.getSelectedId();
		tipe = Reimbursement.cells(Reimbursement.getSelectedId(), 2).getValue();
		tgl = Reimbursement.cells(Reimbursement.getSelectedId(), 8).getValue();
		if (ReimbursementID != '') {
			LoadM('divlistProjectReimbursement', 'finance-reimbursement2-history-detail?ID=' + ReimbursementID);
		}
		return true;
	}

	Reimbursement = new dhtmlXGridObject('Reimbursement');
	Reimbursement.setImagePath("./dhtmlx/imgs/");
	Reimbursement.setHeader("No.,Reimbursement No,Date,Account No,Reference No,Department,Total,Responsibility,Date Submit,Aging,Status,Currency");
	Reimbursement.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#combo_filter");
	Reimbursement.setInitWidths("70,180,100,180,100,100,100,100,100,110,100,100");
	Reimbursement.setColTypes("ro,ro,ro,ro,ed,ro,ron,ro,ro,ro,ro,ro");

	Reimbursement.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
	Reimbursement.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
	Reimbursement.setDateFormat("%Y-%m-%d");

	Reimbursement.setEditable(false);
	Reimbursement.setNumberFormat("0,000", 6, ".", ",");
	Reimbursement.attachEvent("onRowDblClicked", ReimbursementDoubleClick);

	Reimbursement.setSkin("dhx_skyblue");
	Reimbursement.init();
	refreshdata();

	function refreshdata() {
		Reimbursement.clearAndLoad("xml-finance-reimbursement2-history");

	}
</script>
@endsection