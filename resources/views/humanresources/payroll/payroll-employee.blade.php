<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$Priv = Auth::user()->Priv_ID;
// if ($Priv != 'HRD') {
// 	echo '<script>window.location = "/";</script>';
// }
?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Human Resources</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Payroll</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Employee Salary Lists</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="ContentPages">
		<div class="FormInput_Form_Content">

			<div class="FormInput_left">

				Salary Group
			</div>
			<div class="FormInput_right">
				<select id='Main_ID' onchange="refreshdata()">

					<?php
					$q = DB::select(DB::raw("SELECT * FROM payrol_main_group order by Main_ID"));
					foreach ($q as $fa) {

						echo "<option value='$fa->Main_ID'>$fa->Main_ID - $fa->Group_Name :: $fa->Category</option>";
					}
					?>
				</select>


			</div>

		</div>

	</div>
	<div id="EmployeeSalary" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
	<div id="CekReload">
		<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="EmployeeSalary.toExcel('./export/generate.php');" />

		<input type="button" id="reloadata" name="reloadata" value="Recounting" onclick="recounting()" class="btn btn-success mt-2 mr-2" />
	</div>
	<div id="divlistProjectOrg"></div>
</div>

<script>
	function EmployeeSalaryKlik() {

		EmployeeSalaryID = EmployeeSalary.cells(EmployeeSalary.getSelectedId(), 2).getValue();

		if (EmployeeSalaryID != '') {

			LoadM('divlistProjectOrg', 'payroll-employee-detail?ID=' + EmployeeSalaryID);

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
			$.blockUI({ message: '<h5><i class="fa fa-spinner fa-spin me-2"></i> Sedang melakukan kalkulasi ulang (TER, Pasal 17 & Presensi)...</h5>' });
		}

		$.ajax({
			url: '/payroll-recounting-ajax',
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
	EmployeeSalary.setImagePath("./dhtmlx/imgs/");
	EmployeeSalary.setHeader("No.,Name,NIK,Gender,Group Category,Salary Group,Position,Pokok,Tunjangan,Tunjangan PPH,Deduction,PPH,THP");
	EmployeeSalary.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter");
	EmployeeSalary.setInitWidths("70,150,100,100,100,100,100,120,100,100,100,100,100,100");
	EmployeeSalary.setColAlign("center,left,center,center,center,center,center,center,center,center");
	EmployeeSalary.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron");
	EmployeeSalary.setColSorting("int,str,str,str,str,str,str,str");
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
		EmployeeSalary.clearAndLoad('xml-payroll-employee?Main_ID=' + $("#Main_ID").val());

	}
</script>
@endsection