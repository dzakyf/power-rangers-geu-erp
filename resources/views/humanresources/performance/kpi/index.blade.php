@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <h1>Performance Management</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Human Resources</a></li>
        <li class="breadcrumb-item active"><span class="active">KPI & OKR Goals</span></li>
    </ol>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Daftar Sasaran Kerja Pegawai (KPI / OKR)</h5>
        <div>
            <a href="{{ url('hrd-performance-period-index') }}" class="btn btn-outline-secondary me-2">
                <i class="feather-calendar me-1"></i> Master Periode
            </a>
            <a href="{{ url('hrd-performance-kpi-new') }}" class="btn btn-primary">
                <i class="feather-plus me-1"></i> Buat Sasaran Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label font-weight-bold">Filter Periode</label>
                <select id="filterPeriod" class="form-select form-control" onchange="reloadGrid()">
                    <option value="">-- Semua Periode --</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" {{ $p->status === 'ACTIVE' ? 'selected' : '' }}>{{ $p->name }} ({{ $p->period_code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label font-weight-bold">Filter Status</label>
                <select id="filterStatus" class="form-select form-control" onchange="reloadGrid()">
                    <option value="">-- Semua Status --</option>
                    <option value="DRAFT">DRAFT</option>
                    <option value="WAITING_APPROVAL">WAITING_APPROVAL</option>
                    <option value="APPROVED">APPROVED</option>
                    <option value="EVALUATION">EVALUATION</option>
                    <option value="COMPLETED">COMPLETED</option>
                    <option value="REJECTED">REJECTED</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="button" class="btn btn-secondary me-2" onclick="reloadGrid()">
                    <i class="feather-refresh-cw me-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-info text-white" onclick="openSelectedDetail()">
                    <i class="feather-eye me-1"></i> Lihat Detail & Progres
                </button>
            </div>
        </div>

        <div id="gridKpiContainer" style="width: 100%; height: 500px; background-color: white;"></div>
    </div>
</div>

<script>
    var myGrid;
    document.addEventListener("DOMContentLoaded", function () {
        myGrid = new dhtmlXGridObject('gridKpiContainer');
        myGrid.setImagePath("{{ asset('dhtmlx/skins/skyblue/imgs/') }}/");
        myGrid.setHeader("No Goal,Periode,Karyawan,Departemen,Tipe,Bobot KPI,Progres,Status,Dibuat Pada");
        myGrid.setInitWidths("150,150,180,140,90,90,90,140,140");
        myGrid.setColAlign("left,left,left,left,center,right,right,center,center");
        myGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
        myGrid.setColSorting("str,str,str,str,str,int,int,str,date");
        myGrid.enableAutoWidth(true);
        myGrid.init();

        myGrid.attachEvent("onRowDblClicked", function(rId, cInd){
            window.location.href = "{{ url('hrd-performance-kpi-detail') }}/" + rId;
        });

        reloadGrid();
    });

    function reloadGrid() {
        var periodId = document.getElementById('filterPeriod').value;
        var status = document.getElementById('filterStatus').value;
        var url = "{{ url('hrd-performance-kpi-data') }}?period_id=" + periodId + "&status=" + status;
        myGrid.clearAndLoad(url);
    }

    function openSelectedDetail() {
        var selectedId = myGrid.getSelectedRowId();
        if (!selectedId) {
            Swal.fire('Perhatian', 'Silakan pilih salah satu baris sasaran kerja terlebih dahulu.', 'warning');
            return;
        }
        window.location.href = "{{ url('hrd-performance-kpi-detail') }}/" + selectedId;
    }
</script>
@endsection

