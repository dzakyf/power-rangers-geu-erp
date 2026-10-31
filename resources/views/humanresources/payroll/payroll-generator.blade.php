<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$dept =  Auth::user()->Department;

$taskid = "PG-" . $LSP . "-" . date("ymdhis");
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
			<a href="/dashboard1">Payroll</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">New Payroll Generator</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="ContentPages">
		<div class="FormInput_Form_Content">
			<div class="FormInput_left">

				Gen ID

			</div>
			<div class="FormInput_right">

				<input type="text" id="Task_ID" size="50" disabled="disabled" value="<?php echo $taskid ?>" />


			</div>
			<div class="FormInput_left">

				Period

			</div>
			<div class="FormInput_right">

				<input type="hidden" id="Payroll_Date" size="10" readonly="readonly" value="<?php echo date("Y-m-d") ?>" />
				<input type="text" id="Salary_Period" size="10" readonly="readonly" value="<?php echo date("Y-m") ?>" />
				:: <input type="text" id="Total" size="20" readonly="readonly" value="0" />

			</div>


			<div class="FormInput_left">

				Generate for Template
			</div>
			<div class="FormInput_right">
				<select id='Category' onchange="loadsal()">
					<option value="SALARY">SALARY</option>

					<?php
					$q = DB::select(DB::raw("SELECT * FROM payrol_master_template order by Payroll_ID"));
					foreach ($q as $fa) {

						echo "<option value='$fa->Payroll_ID'>$fa->Name</option>";
					}
					?>
				</select>


			</div>
			<div class="FormInput_left">

				Category

			</div>
			<div class="FormInput_right">

				<select id='Use_For' onchange="refreshdata()">
					<option value="INTERNAL">INTERNAL</option>
					<option value="NON_INTERNAL">ALL OS/OT</option>
					<option value="CUSTOM">CUSTOM</option>

				</select>

			</div>
			<div class="FormInput_left">

				Apply to Group

			</div>
			<div class="FormInput_right">

				<select id='Main_ID' onchange="refreshdata()">
					<option value=''>Select</option>
					<?php
					$q = DB::select(DB::raw("SELECT * FROM payrol_main_group"));
					foreach ($q as $fa) {

						echo "<option value='$fa->Main_ID'>$fa->Group_Name - $fa->Use_For - $fa->Reference</option>";
					}
					?>

				</select>

			</div>
			<div class="FormInput_left">

				Note

			</div>
			<div class="FormInput_right">

				<input type="text" id="Note" size="50" />

			</div>
		</div>

	</div>
	<div id="EmployeeSalary" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>

	<div id="divtom">
		<input type="button" id="UploadValues" NAME="UploadValues" value="Upload Data" class="btn  btn-warning mt-2 mr-2" /> &nbsp;&nbsp;<a href="./template_upload.php" target="_blank" style="cursor:pointer;text-decoration:none;color:blue;font-size:10px;">Download Xls Format</a> ::

		<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Process" class="btn  btn-primary mt-2 mr-2" />
	</div>
	<div id="divlistProjectOrg"></div>


</div>

<script>
	function loadsal() {
		setTimeout(function() {
			totalnya = 0;

			for (var i = 0; i < EmployeeSalary.getRowsNum(); i++) {
				rowIdMov = EmployeeSalary.getRowId(i);
				PEmployeeSalary.setUpdated(rowIdMov, true);
				EmployeeSalary.cells(rowIdMov, 16).setValue($("#Category").val());
				EmployeeSalary.cells(rowIdMov, 15).setValue($("#Use_For").val());
				totalnya += parseFloat(EmployeeSalary.cells(rowIdMov, 14).getValue());
			}
			$("#Total").val(totalnya);
		}, 2000);

		$.unblockUI();

	}


	var winNewJournal;
	var WNewJournal;

	function WindoWNewJournal(text, url, width, height) {
		winNewJournal = new dhtmlXWindows();

		winNewJournal.attachViewportTo('PagesContent');
		WNewJournal = winNewJournal.createWindow("WNewJournal", 0, 0, width, height);
		WNewJournal.keepInViewport(false);
		WNewJournal.setText(text);
		WNewJournal.attachURL(url, true);
		WNewJournal.button("minmax1").hide();
		WNewJournal.button("park").hide();
		WNewJournal.centerOnScreen();
		WNewJournal.allowMove();
	}

	function EmployeeSalaryKlik() {

		EmployeeSalaryID = EmployeeSalary.cells(EmployeeSalary.getSelectedId(), 2).getValue();

		if (EmployeeSalaryID != '') {

			LoadM('divlistProjectOrg', 'payroll-generator-detail?ID=' + EmployeeSalaryID);

		}
		return true;
	}

	$('#UploadValues').click(function() {
		WindoWNewJournal("Upload Data Values", "payroll-generator-upload", 518, 290);
	});
	EmployeeSalary = new dhtmlXGridObject('EmployeeSalary');
	EmployeeSalary.setImagePath("./dhtmlx/imgs/");
	EmployeeSalary.setHeader("Select,Name,NIK,Gender,Group Category,Salary Group,Position,Pokok,Allowance,Overtime,KJK,WHT Allowance,Deduction,PPH,THP,Category,Type Sal,Note");
	EmployeeSalary.attachHeader("#master_checkbox,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#combo_filter,,");
	EmployeeSalary.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,,,");
	EmployeeSalary.setInitWidths("70,150,100,100,100,100,100,100,100,120,100,100,100,100,100,100,100,400");
	EmployeeSalary.setColAlign("center,left,center,center,center,center,center,center,center,center,center,center,center,left");
	EmployeeSalary.setColTypes("ch,ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron,ro,ro,ro");
	EmployeeSalary.setColSorting("int,str,str,str,str,str,str,str,str");
	EmployeeSalary.setNumberFormat("0,000", 7, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 8, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 9, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 10, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 11, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 12, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 13, ".", ",");
	EmployeeSalary.setNumberFormat("0,000", 14, ".", ",");
	PEmployeeSalary = new dataProcessor("payroll-generator-detail-update");
	PEmployeeSalary.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		payload: {
			"ID": "<?php echo $taskid ?>",
			"Period": $("#Payroll_Date").val()
		}

	})
	PEmployeeSalary.setUpdateMode("Off");
	PEmployeeSalary.init(EmployeeSalary);
	EmployeeSalary.attachEvent("onRowDblClicked", EmployeeSalaryKlik);
	EmployeeSalary.setSkin("dhx_skyblue");
	EmployeeSalary.setEditable(true);
	EmployeeSalary.init();

	function refreshdata() {
		$.blockUI();
		EmployeeSalary.clearAndLoad('xml-payroll-generator-employee?Use_For=' + $("#Use_For").val() + '&Main_ID=' + $("#Main_ID").val() + '&Category=' + $("#Category").val(), loadsal);

	}


	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Payroll_Date"]);
	myCalendar.setDateFormat("%Y-%m");


	$('#SubmitCustDetail').click(function() {
		dhtmlx.confirm('Confirm Process to Generate?', 'Generator', function(r) {

			if (r == true) {
				$.blockUI();
				
						
					PEmployeeSalary.sendData();
					gettingVars = getAllValues();

					$.ajax({
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						url: "payroll-generator-submit?_token={{ csrf_token() }}",
						type: "POST",
						data: gettingVars,
						success: function(data, textStatus, jqXHR) {
							var dataPos = data.indexOf("success");
							if (dataPos != '-1') {
								setTimeout(function(){
									alert("Salary generated with Task <?php echo $taskid ?>!");
									location.href = 'payroll-approval';
									$.unblockUI();
								},5000);
							} else {
								alert('Salary  Failed to Generate!');
								$.unblockUI();
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							alert("Error Internal! Please Try again!");
							$.unblockUI();

						}
					});
				
				}
		});


	})

	function getAllValues() {
		var inputValues = [];
		$('#ContentPages input,select,textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if (type != "button" || type != "submit") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($this.is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}
</script>
@endsection