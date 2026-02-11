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
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Payroll</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Issued</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="PayrollApproval" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
		<div id="CekReload">
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="PayrollApproval.toExcel('./export/generate.php');"  />
		</div>
		<div id="divlistProjectOrgx"></div>
	</div>	
		
<script>

function PayrollApprovalKlik() {

PayrollApprovalID = PayrollApproval.getSelectedId();

if (PayrollApprovalID !=''){
	
	LoadM('divlistProjectOrgx','accounting-payroll-issued-detail?ID=' + PayrollApprovalID);
	
}
return true;
}

PayrollApproval = new dhtmlXGridObject('PayrollApproval');
PayrollApproval.setImagePath("./dhtmlx/imgs/");
PayrollApproval.setHeader("No.,Salary ID,Period,Total,Category,Use_For,Note,Created By,Created Date");
PayrollApproval.setInitWidths("70,250,100,100,100,100,100,120,100,100,100,100,100,100");
PayrollApproval.setColAlign("center,left,center,center,center,center,center,center,center,center");
PayrollApproval.setColTypes("ro,ro,ro,ron,ro,ro,ro,ro,ro");
PayrollApproval.setColSorting("int,str,str,str,str,str,str,str");
PayrollApproval.setNumberFormat("0,000",3,".",",");
PayrollApproval.attachEvent("onRowDblClicked", PayrollApprovalKlik);
PayrollApproval.setSkin("dhx_skyblue");
PayrollApproval.enableMultiline(true);
PayrollApproval.init();
refreshdata();

function refreshdata(){
	PayrollApproval.clearAndLoad('xml-accounting-payroll-issued');

}
</script>
@endsection