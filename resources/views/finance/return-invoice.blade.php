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
            <a href="#">CANCELED</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Invoices</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="ApprovalInv" style="width:auto;height:340px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselApprovalInvDet" name="ekselApprovalInvDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ApprovalInv.toExcel('./export/generate.php');" />

    </div>
    <div id="divlistcustomer"></div>
</div>

<script>
    function ApprovalInvKlik() {

        CustomerListsID = ApprovalInv.getSelectedId();

        if (CustomerListsID != '') {
            LoadM('divlistcustomer', 'finance-invoice-return-detail?ID=' + CustomerListsID);
        }
        return true;
    }
    ApprovalInv = new dhtmlXGridObject('ApprovalInv');
    ApprovalInv.setImagePath("./dhtmlx/imgs/");
    ApprovalInv.setHeader("No,Inv KSPS,Inv Date,Customer Name,Deskripsi,Kategori,Periode,Nominal (DPP), PPN, Total,Faktur,Created Date,Posted Date,Invoice TO,Status,No. BA,Reason,Cancelation By,Cancelation Date");
    ApprovalInv.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter,#number_filter,#number_filter,#number_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    ApprovalInv.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,,,");
    ApprovalInv.setInitWidths("60,100,100,100,250,180,100,100,100,100,150,150,150,150,150,150,100,100,100,100");
    ApprovalInv.setColAlign("center,center,center,left,left,left,left,right,right,right,center,center,center,left,left,left,left");
    ApprovalInv.setColSorting("na,str,date,str,str,str,str,int,int,int,str,str,str,str,str,str,str");
    ApprovalInv.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro,ro,ed,ro,ro,ro,ro");
    ApprovalInv.enableSmartRendering(true, 150);
    ApprovalInv.setNumberFormat("0,000", 7, ".", ",");
    ApprovalInv.setNumberFormat("0,000", 8, ".", ",");
    ApprovalInv.setNumberFormat("0,000", 9, ".", ",");
    ApprovalInv.attachEvent("onRowDblClicked", ApprovalInvKlik);
    ApprovalInv.setSkin("dhx_skyblue");
    ApprovalInv.init();
    refreshdata();

    function refreshdata() {
        ApprovalInv.clearAndLoad('xml-finance-return-invoice');

    }

</script>
@endsection