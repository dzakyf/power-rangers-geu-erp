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
			<span class="active">Signed Lists</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="SignedLists" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselSignedListsDet" name="ekselSignedListsDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="SignedLists.toExcel('./export/generate.php');" />
		<input type="button" id="AddSigned" NAME="AddBCoA" value="Add Data" class="btn btn-success mt-2 mr-2" />
		<input type="button" id="SubmitSigned" NAME="SaveSigned" value="Save Data" class="btn btn-success mt-2 mr-2" />
		<input type="button" id="delets" name="delets" value="Delete Data" onclick="delsSigned()" class="btn btn-danger mt-2 mr-2" />
	</div>

</div>
<div id="divlistSales"></div>
<script>
	SignedLists = new dhtmlXGridObject('SignedLists');
	SignedLists.setImagePath("./dhtmlx/imgs/");
	SignedLists.setHeader("No,Nama, Jabatan, Active,Update Date, Update By");
	SignedLists.attachHeader("#rspan,#text_filter,#text_filter,#combo_filter,#text_filter,#text_filter");
	SignedLists.setInitWidths("60,200,230,120,120,60");
	SignedLists.setColAlign("center,left,left,center,center,center");
	SignedLists.setColSorting("int,str,str,str,str,str");
	SignedLists.setColTypes("ro,ed,ed,coro,ed,ed");
	SignedLists.filterBy(3, function(a) {
		return (a > 0);
	});
	var combobox = SignedLists.getCombo(3);
	combobox.put("1", "ACTIVE");
	combobox.put("0", "NON ACTIVE");
	SignedLists.enableSmartRendering(true, 150);
	PSignedLists = new dataProcessor("master-proforma-signed-update");
	PSignedLists.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})
	PSignedLists.setUpdateMode("On");
	PSignedLists.init(SignedLists);
	SignedLists.setSkin("dhx_skyblue");
	SignedLists.init();
	refreshdata();

	function refreshdata() {
		SignedLists.clearAndLoad('xml-sales-master-proforma-signed');
	}

	$("#AddSigned").click(function() {
		var idbi = SignedLists.uid();
		SignedLists.addRow(idbi, [SignedLists.getRowsNum() + 1, ''], 0);
		SignedLists.selectRow(0);
	});

	$("#SubmitSigned").click(function() {
		PSignedLists.sendData();
		alert('Data Successfully Updated!');
		setTimeout(function() {
			refreshdata();
		}, 1000);
	});

	function delsSigned() {
		dhtmlx.confirm('Remove this Signed?', 'Signed', function(r) {

			if (r == true) {
				SignedLists.deleteSelectedRows();
				PSignedLists.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}
</script>
@endsection