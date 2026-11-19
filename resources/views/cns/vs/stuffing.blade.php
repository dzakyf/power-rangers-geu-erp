<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<style>
	.stuffing-tab-card {
		border: 1px solid #dee2e6;
		border-radius: 6px;
		background: #ffffff;
		box-shadow: 0 1px 3px rgba(0,0,0,0.05);
	}
	.stuffing-legend-box {
		display: inline-block;
		width: 14px;
		height: 14px;
		border-radius: 3px;
		vertical-align: middle;
		margin-right: 5px;
		border: 1px solid rgba(0,0,0,0.15);
	}
</style>

<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Stuffing</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Stuffing</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">STUFFING</div>

	<div class="stuffing-tab-card w-100">
		<div class="card-header p-0 bg-transparent border-0">
			<ul class="nav nav-tabs m-0" id="stuffingTabs" role="tablist">
				<li class="nav-item" role="presentation">
					<a class="nav-link active" id="tab-schedule-link" data-bs-toggle="tab" data-bs-target="#tab-schedule" data-toggle="tab" href="#tab-schedule" role="tab" aria-controls="tab-schedule" aria-selected="true">
						<i class="fe fe-anchor text-primary" style="font-size: 15px;"></i>
						<span>Jadwal Stuffing (Vessel)</span>
					</a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="nav-link" id="tab-monitoring-link" data-bs-toggle="tab" data-bs-target="#tab-monitoring" data-toggle="tab" href="#tab-monitoring" role="tab" aria-controls="tab-monitoring" aria-selected="false">
						<i class="fe fe-truck text-success" style="font-size: 15px;"></i>
						<span>Monitoring Kontainer</span>
					</a>
				</li>
			</ul>
		</div>

		<div class="card-body p-3">
			<div class="tab-content" id="stuffingTabContent">
				<!-- TAB 1: JADWAL STUFFING (VESSEL) -->
				<div class="tab-pane fade show active" id="tab-schedule" role="tabpanel" aria-labelledby="tab-schedule-link">
					<!-- Status Legend -->
					<div class="d-flex align-items-center flex-wrap mb-3 p-2 bg-light rounded border" style="font-size: 12px; gap: 15px;">
						<span class="text-secondary font-weight-bold"><i class="fe fe-info mr-1"></i> Status:</span>
						<span><span class="stuffing-legend-box" style="background-color: #ffcccc;"></span> Stuffing Not Started</span>
						<span><span class="stuffing-legend-box" style="background-color: #feffcc;"></span> Stuffing Yet Complete</span>
						<span><span class="stuffing-legend-box" style="background-color: #ccffcf;"></span> Stuffing Complete (Done/Kapal Berangkat checked)</span>
					</div>

					<!-- Filter Bar -->
					<div class="d-flex align-items-center flex-wrap mb-3" style="gap: 10px;">
						<span class="font-weight-bold text-secondary" style="font-size: 13px;">Periode:</span>
						<input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control form-control-sm" style="width: 160px;" />
						<span class="text-muted">s/d</span>
						<input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" style="width: 160px;" />
						<button onclick="refreshdata()" class="btn btn-sm btn-primary">Refresh</button>
					</div>

					<div id="StuffingVessel" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>

					<div class="mt-3">
						<input type="button" id="CreateStuffing" name="CreateStuffing" value="Create Stuffing" class="btn btn-success mr-2" onclick="createStuffingTab1()" />
						<input type="button" id="DetailStuffing" name="DetailStuffing" value="Detail Stuffing" class="btn btn-primary mr-2" onclick="StuffingVesselKlik()" />
						<input type="button" id="openShipmentLink" name="openShipmentLink" value="Open ShipmentLink" class="btn btn-secondary mr-2" onclick="openShipmentLink()" />
						<input type="button" id="generateStuffingReport" name="generateStuffingReport" value="Generate Stuffing Report" class="btn btn-success mr-2" onclick="generateStuffingReport()" />
						<input type="button" id="ekselStuffingVesselDet" name="ekselStuffingVesselDet" class="btn btn-warning mr-2" value="Export Excel" onclick="StuffingVessel.toExcel('./export/generate.php');" />
					</div>

					<div id="divlistOpportunity" class="mt-3"></div>
					<div id="DivProcess"></div>
				</div>

				<!-- TAB 2: MONITORING KONTAINER -->
				<div class="tab-pane fade" id="tab-monitoring" role="tabpanel" aria-labelledby="tab-monitoring-link">
					<!-- Filter Bar -->
					<div class="d-flex align-items-center flex-wrap mb-3" style="gap: 10px;">
						<span class="font-weight-bold text-secondary" style="font-size: 13px;">Periode:</span>
						<input type="date" id="mon_tgl1" name="mon_tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control form-control-sm" style="width: 160px;" />
						<span class="text-muted">s/d</span>
						<input type="date" id="mon_tgl2" name="mon_tgl2" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" style="width: 160px;" />
						<button onclick="refreshMonitoringData()" class="btn btn-sm btn-primary">Refresh</button>
					</div>

					<div id="StuffingMonitoringGrid" style="width:auto;height:340px; background-color:white;overflow:hidden"></div>

					<div class="mt-3">
						<button type="button" class="btn btn-success mr-2" onclick="addStuffingContainer()">Add Container</button>
						<button type="button" class="btn btn-info mr-2" onclick="editStuffingContainer()">Edit Container</button>
						<button type="button" class="btn btn-danger mr-2" onclick="deleteStuffingContainer()">Delete Container</button>
						<button type="button" class="btn btn-primary mr-2" onclick="openDetailFromMonitoring()">Buka Detail Stuffing</button>
						<input type="button" id="ekselMonitoringGrid" class="btn btn-warning mr-2" value="Export Excel" onclick="StuffingMonitoringGrid.toExcel('./export/generate.php');" />
					</div>

					<!-- Form Tambah / Edit Container Stuffing -->
					<div class="card mt-3 shadow-sm border" id="stuffingFormCard" style="display:none;">
						<div class="card-body">
							<div class="JudulContent" id="stuffingFormTitle" style="color:#699;background-image:url(hdr_back.gif);">BUAT FORM STUFFING MONITORING</div>
							<form id="StuffingMonitoringForm" enctype="multipart/form-data">
								<input type="hidden" name="id" id="Stuffing_ID" value="" />
								<div class="row">
									<div class="col-md-4 col-12">
										<div class="form-group">
											<label for="Order_No" class="font-weight-bold">Order No <span class="text-danger">*</span></label>
											<select name="Order_No" id="Order_No" class="form-control shadow-sm" required>
												<option value="">-- Pilih Order No --</option>
												@if(isset($vss))
													@foreach ($vss as $vs)
														<option value="{{ $vs->Order_No }}">{{ $vs->Order_No }} - {{ $vs->Vessel }}</option>
													@endforeach
												@endif
											</select>
										</div>
									</div>
									<div class="col-md-2 col-12">
										<div class="form-group">
											<label for="Date" class="font-weight-bold">Tanggal <span class="text-danger">*</span></label>
											<input type="date" name="Date" id="Date" value="{{ date('Y-m-d') }}" class="form-control shadow-sm" required />
										</div>
									</div>
									<div class="col-md-2 col-12">
										<div class="form-group">
											<label for="Time_From">Jam Awal</label>
											<input type="time" name="Time_From" id="Time_From" class="form-control shadow-sm" />
										</div>
									</div>
									<div class="col-md-2 col-12">
										<div class="form-group">
											<label for="Time_To">Jam Akhir</label>
											<input type="time" name="Time_To" id="Time_To" class="form-control shadow-sm" />
										</div>
									</div>
									<div class="col-md-2 col-12">
										<div class="form-group">
											<label for="Police_No">Nopol</label>
											<input type="text" name="Police_No" id="Police_No" class="form-control shadow-sm" />
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-4 col-12">
										<div class="form-group">
											<label for="Container" class="font-weight-bold">Kontainer <span class="text-danger">*</span></label>
											<input type="text" name="Container" id="Container" class="form-control shadow-sm" required />
										</div>
									</div>
									<div class="col-md-4 col-12">
										<div class="form-group">
											<label for="Seal">Seal</label>
											<input type="text" name="Seal" id="Seal" class="form-control shadow-sm" />
										</div>
									</div>
									<div class="col-md-2 col-12">
										<div class="form-group">
											<label for="Tare">Tare</label>
											<input type="text" inputmode="decimal" name="Tare" id="Tare" class="form-control shadow-sm cns-decimal" value="0.00" />
										</div>
									</div>
									<div class="col-md-2 col-12">
										<div class="form-group">
											<label for="Netto_KG">Net (Kg)</label>
											<input type="text" inputmode="decimal" name="Netto_KG" id="Netto_KG" class="form-control shadow-sm cns-decimal" value="0.00" />
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-3 col-12">
										<div class="form-group">
											<label for="Netto_MT">Net (MT)</label>
											<input type="text" name="Netto_MT" id="Netto_MT" class="form-control shadow-sm bg-white" value="0.00" readonly />
										</div>
									</div>
									<div class="col-md-3 col-12">
										<div class="form-group">
											<label for="Gross_KG">Gross (Kg)</label>
											<input type="text" name="Gross_KG" id="Gross_KG" class="form-control shadow-sm bg-white" value="0.00" readonly />
										</div>
									</div>
									<div class="col-md-3 col-12">
										<div class="form-group">
											<label for="Gross_MT">Gross (MT)</label>
											<input type="text" name="Gross_MT" id="Gross_MT" class="form-control shadow-sm bg-white" value="0.00" readonly />
										</div>
									</div>
									<div class="col-md-3 col-12">
										<div class="form-group">
											<label for="Note">Note</label>
											<input type="text" name="Note" id="Note" class="form-control shadow-sm" />
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6 col-12">
										<div class="form-group">
											<label for="Weighbridge_Note">Upload Nota Timbang</label>
											<input type="file" name="Weighbridge_Note" id="Weighbridge_Note" class="form-control shadow-sm" accept=".pdf,.jpg,.jpeg,.png" />
											<small class="text-muted">PDF/JPG/JPEG/PNG, maksimal 8 MB. Kosongkan jika tidak ingin mengganti nota.</small>
										</div>
									</div>
									<div class="col-md-6 col-12">
										<div class="form-group">
											<label>Nota Timbang Saat Ini</label>
											<div id="currentWeighbridgeNote" class="form-control bg-white" style="min-height:38px;height:auto;">Tidak ada nota.</div>
										</div>
									</div>
								</div>
								<div class="mt-2">
									<button type="submit" id="SaveStuffingMonitoring" class="btn btn-success mr-2">Simpan</button>
									<button type="button" id="CancelStuffingMonitoring" class="btn btn-secondary">Batal</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	function openShipmentLink()
	{
		window.open("https://www.shipmentlink.com","_blank");
	}

	function generateStuffingReport()
	{
		let selectedId = StuffingVessel.getSelectedId();

		if (!selectedId) {
			alert("Pilih baris data");
			return;
		}

		window.open('cns-stuffing-report?task=' + selectedId, '_blank');
	}

	function StuffingVesselKlik() {
		StuffingVesselID = StuffingVessel.getSelectedId();
		if (StuffingVesselID != null) {
			LoadM('divlistOpportunity', 'cns-stuffing-detail?task=' + StuffingVesselID);
		} else {
			alert("Pilih baris data");
		}
		return true;
	}

	function createStuffingTab1() {
		let selectedId = StuffingVessel.getSelectedId();
		if (selectedId) {
			StuffingVesselKlik();
			let checkInterval = setInterval(function() {
				if (typeof addStuffingContainer === 'function') {
					addStuffingContainer();
					clearInterval(checkInterval);
				}
			}, 250);
			setTimeout(function() { clearInterval(checkInterval); }, 4000);
		} else {
			$('#stuffingTabs a[href="#tab-monitoring"]').tab('show');
			addStuffingContainer();
		}
	}

	StuffingVessel = new dhtmlXGridObject('StuffingVessel');
	StuffingVessel.setImagePath("./dhtmlx/imgs/");
	StuffingVessel.setHeader("No,Booking Confirmation (BC),Qty,Kapal,POL,POD,Stuffing,Checking,Closing CY,ETD,ATD,ETA,ATA,Estimated Day(s), Actual Day(s), Note, Container (Seal)"); 
	StuffingVessel.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	StuffingVessel.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total");
	StuffingVessel.setInitWidths("60,150,100,200,150,150,100,100,100,100,100,100,100,100,100,100,800");
	StuffingVessel.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
	StuffingVessel.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	StuffingVessel.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	StuffingVessel.enableSmartRendering(true, 150);
	StuffingVessel.attachEvent("onRowDblClicked", StuffingVesselKlik);
	StuffingVessel.setSkin("dhx_skyblue");
	StuffingVessel.init();
	refreshdata();

	function refreshdata() {
		StuffingVessel.clearAndLoad('xml-cns-stuffing?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());
	}

	// -----------------------------------------
	// TAB 2: MONITORING KONTAINER CRUD
	// -----------------------------------------
	var StuffingMonitoringGrid = null;
	var monitoringGridInited = false;

	function initMonitoringGrid() {
		if (monitoringGridInited) {
			if (StuffingMonitoringGrid) {
				StuffingMonitoringGrid.setSizes();
			}
			return;
		}

		StuffingMonitoringGrid = new dhtmlXGridObject('StuffingMonitoringGrid');
		StuffingMonitoringGrid.setImagePath("./dhtmlx/imgs/");
		StuffingMonitoringGrid.setHeader("No,Order No,Jam Awal,Jam Akhir,Tanggal,Nopol,Kontainer,Seal,Tare,Netto (Kg),Netto (MT),Gross (Kg),Gross (MT),Note,Nota Timbang,VesselScheduleID");
		StuffingMonitoringGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan");
		StuffingMonitoringGrid.setInitWidths("60,130,90,90,100,140,140,140,90,100,100,100,100,160,110,0");
		StuffingMonitoringGrid.setColAlign("center,center,center,center,center,center,center,center,right,right,right,right,right,left,center,left");
		StuffingMonitoringGrid.setColSorting("int,str,str,str,str,str,str,str,int,int,int,int,int,str,str,str");
		StuffingMonitoringGrid.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		StuffingMonitoringGrid.enableSmartRendering(true, 150);
		StuffingMonitoringGrid.attachEvent("onRowDblClicked", function(rowId) {
			editStuffingContainer(rowId);
		});
		StuffingMonitoringGrid.setSkin("dhx_skyblue");
		StuffingMonitoringGrid.init();

		monitoringGridInited = true;
		refreshMonitoringData();
	}

	function refreshMonitoringData() {
		if (!StuffingMonitoringGrid) {
			initMonitoringGrid();
			return;
		}
		StuffingMonitoringGrid.clearAndLoad('xml-cns-stuffing-monitoring?tgl1=' + $('#mon_tgl1').val() + "&tgl2=" + $('#mon_tgl2').val());
	}

	function openDetailFromMonitoring() {
		if (!StuffingMonitoringGrid) {
			return;
		}
		let selectedRow = StuffingMonitoringGrid.getSelectedId();
		if (!selectedRow) {
			alert("Pilih baris data kontainer terlebih dahulu.");
			return;
		}
		// Cell index 15 is hidden VesselScheduleID
		let scheduleId = StuffingMonitoringGrid.cells(selectedRow, 15).getValue();
		if (!scheduleId) {
			alert("Vessel Schedule ID tidak ditemukan untuk order ini.");
			return;
		}

		// Switch to Tab 1
		$('#stuffingTabs a[href="#tab-schedule"]').tab('show');

		// Load Detail Stuffing for this schedule
		LoadM('divlistOpportunity', 'cns-stuffing-detail?task=' + scheduleId);
		let target = document.getElementById('divlistOpportunity');
		if (target) {
			target.scrollIntoView({behavior: 'smooth', block: 'start'});
		}
	}

	function calculateStuffingMonitoringValues() {
		let tare = cnsParseNumber($('#Tare').val()) || 0;
		let netto = cnsParseNumber($('#Netto_KG').val()) || 0;
		let nettoMT = netto / 1000;
		let gross = tare + netto;

		$('#Netto_MT').val(cnsFormatNumber(nettoMT));
		$('#Gross_KG').val(cnsFormatNumber(gross));
		$('#Gross_MT').val(cnsFormatNumber(gross / 1000));
	}

	$('#Tare, #Netto_KG').on('input change', calculateStuffingMonitoringValues);

	function setCurrentWeighbridgeNote(url, name) {
		let container = $('#currentWeighbridgeNote').empty();

		if (url) {
			$('<a>', {
				href: url,
				target: '_blank',
				rel: 'noopener'
			}).text(name || 'Lihat Nota Timbang').appendTo(container);
		} else {
			container.text('Tidak ada nota.');
		}
	}

	function resetStuffingMonitoringForm() {
		$('#StuffingMonitoringForm')[0].reset();
		$('#Stuffing_ID').val('');
		$('#Order_No').val('').trigger('change');
		$('#Date').val('{{ date('Y-m-d') }}');
		$('#Tare, #Netto_KG').val('0');
		calculateStuffingMonitoringValues();
		setCurrentWeighbridgeNote(null, null);
	}

	function openStuffingMonitoringForm(title) {
		$('#stuffingFormTitle').text(title);
		$('#stuffingFormCard').show();
		document.getElementById('stuffingFormCard').scrollIntoView({behavior: 'smooth', block: 'start'});
	}

	function addStuffingContainer() {
		resetStuffingMonitoringForm();
		openStuffingMonitoringForm('BUAT FORM STUFFING MONITORING');
	}

	function editStuffingContainer(rowId) {
		let id = (typeof rowId === 'string' || typeof rowId === 'number')
			? rowId
			: (StuffingMonitoringGrid ? StuffingMonitoringGrid.getSelectedId() : null);

		if (!id) {
			alert('Pilih baris data yang ingin diedit.');
			return;
		}

		$.ajax({
			url: 'cns-export-stuffing-monitoring-detail',
			type: 'GET',
			data: {ID: id},
			headers: {'Accept': 'application/json'},
			success: function(response) {
				resetStuffingMonitoringForm();
				$('#Stuffing_ID').val(response.ID);
				$('#Order_No').val(response.Order_No).trigger('change');
				$('#Time_From').val(response.Time_From);
				$('#Time_To').val(response.Time_To);
				$('#Date').val(response.Date);
				$('#Police_No').val(response.Police_No);
				$('#Container').val(response.Container);
				$('#Seal').val(response.Seal);
				$('#Tare').val(cnsFormatNumber(response.Tare));
				$('#Netto_KG').val(cnsFormatNumber(response.Netto_KG));
				$('#Note').val(response.Note);
				calculateStuffingMonitoringValues();
				setCurrentWeighbridgeNote(response.Note_Url, response.Note_Name);
				openStuffingMonitoringForm('EDIT FORM STUFFING MONITORING');
			},
			error: function() {
				alert('Gagal mengambil data container.');
			}
		});
	}

	function deleteStuffingContainer() {
		if (!StuffingMonitoringGrid) {
			return;
		}
		let id = StuffingMonitoringGrid.getSelectedId();

		if (!id) {
			alert('Pilih baris data yang ingin dihapus.');
			return;
		}

		if (!confirm('Hapus container ini?')) {
			return;
		}

		$.ajax({
			url: 'cns-export-stuffing-monitoring-delete',
			type: 'POST',
			data: {_token: '{{ csrf_token() }}', id: id},
			headers: {'Accept': 'application/json'},
			success: function(response) {
				alert(response.message || 'Container berhasil dihapus.');
				if ($('#Stuffing_ID').val() == id) {
					$('#stuffingFormCard').hide();
				}
				refreshMonitoringData();
			},
			error: function() {
				alert('Gagal menghapus container.');
			}
		});
	}

	$('#CancelStuffingMonitoring').on('click', function() {
		$('#stuffingFormCard').hide();
	});

	$('#StuffingMonitoringForm').on('submit', function(e) {
		e.preventDefault();

		let formData = new FormData(this);
		$(this).find('.cns-decimal').each(function() {
			if (this.name) {
				formData.set(this.name, cnsParseNumber($(this).val()));
			}
		});
		formData.append('_token', '{{ csrf_token() }}');
		let button = $('#SaveStuffingMonitoring');

		button.prop('disabled', true).text('Menyimpan...');

		$.ajax({
			url: 'cns-export-stuffing-monitoring-save',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			headers: {'Accept': 'application/json'},
			success: function(response) {
				alert(response.message || 'Data berhasil disimpan.');
				$('#stuffingFormCard').hide();
				resetStuffingMonitoringForm();
				refreshMonitoringData();
			},
			error: function(xhr) {
				if (xhr.responseJSON && xhr.responseJSON.errors) {
					let messages = [];
					Object.keys(xhr.responseJSON.errors).forEach(function(key) {
						messages.push(xhr.responseJSON.errors[key].join('\n'));
					});
					alert(messages.join('\n'));
				} else {
					alert('Gagal menyimpan data.');
				}
			},
			complete: function() {
				button.prop('disabled', false).text('Simpan');
			}
		});
	});

	$(function() {
		// Listen for tab switch to lazily init or resize monitoring grid
		$('a[data-toggle="tab"], a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
			let target = $(e.target).attr('href');
			if (target === '#tab-monitoring') {
				initMonitoringGrid();
			} else if (target === '#tab-schedule') {
				if (typeof StuffingVessel !== 'undefined' && StuffingVessel) {
					StuffingVessel.setSizes();
				}
			}
		});

		// Check for URL parameter ?tab=monitoring
		let urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('tab') === 'monitoring') {
			$('#stuffingTabs a[href="#tab-monitoring"]').tab('show');
			initMonitoringGrid();
		}
	});
</script>
@endsection
