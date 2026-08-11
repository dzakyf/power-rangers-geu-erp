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
                <span class="active">Position</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="MaterPosition" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="MaterPosition.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete " class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add " class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="SaveBM" NAME="SaveBM" value="Save " class="btn btn-success mt-2 mr-2"/>
		</div>

		<div id="MaterPositionDiv"></div>


	
	</div>
<script>





MaterPosition = new dhtmlXGridObject('MaterPosition');
MaterPosition.setImagePath("./dhtmlx/imgs/");
MaterPosition.setHeader("No,Directorat,Department,Subposition,Position,Initial Code,Initial, Level");
MaterPosition.setInitWidths("80,200,250,200,200,100,100,100");
MaterPosition.setColAlign("center,left,left,center,center,center,left,center,center,center,center");
MaterPosition.setColTypes("ro,combo,combo,combo,ed,ed,ed,ed");
MaterPosition.setColSorting("int,str,str,str,str,str,str,str");
MaterPosition.setEditable(true);
MaterPosition.setSkin("dhx_skyblue");
PMaterPosition = new dataProcessor("master-position-update");
	PMaterPosition.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PMaterPosition.setUpdateMode("Off");
PMaterPosition.init(MaterPosition);
MaterPosition.init();
combocurr = MaterPosition.getColumnCombo(1);
combocurr.enableFilteringMode(true);
combocurr.load("xml-combo-directorat");
combocurr2 = MaterPosition.getColumnCombo(2);
combocurr2.enableFilteringMode(true);
combocurr2.load("xml-combo-department");
combocurr2 = MaterPosition.getColumnCombo(3);
combocurr2.enableFilteringMode(true);
combocurr2.load("xml-combo-section");
		reloadgroup();


function reloadgroup(){
	MaterPosition.clearAndLoad("xml-hrd-master-position");	

}

$("#SaveBM").click(function(){
		PMaterPosition.sendData();
		alert("Data was Saved!");
		setTimeout(() => {
			reloadgroup();
		}, 500);
		
});
$("#DeleteBM").click(function(){
		MaterPosition.deleteSelectedRows();
		PMaterPosition.sendData();
		alert("Data were deleted!");
		reloadgroup();

		
});
$("#AddBM").click(function(){
	var idbi =MaterPosition.uid();
		MaterPosition.addRow(idbi,[MaterPosition.getRowsNum()+1,''],0);
		MaterPosition.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
