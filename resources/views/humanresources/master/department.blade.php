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
                <span class="active">Department</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="MasterDepartment" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="MasterDepartment.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete " class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add " class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save " class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="MasterDepartmentDiv"></div>


	
	</div>
<script>





MasterDepartment = new dhtmlXGridObject('MasterDepartment');
MasterDepartment.setImagePath("./dhtmlx/imgs/");
MasterDepartment.setHeader("No,Directorat,Department,Initial Code,Initial");
MasterDepartment.setInitWidths("80,200,250,100,100");
MasterDepartment.setColAlign("center,left,left,center,left,center,left,center,center,center");
MasterDepartment.setColTypes("ro,combo,ed,ed,ed");
MasterDepartment.setColSorting("int,str,str,str,str");
MasterDepartment.setEditable(true);
MasterDepartment.setSkin("dhx_skyblue");
PMasterDepartment = new dataProcessor("master-department-update");
	PMasterDepartment.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PMasterDepartment.setUpdateMode("Off");
PMasterDepartment.init(MasterDepartment);
MasterDepartment.init();
combocurr = MasterDepartment.getColumnCombo(1);
combocurr.enableFilteringMode(true);
combocurr.load("xml-combo-directorat");
	reloadgroup();


function reloadgroup(){
	MasterDepartment.clearAndLoad("xml-hrd-master-department");	

}

$("#SaveBM").click(function(){
		PMasterDepartment.sendData();
		alert("Data was Saved!");
		setTimeout(() => {
			reloadgroup();
		}, 500);
		
});
$("#DeleteBM").click(function(){
		MasterDepartment.deleteSelectedRows();
		PMasterDepartment.sendData();
		alert("Data were deleted!");
		reloadgroup();

		
});
$("#AddBM").click(function(){
	var idbi =MasterDepartment.uid();
		MasterDepartment.addRow(idbi,[MasterDepartment.getRowsNum()+1,''],0);
		MasterDepartment.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
