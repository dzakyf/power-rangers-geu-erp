<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	?>

@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Finance</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Account Payable</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Request Payment</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ReceiveInvoice" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselReceiveInvoiceDet" name="ekselReceiveInvoiceDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ReceiveInvoice.toExcel('./export/generate.php');"  />
        <input type="button" value="Detail Data" id="detailAP"   class="btn btn-warning mt-2 mr-2"  style="width:160px;"/>
        <input type="button" value="Confirm to Request" id="ReceiveAR"   class="btn btn-primary mt-2 mr-2"  style="width:160px;"/>
        <input type="button" value="Reject Invoice" id="rejectInv"   class="btn btn-danger mt-2 mr-2"  style="width:160px;"/>
		</div>
	</div>	
<div id="divlistcustomer"></div>
	
<script>
$("#detailAP").click(function() {
	APReceiveID = ReceiveInvoice.getSelectedId();
	LoadM('divlistcustomer','finance-ap-request-detail?ID=' + APReceiveID);
	
});
$("#ReceiveAR").click(function() {
	dhtmlx.confirm('Confirm to Request Payment Now?', 'Request Payment', function(r) {
		if (r==true){
			
			
			
			pReceiveInvoice.sendData();
			alert('Request Submitted!');
			setTimeout(function(){
				refreshdata();
			},2000);
			
			return true;
			
		}
	})
});
$("#rejectInv").click(function() {
	dhtmlx.confirm('Reject to Receive?', 'Request Payment', function(r) {
		if (r==true){
			
			ReceiveInvoice.deleteSelectedRows();
			pReceiveInvoice.sendData();

			alert('Invoice Rejected!');
			setTimeout(function(){
				refreshdata();
			},2000);
			
			return true;
			
		}
	})
});
ReceiveInvoice = new dhtmlXGridObject('ReceiveInvoice');
ReceiveInvoice.setImagePath("./dhtmlx/imgs/");
ReceiveInvoice.setHeader("No.,Invoice No,Invoice Date,PO No,Reference,Total AR,Due Date,Subject Payment,Confirm,Note Confirm/Reject, Vendor Name,Date Receive,Department");
ReceiveInvoice.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan,#text_filter,#text_filter,#text_filter,#combo_filter");
ReceiveInvoice.setInitWidths("60,120,100,100,150,100,100,100,60,200,280,120,100");
ReceiveInvoice.setColAlign("center,center,center,center,center,right,right,center,center,center,left,center,center");
ReceiveInvoice.setColTypes("ro,ro,ro,ro,ro,ron,ron,ro,ch,ed,ro,ro,ro");
ReceiveInvoice.setEditable(true);
ReceiveInvoice.setSkin("dhx_skyblue");
ReceiveInvoice.setDateFormat("%Y-%m-%d");
ReceiveInvoice.setNumberFormat("0,000.00",5,".",",");
pReceiveInvoice = new dataProcessor("finance-ap-request-update");
	pReceiveInvoice.setTransactionMode({
	 mode: "POST",
	  total:true,
	  headers:{
		'X-CSRF-TOKEN': "{{ csrf_token() }}"
	  }	,
	
	})
	pReceiveInvoice.setUpdateMode("Off");
	pReceiveInvoice.init(ReceiveInvoice);
ReceiveInvoice.init();
refreshdata();

function refreshdata(){
	ReceiveInvoice.clearAndLoad('xml-finance-ap-request');

}
</script>
@endsection