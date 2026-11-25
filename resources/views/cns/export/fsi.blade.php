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
@include('cns.partials.page-header', [
	'title' => 'Final Shipping Instructions',
	'breadcrumbs' => [
		['label' => 'Export Management'],
		['label' => 'Final Shipping Instructions'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Final Shipping Instructions List</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<input type="button" id="newSchedule" name="newSchedule" value="New Final SI" class="btn btn-sm btn-primary" onclick="ShipmentInstructionNewBtn()" />
			<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail Final SI" class="btn btn-sm btn-info" onclick="ShipmentInstructionKlik()" />
			<input type="button" id="approveFSI" name="approveFSI" value="Approve Final SI" class="btn btn-sm btn-success" onclick="ApproveFSI()" />
			<input type="button" id="printSI" name="printSI" value="Print Final SI" class="btn btn-sm btn-secondary" onclick="PrintSI()" />
			<div id="divCreatePL" style="display:none;">
				<input type="text" name="PL_Date" id="PL_Date" value="{{ date("Y-m-d") }}" class="form-control form-control-sm bg-white" placeholder="PL Date" style="width:110px;display:inline-block;" readonly />
				<button type="button" class="btn btn-sm btn-secondary" onclick="$('#divCreatePL').hide();">Batal</button>
			</div>
			<input type="button" id="createPL" name="createPL" value="Create Packing List" class="btn btn-sm btn-success" onclick="CreatePL()" />
			<div id="divCreateCI" style="display:none;">
				<input type="text" name="CI_Date" id="CI_Date" value="{{ date("Y-m-d") }}" class="form-control form-control-sm bg-white" placeholder="CI Date" style="width:110px;display:inline-block;" readonly />
				<button type="button" class="btn btn-sm btn-secondary" onclick="$('#divCreateCI').hide();">Batal</button>
			</div>
			<input type="button" id="createCI" name="createCI" value="Create Commercial Invoice" class="btn btn-sm btn-success" onclick="CreateCI()" />
			<input type="button" id="ekselShipmentInstructionDet" name="ekselShipmentInstructionDet" class="btn btn-sm btn-warning" value="Export Excel" onclick="ShipmentInstruction.toExcel('./export/generate.php');" />
			<button type="button" onclick="refreshdata()" class="btn btn-sm btn-outline-secondary">Refresh</button>
		</div>
	</div>
	<div class="card-body">
		<div class="cns-filter-bar">
			<div>
				<label for="tgl1" class="d-block mb-1"><small>Dari Tanggal</small></label>
				<input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control form-control-sm" style="width:auto;" />
			</div>
			<div>
				<label for="tgl2" class="d-block mb-1"><small>Sampai Tanggal</small></label>
				<input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" style="width:auto;" />
			</div>
			<div>
				<button type="button" onclick="refreshdata()" class="btn btn-sm btn-primary">Filter</button>
			</div>
		</div>
		<div class="cns-grid-wrapper">
			<div id="ShipmentInstruction" class="cns-grid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		</div>
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="DivProcess"></div>

<div class="modal fade" id="modalApproveFSI" tabindex="-1" role="dialog" aria-labelledby="modalApproveFSILabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-success text-white">
				<h5 class="modal-title" id="modalApproveFSILabel"><i class="fa fa-check-circle mr-1"></i> Approve Final Shipping Instructions</h5>
				<button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="$('#modalApproveFSI').modal('hide');">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p>Pilih user yang menyetujui (Approved By) untuk dokumen <strong id="approveFsiDocNo">-</strong>:</p>
				<div class="form-group">
					<label for="approveFsiUser">Approved By <span class="text-danger">*</span></label>
					<select id="approveFsiUser" class="form-control">
						<option value="">-- Pilih Approver --</option>
						@foreach ($approvers ?? [] as $appr)
							<option value="{{ $appr->id }}">
								{{ $appr->Name }} @if(!empty($appr->Position))({{ $appr->Position }})@endif
							</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#modalApproveFSI').modal('hide');">Batal</button>
				<button type="button" class="btn btn-success" id="btnConfirmApproveFSI" onclick="SubmitApproveFSI()">Approve</button>
			</div>
		</div>
	</div>
</div>

<script>
	var myCalendarMain;
	myCalendarMain = new dhtmlXCalendarObject(["PL_Date", 'CI_Date']);
	myCalendarMain.setDateFormat("%Y-%m-%d");

	var selectedFsiId = null;

	function getActiveFsiId() {
		var id = (typeof ShipmentInstruction !== 'undefined' && ShipmentInstruction && typeof ShipmentInstruction.getSelectedId === 'function')
			? ShipmentInstruction.getSelectedId()
			: null;
		if (!id && selectedFsiId) {
			id = selectedFsiId;
		}
		if (!id && typeof ShipmentInstructionID !== 'undefined' && ShipmentInstructionID) {
			id = ShipmentInstructionID;
		}
		return id;
	}

	function ApproveFSI() {
		try {
			var selectedId = getActiveFsiId();

			if (!selectedId) {
				swal('Perhatian', 'Pilih baris data terlebih dahulu pada tabel Final Shipping Instructions!', 'warning');
				return false;
			}

			var fsiNo = "";
			try {
				fsiNo = ShipmentInstruction.cells(selectedId, 1).getValue();
			} catch (cellErr) {
				console.warn("Gagal membaca cell 1:", cellErr);
			}

			var status = "";
			try {
				status = ShipmentInstruction.cells(selectedId, 10).getValue();
			} catch (cellErr) {
				console.warn("Gagal membaca status cell 10:", cellErr);
			}

			if (status && status.trim().toUpperCase() === 'APPROVED') {
				swal('Perhatian', 'Final Shipping Instructions ' + (fsiNo || selectedId) + ' ini sudah berstatus APPROVED!', 'warning');
				return false;
			}

			$('#approveFsiDocNo').text(fsiNo || selectedId);
			$('#approveFsiUser').val('');
			$('#modalApproveFSI').modal('show');
		} catch (err) {
			if (typeof $.unblockUI === 'function') {
				$.unblockUI();
			}
			swal('Error', 'Terjadi kesalahan sistem: ' + err.message, 'error');
			return false;
		}
		return true;
	}

	function SubmitApproveFSI() {
		try {
			var selectedId = getActiveFsiId();
			var approverId = $('#approveFsiUser').val();

			if (!approverId) {
				swal('Perhatian', 'Silakan pilih user Approved By terlebih dahulu!', 'warning');
				return false;
			}

			var fsiNo = "";
			try {
				fsiNo = ShipmentInstruction.cells(selectedId, 1).getValue();
			} catch (cellErr) {}

			var approverName = $('#approveFsiUser option:selected').text().trim();

			swal({
				title: 'Konfirmasi Approval',
				text: "Apakah Anda yakin ingin menyetujui (Approve) Final Shipping Instructions " + (fsiNo || selectedId) + " dengan Approver: " + approverName + "?",
				icon: 'warning',
				buttons: ['Batal', 'Ya, Setujui'],
				dangerMode: false
			}).then(function(confirmed) {
				if (!confirmed) {
					return;
				}

				$('#modalApproveFSI').modal('hide');

				if (typeof $.blockUI === 'function') {
					$.blockUI();
				}

				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					url: "cns-export-final-shipping-instruction-approve",
					type: "POST",
					dataType: "json",
					data: {
						_token: "{{ csrf_token() }}",
						id: selectedId,
						Task: fsiNo,
						approved_by: approverId
					},
					success: function(response) {
						if (typeof $.unblockUI === 'function') {
							$.unblockUI();
						}
						if (response && response.status === 'success') {
							swal('Berhasil!', response.message || "Final Shipping Instructions berhasil di-approve.", 'success').then(function() {
								if (typeof refreshdata === 'function') {
									refreshdata();
								}
							});
						} else {
							swal('Error', (response && response.message) || "Gagal approve Final Shipping Instructions.", 'error');
						}
					},
					error: function(xhr, status, error) {
						if (typeof $.unblockUI === 'function') {
							$.unblockUI();
						}
						var msg = "Gagal approve Final Shipping Instructions.";
						if (xhr.responseJSON && xhr.responseJSON.message) {
							msg = xhr.responseJSON.message;
						} else if (xhr.responseText) {
							try {
								var parsed = JSON.parse(xhr.responseText);
								if (parsed.message) {
									msg = parsed.message;
								}
							} catch (e) {
								msg = "Error (" + xhr.status + "): " + (error || xhr.statusText);
							}
						}
						swal('Error', msg, 'error');
					}
				});
			});
		} catch (err) {
			if (typeof $.unblockUI === 'function') {
				$.unblockUI();
			}
			swal('Error', 'Terjadi kesalahan sistem: ' + err.message, 'error');
			return false;
		}
		return true;
	}

	function CreatePL() {
		var selectedId = getActiveFsiId();
		if (!selectedId) {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
			return false;
		}

		var fsiNo = "";
		try {
			fsiNo = ShipmentInstruction.cells(selectedId, 1).getValue();
		} catch (cellErr) {}

		if (!fsiNo || fsiNo.trim() === '') {
			$('#divCreatePL').css('display', 'none');
			swal('Perhatian', 'Nomor Final SI tidak ditemukan pada baris yang dipilih.', 'warning');
			return false;
		}

		var status = "";
		try {
			status = ShipmentInstruction.cells(selectedId, 10).getValue();
		} catch (cellErr) {}

		if (!status || status.trim().toUpperCase() !== 'APPROVED') {
			$('#divCreatePL').css('display', 'none');
			swal('Perhatian', 'Final Shipping Instructions ' + (fsiNo || selectedId) + ' harus berstatus APPROVED sebelum membuat Packing List!', 'warning');
			return false;
		}

		var display = $('#divCreatePL').css('display');
		if (display == 'none') {
			$('#divCreatePL').css('display', 'inline-block');
		} else {
			swal({
				title: 'Konfirmasi Packing List',
				text: "Buat Packing List untuk Final SI " + fsiNo + " dengan tanggal " + $('#PL_Date').val() + "?",
				icon: 'warning',
				buttons: ['Batal', 'Ya'],
				dangerMode: false
			}).then(function(confirmed) {
				if (!confirmed) return;

				$('#divCreatePL').css('display', 'none');
				if (typeof $.blockUI === 'function') {
					$.blockUI();
				}

				$.ajax({
					url: "cns-export-final-shipping-instruction-create-pl",
					type: "GET",
					dataType: "json",
					data: {
						Task: fsiNo,
						PL_Date: $('#PL_Date').val()
					},
					success: function(response) {
						if (typeof $.unblockUI === 'function') {
							$.unblockUI();
						}
						if (response && response.status === 'success') {
							swal('Berhasil!', response.message || 'Packing List berhasil dibuat.', 'success').then(function() {
								if (typeof refreshdata === 'function') {
									refreshdata();
								}
							});
						} else {
							swal('Error', (response && response.message) || 'Gagal membuat Packing List.', 'error');
						}
					},
					error: function(xhr, status, error) {
						if (typeof $.unblockUI === 'function') {
							$.unblockUI();
						}
						var msg = 'Gagal membuat Packing List.';
						if (xhr.responseJSON && xhr.responseJSON.message) {
							msg = xhr.responseJSON.message;
						} else if (xhr.responseText) {
							try {
								var parsed = JSON.parse(xhr.responseText);
								if (parsed.message) {
									msg = parsed.message;
								}
							} catch (e) {
								msg = "Error (" + xhr.status + "): " + (error || xhr.statusText);
							}
						}
						swal('Error', msg, 'error');
					}
				});
			});
		}
		return true;
	}

	function CreateCI() {
		var selectedId = getActiveFsiId();
		if (selectedId != null) {
			var display = $('#divCreateCI').css('display');
			if (display == 'none') {
				$('#divCreateCI').css('display', 'inline-block');
			} else {
				var fsiNo = ShipmentInstruction.cells(selectedId, 1).getValue();
				swal({
					title: 'Konfirmasi Commercial Invoice',
					text: "Buat Commercial Invoice untuk Final SI " + fsiNo + " dengan tanggal " + $('#CI_Date').val() + "?",
					icon: 'warning',
					buttons: ['Batal', 'Ya'],
					dangerMode: false
				}).then(function(confirmed) {
					if (!confirmed) return;
					$('#divCreateCI').css('display', 'none');
					LoadM('DivProcess', 'cns-export-final-shipping-instruction-create-ci?Task=' + encodeURIComponent(fsiNo) + "&CI_Date=" + encodeURIComponent($('#CI_Date').val()));

					setTimeout(function() {
						refreshdata();
					}, 500);
				});
			}
		} else {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
		}
		return true;
	}

	function PrintSI()
	{
		var selectedId = getActiveFsiId();
		if (selectedId != null) {
			var rowId = selectedId.toString().split(',')[0];
			var finalSiNo = '';
			try {
				finalSiNo = ShipmentInstruction.cells(rowId, 1).getValue();
			} catch (e) {}
			var task = (finalSiNo && finalSiNo.trim() !== '') ? finalSiNo.trim() : rowId;
			window.open('{{ url("cns-export-final-shipping-instruction-print") }}?Task=' + encodeURIComponent(task), '_blank');
		} else {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
		}
		return true;
	}

	function ShipmentInstructionNewBtn() {
		LoadM('divlistOpportunity', 'cns-export-final-shipping-instruction-new');
		return true;
	}

	function ShipmentInstructionKlik() {
		var selectedId = getActiveFsiId();
		if (selectedId != null) {
			LoadM('divlistOpportunity', 'cns-export-final-shipping-instruction-detail?Task=' + selectedId);
		} else {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
		}
		return true;
	}

	ShipmentInstruction = new dhtmlXGridObject('ShipmentInstruction');
	ShipmentInstruction.setImagePath("./dhtmlx/imgs/");
	ShipmentInstruction.setHeader("No,Final SI No, Final SI Date, SI No, Kontainer (Seal), Tare, Netto (Kg), Netto (MT), Gross, Gross (MT), Status");
	ShipmentInstruction.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	ShipmentInstruction.setInitWidths("60,150,100,150,150,150,100,100,100,100,100,auto");
	ShipmentInstruction.setColAlign("center,left,center,left,left,left,left,left,left,left,left,left,left,left");
	ShipmentInstruction.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	ShipmentInstruction.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,cns_badge,ro,ro,ro");
	ShipmentInstruction.attachEvent("onRowDblClicked", ShipmentInstructionKlik);
	ShipmentInstruction.attachEvent("onRowSelect", function(id, ind) {
		selectedFsiId = id;
		ShipmentInstructionID = id;
	});
	ShipmentInstruction.setSkin("dhx_skyblue");
	ShipmentInstruction.init();

	var FinalSiPagination = initCnsGridPagination(ShipmentInstruction, {
		pageSize: 25,
		pageSizeOptions: [10, 25, 50, 100],
		serverFilter: false,
		getUrl: function (params) {
			var q = [
				'posStart=' + params.posStart,
				'count=' + params.count,
				'tgl1=' + encodeURIComponent($('#tgl1').val()),
				'tgl2=' + encodeURIComponent($('#tgl2').val())
			];
			if (params.sortCol !== null && params.sortCol !== undefined) {
				q.push('sort_col=' + params.sortCol);
				q.push('sort_dir=' + (params.sortDir || 'asc'));
			}
			return 'xml-cns-export-final-shipping-instruction?' + q.join('&');
		}
	});
	refreshdata();

	function refreshdata() {
		FinalSiPagination.reload(1);
	}
</script>
@endsection