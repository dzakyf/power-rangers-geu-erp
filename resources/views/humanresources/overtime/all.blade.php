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
                <span class="active">ALL Data</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	<div>
			Employee <input type="text" id="Name" name="Name" size="40"/> <input type="hidden" id="NIK" name="NIK" size="30"/> &nbsp; &nbsp;
			Periode&nbsp;&nbsp; <input type="text" id="Tgl1" name="Tgl1"  size="10" readonly="readonly" value="<?php echo date("Y-m-d")?>"/>
			&nbsp;&nbsp;To &nbsp;&nbsp;<input type="text" id="Tgl2" name="Tgl2"  size="10" readonly="readonly" value="<?php echo date("Y-m-d")?>"/>
			<input type="button" id="Process" name="Process"  class="btn btn-info mt-2 mr-2" value="Process" onclick="Reload_Overtime();"  />
	</div>
		 <div id="Overtime" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="Overtime.toExcel('./export/generate.php');"  />
			<input type="button" id="refreshdata" NAME="refreshdata" value="Refresh Data" class="btn btn-info mt-2 mr-2"/>
		</div>

		<div id="OvertimeDiv"></div>


	
	</div>
<script>


var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
myCalendar.setDateFormat("%Y-%m-%d");

$("#Name").coolautosuggest({
		url:"search-employee?chars=",
		showThumbnail:false,
		showDescription:true,
		idField:$("#NIK"),
		width:500,
		minChars:1,
		onSelected:function(result){
		  if(result!=null){
			$("#NIK").val(result.id); 
			$("#Name").val(result.data); 
		  }
		}
	});


Overtime = new dhtmlXGridObject('Overtime');
Overtime.setImagePath("./dhtmlx/imgs/");
Overtime.setHeader("No,Task ID,NIK,Name,Start Date,End Date,Early IN,Early OUT,After IN,After OUT,Holiday,Total OT,Calculated OT,Rate,Salary Period,Amount Received,Reference,Note,Salary Group");
Overtime.attachHeader("#rspan,#text_filter,#numeric_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter,#rspan,#text_filter");
Overtime.setInitWidths("80,100,120,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,150,300,100");
Overtime.setColAlign("center,center,center,left,left,center,left,center,center,center,center,center,center,center,center,center");
Overtime.setColTypes("ro,ro,ro,ro,dhxCalendar,dhxCalendar,time,time,time,time,ch,ro,ro,edn,ro,ron,ro,ro,ed,ed,ro");
Overtime.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
Overtime.setEditable(true);
Overtime.enableEditEvents(true,false,true);
Overtime.setDateFormat("%Y-%m-%d"); 
Overtime.setNumberFormat("0,000",13,".",",");
Overtime.setNumberFormat("0,000",15,".",",");
Overtime.setSkin("dhx_skyblue");
Overtime.init();
Reload_Overtime();

function Reload_Overtime(){
	Overtime.clearAndLoad("xml-overtime-all?NIK=" + $("#NIK").val() + "&Tgl1=" + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());
}

$("#refreshdata").click(function(){
		Reload_Overtime();
});

$("#RefreshBM").click(function(){
	Reload_Overtime();		
});


</script>

@endsection
