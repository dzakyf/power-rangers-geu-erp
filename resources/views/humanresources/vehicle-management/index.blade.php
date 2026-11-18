@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Vehicle Management & Document Reminder</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Human Capital & General Affairs</a></li>
            <li class="breadcrumb-item"><a href="#">Asset & Vehicle Management</a></li>
            <li class="breadcrumb-item active"><span class="active">Vehicle Management</span></li>
        </ol>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mt-1 mb-3">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Total Kendaraan</div>
                    <div class="fs-3 fw-bold text-primary">{{ $counts['total_vehicles'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Dokumen Expired</div>
                    <div class="fs-3 fw-bold text-danger">{{ $counts['expired'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Kritis (&le; 14 Hari)</div>
                    <div class="fs-3 fw-bold text-danger">{{ $counts['critical_14'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Segera (&le; 30 Hari)</div>
                    <div class="fs-3 fw-bold text-warning">{{ $counts['warning_30'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Perhatian (&le; 60 Hari)</div>
                    <div class="fs-3 fw-bold text-info">{{ $counts['notice_60'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 bg-light">
                <div class="card-body py-3 d-flex flex-column justify-content-between">
                    <div class="text-muted small fw-semibold">Reminder In-App</div>
                    <button type="button" id="btnSyncReminder" class="btn btn-outline-primary btn-sm mt-1" onclick="syncVehicleReminders();">
                        <i class="fa fa-bell me-1"></i> Sinkronkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Banner Reminder STNK & KEUR -->
    @if(!empty($reminderItems))
    <div class="card border-warning shadow-sm mb-3">
        <div class="card-header bg-warning bg-opacity-25 d-flex justify-content-between align-items-center py-2">
            <div class="fw-bold text-dark">
                <i class="fa fa-triangle-exclamation text-danger me-2"></i>
                Pengingat Jatuh Tempo STNK (Tahunan / 5 Tahunan) &amp; KEUR/KIR ({{ count($reminderItems) }} item perlu perhatian)
            </div>
            <button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#reminderCollapse" aria-expanded="true">
                Tampilkan / Sembunyikan
            </button>
        </div>
        <div class="collapse show" id="reminderCollapse">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:240px;overflow-y:auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>No Polisi</th>
                                <th>Asset No</th>
                                <th>Kendaraan</th>
                                <th>Dokumen</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th>PIC</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reminderItems as $item)
                            <tr>
                                <td class="fw-bold">{{ $item['registration_no'] }}</td>
                                <td><code>{{ $item['asset_no'] }}</code></td>
                                <td>{{ $item['vehicle_name'] }}</td>
                                <td><span class="badge bg-dark text-white">{{ $item['doc_label'] }}</span></td>
                                <td>{{ $item['due_date'] }}</td>
                                <td><span class="badge {{ $item['badge_class'] }}">{{ $item['label'] }}</span></td>
                                <td>{{ $item['pic'] }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-xs btn-primary py-0 px-2" onclick="openVehicleDetail('{{ $item['asset_no'] }}');">
                                        <i class="fa fa-edit me-1"></i> Detail / Update
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Grid Kendaraan -->
    <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fa fa-car me-2 text-primary"></i>Daftar Kendaraan Operasional</h5>
                <small class="text-muted">Klik dua kali pada baris kendaraan untuk membuka detail aset serta pembaruan STNK &amp; KEUR/KIR.</small>
            </div>
            <div class="d-flex flex-wrap gap-1" id="docFilterGroup">
                <button type="button" class="btn btn-sm btn-primary active" onclick="setDocFilter('ALL', this);">Semua</button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="setDocFilter('ATTENTION', this);">Perlu Perhatian</button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="setDocFilter('EXPIRED', this);">Expired</button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="setDocFilter('CRITICAL', this);">Kritis &le;14 Hr</button>
                <button type="button" class="btn btn-sm btn-outline-warning text-dark" onclick="setDocFilter('WARNING', this);">Segera &le;30 Hr</button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="setDocFilter('NOTICE', this);">Perhatian &le;60 Hr</button>
            </div>
        </div>
        <div class="card-body">
            <div id="VEHICLEGRID" style="width:auto;height:380px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" class="btn btn-warning btn-sm" onclick="VEHICLEGRID.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" class="btn btn-info btn-sm text-white" onclick="VEHICLERefresh();">
                    <i class="fa fa-sync me-1"></i> Refresh Data
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="VEHICLEDetailSelected();">
                    <i class="fa fa-eye me-1"></i> Detail &amp; Update STNK / KEUR
                </button>
                <a href="hrd-vehicle-maintenance-index" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-wrench me-1"></i> Vehicle Maintenance
                </a>
                <a href="hrd-vehicle-operations" class="btn btn-outline-success btn-sm">
                    <i class="fa fa-truck me-1"></i> Logbook &amp; Fuel Tracking
                </a>
                <a href="hrd-asset-lists" class="btn btn-outline-dark btn-sm">
                    <i class="fa fa-boxes-stacked me-1"></i> Semua Asset Lists
                </a>
            </div>
        </div>
    </div>

    <div id="VEHICLEDTL" class="mt-3"></div>

<script>
var currentDocFilter = 'ALL';

function setDocFilter(status, btn) {
    currentDocFilter = status;
    var btns = document.querySelectorAll('#docFilterGroup button');
    btns.forEach(function (el) {
        el.classList.remove('active', 'btn-primary');
        if (!el.classList.contains('btn-outline-danger') && !el.classList.contains('btn-outline-warning') && !el.classList.contains('btn-outline-info')) {
            el.classList.add('btn-outline-primary');
        }
    });
    if (btn) {
        btn.classList.add('active');
    }
    VEHICLERefresh();
}

function openVehicleDetail(assetNo) {
    LoadM('VEHICLEDTL', 'hrd-asset-lists-detail?Task_ID=' + encodeURIComponent(assetNo));
    var el = document.getElementById('VEHICLEDTL');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function VEHICLEDetailSelected() {
    var selectedId = VEHICLEGRID.getSelectedId();
    if (!selectedId) {
        alert('Pilih baris kendaraan terlebih dahulu.');
        return;
    }
    var assetNo = VEHICLEGRID.cells(selectedId, 1).getValue();
    openVehicleDetail(assetNo);
}

function syncVehicleReminders() {
    var btn = document.getElementById('btnSyncReminder');
    btn.disabled = true;
    $.ajax({
        url: 'hrd-vehicle-management-sync-reminders',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
        success: function (res) {
            alert(res.message || 'Reminder berhasil disinkronkan.');
            btn.disabled = false;
        },
        error: function (xhr) {
            alert('Gagal sinkronisasi reminder.');
            btn.disabled = false;
        }
    });
}

VEHICLEGRID = new dhtmlXGridObject('VEHICLEGRID');
VEHICLEGRID.setImagePath("./dhtmlx/imgs/");
VEHICLEGRID.setHeader("No,Asset No,No Polisi,Kendaraan,Jenis,Lokasi,Departemen,PIC,KM Saat Ini,STNK Tahunan,Status STNK 1 Thn,STNK 5 Tahun,Status STNK 5 Thn,Jadwal KEUR/KIR,Status KEUR,Status Dokumen,Status Aset");
VEHICLEGRID.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter");
VEHICLEGRID.setInitWidths("50,130,105,210,120,105,110,120,90,105,130,105,130,110,130,115,95");
VEHICLEGRID.setColAlign("center,center,center,left,center,center,center,left,right,center,center,center,center,center,center,center,center");
VEHICLEGRID.setColSorting("int,str,str,str,str,str,str,str,int,date,str,date,str,date,str,str,str");
VEHICLEGRID.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro");
VEHICLEGRID.setNumberFormat("0,000", 8, ".", ",");
VEHICLEGRID.attachEvent("onRowDblClicked", VEHICLEDetailSelected);
VEHICLEGRID.enableSmartRendering(true, 150);
VEHICLEGRID.setSkin("dhx_skyblue");
VEHICLEGRID.init();

function VEHICLERefresh() {
    VEHICLEGRID.clearAndLoad('hrd-vehicle-management-data?doc_status=' + encodeURIComponent(currentDocFilter));
}

VEHICLERefresh();
</script>
@endsection
