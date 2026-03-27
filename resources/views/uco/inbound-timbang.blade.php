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
                <span class="active">INBOUND TIMBANG</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="InboundTimbang" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="InboundTimbang.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			</div>

		<div id="MasterDirectoratDiv"></div>


	
	</div>
<script>





InboundTimbang = new dhtmlXGridObject('InboundTimbang');
InboundTimbang.setImagePath("./dhtmlx/imgs/");
InboundTimbang.setHeader("No,Wo No,Supplier,Date Pickup,Weigh-in Date,Picker,Total Liter,Total Kg");
InboundTimbang.setInitWidths("80,150,150,100,100,150,150,150");
InboundTimbang.setColAlign("center,center,left,center,left,center,left,center,center,center");
InboundTimbang.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
InboundTimbang.setColSorting("int,str,str,str,str");
InboundTimbang.setEditable(false);
InboundTimbang.setSkin("dhx_skyblue");

InboundTimbang.init();
	reloadgroup();


function reloadgroup(){
	InboundTimbang.clearAndLoad("xml-inbound-timbang");	

}


$("#RefreshBM").click(function(){
	reloadgroup();		
});



</script>

@endsection
