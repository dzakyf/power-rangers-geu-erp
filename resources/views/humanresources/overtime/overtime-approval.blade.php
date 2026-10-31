@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Overtime</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Approval</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	Periode <input type="text" id="Tgl1" size="10" value="<?php echo date('Y-m-d') ?>" readonly="readonly" /> to
		<input type="text" id="Tgl2" size="10" value="<?php echo date('Y-m-d') ?>" readonly="readonly" />
				<input type="button" id="SubmitCheck" onclick="Reload_Overtime()" value="Load Data" class="btn btn-success mt-2 mr-2" />

		 <div id="Overtime" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="refreshdata" NAME="refreshdata" value="Refresh Data" class="btn btn-warning mt-2 mr-2"/>
			<input type="button" id="SubmitApproval" NAME="SubmitApproval" value="Approve" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="OvertimeDiv"></div>


	
	</div>
<?php

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

?>	
<script>


	var SHPREVDAY_Cal;
		SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1", "Tgl2"]);
		SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");



Overtime = new dhtmlXGridObject('Overtime');
Overtime.setImagePath("./dhtmlx/imgs/");
Overtime.setHeader("Approve,Task ID,NIK,Name,Start Date,End Date,Early IN,Early OUT,After IN,After OUT,Holiday,Total OT,Calculated OT,Rate,Salary Period,Amount Received,Reference,File Archive");
Overtime.attachHeader("#master_checkbox,#text_filter,#numeric_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter,#rspan");
Overtime.setInitWidths("80,100,120,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
Overtime.setColAlign("center,center,center,left,left,center,left,center,center,center,center,center,center,center,center");
Overtime.setColTypes("ch,ro,ro,ro,dhxCalendar,dhxCalendar,time,time,time,time,ch,ro,ro,edn,ro,ron,ro,ro,ro,ro");
Overtime.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
Overtime.setEditable(true);
Overtime.enableEditEvents(true,false,true);
Overtime.setDateFormat("%Y-%m-%d"); 
Overtime.setNumberFormat("0,000",13,".",",");
Overtime.setNumberFormat("0,000",15,".",",");
Overtime.setSkin("dhx_skyblue");
POvertime = new dataProcessor("overtime-approval-update");
	POvertime.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	
    })
POvertime.setUpdateMode("Off");
POvertime.init(Overtime);
Overtime.init();
Reload_Overtime();

function Reload_Overtime(){
	
	Overtime.clearAndLoad("xml-overtime-approval?Tgl1=" + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());
}

$("#refreshdata").click(function(){
		Reload_Overtime();
});

$("#SubmitApproval").click(function(){
	dhtmlx.confirm('Are you sure want to approve?', 'Overtime Submission', function(r) {
		
		if (r==true){
			
			POvertime.sendData();
			setTimeout(function(){
				alert("Data was Approved!");
				location.href='overtime-approval';
			},2000);
			
		}
	});	
});


</script>

@endsection
