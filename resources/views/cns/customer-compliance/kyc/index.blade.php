@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Customer Compliance</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Contract</a></li>
        <li class="breadcrumb-item">Customer Compliance</li>
        <li class="breadcrumb-item active"><span class="active">KYC</span></li>
    </ol>
</div>

<style>
    #KycGridWrapper {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }
    #KycGrid {
        box-sizing: border-box !important;
        max-width: 100% !important;
    }
    #KycGrid .xhdr {
        height: auto !important;
        min-height: 0 !important;
    }
    #KycGrid table.hdr td {
        color: #000000 !important;
        font-weight: bold !important;
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 12px !important;
        padding: 5px 6px !important;
        line-height: 1.2 !important;
        height: auto !important;
    }
    #KycGrid table.hdr td.filter {
        padding: 3px 4px !important;
        font-weight: normal !important;
    }
    #KycGrid table.hdr td div.hdrcell {
        padding: 0 4px !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
    }
    #KycGrid table.hdr td.filter input,
    #KycGrid table.hdr td.filter select {
        width: 100% !important;
        height: 22px !important;
        padding: 1px 4px !important;
        font-size: 11px !important;
        line-height: 1.2 !important;
        box-sizing: border-box !important;
    }
    #KycGrid td {
        font-size: 12px !important;
        vertical-align: middle !important;
    }
</style>

<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filterSearch" class="mb-1">Pencarian</label>
                <input type="text" id="filterSearch" class="form-control" placeholder="Cari No. KYC, Perusahaan, Reg. No, Pengisi...">
            </div>
            <div class="col-md-2">
                <label for="filterStartDate" class="mb-1">Dari Tanggal</label>
                <input type="date" id="filterStartDate" class="form-control">
            </div>
            <div class="col-md-2">
                <label for="filterEndDate" class="mb-1">Sampai Tanggal</label>
                <input type="date" id="filterEndDate" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-primary mr-2" onclick="loadGridData()"><i class="fa fa-search"></i> Cari</button>
                <button type="button" class="btn btn-light" onclick="resetFilters()"><i class="fa fa-refresh"></i> Reset</button>
            </div>
        </div>

        <div id="KycGridWrapper">
            <div id="KycGrid" style="width:100%;height:400px;background-color:white;overflow:hidden"></div>
        </div>

        <div class="mt-3 d-flex flex-wrap" style="gap: 8px;">
            <a href="{{ route('cns-customer-compliance-kyc.create') }}" class="btn btn-primary">
                <i class="fa fa-plus-circle"></i> Tambah KYC
            </a>
            <button type="button" class="btn btn-info" onclick="openSelectedDetail()">
                <i class="fa fa-eye"></i> Lihat Detail
            </button>
            <button type="button" class="btn btn-warning" onclick="openSelectedEdit()">
                <i class="fa fa-edit"></i> Edit
            </button>
            <button type="button" class="btn btn-secondary" onclick="printSelected()">
                <i class="fa fa-print"></i> Cetak PDF
            </button>
            <button type="button" class="btn btn-danger" onclick="deleteSelected()">
                <i class="fa fa-trash"></i> Hapus
            </button>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @csrf
</form>

<script>
    var KycGrid;

    $(document).ready(function () {
        KycGrid = new dhtmlXGridObject('KycGrid');
        KycGrid.setImagePath("./dhtmlx/imgs/");
        KycGrid.setSkin("dhx_skyblue");
        KycGrid.setHeader("No,No. KYC,Tanggal,Nama Perusahaan,Pengisi Form,Badan Hukum,PIC Trading,Dokumen,Dibuat Oleh");
        KycGrid.attachHeader("#rspan,#text_filter,#rspan,#text_filter,#text_filter,#select_filter,#text_filter,#rspan,#select_filter");
        KycGrid.setInitWidths("45,180,95,240,160,110,140,110,100");
        KycGrid.setColAlign("center,center,center,left,left,left,left,center,center");
        KycGrid.setColSorting("int,str,date,str,str,str,str,str,str");
        KycGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
        KycGrid.enableSmartRendering(true);
        KycGrid.attachEvent("onRowDblClicked", function (rowId) {
            window.location.href = "{{ url('cns-customer-compliance-kyc-detail') }}/" + rowId;
        });
        KycGrid.init();
        loadGridData();

        $(window).on('resize', function () {
            if (KycGrid) KycGrid.setSizes();
        });
    });

    function loadGridData() {
        var params = [];
        var search = $('#filterSearch').val();
        var start  = $('#filterStartDate').val();
        var end    = $('#filterEndDate').val();

        if (search) params.push('search=' + encodeURIComponent(search));
        if (start)  params.push('start_date=' + encodeURIComponent(start));
        if (end)    params.push('end_date=' + encodeURIComponent(end));

        var url = "{{ route('cns-customer-compliance-kyc.xml') }}" + (params.length ? '?' + params.join('&') : '');
        KycGrid.clearAll();
        KycGrid.load(url, function () {
            if (KycGrid) KycGrid.setSizes();
        });
    }

    function resetFilters() {
        $('#filterSearch').val('');
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        loadGridData();
    }

    function openSelectedDetail() {
        var rowId = KycGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih data terlebih dahulu.');
            return;
        }
        window.location.href = "{{ url('cns-customer-compliance-kyc-detail') }}/" + rowId;
    }

    function openSelectedEdit() {
        var rowId = KycGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih data terlebih dahulu.');
            return;
        }
        window.location.href = "{{ url('cns-customer-compliance-kyc-edit') }}/" + rowId;
    }

    function printSelected() {
        var rowId = KycGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih baris data terlebih dahulu.');
            return;
        }
        window.open("{{ url('cns-customer-compliance-kyc-print') }}/" + rowId, '_blank');
    }

    function deleteSelected() {
        var rowId = KycGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih data terlebih dahulu.');
            return;
        }
        if (confirm('Yakin ingin menghapus data KYC ini?')) {
            var form = document.getElementById('deleteForm');
            form.action = "{{ url('cns-customer-compliance-kyc-delete') }}/" + rowId;
            form.submit();
        }
    }
</script>
@endsection
