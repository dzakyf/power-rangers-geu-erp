<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	
	
?>

@extends('layout.mainlayout-asset')

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
                <span class="active">Data</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="InventoryLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselInventoryListsDet" name="ekselInventoryListsDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="InventoryLists.toExcel('./export/generate.php');"  />
		</div>
		<div id='InventoryDiv' ></div>
	</div>	
<script>
InventoryLists = new dhtmlXGridObject('InventoryLists');
InventoryLists.setImagePath("./dhtmlx/imgs/");
InventoryLists.setHeader("No.,Tipe,Kode Produk,Nama Produk,Lokasi,Jumlah Stok,Spesifikasi,Foto,Satuan,Harga Satuan");
InventoryLists.attachHeader("&nbsp;,#combo_filter,#combo_filter,#text_filter,#combo_filter,#combo_filter,&nbsp;,&nbsp;,#combo_filter,#combo_filter");
InventoryLists.setInitWidths("60,100,100,360,100,100,0,0,100,80");
InventoryLists.setColAlign("center,center,center,left,left,center,right,left,center,center,right");
InventoryLists.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
InventoryLists.setColSorting("int,str,str,str,int,str,str,str,str");
InventoryLists.attachEvent("onRowDblClicked", POMgtDoubleClick);
InventoryLists.setNumberFormat("0,000",9,".",",");
InventoryLists.enableSmartRendering(true);
InventoryLists.setSkin("dhx_skyblue");
InventoryLists.init();
refreshdata();

function refreshdata(){
	InventoryLists.clearAndLoad('xml-inventory-lists');

}

function POMgtDoubleClick() {
	InventoryListsMID = InventoryLists.getSelectedId();
	InventoryListsMIDStatus = InventoryLists.cells(InventoryLists.getSelectedId(),5).getValue();
	LoadM('InventoryDiv','inventory-lists-detail?IdProd=' + InventoryListsMID);
}



</script>
@endsection
