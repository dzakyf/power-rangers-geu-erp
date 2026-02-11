@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Master</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Location</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="LocationMaster" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="LocationMaster.toExcel('./export/generate.php');"  />
			<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Data" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Data" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save Data" class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="LocationMasterDiv"></div>


	
	</div>
<script>





LocationMaster = new dhtmlXGridObject('LocationMaster');
LocationMaster.setImagePath("./dhtmlx/imgs/");
LocationMaster.setHeader("No,Location Name,Location ID,Address,City,Phone,PIC,Main City,Latitude,Longitude");
LocationMaster.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan");
LocationMaster.setInitWidths("80,150,80,300,100,100,100,100,100,150");
LocationMaster.setColAlign("center,left,left,center,left,center,left,center,center,center");
LocationMaster.setColTypes("ro,ed,ed,ed,ed,ed,ed,ed,ed,ed");
LocationMaster.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
LocationMaster.setEditable(true);
LocationMaster.enableMultiline(true);
LocationMaster.enableSmartRendering(true, 100);
LocationMaster.setSkin("dhx_skyblue");
PLocationMaster = new dataProcessor("master-location-submit");
	PLocationMaster.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PLocationMaster.init(LocationMaster);
LocationMaster.enableEditEvents(true,true,true);
LocationMaster.init();

	reloadbank();


function reloadbank(){
	//$.blockUI();
	LocationMaster.clearAndLoad("xml-accounting-master-location",finishload);	

}
function finishload(){
	//$.unblockUI();
}
$("#SaveBM").click(function(){
		PLocationMaster.sendData();
		alert("Location was Saved!");
		
});
$("#DeleteBM").click(function(){
		LocationMaster.deleteSelectedRows();
		PLocationMaster.sendData();
		alert("Data were deleted!");
		reloadbank();

		
});
$("#AddBM").click(function(){
	var idbi =LocationMaster.uid();
		LocationMaster.addRow(idbi,[LocationMaster.getRowsNum()+1,''],0);
		LocationMaster.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadbank();		
});



</script>

@endsection
