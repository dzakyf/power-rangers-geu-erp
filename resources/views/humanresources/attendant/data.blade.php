@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Attendant</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="AttendantData" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="AttendantData.toExcel('./export/generate.php');"  />
			<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="SETLIST" NAME="SETLIST" value="Data Atttendant" class="btn btn-info mt-2 mr-2"/>

		</div>

		<div id="AttendantDataDiv"></div>


	
	</div>
<script>





AttendantData = new dhtmlXGridObject('AttendantData');
AttendantData.setImagePath("./dhtmlx/imgs/");
AttendantData.setHeader("No,Task No,NIK,Name,Periode,Status,Created By,Created Date");
AttendantData.setInitWidths("80,130,150,250,100,100,100,100,100");
AttendantData.setColAlign("center,left,left,center,left,center,left,center,center,center");
AttendantData.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
AttendantData.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
AttendantData.setEditable(false);
AttendantData.setSkin("dhx_skyblue");
AttendantData.init();
	Reload_AttendantData();

function Reload_AttendantData(){
	AttendantData.clearAndLoad("xml-attendant-data");	

}



$("#RefreshBM").click(function(){
	Reload_AttendantData();		
});




$("#SETLIST").click(function(){
	AttendantID  = AttendantData.cells(AttendantData.getSelectedId(),1).getValue();
	LoadM('AttendantDataDiv','attendant-data-lists?ID=' + AttendantID);
		
});


</script>

@endsection
