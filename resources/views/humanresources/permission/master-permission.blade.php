@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Master</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Permission Lists</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="Permission" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="Permission.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Data" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Data" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save Data" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="PermissionDiv"></div>


	
	</div>
<script>





Permission = new dhtmlXGridObject('Permission');
Permission.setImagePath("./dhtmlx/imgs/");
Permission.setHeader("No,Permission Name,Group,Days Limit");
Permission.attachHeader("#rspan,#text_filter,#text_filter,#text_filter");
Permission.setInitWidths("80,450,150,100");
Permission.setColAlign("center,left,center,right");
Permission.setColTypes("ro,ed,combo,ed");
Permission.setColSorting("int,str,str,str");
Permission.setEditable(true);
Permission.enableMultiline(true);
Permission.enableSmartRendering(true, 100);
Permission.setSkin("dhx_skyblue");
PPermission = new dataProcessor("hrd-master-permission-submit");
	PPermission.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PPermission.setUpdateMode("Off");
	
PPermission.init(Permission);
Permission.enableEditEvents(true,true,true);
Permission.init();
combocurr = Permission.getColumnCombo(2);
combocurr.enableFilteringMode(true);
combocurr.load("xml-combo-permission-group");

setTimeout(function(){
	reloadmasterpermission();
},1000);

function reloadmasterpermission(){
	Permission.clearAndLoad("xml-hrd-master-permission");	

}
$("#SaveBM").click(function(){
		PPermission.sendData();
		alert("Permission was Saved!");
		setTimeout(function(){
			reloadmasterpermission();
		},1000);
		
});
$("#DeleteBM").click(function(){
	dhtmlx.confirm('Delete Data?', 'Permission', function(r) {
			
		if (r==true){
							
				Permission.deleteSelectedRows();
				PPermission.sendData();
				alert("Data were deleted!");
				reloadmasterpermission();
		}
	});
		
});
$("#AddBM").click(function(){
	var idbi =Permission.uid();
		Permission.addRow(idbi,[Permission.getRowsNum()+1,''],0);
		Permission.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadmasterpermission();		
});



</script>

@endsection
