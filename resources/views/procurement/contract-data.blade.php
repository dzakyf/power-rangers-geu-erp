@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Purchase Master</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Contract Data</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="ContractData" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="ContractData.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="DeleteBM" NAME="DeleteBM" value="Delete Contract" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="AddBM" NAME="AddBM" value="Add Contract" class="btn btn-info mt-2 mr-2"/>
		</div>

		<div id="ContractDataDiv"></div>


	
	</div>
<script>


ContractData = new dhtmlXGridObject('ContractData');
ContractData.setImagePath("./dhtmlx/imgs/");
ContractData.setHeader("No.,Contract Type,Contract No,Reference No,Start Date,End Date,Supplier,Note");
ContractData.setInitWidths("70,130,100,150,150,100,120,400");
ContractData.setColAlign("center,center,center,center,center,center,center,center,center");
ContractData.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
ContractData.enableMultiline(true);
ContractData.setEditable(false);
ContractData.setSkin("dhx_skyblue");
PContractData = new dataProcessor("po-contract-data-update");
	PContractData.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PContractData.setUpdateMode("Off");
PContractData.init(ContractData);
ContractData.init();
	reloadgroup();


function reloadgroup(){
	ContractData.clearAndLoad("xml-po-contract-data");	

}


$("#AddBM").click(function(){
	LoadM('ContractDataDiv','add-contract-data');

});
$("#DeleteBM").click(function(){
	
	dhtmlx.confirm('Are you sure want to delete the data?', 'Contract Management', function(r) {
			
		if (r==true){
			
			ContractData.deleteSelectedRows();
			PContractData.sendData();
			alert("Data were deleted!");
			reloadgroup();

			
		}
	});
});


		




$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
