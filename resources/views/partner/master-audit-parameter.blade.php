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
			<span class="active">Parameter</span>
		</li>
	</ol>
</div>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body">
				<div id="MasterAuditParameter" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
				<div>
					<input type="button" id="ekselMasterAuditParameterDet" name="ekselMasterAuditParameterDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="MasterAuditParameter.toExcel('./export/generate.php');" />
					<input type="button" id="newMasterAuditParameter" name="new" value="New Parameter" class="btn btn-primary mt-2 mr-2" onclick="MasterAuditParameterNew()" />
					<input type="button" id="detailMasterAuditParameter" name="detailMasterAuditParameter" value="Detail Parameter" class="btn btn-info mt-2 mr-2" onclick="MasterAuditParameterKlik()" />
					<!-- <input type="button" id="changeStatusMasterAuditParameter" name="changeStatusMasterAuditParameter" value="Change Status" class="btn btn-danger mt-2 mr-2" onclick="MasterAuditParameterStatus()" /> -->
					<input type="button" id="deleteMasterAuditParameter" value="Hapus" class="btn btn-danger mt-2 mr-2" onclick="deleteMasterAuditParameter()" />

					<input type="file" id="csvFileInput" accept=".csv" style="display: none;">
				</div>
			</div>
		</div>
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script>
	function MasterAuditParameterNew() {
		LoadM('divlistOpportunity', 'partner-master-audit-parameter-new');
		return true;
	}

	function MasterAuditParameterKlik() {

		MasterAuditParameterID = MasterAuditParameter.getSelectedId();
		$("#divlistOpportunity").show();
		if (MasterAuditParameterID != '') {
			$("#divlistProjectList").hide();
			LoadM('divlistOpportunity', 'partner-master-audit-parameter-detail?ID=' + MasterAuditParameterID);
		}
		return true;
	}

	function MasterAuditParameterStatus() {

		MasterAuditParameterID = MasterAuditParameter.getSelectedId();
		if (MasterAuditParameterID != '') {
			$.ajax({
				url: "partner-master-audit-parameter-change-status",
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				type: "POST",
				data: {
					param: MasterAuditParameterID
				},
				success: function(data, textStatus, jqXHR) {
					var dataPos = data.status;
					if (dataPos == 'success') {
						// var cariAWB = data.split(":");
						alert('Training Application Submitted!');
						refreshdata();
					} else {
						alert('Application Failed to Submit!');
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert("Error Internal! Please Try again!");

				}
			})
		}
		return true;
	}
	MasterAuditParameter = new dhtmlXGridObject('MasterAuditParameter');
	MasterAuditParameter.setImagePath("./dhtmlx/imgs/");
	MasterAuditParameter.setHeader("No, Category, Parameter Description, Minor, Major, Critical, Status");
	MasterAuditParameter.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// MasterAuditParameter.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	MasterAuditParameter.setInitWidths("60,100,300,200,200,200,150");
	MasterAuditParameter.setColAlign("center,left,left,left,left,left,center");
	MasterAuditParameter.setColSorting("int,str,str,str,str,str,str");
	MasterAuditParameter.setColTypes("ron,ro,ro,ro,ro,ro,ro");
	MasterAuditParameter.enableSmartRendering(true, 150);
	MasterAuditParameter.setDateFormat("%Y-%m-%d");
	MasterAuditParameter.attachEvent('onRowDblClicked', function(rId, cInd) {
		MasterAuditParameterKlik();
	});
	MasterAuditParameter.setSkin("dhx_skyblue");
	MasterAuditParameter.init();
	refreshdata();

	function refreshdata() {
		MasterAuditParameter.clearAndLoad('xml-partner-master-audit-parameter');
	}

	function deleteRow() {
		dhtmlx.confirm('Delete this Row?', 'Opportunity', function(r) {

			if (r == true) {
				MasterAuditParameter.deleteSelectedRows();
				PMasterAuditParameter.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}

	function addRow() {
		let uid = MasterAuditParameter.uid();
		let num = MasterAuditParameter.getRowsNum() + 1;
		MasterAuditParameter.addRow(uid, [num, "{{ date('Y-m-d') }}", '0', '', '']);
		MasterAuditParameter.selectRow(uid);
	}

	function savePrice() {
		PMasterAuditParameter.sendData();
		refreshdata();
	}

	function deleteMasterAuditParameter() {
		var id = MasterAuditParameter.getSelectedId();
		if (!id) {
			alert('Pilih data terlebih dulu');
			return;
		}
		if (!confirm('Yakin ingin menghapus parameter ini?')) return;

		$.ajax({
			url: "partner-master-audit-parameter-delete",
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			type: "POST",
			data: {
				id: id
			},
			success: function(data) {
				if (data.status == 'success') {
					alert('Data berhasil dihapus');
					refreshdata();
				} else {
					alert('Gagal menghapus data');
				}
			},
			error: function() {
				alert("Error Internal!");
			}
		});
	}
</script>
@endsection