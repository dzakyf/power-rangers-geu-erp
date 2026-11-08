@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1 class="h3 font-weight-bold text-dark mb-1">Approval Pengadaan (Procurement)</h1>
                <ol class="breadcrumb bg-transparent p-0 mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="/dashboard1" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/hrd-procurement-index" class="text-decoration-none">Procurement</a></li>
                    <li class="breadcrumb-item active text-primary font-weight-semibold">Approval List</li>
                </ol>
            </div>
            <div class="d-flex align-items-center mt-2 mt-md-0">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-xs">
                    <i class="fa fa-check-square-o me-1"></i> Verifikasi & Persetujuan
                </span>
            </div>
        </div>
    </div> 
    <!-- End Main Content Header -->

    <!-- Main Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fa fa-tasks text-primary fa-lg me-2"></i>
                    <h5 class="card-title mb-0 font-weight-bold text-dark">Daftar Purchase Request Menunggu Tindakan</h5>
                </div>
                <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshData()">
                        <i class="fa fa-refresh me-1"></i> Muat Ulang
                    </button>
                    <button type="button" name="ExportProduct" id="ExportProduct" class="btn btn-sm btn-outline-success" onclick="PurchaseManagement.toExcel('./export/generate.php');">
                        <i class="fa fa-file-excel-o me-1"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-3">
            <!-- Toolbar & Notice -->
            <div class="bg-light p-2 rounded-3 border mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center">
                    <button type="button" name="DetailForm" id="DetailForm" class="btn btn-primary btn-sm text-white shadow-xs me-2" onclick="PurchaseManagementDoubleClick()">
                        <i class="fa fa-pencil-square-o me-1"></i> Proses / Detail Approval
                    </button>
                </div>
                <small class="text-muted">
                    <i class="fa fa-mouse-pointer me-1 text-primary"></i>Tip: Klik dua kali baris tabel untuk langsung membuka form approval.
                </small>
            </div>

            <!-- Grid Container -->
            <div class="border rounded-3 overflow-hidden bg-white shadow-xs">
                <div id="PurchaseManagement" style="width:100%; height:480px; background-color:white; overflow:hidden"></div>
            </div>
        </div>
    </div>

    <!-- Container for dynamic AJAX loaded views -->
    <div id="PurchaseManagementDiv" class="mt-3"></div>

    <script>
        function PurchaseManagementDoubleClick() {
            var PurchaseManagementMID = PurchaseManagement.getSelectedId();
            if(!PurchaseManagementMID) {
                alert('Silakan pilih salah satu baris terlebih dahulu.');
                return;
            }
            var prNo = PurchaseManagement.cells(PurchaseManagementMID, 1).getValue();
            $.blockUI({ message: '<h4><i class="fa fa-spinner fa-spin"></i> Membuka Detail Approval...</h4>' });
            LoadM('PurchaseManagementDiv','hrd-procurement-approval-detail?PR_No=' + encodeURIComponent(prNo));
        }

        var PurchaseManagement = new dhtmlXGridObject('PurchaseManagement');
        PurchaseManagement.setImagePath("dhtmlx/imgs/");
        PurchaseManagement.setHeader("No.,PR No,PR Date,Category,Sub Category,Purchase Type,Status,Priority,Remark,Date Trx,Created By");
        PurchaseManagement.setInitWidths("60,180,105,100,105,110,110,90,150,110,120");
        PurchaseManagement.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        PurchaseManagement.setColAlign("center,center,center,center,center,center,center,center,left,center,center");
        PurchaseManagement.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        PurchaseManagement.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
        PurchaseManagement.setEditable(false);
        PurchaseManagement.attachEvent("onRowDblClicked", PurchaseManagementDoubleClick);
        PurchaseManagement.setSkin("dhx_skyblue");
        PurchaseManagement.enableRowsHover(true, 'grid_hover');
        PurchaseManagement.init();
        refreshData();

        function refreshData() {
            PurchaseManagement.clearAndLoad("hrd-procurement-approval-data");
        }
    </script>
@endsection
