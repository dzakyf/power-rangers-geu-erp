<?php
use Illuminate\Support\Facades\Auth;
?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Data Assets</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Asset Fixing Approval</span>
            </li>
        </ol>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <div id="AssetFixingApproval" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ASSETFIXINGAP_Refresh" class="btn btn-info mt-2 mr-2" value="Refresh Data" onclick="AssetFixingApprovalRefresh();" />
                <input type="button" id="dxdata" class="btn btn-success mt-2 mr-2" value="Detail Approval" onclick="AssetFixingApprovalDetail();" />
            </div>
        </div>
    </div>

    <div id="ASSETFIXINGAPPROVALDTL"></div>

<script>
function AssetFixingApprovalDetail() {
    let AssetFixingApprovalID = AssetFixingApproval.getSelectedRowId();
    if (AssetFixingApprovalID == null) {
        alert("Pilih Baris terlebih dulu");
        return false;
    }
    LoadM('ASSETFIXINGAPPROVALDTL', 'hrd-asset-fixing-approval-detail?ID=' + AssetFixingApprovalID);
}

function AssetFixingApprovalRefresh() {
    AssetFixingApproval.clearAndLoad('hrd-asset-fixing-approval-data');
}

AssetFixingApproval = new dhtmlXGridObject('AssetFixingApproval');
AssetFixingApproval.setImagePath("./dhtmlx/imgs/");
AssetFixingApproval.setHeader("No,Request No,Jenis Perbaikan,Requestor,Tanggal Pengajuan,Nama Aset,Estimasi Biaya,Status,Department");
AssetFixingApproval.attachHeader("&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
AssetFixingApproval.setInitWidths("60,140,120,150,120,180,140,120,100");
AssetFixingApproval.setColAlign("center,center,center,center,center,center,right,center,center");
AssetFixingApproval.setDateFormat("%Y-%m-%d");
AssetFixingApproval.setColSorting("int,str,str,str,date,str,str,str,str");
AssetFixingApproval.setColTypes("ro,ed,ro,ed,ro,ro,ro,ro,ro");
AssetFixingApproval.setNumberFormat("0,000",6,".",",");
AssetFixingApproval.enableSmartRendering(true, 150);
AssetFixingApproval.setSkin("dhx_skyblue");
AssetFixingApproval.init();

function refreshdata() {
    AssetFixingApproval.clearAndLoad('hrd-asset-fixing-approval-data');
}

AssetFixingApproval.clearAndLoad('hrd-asset-fixing-approval-data');
</script>
@endsection
