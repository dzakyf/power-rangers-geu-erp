<?php

use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
@include('cns.partials.page-header', [
	'title' => 'Stuffing Monitoring',
	'breadcrumbs' => [
		['label' => 'Export Management'],
		['label' => 'Stuffing Monitoring'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Stuffing Monitoring List</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<button type="button" class="btn btn-sm btn-primary" onclick="addStuffingContainer()">Add Container</button>
			<button type="button" class="btn btn-sm btn-info" onclick="editStuffingContainer()">Edit Container</button>
			<button type="button" class="btn btn-sm btn-danger" onclick="deleteStuffingContainer()">Delete Container</button>
			<input type="button" id="ekselShipmentInstructionDet" name="ekselShipmentInstructionDet" class="btn btn-sm btn-warning" value="Export Excel" onclick="StuffingMonitoring.toExcel('./export/generate.php');" />
			<button type="button" onclick="refreshdataDetail()" class="btn btn-sm btn-outline-secondary">Refresh</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-filter-bar">
			<div>
				<label for="tgl1" class="d-block mb-1"><small>Dari Tanggal</small></label>
				<input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="text-black form-control form-control-sm" style="width:auto;" />
			</div>
			<div>
				<label for="tgl2" class="d-block mb-1"><small>Sampai Tanggal</small></label>
				<input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="text-black form-control form-control-sm" style="width:auto;" />
			</div>
			<div>
				<button type="button" onclick="refreshdataDetail()" class="btn btn-sm btn-primary">Filter</button>
			</div>
		</div>
		<div class="cns-grid-wrapper">
			<div id="StuffingMonitoring" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		</div>
	</div>
</div>

<div class="card cns-card mt-3" id="stuffingFormCard" style="display:none;">
	<div class="card-body">
		<div class="cns-section-header" id="stuffingFormTitle">BUAT FORM STUFFING MONITORING</div>
		<form id="StuffingMonitoringForm" enctype="multipart/form-data">
			<input type="hidden" name="id" id="Stuffing_ID" value="" />
			<div class="row">
				<div class="col-md-4 col-12">
					<div class="form-group">
						<label for="Order_No">Order No</label>
						<select name="Order_No" id="Order_No" class="text-black form-control shadow-sm" required>
							<option value="">-- Pilih Order No --</option>
							@foreach ($vss as $vs)
								<option value="{{ $vs->Order_No }}">{{ $vs->Order_No }} - {{ $vs->Vessel }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Date">Tanggal</label>
						<input type="date" name="Date" id="Date" value="{{ date('Y-m-d') }}" class="text-black form-control shadow-sm" required />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Time_From">Jam Awal</label>
						<input type="time" name="Time_From" id="Time_From" class="text-black form-control shadow-sm" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Time_To">Jam Akhir</label>
						<input type="time" name="Time_To" id="Time_To" class="text-black form-control shadow-sm" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Police_No">Nopol</label>
						<input type="text" name="Police_No" id="Police_No" class="text-black form-control shadow-sm" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-4 col-12">
					<div class="form-group">
						<label for="Container">Kontainer</label>
						<input type="text" name="Container" id="Container" class="text-black form-control shadow-sm" required />
					</div>
				</div>
				<div class="col-md-4 col-12">
					<div class="form-group">
						<label for="Seal">Seal</label>
						<input type="text" name="Seal" id="Seal" class="text-black form-control shadow-sm" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Tare">Tare</label>
						<input type="text" inputmode="decimal" name="Tare" id="Tare" class="text-black form-control shadow-sm cns-decimal" value="0.00" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Netto_KG">Net (Kg)</label>
						<input type="text" inputmode="decimal" name="Netto_KG" id="Netto_KG" class="text-black form-control shadow-sm cns-decimal" value="0.00" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Netto_MT">Net (MT)</label>
						<input type="text" name="Netto_MT" id="Netto_MT" class="text-black form-control shadow-sm bg-white" value="0.00" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Gross_KG">Gross (Kg)</label>
						<input type="text" name="Gross_KG" id="Gross_KG" class="text-black form-control shadow-sm bg-white" value="0.00" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Gross_MT">Gross (MT)</label>
						<input type="text" name="Gross_MT" id="Gross_MT" class="text-black form-control shadow-sm bg-white" value="0.00" readonly />
					</div>
				</div>
				<div class="col-md-3 col-12">
					<div class="form-group">
						<label for="Note">Note</label>
						<input type="text" name="Note" id="Note" class="text-black form-control shadow-sm" />
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label for="Weighbridge_Note">Upload Nota Timbang</label>
						<input type="file" name="Weighbridge_Note" id="Weighbridge_Note" class="text-black form-control shadow-sm" accept=".pdf,.jpg,.jpeg,.png" />
						<small class="text-muted">PDF/JPG/JPEG/PNG, maksimal 8 MB. Kosongkan jika tidak ingin mengganti nota.</small>
					</div>
				</div>
				<div class="col-md-6 col-12">
					<div class="form-group">
						<label>Nota Timbang Saat Ini</label>
						<div id="currentWeighbridgeNote" class="text-black form-control bg-white" style="min-height:38px;height:auto;">Tidak ada nota.</div>
					</div>
				</div>
			</div>
			<button type="submit" id="SaveStuffingMonitoring" class="btn btn-success mt-2 mr-2">Simpan</button>
			<button type="button" id="CancelStuffingMonitoring" class="btn btn-secondary mt-2">Batal</button>
		</form>
	</div>
</div>

<div id="divlistOpportunity"></div>
<div id="DivProcess"></div>

<script>
	var StuffingMonitoring;

	$(function() {
		$('#Order_No').select2({
			width: '100%',
			placeholder: '-- Pilih Order No --',
			allowClear: true
		});

		$('#Order_No').on('change', function() {
			let orderNo = $(this).val();

			if (!orderNo) {
				setCurrentWeighbridgeNote(null, null);
				return;
			}

			$.ajax({
				url: 'cns-export-stuffing-monitoring-note-info',
				type: 'GET',
				data: {Order_No: orderNo},
				headers: {'Accept': 'application/json'},
				success: function(response) {
					setCurrentWeighbridgeNote(response.url, response.name);
				},
				error: function() {
					setCurrentWeighbridgeNote(null, null);
				}
			});
		});
	});

	StuffingMonitoring = new dhtmlXGridObject('StuffingMonitoring');
	StuffingMonitoring.setImagePath("./dhtmlx/imgs/");
	StuffingMonitoring.setHeader("No, Order No, Jam Awal (Jam:Menit), Jam Akhir (Jam:Menit), Tanggal, Nopol, Kontainer, Seal, Tare, Netto (Kg), Netto (MT), Gross (Kg), Gross (MT), Note, Nota Timbang");
	StuffingMonitoring.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	StuffingMonitoring.setInitWidths("60,120,90,90,100,160,140,140,90,100,100,100,100,180,100");
	StuffingMonitoring.setColAlign("center,center,center,center,center,center,center,center,right,right,right,right,right,left,left");
	StuffingMonitoring.setColSorting("int,str,str,str,str,str,str,str,int,int,int,int,int,str,str");
	StuffingMonitoring.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	StuffingMonitoring.enableSmartRendering(true, 150);
	StuffingMonitoring.attachEvent("onRowDblClicked", editStuffingContainer);
	StuffingMonitoring.setSkin("dhx_skyblue");
	StuffingMonitoring.init();
	refreshdataDetail();

	function refreshdataDetail() {
		StuffingMonitoring.clearAndLoad('xml-cns-export-stuffing-monitoring?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());
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
			: StuffingMonitoring.getSelectedId();

		if (!id) {
			swal('Perhatian', 'Pilih baris data yang ingin diedit.', 'warning');
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
				swal('Error', 'Gagal mengambil data container.', 'error');
			}
		});
	}

	function deleteStuffingContainer() {
		let id = StuffingMonitoring.getSelectedId();

		if (!id) {
			swal('Perhatian', 'Pilih baris data yang ingin dihapus.', 'warning');
			return;
		}

		swal({
			title: 'Konfirmasi',
			text: 'Hapus container ini?',
			icon: 'warning',
			buttons: ['Batal', 'Ya'],
			dangerMode: true
		}).then(function(confirmed) {
			if (!confirmed) return;

			$.ajax({
				url: 'cns-export-stuffing-monitoring-delete',
				type: 'POST',
				data: {_token: '{{ csrf_token() }}', id: id},
				headers: {'Accept': 'application/json'},
				success: function(response) {
					swal('Berhasil!', response.message || 'Container berhasil dihapus.', 'success');
					if ($('#Stuffing_ID').val() == id) {
						$('#stuffingFormCard').hide();
					}
					refreshdataDetail();
				},
				error: function() {
					swal('Error', 'Gagal menghapus container.', 'error');
				}
			});
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
				swal('Berhasil!', response.message || 'Data berhasil disimpan.', 'success');
				$('#stuffingFormCard').hide();
				resetStuffingMonitoringForm();
				refreshdataDetail();
			},
			error: function(xhr) {
				if (xhr.responseJSON && xhr.responseJSON.errors) {
					let messages = [];
					Object.keys(xhr.responseJSON.errors).forEach(function(key) {
						messages.push(xhr.responseJSON.errors[key].join('\n'));
					});
					swal('Perhatian', messages.join('\n'), 'warning');
				} else {
					swal('Error', 'Gagal menyimpan data.', 'error');
				}
			},
			complete: function() {
				button.prop('disabled', false).text('Simpan');
			}
		});
	});
</script>
@endsection
