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
        <h1>Assets</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Data Assets</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Lists</span>
            </li>
        </ol>
    </div> 
   
	<div id="DATAASSETS" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
		<div>
		<input type="button" id="ekselDATAASSETSDet" value="Export Excel" onclick="DATAASSETS.toExcel('./export/generate.php');"  />
		<input type="button" id="DATAASSETS_Refresh" value="Refresh Data" onclick="DATAASSETSRefresh();"  />
		<input type="button" id="dxdata" value="New Data" onclick="NEWDATA();"  />
		<input type="button" id="DATAASSETS_Refresh" value="Detail Data" onclick="DATAASSETSDBL();"  />
		</div>
		<div id="DATAASSETSDTL"></div>
	
<script>

function NEWDATA() {
	LoadM('DATAASSETSDTL','asset-lists-new');
}
			
function DATAASSETSDBL() {
	TarifID = DATAASSETS.cells(DATAASSETS.getSelectedId(),1).getValue();
	LoadM('DATAASSETSDTL','asset-lists-detail?Task_ID=' + TarifID);
}

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

DATAASSETSRefresh();
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
