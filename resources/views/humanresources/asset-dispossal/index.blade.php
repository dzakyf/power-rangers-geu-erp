@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Asset Disposal Management
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">General Affairs</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Asset Disposal List</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-trash-alt me-1"></i> Pemusnahan Aset
            </span>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <div id="AssetDisposal" style="width:100%;height:500px;background-color:white;overflow:hidden"></div>
            <div id="divtom2" class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" id="FormAssetDisposal" name="FormAssetDisposal" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus me-1"></i> Form Pemusnahan Aset
                </button>
                <button type="button" id="DetailAssetDisposal" name="DetailAssetDisposal" class="btn btn-info btn-sm text-white">
                    <i class="fa fa-eye me-1"></i> Detail Pemusnahan
                </button>
                <button type="button" id="LogAssetDisposal" name="LogAssetDisposal" class="btn btn-success btn-sm">
                    <i class="fa fa-history me-1"></i> Log Pemusnahan
                </button>
                <button type="button" id="ekselAssetDisposal" name="ekselAssetDisposal" class="btn btn-warning btn-sm text-dark" onclick="AssetDisposal.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" id="RefreshAssetDisposal" class="btn btn-outline-secondary btn-sm" onclick="refreshData();">
                    <i class="fa fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <div id="AssetDisposalDiv"></div>

    <script>
        AssetDisposal = new dhtmlXGridObject('AssetDisposal');
        AssetDisposal.setImagePath("dhtmlx/imgs/");
        AssetDisposal.setHeader("No.,Disposal No, Tanggal Pengajuan, Pemohon, Jabatan, Nama Aset, Nomor Seri, Kondisi, Lokasi, Alasan, Tanggal Pemusnahan, Status");
        AssetDisposal.setInitWidths("70,170,100,100,100,150,100,100,100,200,120,80");
        AssetDisposal.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        AssetDisposal.setColAlign("center,center,center,center,center,left,center,center,left,left,center,center");
        AssetDisposal.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        AssetDisposal.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
        AssetDisposal.setEditable(true);
        AssetDisposal.setSkin("dhx_skyblue");
        AssetDisposal.init();
        refreshData();

        function refreshData() {
            AssetDisposal.clearAndLoad("hrd-asset-dispossal-data");
        }

        function scrollToDisposalPanel() {
            var el = document.getElementById('AssetDisposalDiv');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        $('#FormAssetDisposal').on('click', function() {
            LoadM('AssetDisposalDiv', 'hrd-asset-dispossal-new');
            scrollToDisposalPanel();
        });

        $('#LogAssetDisposal').on('click', function() {
            let pmid = AssetDisposal.getSelectedRowId();
            if (pmid == null) {
                alert('Mohon pilih baris');
                return;
            }
            let Disposal_No = AssetDisposal.cells(pmid, 1).getValue();
            LoadM('AssetDisposalDiv', 'hrd-asset-dispossal-log?Disposal_No=' + encodeURIComponent(Disposal_No));
            scrollToDisposalPanel();
        });

        $('#DetailAssetDisposal').on('click', function() {
            let pmid = AssetDisposal.getSelectedRowId();
            if (pmid == null) {
                alert('Mohon pilih baris');
                return;
            }
            let Disposal_No = AssetDisposal.cells(pmid, 1).getValue();
            LoadM('AssetDisposalDiv', 'hrd-asset-dispossal-detail?Disposal_No=' + encodeURIComponent(Disposal_No));
            scrollToDisposalPanel();
        });
    </script>
@endsection
