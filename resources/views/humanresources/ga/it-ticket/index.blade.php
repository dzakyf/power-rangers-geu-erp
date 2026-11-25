@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                IT Support &amp; Helpdesk
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">General Affairs</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">IT Support &amp; Helpdesk</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if (can_menu('SI01161', 'view'))
                <a class="btn btn-outline-light btn-sm text-light" href="{{ url('hrd-it-category-index') }}">
                    <i class="fa fa-tags me-1"></i> Master Kategori IT
                </a>
            @endif
            @if (can_menu('SI01159', 'view'))
                <a class="btn btn-outline-light btn-sm text-light" href="{{ url('hrd-it-my-assignment-index') }}">
                    <i class="fa fa-tasks me-1"></i> My IT Assignments
                </a>
            @endif
            @if (can_menu('SI01013', 'view'))
                <a class="btn btn-outline-light btn-sm text-light" href="{{ url('hrd-ga-ticket-index') }}">
                    <i class="fa fa-ticket-alt me-1"></i> GA Ticketing &amp; Voucher
                </a>
            @endif
        </div>
    </div>

    {{-- Dashboard KPI IT --}}
    <div class="row mt-2 mb-3">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Tiket Aktif</div>
                    <div class="fs-3 fw-bold text-primary" id="itKpiActive">{{ (int) ($summary['total_active'] ?? 0) }}</div>
                    <div class="small text-muted">Belum ditutup</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Open / Antrean</div>
                    <div class="fs-3 fw-bold text-info" id="itKpiOpen">{{ (int) ($summary['open_count'] ?? 0) }}</div>
                    <div class="small text-muted">Menunggu respon IT</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Dalam Penanganan</div>
                    <div class="fs-3 fw-bold text-warning" id="itKpiProgress">{{ (int) ($summary['in_progress_count'] ?? 0) }}</div>
                    <div class="small text-muted">Diagnosa / Recovery</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Gangguan Sistem</div>
                    <div class="fs-3 fw-bold text-danger" id="itKpiIncident">{{ (int) ($summary['incident_active'] ?? 0) }}</div>
                    <div class="small text-muted">Incident aktif</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Tunggu Feedback</div>
                    <div class="fs-3 fw-bold text-success" id="itKpiFeedback">{{ (int) ($summary['waiting_feedback'] ?? 0) }}</div>
                    <div class="small text-muted">Konfirmasi user</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100 border-start border-dark border-4">
                <div class="card-body py-3">
                    <div class="text-muted small fw-semibold">Prev / Corr</div>
                    <div class="fs-5 fw-bold text-dark">
                        <span id="itKpiPreventive">{{ (int) ($summary['preventive_count'] ?? 0) }}</span>
                        <span class="text-muted">/</span>
                        <span id="itKpiCorrective">{{ (int) ($summary['corrective_count'] ?? 0) }}</span>
                    </div>
                    <div class="small text-muted">Preventive / Corrective</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-2">
        <div class="card-header bg-light pt-3 pb-0 border-bottom">
            <ul class="nav nav-tabs card-header-tabs" id="ITTicketModeTabs" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active fw-semibold" onclick="ITTicketSwitchTab('', this)">Semua Tiket IT</button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link fw-semibold" onclick="ITTicketSwitchTab('IT_SUPPORT', this)">
                        <i class="fa fa-headset me-1 text-primary"></i> Tiket Bantuan IT
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link fw-semibold" onclick="ITTicketSwitchTab('IT_INCIDENT', this)">
                        <i class="fa fa-exclamation-triangle me-1 text-danger"></i> Gangguan Sistem
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link fw-semibold" onclick="ITTicketSwitchTab('IT_DEVICE_REQUEST', this)">
                        <i class="fa fa-laptop me-1 text-info"></i> Pengajuan Perangkat IT
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link fw-semibold" onclick="ITTicketSwitchTab('KB', this)">
                        <i class="fa fa-book me-1 text-success"></i> Catatan Knowledge Base
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            {{-- Panel Daftar Tiket IT --}}
            <div id="ITTicketGridPanel">
                <div class="row g-2 align-items-center mb-3">
                    <div class="col-md-2">
                        <select id="IT_Filter_Action" class="form-control form-control-sm">
                            <option value="">Semua Action Type</option>
                            <option value="CORRECTIVE">Corrective Action</option>
                            <option value="PREVENTIVE">Preventive Action</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="IT_Filter_Status" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            <option value="ACTIVE">Aktif</option>
                            <option value="OPEN">Open</option>
                            <option value="IN_PROGRESS">In Progress</option>
                            <option value="TROUBLESHOOTING">Troubleshooting</option>
                            <option value="ESCALATED">Escalated</option>
                            <option value="VALIDATED">Validasi Gangguan</option>
                            <option value="ROOT_CAUSE_ANALYSIS">Root Cause</option>
                            <option value="RECOVERY">Recovery / Perbaikan</option>
                            <option value="TESTING">Testing Sistem</option>
                            <option value="SYSTEM_NORMAL">Sistem Normal</option>
                            <option value="WAITING_MANAGER_APPROVAL">Approval Manager</option>
                            <option value="DEVICE_PREPARATION">Persiapan Perangkat</option>
                            <option value="ASSIGNED_TO_USER">Assign User</option>
                            <option value="HANDOVER">Serah Terima</option>
                            <option value="RESOLVED">Resolved</option>
                            <option value="USER_CONFIRMED">Konfirmasi User</option>
                            <option value="ON_HOLD">On Hold</option>
                            <option value="REOPENED">Reopened</option>
                            <option value="CLOSED">Closed</option>
                            <option value="REJECTED">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="IT_Filter_Priority" class="form-control form-control-sm">
                            <option value="">Semua Prioritas</option>
                            <option value="LOW">Low</option>
                            <option value="NORMAL">Normal</option>
                            <option value="HIGH">High</option>
                            <option value="URGENT">Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="IT_Filter_Search" class="form-control form-control-sm" placeholder="Cari no tiket / subjek / reporter" />
                    </div>
                    <div class="col-md-3 d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-primary" onclick="ITTicketApplyFilter()">Terapkan Filter</button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="ITTicketResetFilter()">Reset</button>
                    </div>
                </div>

                <div id="ITTicketGrid" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>

                <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                    <button type="button" class="btn btn-info btn-sm text-white" onclick="ITTicketRefresh()">Refresh Data</button>
                    @if (can_menu('SI01158', 'create'))
                        <button type="button" class="btn btn-primary btn-sm" onclick="ITTicketNew()">Buat Tiket IT Baru</button>
                    @endif
                    <button type="button" class="btn btn-success btn-sm" onclick="ITTicketDetail()">Detail / Tracking Tiket</button>
                </div>
            </div>

            {{-- Panel Catatan Knowledge Base Sederhana --}}
            <div id="ITTicketKbPanel" style="display:none;">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <input type="text" id="IT_KB_SearchInput" class="form-control" placeholder="Cari solusi, judul KB, root cause, atau gejala..." />
                            <button type="button" class="btn btn-primary" onclick="ITSearchKb()">Cari Solusi KB</button>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">Solusi diambil dari tiket IT yang ditandai sebagai catatan Knowledge Base.</small>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No Tiket</th>
                                <th>Tipe / Action</th>
                                <th>Judul KB</th>
                                <th>Root Cause / Diagnosa</th>
                                <th>Solusi</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="ITKbTableBody">
                            <tr><td colspan="6" class="text-center text-muted py-3">Memuat catatan Knowledge Base...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="ITTICKETDTL1"></div>
    <div id="ITTICKETDTL"></div>

<script>
var currentItMode = '';

function ITTicketQuery() {
    var params = [];
    var actionType = document.getElementById('IT_Filter_Action').value;
    var status = document.getElementById('IT_Filter_Status').value;
    var priority = document.getElementById('IT_Filter_Priority').value;
    var search = document.getElementById('IT_Filter_Search').value;
    if (currentItMode) params.push('workflow_mode=' + encodeURIComponent(currentItMode));
    if (actionType) params.push('action_type=' + encodeURIComponent(actionType));
    if (status) params.push('status=' + encodeURIComponent(status));
    if (priority) params.push('priority=' + encodeURIComponent(priority));
    if (search) params.push('search=' + encodeURIComponent(search));
    return params.length ? ('?' + params.join('&')) : '';
}

function ITTicketSwitchTab(mode, btn) {
    $('#ITTicketModeTabs .nav-link').removeClass('active');
    $(btn).addClass('active');
    if (mode === 'KB') {
        $('#ITTicketGridPanel').hide();
        $('#ITTicketKbPanel').show();
        ITSearchKb();
    } else {
        currentItMode = mode;
        $('#ITTicketKbPanel').hide();
        $('#ITTicketGridPanel').show();
        ITTicketApplyFilter();
    }
}

function ITTicketApplyFilter() {
    ITTicketGrid.clearAndLoad('hrd-it-ticket-data' + ITTicketQuery());
}

function ITTicketResetFilter() {
    document.getElementById('IT_Filter_Action').value = '';
    document.getElementById('IT_Filter_Status').value = '';
    document.getElementById('IT_Filter_Priority').value = '';
    document.getElementById('IT_Filter_Search').value = '';
    ITTicketApplyFilter();
}

function ITTicketRefreshSummary() {
    $.get('hrd-it-ticket-summary', function (res) {
        if (!res || !res.summary) return;
        var s = res.summary;
        $('#itKpiActive').text(s.total_active || 0);
        $('#itKpiOpen').text(s.open_count || 0);
        $('#itKpiProgress').text(s.in_progress_count || 0);
        $('#itKpiIncident').text(s.incident_active || 0);
        $('#itKpiFeedback').text(s.waiting_feedback || 0);
        $('#itKpiPreventive').text(s.preventive_count || 0);
        $('#itKpiCorrective').text(s.corrective_count || 0);
    });
}

function ITTicketRefresh() {
    ITTicketGrid.clearAndLoad('hrd-it-ticket-data' + ITTicketQuery());
    ITTicketRefreshSummary();
}

function ITTicketNew() {
    var modeParam = currentItMode ? ('&mode=' + encodeURIComponent(currentItMode)) : '';
    LoadM('ITTICKETDTL1', 'hrd-it-ticket-create?partial=1' + modeParam);
}

function ITTicketDetail() {
    var id = ITTicketGrid.getSelectedRowId();
    if (!id) {
        alert('Pilih baris tiket IT terlebih dahulu.');
        return false;
    }
    LoadM('ITTICKETDTL', 'hrd-it-ticket-detail?partial=1&ID=' + encodeURIComponent(id));
}

function ITOpenTicketById(id) {
    LoadM('ITTICKETDTL', 'hrd-it-ticket-detail?partial=1&ID=' + encodeURIComponent(id));
    var el = document.getElementById('ITTICKETDTL');
    if (el && el.scrollIntoView) el.scrollIntoView({behavior: 'smooth', block: 'start'});
}

function ITSearchKb() {
    var q = $('#IT_KB_SearchInput').val() || '';
    $.get('hrd-it-ticket-kb-search', {q: q}, function (res) {
        var rows = (res && res.items) ? res.items : [];
        if (!rows.length) {
            $('#ITKbTableBody').html('<tr><td colspan="6" class="text-center text-muted py-3">Belum ada catatan Knowledge Base yang cocok.</td></tr>');
            return;
        }
        var html = '';
        rows.forEach(function (r) {
            html += '<tr>' +
                '<td class="fw-bold">' + itEsc(r.Ticket_No) + '</td>' +
                '<td><span class="badge bg-info text-white">' + itEsc(r.Workflow_Mode) + '</span> <span class="badge bg-secondary text-white">' + itEsc(r.Action_Type) + '</span></td>' +
                '<td>' + itEsc(r.Kb_Title || r.Ticket_Subject) + '</td>' +
                '<td>' + itEsc(r.Root_Cause || r.Diagnosis_Note || '-') + '</td>' +
                '<td>' + itEsc(r.Kb_Solution || '-') + '</td>' +
                '<td class="text-end"><button type="button" class="btn btn-xs btn-outline-primary" onclick="ITOpenTicketById(' + Number(r.ID) + ')">Buka Tiket</button></td>' +
                '</tr>';
        });
        $('#ITKbTableBody').html(html);
    });
}

function itEsc(v) {
    return String(v === null || v === undefined ? '' : v)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

window.ITTicketReloadDetail = function (id) {
    LoadM('ITTICKETDTL', 'hrd-it-ticket-detail?partial=1&ID=' + encodeURIComponent(id));
};

window.ITTicketRefreshGrid = function () {
    ITTicketRefresh();
};

$('#IT_KB_SearchInput').on('keypress', function (e) {
    if (e.which === 13) ITSearchKb();
});

ITTicketGrid = new dhtmlXGridObject('ITTicketGrid');
ITTicketGrid.setImagePath('./dhtmlx/imgs/');
ITTicketGrid.setHeader('No,No Tiket,Tipe Kebutuhan,Action Type,Subjek,Prioritas,Status,Assignee,Resolve Due,Dibuat,Reporter');
ITTicketGrid.attachHeader('&nbsp;,#text_filter,#select_filter,#select_filter,#text_filter,#select_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter');
ITTicketGrid.setInitWidths('45,150,140,100,220,85,140,130,135,135,130');
ITTicketGrid.setColAlign('center,center,center,center,left,center,center,left,center,center,left');
ITTicketGrid.setColSorting('int,str,str,str,str,str,str,str,str,str,str');
ITTicketGrid.setColTypes('ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro');
ITTicketGrid.enableSmartRendering(true, 200);
ITTicketGrid.setSkin('dhx_skyblue');
ITTicketGrid.init();
ITTicketGrid.attachEvent('onRowDblClicked', function (rowId) {
    if (rowId) {
        LoadM('ITTICKETDTL', 'hrd-it-ticket-detail?partial=1&ID=' + encodeURIComponent(rowId));
    }
    return true;
});
ITTicketGrid.clearAndLoad('hrd-it-ticket-data');
</script>
@endsection
