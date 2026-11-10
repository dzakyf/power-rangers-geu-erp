<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$Priv = Auth::user()->Priv_ID;
?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
	<div class="mb-2 mb-md-0">
		<h4 class="content-title text-white fw-bold mb-1"
			style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
			<i class="fe fe-user-check me-2"></i>Setup Gaji Pegawai (Employee Salary)
		</h4>
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb mb-0">
				<li class="breadcrumb-item">
					<a href="{{ url('payroll-tax') }}" style="color: rgba(255, 255, 255, 0.8) !important;">
						Payroll
					</a>
				</li>
				<li class="breadcrumb-item active text-white" aria-current="page"
					style="color: #ffffff !important; font-weight: 600;">
					Employee Salary Lists
				</li>
			</ol>
		</nav>
	</div>
	<div class="d-flex align-items-center flex-wrap gap-2">
		<a href="{{ url('payroll-tax') }}" class="btn btn-sm rounded-pill shadow-sm"
			style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
			<i class="fe fe-percent me-1"></i> Master Tax &amp; TER
		</a>
		<a href="{{ url('payroll-generator') }}" class="btn btn-sm rounded-pill shadow-sm"
			style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
			<i class="fe fe-play me-1"></i> Generator Payroll
		</a>
		<a href="{{ url('payroll-approval') }}" class="btn btn-sm rounded-pill shadow-sm"
			style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
			<i class="fe fe-check-circle me-1"></i> Approval Payroll
		</a>
	</div>
</div>
<!-- End Main Content Header -->

<div class="card border-0 shadow-sm rounded-4 mb-4">
	<div class="card-header bg-white border-0 pt-3 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
		<div>
			<h5 class="fw-bold mb-0 text-primary">Daftar Penggajian Karyawan per Group</h5>
			<small class="text-muted">Kelola nilai gaji pokok, tunjangan, potongan absensi (Alpha/Late), dan pemotongan TER/BPJS.</small>
		</div>
		<div class="d-flex align-items-center gap-2">
			<label class="fw-semibold text-muted mb-0 small">Salary Group:</label>
			<select id='Main_ID' class="form-select form-select-sm rounded-pill" style="min-width: 260px;" onchange="refreshdata()">
				<?php
				$q = DB::select(DB::raw("SELECT * FROM payrol_main_group order by Main_ID"));
				foreach ($q as $fa) {
					echo "<option value='$fa->Main_ID'>$fa->Main_ID - $fa->Group_Name :: $fa->Category</option>";
				}
				?>
			</select>
		</div>
	</div>
	<div class="card-body p-3">
		<div id="EmployeeSalary" style="width:100%; height:420px; background-color:white; overflow:hidden;"></div>
		<div class="d-flex flex-wrap gap-2 mt-3" id="CekReload">
			<button type="button" id="reloadata" class="btn btn-success rounded-pill px-4 text-white" onclick="recounting()">
				<i class="fa fa-calculator me-1"></i> Recounting (Sync Presensi, TER & Deduction)
			</button>
			<button type="button" id="ekselPODListDet" class="btn btn-warning rounded-pill px-3 text-dark" onclick="EmployeeSalary.toExcel('{{ asset('export/generate.php') }}');">
				<i class="fa fa-file-excel me-1"></i> Export Excel
			</button>
			<button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="refreshdata()">
				<i class="fa fa-sync me-1"></i> Refresh
			</button>
		</div>
	</div>
</div>

<div id="divlistProjectOrg"></div>

<script>
	function EmployeeSalaryKlik() {
		var selectedId = EmployeeSalary.getSelectedId();
		if (!selectedId) return true;
		var employeeSalaryID = EmployeeSalary.cells(selectedId, 2).getValue();

		if (employeeSalaryID != '') {
			LoadM('divlistProjectOrg', '{{ url('payroll-employee-detail') }}?ID=' + employeeSalaryID);
		}
		return true;
	}

	function recounting() {
		var mainId = $("#Main_ID").val();
		if (!mainId) {
			alert('Pilih Salary Group terlebih dahulu.');
			return;
		}

		if (typeof $.blockUI === 'function') {
			$.blockUI({ message: '<div class="p-3"><h5><i class="fa fa-spinner fa-spin me-2"></i> Sedang melakukan kalkulasi ulang (TER, Pasal 17 & Presensi)...</h5></div>' });
		}

		$.ajax({
			url: '{{ url('payroll-recounting-ajax') }}',
			type: 'POST',
			data: {
				_token: '{{ csrf_token() }}',
				Main_ID: mainId
			},
			success: function(res) {
				if (typeof $.unblockUI === 'function') {
					$.unblockUI();
				}
				if (res.success) {
					alert('Recounting Berhasil!\nTotal Karyawan: ' + res.data.total_employees + '\nTotal THP: Rp ' + Number(res.data.total_thp).toLocaleString('id-ID'));
					refreshdata();
				} else {
					alert('Gagal: ' + res.message);
				}
			},
			error: function(err) {
				if (typeof $.unblockUI === 'function') {
					$.unblockUI();
				}
				alert('Terjadi kesalahan saat memproses kalkulasi ulang.');
			}
		});
	}

	EmployeeSalary = new dhtmlXGridObject('EmployeeSalary');
	EmployeeSalary.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
	EmployeeSalary.setHeader("No.,Name,NIK,Gender,Group Category,Salary Group,Position,Pokok,Tunjangan,Tunjangan PPH,Deduction,PPH,THP");
	EmployeeSalary.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter");
	EmployeeSalary.setInitWidths("70,180,110,90,110,110,120,120,110,110,110,110,120");
	EmployeeSalary.setColAlign("center,left,center,center,center,center,center,right,right,right,right,right,right");
	EmployeeSalary.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron");
	EmployeeSalary.setColSorting("int,str,str,str,str,str,str,int,int,int,int,int,int");
	EmployeeSalary.setNumberFormat("0,000", 7, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 8, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 9, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 10, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 11, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 12, ".", ",");
	EmployeeSalary.attachEvent("onRowDblClicked", EmployeeSalaryKlik);
	EmployeeSalary.setSkin("dhx_skyblue");
	EmployeeSalary.enableMultiline(true);
	EmployeeSalary.init();
	refreshdata();

	function refreshdata() {
		var mainId = $("#Main_ID").val();
		EmployeeSalary.clearAndLoad('{{ url('xml-payroll-employee') }}?Main_ID=' + mainId);
	}
</script>
@endsection
