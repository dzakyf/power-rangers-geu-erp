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
                <span class="active">Moving Assets</span>
            </li>
        </ol>
    </div> 
    <div id="AssetMoving" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
    <div>
        <input type="button" id="ekselDATAASSETSDet" class="btn btn-warning mr-2 mt-2" value="Export Excel" onclick="DATAASSETS.toExcel('./export/generate.php');"  />
        <input type="button" id="DATAASSETS_Refresh" class="btn btn-info mr-2 mt-2" value="Refresh Data" onclick="AssetMovingRefresh();"  />
        <input type="button" id="dxdata" class="btn btn-primary mr-2 mt-2" value="Form Asset Moving" onclick="AssetMovingNew();"  />
        <input type="button" id="DATAASSETS_Refresh" class="btn btn-success mr-2 mt-2" value="Log Asset Moving" onclick="AssetMovingLog();"  />
        <input type="button" id="DATAASSETS_Refresh" class="btn btn-secondary mr-2 mt-2" value="Asset Lists" onclick="AssetMovingAssetList();"  />
    </div>
   <div id="assetList" class="mt-3" style="display:none;width:100%">
       <div id="DATAASSETS" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
        <div>
           <input type="button" id="ekselDATAASSETSDet" class="btn btn-warning" value="Export Excel" onclick="DATAASSETS.toExcel('./export/generate.php');"  />
           <input type="button" id="DATAASSETS_Refresh" class="btn btn-info" value="Refresh Data" onclick="DATAASSETSRefresh();"  />
           <input type="button" id="DATAASSETS_Refresh" class="btn btn-primary" value="Detail Data" onclick="DATAASSETSDBL();"  />
        </div>
   </div>
    <div id="DATAASSETSDTL"></div>
    <div id="DATAASSETSDTL1"></div>
	
<script>
function AssetMovingAssetList() {
    DATAASSETSRefresh();
    $('#assetList').toggle();
}
function AssetMovingNew() {
    LoadM('DATAASSETSDTL1', 'hrd-asset-moving-create');
}
function AssetMovingLog() {
	TarifID = DATAASSETS.cells(DATAASSETS.getSelectedId(),1).getValue();
    LoadM('DATAASSETSDTL1', 'hrd-asset-moving-log?ID=' + TarifID);
}
function NEWDATA() {
	LoadM('DATAASSETSDTL','asset-lists-new');
}
			
function DATAASSETSDBL() {
	TarifID = DATAASSETS.cells(DATAASSETS.getSelectedId(),1).getValue();
	LoadM('DATAASSETSDTL','asset-lists-detail?Task_ID=' + TarifID);
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
AssetMoving.clearAndLoad('hrd-asset-moving-data');

DATAASSETS = new dhtmlXGridObject('DATAASSETS');
DATAASSETS.setImagePath("./dhtmlx/imgs/");
DATAASSETS.setHeader("No,Asset No,Asset Category,Asset Sub Category,Asset Type,Asset Code,Asset Description,Unit Name,Sub Product Type,Manufacture Date,Registration No,Buy Date,Buy Value,Asset Location,Address,City,Departement,Vendor,Contract No,Acc No");
DATAASSETS.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");

DATAASSETS.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,,#cspan,#cspan,");
DATAASSETS.setInitWidths("90,120,150,120,100,100,220,200,100,100,100,100,100,100,100,100,100,100,100,100");
DATAASSETS.setColAlign("center,center,center,center,center,center,left,left,center,right,center,center,center,center,center,center,center,center,center,center");
DATAASSETS.setDateFormat("%Y-%m-%d"); 

DATAASSETS.setColSorting("int,str,str,str,date,str,str,str,str,str,date,str,str,str,str,str,str,str,str,str");
DATAASSETS.setColTypes("ro,ed,ro,ed,ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro");
DATAASSETS.setNumberFormat("0,000",12,".",",");
DATAASSETS.attachEvent("onRowDblClicked", DATAASSETSDBL);
DATAASSETS.enableSmartRendering(true, 150);	
DATAASSETS.setEditable(true);
		
DATAASSETS.setSkin("dhx_skyblue");
DATAASSETS.init();

function DATAASSETSRefresh(){
	DATAASSETS.clearAndLoad('xml-asset-lists?');
}

function SimpanPenyelesaianOrder(){
	PDATAASSETS.sendData();
	alert("Data Berhasil Disimpan");
	setTimeout(function(){
	DATAASSETSRefresh();
	},2000);
}


</script>
@endsection
