<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$LSP  = Auth::user()->Location;
$Saya = Auth::user()->username;

?>

@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Export</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/">Sustainability Declaration</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Sustainability Declaration List</span>
        </li>
    </ol>
</div>

<div>
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SUSTAINABILITY DECLARATION LIST</div>
    <div class="card">
        <div class="card-body">
            <div id="SustainabilityDeclaration" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="newSD" value="New SD" class="btn btn-primary mt-2 mr-2" onclick="SDNewBtn()" />
                <input type="button" id="detailSD" value="Detail SD" class="btn btn-info mt-2 mr-2" onclick="SDKlik()" />
                <input type="button" id="printSD" value="Print SD" class="btn btn-secondary mt-2 mr-2" onclick="PrintSD()" />
                <input type="button" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="SustainabilityDeclaration.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
    <div id="divlistOpportunity"></div>
    <div id="DivProcess"></div>
</div>

<script>
    function PrintSD() {
        var SDID = SustainabilityDeclaration.getSelectedId();
        if (SDID != null) {
            window.open('cns-export-sustainability-declaration-print?Task=' + SustainabilityDeclaration.cells(SDID, 1).getValue(), '_blank');
        } else {
            alert("Pilih baris data");
        }
    }
    function SDNewBtn() {
        LoadM('divlistOpportunity', 'cns-export-sustainability-declaration-new');
    }
    function SDKlik() {
        var SDID = SustainabilityDeclaration.getSelectedId();
        if (SDID != null) {
            LoadM('divlistOpportunity', 'cns-export-sustainability-declaration-detail?Task=' + SustainabilityDeclaration.cells(SDID, 1).getValue());
        } else {
            alert("Pilih baris data");
        }
    }

    SustainabilityDeclaration = new dhtmlXGridObject('SustainabilityDeclaration');
    SustainabilityDeclaration.setImagePath("./dhtmlx/imgs/");
    SustainabilityDeclaration.setHeader("No,SD No,Issue Date,Consignee,Contract No,Dispatch Date,Quantity (MT),Country of Origin,Status");
    SustainabilityDeclaration.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
    SustainabilityDeclaration.setInitWidths("50,200,110,200,150,110,120,130,100");
    SustainabilityDeclaration.setColAlign("center,left,left,left,left,left,right,left,center");
    SustainabilityDeclaration.setColSorting("int,str,str,str,str,str,str,str,str");
    SustainabilityDeclaration.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro");
    SustainabilityDeclaration.enableSmartRendering(true, 150);
    SustainabilityDeclaration.attachEvent("onRowDblClicked", SDKlik);
    SustainabilityDeclaration.setSkin("dhx_skyblue");
    SustainabilityDeclaration.init();

    function refreshdata() {
        SustainabilityDeclaration.clearAndLoad('xml-cns-export-sustainability-declaration');
    }
    refreshdata();
</script>
@endsection
