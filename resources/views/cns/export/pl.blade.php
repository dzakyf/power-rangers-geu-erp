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
	'title' => 'Packing List',
	'breadcrumbs' => [
		['label' => 'Export Management'],
		['label' => 'Packing List'],
	],
])

<div class="card cns-card">
	<div class="card-header">
		<h5 class="card-title mb-0">Packing List</h5>
		<div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
			<input type="button" id="newSchedule" name="newSchedule" value="New PL" class="btn btn-sm btn-primary" onclick="ShipmentInstructionNewBtn()" />
			<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail PL" class="btn btn-sm btn-info" onclick="ShipmentInstructionKlik()" />
			<input type="button" id="approvePL" name="approvePL" value="Approve PL" class="btn btn-sm btn-success" onclick="ApprovePL()" />
			<input type="button" id="printPL" name="printPL" value="Print PL" class="btn btn-sm btn-secondary" onclick="PrintPL()" />
			<input type="button" id="printPLByer" name="printPLByer" value="Print PL Buyer" class="btn btn-sm btn-secondary" onclick="PrintPLByer()" />
			<input type="button" id="ekselShipmentInstructionDet" name="ekselShipmentInstructionDet" class="btn btn-sm btn-warning" value="Export Excel" onclick="ShipmentInstruction.toExcel('./export/generate.php');" />
			<button type="button" onclick="refreshdata()" class="btn btn-sm btn-outline-secondary">Refresh</button>
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

<div class="modal fade" id="modalApprovePL" tabindex="-1" role="dialog" aria-labelledby="modalApprovePLLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header bg-success text-white">
				<h5 class="modal-title" id="modalApprovePLLabel"><i class="fa fa-check-circle mr-1"></i> Approve Packing List</h5>
				<button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="$('#modalApprovePL').modal('hide');">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p>Pilih user yang menyetujui (Approved By) untuk dokumen <strong id="approvePlDocNo">-</strong>:</p>
				<div class="form-group">
					<label for="approvePlUser">Approved By <span class="text-danger">*</span></label>
					<select id="approvePlUser" class="text-black form-control">
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
				<button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#modalApprovePL').modal('hide');">Batal</button>
				<button type="button" class="btn btn-success" id="btnConfirmApprovePL" onclick="SubmitApprovePL()">Approve</button>
			</div>
		</div>
	</div>
</div>

<script>
	var selectedPlId = null;

	function getActivePlId() {
		var id = (typeof ShipmentInstruction !== 'undefined' && ShipmentInstruction && typeof ShipmentInstruction.getSelectedId === 'function')
			? ShipmentInstruction.getSelectedId()
			: null;
		if (!id && selectedPlId) {
			id = selectedPlId;
		}
		if (!id && typeof ShipmentInstructionID !== 'undefined' && ShipmentInstructionID) {
			id = ShipmentInstructionID;
		}
		return id;
	}

	function ApprovePL() {
		try {
			var selectedId = getActivePlId();

			if (!selectedId) {
				swal('Perhatian', 'Pilih baris data terlebih dahulu pada tabel Packing List!', 'warning');
				return false;
			}

			var plNo = "";
			try {
				plNo = ShipmentInstruction.cells(selectedId, 1).getValue();
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
				swal('Perhatian', 'Packing List ' + (plNo || selectedId) + ' ini sudah berstatus APPROVED!', 'warning');
				return false;
			}

			$('#approvePlDocNo').text(plNo || selectedId);
			$('#approvePlUser').val('');
			$('#modalApprovePL').modal('show');
		} catch (err) {
			console.error("Error pada ApprovePL:", err);
			swal('Error', 'Terjadi kesalahan pada aksi Approve: ' + err.message, 'error');
			return false;
		}
		return true;
	}

	function SubmitApprovePL() {
		try {
			var selectedId = getActivePlId();
			var approverId = $('#approvePlUser').val();

			if (!approverId) {
				swal('Perhatian', 'Silakan pilih user Approved By terlebih dahulu!', 'warning');
				return false;
			}

			var plNo = "";
			try {
				plNo = ShipmentInstruction.cells(selectedId, 1).getValue();
			} catch (cellErr) {}

			var approverName = $('#approvePlUser option:selected').text().trim();

			swal({
				title: 'Konfirmasi Approval',
				text: "Apakah Anda yakin ingin menyetujui (Approve) Packing List " + (plNo || selectedId) + " dengan Approver: " + approverName + "?",
				icon: 'warning',
				buttons: ['Batal', 'Ya, Setujui'],
				dangerMode: false
			}).then(function(confirmed) {
				if (!confirmed) {
					return;
				}

				$('#modalApprovePL').modal('hide');

				if (typeof $.blockUI === 'function') {
					$.blockUI();
				}

				$.ajax({
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					url: "cns-export-packing-list-approve",
					type: "POST",
					dataType: "json",
					data: {
						_token: "{{ csrf_token() }}",
						id: selectedId,
						Task: plNo,
						approved_by: approverId
					},
					success: function(response) {
						if (typeof $.unblockUI === 'function') {
							$.unblockUI();
						}
						if (response && response.status === 'success') {
							swal('Berhasil!', response.message || "Packing List berhasil di-approve.", 'success').then(function() {
								if (typeof refreshdata === 'function') {
									refreshdata();
								}
							});
						} else {
							swal('Error', (response && response.message) || "Gagal approve Packing List.", 'error');
						}
					},
					error: function(xhr, status, error) {
						if (typeof $.unblockUI === 'function') {
							$.unblockUI();
						}
						var msg = "Gagal approve Packing List.";
						if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
							msg = xhr.responseJSON.message;
						}
						swal('Error', msg, 'error');
					}
				});
			});
		} catch (err) {
			console.error("Error pada ApprovePL:", err);
			swal('Error', 'Terjadi kesalahan pada aksi Approve: ' + err.message, 'error');
		}
		return true;
	}

	function PrintPL()
	 {
		var selectedId = getActivePlId();
		if (selectedId != null) {
			window.open('cns-export-packing-list-print?Task=' + ShipmentInstruction.cells(selectedId, 1).getValue() + '&Buyer=0', '_blank');
		}else {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
		}
		return true;
	 }
	function PrintPLByer()
	 {
		var selectedId = getActivePlId();
		if (selectedId != null) {
			window.open('cns-export-packing-list-print?Task=' + ShipmentInstruction.cells(selectedId, 1).getValue() + '&Buyer=1', '_blank');
		}else {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
		}
		return true;
	 }
	function ShipmentInstructionNewBtn() {

		LoadM('divlistOpportunity', 'cns-export-packing-list-new');
		return true;
	}
	function ShipmentInstructionKlik() {

		var selectedId = getActivePlId();
		if (selectedId != null) {
			LoadM('divlistOpportunity', 'cns-export-packing-list-detail?Task=' + selectedId);
		}else {
			swal('Perhatian', 'Pilih baris data terlebih dahulu', 'warning');
		}
		return true;
	}
	ShipmentInstruction = new dhtmlXGridObject('ShipmentInstruction');
	ShipmentInstruction.setImagePath("./dhtmlx/imgs/");
	ShipmentInstruction.setHeader("No,Packing List No, Final SI No,Packing List Date, Kontainer (Seal), Tare, Netto (Kg), Netto (MT), Gross (Kg), Gross (MT), Status");
	ShipmentInstruction.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	ShipmentInstruction.setInitWidths("60,150,150,100,150,150,100,100,100,100,100");
	ShipmentInstruction.setColAlign("center,left,left,center,left,left,left,left,left,left,center");
	ShipmentInstruction.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
	ShipmentInstruction.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,cns_badge");
	ShipmentInstruction.attachEvent("onRowDblClicked", ShipmentInstructionKlik);
	ShipmentInstruction.attachEvent("onRowSelect", function(id, ind) {
		selectedPlId = id;
		ShipmentInstructionID = id;
	});
	ShipmentInstruction.setSkin("dhx_skyblue");
	ShipmentInstruction.init();

	var PackingListPagination = initCnsGridPagination(ShipmentInstruction, {
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
			return 'xml-cns-export-packing-list?' + q.join('&');
		}
	});
	refreshdata();

	function refreshdata() {
		PackingListPagination.reload(1);
	}
</script>
@endsection