<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$Priv = Auth::user()->Priv_ID;
// if ($Priv != 'HRD') {
// 	echo '<script>window.location = "/";</script>';
// }


?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Human Resources</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Payroll</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">APPROVAL</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="PayrollApproval" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
	<div id="CekReload">
		<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="PayrollApproval.toExcel('./export/generate.php');" />
		<input type="button" id="ekselPODListDet2" name="ekselPODListDet2" value="Cancel" class="btn btn-danger mt-2 mr-2" onclick="batalkansalary();" />
		<input type="button" id="apprv2" name="apprv2" value="APPROVE" class="btn btn-success mt-2 mr-2" onclick="approvesalary();" />
	</div>
	<div id="divlistProjectOrgx"></div>
</div>

<script>
	function batalkansalary() {
		dhtmlx.confirm('Confirm Cancel this Salary?', 'Generator', function(r) {

			if (r == true) {

				PayrollApprovalID = PayrollApproval.getSelectedId();

				if (PayrollApprovalID != '') {

					LoadM('divlistProjectOrgx', 'payroll-approval-cancel?ID=' + PayrollApprovalID);

				}
				return true;
			}
		});
	}

	function approvesalary() {
		dhtmlx.confirm('Confirm Approve this Salary?', 'Generator', function(r) {

			if (r == true) {
				PayrollApprovalID = PayrollApproval.getSelectedId();

				if (PayrollApprovalID != '') {

					LoadM('divlistProjectOrgx', 'payroll-approval-approve?ID=' + PayrollApprovalID);

				}
				return true;
			}
		});
	}

	function PayrollApprovalKlik() {

		PayrollApprovalID = PayrollApproval.getSelectedId();

		if (PayrollApprovalID != '') {

			LoadM('divlistProjectOrgx', 'payroll-approval-detail?ID=' + PayrollApprovalID);

		}
		return true;
	}

	PayrollApproval = new dhtmlXGridObject('PayrollApproval');
	PayrollApproval.setImagePath("./dhtmlx/imgs/");
	PayrollApproval.setHeader("No.,Salary ID,Period,Total,Category,Use_For,Note,Created By,Created Date,Status");
	PayrollApproval.setInitWidths("70,250,100,100,100,100,100,120,100,100,100,100,100,100,100");
	PayrollApproval.setColAlign("center,left,center,center,center,center,center,center,center,center");
	PayrollApproval.setColTypes("ro,ro,ro,ron,ro,ro,ro,ro,ro,ro");
	PayrollApproval.setColSorting("int,str,str,str,str,str,str,str");
	PayrollApproval.setNumberFormat("0,000", 3, ".", ",");
	PayrollApproval.attachEvent("onRowDblClicked", PayrollApprovalKlik);
	PayrollApproval.setSkin("dhx_skyblue");
	PayrollApproval.enableMultiline(true);
	PayrollApproval.init();
	refreshdata();

	function refreshdata() {
		PayrollApproval.clearAndLoad('xml-payroll-approval');

	}
</script>
@endsection