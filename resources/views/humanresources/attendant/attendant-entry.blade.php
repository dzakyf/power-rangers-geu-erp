@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Attendant</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Attendant Log</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="AttendantEntry" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="AttendantEntry.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Cancel Selected Periode" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Periode" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SETLIST" NAME="SETLIST" value="Data Atttendant" class="btn btn-info mt-2 mr-2"/>

		</div>

		<div id="AttendantEntryDiv"></div>


	
	</div>
<script>





AttendantEntry = new dhtmlXGridObject('AttendantEntry');
AttendantEntry.setImagePath("./dhtmlx/imgs/");
AttendantEntry.setHeader("No,Task No,NIK,Name,Periode,Status,Created By,Created Date");
AttendantEntry.setInitWidths("80,130,150,250,100,100,100,100,100");
AttendantEntry.setColAlign("center,left,left,center,left,center,left,center,center,center");
AttendantEntry.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
AttendantEntry.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
AttendantEntry.setEditable(false);
AttendantEntry.setSkin("dhx_skyblue");
PAttendantEntry = new dataProcessor("attendant-entry-update");
	PAttendantEntry.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PAttendantEntry.setUpdateMode("Off");
PAttendantEntry.init(AttendantEntry);
AttendantEntry.init();
	Reload_AttendantEntry();

function Reload_AttendantEntry(){
	AttendantEntry.clearAndLoad("xml-attendant-entry");	

}

$("#DeleteBM").click(function(){
		AttendantEntry.deleteSelectedRows();
		PAttendantEntry.sendData();
		alert("Data were deleted!");
		Reload_AttendantEntry();

		
});
$("#AddBM").click(function(){
		$.blockUI();
		var idbi =AttendantEntry.uid();
		AttendantEntry.addRow(idbi,[AttendantEntry.getRowsNum()+1,''],0);
		AttendantEntry.selectRow(0);
		PAttendantEntry.sendData();
		setTimeout(function(){
			$.unblockUI();
			alert("Task Created! Double Click on the Task for entry detail");
			Reload_AttendantEntry();
		},2000);
		
});


$("#RefreshBM").click(function(){
	Reload_AttendantEntry();		
});




$("#SETLIST").click(function(){
	AttendantID  = AttendantEntry.cells(AttendantEntry.getSelectedId(),1).getValue();
	LoadM('AttendantEntryDiv','attendant-entry-lists?ID=' + AttendantID);
		
});


</script>

@endsection
