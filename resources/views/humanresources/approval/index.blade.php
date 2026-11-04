@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
/* Custom Inbox UX styling */
.approval-metric-card {
    transition: all 0.2s ease-in-out;
    border-radius: 8px;
    border: 1px solid rgba(0,0,0,0.06);
}
.approval-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.status-pill-badge {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.25rem 0.6rem;
    border-radius: 50rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.quick-action-btn {
    transition: all 0.15s ease;
    font-weight: 500;
}
.quick-action-btn:hover:not(:disabled) {
    transform: translateY(-1px);
}
.quick-action-btn:disabled {
    opacity: 0.45 !important;
    cursor: not-allowed;
}
#ApprovalInboxGrid {
    border-radius: 4px;
}
/* Dynamic Category Tab Navigation Styling */
.approval-policy-tabs {
    border-bottom: 1px solid #e9ecef;
    gap: 0.35rem;
    flex-wrap: nowrap;
    overflow-x: auto;
    scrollbar-width: thin;
}
.approval-policy-tabs .nav-link {
    border: 1px solid transparent;
    border-bottom: 3px solid transparent;
    border-radius: 6px 6px 0 0;
    color: #495057;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 0.65rem 1rem;
    white-space: nowrap;
    transition: all 0.18s ease;
    background-color: #f8f9fa;
}
.approval-policy-tabs .nav-link:hover {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}
.approval-policy-tabs .nav-link.active {
    color: #0d6efd;
    background-color: #ffffff;
    border-color: #e9ecef #e9ecef #ffffff;
    border-bottom: 3px solid #0d6efd;
    box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.03);
}
.policy-subfilter-pill {
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 0.76rem;
    font-weight: 500;
    border-radius: 50rem;
    padding: 0.3rem 0.75rem;
    user-select: none;
}
.policy-subfilter-pill:hover {
    transform: translateY(-1px);
}
.policy-subfilter-pill.active {
    background-color: #0d6efd !important;
    color: #ffffff !important;
    border-color: #0d6efd !important;
    box-shadow: 0 2px 5px rgba(13, 110, 253, 0.25);
}
/* Policy Step Pipeline Styling */
.policy-step-flow-card {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    transition: all 0.15s ease;
}
.policy-step-node {
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    background-color: #f8f9fa;
    color: #343a40;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.15s ease;
    user-select: none;
}
.policy-step-node:hover {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.06);
    transform: translateY(-1px);
}
.policy-step-node.active {
    background-color: #0d6efd !important;
    color: #ffffff !important;
    border-color: #0d6efd !important;
    box-shadow: 0 2px 5px rgba(13, 110, 253, 0.25);
}
.policy-step-node.active .step-stage-badge {
    background-color: #ffffff !important;
    color: #0d6efd !important;
}
.step-stage-badge {
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    background-color: #0d6efd;
    color: #ffffff;
}
.policy-step-arrow {
    color: #adb5bd;
    font-size: 0.7rem;
    margin: 0 0.15rem;
}
</style>

<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            Approval Inbox
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Approval Engine</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Inbox Persetujuan</li>
            </ol>
        </nav>
    </div>
    <div class="d-none d-sm-flex align-items-center gap-2">
        <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
            <i class="fa fa-layer-group me-1"></i> Position &amp; Policy-Based Engine
        </span>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fa fa-triangle-exclamation me-2"></i>
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Quick Status Metrics --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-warning-transparent text-warning rounded-3 me-3">
                    <i class="fa fa-clock fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Perlu Persetujuan</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountPending">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-primary-transparent text-primary rounded-3 me-3">
                    <i class="fa fa-user-check fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Claimed By You</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountClaimed">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-info-transparent text-info rounded-3 me-3">
                    <i class="fa fa-inbox fs-20"></i>
                </div>
                <div>
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Total Task di Inbox</h6>
                    <h4 class="fw-bold mb-0 text-dark" id="metricCountTotal">-</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 approval-metric-card bg-white h-100">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="avatar avatar-md bg-secondary-transparent text-secondary rounded-3 me-3">
                    <i class="fa fa-arrow-pointer fs-20"></i>
                </div>
                <div class="overflow-hidden">
                    <h6 class="text-muted text-uppercase fs-11 fw-bold mb-1">Task Terpilih</h6>
                    <div id="metricSelectedDoc" class="fw-bold text-dark text-truncate fs-13" title="Belum dipilih">Belum dipilih</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Actions Toolbar Card --}}
<div class="card shadow-sm border mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            {{-- Action Buttons --}}
            <div class="d-flex flex-wrap align-items-center gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm quick-action-btn" onclick="ApprovalInboxRefresh();" title="Refresh Daftar Task">
                    <i class="fa fa-rotate me-1"></i> Refresh
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm quick-action-btn" id="btnActionDetail" onclick="ApprovalInboxDetail();" title="Buka Detail Task">
                    <i class="fa fa-eye me-1"></i> Detail
                </button>

                <div class="vr my-1 mx-1 text-muted opacity-50"></div>

                {{-- Direct Decision Action Buttons --}}
                <button type="button" class="btn btn-success btn-sm text-white quick-action-btn" id="btnActionApprove" onclick="ApprovalInboxAct('APPROVE');" title="Setujui Permohonan">
                    <i class="fa fa-check me-1"></i> Approve
                </button>
                <button type="button" class="btn btn-danger btn-sm text-white quick-action-btn" id="btnActionReject" onclick="ApprovalInboxAct('REJECT');" title="Tolak Permohonan">
                    <i class="fa fa-xmark me-1"></i> Reject
                </button>
                <button type="button" class="btn btn-primary btn-sm text-white quick-action-btn" id="btnActionClaim" onclick="ApprovalInboxClaim();" title="Claim Task agar Tidak Diambil Approver Lain">
                    <i class="fa fa-user-check me-1"></i> Claim
                </button>

                @if($isAdmin)
                <button type="button" class="btn btn-warning btn-sm text-dark quick-action-btn" id="btnActionRelease" onclick="ApprovalInboxRelease();" title="Release Claim (Admin)">
                    <i class="fa fa-lock-open me-1"></i> Release
                </button>
                <div class="vr my-1 mx-1 text-muted opacity-50"></div>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-dark btn-sm dropdown-toggle quick-action-btn" type="button" id="adminApprovalMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-gear me-1"></i> Admin Tools
                    </button>
                    <ul class="dropdown-menu shadow border-0 fs-13" aria-labelledby="adminApprovalMenu">
                        <li>
                            <a class="dropdown-item py-2" href="{{ url('hrd-approval-escalation') }}">
                                <i class="fa fa-triangle-exclamation text-warning me-2"></i> Escalation Queue
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ url('hrd-approval-policy') }}">
                                <i class="fa fa-sliders text-primary me-2"></i> Policy Management
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ url('hrd-organization-chart') }}">
                                <i class="fa fa-sitemap text-info me-2"></i> Bagan Organisasi
                            </a>
                        </li>
                    </ul>
                </div>
                @endif
            </div>

            {{-- Task Selection Indicator Bar --}}
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small d-none d-md-inline">Status Pilihan:</span>
                <span id="selectedTaskIndicator" class="badge bg-light text-dark border px-3 py-2 fs-12 fw-normal">
                    <i class="fa fa-arrow-pointer text-muted me-1"></i> Belum ada task dipilih
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Dynamic Policy Category Tab Navigation + dhtmlXGrid Container --}}
<div class="card shadow-sm border mb-3">
    {{-- Dynamic Nav Tabs Header --}}
    <div class="card-header bg-white pt-3 pb-0 px-3 border-bottom-0">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
            <span class="fw-semibold text-dark fs-13">
                <i class="fa fa-layer-group text-primary me-1"></i> Kategori Approval Policy &amp; Antrean Task
            </span>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-muted border fw-normal" id="gridFilterHelper">
                    <i class="fa fa-filter me-1"></i> Ketik pada kolom tabel untuk memfilter
                </span>
                <small class="text-muted" id="inboxRowCountLabel">Memuat data...</small>
            </div>
        </div>

        <ul class="nav nav-tabs approval-policy-tabs" id="approvalPolicyTabNav" role="tablist">
            {{-- Tab Semua Kategori --}}
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center gap-2"
                        id="tab-btn-ALL"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-pane-ALL"
                        data-category="ALL"
                        type="button"
                        role="tab"
                        aria-controls="tab-pane-ALL"
                        aria-selected="true"
                        onclick="ApprovalInboxSwitchTab('ALL', 0);">
                    <i class="fa fa-inbox text-primary"></i>
                    <span>Semua Kategori</span>
                    <span class="badge rounded-pill bg-primary text-white px-2 py-1" id="tabBadgeCount_ALL">{{ $totalAllTasks ?? 0 }}</span>
                </button>
            </li>

            {{-- Dynamic Tabs mengikuti kategori di tabel approval_policy --}}
            @foreach($policyTabs ?? [] as $tab)
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center gap-2"
                        id="tab-btn-{{ $tab['key'] }}"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-pane-{{ $tab['key'] }}"
                        data-category="{{ $tab['key'] }}"
                        type="button"
                        role="tab"
                        aria-controls="tab-pane-{{ $tab['key'] }}"
                        aria-selected="false"
                        onclick="ApprovalInboxSwitchTab('{{ $tab['key'] }}', 0);">
                    <i class="fa {{ $tab['icon'] }} text-{{ $tab['badge_color'] }}"></i>
                    <span>{{ $tab['label'] }}</span>
                    <span class="badge rounded-pill {{ $tab['task_count'] > 0 ? 'bg-' . $tab['badge_color'] . ' text-white' : 'bg-light text-muted border' }} px-2 py-1"
                          id="tabBadgeCount_{{ $tab['key'] }}"
                          data-badge-color="{{ $tab['badge_color'] }}">{{ $tab['task_count'] }}</span>
                </button>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Dynamic Tab Panes (Info Kategori & Sub-Filter per Approval Policy) --}}
    <div class="tab-content bg-light border-bottom px-3 py-2" id="approvalPolicyTabContent">
        {{-- Pane ALL --}}
        <div class="tab-pane fade show active" id="tab-pane-ALL" role="tabpanel" aria-labelledby="tab-btn-ALL">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                <div class="text-muted">
                    <i class="fa fa-list-check text-primary me-1"></i>
                    Menampilkan seluruh antrean persetujuan lintas kategori (<strong>{{ count($policyTabs ?? []) }}</strong> kategori policy terdaftar).
                </div>
                <div class="text-muted">
                    Pilih tab kategori di atas untuk memfokuskan navigasi dan memfilter berdasarkan spesifik <strong>Approval Policy</strong>.
                </div>
            </div>
        </div>

        {{-- Dynamic Panes per Request_Type dari approval_policy --}}
        @foreach($policyTabs ?? [] as $tab)
        <div class="tab-pane fade" id="tab-pane-{{ $tab['key'] }}" role="tabpanel" aria-labelledby="tab-btn-{{ $tab['key'] }}">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="d-flex flex-wrap align-items-center gap-1">
                    <span class="text-muted small fw-semibold me-1">
                        <i class="fa fa-sliders text-{{ $tab['badge_color'] }} me-1"></i> Filter Policy ({{ $tab['key'] }}):
                    </span>
                    <span class="badge bg-white text-dark border policy-subfilter-pill active"
                          id="policyPill_{{ $tab['key'] }}_0"
                          data-category="{{ $tab['key'] }}"
                          data-policy-id="0"
                          onclick="ApprovalInboxSelectPolicyPill('{{ $tab['key'] }}', 0);">
                        Semua {{ $tab['label'] }}
                    </span>
                    @foreach($tab['policies'] as $pol)
                    <span class="badge bg-white text-dark border policy-subfilter-pill"
                          id="policyPill_{{ $tab['key'] }}_{{ $pol['id'] }}"
                          data-category="{{ $tab['key'] }}"
                          data-policy-id="{{ $pol['id'] }}"
                          title="{{ $pol['name'] }} ({{ $pol['status'] }})"
                          onclick="ApprovalInboxSelectPolicyPill('{{ $tab['key'] }}', {{ $pol['id'] }});">
                        <i class="fa fa-code-branch me-1 text-muted"></i>{{ $pol['code'] }}
                        <span class="opacity-75 ms-1">({{ count($pol['steps'] ?? []) }} Step)</span>
                        @if(strtoupper($pol['status']) !== 'ACTIVE')
                            <small class="text-warning ms-1">[{{ $pol['status'] }}]</small>
                        @endif
                    </span>
                    @endforeach
                </div>
                <div class="small text-muted">
                    <span class="badge bg-white text-secondary border">
                        {{ $tab['active_policies'] }} Active / {{ $tab['total_policies'] }} Total Policy
                    </span>
                </div>
            </div>

            {{-- Alur Approval Policy Step (approval_policy_step) --}}
            @if(!empty($tab['policies']))
            <div class="d-flex flex-column gap-1 mt-1" id="policyStepsContainer_{{ $tab['key'] }}">
                @foreach($tab['policies'] as $pol)
                <div class="policy-step-flow-card d-flex flex-wrap align-items-center justify-content-between gap-2"
                     id="policyStepRow_{{ $tab['key'] }}_{{ $pol['id'] }}"
                     data-policy-step-row="{{ $pol['id'] }}">
                    <div class="d-flex flex-wrap align-items-center gap-1">
                        <span class="badge bg-light text-dark border fw-bold me-1" title="{{ $pol['name'] }}">
                            <i class="fa fa-route text-{{ $tab['badge_color'] }} me-1"></i>{{ $pol['code'] }}:
                        </span>

                        @forelse($pol['steps'] ?? [] as $stepIdx => $step)
                            @if($stepIdx > 0)
                                <i class="fa fa-chevron-right policy-step-arrow"></i>
                            @endif
                            <span class="policy-step-node"
                                  data-category="{{ $tab['key'] }}"
                                  data-policy-id="{{ $pol['id'] }}"
                                  data-stage-order="{{ $step['stage'] }}"
                                  title="Klik untuk memfilter antrean pada {{ $pol['code'] }} - Stage {{ $step['stage'] }} (Level {{ $step['level'] }}, Mode: {{ $step['mode'] }})"
                                  onclick="ApprovalInboxSelectPolicyStep('{{ $tab['key'] }}', {{ $pol['id'] }}, {{ $step['stage'] }});">
                                <span class="step-stage-badge">S{{ $step['stage'] }}</span>
                                <span class="fw-semibold">{{ $step['label'] }}</span>
                                <small class="opacity-75">(Lv.{{ $step['level'] }}{{ $step['position_code'] ? ' / ' . $step['position_code'] : '' }}{{ $step['department'] ? ' / ' . $step['department'] : '' }} &bull; {{ $step['mode'] }}{{ $step['quorum'] ? ':' . $step['quorum'] : '' }})</small>
                            </span>
                        @empty
                            <span class="text-muted small fst-italic">Belum ada step approval yang dikonfigurasi pada policy ini.</span>
                        @endforelse
                    </div>
                    <div class="small text-muted d-none d-xl-block">
                        <span class="text-secondary">{{ $pol['name'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="card-body p-0">
        <div id="ApprovalInboxGrid" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
    </div>
    <div class="card-footer bg-light py-2 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">
            <i class="fa fa-circle-info text-info me-1"></i> <strong>Tips UX:</strong> Klik tab kategori di atas untuk memfilter jenis pengajuan, atau klik pada baris tabel untuk langsung meninjau &amp; melakukan <strong>Approve / Reject</strong>.
        </small>
        <small class="text-muted">
            Double-click pada baris untuk scroll langsung ke panel detail.
        </small>
    </div>
</div>

{{-- Detail Container --}}
<div id="ApprovalInboxDetail" class="mb-4"></div>

{{-- ========================================================================= --}}
{{-- MODALS INTERAKTIF (PENGGANTI JAVASCRIPT PROMPT / CONFIRM / ALERT)          --}}
{{-- ========================================================================= --}}

{{-- 1. Modal Approve / Reject --}}
<div class="modal fade" id="modalApprovalAct" tabindex="-1" aria-labelledby="modalApprovalActLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header py-3" id="modalApprovalActHeader">
                <h5 class="modal-title fw-bold" id="modalApprovalActLabel">
                    <i class="fa fa-circle-check text-success me-2"></i>Konfirmasi Persetujuan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="modalActTaskId" />
                <input type="hidden" id="modalActAction" />

                {{-- Task Summary Box --}}
                <div class="bg-light p-3 rounded-3 border mb-3" id="modalActSummaryBox">
                    <div class="row g-2 small">
                        <div class="col-sm-4 text-muted fw-semibold">No. Permohonan:</div>
                        <div class="col-sm-8 fw-bold text-dark" id="modalActRequestNo">-</div>
                        <div class="col-sm-4 text-muted fw-semibold">Tipe Request:</div>
                        <div class="col-sm-8" id="modalActRequestType">-</div>
                        <div class="col-sm-4 text-muted fw-semibold">Pemohon:</div>
                        <div class="col-sm-8 fw-semibold" id="modalActRequester">-</div>
                        <div class="col-sm-4 text-muted fw-semibold">Posisi Approval:</div>
                        <div class="col-sm-8" id="modalActPosition">-</div>
                    </div>
                </div>

                {{-- Notes Form --}}
                <div class="mb-2">
                    <label for="modalActNotes" class="form-label fw-semibold" id="modalActNotesLabel">
                        Catatan Persetujuan (Opsional):
                    </label>
                    <textarea class="form-control" id="modalActNotes" rows="3" placeholder="Tulis catatan atau instruksi jika ada..."></textarea>
                    <div class="form-text small" id="modalActNotesHelp">
                        Catatan ini akan tersimpan ke dalam riwayat audit log approval.
                    </div>
                    <div class="invalid-feedback" id="modalActNotesFeedback">
                        Alasan wajib diisi saat menolak permohonan.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm shadow-sm" id="modalActSubmitBtn" onclick="ApprovalInboxSubmitAct();">
                    <i class="fa fa-check me-1"></i> Ya, Setujui
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 2. Modal Claim Task --}}
<div class="modal fade" id="modalApprovalClaim" tabindex="-1" aria-labelledby="modalApprovalClaimLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header py-3">
                <h5 class="modal-title fw-bold text-primary" id="modalApprovalClaimLabel">
                    <i class="fa fa-user-check me-2"></i>Konfirmasi Claim Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="modalClaimTaskId" />
                <p class="mb-3 fs-14">
                    Apakah Anda yakin ingin meng-claim task approval untuk permohonan <strong id="modalClaimRequestNo" class="text-primary">-</strong>?
                </p>
                <div class="alert alert-info py-2 px-3 small mb-0 border-0 shadow-none d-flex align-items-start">
                    <i class="fa fa-circle-info me-2 mt-1 fs-15"></i>
                    <div>Setelah di-claim, task ini akan terkunci sebagai tugas Anda dan kandidat lain tidak dapat mengambil tindakan sampai claim di-release atau diputuskan.</div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm shadow-sm" id="modalClaimSubmitBtn" onclick="ApprovalInboxSubmitClaim();">
                    <i class="fa fa-user-check me-1"></i> Ya, Claim Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 3. Modal Release Claim --}}
<div class="modal fade" id="modalApprovalRelease" tabindex="-1" aria-labelledby="modalApprovalReleaseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header py-3 bg-warning-transparent">
                <h5 class="modal-title fw-bold text-warning-dark" id="modalApprovalReleaseLabel">
                    <i class="fa fa-lock-open me-2"></i>Release Claim Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="modalReleaseTaskId" />
                <p class="small text-muted mb-3">
                    Task akan dikembalikan ke status <strong>PENDING</strong> sehingga seluruh kandidat approver terkait dapat kembali meng-claim atau menyetujui permohonan ini.
                </p>
                <div class="mb-2">
                    <label for="modalReleaseReason" class="form-label fw-semibold">
                        Alasan Release Claim <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="modalReleaseReason" rows="3" placeholder="Tulis alasan pelepasan claim..."></textarea>
                    <div class="invalid-feedback" id="modalReleaseReasonFeedback">
                        Alasan release claim wajib diisi.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning btn-sm text-dark shadow-sm" id="modalReleaseSubmitBtn" onclick="ApprovalInboxSubmitRelease();">
                    <i class="fa fa-lock-open me-1"></i> Release Claim
                </button>
            </div>
        </div>
    </div>
</div>

{{-- 4. Modal Alert Notifikasi (Pengganti alert() javascript biasa) --}}
<div class="modal fade" id="modalApprovalAlert" tabindex="-1" aria-labelledby="modalApprovalAlertLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow border-0 text-center p-3">
            <div class="modal-body py-2">
                <div id="modalAlertIconContainer" class="mb-3"></div>
                <h5 class="fw-bold mb-2" id="modalAlertTitle">Notifikasi</h5>
                <p class="text-muted small mb-0" id="modalAlertMessage"></p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0">
                <button type="button" class="btn btn-primary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var ApprovalInboxToken = @json(csrf_token());
var currentApprovalTaskId = null;
var currentApprovalCategory = 'ALL';
var currentApprovalPolicyId = 0;
var currentApprovalStageOrder = 0;

// Bootstrap Modal Instances
var bsModalAct = null;
var bsModalClaim = null;
var bsModalRelease = null;
var bsModalAlert = null;

document.addEventListener('DOMContentLoaded', function () {
    bsModalAct = new bootstrap.Modal(document.getElementById('modalApprovalAct'));
    bsModalClaim = new bootstrap.Modal(document.getElementById('modalApprovalClaim'));
    bsModalRelease = new bootstrap.Modal(document.getElementById('modalApprovalRelease'));
    bsModalAlert = new bootstrap.Modal(document.getElementById('modalApprovalAlert'));
    
    // Inisialisasi status tombol awal (disable jika belum ada baris terpilih)
    updateActionButtonState(null);
});

/**
 * Update Status Tombol Aksi (Enable/Disable berdasarkan task terpilih)
 */
function updateActionButtonState(id) {
    var hasSelection = (id !== null && id !== undefined && id !== '');
    
    var btnDetail = document.getElementById('btnActionDetail');
    var btnApprove = document.getElementById('btnActionApprove');
    var btnReject = document.getElementById('btnActionReject');
    var btnClaim = document.getElementById('btnActionClaim');
    var btnRelease = document.getElementById('btnActionRelease');

    if (btnDetail) btnDetail.disabled = !hasSelection;
    if (btnApprove) btnApprove.disabled = !hasSelection;
    if (btnReject) btnReject.disabled = !hasSelection;
    if (btnClaim) btnClaim.disabled = !hasSelection;
    if (btnRelease) btnRelease.disabled = !hasSelection;
}

/**
 * Tampilkan Notifikasi Modal interaktif
 */
function showAppNotification(title, message, type) {
    type = type || 'info';
    var iconHtml = '';

    if (type === 'success') {
        iconHtml = '<div class="avatar avatar-xl bg-success-transparent text-success rounded-circle mx-auto"><i class="fa fa-check fs-24"></i></div>';
    } else if (type === 'danger' || type === 'error') {
        iconHtml = '<div class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle mx-auto"><i class="fa fa-exclamation-circle fs-24"></i></div>';
    } else if (type === 'warning') {
        iconHtml = '<div class="avatar avatar-xl bg-warning-transparent text-warning rounded-circle mx-auto"><i class="fa fa-triangle-exclamation fs-24"></i></div>';
    } else {
        iconHtml = '<div class="avatar avatar-xl bg-info-transparent text-info rounded-circle mx-auto"><i class="fa fa-circle-info fs-24"></i></div>';
    }

    document.getElementById('modalAlertIconContainer').innerHTML = iconHtml;
    document.getElementById('modalAlertTitle').innerText = title;
    document.getElementById('modalAlertMessage').innerText = message;

    if (bsModalAlert) {
        bsModalAlert.show();
    }
}

/**
 * Bangun URL data grid sesuai tab kategori, sub-filter policy, & step stage aktif
 */
function buildInboxDataUrl() {
    var params = [];
    if (currentApprovalCategory && currentApprovalCategory !== 'ALL') {
        params.push('request_type=' + encodeURIComponent(currentApprovalCategory));
    }
    if (currentApprovalPolicyId && parseInt(currentApprovalPolicyId, 10) > 0) {
        params.push('policy_id=' + encodeURIComponent(currentApprovalPolicyId));
    }
    if (currentApprovalStageOrder && parseInt(currentApprovalStageOrder, 10) > 0) {
        params.push('stage_order=' + encodeURIComponent(currentApprovalStageOrder));
    }
    return 'hrd-approval-inbox-data' + (params.length ? '?' + params.join('&') : '');
}

/**
 * Sinkronisasi tampilan pill policy dan alur approval_policy_step di dalam Tab Pane
 */
function syncPolicyPaneVisualState(categoryKey) {
    if (!categoryKey || categoryKey === 'ALL') return;
    var pane = document.getElementById('tab-pane-' + categoryKey);
    if (!pane) return;

    // 1. Update status active pada pill policy
    var pills = pane.querySelectorAll('.policy-subfilter-pill');
    for (var i = 0; i < pills.length; i++) {
        var pillPolId = parseInt(pills[i].getAttribute('data-policy-id') || '0', 10);
        if (pillPolId === currentApprovalPolicyId) {
            pills[i].classList.add('active');
        } else {
            pills[i].classList.remove('active');
        }
    }

    // 2. Tampilkan hanya baris step untuk policy yang dipilih (atau semua jika policyId = 0)
    var stepRows = pane.querySelectorAll('[data-policy-step-row]');
    for (var j = 0; j < stepRows.length; j++) {
        var rowPolId = parseInt(stepRows[j].getAttribute('data-policy-step-row') || '0', 10);
        if (currentApprovalPolicyId === 0 || rowPolId === currentApprovalPolicyId) {
            stepRows[j].classList.remove('d-none');
            stepRows[j].classList.add('d-flex');
        } else {
            stepRows[j].classList.remove('d-flex');
            stepRows[j].classList.add('d-none');
        }
    }

    // 3. Update status active pada node step (approval_policy_step)
    var stepNodes = pane.querySelectorAll('.policy-step-node');
    for (var k = 0; k < stepNodes.length; k++) {
        var nodePolId = parseInt(stepNodes[k].getAttribute('data-policy-id') || '0', 10);
        var nodeStage = parseInt(stepNodes[k].getAttribute('data-stage-order') || '0', 10);
        if (currentApprovalPolicyId === nodePolId && currentApprovalStageOrder > 0 && currentApprovalStageOrder === nodeStage) {
            stepNodes[k].classList.add('active');
        } else {
            stepNodes[k].classList.remove('active');
        }
    }
}

/**
 * Ganti Tab Pane Kategori Approval Policy
 */
function ApprovalInboxSwitchTab(categoryKey, policyId) {
    currentApprovalCategory = categoryKey || 'ALL';
    currentApprovalPolicyId = policyId ? parseInt(policyId, 10) : 0;
    currentApprovalStageOrder = 0;

    syncPolicyPaneVisualState(currentApprovalCategory);

    currentApprovalTaskId = null;
    updateSelectedTaskIndicator(null);
    ApprovalInboxCloseDetail();
    ApprovalInboxRefresh();
}

/**
 * Filter spesifik Approval Policy di dalam Tab Pane kategori aktif
 */
function ApprovalInboxSelectPolicyPill(categoryKey, policyId) {
    currentApprovalCategory = categoryKey || 'ALL';
    currentApprovalPolicyId = policyId ? parseInt(policyId, 10) : 0;
    currentApprovalStageOrder = 0;

    syncPolicyPaneVisualState(currentApprovalCategory);

    currentApprovalTaskId = null;
    updateSelectedTaskIndicator(null);
    ApprovalInboxCloseDetail();
    ApprovalInboxRefresh();
}

/**
 * Filter spesifik Step Stage (approval_policy_step) saat node step diklik
 */
function ApprovalInboxSelectPolicyStep(categoryKey, policyId, stageOrder) {
    currentApprovalCategory = categoryKey || 'ALL';
    var targetPolicyId = policyId ? parseInt(policyId, 10) : 0;
    var targetStage = stageOrder ? parseInt(stageOrder, 10) : 0;

    // Jika mengklik step yang sudah aktif, maka toggle off kembali ke semua stage di policy tersebut
    if (currentApprovalPolicyId === targetPolicyId && currentApprovalStageOrder === targetStage) {
        currentApprovalStageOrder = 0;
    } else {
        currentApprovalPolicyId = targetPolicyId;
        currentApprovalStageOrder = targetStage;
    }

    syncPolicyPaneVisualState(currentApprovalCategory);

    currentApprovalTaskId = null;
    updateSelectedTaskIndicator(null);
    ApprovalInboxCloseDetail();
    ApprovalInboxRefresh();
}

/**
 * Refresh Grid dan pertahankan task terpilih jika masih ada
 */
function ApprovalInboxRefresh() {
    var rowCountLabel = document.getElementById('inboxRowCountLabel');
    if (rowCountLabel) {
        rowCountLabel.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menyinkronkan...';
    }
    ApprovalInbox.clearAndLoad(buildInboxDataUrl());
}

/**
 * Hitung metrik ringkasan antrean approval setelah data grid terisi
 */
function calculateInboxMetrics() {
    var totalRows = ApprovalInbox.getRowsNum();
    var pendingCount = 0;
    var claimedCount = 0;
    var categoryCounts = {};

    for (var i = 0; i < totalRows; i++) {
        var rowId = ApprovalInbox.getRowId(i);
        var status = (ApprovalInbox.cells(rowId, 8).getValue() || '').toUpperCase();
        var reqType = (ApprovalInbox.getUserData(rowId, 'request_type') || ApprovalInbox.cells(rowId, 2).getValue() || '').toUpperCase();

        if (status.indexOf('CLAIMED') !== -1) {
            claimedCount++;
        } else {
            pendingCount++;
        }

        if (reqType) {
            categoryCounts[reqType] = (categoryCounts[reqType] || 0) + 1;
        }
    }

    var elTotal = document.getElementById('metricCountTotal');
    var elPending = document.getElementById('metricCountPending');
    var elClaimed = document.getElementById('metricCountClaimed');
    var rowCountLabel = document.getElementById('inboxRowCountLabel');

    if (elTotal) elTotal.innerText = totalRows;
    if (elPending) elPending.innerText = pendingCount;
    if (elClaimed) elClaimed.innerText = claimedCount;
    if (rowCountLabel) {
        var suffix = (currentApprovalCategory && currentApprovalCategory !== 'ALL')
            ? ' pada kategori ' + currentApprovalCategory
            : ' antrean task';
        rowCountLabel.innerText = 'Total ' + totalRows + suffix;
    }

    // Perbarui badge angka pada tab yang sedang aktif (atau semua tab saat berada di tab ALL)
    if (currentApprovalCategory === 'ALL' && !currentApprovalPolicyId) {
        var badgeAll = document.getElementById('tabBadgeCount_ALL');
        if (badgeAll) badgeAll.innerText = totalRows;

        var allTabButtons = document.querySelectorAll('#approvalPolicyTabNav button[data-category]');
        for (var j = 0; j < allTabButtons.length; j++) {
            var cat = allTabButtons[j].getAttribute('data-category');
            if (!cat || cat === 'ALL') continue;
            var badgeEl = document.getElementById('tabBadgeCount_' + cat);
            if (badgeEl) {
                var cnt = categoryCounts[cat] || 0;
                var color = badgeEl.getAttribute('data-badge-color') || 'primary';
                badgeEl.innerText = cnt;
                badgeEl.className = 'badge rounded-pill px-2 py-1 ' + (cnt > 0 ? ('bg-' + color + ' text-white') : 'bg-light text-muted border');
            }
        }
    } else if (currentApprovalCategory !== 'ALL' && !currentApprovalPolicyId) {
        var activeBadge = document.getElementById('tabBadgeCount_' + currentApprovalCategory);
        if (activeBadge) {
            var activeColor = activeBadge.getAttribute('data-badge-color') || 'primary';
            activeBadge.innerText = totalRows;
            activeBadge.className = 'badge rounded-pill px-2 py-1 ' + (totalRows > 0 ? ('bg-' + activeColor + ' text-white') : 'bg-light text-muted border');
        }
    }
}

/**
 * Mendapatkan Task ID yang sedang aktif/dipilih secara cerdas:
 */
function ApprovalInboxSelectedId(explicitId) {
    if (explicitId) {
        currentApprovalTaskId = explicitId;
        return explicitId;
    }

    var id = ApprovalInbox.getSelectedRowId();
    if (id) {
        currentApprovalTaskId = id;
        return id;
    }

    if (currentApprovalTaskId) {
        return currentApprovalTaskId;
    }

    var detailCard = document.getElementById('ApprovalTaskDetailCard');
    if (detailCard && detailCard.dataset.taskId) {
        currentApprovalTaskId = detailCard.dataset.taskId;
        return currentApprovalTaskId;
    }

    showAppNotification('Pilih Baris', 'Silakan klik salah satu baris task approval pada tabel terlebih dahulu.', 'warning');
    return null;
}

/**
 * Update indikator visual task yang aktif
 */
function updateSelectedTaskIndicator(id) {
    var indicator = document.getElementById('selectedTaskIndicator');
    var metricSelected = document.getElementById('metricSelectedDoc');
    
    updateActionButtonState(id);

    if (!id) {
        if (indicator) {
            indicator.className = 'badge bg-light text-dark border px-3 py-2 label-text-standard shadow-none';
            indicator.innerHTML = '<i class="fa fa-arrow-pointer text-muted me-1"></i> Belum ada task dipilih';
        }
        if (metricSelected) {
            metricSelected.innerText = 'Belum dipilih';
            metricSelected.title = 'Belum dipilih';
        }
        return;
    }

    var reqNo = '#' + id;
    var reqType = '';
    try {
        if (ApprovalInbox.getRowIndex(id) !== -1) {
            reqNo = ApprovalInbox.cells(id, 1).getValue() || reqNo;
            reqType = ApprovalInbox.cells(id, 2).getValue() || '';
        }
    } catch (e) {}

    if (indicator) {
        indicator.className = 'badge bg-primary text-white px-3 py-2 label-text-standard shadow-sm';
        indicator.innerHTML = '<i class="fa fa-circle-check me-1"></i> Task #' + id + ' (' + reqNo + ')';
    }
    if (metricSelected) {
        metricSelected.innerText = reqNo + (reqType ? ' (' + reqType + ')' : '');
        metricSelected.title = reqNo + (reqType ? ' (' + reqType + ')' : '');
    }
}

/**
 * Muat view detail task dengan indikator loading UX
 */
function ApprovalInboxDetail(explicitId, autoScroll) {
    var id = ApprovalInboxSelectedId(explicitId);
    if (id == null) return false;

    updateSelectedTaskIndicator(id);
    
    var container = document.getElementById('ApprovalInboxDetail');
    container.innerHTML = '<div class="card shadow-sm border p-4 text-center my-3"><div class="spinner-border text-primary mx-auto mb-2" role="status"></div><div class="text-muted small">Memuat rincian dokumen &amp; alur persetujuan...</div></div>';

    LoadM('ApprovalInboxDetail', 'hrd-approval-detail?ID=' + encodeURIComponent(id));
    
    if (autoScroll) {
        setTimeout(function() {
            var detailEl = document.getElementById('ApprovalInboxDetail');
            if (detailEl) {
                detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 300);
    }

    return false;
}

/**
 * Tutup view detail task
 */
function ApprovalInboxCloseDetail() {
    document.getElementById('ApprovalInboxDetail').innerHTML = '';
}

/**
 * Eksekusi AJAX Request dengan fetch
 */
function ApprovalInboxPost(url, data, onSuccess, buttonElement) {
    var originalButtonHtml = '';
    if (buttonElement) {
        originalButtonHtml = buttonElement.innerHTML;
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...';
    }

    var body = new URLSearchParams();
    body.append('_token', ApprovalInboxToken);
    Object.keys(data).forEach(function (key) {
        body.append(key, data[key]);
    });

    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-CSRF-TOKEN': ApprovalInboxToken,
            'Accept': 'application/json'
        },
        body: body.toString()
    }).then(function (response) {
        return response.json().then(function (payload) {
            return { ok: response.ok, payload: payload };
        });
    }).then(function (result) {
        if (buttonElement) {
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalButtonHtml;
        }

        if (!result.ok || result.payload.status === 'error') {
            showAppNotification('Gagal', result.payload.message || 'Aksi approval tidak dapat diproses.', 'danger');
            return;
        }

        showAppNotification('Berhasil', result.payload.message || 'Aksi approval berhasil diselesaikan.', 'success');
        ApprovalInboxRefresh();
        if (typeof onSuccess === 'function') onSuccess(result.payload);
    }).catch(function () {
        if (buttonElement) {
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalButtonHtml;
        }
        showAppNotification('Error Jaringan', 'Tidak dapat terhubung ke server. Silakan coba kembali.', 'danger');
    });
}

/**
 * Buka Modal Konfirmasi Claim
 */
function ApprovalInboxClaim(explicitId) {
    var id = ApprovalInboxSelectedId(explicitId);
    if (id == null) return false;

    var reqNo = '#' + id;
    try {
        if (ApprovalInbox.getRowIndex(id) !== -1) {
            reqNo = ApprovalInbox.cells(id, 1).getValue() || reqNo;
        }
    } catch (e) {}

    document.getElementById('modalClaimTaskId').value = id;
    document.getElementById('modalClaimRequestNo').innerText = reqNo;

    if (bsModalClaim) {
        bsModalClaim.show();
    }
    return false;
}

/**
 * Submit Claim dari Modal
 */
function ApprovalInboxSubmitClaim() {
    var taskId = document.getElementById('modalClaimTaskId').value;
    var btn = document.getElementById('modalClaimSubmitBtn');

    if (!taskId) return;

    ApprovalInboxPost('hrd-approval-claim', { Task_ID: taskId }, function () {
        if (bsModalClaim) bsModalClaim.hide();
        ApprovalInboxDetail(taskId);
    }, btn);
}

/**
 * Buka Modal Approve atau Reject
 */
function ApprovalInboxAct(action, explicitId) {
    var id = ApprovalInboxSelectedId(explicitId);
    if (id == null) return false;

    action = (action || 'APPROVE').toUpperCase();

    var reqNo = '-' ;
    var reqType = '-';
    var requester = '-';
    var position = '-';

    try {
        if (ApprovalInbox.getRowIndex(id) !== -1) {
            reqNo = ApprovalInbox.cells(id, 1).getValue() || reqNo;
            reqType = ApprovalInbox.cells(id, 2).getValue() || reqType;
            position = ApprovalInbox.cells(id, 4).getValue() || position;
            requester = ApprovalInbox.cells(id, 9).getValue() || requester;
        }
    } catch (e) {}

    var detailCard = document.getElementById('ApprovalTaskDetailCard');
    if (detailCard && detailCard.dataset.taskId == id) {
        reqNo = detailCard.dataset.requestNo || reqNo;
        reqType = detailCard.dataset.requestType || reqType;
        requester = detailCard.dataset.requester || requester;
        position = detailCard.dataset.position || position;
    }

    // Set Data ke Modal
    document.getElementById('modalActTaskId').value = id;
    document.getElementById('modalActAction').value = action;
    document.getElementById('modalActRequestNo').innerText = reqNo;
    document.getElementById('modalActRequestType').innerText = reqType;
    document.getElementById('modalActRequester').innerText = requester;
    document.getElementById('modalActPosition').innerText = position;

    var notesInput = document.getElementById('modalActNotes');
    notesInput.value = '';
    notesInput.classList.remove('is-invalid');

    var modalLabel = document.getElementById('modalApprovalActLabel');
    var notesLabel = document.getElementById('modalActNotesLabel');
    var notesHelp = document.getElementById('modalActNotesHelp');
    var submitBtn = document.getElementById('modalActSubmitBtn');

    if (action === 'APPROVE') {
        modalLabel.innerHTML = '<i class="fa fa-circle-check text-success me-2"></i>Konfirmasi Persetujuan (Approve)';
        notesLabel.innerHTML = 'Catatan Persetujuan <span class="text-muted fw-normal">(Opsional)</span>:';
        notesHelp.innerHTML = 'Catatan atau instruksi persetujuan jika diperlukan.';
        submitBtn.className = 'btn btn-success btn-sm quick-action-btn text-white shadow-sm';
        submitBtn.innerHTML = '<i class="fa fa-check me-1"></i> Ya, Setujui Permohonan';
    } else {
        modalLabel.innerHTML = '<i class="fa fa-circle-xmark text-danger me-2"></i>Konfirmasi Penolakan (Reject)';
        notesLabel.innerHTML = 'Alasan Penolakan <span class="text-danger">*</span>:';
        notesHelp.innerHTML = 'Harap tuliskan alasan atau kekurangan dokumen secara jelas.';
        submitBtn.className = 'btn btn-danger btn-sm quick-action-btn text-white shadow-sm';
        submitBtn.innerHTML = '<i class="fa fa-xmark me-1"></i> Ya, Tolak Permohonan';
    }

    if (bsModalAct) {
        bsModalAct.show();
    }
    return false;
}

/**
 * Submit Keputusan Approval / Reject dari Modal
 */
function ApprovalInboxSubmitAct() {
    var taskId = document.getElementById('modalActTaskId').value;
    var action = document.getElementById('modalActAction').value;
    var notesInput = document.getElementById('modalActNotes');
    var notes = notesInput.value.trim();
    var btn = document.getElementById('modalActSubmitBtn');

    // Validasi catatan jika reject
    if (action === 'REJECT' && notes === '') {
        notesInput.classList.add('is-invalid');
        notesInput.focus();
        return;
    }
    notesInput.classList.remove('is-invalid');

    ApprovalInboxPost('hrd-approval-act', { Task_ID: taskId, Action: action, Notes: notes }, function () {
        if (bsModalAct) bsModalAct.hide();
        ApprovalInboxCloseDetail();
        currentApprovalTaskId = null;
        updateSelectedTaskIndicator(null);
    }, btn);
}

/**
 * Buka Modal Release Claim
 */
function ApprovalInboxRelease(explicitId) {
    var id = ApprovalInboxSelectedId(explicitId);
    if (id == null) return false;

    document.getElementById('modalReleaseTaskId').value = id;
    var reasonInput = document.getElementById('modalReleaseReason');
    reasonInput.value = '';
    reasonInput.classList.remove('is-invalid');

    if (bsModalRelease) {
        bsModalRelease.show();
    }
    return false;
}

/**
 * Submit Release Claim dari Modal
 */
function ApprovalInboxSubmitRelease() {
    var taskId = document.getElementById('modalReleaseTaskId').value;
    var reasonInput = document.getElementById('modalReleaseReason');
    var reason = reasonInput.value.trim();
    var btn = document.getElementById('modalReleaseSubmitBtn');

    if (reason === '') {
        reasonInput.classList.add('is-invalid');
        reasonInput.focus();
        return;
    }
    reasonInput.classList.remove('is-invalid');

    ApprovalInboxPost('hrd-approval-release', { Task_ID: taskId, Reason: reason }, function () {
        if (bsModalRelease) bsModalRelease.hide();
        ApprovalInboxDetail(taskId);
    }, btn);
}

// Inisialisasi dhtmlXGrid
var ApprovalInbox = new dhtmlXGridObject('ApprovalInboxGrid');
ApprovalInbox.setImagePath("./dhtmlx/imgs/");
ApprovalInbox.setHeader("No,Request No,Type,Policy & Approval Step,Position,Stage,Level,Mode,Task Status,Requester,Location,Submitted");
ApprovalInbox.attachHeader("&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#select_filter,#text_filter,#select_filter,#text_filter");
ApprovalInbox.setInitWidths("45,145,120,240,180,55,55,95,120,140,95,140");
ApprovalInbox.setColAlign("center,center,center,left,left,center,center,center,center,left,center,center");
ApprovalInbox.setColSorting("int,str,str,str,str,int,int,str,str,str,str,str");
ApprovalInbox.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
ApprovalInbox.enableSmartRendering(true, 150);
ApprovalInbox.setSkin("dhx_skyblue");
ApprovalInbox.init();

// Event saat baris di-klik satu kali: simpan task aktif, aktifkan tombol, & tampilkan detail
ApprovalInbox.attachEvent("onRowSelect", function (id, ind) {
    currentApprovalTaskId = id;
    updateSelectedTaskIndicator(id);
    ApprovalInboxDetail(id, false);
});

// Event saat baris di-double-click: buka detail dan otomatis scroll ke bawah
ApprovalInbox.attachEvent("onRowDblClicked", function (id, ind) {
    currentApprovalTaskId = id;
    updateSelectedTaskIndicator(id);
    ApprovalInboxDetail(id, true);
    return true;
});

// Event saat data selesai dimuat: hitung metrik dan pulihkan seleksi jika task masih ada
ApprovalInbox.attachEvent("onXLE", function () {
    calculateInboxMetrics();

    if (currentApprovalTaskId) {
        try {
            if (ApprovalInbox.getRowIndex(currentApprovalTaskId) !== -1) {
                ApprovalInbox.selectRowById(currentApprovalTaskId);
                updateSelectedTaskIndicator(currentApprovalTaskId);
            } else {
                currentApprovalTaskId = null;
                updateSelectedTaskIndicator(null);
            }
        } catch (e) {}
    } else {
        updateSelectedTaskIndicator(null);
    }
});

// Muat data awal
ApprovalInbox.clearAndLoad(buildInboxDataUrl());
</script>
@endsection
