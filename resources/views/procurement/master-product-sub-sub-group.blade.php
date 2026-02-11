@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Master Sub Sub Group</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Lists</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="MasterSubSubGroup" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="MasterSubSubGroup.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Sub Group" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Sub Group" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save Sub Group" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="MasterSubSubGroupDiv"></div>


	
	</div>
<script>





MasterSubSubGroup = new dhtmlXGridObject('MasterSubSubGroup');
MasterSubSubGroup.setImagePath("./dhtmlx/imgs/");
MasterSubSubGroup.setHeader("No,Group Name,Sub Group,Sub Sub Group,Initial,Dept Approval");
MasterSubSubGroup.setInitWidths("80,200,250,100,100,250");
MasterSubSubGroup.setColAlign("center,left,left,center,left,center,left,center,center,center");
MasterSubSubGroup.setColTypes("ro,combo,combo,ed,ed,combo");
MasterSubSubGroup.setColSorting("int,str,str,str,str,str,str");
MasterSubSubGroup.setEditable(true);
MasterSubSubGroup.setSkin("dhx_skyblue");
PMasterSubSubGroup = new dataProcessor("master-product-sub-sub-group-update");
	PMasterSubSubGroup.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PMasterSubSubGroup.setUpdateMode("Off");
PMasterSubSubGroup.init(MasterSubSubGroup);
MasterSubSubGroup.init();
combocurr = MasterSubSubGroup.getColumnCombo(1);
combocurr.enableFilteringMode(true);
combocurr.load("xml-combo-product-group");
combocurr2 = MasterSubSubGroup.getColumnCombo(2);
combocurr2.enableFilteringMode(true);
combocurr2.load("xml-combo-product-sub-group");
	reloadgroup();
combocurr3 = MasterSubSubGroup.getColumnCombo(5);
combocurr3.enableFilteringMode(true);
combocurr3.load("xml-combo-product-approval");
	reloadgroup();

function reloadgroup(){
	MasterSubSubGroup.clearAndLoad("xml-master-product-sub-sub-group");	

}

$("#SaveBM").click(function(){
		PMasterSubSubGroup.sendData();
		alert("Data was Saved!");
		
});
$("#DeleteBM").click(function(){
		MasterSubSubGroup.deleteSelectedRows();
		PMasterSubSubGroup.sendData();
		alert("Data were deleted!");
		reloadgroup();

		
});
$("#AddBM").click(function(){
	var idbi =MasterSubSubGroup.uid();
		MasterSubSubGroup.addRow(idbi,[MasterSubSubGroup.getRowsNum()+1,''],0);
		MasterSubSubGroup.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
