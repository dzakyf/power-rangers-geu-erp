@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Human Resources</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/dashboard1">Overtime</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">New Overtime</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
	<div id="Overtime" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
	<input type="button" id="DeleteBM" NAME="DeleteBM" value="Cancel Overtime" class="btn btn-danger mt-2 mr-2" />
	<input type="button" id="refreshdata" NAME="refreshdata" value="Refresh Data" class="btn btn-warning mt-2 mr-2" />
	<input type="button" id="AddBM" NAME="AddBM" value="Add Data" class="btn btn-info mt-2 mr-2" />
	<input type="button" id="SaveBM" NAME="SaveBM" value="Save" class="btn btn-success mt-2 mr-2" />
	<input type="button" id="SubmitApproval" NAME="SubmitApproval" value="Submit to Approve" class="btn btn-success mt-2 mr-2" />
</div>

<div id="OvertimeDiv"></div>



</div>
<?php

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$NIK = Auth::user()->Emp_ID;
$Nama = Auth::user()->Name;
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$taskid = "OV/$LSP/" . date('ymd') . "/" . date('his');
?>
<script>
	Overtime = new dhtmlXGridObject('Overtime');
	Overtime.setImagePath("./dhtmlx/imgs/");
	Overtime.setHeader("No,Task ID,NIK,Name,Start Date,End Date,Early IN,Early OUT,After IN,After OUT,Holiday,Total OT,Calculated OT,Rate,Salary Period,Amount Received,Reference,Note,Salary Group");
	Overtime.attachHeader("#rspan,#text_filter,#numeric_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter,#rspan,#text_filter");
	Overtime.setInitWidths("80,100,120,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,150,300,100");
	Overtime.setColAlign("center,center,center,left,left,center,left,center,center,center,center,center,center,center,center,center");
	Overtime.setColTypes("ro,ro,ro,ro,dhxCalendar,dhxCalendar,time,time,time,time,ch,ro,ro,edn,ro,ron,ro,ro,ed,ed,ro");
	Overtime.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	Overtime.setEditable(true);
	Overtime.enableEditEvents(true, false, true);
	Overtime.setDateFormat("%Y-%m-%d");
	Overtime.setNumberFormat("0,000", 13, ".", ",");
	Overtime.setNumberFormat("0,000", 15, ".", ",");
	Overtime.setSkin("dhx_skyblue");
	POvertime = new dataProcessor("overtime-new-update");
	POvertime.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		payload: {
			"ID": "<?php echo $taskid ?>"
		}

	})
	POvertime.setUpdateMode("Off");
	POvertime.init(Overtime);
	Overtime.init();
	Reload_Overtime();

	function Reload_Overtime() {
		Overtime.clearAndLoad("xml-overtime-new?taskid=<?php echo $taskid ?>");
	}

	var winCOA;
	var WCOA;

	function WindoWCOA(text, url, width, height) {
		winCOA = new dhtmlXWindows();

		winCOA.attachViewportTo('PagesContent');
		WCOA = winCOA.createWindow("WCOA", 0, 0, width, height);
		WCOA.keepInViewport(false);
		WCOA.setText(text);
		WCOA.attachURL(url, true);
		WCOA.button("minmax1").hide();
		WCOA.button("park").hide();
		WCOA.centerOnScreen();
		WCOA.allowMove();
	}

	$("#refreshdata").click(function() {
		Reload_Overtime();
	});
	$("#SaveBM").click(function() {
		POvertime.sendData();
		alert("Data was Saved!");
		setTimeout(function() {

			Reload_Overtime();
		}, 2000);
	});
	$("#DeleteBM").click(function() {
		Overtime.deleteSelectedRows();
		POvertime.sendData();
		alert("Data were Canceled!");
		Reload_Overtime();


	});
	$("#AddBM").click(function() {
		var idbi =Overtime.uid();
		Overtime.addRow(idbi,[Overtime.getRowsNum()+1,"<?php echo $taskid; ?>" + "-" + new Date().getTime(),'','','','','VALUES','0','0','0'],0);
		WindoWCOA("Select Employee","new-select-employee?id=" + idbi,650,350);
		Overtime.selectRow(0);
		

	});


	$("#RefreshBM").click(function() {
		Reload_Overtime();
	});
	$("#SubmitApproval").click(function() {
		dhtmlx.confirm('Are you sure want to submit?', 'Overtime Submission', function(r) {

			if (r == true) {
				var out = 0;
				for (var i = 0; i < Overtime.getRowsNum(); i++) {
					rowIdMov = Overtime.getRowId(i);
					POvertime.setUpdated(rowIdMov, true);
					Overtime.cells(rowIdMov, 0).setValue('SB');
				}


				POvertime.sendData();
				setTimeout(function() {
					alert("Data was Submitted!");
					location.href = 'overtime-new';
				}, 2000);

			}
		});
	});
</script>

@endsection