<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	
	
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
                <span class="active">Moving Assets Approval</span>
            </li>
        </ol>
    </div> 
    <div class="card mt-3">
        <div class="card-body">
            <div id="AssetMoving" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
            <div>
                <input type="button" id="ekselDATAASSETSDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="DATAASSETS.toExcel('./export/generate.php');"  />
                <input type="button" id="DATAASSETS_Refresh" class="btn btn-info mt-2 mr-2" value="Refresh Data" onclick="AssetMovingRefresh();"  />
                <input type="button" id="dxdata" class="btn btn-success mt-2 mr-2" value="Detail Form Asset Moving" onclick="AssetMovingDetail();"  />
            </div>
        </div>
    </div>
    <div id="DATAASSETSDTL"></div>
	
<script>
function AssetMovingDetail() {
    let AssetMovingID = AssetMoving.getSelectedRowId();
    if(AssetMovingID == null) {
        alert("Pilih Baris terlebih dulu");
        return false;
    }
    LoadM('DATAASSETSDTL', 'hrd-asset-moving-approval-detail');
}

AssetMoving = new dhtmlXGridObject('AssetMoving');
AssetMoving.setImagePath("./dhtmlx/imgs/");
AssetMoving.setHeader("No,Request No, Requestor, Department, Request Date, Asset No, Asset Name, Asset Description, Moving Reason, Moving Destination, Moving Date, Receipent Name, Receipent Department, Status");
AssetMoving.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
AssetMoving.setInitWidths("90,120,150,120,100,100,220,200,100,100,100,100,100,100,100,100,100,100,100,100");
AssetMoving.setColAlign("center,center,center,center,center,center,left,left,center,right,center,center,center,center,center,center,center,center,center,center");
AssetMoving.setDateFormat("%Y-%m-%d"); 

AssetMoving.setColSorting("int,str,str,str,date,str,str,str,str,str,date,str,str,str,str,str,str,str,str,str");
AssetMoving.setColTypes("ro,ed,ro,ed,ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro");
AssetMoving.setNumberFormat("0,000",12,".",",");
AssetMoving.enableSmartRendering(true, 150);	
AssetMoving.setEditable(true);
		
AssetMoving.setSkin("dhx_skyblue");
AssetMoving.init();
function refreshdata() {
    AssetMoving.clearAndLoad('hrd-asset-moving-approval-data');
}

refreshdata();

</script>
@endsection
