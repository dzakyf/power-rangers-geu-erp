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
                <a href="#">Master</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Bin</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="MasterBIN" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselMasterBINDet" name="ekselMasterBINDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="MasterBIN.toExcel('./export/generate.php');"  />
			<input type="button" id="delets" name="delets" value="Delete Bin" onclick="delbin()"  class="btn btn-danger mt-2 mr-2" />
			<input type="button" id="deletsaa" name="deletsaaa" value="Add New" onclick="addNew()" class="btn btn-success mt-2 mr-2"  />
			<input type="button" id="deletsaa" name="deletsaaa" value="Update" onclick="SubmitGrid()"class="btn btn-info mt-2 mr-2"  />
		</div>

	</div>	
<script>
MasterBIN = new dhtmlXGridObject('MasterBIN');
MasterBIN.setImagePath("./dhtmlx/imgs/");
MasterBIN.setHeader("No,Bin ID,Bin Name,Location,Width,Height,Depth");
MasterBIN.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
MasterBIN.setInitWidths("60,150,150,100,100,200,120");
MasterBIN.setColAlign("center,center,left,center,right,right,right");
MasterBIN.setColSorting("int,str,str,str,str,str,str,str");
MasterBIN.setColTypes("ro,ed,ed,ed,ed,ed,ed");
MasterBIN.enableSmartRendering(true, 150);
PMasterBIN = new dataProcessor("asset-master-bin-update");
PMasterBIN.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PMasterBIN.setTransactionMode("POST", true);
PMasterBIN.setUpdateMode("Off");
PMasterBIN.init(MasterBIN);
MasterBIN.setSkin("dhx_skyblue");
MasterBIN.init();
refreshdata();

function refreshdata(){
	MasterBIN.clearAndLoad('xml-asset-master-bin');

}
function addNew(){
	var idbins = MasterBIN.uid();
	MasterBIN.addRow(idbins,[MasterBIN.getRowsNum()+1],0);
}

function delbin(){
	dhtmlx.confirm('Delete This Bin?', 'Bin Location', function(r) {
			
			if (r==true){
				MasterBIN.deleteSelectedRows();
				PMasterBIN.sendData();
					alert('Data Successfully Deleted!');
					setTimeout(function(){
						refreshdata();
					},1000);
			}
	})
	
}
function SubmitGrid(){
	PMasterBIN.sendData();
	alert('Data Bin Updated!');
	refreshdata();
}


</script>
@endsection
