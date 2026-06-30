@extends('layout.mainlayout')

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
		 <div id="MasterSection" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="MasterSection.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete " class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add " class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save " class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="MasterSectionDiv"></div>


	
	</div>
<script>

MasterSection = new dhtmlXGridObject('MasterSection');
MasterSection.setImagePath("./dhtmlx/imgs/");
MasterSection.setHeader("No,Directorat,Department, Name, Level,Status");
MasterSection.setInitWidths("80,200,250,200,100,100");
MasterSection.setColAlign("center,left,left,center,left,center,left");
MasterSection.setColTypes("ro,ro,ro,ro,ro,ro");
MasterSection.setColSorting("int,str,str,str,str,str");
MasterSection.setEditable(true);
MasterSection.setSkin("dhx_skyblue");
PMasterSection = new dataProcessor("master-section-update");
	PMasterSection.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PMasterSection.setUpdateMode("Off");
PMasterSection.init(MasterSection);
MasterSection.init();
reloadgroup();


function reloadgroup(){
	MasterSection.clearAndLoad("xml-hrd-master-approval");	

}

$("#SaveBM").click(function(){
		PMasterSection.sendData();
		alert("Data was Saved!");
		
});
$("#DeleteBM").click(function(){
		MasterSection.deleteSelectedRows();
		PMasterSection.sendData();
		alert("Data were deleted!");
		reloadgroup();

		
});
$("#AddBM").click(function(){
	LoadM("MasterSectionDiv", "hrd-master-approval-new");
		
});


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
