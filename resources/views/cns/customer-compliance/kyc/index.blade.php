@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Customer Compliance - KYC',
    'breadcrumbs' => [
        ['label' => 'Customer Compliance'],
        ['label' => 'KYC'],
    ],
])

<div class="card cns-card">
    <div class="card-header">
        <h5 class="card-title">Daftar Form KYC</h5>
        <div class="d-flex flex-wrap" style="gap: 8px;">
            <a href="{{ route('cns-customer-compliance-kyc.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus-circle"></i> Tambah KYC
            </a>
            <button type="button" class="btn btn-info btn-sm" onclick="openSelectedDetail()">
                <i class="fa fa-eye"></i> Lihat Detail
            </button>
            <button type="button" class="btn btn-warning btn-sm" onclick="openSelectedEdit()">
                <i class="fa fa-edit"></i> Edit
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="printSelected()">
                <i class="fa fa-print"></i> Cetak PDF
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="deleteSelected()">
                <i class="fa fa-trash"></i> Hapus
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row cns-filter-bar">
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

        <div id="KycGridWrapper" class="cns-grid-wrapper">
            <div id="KycGrid" class="cns-grid" style="width:100%;height:400px;background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @csrf
</form>

<script>
    var KycGrid;
    var KycPagination;

    $(document).ready(function () {
        KycGrid = new dhtmlXGridObject('KycGrid');
        KycGrid.setImagePath("./dhtmlx/imgs/");
        KycGrid.setSkin("dhx_skyblue");
        KycGrid.setHeader("No,No. KYC,Tanggal,Nama Perusahaan,Pengisi Form,Badan Hukum,PIC Trading,Dokumen,Status Approval,Dibuat Oleh");
        KycGrid.attachHeader("#rspan,#text_filter,#rspan,#text_filter,#text_filter,#select_filter,#text_filter,#rspan,#select_filter,#select_filter");
        KycGrid.setInitWidths("45,180,95,220,150,110,130,100,110,100");
        KycGrid.setColAlign("center,center,center,left,left,left,left,center,center,center");
        KycGrid.setColSorting("int,str,date,str,str,str,str,str,str,str");
        KycGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,cns_badge,ro");
        KycGrid.attachEvent("onRowDblClicked", function (rowId) {
            window.location.href = "{{ url('cns-customer-compliance-kyc-detail') }}/" + rowId;
        });
        KycGrid.init();

        KycPagination = initCnsGridPagination(KycGrid, {
            pageSize: 25,
            pageSizeOptions: [10, 25, 50, 100],
            getUrl: function (params) {
                var q = [];
                q.push('posStart=' + params.posStart);
                q.push('count=' + params.count);

                var search = $('#filterSearch').val();
                var start  = $('#filterStartDate').val();
                var end    = $('#filterEndDate').val();
                if (search) q.push('search=' + encodeURIComponent(search));
                if (start)  q.push('start_date=' + encodeURIComponent(start));
                if (end)    q.push('end_date=' + encodeURIComponent(end));

                if (params.sortCol !== null && params.sortCol !== undefined) {
                    q.push('sort_col=' + params.sortCol);
                    q.push('sort_dir=' + (params.sortDir || 'asc'));
                }

                if (params.filters) {
                    for (var colIdx in params.filters) {
                        q.push('filters[' + colIdx + ']=' + encodeURIComponent(params.filters[colIdx]));
                    }
                }

                return "{{ route('cns-customer-compliance-kyc.xml') }}?" + q.join('&');
            }
        });

        loadGridData();

        $(window).on('resize', function () {
            if (KycGrid) KycGrid.setSizes();
        });
    });

    function loadGridData() {
        if (KycPagination) {
            KycPagination.reload(true);
        }
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
            swal('Perhatian', 'Pilih data terlebih dahulu.', 'warning');
            return;
        }
        window.location.href = "{{ url('cns-customer-compliance-kyc-detail') }}/" + rowId;
    }

    function openSelectedEdit() {
        var rowId = KycGrid.getSelectedRowId();
        if (!rowId) {
            swal('Perhatian', 'Pilih data terlebih dahulu.', 'warning');
            return;
        }
        window.location.href = "{{ url('cns-customer-compliance-kyc-edit') }}/" + rowId;
    }

    function printSelected() {
        var rowId = KycGrid.getSelectedRowId();
        if (!rowId) {
            swal('Perhatian', 'Pilih baris data terlebih dahulu.', 'warning');
            return;
        }
        window.open("{{ url('cns-customer-compliance-kyc-print') }}/" + rowId, '_blank');
    }

    function deleteSelected() {
        var rowId = KycGrid.getSelectedRowId();
        if (!rowId) {
            swal('Perhatian', 'Pilih data terlebih dahulu.', 'warning');
            return;
        }
        swal({
            title: 'Konfirmasi',
            text: 'Yakin ingin menghapus data KYC ini?',
            icon: 'warning',
            buttons: ['Batal', 'Ya'],
            dangerMode: true
        }).then(function (confirmed) {
            if (!confirmed) return;
            var form = document.getElementById('deleteForm');
            form.action = "{{ url('cns-customer-compliance-kyc-delete') }}/" + rowId;
            form.submit();
        });
    }
</script>
@endsection
