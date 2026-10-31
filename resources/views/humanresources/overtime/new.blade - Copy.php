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
                <span class="active">New Overtime</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="Overtime" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Cancel Overtime" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Data" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="OvertimeDiv"></div>


	
	</div>
<script>





Overtime = new dhtmlXGridObject('Overtime');
Overtime.setImagePath("./dhtmlx/imgs/");
Overtime.setHeader("No,Task ID,NIK,Name,Start Date,End Date,Early IN,Early OUT,After IN,After OUT,Holiday,Total OT,Calculated OT,Is Outdated,Salary Period,Amount Received,Reference,Created Date,Created By,File Archive");
Overtime.attachHeader("#rspan,#text_filter,#numeric_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter, #rspan");
Overtime.setInitWidths("80,100,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
Overtime.setColAlign("center,center,left,center,left,center,left,center,center,center,center,center,center,center,center");
Overtime.setColTypes("ro,ro,ro,ro,dhxCalendar,dhxCalendar,time,time,time,time,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro");
Overtime.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
Overtime.setEditable(true);
Overtime.setNumberFormat("0,000",15,".",",");
Overtime.setSkin("dhx_skyblue");
POvertime = new dataProcessor("overtime-new-update");
	POvertime.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
POvertime.setUpdateMode("Off");
POvertime.init(Overtime);
Overtime.init();


var winCOA;
var WCOA;
function WindoWCOA(text,url,width,height) {
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

$("#SaveBM").click(function(){
		POvertime.sendData();
		alert("Data was Saved!");
		setTimeout(function(){
			
			Reload_Overtime();
		},2000);
});
$("#DeleteBM").click(function(){
		Overtime.deleteSelectedRows();
		POvertime.sendData();
		alert("Data were Canceled!");
		Reload_Overtime();

		
});
$("#AddBM").click(function(){
	var idbi =Overtime.uid();
		Overtime.addRow(idbi,[Overtime.getRowsNum()+1,'','','','','','VALUES','0','0','0'],0);
		WindoWCOA("Select Employee","new-select-employee?id=" + idbi,650,350);
		Overtime.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	Reload_Overtime();		
});



</script>

@endsection
