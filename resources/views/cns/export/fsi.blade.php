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
			<a href="/">Final Shipping Instruction</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Final Shipping Instruction List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">FINAL SHIPPING INSTRUCTION LIST</div>
	<div class="card">
		<div class="card-body">
			<div class="mb-2">
				Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
				s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
				<button onclick="refreshdata()" class="btn btn-primary p-2">Refresh</button>
			</div>
			<div id="ShipmentInstruction" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="newSchedule" name="newSchedule" value="New Final SI" class="btn btn-primary mt-2 mr-2" onclick="ShipmentInstructionNewBtn()" />
				<input type="button" id="DetailSchedule" name="DetailSchedule" value="Detail Final SI" class="btn btn-info mt-2 mr-2" onclick="ShipmentInstructionKlik()" />
				<input type="button" id="approveFSI" name="approveFSI" value="Approve Final SI" class="btn btn-success mt-2 mr-2" onclick="ApproveFSI()" />
				<input type="button" id="printSI" name="printSI" value="Print Final SI" class="btn btn-secondary mt-2 mr-2" onclick="PrintSI()" />
				<div id="divCreatePL" style="display:none;">
					<input type="text" name="PL_Date" id="PL_Date" value="{{ date("Y-m-d") }}" class="form-control mt-2 mr-2" placeholder="PL Date" style="display:inline-block;" readonly />
					<button type="button" class="btn btn-secondary mt-2 mr-2" onclick="$('#divCreatePL').hide();">Batal</button>
				</div>
				<input type="button" id="createPL" name="createPL" value="Create Packing List" class="btn btn-success mt-2 mr-2" onclick="CreatePL()" />
				<div id="divCreateCI" style="display:none;">
					<input type="text" name="CI_Date" id="CI_Date" value="{{ date("Y-m-d") }}" class="form-control mt-2 mr-2" placeholder="CI Date" style="display:inline-block;" readonly />
					<button type="button" class="btn btn-secondary mt-2 mr-2" onclick="$('#divCreateCI').hide();">Batal</button>
				</div>
				<input type="button" id="createCI" name="createCI" value="Create Commercial Invoice" class="btn btn-success mt-2 mr-2" onclick="CreateCI()" />
				<input type="button" id="ekselShipmentInstructionDet" name="ekselShipmentInstructionDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ShipmentInstruction.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

	<div class="modal fade" id="modalApproveFSI" tabindex="-1" role="dialog" aria-labelledby="modalApproveFSILabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header bg-success text-white">
					<h5 class="modal-title" id="modalApproveFSILabel"><i class="fa fa-check-circle mr-1"></i> Approve Final Shipping Instruction</h5>
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
				dhtmlx.alert({
					title: "Peringatan",
					text: "Pilih baris data terlebih dahulu pada tabel Final Shipping Instruction!",
					ok: "OK"
				});
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
				dhtmlx.alert({
					title: "Peringatan",
					text: "Final Shipping Instruction " + (fsiNo || selectedId) + " ini sudah berstatus APPROVED!",
					ok: "OK"
				});
				return false;
			}

			$('#approveFsiDocNo').text(fsiNo || selectedId);
			$('#approveFsiUser').val('');
			$('#modalApproveFSI').modal('show');
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

	function SubmitApproveFSI() {
		try {
			var selectedId = getActiveFsiId();
			var approverId = $('#approveFsiUser').val();

			if (!approverId) {
				dhtmlx.alert({
					title: "Peringatan",
					text: "Silakan pilih user Approved By terlebih dahulu!",
					ok: "OK"
				});
				return false;
			}

			var fsiNo = "";
			try {
				fsiNo = ShipmentInstruction.cells(selectedId, 1).getValue();
			} catch (cellErr) {}

			var approverName = $('#approveFsiUser option:selected').text().trim();

			dhtmlx.confirm({
				title: "Konfirmasi Approval",
				text: "Apakah Anda yakin ingin menyetujui (Approve) Final Shipping Instruction " + (fsiNo || selectedId) + " dengan Approver: " + approverName + "?",
				ok: "Ya, Setujui",
				cancel: "Batal",
				callback: function(result) {
					if (!result) {
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
						url: "cns-export-final-shipment-instruction-approve",
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
								dhtmlx.alert({
									title: "Sukses",
									text: response.message || "Final Shipping Instruction berhasil di-approve.",
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
									text: (response && response.message) || "Gagal approve Final Shipping Instruction.",
									ok: "OK"
								});
							}
						},
						error: function(xhr, status, error) {
							if (typeof $.unblockUI === 'function') {
								$.unblockUI();
							}
							var msg = "Gagal approve Final Shipping Instruction.";
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

	function CreatePL() {
		var selectedId = getActiveFsiId();
		if (selectedId != null) {
			var display = $('#divCreatePL').css('display');
			if (display == 'none') {
				$('#divCreatePL').css('display', 'inline-block');
			} else {
				var fsiNo = ShipmentInstruction.cells(selectedId, 1).getValue();
				dhtmlx.confirm({
					title: "Konfirmasi Packing List",
					text: "Buat Packing List untuk Final SI " + fsiNo + " dengan tanggal " + $('#PL_Date').val() + "?",
					ok: "Ya",
					cancel: "Batal",
					callback: function(result) {
						if (!result) return;
						$('#divCreatePL').css('display', 'none');
						LoadM('DivProcess', 'cns-export-final-shipment-instruction-create-pl?Task=' + encodeURIComponent(fsiNo) + '&PL_Date=' + encodeURIComponent($('#PL_Date').val()));

						setTimeout(function() {
							refreshdata();
						}, 500);
					}
				});
			}
		} else {
			dhtmlx.alert({
				title: "Peringatan",
				text: "Pilih baris data terlebih dahulu",
				ok: "OK"
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
				dhtmlx.confirm({
					title: "Konfirmasi Commercial Invoice",
					text: "Buat Commercial Invoice untuk Final SI " + fsiNo + " dengan tanggal " + $('#CI_Date').val() + "?",
					ok: "Ya",
					cancel: "Batal",
					callback: function(result) {
						if (!result) return;
						$('#divCreateCI').css('display', 'none');
						LoadM('DivProcess', 'cns-export-final-shipment-instruction-create-ci?Task=' + encodeURIComponent(fsiNo) + "&CI_Date=" + encodeURIComponent($('#CI_Date').val()));

						setTimeout(function() {
							refreshdata();
						}, 500);
					}
				});
			}
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
		var selectedId = getActiveFsiId();
		if (selectedId != null) {
			var rowId = selectedId.toString().split(',')[0];
			var finalSiNo = '';
			try {
				finalSiNo = ShipmentInstruction.cells(rowId, 1).getValue();
			} catch (e) {}
			var task = (finalSiNo && finalSiNo.trim() !== '') ? finalSiNo.trim() : rowId;
			window.open('{{ url("cns-export-final-shipment-instruction-print") }}?Task=' + encodeURIComponent(task), '_blank');
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
		LoadM('divlistOpportunity', 'cns-export-final-shipment-instruction-new');
		return true;
	}

	function ShipmentInstructionKlik() {
		var selectedId = getActiveFsiId();
		if (selectedId != null) {
			LoadM('divlistOpportunity', 'cns-export-final-shipment-instruction-detail?Task=' + selectedId);
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
	ShipmentInstruction.setHeader("No,Final SI No, Final SI Date, SI No, Kontainer (Seal), Tare, Netto (Kg), Netto (MT), Gross, Gross (MT), Status");
	ShipmentInstruction.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	ShipmentInstruction.setInitWidths("60,150,100,150,150,150,100,100,100,100,100,auto");
	ShipmentInstruction.setColAlign("center,left,center,left,left,left,left,left,left,left,left,left,left,left");
	ShipmentInstruction.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	ShipmentInstruction.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	ShipmentInstruction.enableSmartRendering(true, 150);
	ShipmentInstruction.attachEvent("onRowDblClicked", ShipmentInstructionKlik);
	ShipmentInstruction.attachEvent("onRowSelect", function(id, ind) {
		selectedFsiId = id;
		ShipmentInstructionID = id;
	});
	ShipmentInstruction.setSkin("dhx_skyblue");
	ShipmentInstruction.init();
	refreshdata();

	function refreshdata() {
		ShipmentInstruction.clearAndLoad('xml-cns-export-final-shipment-instruction?tgl1=' + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());
	}
</script>
@endsection