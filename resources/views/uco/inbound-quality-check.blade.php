@extends('layout.mainlayout-mitra')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>INVENTORY</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="./">Unused Crude Oil (UCO)</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">INBOUND QUALITY CHECK</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="InboundQualityCheck" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="InboundQualityCheck.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			</div>

		<div id="MasterDirectoratDiv"></div>


	
	</div>
<script>





InboundQualityCheck = new dhtmlXGridObject('InboundQualityCheck');
InboundQualityCheck.setImagePath("./dhtmlx/imgs/");
InboundQualityCheck.setHeader("No,Wo No,Total Liter,From Supplier,Date Pickup,Weigh-in Date,Status Check,Result,Checker,Checker Date,Analysis,Note");
InboundQualityCheck.setInitWidths("80,150,150,100,100,150,150,150");
InboundQualityCheck.setColAlign("center,center,left,center,left,center,left,center,center,center");
InboundQualityCheck.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
InboundQualityCheck.setColSorting("int,str,str,str,str");
InboundQualityCheck.setEditable(false);
InboundQualityCheck.setSkin("dhx_skyblue");

InboundQualityCheck.init();
	reloadgroup();


function reloadgroup(){
	InboundQualityCheck.clearAndLoad("xml-inbound-timbang");	

}


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
