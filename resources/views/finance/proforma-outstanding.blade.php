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
            <a href="#">Proforma</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Outstanding</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="ProformaPost" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselProformaPostDet" name="ekselProformaPostDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProformaPost.toExcel('./export/generate.php');" />

    </div>
    <div id="divlistcustomer"></div>
</div>

<script>
    function ProformaPostKlik() {

        CustomerListsID = ProformaPost.getSelectedId();

        if (CustomerListsID != '') {
            LoadM('divlistcustomer', 'finance-proforma-outstanding-detail?ID=' + CustomerListsID);
        }
        return true;
    }
    ProformaPost = new dhtmlXGridObject('ProformaPost');
    ProformaPost.setImagePath("./dhtmlx/imgs/");
    ProformaPost.setHeader("No,Proforma No,Proforma Date,Customer Name,Pekerjaan, Periode, Proforma Type,Total Amount,BA No,BA Date,Created By,Posted Date");
    ProformaPost.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    ProformaPost.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
    ProformaPost.setInitWidths("60,120,100,100,100,100,80,100,100,100,100,100");
    ProformaPost.setColAlign("center,center,left,center,center,right,center,center,center,center,left,right");
    ProformaPost.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,int");
    ProformaPost.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro");
    ProformaPost.enableSmartRendering(true, 150);
    ProformaPost.setNumberFormat("0,000", 7, ".", ",");
    ProformaPost.attachEvent("onRowDblClicked", ProformaPostKlik);

    ProformaPost.setSkin("dhx_skyblue");
    ProformaPost.init();
    refreshdata();

    function refreshdata() {
        ProformaPost.clearAndLoad('xml-finance-proforma-outstanding');

    }
</script>
@endsection