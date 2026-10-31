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
                <span class="active">Incoming Parts</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="IncomingParts" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselIncomingPartsDet" name="ekselIncomingPartsDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="IncomingParts.toExcel('./export/generate.php');"  />
			<input type="button" name="newInv" value="New Incoming" id="newInv" onclick="newIncoming();" class="btn btn-success mt-2 mr-2" />

		</div>
	</div>	
	<div id='IncomingPartsDiv' ></div>
<script>
IncomingParts = new dhtmlXGridObject('IncomingParts');
IncomingParts.setImagePath("./dhtmlx/imgs/");
IncomingParts.setHeader("No.,Tipe,Kode Produk,Nama Produk,Lokasi,Jumlah Stok,Spesifikasi,Foto,Satuan,Harga");
IncomingParts.setHeader("No.,GR No,PO No,Reference,PR No,Status,Apply_To,Date Created");
IncomingParts.setInitWidths("70,120,150,200,100,100,150,100");
IncomingParts.setColAlign("center,center,center,center,center,center,center,center");
IncomingParts.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
IncomingParts.setColSorting("int,str,str,str,str,str,str,str");
IncomingParts.setEditable(true);
IncomingParts.attachEvent("onRowDblClicked", IncomingPartsDoubleClick);
IncomingParts.setSkin("dhx_skyblue");
IncomingParts.init();
refreshdata();

function refreshdata(){
	IncomingParts.clearAndLoad('xml-inventory-incoming');

}

function IncomingPartsDoubleClick() {

	IncomingPartsMIDStatus = IncomingParts.cells(IncomingParts.getSelectedId(),5).getValue();
	IncomingPartsMID = IncomingParts.cells(IncomingParts.getSelectedId(),1).getValue();
	if (IncomingPartsMIDStatus =='CREATED'){
		LoadM('IncomingPartsDiv','inventory-incoming-detail?ID=' + encodeURI(IncomingPartsMID));
	}else if (IncomingPartsMIDStatus =='PROPOSED'){
		LoadM('IncomingPartsDiv','inventory-incoming-detail2?ID=' + encodeURI(IncomingPartsMID));
	}
}
function newIncoming() {

	LoadM('IncomingPartsDiv','inventory-incoming-new');
}


</script>
@endsection
