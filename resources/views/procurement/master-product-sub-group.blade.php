@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Master Sub Group</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Lists</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="MasterSubGroup" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="MasterSubGroup.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Sub Group" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Sub Group" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save Sub Group" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="MasterSubGroupDiv"></div>


	
	</div>
<script>





MasterSubGroup = new dhtmlXGridObject('MasterSubGroup');
MasterSubGroup.setImagePath("./dhtmlx/imgs/");
MasterSubGroup.setHeader("No,Group Name,Sub Group,Initial");
MasterSubGroup.setInitWidths("80,200,250,100");
MasterSubGroup.setColAlign("center,left,left,center,left,center,left,center,center,center");
MasterSubGroup.setColTypes("ro,combo,ed,ed");
MasterSubGroup.setColSorting("int,str,str,str");
MasterSubGroup.setEditable(true);
MasterSubGroup.setSkin("dhx_skyblue");
PMasterSubGroup = new dataProcessor("master-product-sub-group-update");
	PMasterSubGroup.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PMasterSubGroup.setUpdateMode("Off");
PMasterSubGroup.init(MasterSubGroup);
MasterSubGroup.init();
combocurr = MasterSubGroup.getColumnCombo(1);
combocurr.enableFilteringMode(true);
combocurr.load("xml-combo-product-group");
	reloadgroup();


function reloadgroup(){
	MasterSubGroup.clearAndLoad("xml-master-product-sub-group");	

}

$("#SaveBM").click(function(){
		PMasterSubGroup.sendData();
		alert("Data was Saved!");
		
});
$("#DeleteBM").click(function(){
		MasterSubGroup.deleteSelectedRows();
		PMasterSubGroup.sendData();
		alert("Data were deleted!");
		reloadgroup();

		
});
$("#AddBM").click(function(){
	var idbi =MasterSubGroup.uid();
		MasterSubGroup.addRow(idbi,[MasterSubGroup.getRowsNum()+1,''],0);
		MasterSubGroup.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
