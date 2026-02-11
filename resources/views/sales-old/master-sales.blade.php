<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Sales</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Person</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Lists</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="SalesLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselSalesListsDet" name="ekselSalesListsDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="SalesLists.toExcel('./export/generate.php');"  />
			<input type="button" id="delets" name="delets" value="Delete Sales" onclick="delsSales()"  class="btn btn-danger mt-2 mr-2" />
			<input type="button" id="deletsaa" name="deletsaaa" value="Add New" onclick="addNew()" class="btn btn-success mt-2 mr-2"  />
		</div>

	</div>	
		<div id="divlistSales"></div>
<script>
function addNew() {

LoadM('divlistSales','sales-lists-add');
}
function SalesListsKlik() {

SalesListsID = SalesLists.getSelectedId();

if (SalesListsID !=''){
LoadM('divlistSales','sales-lists-detail?ID=' + SalesListsID);
}
return true;
}
SalesLists = new dhtmlXGridObject('SalesLists');
SalesLists.setImagePath("./dhtmlx/imgs/");
SalesLists.setHeader("No,NIK,Name,Teritory,Location,Sales,Registered Date");
SalesLists.setInitWidths("60,150,100,100,200,120,120,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
SalesLists.setColAlign("center,left,left,left,left,left,left,center,center,center,center,center,center,center,center,center");
SalesLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
SalesLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
SalesLists.enableSmartRendering(true, 150);
PSalesLists = new dataProcessor("sales-lists-update");
PSalesLists.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PSalesLists.setTransactionMode("POST", true);
PSalesLists.setUpdateMode("Off");
PSalesLists.init(SalesLists);
SalesLists.attachEvent("onRowDblClicked", SalesListsKlik);
SalesLists.setSkin("dhx_skyblue");
SalesLists.init();
refreshdata();

function refreshdata(){
	SalesLists.clearAndLoad('xml-master-sales');

}

function delsSales(){
	dhtmlx.confirm('Cancel this Sales?', 'Sales', function(r) {
			
			if (r==true){
				SalesLists.deleteSelectedRows();
				PSalesLists.sendData();
					alert('Data Successfully Deleted!');
					setTimeout(function(){
						refreshdata();
					},1000);
			}
	})
}


function UpdateGrid_Sales() {
	var out = 0;
    for (var i = 0; i < SalesLists.getRowsNum(); i++) {
		rowIdMov=SalesLists.getRowId(i);
        SalesListsProc.setUpdated(rowIdMov,true);
    }
	return out;
}

function SubmitGrid_Sales(){
	SalesListsProc.sendData();
	alert('Data Successfully Updated!');
	SalesLists.clearAll();
	refreshdata();
}


</script>
@endsection