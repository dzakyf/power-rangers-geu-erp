@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Purchase Request (History)',
    'breadcrumbs' => [
        ['label' => 'Procurement'],
        ['label' => 'Purchase Request'],
        ['label' => 'History'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title mb-0">History Purchase Request</h5>
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <button type="button" name="openPurchaseRequest" id="openPurchaseRequest" class="btn btn-sm btn-primary" onclick="PurchaseManagementNew()">Create New</button>
            <button type="button" name="detailPurchaseRequest" id="detailPurchaseRequest" class="btn btn-sm btn-info" onclick="PurchaseManagementDoubleClick()">Open Detail</button>
            <button type="button" name="printPurchaseRequest" id="printPurchaseRequest" class="btn btn-sm btn-outline-info" onclick="PurchaseManagementPrint()">Print PR</button>
            <button type="button" name="ExportProduct" id="ExportProduct" class="btn btn-sm btn-success" onclick="PurchaseManagement.toExcel('./export/generate.php');">Excel</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshdata()">Refresh</button>
        </div>
    </div>
    <div class="card-body">
        <div class="cns-filter-bar">
            <div>
                <label for="tgl1" class="d-block mb-1"><small>Dari Tanggal</small></label>
                <input type="date" id="tgl1" name="tgl1" value="{{ date('Y-m-01', strtotime('-7 days')) }}" class="form-control form-control-sm" style="width:auto;" />
            </div>
            <div>
                <label for="tgl2" class="d-block mb-1"><small>Sampai Tanggal</small></label>
                <input type="date" id="tgl2" name="tgl2" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" style="width:auto;" />
            </div>
            <div>
                <button type="button" onclick="refreshdata()" class="btn btn-sm btn-primary">Filter</button>
            </div>
        </div>
        <div class="cns-grid-wrapper">
            <div id="PurchaseManagement" class="cns-grid" style="width:auto;height:320px;background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>
<div id="PurchaseManagementDiv"></div>
	<script>
	function PurchaseManagementPrint() {
		PurchaseManagementMID = PurchaseManagement.getSelectedId();
		if (PurchaseManagementMID != null) {
			window.open('cns-procurement-purchase-request-print?PR_No=' + PurchaseManagement.cells(PurchaseManagementMID, 1).getValue(), '_blank');
		}else {
			swal('Perhatian', 'Pilih baris data', 'warning');
		}
		
	}
		function PurchaseManagementDoubleClick() {

			PurchaseManagementMID = PurchaseManagement.getSelectedId();
			if (PurchaseManagementMID != null) {
				LoadM('PurchaseManagementDiv','cns-procurement-purchase-request-detail?ID=' + PurchaseManagementMID);
			}else {
				swal('Perhatian', 'Pilih baris data', 'warning');
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
		PurchaseManagement.setColTypes("ro,ro,ro,ro,ro,ro,cns_badge,ro,ro,ro");
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
