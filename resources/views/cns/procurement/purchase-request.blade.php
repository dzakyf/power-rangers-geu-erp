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
		<div class="mb-2">
			Periode : <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control-sm" />
			s/d <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control-sm" />
			<button onclick="refreshdata()" class="btn btn-primary p-2">Refresh</button>
		</div>
		 <div id="PurchaseManagement" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		 <div class="badan">
			 <input type="button" name="openPurchaseRequest" id="openPurchaseRequest" class="btn btn-primary mt-2 mr-2" value="Create New" onclick="PurchaseManagementNew()" />
			 <input type="button" name="detailPurchaseRequest" id="detailPurchaseRequest" class="btn btn-info mt-2 mr-2" value="Open Detail" onclick="PurchaseManagementDoubleClick()" />
			 <input type="button" name="ExportProduct" id="ExportProduct" class="btn btn-warning mt-2 mr-2" value="Excel" onclick="PurchaseManagement.toExcel('./export/generate.php');" />
		</div>
	</div>
	<div id="PurchaseManagementDiv"></div>
	<script>
		function PurchaseManagementDoubleClick() {

			PurchaseManagementMID = PurchaseManagement.getSelectedId();
			if (PurchaseManagementMID != null) {
				LoadM('PurchaseManagementDiv','cns-procurement-purchase-request-detail?ID=' + PurchaseManagementMID);
			}else {
				alert("Pilih baris data");
			}
			return true;
		}

		function PurchaseManagementNew() {

			LoadM('PurchaseManagementDiv','cns-procurement-purchase-request-news');
			return true;
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
		refreshdata();

		function refreshdata() {
			PurchaseManagement.clearAndLoad("xml-cns-procurement-purchase-request?tgl1=" + $('#tgl1').val() + "&tgl2=" + $('#tgl2').val());
		}
	</script>
@endsection
