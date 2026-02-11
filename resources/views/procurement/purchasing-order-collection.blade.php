@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">PURCHASING ORDER</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">APPROVED PR COLLECTION</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
		 <div id="PurchaseManagement" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
	
	</div>
<script>
	function PurchaseManagementDoubleClick() {

		PurchaseManagementMID = PurchaseManagement.getSelectedId();
		LoadM('PurchaseManagementDiv','purchasing-order-collection-detail?ID=' + PurchaseManagementMID);
}	

		PurchaseManagement = new dhtmlXGridObject('PurchaseManagement');
		PurchaseManagement.setImagePath("dhtmlx/imgs/");
		PurchaseManagement.setHeader("No.,PR No,PR Date,Category,Sub Category,Purchase Type,Request By,Request For,Priority,Note Priority,Prority By,Date Trx,Created By");
		PurchaseManagement.setInitWidths("70,170,100,100,100,100,100,100,100,100,100,120,100");
		PurchaseManagement.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		PurchaseManagement.setColAlign("center,center,center,center,center,center,center,center,center,center,left,center,center");
		PurchaseManagement.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		PurchaseManagement.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
		PurchaseManagement.setEditable(true);
		PurchaseManagement.attachEvent("onRowDblClicked", PurchaseManagementDoubleClick);
		PurchaseManagement.setSkin("dhx_skyblue");
		PurchaseManagement.init();
		PurchaseManagement.loadXML("xml-po-collection");
		

		
		</script>
<div id="PurchaseManagementDiv"></div>

@endsection
