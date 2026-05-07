@extends('layout.mainlayout-cns')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">PURCHASING REQUEST</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">HISTORY</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="PurchaseManagement" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		 <div class="badan">
			<input type="button" name="ExportProduct" id="ExportProduct" class="btn btn-success mt-2 mr-2" value="Excel" onclick="PurchaseManagement.toExcel('./export/generate.php');" />
		</div>
		

</div>
<div id="PurchaseManagementDiv"></div>
<script>
		function PurchaseManagementDoubleClick() {

			PurchaseManagementMID = PurchaseManagement.getSelectedId();
			LoadM('PurchaseManagementDiv','cns-procurement-purchase-request-detail?ID=' + PurchaseManagementMID);
		}

		PurchaseManagement = new dhtmlXGridObject('PurchaseManagement');
		PurchaseManagement.setImagePath("dhtmlx/imgs/");
		PurchaseManagement.setHeader("No.,PR No,PR Date,Supplier,Payment Term,Product Specification,Status,Remark,Date Trx,Created By");
		PurchaseManagement.setInitWidths("70,170,100,100,100,100,100,100,120,100");
		PurchaseManagement.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		PurchaseManagement.setColAlign("center,center,center,center,center,center,center,left,center,center");
		PurchaseManagement.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		PurchaseManagement.setColSorting("int,str,str,str,str,str,str,str");
		PurchaseManagement.setEditable(false);
		PurchaseManagement.attachEvent("onRowDblClicked", PurchaseManagementDoubleClick);
		PurchaseManagement.setSkin("dhx_skyblue");
		PurchaseManagement.init();
		PurchaseManagement.loadXML("xml-cns-procurement-purchase-request");
		</script>
@endsection
