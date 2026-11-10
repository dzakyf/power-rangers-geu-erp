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
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Shipping Instruction</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Shipping Instruction List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SHIPPING INSTRUCTION LIST</div>
	<div class="card">
		<div class="card-body">
			<div class="mb-2">
				Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
				s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
				<button onclick="refreshdata()" class="btn btn-primary p-2">Refresh</button>
			</div>
			<div id="ShipmentInstruction" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="newSchedule" name="newSchedule" value="New SI" class="btn btn-primary mt-2 mr-2" onclick="ShipmentInstructionNewBtn()" />
				<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail SI" class="btn btn-info mt-2 mr-2" onclick="ShipmentInstructionKlik()" />
				<input type="button" id="approveSI" name="approveSI" value="Approve SI" class="btn btn-success mt-2 mr-2" onclick="ApproveSI()" />
				<input type="button" id="DeleteSI" name="DeleteSI" value="Delete SI" class="btn btn-danger mt-2 mr-2" onclick="DeleteSI()" />
				<input type="button" id="printSI" name="printSI" value="Print SI" class="btn btn-secondary mt-2 mr-2" onclick="PrintSI()" />
				<div id="divFSIDate" style="display:none;">
					<input type="text" name="FSI_Date" id="FSI_Date" class="form-control mt-2 mr-2 bg-white" placeholder="FSI Date" value="{{ date('Y-m-d') }}" style="width:110px;display:inline-block;" readonly />
					<button type="button" class="btn btn-secondary mt-2 mr-2" onclick="$('#divFSIDate').hide();">Batal</button>
				</div>
				<input type="button" id="createToFSI" name="createToFSI" value="Create Final SI" class="btn btn-success mt-2 mr-2" onclick="CreateToFSI()" />
				<input type="button" id="ekselShipmentInstructionDet" name="ekselShipmentInstructionDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ShipmentInstruction.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

	<div class="modal fade" id="modalApproveSI" tabindex="-1" role="dialog" aria-labelledby="modalApproveSILabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header bg-success text-white">
					<h5 class="modal-title" id="modalApproveSILabel"><i class="fa fa-check-circle mr-1"></i> Approve Shipping Instruction</h5>
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
				dhtmlx.alert({
					title: "Peringatan",
					text: "Pilih baris data terlebih dahulu pada tabel Shipping Instruction!",
					ok: "OK"
				});
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
				dhtmlx.alert({
					title: "Peringatan",
					text: "Shipping Instruction " + (siNo || selectedId) + " ini sudah berstatus APPROVED!",
					ok: "OK"
				});
				return false;
			}

			$('#approveSiDocNo').text(siNo || selectedId);
			$('#approveSiUser').val('');
			$('#modalApproveSI').modal('show');
		} catch (err) {
			if (typeof $.unblockUI === 'function') {
				$.unblockUI();
			}
			dhtmlx.alert({
				title: "Error",
				text: "Terjadi kesalahan sistem: " + err.message,
				ok: "OK"
			});
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
				dhtmlx.alert({
					title: "Peringatan",
					text: "Silakan pilih user Approved By terlebih dahulu!",
					ok: "OK"
				});
				return false;
			}

			var siNo = "";
			try {
				siNo = ShipmentInstruction.cells(selectedId, 2).getValue();
			} catch (cellErr) {}

			var approverName = $('#approveSiUser option:selected').text().trim();

			dhtmlx.confirm({
				title: "Konfirmasi Approval",
				text: "Apakah Anda yakin ingin menyetujui (Approve) Shipping Instruction " + (siNo || selectedId) + " dengan Approver: " + approverName + "?",
				ok: "Ya, Setujui",
				cancel: "Batal",
				callback: function(result) {
					if (!result) {
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
						url: "cns-export-shipment-instruction-approve",
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
								dhtmlx.alert({
									title: "Sukses",
									text: response.message || "Shipping Instruction berhasil di-approve.",
									ok: "OK",
									callback: function() {
										if (typeof refreshdata === 'function') {
											refreshdata();
										}
									}
								});
							} else {
								dhtmlx.alert({
									title: "Gagal",
									text: (response && response.message) || "Gagal approve Shipping Instruction.",
									ok: "OK"
								});
							}
						},
						error: function(xhr, status, error) {
							if (typeof $.unblockUI === 'function') {
								$.unblockUI();
							}
							var msg = "Gagal approve Shipping Instruction.";
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
							dhtmlx.alert({
								title: "Error",
								text: msg,
								ok: "OK"
							});
						}
					});
				}
			});
		} catch (err) {
			if (typeof $.unblockUI === 'function') {
				$.unblockUI();
			}
			dhtmlx.alert({
				title: "Error",
				text: "Terjadi kesalahan sistem: " + err.message,
				ok: "OK"
			});
			return false;
		}
		return true;
	}

	function DeleteSI()
	{
		var selectedId = getActiveSiId();
		if (selectedId != null) {
			var siNo = ShipmentInstruction.cells(selectedId, 2).getValue();
			dhtmlx.confirm({
				title: "Hapus Shipping Instruction",
				text: "Apakah Anda yakin ingin menghapus Shipping Instruction " + siNo + "?",
				ok: "Ya",
				cancel: "Batal",
				callback: function(result) {
					if (!result) {
						return;
					}

					$.ajax({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						url: "cns-export-shipment-instruction-delete",
						type: "POST",
						data: {
							_token: "{{ csrf_token() }}",
							id: selectedId,
							Task: siNo
						},
						success: function(response) {
							if (response.status === 'success') {
								dhtmlx.alert({
									title: "Sukses",
									text: response.message || "Shipping Instruction berhasil dihapus.",
									ok: "OK",
									callback: function() {
										$('#divlistOpportunity').html('');
										refreshdata();
									}
								});
							} else {
								dhtmlx.alert({
									title: "Gagal",
									text: response.message || "Gagal menghapus Shipping Instruction.",
									ok: "OK"
								});
							}
						},
						error: function(xhr) {
							var msg = (xhr.responseJSON && xhr.responseJSON.message)
								? xhr.responseJSON.message
								: "Gagal menghapus Shipping Instruction. Silakan coba lagi.";
							dhtmlx.alert({
								title: "Error",
								text: msg,
								ok: "OK"
							});
						}
					});
				}
			});
		} else {
			dhtmlx.alert({
				title: "Peringatan",
				text: "Pilih baris data terlebih dahulu",
				ok: "OK"
			});
		}
		return true;
	}

	function PrintSI()
	{
		var selectedId = getActiveSiId();
		if (selectedId != null) {
			window.open('cns-export-shipment-instruction-print?Task=' + ShipmentInstruction.cells(selectedId, 2).getValue(), '_blank');
		} else {
			dhtmlx.alert({
				title: "Peringatan",
				text: "Pilih baris data terlebih dahulu",
				ok: "OK"
			});
		}
		return true;
	}

	function openFSIDate()
	{
		var selectedId = getActiveSiId();
		if (!selectedId) {
			dhtmlx.alert({
				title: "Peringatan",
				text: "Pilih baris data terlebih dahulu",
				ok: "OK"
			});
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
			dhtmlx.alert({
				title: "Peringatan",
				text: "Shipping Instruction harus berstatus APPROVED sebelum dibuatkan Final SI.",
				ok: "OK"
			});
			return false;
		}

		var display = $('#divFSIDate').css("display");
		if (display === 'none') {
			$('#divFSIDate').css('display', 'inline-block');
		} else {
			dhtmlx.confirm({
				title: "Konfirmasi Final SI",
				text: "Buat Final SI untuk Shipping Instruction " + (siNo || selectedId) + " dengan tanggal " + $('#FSI_Date').val() + "?",
				ok: "Ya",
				cancel: "Batal",
				callback: function(result) {
					if (!result) {
						return;
					}
					$('#divFSIDate').css('display', 'none');
					LoadM('divlistOpportunity', 'cns-export-shipment-instruction-generate-fsi?Task=' + encodeURIComponent(siNo) + "&FSI_Date=" + encodeURIComponent($('#FSI_Date').val()));

					setTimeout(function() {
						refreshdata();
					}, 500);
				}
			});
		}
	}

	function CreateToFSI()
	{
		var selectedId = getActiveSiId();
		if (selectedId != null) {
			openFSIDate();
		} else {
			dhtmlx.alert({
				title: "Peringatan",
				text: "Pilih baris data terlebih dahulu",
				ok: "OK"
			});
		}
		return true;
	}

	function ShipmentInstructionNewBtn() {
		LoadM('divlistOpportunity', 'cns-export-shipment-instruction-new');
		return true;
	}

	function ShipmentInstructionKlik() {
		var selectedId = getActiveSiId();
		if (selectedId != null) {
			LoadM('divlistOpportunity', 'cns-export-shipment-instruction-detail?Task=' + selectedId);
		} else {
			dhtmlx.alert({
				title: "Peringatan",
				text: "Pilih baris data terlebih dahulu",
				ok: "OK"
			});
		}
		return true;
	}

	ShipmentInstruction = new dhtmlXGridObject('ShipmentInstruction');
	ShipmentInstruction.setImagePath("./dhtmlx/imgs/");
	ShipmentInstruction.setHeader("No,Order (DO)/Booking Confirmation (BC), SI No, SI Date,Kontainer (Seal), Tare, Netto (Kg), Netto (MT), Gross, Gross (MT), Status");
	ShipmentInstruction.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	ShipmentInstruction.setInitWidths("60,150,150,150,150,150,100,100,100,100,100,auto");
	ShipmentInstruction.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left");
	ShipmentInstruction.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	ShipmentInstruction.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	ShipmentInstruction.enableSmartRendering(true, 150);
	ShipmentInstruction.attachEvent("onRowDblClicked", ShipmentInstructionKlik);
	ShipmentInstruction.attachEvent("onRowSelect", function(id, ind) {
		selectedSiId = id;
		ShipmentInstructionID = id;
	});
	ShipmentInstruction.setSkin("dhx_skyblue");
	ShipmentInstruction.init();
	refreshdata();

	function refreshdata() {
		ShipmentInstruction.clearAndLoad('xml-cns-export-shipment-instruction?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());
	}
</script>
@endsection