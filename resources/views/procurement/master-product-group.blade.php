@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Master Group</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Lists</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="MasterGroup" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="MasterGroup.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Group" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Group" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save Group" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="MasterGroupDiv"></div>


	
	</div>
<script>





MasterGroup = new dhtmlXGridObject('MasterGroup');
MasterGroup.setImagePath("./dhtmlx/imgs/");
MasterGroup.setHeader("No,Group Name,Initial");
MasterGroup.setInitWidths("80,250,100");
MasterGroup.setColAlign("center,left,left,center,left,center,left,center,center,center");
MasterGroup.setColTypes("ro,ed,ed");
MasterGroup.setColSorting("int,str,str");
MasterGroup.setEditable(true);
MasterGroup.setNumberFormat("0,000",6,".",",");
MasterGroup.setSkin("dhx_skyblue");
PMasterGroup = new dataProcessor("master-product-group-update");
	PMasterGroup.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PMasterGroup.setUpdateMode("Off");
PMasterGroup.init(MasterGroup);
MasterGroup.init();
	reloadgroup();


function reloadgroup(){
	MasterGroup.clearAndLoad("xml-master-product-group");	

}

$("#SaveBM").click(function(){
		PMasterGroup.sendData();
		alert("Data was Saved!");
		
});
$("#DeleteBM").click(function(){
		MasterGroup.deleteSelectedRows();
		PMasterGroup.sendData();
		alert("Data were deleted!");
		reloadgroup();

		
});
$("#AddBM").click(function(){
	var idbi =MasterGroup.uid();
		MasterGroup.addRow(idbi,[MasterGroup.getRowsNum()+1,''],0);
		MasterGroup.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
