<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;

?>

@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Export</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/">Delivery Note</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Delivery Note List</span>
        </li>
    </ol>
</div>

<div>
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DELIVERY NOTE LIST</div>
    <div class="card">
        <div class="card-body">
            <div id="DeliveryNote" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselDeliveryNoteDet" name="ekselDeliveryNoteDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="DeliveryNote.toExcel('./export/generate.php');" />
                <input type="button" id="newDN" name="newDN" value="New DN" class="btn btn-primary mt-2 mr-2" onclick="DeliveryNoteNewBtn()" />
                <input type="button" id="detailDN" name="detailDN" value="Detail DN" class="btn btn-info mt-2 mr-2" onclick="DeliveryNoteKlik()" />
                <input type="button" id="printDN" name="printDN" value="Print DN" class="btn btn-secondary mt-2 mr-2" onclick="PrintDN()" />
            </div>
        </div>
    </div>
    <div id="divlistOpportunity"></div>
    <div id="DivProcess"></div>
</div>

<script>
    function PrintDN() {
        DeliveryNoteID = DeliveryNote.getSelectedId();
        if (DeliveryNoteID != null) {
            window.open('cns-export-delivery-note-print?Task=' + DeliveryNote.cells(DeliveryNoteID, 1).getValue(), '_blank');
        } else {
            alert("Pilih baris data");
        }
        return true;
    }
    function DeliveryNoteNewBtn() {
        LoadM('divlistOpportunity', 'cns-export-delivery-note-new');
        return true;
    }
    function DeliveryNoteKlik() {
        DeliveryNoteID = DeliveryNote.getSelectedId();
        if (DeliveryNoteID != null) {
            LoadM('divlistOpportunity', 'cns-export-delivery-note-detail?Task=' + DeliveryNote.cells(DeliveryNoteID, 1).getValue());
        } else {
            alert("Pilih baris data");
        }
        return true;
    }
    DeliveryNote = new dhtmlXGridObject('DeliveryNote');
    DeliveryNote.setImagePath("./dhtmlx/imgs/");
    DeliveryNote.setHeader("No,DN No,Delivery Date,Consignee,Contract No,Product,Total Tonnage (MT),Country of Origin,Status");
    DeliveryNote.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    DeliveryNote.setInitWidths("50,180,120,180,150,250,130,130,100");
    DeliveryNote.setColAlign("center,left,left,left,left,left,right,left,center");
    DeliveryNote.setColSorting("int,str,str,str,str,str,str,str,str");
    DeliveryNote.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro");
    DeliveryNote.enableSmartRendering(true, 150);
    DeliveryNote.attachEvent("onRowDblClicked", DeliveryNoteKlik);
    DeliveryNote.setSkin("dhx_skyblue");
    DeliveryNote.init();
    refreshdata();

    function refreshdata() {
        DeliveryNote.clearAndLoad('xml-cns-export-delivery-note');
    }
</script>
@endsection
