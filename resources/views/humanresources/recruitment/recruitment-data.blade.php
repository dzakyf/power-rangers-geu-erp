<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;

$GridView = 'recruitment-data';
$AllCols = recruitment_data_columns();
$DefaultCols = recruitment_data_default_columns();
$ActiveCols = $DefaultCols;

if (Schema::hasTable('user_grid_prefs')) {
	$SavedJson = DB::table('user_grid_prefs')->where('username', $Saya)->where('view', $GridView)->value('columns_json');
	$SavedCols = $SavedJson ? json_decode($SavedJson, true) : null;
	if (is_array($SavedCols) && count($SavedCols)) {
		$ValidCols = array_values(array_intersect($SavedCols, array_keys($AllCols)));
		if (count($ValidCols)) {
			$ActiveCols = $ValidCols;
		}
	}
}

$Meta = [];
foreach ($AllCols as $ck => $cm) {
	$Meta[$ck] = [
		'label'  => $cm[0],
		'width'  => $cm[1],
		'align'  => $cm[2],
		'filter' => $cm[3],
		'sort'   => $cm[4],
		'type'   => 'ro',
	];
}

// Ambil daftar Requisition / PTK yang sudah APPROVED untuk dropdown update posisi
$ApprovedPtk = DB::table('requisition')
	->where('Status', 'APPROVED')
	->whereNotNull('Job_Position')
	->select('ID', 'Task_ID', 'Job_Position', 'Department', 'Location')
	->orderBy('Job_Position', 'ASC')
	->get();
?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header mb-3">
	<h1>Human Resources</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Recruitment</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Data Management (Talent Pool)</span>
		</li>
	</ol>
</div>

<!-- Panel Pengaturan Kolom (Dinamis seperti di employee-lists) -->
<div class="card shadow border-0 mb-3">
	<div class="card-body p-3">
		<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
			<div class="d-flex align-items-center flex-wrap gap-2">
				<a href="javascript:void(0)" onclick="toggleRecColPanel()" class="text-primary text-decoration-none fw-bold">
					<span id="RecColToggleIcon">&#9656;</span> Pengaturan Kolom (<span id="RecColCount">{{ count($ActiveCols) }}</span> aktif)
				</a>
				<input type="text" id="RecColSearch" placeholder="Cari kolom..." onkeyup="filterRecCols()" onclick="expandRecColPanel()" style="padding:4px 10px;width:220px;border:1px solid #ced4da;border-radius:4px;font-size:13px;" />
				<span id="RecColFound" class="text-muted ms-1 small">{{ count($AllCols) }} kolom</span>
				<span id="RecColMsg" class="ms-2 text-success fw-bold small"></span>
			</div>
			<div>
				<button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleMultiSelectMode()">
					<i class="fa fa-check-square-o me-1"></i> <span id="btnSelectModeText">Enable Multi-Select</span>
				</button>
			</div>
		</div>
		<div id="RecColPanel" style="margin-top:15px;display:none;padding-top:12px;border-top:1px dashed #ced4da;">
			<div class="row g-2">
				@foreach($AllCols as $ck => $cm)
				<div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 recColItem" title="{{ $cm[0] }}">
					<div class="form-check m-0 py-1">
						<input type="checkbox" class="form-check-input recColCheck me-2" id="col_{{ $ck }}" value="{{ $ck }}" {{ in_array($ck, $ActiveCols) ? 'checked' : '' }} style="cursor:pointer;">
						<label class="form-check-label text-dark text-truncate d-block" for="col_{{ $ck }}" style="cursor:pointer;" title="{{ $cm[0] }}">
							{{ $cm[0] }}
						</label>
					</div>
				</div>
				@endforeach
			</div>
			<div class="mt-3 pt-2 border-top d-flex gap-2">
				<button type="button" class="btn btn-success btn-sm me-2" onclick="saveRecruitmentCols(false)">
					<i class="fa fa-check me-1"></i> Simpan Pilihan
				</button>
				<button type="button" class="btn btn-secondary btn-sm" onclick="saveRecruitmentCols(true)">
					<i class="fa fa-undo me-1"></i> Reset Default
				</button>
			</div>
		</div>
	</div>
</div>

<div class="card shadow border-0">
	<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3">
		<h5 class="card-title mb-0 text-white font-weight-bold"><i class="fa fa-users me-2"></i>Data Management Candidates</h5>
		<span class="badge bg-light text-dark">
			<i class="fa fa-info-circle me-1"></i> Dobel klik baris untuk detail &middot; Gunakan Ctrl / Shift + Klik untuk multi-pilih baris
		</span>
	</div>
	<div class="card-body p-0">
		<div id="RecruitmentLists" style="width:100%;height:460px; background-color:white;overflow:hidden;border:1px solid #ced4da;"></div>
		<div class="p-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color:#f8f9fa;">
			<div class="d-flex align-items-center flex-wrap gap-2">
				<button type="button" id="ekselRecruitmentListsDet" class="btn btn-warning btn-sm" onclick="RecruitmentLists.toExcel('./export/generate.php');">
					<i class="fa fa-file-excel-o me-1"></i> Export Excel
				</button>
				<button type="button" id="tambahKandidat" class="btn btn-success btn-sm" onclick="newRecruitment()">
					<i class="fa fa-user-plus me-1"></i> Tambah Kandidat
				</button>
				<button type="button" id="updateKandidat" class="btn btn-primary btn-sm" onclick="updateRecruitment()">
					<i class="fa fa-edit me-1"></i> Detail / Update
				</button>

				<!-- BATCH ACTIONS -->
				<div class="btn-group me-1">
					<button type="button" class="btn btn-info btn-sm text-white" onclick="openBatchPositionModal()">
						<i class="fa fa-briefcase me-1"></i> Update Posisi / Link PTK
					</button>
					<button type="button" class="btn btn-dark btn-sm" onclick="batchCreateUserAccounts()">
						<i class="fa fa-id-badge me-1"></i> Create User Akun Kandidat
					</button>
				</div>

				<button type="button" id="delets" class="btn btn-danger btn-sm" onclick="delsRecruitment()">
					<i class="fa fa-times-circle me-1"></i> Cancel Selected
				</button>
				<button type="button" class="btn btn-secondary btn-sm" onclick="refreshdata()">
					<i class="fa fa-refresh me-1"></i> Refresh
				</button>
			</div>
			<div class="text-muted small">
				<span id="selectedRowsBadge" class="badge badge-info px-2 py-1">0 kandidat dipilih</span>
			</div>
		</div>
	</div>
</div>

<!-- Modal Update Posisi / Link PTK -->
<div class="modal fade" id="modalBatchPosition" tabindex="-1" role="dialog" aria-labelledby="modalBatchPositionLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content shadow">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title font-weight-bold" id="modalBatchPositionLabel">
					<i class="fa fa-briefcase me-2"></i> Update Posisi & Link ke PTK (Approved)
				</h5>
				<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$('#modalBatchPosition').modal('hide')">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="text-muted small mb-3">
					Perubahan ini akan diterapkan ke <strong id="modalSelectedCount" class="text-primary">0</strong> kandidat yang sedang dipilih di tabel.
				</p>
				
				<div class="form-group mb-3">
					<label class="form-label font-weight-bold text-dark">Pilih dari PTK Approved:</label>
					<select id="selectApprovedPtk" class="form-control" onchange="onSelectPtkChange()">
						<option value="">-- Pilih Posisi dari Requisition (PTK) Approved --</option>
						@foreach($ApprovedPtk as $ptk)
						<option value="{{ $ptk->Task_ID }}" data-pos="{{ $ptk->Job_Position }}">
							[{{ $ptk->Department }} - {{ $ptk->Location }}] {{ $ptk->Job_Position }} ({{ $ptk->Task_ID }})
						</option>
						@endforeach
					</select>
					<small class="form-text text-muted">Secara otomatis akan menghubungkan kandidat ke Requisition PTK ini.</small>
				</div>

				<div class="text-center my-2 text-muted font-italic">- ATAU INPUT POSISI MANUAL -</div>

				<div class="form-group mb-3">
					<label class="form-label font-weight-bold text-dark">Nama Posisi Manual:</label>
					<input type="text" id="inputCustomPosition" class="form-control" placeholder="Contoh: Staff Accounting" oninput="onCustomPosInput()">
					<small class="form-text text-muted">Gunakan jika posisi tidak terkait langsung dengan nomor PTK yang terdaftar.</small>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" onclick="$('#modalBatchPosition').modal('hide')">Batal</button>
				<button type="button" class="btn btn-primary btn-sm" onclick="submitBatchPosition()">
					<i class="fa fa-save me-1"></i> Terapkan Posisi
				</button>
			</div>
		</div>
	</div>
</div>

<div id="divlistRecruitment" class="mt-3"></div>

<!-- Custom CSS - Kontras & Header dhtmlxGrid -->
<style>
	.card-header.bg-primary {
		background-color: #0d6efd !important;
		color: #ffffff !important;
	}
	.card-header.bg-primary h5 {
		color: #ffffff !important;
	}
	div.gridbox_dhx_skyblue table.hdr td {
		font-weight: 600 !important;
		font-size: 12px;
	}
	div.gridbox_dhx_skyblue table.obj tr.rowselected td {
		background-color: #d1e7dd !important;
		color: #0f5132 !important;
	}
</style>

<script>
var RecColMeta = {!! json_encode($Meta) !!};
var RecActiveCols = {!! json_encode(array_values($ActiveCols)) !!};
var RecDefaultCols = {!! json_encode(array_values($DefaultCols)) !!};
var RecColTotal = {!! count($AllCols) !!};

var RecruitmentLists = null;
var PRecruitmentLists = null;
var isMultiSelectActive = false;

function expandRecColPanel() {
	$("#RecColPanel").slideDown();
	$("#RecColToggleIcon").html("&#9662;");
}

function toggleRecColPanel() {
	$("#RecColPanel").slideToggle(200, function() {
		$("#RecColToggleIcon").html($(this).is(":visible") ? "&#9662;" : "&#9656;");
	});
}

function filterRecCols() {
	var q = $("#RecColSearch").val().toLowerCase();
	if (q !== "") expandRecColPanel();
	var n = 0;
	$(".recColItem").each(function() {
		var hit = $(this).text().toLowerCase().indexOf(q) !== -1;
		$(this).toggle(hit);
		if (hit) n++;
	});
	$("#RecColFound").text(q === "" ? (RecColTotal + " kolom") : (n + " cocok"));
}

function updateRecColCount() {
	$("#RecColCount").text($(".recColCheck:checked").length);
}

$(document).on("change", ".recColCheck", updateRecColCount);

function getSelectedIds() {
	if (!RecruitmentLists) return [];
	var sel = RecruitmentLists.getSelectedRowId();
	if (!sel) return [];
	return sel.split(',');
}

function updateSelectedCountBadge() {
	var count = getSelectedIds().length;
	$('#selectedRowsBadge').text(count + ' kandidat dipilih');
	if (count > 0) {
		$('#selectedRowsBadge').removeClass('badge-secondary').addClass('badge-success');
	} else {
		$('#selectedRowsBadge').removeClass('badge-success').addClass('badge-info');
	}
}

function toggleMultiSelectMode() {
	if (!RecruitmentLists) return;
	isMultiSelectActive = !isMultiSelectActive;
	RecruitmentLists.enableMultiselect(isMultiSelectActive);
	if (isMultiSelectActive) {
		$('#btnSelectModeText').text('Multi-Select ON');
		alert('Multi-Select aktif. Anda dapat memilih beberapa baris dengan klik atau Ctrl/Shift + Klik.');
	} else {
		$('#btnSelectModeText').text('Enable Multi-Select');
	}
}

function RecruitmentListsKlik() {
	var ids = getSelectedIds();
	if (ids.length === 1) {
		LoadM('divlistRecruitment', 'recruitment-lists-detail?ID=' + encodeURIComponent(ids[0]));
	}
	return true;
}

function newRecruitment() {
	LoadM('divlistRecruitment', 'recruitment-new-inline');
}

function updateRecruitment() {
	var ids = getSelectedIds();
	if (ids.length === 0) {
		alert('Pilih minimal 1 kandidat terlebih dahulu!');
		return;
	}
	LoadM('divlistRecruitment', 'recruitment-lists-detail?ID=' + encodeURIComponent(ids[0]));
}

function delsRecruitment() {
	var ids = getSelectedIds();
	if (ids.length === 0) {
		alert('Pilih kandidat yang ingin dibatalkan terlebih dahulu!');
		return;
	}

	dhtmlx.confirm({
		title: 'Cancel Recruitment?',
		text: 'Apakah Anda yakin ingin membatalkan recruitment untuk ' + ids.length + ' candidate yang dipilih?',
		ok: 'Yes, Cancel',
		cancel: 'Batal',
		callback: function(r) {
			if (r == true) {
				RecruitmentLists.deleteSelectedRows();
				if (PRecruitmentLists) {
					PRecruitmentLists.sendData();
				}
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		}
	});
}

/**
 * BATCH ACTION 1: CREATE USER ACCOUNTS KHUSUS CANDIDATE
 */
function batchCreateUserAccounts() {
	var ids = getSelectedIds();
	if (ids.length === 0) {
		alert('Pilih minimal 1 kandidat yang ingin dibuatkan akun user!');
		return;
	}

	if (!confirm('Buat akun login kandidat untuk ' + ids.length + ' orang yang dipilih?\n(Username: Task_ID, Password default: 12345678)')) {
		return;
	}

	if (typeof $.blockUI === 'function') $.blockUI({ message: 'Membuat akun kandidat...' });

	$.post('{{ url("/recruitment-data-batch-create-user") }}', {
		_token: "{{ csrf_token() }}",
		ids: ids
	}, function(res) {
		if (typeof $.unblockUI === 'function') $.unblockUI();
		if (res.status === 'ok') {
			alert(res.message);
		} else {
			alert('Gagal: ' + (res.message || 'Terjadi kesalahan sistem.'));
		}
	}, 'json').fail(function() {
		if (typeof $.unblockUI === 'function') $.unblockUI();
		alert('Gagal memproses pembuatan user account.');
	});
}

/**
 * BATCH ACTION 2: UPDATE POSITION / LINK TO REQUISITION PTK
 */
function openBatchPositionModal() {
	var ids = getSelectedIds();
	if (ids.length === 0) {
		alert('Pilih minimal 1 kandidat terlebih dahulu untuk memperbarui posisi!');
		return;
	}
	$('#modalSelectedCount').text(ids.length);
	$('#selectApprovedPtk').val('');
	$('#inputCustomPosition').val('');
	$('#modalBatchPosition').modal('show');
}

function onSelectPtkChange() {
	var val = $('#selectApprovedPtk').val();
	if (val) {
		$('#inputCustomPosition').val('');
	}
}

function onCustomPosInput() {
	if ($('#inputCustomPosition').val().trim() !== '') {
		$('#selectApprovedPtk').val('');
	}
}

function submitBatchPosition() {
	var ids = getSelectedIds();
	var reqTaskId = $('#selectApprovedPtk').val();
	var customPos = $('#inputCustomPosition').val().trim();

	if (!reqTaskId && !customPos) {
		alert('Pilih salah satu posisi dari PTK Approved atau masukkan posisi manual!');
		return;
	}

	if (typeof $.blockUI === 'function') $.blockUI({ message: 'Mengupdate posisi kandidat...' });

	$.post('{{ url("/recruitment-data-batch-update-position") }}', {
		_token: "{{ csrf_token() }}",
		ids: ids,
		requisition_task_id: reqTaskId,
		custom_position: customPos
	}, function(res) {
		if (typeof $.unblockUI === 'function') $.unblockUI();
		if (res.status === 'ok') {
			$('#modalBatchPosition').modal('hide');
			alert(res.message);
			refreshdata();
		} else {
			alert('Gagal: ' + (res.message || 'Terjadi kesalahan.'));
		}
	}, 'json').fail(function() {
		if (typeof $.unblockUI === 'function') $.unblockUI();
		alert('Gagal memperbarui posisi kandidat.');
	});
}

/**
 * Bangun grid dengan kolom aktif dan filter header dinamis
 */
function buildRecruitmentDataGrid(keys) {
	document.getElementById('RecruitmentLists').innerHTML = '';
	RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
	RecruitmentLists.setImagePath("./dhtmlx/imgs/");

	var header = ['No.'], fh = ['#rspan'], w = ['50'], al = ['center'], ty = ['ro'], so = ['int'];
	for (var i = 0; i < keys.length; i++) {
		var m = RecColMeta[keys[i]];
		if (!m) continue;
		header.push(m.label);
		fh.push(m.filter);
		w.push(m.width);
		al.push(m.align);
		ty.push(m.type);
		so.push(m.sort);
	}

	RecruitmentLists.setHeader(header.join(','));
	RecruitmentLists.attachHeader(fh.join(','));
	RecruitmentLists.setInitWidths(w.join(','));
	RecruitmentLists.setColAlign(al.join(','));
	RecruitmentLists.setColTypes(ty.join(','));
	RecruitmentLists.setColSorting(so.join(','));
	RecruitmentLists.enableKeyboardSupport(true);
	RecruitmentLists.enableMultiselect(true);
	RecruitmentLists.setEditable(false);
	RecruitmentLists.attachEvent("onRowDblClicked", RecruitmentListsKlik);
	RecruitmentLists.attachEvent("onRowSelect", updateSelectedCountBadge);
	RecruitmentLists.setSkin("dhx_skyblue");

	PRecruitmentLists = new dataProcessor("recruitment-data-submit");
	PRecruitmentLists.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
	});
	PRecruitmentLists.setTransactionMode("POST", true);
	PRecruitmentLists.setUpdateMode("Off");
	PRecruitmentLists.init(RecruitmentLists);

	RecruitmentLists.init();
	refreshdata();
}

function saveRecruitmentCols(reset) {
	var keys = reset ? RecDefaultCols.slice() : $(".recColCheck:checked").map(function() { return this.value; }).get();
	if (!keys.length) {
		alert('Pilih minimal 1 kolom.');
		return;
	}
	$("#RecColMsg").text('Menyimpan...');
	$.post('recruitment-data-columns-save', {
		_token: "{{ csrf_token() }}",
		cols: keys.join(',')
	}, function(res) {
		if (res && res.status === 'ok') {
			$(".recColCheck").each(function() {
				this.checked = (keys.indexOf(this.value) !== -1);
			});
			updateRecColCount();
			RecActiveCols = keys;
			buildRecruitmentDataGrid(keys);
			$("#RecColMsg").text('Pilihan kolom tersimpan.');
			setTimeout(function() { $("#RecColMsg").text(''); }, 3000);
		} else {
			$("#RecColMsg").text('');
			alert('Gagal menyimpan: ' + ((res && res.message) ? res.message : 'unknown error'));
		}
	}, 'json').fail(function() {
		$("#RecColMsg").text('');
		alert('Gagal menyimpan pilihan kolom.');
	});
}

function refreshdata() {
	if (!RecruitmentLists) return;
	RecruitmentLists.clearAndLoad('xml-recruitment-data?cols=' + encodeURIComponent(RecActiveCols.join(',')));
	RecruitmentLists.sortRows(0, "str", "des");
	updateSelectedCountBadge();
}

buildRecruitmentDataGrid(RecActiveCols);
</script>
@endsection
