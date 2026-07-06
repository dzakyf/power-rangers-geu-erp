<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Opportunity</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Fresh Leads</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="OpportunityLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselOpportunityListsDet" name="ekselOpportunityListsDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="OpportunityLists.toExcel('./export/generate.php');" />
		<input type="button" id="detailLeads" name="detailLeads" value="Detail Leads" class="btn btn-info mt-2 mr-2" onclick="detailLeads()" />
		<input type="button" id="assignToSales" name="assignToSales" value="Assign to Sales" class="btn btn-primary mt-2 mr-2" onclick="assignToSales()" />
		<div id="divAssignToSales" style="display: none;">
			<select name="Sales" id="Sales" class="mr-2">
				<option value="">-- Pilih Sales --</option>
				@foreach($pic as $p)
				<option value="{{ $p->username }}">{{ $p->Name }}</option>
				@endforeach
			</select>
			<input type="button" id="assign" name="assign" value="Assign" class="btn btn-success mt-2 mr-2" onclick="assign()" />
		</div>

		<input type="file" id="csvFileInput" accept=".csv" style="display: none;">
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<div id="divLeadsDetail" style="display:none;">
	@include('partner.leads-detail')
</div>
<script>
	function assignToSales() {
		if ($('#divAssignToSales').css('display') == 'none') {
			$('#divAssignToSales').css('display', 'inline-block');
		} else {
			$('#divAssignToSales').css('display', 'none');
			$('#Sales').val('');
		}
	}

	function assign() {
		let sales = $('#Sales').val()
		let ids = OpportunityLists.getChangedRows().split(',').filter(id => id != '');
		console.log(ids);

		let checker = 0;
		for (let i = 0; i < ids.length; i++) {
			const ele = ids[i];
			console.log(OpportunityLists.cells(ele, 1).getValue());
			if (OpportunityLists.cells(ele, 1).getValue() == 1) {
				checker++;
			}
		}

		if (checker == 0 || ids.length == 0) {
			alert('Tidak dapat assign ke sales, pastikan checklist ada dicentang!');
			return;
		}

		if (sales == '') {
			alert("Pilih sales terlebih dahulu!")
			return
		}
		POpportunityLists.sendData();
	}
	$('#csvFileInput').on('change', function() {
		let file = this.files[0]

		ImportCSV(file)
	})

	function downloadCSVTemplate() {
		$.ajax({
			url: "download-leads-template",
			type: "GET",
			xhrFields: {
				responseType: 'blob'
			},
			success: function(response, status, xhr) {
				var blob = new Blob([response]);
				var link = document.createElement('a');
				link.href = window.URL.createObjectURL(blob);
				link.download = 'import-leads-template.xlsx';
				link.click();

				window.URL.revokeObjectURL(link.href);
			},
			error: function(err) {
				alert("Telah terjadi kesalahan.");
			}
		})
	}

	function ImportCSV(file) {
		let fd = new FormData()

		fd.append('csv_file', file)
		fd.append('_token', '{{ csrf_token() }}')

		$.ajax({
			url: "upload-csv",
			data: fd,
			type: "POST",
			processData: false,
			contentType: false,
			success: function(res) {
				alert("Berhasil mengunggah file!")
				$('#csvFileInput').val(null)
			},
			error: function() {
				alert("Gagal mengunggah file!")
			}
		})
	}

	function OpportunityListsKlik() {

		OpportunityListsID = OpportunityLists.getSelectedId();
		$("#divlistOpportunity").show();
		if (OpportunityListsID != '') {
			$("#divlistProjectList").hide();
			LoadM('divlistOpportunity', 'partner-fresh-leads-detail?ID=' + OpportunityListsID);
		}
		return true;
	}
	OpportunityLists = new dhtmlXGridObject('OpportunityLists');
	OpportunityLists.setImagePath("./dhtmlx/imgs/");
	OpportunityLists.setHeader("No, Check list, Task ID,Created Person,Full Name, Date, Active Contact, Partnership Interest, Capital Estimation (Modal/RP), Created Date, Source");
	OpportunityLists.attachHeader("#rspan,#master_checkbox,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter");
	// OpportunityLists.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	OpportunityLists.setInitWidths("60,80,150,100,200,100,120,120,200,100,100");
	OpportunityLists.setColAlign("center,center,left,left,left,left,left,left,left,left,left");
	OpportunityLists.setColSorting("int,int,str,str,str,str,str,str,str,str,str");
	OpportunityLists.setColTypes("ro,ch,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	OpportunityLists.enableSmartRendering(true, 150);
	// OpportunityLists.setNumberFormat("0,000", 7, ".", ",");
	POpportunityLists = new dataProcessor("partner-fresh-leads-update");
	POpportunityLists.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	POpportunityLists.setTransactionMode("POST", true);
	POpportunityLists.setUpdateMode("Off");
	POpportunityLists.init(OpportunityLists);
	POpportunityLists.attachEvent("onBeforeDataSending", function(id, state, data) {
		// Modify or add payload data here
		let sales = $('#Sales').val()
		// console.log(data)
		data.customPayload = {
			// Add your payload properties dynamically
			sales: sales,
			// ... and so on
		};
		// console.log(id)
		return true; // Return true to continue processing
	});
	POpportunityLists.attachEvent("onAfterUpdateFinish", function(id, state, data) {
		alert("Data Successfully Submitted!");
		setTimeout(function() {
			refreshdata();
			assignToSales();
		}, 500);
	});
	OpportunityLists.attachEvent("onRowDblClicked", detailLeads);
	OpportunityLists.setSkin("dhx_skyblue");
	OpportunityLists.init();
	refreshdata();

	function refreshdata() {
		OpportunityLists.clearAndLoad('xml-partner-fresh-leads');
	}

	function delsOpportunity() {
		dhtmlx.confirm('Cancel this Opportunity?', 'Opportunity', function(r) {

			if (r == true) {
				OpportunityLists.deleteSelectedRows();
				POpportunityLists.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}


	function detailLeads() {
		var id = OpportunityLists.getSelectedId();
		if (!id) {
			alert('Pilih Leads terlebih dulu');
			return;
		}
		$('#ID').val(id);
		$('#divLeadsDetail').show();
		getLeads();
	}


	function UpdateGrid_Opportunity() {
		var out = 0;
		for (var i = 0; i < OpportunityLists.getRowsNum(); i++) {
			rowIdMov = OpportunityLists.getRowId(i);
			OpportunityListsProc.setUpdated(rowIdMov, true);
		}
		return out;
	}

	function SubmitGrid_Opportunity() {
		OpportunityListsProc.sendData();
		alert('Data Successfully Updated!');
		OpportunityLists.clearAll();
		refreshdata();
	}

	function EditOpportunity() {
		taskid = OpportunityLists.cells(OpportunityLists.getSelectedId(), 1).getValue();
		LoadM('divlistOpportunity', 'sales-opportunity-lists-detail?ID=' + taskid);
	}

	function LihatDetailOpportunityLists() {
		taskid = OpportunityLists.cells(OpportunityLists.getSelectedId(), 1).getValue();
		LoadM('divlistOpportunity', 'sales-opportunity-activity?taskid=' + taskid);
	}

	function listDoc() {

		DocumentListsDetailID = OpportunityLists.cells(OpportunityLists.getSelectedId(), 12).getValue();

		$("#divlistProjectList").show();

		if (DocumentListsDetailID != '') {
			$("#divlistOpportunity").hide();
			LoadM('divlistProjectList', 'project-documents-detail?Task_ID=' + DocumentListsDetailID);
		}
		return true;
	}
</script>
@endsection