@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Vehicle Maintenance Management
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Data Assets</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Vehicle Maintenance</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge bg-light text-dark px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2) !important; border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-car me-1"></i> Perawatan &amp; Penjadwalan PM Kendaraan
            </span>
        </div>
    </div>

    {{-- KPI Summary Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Total Pengajuan</small>
                        <h4 class="fw-bold mb-0 mt-1">{{ $kpi['total'] ?? 0 }}</h4>
                    </div>
                    <span class="badge bg-primary text-white p-3 rounded-3"><i class="fa fa-clipboard-list fs-5"></i></span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Menunggu Approval</small>
                        <h4 class="fw-bold text-warning mb-0 mt-1">{{ $kpi['waiting'] ?? 0 }}</h4>
                    </div>
                    <span class="badge bg-warning text-dark p-3 rounded-3"><i class="fa fa-hourglass-half fs-5"></i></span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Sedang Servis</small>
                        <h4 class="fw-bold text-info mb-0 mt-1">{{ $kpi['in_progress'] ?? 0 }}</h4>
                    </div>
                    <span class="badge bg-info text-white p-3 rounded-3"><i class="fa fa-wrench fs-5"></i></span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100" style="cursor:pointer;" onclick="switchToDuePmTab();">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Jatuh Tempo PM</small>
                        <h4 class="fw-bold text-danger mb-0 mt-1">{{ $kpi['due_pm'] ?? 0 }}</h4>
                    </div>
                    <span class="badge bg-danger text-white p-3 rounded-3"><i class="fa fa-bell fs-5"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-2">
        <div class="card-header bg-white border-bottom pb-0 pt-2">
            <ul class="nav nav-tabs card-header-tabs" id="vmTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold" id="tab-list-btn" data-bs-toggle="tab" data-bs-target="#tab-list" type="button" role="tab">
                        <i class="fa fa-list me-1"></i> Daftar Perawatan Kendaraan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="tab-due-pm-btn" data-bs-toggle="tab" data-bs-target="#tab-due-pm" type="button" role="tab">
                        <i class="fa fa-calendar-check me-1"></i> Jadwal PM Jatuh Tempo
                        @if(($kpi['due_pm'] ?? 0) > 0)
                            <span class="badge bg-danger text-white ms-1">{{ $kpi['due_pm'] }}</span>
                        @endif
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="vmTabsContent">
                {{-- Tab 1: Daftar Perawatan Kendaraan --}}
                <div class="tab-pane fade show active" id="tab-list" role="tabpanel">
                    <div id="VehicleMaintenance" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                        <button type="button" id="VM_New" class="btn btn-primary btn-sm" onclick="VehicleMaintenanceNew();">
                            <i class="fa fa-plus me-1"></i> Form Perawatan Kendaraan
                        </button>
                        <button type="button" id="VM_Detail" class="btn btn-info btn-sm text-white" onclick="VehicleMaintenanceDetail();">
                            <i class="fa fa-eye me-1"></i> Detail Perawatan
                        </button>
                        <a href="hrd-vehicle-maintenance-export" id="ekselVM" class="btn btn-warning btn-sm text-dark">
                            <i class="fa fa-file-excel me-1"></i> Export Excel
                        </a>
                        <button type="button" id="VM_Refresh" class="btn btn-outline-secondary btn-sm" onclick="VehicleMaintenanceRefresh();">
                            <i class="fa fa-sync-alt me-1"></i> Refresh Data
                        </button>
                        <a href="hrd-vehicle-operations" class="btn btn-success btn-sm fw-semibold">
                            <i class="fa fa-truck me-1"></i> Logbook &amp; Fuel Tracking
                        </a>
                        <a href="hrd-vehicle-maintenance-approval-index" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-clipboard-check me-1"></i> Approval Maintenance
                        </a>
                    </div>
                </div>

                {{-- Tab 2: Jadwal PM Jatuh Tempo (Auto Scheduling Panel) --}}
                <div class="tab-pane fade" id="tab-due-pm" role="tabpanel">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h6 class="fw-bold mb-0 text-primary"><i class="fa fa-clock me-1"></i> Monitoring Preventive Maintenance (Tanggal &amp; Odometer)</h6>
                            <small class="text-muted">Odometer otomatis disinkronkan dari Logbook &amp; Fuel Tracking. Reminder harian berjalan pukul 07:40 WIB (H-30, H-14, H-7, H-0 &amp; sisa &le; 1.000 KM).</small>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active" onclick="filterDuePmTable('ALL', this)">Semua</button>
                            <button type="button" class="btn btn-outline-danger" onclick="filterDuePmTable('DUE', this)">Perlu Tindakan</button>
                            <button type="button" class="btn btn-outline-dark" onclick="filterDuePmTable('UNSET', this)">Belum Diatur</button>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 430px; overflow-y: auto;">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0" id="DuePmTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Asset No</th>
                                    <th>Plat / Kendaraan</th>
                                    <th class="text-end">KM Efektif</th>
                                    <th>Jadwal Tanggal PM</th>
                                    <th>Target KM PM</th>
                                    <th>Status PM</th>
                                    <th>Pengajuan Aktif</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($duePmItems as $item)
                                    @php
                                        $v = $item['vehicle'];
                                        $dEval = $item['date_eval'];
                                        $kEval = $item['km_eval'];
                                        $overall = $item['overall'];
                                        $activeVm = $item['active_vm'];
                                        $rowNeeds = !empty($item['needs_action']) ? '1' : '0';
                                        $rowStatus = $overall['status'] ?? 'UNSET';
                                    @endphp
                                    <tr class="due-pm-row" data-needs="{{ $rowNeeds }}" data-status="{{ $rowStatus }}">
                                        <td class="fw-semibold text-nowrap">{{ $v->Asset_No }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $v->Registration_No ?: '-' }}</div>
                                            <small class="text-muted">{{ $v->Product_Description ?: '-' }}</small>
                                        </td>
                                        <td class="text-end text-nowrap">{{ number_format($item['effective_km'], 0, ',', '.') }} km</td>
                                        <td class="text-nowrap">
                                            <div>{{ $v->Tanggal_Jadwal_PM && $v->Tanggal_Jadwal_PM !== '0000-00-00' ? date('d/m/Y', strtotime($v->Tanggal_Jadwal_PM)) : '-' }}</div>
                                            <span class="badge {{ $dEval['badge_class'] }}">{{ $dEval['label'] }}</span>
                                        </td>
                                        <td class="text-nowrap">
                                            <div>{{ !empty($v->KM_Jadwal_PM) ? number_format($v->KM_Jadwal_PM, 0, ',', '.') . ' km' : '-' }}</div>
                                            <span class="badge {{ $kEval['badge_class'] }}">{{ $kEval['label'] }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $overall['badge_class'] }}">{{ $overall['status'] }}</span>
                                        </td>
                                        <td class="text-nowrap">
                                            @if($activeVm)
                                                <span class="badge bg-info text-white">{{ $activeVm->No_Trx }}</span>
                                                <div class="small text-muted">{{ $activeVm->Status }}</div>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center text-nowrap">
                                            @if(!$activeVm)
                                                <button type="button" class="btn btn-primary btn-sm me-1"
                                                    onclick="VehicleMaintenanceNewPrefill('{{ $v->Asset_No }}');">
                                                    <i class="fa fa-plus-circle me-1"></i> Buat Pengajuan
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-info btn-sm me-1"
                                                    onclick="LoadM('VMDTL', 'hrd-vehicle-maintenance-detail?ID={{ $activeVm->id }}'); scrollToVmPanel('VMDTL');">
                                                    <i class="fa fa-eye me-1"></i> Lihat
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                                onclick="openPmScheduleModal(
                                                    '{{ $v->Asset_No }}',
                                                    '{{ htmlspecialchars((string) ($v->Registration_No ?: $v->Product_Description), ENT_QUOTES) }}',
                                                    '{{ (int) ($v->Interval_PM ?? 0) }}',
                                                    '{{ (int) ($v->Interval_KM_PM ?? 0) }}',
                                                    '{{ $v->Tanggal_Jadwal_PM ?? '' }}',
                                                    '{{ (int) ($v->KM_Jadwal_PM ?? 0) }}',
                                                    '{{ htmlspecialchars((string) ($v->Repair_Vendor ?? ''), ENT_QUOTES) }}'
                                                )">
                                                <i class="fa fa-cog me-1"></i> Atur Jadwal
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Belum ada data kendaraan pada Master Asset.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="VMDTL"></div>
    <div id="VMDTL1"></div>

    {{-- Modal Atur Jadwal PM --}}
    <div class="modal fade" id="pmScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title text-white"><i class="fa fa-calendar-alt me-2"></i>Atur Jadwal PM Kendaraan</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="PM_Asset_No" />
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Kendaraan</label>
                        <input type="text" id="PM_Vehicle_Label" class="form-control form-control-sm bg-light" readonly />
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Interval PM (Bulan)</label>
                            <input type="number" id="PM_Interval_Bulan" class="form-control form-control-sm" min="1" max="24" placeholder="Contoh: 3 atau 6" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Interval PM (KM)</label>
                            <input type="number" id="PM_Interval_KM" class="form-control form-control-sm" min="0" step="500" placeholder="Contoh: 5000" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tanggal Jadwal PM Berikutnya</label>
                            <input type="date" id="PM_Tanggal_Jadwal" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Target KM PM Berikutnya</label>
                            <input type="number" id="PM_KM_Jadwal" class="form-control form-control-sm" min="0" placeholder="Contoh: 55000" />
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Vendor / Bengkel Default</label>
                            <input type="text" id="PM_Vendor" class="form-control form-control-sm" maxlength="40" placeholder="Nama bengkel rekanan" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="savePmScheduleModal();">
                        <i class="fa fa-save me-1"></i> Simpan Jadwal PM
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
function scrollToVmPanel(id) {
    var el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function switchToDuePmTab() {
    var btn = document.getElementById('tab-due-pm-btn');
    if (btn) btn.click();
}

function VehicleMaintenanceNew() {
    LoadM('VMDTL1', 'hrd-vehicle-maintenance-create');
    scrollToVmPanel('VMDTL1');
}

function VehicleMaintenanceNewPrefill(assetNo) {
    var url = 'hrd-vehicle-maintenance-create?asset_no=' + encodeURIComponent(assetNo) + '&jenis=preventif';
    LoadM('VMDTL1', url);
    scrollToVmPanel('VMDTL1');
}

function VehicleMaintenanceDetail() {
    var id = VehicleMaintenance.getSelectedRowId();
    if (id == null) {
        alert('Pilih Baris terlebih dulu');
        return false;
    }
    var url = 'hrd-vehicle-maintenance-detail?ID=' + encodeURIComponent(id);
    LoadM('VMDTL', url);
    scrollToVmPanel('VMDTL');
    return false;
}

function VehicleMaintenanceRefresh() {
    VehicleMaintenance.clearAndLoad('hrd-vehicle-maintenance-data');
}

function filterDuePmTable(mode, btn) {
    $(btn).siblings().removeClass('active');
    $(btn).addClass('active');

    $('#DuePmTable tbody tr.due-pm-row').each(function() {
        var needs = $(this).data('needs');
        var status = $(this).data('status');
        if (mode === 'ALL') {
            $(this).show();
        } else if (mode === 'DUE') {
            $(this).toggle(String(needs) === '1');
        } else if (mode === 'UNSET') {
            $(this).toggle(status === 'UNSET');
        }
    });
}

function openPmScheduleModal(assetNo, label, intervalBulan, intervalKm, tglJadwal, kmJadwal, vendor) {
    $('#PM_Asset_No').val(assetNo);
    $('#PM_Vehicle_Label').val(assetNo + ' - ' + label);
    $('#PM_Interval_Bulan').val(intervalBulan > 0 ? intervalBulan : '');
    $('#PM_Interval_KM').val(intervalKm > 0 ? intervalKm : '');
    $('#PM_Tanggal_Jadwal').val(tglJadwal && tglJadwal !== '0000-00-00' ? tglJadwal : '');
    $('#PM_KM_Jadwal').val(kmJadwal > 0 ? kmJadwal : '');
    $('#PM_Vendor').val(vendor || '');

    var modalEl = document.getElementById('pmScheduleModal');
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } else {
        $('#pmScheduleModal').modal('show');
    }
}

function savePmScheduleModal() {
    $.ajax({
        url: 'hrd-vehicle-maintenance-pm-schedule-save',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        data: {
            Asset_No: $('#PM_Asset_No').val(),
            Interval_PM: $('#PM_Interval_Bulan').val(),
            Interval_KM_PM: $('#PM_Interval_KM').val(),
            Tanggal_Jadwal_PM: $('#PM_Tanggal_Jadwal').val(),
            KM_Jadwal_PM: $('#PM_KM_Jadwal').val(),
            Repair_Vendor: $('#PM_Vendor').val()
        },
        success: function(res) {
            if (res.status === 'success') {
                alert(res.message);
                window.location.reload();
            } else {
                alert(res.message || 'Gagal menyimpan jadwal PM');
            }
        },
        error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan server';
            alert(msg);
        }
    });
}

VehicleMaintenance = new dhtmlXGridObject('VehicleMaintenance');
VehicleMaintenance.setImagePath("./dhtmlx/imgs/");
VehicleMaintenance.setHeader("No,Request No,Asset No,Jenis Perawatan,Plat Nomor,Nama Kendaraan,Kilometer,Biaya (Rp),Status");
VehicleMaintenance.attachHeader("&nbsp;,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");
VehicleMaintenance.setInitWidths("55,175,125,115,105,220,90,120,135");
VehicleMaintenance.setColAlign("center,center,center,center,center,left,right,right,center");
VehicleMaintenance.setDateFormat("%Y-%m-%d");
VehicleMaintenance.setColSorting("int,str,str,str,str,str,int,str,str");
VehicleMaintenance.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
VehicleMaintenance.setNumberFormat("0,000",6,".",",");
VehicleMaintenance.enableSmartRendering(true, 150);
VehicleMaintenance.setSkin("dhx_skyblue");
VehicleMaintenance.init();
VehicleMaintenance.clearAndLoad('hrd-vehicle-maintenance-data');

function refreshdata() {
    VehicleMaintenance.clearAndLoad('hrd-vehicle-maintenance-data');
}
</script>
@endsection
