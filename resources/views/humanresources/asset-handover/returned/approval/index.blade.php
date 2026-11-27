@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Asset Handover Returned Confirmation
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">General Affairs</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Handover Returned Confirmation</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-clipboard-check me-1"></i> Persetujuan Pengembalian
            </span>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            @include('humanresources.asset-handover.partials.quick-nav', ['activeTab' => 'return_approval'])
            <div id="AssetHandover" style="width:100%;height:500px;background-color:white;overflow:hidden"></div>
            <div id="divtom2" class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" id="DetailAssetHandover" name="DetailAssetHandover" class="btn btn-info btn-sm text-white">
                    <i class="fa fa-check-circle me-1"></i> Detail &amp; Approval Pengembalian
                </button>
                <button type="button" id="PrintBastReturnBtn" class="btn btn-danger btn-sm">
                    <i class="fa fa-file-pdf me-1"></i> Cetak BAST Return
                </button>
                <button type="button" id="BastReturnModalBtn" class="btn btn-outline-warning btn-sm text-dark">
                    <i class="fa fa-paperclip me-1"></i> BAST &amp; Lampiran Return
                </button>
                <button type="button" id="ekselAssetHandover" name="ekselAssetHandover" class="btn btn-warning btn-sm text-dark" onclick="AssetHandover.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshData();">
                    <i class="fa fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <div id="AssetHandoverDiv"></div>

    <script>
        AssetHandover = new dhtmlXGridObject('AssetHandover');
        AssetHandover.setImagePath("dhtmlx/imgs/");
        AssetHandover.setHeader("No.,Handover No,Handover Date,Stage / Status,BAST Return,User Name,User NIK,PIC Name,PIC Department,PIC Phone,Reference,Remark,Products");
        AssetHandover.setInitWidths("60,175,100,115,110,110,95,120,110,100,120,140,180");
        AssetHandover.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        AssetHandover.setColAlign("center,center,center,center,center,left,center,left,center,center,center,left,left");
        AssetHandover.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        AssetHandover.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
        AssetHandover.setEditable(false);
        AssetHandover.setSkin("dhx_skyblue");
        AssetHandover.attachEvent("onRowDblClicked", function(rId) {
            AssetHandover.selectRowById(rId);
            $('#DetailAssetHandover').trigger('click');
        });
        AssetHandover.init();
        refreshData();

        function refreshData() {
            AssetHandover.clearAndLoad("hrd-asset-handover-returned-approval-data");
        }

        $('#DetailAssetHandover').on('click', function() {
            let pmid = AssetHandover.getSelectedRowId();
            if (pmid == null) {
                alert('Mohon pilih baris');
                return;
            }
            let Handover_No = AssetHandover.cells(pmid, 1).getValue();
            LoadM('AssetHandoverDiv', 'hrd-asset-handover-returned-approval-detail?Handover_No=' + encodeURIComponent(Handover_No));
            var el = document.getElementById('AssetHandoverDiv');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        $('#PrintBastReturnBtn').on('click', function() {
            let pmid = AssetHandover.getSelectedRowId();
            if (pmid == null) {
                alert('Mohon pilih baris handover terlebih dahulu');
                return;
            }
            let Handover_No = AssetHandover.cells(pmid, 1).getValue();
            window.open('hrd-asset-handover-bast-print?Handover_No=' + encodeURIComponent(Handover_No) + '&doc_type=RETURN', '_blank');
        });

        $('#BastReturnModalBtn').on('click', function() {
            let pmid = AssetHandover.getSelectedRowId();
            if (pmid == null) {
                alert('Mohon pilih baris handover terlebih dahulu');
                return;
            }
            let Handover_No = AssetHandover.cells(pmid, 1).getValue();
            let modalUrl = 'hrd-asset-handover-bast-modal?Handover_No=' + encodeURIComponent(Handover_No) + '&doc_type=RETURN';
            if (window.AppModal && typeof window.AppModal.open === 'function') {
                window.AppModal.open({
                    title: 'Dokumen BAST Return — ' + Handover_No,
                    body: '<div class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><div class="small text-muted mt-2">Memuat dokumen BAST...</div></div>',
                    size: 'xl'
                });
                $.get(modalUrl, function(html) {
                    $('#appModalBody').html(html);
                }).fail(function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : ('Gagal memuat dokumen BAST (' + xhr.status + ').');
                    $('#appModalBody').html('<div class="alert alert-danger mb-0">' + msg + '</div>');
                });
            } else {
                LoadM('AssetHandoverDiv', modalUrl);
            }
        });
    </script>
@endsection
