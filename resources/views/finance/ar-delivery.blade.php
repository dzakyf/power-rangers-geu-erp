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
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Account Receive</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Delivery Invoice</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="DeliveryInvoice" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselDeliveryInvoiceDet" name="ekselDeliveryInvoiceDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="DeliveryInvoice.toExcel('./export/generate.php');" />
		<input type="button" value="Update" id="ReceiveAR" class="btn btn-primary mt-2 mr-2" style="width:160px;" />
	</div>
	<div id="divlistcustomer"></div>
</div>

<script>
	$("#ReceiveAR").click(function() {
		dhtmlx.confirm('Confirm Customer Received the Invoice?', 'Receive Invoice', function(r) {
			if (r == true) {



				pDeliveryInvoice.sendData();
				alert('Invoice Confirmed!');
				setTimeout(function() {
					refreshdata();
				}, 2000);

				return true;

			}
		})
	});

	DeliveryInvoice = new dhtmlXGridObject('DeliveryInvoice');
	DeliveryInvoice.setImagePath("./dhtmlx/imgs/");
	DeliveryInvoice.setHeader("No.,Invoice Date,Invoice No,Subject,Invoice Type,Total AR,Due Date,Delivery Date,Receive,Collector,Date Receive,PIC Receive,Delivery Note,Account No, Account Name,User ID");
	DeliveryInvoice.setColValidators(",,,,,,,,,NotEmpty,NotEmpty,NotEmpty,NotEmpty");

	DeliveryInvoice.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan,#rspan,#rspan,#rspan,#rspan,#text_filter,#text_filter,#text_filter");
	DeliveryInvoice.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total");
	DeliveryInvoice.setInitWidths("60,120,100,100,150,100,100,100,100,100,120,100,100,100,100,100,100");
	DeliveryInvoice.setColAlign("center,center,center,center,center,right,right,right,center,center,left,center,center,center,center,center,center");
	DeliveryInvoice.setColTypes("ro,ro,ro,ro,ro,ron,dhxCalendar,dhxCalendar,ch,ed,dhxCalendar,ed,ed,ed,ro,ro,ro");
	DeliveryInvoice.setEditable(true);
	DeliveryInvoice.setSkin("dhx_skyblue");
	DeliveryInvoice.setDateFormat("%Y-%m-%d");
	DeliveryInvoice.setNumberFormat("0,000.00", 5, ".", ",");
	pDeliveryInvoice = new dataProcessor("finance-ar-delivery-update");
	pDeliveryInvoice.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})
	pDeliveryInvoice.setUpdateMode("Off");
	pDeliveryInvoice.init(DeliveryInvoice);
	DeliveryInvoice.init();
	refreshdata();

	function refreshdata() {
		DeliveryInvoice.clearAndLoad('xml-finance-ar-delivery');

	}
</script>
@endsection