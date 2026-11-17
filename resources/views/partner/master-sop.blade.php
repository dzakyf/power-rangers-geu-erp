<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();

?>

@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Master</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">SOP Compliance</span>
		</li>
	</ol>
</div>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body">
			    @include('partner.partials.index-title', ['title' => 'SOP'])

				<div id="SopGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
				<div class="mt-2">
					<input type="button" id="ekselSop" name="ekselSop" class="btn btn-warning mr-2" value="Export Excel" onclick="SopGrid.toExcel('./export/generate.php');" />
					<input type="button" id="newSop" name="newSop" value="New SOP" class="btn btn-primary mr-2" onclick="newSop()" />
					<input type="button" id="editSop" name="editSop" value="Edit" class="btn btn-info mr-2" onclick="editSop()" />
					<input type="button" id="deleteSop" name="deleteSop" value="Delete" class="btn btn-danger mr-2" onclick="deleteSop()" />
				</div>
			</div>
		</div>
	</div>
</div>

<div id="SopFormDiv"></div>

<script>
	function newSop() {
		LoadM('SopFormDiv', 'partner-master-sop-new');
	}

	function editSop(rowId) {
		var id = rowId || SopGrid.getSelectedId();
		if (!id) {
			alert('Pilih data terlebih dahulu.');
			return;
		}
		LoadM('SopFormDiv', 'partner-master-sop-detail?ID=' + id);
	}

	function deleteSop() {
		var id = SopGrid.getSelectedId();
		if (!id) {
			alert('Pilih data terlebih dahulu.');
			return;
		}
		if (!confirm('Yakin ingin menghapus SOP ini?')) {
			return;
		}

		$.ajax({
			url: 'partner-master-sop-delete',
			type: 'POST',
			data: {
				id: id,
				_token: '{{ csrf_token() }}'
			},
			success: function(res) {
				if (res.status === 'success') {
					alert(res.message || 'SOP berhasil dihapus.');
					$('#SopFormDiv').html('');
					refreshdata();
				} else {
					alert(res.message || 'Gagal menghapus SOP.');
				}
			},
			error: function(xhr) {
				var msg = (xhr.responseJSON && xhr.responseJSON.message)
					? xhr.responseJSON.message
					: 'Gagal menghapus SOP.';
				alert(msg);
			}
		});
	}

	SopGrid = new dhtmlXGridObject('SopGrid');
	SopGrid.setImagePath("./dhtmlx/imgs/");
	SopGrid.setHeader("No, Category, Description, Parameter List, Parameter Score, Status");
	SopGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	SopGrid.setInitWidths("60,150,250,250,150,100");
	SopGrid.setColAlign("center,left,left,left,left,center");
	SopGrid.setColSorting("int,str,str,str,str,str");
	SopGrid.setColTypes("ron,ro,ro,ro,ro,ro");
	SopGrid.enableSmartRendering(true, 150);
	SopGrid.attachEvent('onRowDblClicked', function(rowId) {
		editSop(rowId);
	});
	SopGrid.setSkin("dhx_skyblue");
	SopGrid.init();
	refreshdata();

	function refreshdata() {
		SopGrid.clearAndLoad('xml-partner-master-audit-category');
	}
</script>
@endsection
