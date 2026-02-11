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
                <span class="active">Directorat</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="MasterDirectorat" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="MasterDirectorat.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete " class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add " class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save " class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="MasterDirectoratDiv"></div>


	
	</div>
<script>





MasterDirectorat = new dhtmlXGridObject('MasterDirectorat');
MasterDirectorat.setImagePath("./dhtmlx/imgs/");
MasterDirectorat.setHeader("No,Directorat,Initial Code,Initial");
MasterDirectorat.setInitWidths("80,250,100,100");
MasterDirectorat.setColAlign("center,left,left,center,left,center,left,center,center,center");
MasterDirectorat.setColTypes("ro,ed,ed,ed");
MasterDirectorat.setColSorting("int,str,str,str,str");
MasterDirectorat.setEditable(true);
MasterDirectorat.setSkin("dhx_skyblue");
PMasterDirectorat = new dataProcessor("master-directorat-update");
	PMasterDirectorat.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PMasterDirectorat.setUpdateMode("Off");
PMasterDirectorat.init(MasterDirectorat);
MasterDirectorat.init();
combocurr = MasterDirectorat.getColumnCombo(1);
combocurr.enableFilteringMode(true);
combocurr.load("xml-combo-directorat");
	reloadgroup();


function reloadgroup(){
	MasterDirectorat.clearAndLoad("xml-hrd-master-directorat");	

}

$("#SaveBM").click(function(){
		PMasterDirectorat.sendData();
		alert("Data was Saved!");
		
});
$("#DeleteBM").click(function(){
		MasterDirectorat.deleteSelectedRows();
		PMasterDirectorat.sendData();
		alert("Data were deleted!");
		reloadgroup();

		
});
$("#AddBM").click(function(){
	var idbi =MasterDirectorat.uid();
		MasterDirectorat.addRow(idbi,[MasterDirectorat.getRowsNum()+1,''],0);
		MasterDirectorat.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
