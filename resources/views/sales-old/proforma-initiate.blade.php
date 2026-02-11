<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
$Task_ID = "PIT-".date("Y-m-d")."-".$LSP.date("-his");	
	
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Sales</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Proforma</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Collection</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProformaInitiate" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselProformaInitiateDet" name="ekselProformaInitiateDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProformaInitiate.toExcel('./export/generate.php');"  />
			<input type="button" id="ProSubmt" NAME="ProSubmt" value="Process" onclick="UpdatePO2()" class="btn btn-success mt-2 mr-2"/>

		</div>

	</div>	
		<div id="divlistOpportunity"></div>
<script>

function UpdatePO2(){
	PProformaInitiate.sendData();
	
	setTimeout(function(){
		alert('Data PO Collection Submitted!');
		
	},1000);

	
}
ProformaInitiate = new dhtmlXGridObject('ProformaInitiate');
ProformaInitiate.setImagePath("./dhtmlx/imgs/");
ProformaInitiate.setHeader("Select,PO No,Subject,PO Start Date,PO End Date,Contract No,Amount,Customer No,Customer Name,PIC,Note,Num of Invoiced");
ProformaInitiate.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ProformaInitiate.setInitWidths("60,120,220,100,100,100,80,150,100,100,100,100");
ProformaInitiate.setColAlign("center,center,left,center,center,right,center,center,center,center,left,right");
ProformaInitiate.setColSorting("int,str,str,str,str,str,str,str,str,str,str,int");
ProformaInitiate.setColTypes("ch,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro");
ProformaInitiate.enableSmartRendering(true, 150);
ProformaInitiate.setNumberFormat("0,000",6,".",",");
PProformaInitiate = new dataProcessor("sales-proforma-initiate-update");
PProformaInitiate.setTransactionMode({
 mode: "POST",
  total:true,
  headers:{
	'X-CSRF-TOKEN': "{{ csrf_token() }}"
  }	,
  payload: {"Task_ID":"<?php echo $Task_ID ?>"}

})
PProformaInitiate.setUpdateMode("Off");
PProformaInitiate.init(ProformaInitiate);
ProformaInitiate.setSkin("dhx_skyblue");
ProformaInitiate.init();
refreshdata();

function refreshdata(){
	ProformaInitiate.clearAndLoad('xml-sales-proforma-initiate');

}
</script>
@endsection