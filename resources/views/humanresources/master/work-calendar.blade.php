@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Master Data</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Sub Section</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="WorkCalendar" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="WorkCalendar.toExcel('./export/generate.php');"  />
			<input type="button" id="AddBM" NAME="AddBM" value="Add " class="btn btn-primary mt-2 mr-2"/>
			<input type="button" id="EditBM" NAME="EditBM" value="Edit " class="btn btn-info mt-2 mr-2"/>

			<input type="button" id="DetailBM" NAME="DetailBM" value="Detail Days" class="btn btn-secondary mt-2 mr-2"/>
		</div>

		<div id="WorkCalendarDiv"></div>


	
	</div>
<script>





WorkCalendar = new dhtmlXGridObject('WorkCalendar');
WorkCalendar.setImagePath("./dhtmlx/imgs/");
WorkCalendar.setHeader("No, Name, Employee Type, Weekly Hours");
WorkCalendar.setInitWidths("80,200,250,200");
WorkCalendar.setColAlign("center,left,left,center");
WorkCalendar.setColTypes("ro,ed,ed,ed");
WorkCalendar.setColSorting("int,str,str,str");
WorkCalendar.setEditable(true);
WorkCalendar.setSkin("dhx_skyblue");
PWorkCalendar = new dataProcessor("master-work-calendar-update");
	PWorkCalendar.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PWorkCalendar.setUpdateMode("Off");
PWorkCalendar.init(WorkCalendar);
WorkCalendar.init();
		reloadgroup();


function reloadgroup(){
	WorkCalendar.clearAndLoad("xml-hrd-master-work-calendar");	

}

$("#AddBM").click(function(){
    LoadM('WorkCalendarDiv','hrd-master-work-calendar-new');
});

$("#DetailBM").click(function(){
    WorkCalendarID = WorkCalendar.getSelectedId();
    if (WorkCalendarID == '' || WorkCalendarID == null) {
        alert('Please select a row to see detail.');
        return;
    }
    if (WorkCalendar.cells(WorkCalendarID, 2).getValue() != 'OFFICE') {
        LoadM('WorkCalendarDiv','hrd-master-work-calendar-detail-shift?task=' + WorkCalendarID);
    }else {
        LoadM('WorkCalendarDiv','hrd-master-work-calendar-detail?task=' + WorkCalendarID);
    }

});

$("#EditBM").click(function(){
    WorkCalendarID = WorkCalendar.getSelectedId();
    if (WorkCalendarID == '' || WorkCalendarID == null) {
        alert('Please select a row to edit.');
        return;
    }
    LoadM('WorkCalendarDiv','hrd-master-work-calendar-edit?id=' + WorkCalendar.getSelectedId());
});


</script>

@endsection
