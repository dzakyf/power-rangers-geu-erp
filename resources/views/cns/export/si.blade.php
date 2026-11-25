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
	'title' => 'Draft Shipping Instructions',
	'breadcrumbs' => [
		['label' => 'Export Management'],
		['label' => 'Draft Shipping Instructions'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Shipping Instructions List</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<input type="button" id="newSchedule" name="newSchedule" value="New SI" class="btn btn-sm btn-primary" onclick="ShipmentInstructionNewBtn()" />
			<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail SI" class="btn btn-sm btn-info" onclick="ShipmentInstructionKlik()" />
			<input type="button" id="approveSI" name="approveSI" value="Approve SI" class="btn btn-sm btn-success" onclick="ApproveSI()" />
			<input type="button" id="DeleteSI" name="DeleteSI" value="Delete SI" class="btn btn-sm btn-danger" onclick="DeleteSI()" />
			<input type="button" id="printSI" name="printSI" value="Print SI" class="btn btn-sm btn-secondary" onclick="PrintSI()" />
			<div id="divFSIDate" style="display:none;">
				<input type="text" name="FSI_Date" id="FSI_Date" class="form-control form-control-sm bg-white" placeholder="FSI Date" value="{{ date('Y-m-d') }}" style="width:110px;display:inline-block;" readonly />
				<button type="button" class="btn btn-sm btn-secondary" onclick="$('#divFSIDate').hide();">Batal</button>
			</div>
			<input type="button" id="createToFSI" name="createToFSI" value="Create Final SI" class="btn btn-sm btn-success" onclick="CreateToFSI()" />
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

<div class="modal fade" id="modalApproveSI" tabindex="-1" role="dialog" aria-labelledby="modalApproveSILabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-success text-white">
				<h5 class="modal-title" id="modalApproveSILabel"><i class="fa fa-check-circle mr-1"></i> Approve Shipping Instructions</h5>
				<button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="$('#modalApproveSI').modal('hide');">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p>Pilih user yang menyetujui (Approved By) untuk dokumen <strong id="approveSiDocNo">-</strong>:</p>
				<div class="form-group">
					<label for="approveSiUser">Approved By <span class="text-danger">*</span></label>
					<select id="approveSiUser" class="form-control">
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
				<button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#modalApproveSI').modal('hide');">Batal</button>
				<button type="button" class="btn btn-success" id="btnConfirmApproveSI" onclick="SubmitApproveSI()">Approve</button>
			</div>
		</div>
	</div>
</div>

<script>
	var myCalendarMain;
	myCalendarMain = new dhtmlXCalendarObject(["FSI_Date"]);
	myCalendarMain.setDateFormat("%Y-%m-%d");

	var selectedSiId = null;

	function getActiveSiId() {
		var id = (typeof ShipmentInstruction !== 'undefined' && ShipmentInstruction && typeof ShipmentInstruction.getSelectedId === 'function')
			? ShipmentInstruction.getSelectedId()
			: null;
		if (!id && selectedSiId) {
			id = selectedSiId;
		}
		if (!id && typeof ShipmentInstructionID !== 'undefined' && ShipmentInstructionID) {
			id = ShipmentInstructionID;
		}
		return id;
	}

	function ApproveSI()
	{
		try {
			var selectedId = getActiveSiId();

			if (!selectedId) {
				swal('Perhatian', 'Pilih baris data terlebih dahulu pada tabel Shipping Instructions!', 'warning');
				return false;
			}

			var siNo = "";
			try {
				siNo = ShipmentInstruction.cells(selectedId, 2).getValue();
			} catch (cellErr) {
				console.warn("Gagal membaca SI No cell 2:", cellErr);
			}

			var status = "";
			try {
				status = ShipmentInstruction.cells(selectedId, 10).getValue();
			} catch (cellErr) {
				console.warn("Gagal membaca status cell 10:", cellErr);
			}

			if (status && status.trim().toUpperCase() === 'APPROVED') {
				swal('Perhatian', 'Shipping Instructions ' + (siNo || selectedId) + ' ini sudah berstatus APPROVED!', 'warning');
				return false;
			}

			$('#approveSiDocNo').text(siNo || selectedId);
			$('#approveSiUser').val('');
			$('#modalApproveSI').modal('show');
		} catch (err) {
			if (typeof $.unblockUI === 'function') {
				$.unblockUI();
			}
			swal('Error', 'Terjadi kesalahan sistem: ' + err.message, 'error');
			return false;
		}
		return true;
	}

	function SubmitApproveSI()
	{
		try {
			var selectedId = getActiveSiId();
			var approverId = $('#approveSiUser').val();

			if (!approverId) {
				swal('Perhatian', 'Silakan pilih user Approved By terlebih dahulu!', 'warning');
				return false;
			}

			var siNo = "";
			try {
				siNo = ShipmentInstruction.cells(selectedId, 2).getValue();
			} catch (cellErr) {}

			var approverName = $('#approveSiUser option:selected').text().trim();

			swal({
				title: 'Konfirmasi Approval',
				text: "Apakah Anda yakin ingin menyetujui (Approve) Shipping Instructions " + (siNo || selectedId) + " dengan Approver: " + approverName + "?",
				icon: 'warning',
				buttons: ['Batal', 'Ya, Setujui'],
				dangerMode: false
			}).then(function(confirmed) {
				if (!confirmed) {
					return;
				}

				$('#modalApproveSI').modal('hide');

				if (typeof $.blockUI === 'function') {
					$.blockUI();
				}

				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					url: "cns-export-shipping-instruction-approve",
					type: "POST",
					dataType: "json",
					data: {
						_token: "{{ csrf_token() }}",
						id: selectedId,
						Task: siNo,
						approved_by: approverId
					},
					success: function(response) {
						if (typeof $.unblockUI === 'function') {
							$.unblockUI();
						}
						if (response && response.status === 'success') {
							swal('Berhasil!', response.message || "Shipping Instructions berhasil di-approve.", 'success').then(function() {
								if (typeof refreshdata === 'function') {
									refreshdata();
								}
							});
						} else {
							swal('Error', (response && response.message) || "Gagal approve Shipping Instructions.", 'error');
						}
					},
					error: function(xhr, status, error) {
						if (typeof $.unblockUI === 'function') {
							$.unblockUI();
						}
						var msg = "Gagal approve Shipping Instructions.";
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

	function DeleteSI()
	{
		var selectedId = getActiveSiId();
		if (selectedId != null) {
			var siNo = ShipmentInstruction.cells(selectedId, 2).getValue();
			swal({
				title: 'Konfirmasi',
				text: "Apakah Anda yakin ingin menghapus Shipping Instructions " + siNo + "?",
				icon: 'warning',
				buttons: ['Batal', 'Ya'],
				dangerMode: true
			}).then(function(confirmed) {
				if (!confirmed) {
					return;
				}

				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					url: "cns-export-shipping-instruction-delete",
					type: "POST",
					data: {
						_token: "{{ csrf_token() }}",
						id: selectedId,
						Task: siNo
					},
					success: function(response) {
						if (response.status === 'success') {
							swal('Berhasil!', response.message || "Shipping Instructions berhasil dihapus.", 'success').then(function() {
								$('#divlistOpportunity').html('');
								refreshdata();
							});
						} else {
							swal('Error', response.message || "Gagal menghapus Shipping Instructions.", 'error');
						}
					},
					error: function(xhr) {
						var msg = (xhr.responseJSON && xhr.responseJSON.message)
							? xhr.responseJSON.message
							: "Gagal menghapus Shipping Instructions. Silakan coba lagi.";
						swal('Error', msg, 'error');
					}
				});
			});
		} else {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
		}
		return true;
	}

	function PrintSI()
	{
		var selectedId = getActiveSiId();
		if (selectedId != null) {
			window.open('cns-export-shipping-instruction-print?Task=' + ShipmentInstruction.cells(selectedId, 2).getValue(), '_blank');
		} else {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
		}
		return true;
	}

	function openFSIDate()
	{
		var selectedId = getActiveSiId();
		if (!selectedId) {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
			return false;
		}

		var status = "";
		var siNo = "";
		try {
			status = ShipmentInstruction.cells(selectedId, 10).getValue();
			siNo = ShipmentInstruction.cells(selectedId, 2).getValue();
		} catch (e) {
			console.warn("Gagal membaca cell grid:", e);
		}

		if (status !== 'APPROVED') {
			swal('Perhatian', 'Shipping Instructions harus berstatus APPROVED sebelum dibuatkan Final SI.', 'warning');
			return false;
		}

		var display = $('#divFSIDate').css("display");
		if (display === 'none') {
			$('#divFSIDate').css('display', 'inline-block');
		} else {
			swal({
				title: 'Konfirmasi Final SI',
				text: "Buat Final SI untuk Shipping Instructions " + (siNo || selectedId) + " dengan tanggal " + $('#FSI_Date').val() + "?",
				icon: 'warning',
				buttons: ['Batal', 'Ya'],
				dangerMode: false
			}).then(function(confirmed) {
				if (!confirmed) {
					return;
				}
				$('#divFSIDate').css('display', 'none');
				LoadM('divlistOpportunity', 'cns-export-shipping-instruction-generate-fsi?Task=' + encodeURIComponent(siNo) + "&FSI_Date=" + encodeURIComponent($('#FSI_Date').val()));

				setTimeout(function() {
					refreshdata();
				}, 500);
			});
		}
	}

	function CreateToFSI()
	{
		var selectedId = getActiveSiId();
		if (selectedId != null) {
			openFSIDate();
		} else {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
		}
		return true;
	}

	function ShipmentInstructionNewBtn() {
		LoadM('divlistOpportunity', 'cns-export-shipping-instruction-new');
		return true;
	}

	function ShipmentInstructionKlik() {
		var selectedId = getActiveSiId();
		if (selectedId != null) {
			LoadM('divlistOpportunity', 'cns-export-shipping-instruction-detail?Task=' + selectedId);
		} else {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
		}
		return true;
	}

	ShipmentInstruction = new dhtmlXGridObject('ShipmentInstruction');
	ShipmentInstruction.setImagePath("./dhtmlx/imgs/");
	ShipmentInstruction.setHeader("No,Order (DO)/Booking Confirmation (BC), SI No, SI Date,Kontainer (Seal), Tare, Netto (Kg), Netto (MT), Gross, Gross (MT), Status");
	ShipmentInstruction.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	ShipmentInstruction.setInitWidths("60,150,150,150,150,150,100,100,100,100,100");
	ShipmentInstruction.setColAlign("center,left,left,left,left,left,left,left,left,left,left");
	ShipmentInstruction.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
	ShipmentInstruction.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,cns_badge");
	ShipmentInstruction.attachEvent("onRowDblClicked", ShipmentInstructionKlik);
	ShipmentInstruction.attachEvent("onRowSelect", function(id, ind) {
		selectedSiId = id;
		ShipmentInstructionID = id;
	});
	ShipmentInstruction.setSkin("dhx_skyblue");
	ShipmentInstruction.init();

	var ShipmentInstructionPagination = initCnsGridPagination(ShipmentInstruction, {
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
			return 'xml-cns-export-shipping-instruction?' + q.join('&');
		}
	});
	refreshdata();

	function refreshdata() {
		ShipmentInstructionPagination.reload(1);
	}
</script>
@endsection