<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Data Assets</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Asset Fixing</span>
            </li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div id="AssetFixing" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselASSETFIXING" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="AssetFixing.toExcel('./export/generate.php');" />
                <input type="button" id="ASSETFIXING_Refresh" class="btn btn-info mt-2 mr-2" value="Refresh Data" onclick="AssetFixingRefresh();" />
                <input type="button" id="dxdata" class="btn btn-primary mt-2 mr-2" value="Form Perbaikan Aset" onclick="AssetFixingNew();" />
            </div>
        </div>
    </div>

    <div id="ASSETFIXINGDTL"></div>
    <div id="ASSETFIXINGDTL1"></div>

<script>
function AssetFixingNew() {
    LoadM('ASSETFIXINGDTL1', 'hrd-asset-fixing-create');
}

function AssetFixingDetail() {
    let AssetFixingID = AssetFixing.getSelectedRowId();
    if (AssetFixingID == null) {
        alert("Pilih Baris terlebih dulu");
        return false;
    }
    var url = 'hrd-asset-fixing-detail?ID=' + AssetFixingID;
    LoadM('ASSETFIXINGDTL', url);
}

function AssetFixingRefresh() {
    AssetFixing.clearAndLoad('hrd-asset-fixing-data');
}

AssetFixing = new dhtmlXGridObject('AssetFixing');
AssetFixing.setImagePath("./dhtmlx/imgs/");
AssetFixing.setHeader("No,Request No,Jenis Perbaikan,Requestor,Tanggal Pengajuan,Nama Aset,Product Code,Asset No,SN,Estimasi Biaya,Status");
AssetFixing.attachHeader("&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
AssetFixing.setInitWidths("60,140,120,150,120,180,100,100,100,120,100");
AssetFixing.setColAlign("center,center,center,center,center,center,right,center,center,right,center");
AssetFixing.setDateFormat("%Y-%m-%d");
AssetFixing.setColSorting("int,str,str,str,date,str,str,str,str,str,str");
AssetFixing.setColTypes("ro,ed,ro,ed,ro,ro,ro,ro,ro,ro,ro");
AssetFixing.setNumberFormat("0,000",9,".",",");
AssetFixing.enableSmartRendering(true, 150);
AssetFixing.setSkin("dhx_skyblue");
AssetFixing.init();
AssetFixing.clearAndLoad('hrd-asset-fixing-data');

function refreshdata() {
    AssetFixing.clearAndLoad('hrd-asset-fixing-data');
}
</script>
@endsection
