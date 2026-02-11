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
    <h1>Sales</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Proforma</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Attachment Upload</span>
        </li>
    </ol>
</div>

<div class="row">
    <div id="AttachmentUpload" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselAttachmentUploadDet" name="ekselAttachmentUploadDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="AttachmentUpload.toExcel('./export/generate.php');" />

    </div>
    <div id="divlistcustomer"></div>
</div>

<script>
    function AttachmentUploadKlik() {

        CustomerListsID = AttachmentUpload.getSelectedId();

        if (CustomerListsID != '') {
            LoadM('divlistcustomer', 'sales-proforma-attachment-detail?ID=' + CustomerListsID);
        }
        return true;
    }
    AttachmentUpload = new dhtmlXGridObject('AttachmentUpload');
    AttachmentUpload.setImagePath("./dhtmlx/imgs/");
    AttachmentUpload.setHeader("No,Proforma No,Proforma Date,Customer Name,Pekerjaan, Periode, Proforma Type,Total Amount,Qty PO,Status,Created By,Created Date");
    AttachmentUpload.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#text_filter,#text_filter,#text_filter");
    AttachmentUpload.setInitWidths("60,120,100,100,200,150,100,100,80,100,100,100,100,100");
    AttachmentUpload.setColAlign("center,center,left,center,left,left,center,right,center,center,center,center,left,right");
    AttachmentUpload.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,int");
    AttachmentUpload.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro");
    AttachmentUpload.enableSmartRendering(true, 150);
    AttachmentUpload.setNumberFormat("0,000", 7, ".", ",");
    AttachmentUpload.attachEvent("onRowDblClicked", AttachmentUploadKlik);

    AttachmentUpload.setSkin("dhx_skyblue");
    AttachmentUpload.init();
    refreshdata();

    function refreshdata() {
        AttachmentUpload.clearAndLoad('xml-sales-proforma-attachment');

    }
</script>
@endsection