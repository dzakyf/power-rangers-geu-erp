@extends('layout.mainlayout-bp')

@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
@endpush

@section('content-wrapper')
@csrf
<div id="gridTip" style="position:fixed;display:none;z-index:9999;background:#333;color:#fff;padding:4px 8px;border-radius:4px;font-size:12px;pointer-events:none;"></div>
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Sales & Marketing</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Pipeline Leads</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PIPELINE LEADS</div>
	<div class="card">
		<div class="card-body">
			<div id="SalesPipelineLeads" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselSalesPipelineLeadsDet" name="ekselSalesPipelineLeadsDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="SalesPipelineLeads.toExcel('./export/generate.php');" />
				<input type="button" id="btnLihatLeads" value="Lihat Leads" class="btn btn-info mt-2 mr-2" />
				<input type="button" value="Ratio Pipeline Leads" class="btn btn-primary mt-2 mr-2" onclick="window.location='partner-sales-ratio-pipeline-leads'" />
			</div>
		</div>
	</div>

	{{-- Checker: leads belonging to the selected sales row --}}
	<div class="card mt-3" id="checkerCard" style="display:none">
		<div class="card-body">
			<div class="d-flex flex-wrap align-items-end mb-3">
				<div class="mr-3 mb-2">
					<label class="d-block mb-1">Sales</label>
					<input type="text" id="checkerSalesLabel" class="form-control" value="-" readonly style="min-width:180px;">
				</div>
				<div class="mr-3 mb-2">
					<label class="d-block mb-1">Stage</label>
					<select id="filterStage" class="form-control" style="min-width:160px;">
						<option value="">-- Semua Stage --</option>
					</select>
				</div>
				<div class="mr-3 mb-2">
					<label class="d-block mb-1">Response</label>
					<select id="filterResponse" class="form-control" style="min-width:160px;">
						<option value="">-- Semua Response --</option>
						<option value="Response">Response</option>
						<option value="No Response">No Response</option>
					</select>
				</div>
				<div class="mb-2">
					<button type="button" class="btn btn-info" id="btnListLeads">List of Leads</button>
				</div>
			</div>
			<div class="table-responsive">
				<table id="pipelineLeadsTable" class="table table-bordered table-sm" style="width:100%">
					<thead>
						<tr>
							<th>Lead ID</th>
							<th>Full Name</th>
							<th>Response</th>
							<th>Qualification</th>
							<th>Classification</th>
							<th>Status</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>

	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	SalesPipelineLeads = new dhtmlXGridObject('SalesPipelineLeads');
	SalesPipelineLeads.setImagePath("./dhtmlx/imgs/");
	SalesPipelineLeads.setHeader("No,Sales, Total Leads, Response, No Response, Qualified, Unqualified, Unidentified, Cold, Meeting 1, Meeting 2, Warm, Deal, Cancel");
	SalesPipelineLeads.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	SalesPipelineLeads.attachFooter("Total,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
	SalesPipelineLeads.setInitWidths("60,200,100,100,100,100,100,100,100,100,100,100,100,100");
	SalesPipelineLeads.setColAlign("center,left,right,right,right,right,right,right,right,right,right,right,right,right");
	SalesPipelineLeads.setColSorting("int,str,int,int,int,int,int,int,int,int,int,int,int,int");
	SalesPipelineLeads.setColTypes("ron,ro,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron");
	SalesPipelineLeads.setNumberFormat("0,000",2,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",3,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",4,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",5,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",6,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",7,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",8,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",9,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",10,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",11,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",12,",",".");
	SalesPipelineLeads.setNumberFormat("0,000",13,",",".");
	SalesPipelineLeads.enableSmartRendering(true, 150);
	SalesPipelineLeads.setSkin("dhx_skyblue");
	SalesPipelineLeads.init();
	refreshdata();

	function refreshdata() {
		SalesPipelineLeads.clearAndLoad('xml-partner-sales-pipeline-leads');
	}
</script>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
<script>
	var pipelineTable = null;
	var selectedSalesRef = null;

	$(document).ready(function() {
		// Populate the Stage filter from the ordered master.
		$.get('master-lead-status', function(res) {
			(res.data || []).forEach(function(row) {
				$('#filterStage').append('<option value="' + row.status + '">' + row.status + '</option>');
			});
		});

		SalesPipelineLeads.attachEvent('onRowSelect', function(id) {
			selectedSalesRef = id;
			$('#checkerSalesLabel').val(SalesPipelineLeads.cells(id, 1).getValue());
			if ($('#checkerCard').is(':visible')) {
				loadPipelineLeads();
			}
		});

		$('#btnListLeads').on('click', function() {
			loadPipelineLeads();
		});

		$('#btnLihatLeads').on('click', function() {
			var $card = $('#checkerCard');
			$card.toggle();
			if ($card.is(':visible')) {
				initPipelineTable();
				if (selectedSalesRef !== null) {
					loadPipelineLeads();
				}
			}
		});
		$('#filterStage, #filterResponse').on('change', function() {
			if (selectedSalesRef !== null) loadPipelineLeads();
		});
	});

	function initPipelineTable() {
		if (pipelineTable) return pipelineTable;
		pipelineTable = $('#pipelineLeadsTable').DataTable({
			data: [],
			columns: [{
					data: 'Task_ID'
				},
				{
					data: 'Full_Name'
				},
				{
					data: 'Response'
				},
				{
					data: 'Qualification'
				},
				{
					data: 'Classification'
				},
				{
					data: 'Status'
				}
			],
			order: [
				[0, 'desc']
			],
			paging: true,
			pageLength: 10,
			searching: true,
			language: {
				emptyTable: 'Pilih baris sales, lalu klik "List of Leads".',
				zeroRecords: 'Tidak ada data',
				search: 'Cari:'
			}
		});
		return pipelineTable;
	}

	function loadPipelineLeads() {
		if (!selectedSalesRef) {
			alert('Pilih baris sales terlebih dahulu.');
			return;
		}
		initPipelineTable();
		$.ajax({
			url: 'partner-leads',
			method: 'GET',
			data: {
				sales: selectedSalesRef,
				status: $('#filterStage').val(),
				response: $('#filterResponse').val()
			},
			success: function(res) {
				pipelineTable.clear().rows.add(res.data || []).draw();
			},
			error: function() {
				alert('Gagal memuat daftar leads.');
			}
		});
	}

	var TIP_COLS = {2:'total_leads',3:'response',4:'no_response',5:'qualified',6:'unqualified',7:'unidentified',8:'cold',9:'meeting_1',10:'meeting_2',11:'warm',12:'deal',13:'cancel_total'};
	var TIP_LABELS = {total_leads:'Total Leads',response:'Response',no_response:'No Response',qualified:'Qualified',unqualified:'Unqualified',unidentified:'Unidentified',cold:'Cold',meeting_1:'Meeting 1',meeting_2:'Meeting 2',warm:'Warm',deal:'Deal',cancel_total:'Cancel'};

	function showGridTip(grid, rowId, colIndex) {
		var key = TIP_COLS[colIndex];
		if (!key) { hideGridTip(); return; }
		var val = grid.getRowAttribute(rowId, 't_' + key);
		if (!val) { hideGridTip(); return; }
		var $tip = $('#gridTip');
		$tip.text(TIP_LABELS[key] + ': ' + val).show();
		try {
			var cell = grid.cells(rowId, colIndex).cell;
			if (cell) {
				var rect = cell.getBoundingClientRect();
				$tip.css({ top: (rect.top - $tip.outerHeight() - 6) + 'px', left: rect.left + 'px' });
			}
		} catch (e) {}
	}
	function hideGridTip() { $('#gridTip').hide(); }
	SalesPipelineLeads.attachEvent('onMouseOver', function(rowId, colIndex) { showGridTip(SalesPipelineLeads, rowId, colIndex); });
	SalesPipelineLeads.attachEvent('onMouseOut', function() { hideGridTip(); });
	$('#SalesPipelineLeads').on('mouseleave', hideGridTip);

</script>
@endpush
