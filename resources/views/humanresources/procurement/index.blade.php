@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1 class="h3 font-weight-bold text-dark mb-1">Pengadaan Barang (Procurement)</h1>
                <ol class="breadcrumb bg-transparent p-0 mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="/dashboard1" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/hrd-procurement-index" class="text-decoration-none">Procurement</a></li>
                    <li class="breadcrumb-item active text-primary font-weight-semibold">Purchase Request List</li>
                </ol>
            </div>
            <div class="d-flex align-items-center mt-2 mt-md-0">
                <span class="badge bg-primary text-white px-3 py-2 rounded-pill shadow-xs">
                    <i class="fa fa-shopping-cart me-1"></i> Modul Pengadaan
                </span>
            </div>
        </div>
    </div> 
    <!-- End Main Content Header -->

    <!-- Main Card Container -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fa fa-list-alt text-primary fa-lg me-2"></i>
                    <h5 class="card-title mb-0 font-weight-bold text-dark">Daftar Purchase Request</h5>
                </div>
                <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshData()">
                        <i class="fa fa-refresh me-1"></i> Muat Ulang
                    </button>
                    <button type="button" id="ekselRequestProcurement" name="ekselRequestProcurement" class="btn btn-sm btn-outline-success" onclick="PurchaseManagement.toExcel('./export/generate.php');">
                        <i class="fa fa-file-excel-o me-1"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-3">
            <!-- Action Toolbar Banner -->
            <div class="bg-light p-2 rounded-3 border mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex flex-wrap align-items-center gap-1" id="divtom2">
                        <button type="button" id="FormRequestProcurement" name="FormRequestProcurement" class="btn btn-primary btn-sm me-1 shadow-xs">
                            <i class="fa fa-plus-circle me-1"></i> Buat PR Baru
                        </button>
                        <div class="vr mx-1 d-none d-md-block text-muted"></div>
                        <button type="button" id="DetailRequestProcurement" name="DetailRequestProcurement" class="btn btn-info btn-sm text-white me-1">
                            <i class="fa fa-eye me-1"></i> Detail / Edit
                        </button>
                        <button type="button" id="LogRequestProcurement" name="LogRequestProcurement" class="btn btn-secondary btn-sm me-1">
                            <i class="fa fa-history me-1"></i> History Log
                        </button>
                        <div class="vr mx-1 d-none d-md-block text-muted"></div>
                        <button type="button" id="PrintPRProcurement" name="PrintPRProcurement" class="btn btn-dark btn-sm me-1">
                            <i class="fa fa-print me-1"></i> Cetak PR
                        </button>
                        <button type="button" id="PrintPOProcurement" name="PrintPOProcurement" class="btn btn-outline-dark btn-sm me-1">
                            <i class="fa fa-file-text-o me-1"></i> Cetak PO
                        </button>
                        <button type="button" id="CheckInventoryProcurement" name="CheckInventoryProcurement" class="btn btn-warning btn-sm text-dark me-1">
                            <i class="fa fa-truck me-1"></i> Cek Penerimaan (GR)
                        </button>
                    </div>
                    <small class="text-muted d-none d-lg-block">
                        <i class="fa fa-info-circle me-1 text-primary"></i>Pilih salah satu baris untuk membuka Detail, Cetak, atau Log.
                    </small>
                </div>
            </div>

            <!-- DhtmlX Grid Wrapper -->
            <div class="border rounded-3 overflow-hidden bg-white shadow-xs">
                <div id="PurchaseManagement" style="width:100%; height:540px; background-color:white; overflow:hidden"></div>
            </div>
        </div>
    </div>

    <!-- Container for dynamic AJAX loaded views -->
    <div id="PurchaseManagementDiv" class="mt-3"></div>

    <script>
        var PurchaseManagement = new dhtmlXGridObject('PurchaseManagement');
        PurchaseManagement.setImagePath("dhtmlx/imgs/");
        PurchaseManagement.setHeader("No.,PR No,PR Date,Category,Status,PO No,Remark,Date Trx,Created By");
        PurchaseManagement.setInitWidths("60,180,105,110,110,120,160,110,120");
        PurchaseManagement.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        PurchaseManagement.setColAlign("center,center,center,center,center,center,left,center,center");
        PurchaseManagement.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
        PurchaseManagement.setColSorting("int,str,str,str,str,str,str,str,str");
        PurchaseManagement.setEditable(false);
        PurchaseManagement.setSkin("dhx_skyblue");
        PurchaseManagement.init();
        PurchaseManagement.enableRowsHover(true, 'grid_hover');
        refreshData();

        function refreshData() {
            PurchaseManagement.clearAndLoad("xml-hrd-procurement-index");
        }

        $('#LogRequestProcurement').click(function () {
            let pmid = PurchaseManagement.getSelectedRowId();
            if(pmid == null) {
                alert('Silakan pilih salah satu baris PR terlebih dahulu.');
                return;
            }
            let PR_No = PurchaseManagement.cells(pmid,1).getValue();
            $.blockUI({ message: '<h4><i class="fa fa-spinner fa-spin"></i> Memuat Log Request...</h4>' });
            LoadM('PurchaseManagementDiv','hrd-procurement-log?PR_No=' + encodeURIComponent(PR_No));
        });

        $('#FormRequestProcurement').on('click', function() {
            $.blockUI({ message: '<h4><i class="fa fa-spinner fa-spin"></i> Memuat Form Request...</h4>' });
            LoadM('PurchaseManagementDiv','hrd-procurement-new');
        });

        $('#DetailRequestProcurement').on('click', function() {
            let pmid = PurchaseManagement.getSelectedRowId();
            if(pmid == null) {
                alert('Silakan pilih salah satu baris PR terlebih dahulu.');
                return;
            }
            let PR_No = PurchaseManagement.cells(pmid,1).getValue();
            $.blockUI({ message: '<h4><i class="fa fa-spinner fa-spin"></i> Memuat Detail PR...</h4>' });
            LoadM('PurchaseManagementDiv','hrd-procurement-detail?PR_No=' + encodeURIComponent(PR_No));
        });

        // Double click shortcut to open detail
        PurchaseManagement.attachEvent("onRowDblClicked", function(rowId, colInd) {
            let PR_No = PurchaseManagement.cells(rowId, 1).getValue();
            $.blockUI({ message: '<h4><i class="fa fa-spinner fa-spin"></i> Memuat Detail PR...</h4>' });
            LoadM('PurchaseManagementDiv','hrd-procurement-detail?PR_No=' + encodeURIComponent(PR_No));
        });

        function selectedPRPO() {
            let pmid = PurchaseManagement.getSelectedRowId();
            if(pmid == null) { 
                alert('Silakan pilih salah satu baris PR terlebih dahulu.'); 
                return null; 
            }
            return {
                pr: PurchaseManagement.cells(pmid,1).getValue(),
                po: PurchaseManagement.cells(pmid,5).getValue()
            };
        }

        function streamPdf(url, title) {
            let w = window.open('', '_blank');
            if(!w) { alert('Popup diblokir, izinkan popup browser untuk preview PDF'); return; }
            w.document.write('<html><head><title>'+title+'</title></head><body style="margin:0"><iframe src="'+url+'" style="width:100vw;height:100vh;border:0"></iframe></body></html>');
        }

        $('#PrintPRProcurement').on('click', function() {
            let s = selectedPRPO(); if(!s) return;
            streamPdf('hrd-procurement-print-pr?PR_No=' + encodeURIComponent(s.pr), 'Cetak PR ' + s.pr);
        });

        $('#PrintPOProcurement').on('click', function() {
            let s = selectedPRPO(); if(!s) return;
            if(!s.po) { alert('PO belum diterbitkan untuk PR ini'); return; }
            streamPdf('hrd-procurement-print-po?PR_No=' + encodeURIComponent(s.pr) + '&PO_No=' + encodeURIComponent(s.po), 'Cetak PO ' + s.po);
        });

        $('#CheckInventoryProcurement').on('click', function() {
            let s = selectedPRPO(); if(!s) return;
            if(!s.po) {
                alert('PO belum diterbitkan untuk PR ini. Penerimaan barang memerlukan nomor PO yang sudah disetujui.');
                return;
            }
            $.blockUI({ message: '<h4><i class="fa fa-spinner fa-spin"></i> Membuka Penerimaan Barang...</h4>' });
            LoadM('PurchaseManagementDiv', 'inventory-incoming-new?PO_NO=' + encodeURIComponent(s.po));
        });
    </script>
@endsection
