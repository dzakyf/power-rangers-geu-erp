<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$expired = "Wed, 18 Dec 2030 12:00:00 GMT;";

?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Finance</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Approval</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Invoice</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="ApprovalInv" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselApprovalInvDet" name="ekselApprovalInvDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ApprovalInv.toExcel('./export/generate.php');" />
	 <input type="button" id="ekselApprovalInvDet2" name="ekselApprovalInvDet2" class="btn btn-success mt-2 mr-2" value="Batch Approve" onclick="approveall()" />

    </div>
    <div id="divlistcustomer"></div>
</div>

<script>
function approveall() {
	PApprovalInv.sendData();
		alert("Batch Approve Submitted!");
		setTimeout(function(){
			refreshdata();
		},2000);
        return true;
    }
    function ApprovalInvKlik() {

        CustomerListsID = ApprovalInv.getSelectedId();

        if (CustomerListsID != '') {
            LoadM('divlistcustomer', 'finance-approval-invoice-detail?ID=' + CustomerListsID);
        }
        return true;
    }
    ApprovalInv = new dhtmlXGridObject('ApprovalInv');
    ApprovalInv.setImagePath("./dhtmlx/imgs/");
    ApprovalInv.setHeader("No,Inv KSPS,Inv Date,Customer Name, Deskripsi,Kategori, Periode,Nominal (DPP), PPN, Total,Faktur,Created Date,Invoice TO, Batch Approve, No. BA");
    ApprovalInv.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter,#number_filter,#number_filter,#number_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    ApprovalInv.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,,");
    ApprovalInv.setInitWidths("60,100,100,100,250,180,100,100,100,100,150,150,150,150,150");
    ApprovalInv.setColAlign("center,center,center,left,left,left,left,right,right,right,center,center,left,center,left");
    ApprovalInv.setColSorting("na,str,date,str,str,str,str,int,int,int,str,str,str,str,str,str,str,str");
    ApprovalInv.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ch,ed");
    ApprovalInv.enableSmartRendering(true, 150);
    ApprovalInv.setNumberFormat("0,000", 7, ".", ",");
    ApprovalInv.setNumberFormat("0,000", 7, ".", ",");
    ApprovalInv.setNumberFormat("0,000", 8, ".", ",");
    ApprovalInv.setNumberFormat("0,000", 9, ".", ",");
    ApprovalInv.attachEvent("onRowDblClicked", ApprovalInvKlik);
	PApprovalInv = new dataProcessor("finance-approval-invoice-update");
		PApprovalInv.setTransactionMode({
			mode: "POST",
			total: true,
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			}
		})
		PApprovalInv.setUpdateMode("Off");
		PApprovalInv.init(ApprovalInv);
    ApprovalInv.setSkin("dhx_skyblue");
    ApprovalInv.init();
    refreshdata();

    function refreshdata() {
        ApprovalInv.clearAndLoad('xml-finance-approval-invoice');

    }
</script>
@endsection