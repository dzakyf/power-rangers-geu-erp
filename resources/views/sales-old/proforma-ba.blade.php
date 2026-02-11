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
			<a href="#">Proforma</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">BA COLLECTION</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="BA_Collection" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselBA_CollectionDet" name="ekselBA_CollectionDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="BA_Collection.toExcel('./export/generate.php');" />
		<input type="button" id="ekselBA_CollectionDet2" name="ekselBA_CollectionDet2" class="btn btn-info mt-2 mr-2" value="Reprint BA" onclick="reprintba();" />
		<input type="button" id="ekselBA_CollectionDet2" name="ekselBA_CollectionDet3" class="btn btn-warning mt-2 mr-2" value="Reprint BA LA" onclick="printbala();" />
		<input type="button" id="ekselBA_CollectionDet2" name="ekselBA_CollectionDet4" class="btn btn-success mt-2 mr-2" value="Reprint BA LA 2" onclick="printbala2();" />
		<input type="button" id="delets" name="delets" value="Delete Proforma" onclick="delsBa()" class="btn btn-danger mt-2 mr-2" />

	</div>
	<div id="divlistcustomer"></div>
</div>

<script>
	function reprintba() {
		CustomerListsID = BA_Collection.getSelectedId();

		window.open("badownload?ID=" + CustomerListsID);

	}

	function printbala() {
		CustomerListsID = BA_Collection.getSelectedId();

		window.open("baladownload?ID=" + CustomerListsID);

	}

	function printbala2() {
		CustomerListsID = BA_Collection.getSelectedId();

		window.open("bala2download?ID=" + CustomerListsID);

	}

	function BA_CollectionKlik() {

		CustomerListsID = BA_Collection.getSelectedId();
		bano = BA_Collection.cells(CustomerListsID, 3).getValue();
		if (CustomerListsID != '') {
			if (bano != '') {

				LoadM('divlistcustomer', 'sales-proforma-ba-detail2?ID=' + CustomerListsID);
			} else {
				LoadM('divlistcustomer', 'sales-proforma-ba-detail?ID=' + CustomerListsID);
			}
		}
		return true;
	}
	BA_Collection = new dhtmlXGridObject('BA_Collection');
	BA_Collection.setImagePath("./dhtmlx/imgs/");
	BA_Collection.setHeader("No,Proforma No,Proforma Date,BA No,BA Date,Subject,Pekerjaan,Periode,Sub Total,Man Fee,Amount,Proforma Type,Signed BY(User),No PO,Customer Name,Created Date,Created By");
	BA_Collection.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total",
		["", "", "", "", "", "", "", "", "text-align:right", "text-align:right", "text-align:right"]
	);
	BA_Collection.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	BA_Collection.setInitWidths("60,120,120,120,100,150,200,200,120,120,120,100,110,100,100,100,100,100,100,100,100");
	BA_Collection.setColAlign("center,center,left,left,left,left,left,left,left,left,left,left,left,center,center,center,left,right,left");
	BA_Collection.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,int");
	BA_Collection.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	BA_Collection.enableSmartRendering(true, 150);
	BA_Collection.setNumberFormat("0,000", 8, ".", ",");
	BA_Collection.setNumberFormat("0,000", 9, ".", ",");
	BA_Collection.setNumberFormat("0,000", 10, ".", ",");
	BA_Collection.attachEvent("onRowDblClicked", BA_CollectionKlik);
	//	BA_Collection.enableAutoWidth(true, 220, 600);
	PBA_Collection = new dataProcessor("sales-proforma-ba-update");
	PBA_Collection.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PBA_Collection.setTransactionMode("POST", true);
	PBA_Collection.init(BA_Collection);

	BA_Collection.setSkin("dhx_skyblue");
	BA_Collection.init();


	refreshdata();

	function refreshdata() {
		BA_Collection.clearAndLoad('xml-sales-proforma-ba');

	}

	function delsBa() {
		dhtmlx.confirm('Remove this Proforma?', 'Proforma', function(r) {

			if (r == true) {
				BA_Collection.deleteSelectedRows();
				PBA_Collection.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					// 	refreshdata();
				}, 1000);
			}
		})
	}
</script>
@endsection