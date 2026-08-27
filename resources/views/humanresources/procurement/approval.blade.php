@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>PROCUREMENT</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">PURCHASING REQUEST</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">APPROVAL</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="card">
		<div class="card-body">
			<div id="PurchaseManagement" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div class="badan">
			   <input type="button" name="ExportProduct" id="ExportProduct" class="btn btn-warning mt-2 mr-2" value="Excel" onclick="PurchaseManagement.toExcel('./export/generate.php');" />
			   <input type="button" name="DetailForm" id="DetailForm" class="btn btn-info mt-2 mr-2" value="Detail Form" onclick="PurchaseManagementDoubleClick()" />
			</div>
		</div>
		

</div>
<div id="PurchaseManagementDiv"></div>
<script>
		function PurchaseManagementDoubleClick() {

			PurchaseManagementMID = PurchaseManagement.getSelectedId();
			LoadM('PurchaseManagementDiv','hrd-procurement-approval-detail?PR_No=' + PurchaseManagement.cells(PurchaseManagementMID, 1).getValue());
		}

		PurchaseManagement = new dhtmlXGridObject('PurchaseManagement');
		PurchaseManagement.setImagePath("dhtmlx/imgs/");
		PurchaseManagement.setHeader("No.,PR No,PR Date,Category,Sub Category,Purchase Type,Status,Priority,Remark,Date Trx,Created By");
		PurchaseManagement.setInitWidths("70,170,100,100,100,100,100,100,100,120,100");
		PurchaseManagement.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		PurchaseManagement.setColAlign("center,center,center,center,center,center,center,center,left,center,center");
		PurchaseManagement.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		PurchaseManagement.setColSorting("int,str,str,str,str,str,str,str,str");
		PurchaseManagement.setEditable(false);
		PurchaseManagement.attachEvent("onRowDblClicked", PurchaseManagementDoubleClick);
		PurchaseManagement.setSkin("dhx_skyblue");
		PurchaseManagement.init();
		refreshData();
		function refreshData()
		{
			PurchaseManagement.clearAndLoad("hrd-procurement-approval-data");
		}
		</script>
@endsection
