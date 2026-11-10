<?php

use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Stuffing Monitoring</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Stuffing Monitoring List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">STUFFING MONITORING LIST</div>
	<div class="card">
		<div class="card-body">
			<div class="mb-2">
				Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
				s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
				<button onclick="refreshdataDetail()" class="btn btn-primary p-2">Refresh</button>
			</div>
			<div id="StuffingMonitoring" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div class="mt-3">
				<button class="btn btn-primary mr-3" onclick="addStuffingContainer()">Add Container</button>
				<button class="btn btn-info mr-3" onclick="editStuffingContainer()">Edit Container</button>
				<button class="btn btn-danger mr-3" onclick="deleteStuffingContainer()">Delete Container</button>
				<input type="button" id="ekselShipmentInstructionDet" name="ekselShipmentInstructionDet" class="btn btn-warning mr-3" value="Export Excel" onclick="StuffingMonitoring.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
</div>

<div class="card mt-3" id="stuffingFormCard" style="display:none;">
	<div class="card-body">
		<div class="JudulContent" id="stuffingFormTitle" style="color:#699;background-image:url(hdr_back.gif);">BUAT FORM STUFFING MONITORING</div>
		<form id="StuffingMonitoringForm" enctype="multipart/form-data">
			<input type="hidden" name="id" id="Stuffing_ID" value="" />
			<div class="row">
				<div class="col-md-4 col-12">
					<div class="form-group">
						<label for="Order_No">Order No</label>
						<select name="Order_No" id="Order_No" class="form-control shadow-sm" required>
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
						<label for="Container">Kontainer</label>
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
						<input type="number" step="0.01" name="Tare" id="Tare" class="form-control shadow-sm" value="0" />
					</div>
				</div>
				<div class="col-md-2 col-12">
					<div class="form-group">
						<label for="Netto_KG">Net (Kg)</label>
						<input type="number" step="0.01" name="Netto_KG" id="Netto_KG" class="form-control shadow-sm" value="0" />
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
		let tare = parseFloat($('#Tare').val()) || 0;
		let netto = parseFloat($('#Netto_KG').val()) || 0;
		let nettoMT = netto / 1000;
		let gross = tare + netto;

		$('#Netto_MT').val(nettoMT.toFixed(2));
		$('#Gross_KG').val(gross.toFixed(2));
		$('#Gross_MT').val((gross / 1000).toFixed(2));
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
				$('#Tare').val(response.Tare);
				$('#Netto_KG').val(response.Netto_KG);
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
		let id = StuffingMonitoring.getSelectedId();

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
				refreshdataDetail();
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
				refreshdataDetail();
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
</script>
@endsection
