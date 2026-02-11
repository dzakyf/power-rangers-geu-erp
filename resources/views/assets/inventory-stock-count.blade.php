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
        <h1>Assets And Inventory</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Inventory</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Stock Count</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="StockCount" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselStockCountDet" name="ekselStockCountDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="StockCount.toExcel('./export/generate.php');"  />
			<input type="button" name="newInv" value="New Stock Count" id="newInv" onclick="newIncoming();" class="btn btn-success mt-2 mr-2" />

		</div>
	</div>	
	<div id='StockCountDiv' ></div>
<script>



StockCount = new dhtmlXGridObject('StockCount');
StockCount.setImagePath("./dhtmlx/imgs/");
StockCount.setHeader("No.,Task ID,Created Date,Created By,Aging (days),Status,Remark,Accuration");
StockCount.setInitWidths("60,200,120,100,100,100,200,100");
StockCount.setColAlign("center,center,center,center,center,center,left,right");
StockCount.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron");
StockCount.setColSorting("int,str,str,str,str,str,str");
StockCount.attachEvent("onRowDblClicked", StockCountDoubleClick);
StockCount.enableSmartRendering(true, 50);
StockCount.setSkin("dhx_skyblue");
StockCount.init();
refreshdata();

function refreshdata(){
	StockCount.clearAndLoad('xml-inventory-stock-count');

}

function StockCountDoubleClick() {

	StockCountMIDStatus = StockCount.cells(StockCount.getSelectedId(),5).getValue();
	StockCountMID = StockCount.cells(StockCount.getSelectedId(),1).getValue();
		LoadM('StockCountDiv','inventory-stock-count-detail?ID=' + StockCountMID);
	
}
function newIncoming() {
	$.blockUI();

	LoadM('StockCountDiv','inventory-stock-count-new');
}


</script>
@endsection
