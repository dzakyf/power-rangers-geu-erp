<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mobile Approval Inbox - HCGA ERP</title>

    <!-- PWA & Mobile Meta -->
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Approval HCGA">
    <link rel="apple-touch-icon" href="{{ asset('adminor/assets/img/brand/favicon.png') }}">

    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 80px;
            color: #1e293b;
        }
        .header-mobile-appr {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
            padding: 20px 18px 22px;
            border-bottom-left-radius: 22px;
            border-bottom-right-radius: 22px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2);
        }
        .task-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            transition: all 0.15s ease;
        }
        .task-card:active {
            transform: scale(0.985);
        }
        .search-pill-input {
            position: relative;
            margin-top: 12px;
        }
        .search-pill-input i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        .search-pill-input input {
            padding-left: 38px;
            border-radius: 25px;
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-size: 13px;
        }
        .search-pill-input input::placeholder {
            color: rgba(255,255,255,0.7);
        }
        .search-pill-input input:focus {
            background: #fff;
            color: #1e293b;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
        }
        .category-scroll-bar {
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 8px 16px;
            gap: 8px;
            display: flex;
        }
        .category-scroll-bar::-webkit-scrollbar {
            display: none;
        }
        .cat-chip {
            border-radius: 50rem;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .cat-chip.active {
            background-color: #1e3a8a;
            color: #fff !important;
            box-shadow: 0 2px 6px rgba(30, 58, 138, 0.3);
        }
        .cat-chip:not(.active) {
            background-color: #fff;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        /* Mobile Policy & Step Filter Container */
        .policy-filter-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 12px 14px;
            margin: 6px 16px 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        }
        .policy-sub-chip {
            cursor: pointer;
            border-radius: 50rem;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            text-decoration: none;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            user-select: none;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            white-space: nowrap;
        }
        .policy-sub-chip.active {
            background: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
        }
        .flow-step-scroll {
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: thin;
            padding: 4px 0 2px;
            gap: 6px;
            display: flex;
            align-items: center;
        }
        .mobile-step-node {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #1e293b;
            font-size: 11px;
            font-weight: 500;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.15s ease;
            cursor: pointer;
            user-select: none;
        }
        .mobile-step-node.active {
            background: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
            box-shadow: 0 2px 5px rgba(37, 99, 235, 0.25);
        }
        .mobile-step-node.active .m-step-badge {
            background: #ffffff !important;
            color: #2563eb !important;
        }
        .m-step-badge {
            font-size: 9px;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 4px;
            background: #2563eb;
            color: #ffffff;
        }
        .m-step-arrow {
            color: #94a3b8;
            font-size: 9px;
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            height: 65px;
            box-shadow: 0 -3px 12px rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1040;
        }
        .nav-item-mobile {
            text-align: center;
            color: #94a3b8;
            text-decoration: none;
            font-size: 11px;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-weight: 500;
        }
        .nav-item-mobile.active {
            color: #1e3a8a;
            font-weight: 700;
        }
        .nav-item-mobile i {
            font-size: 20px;
            margin-bottom: 2px;
        }
        .btn-action-touch {
            border-radius: 10px;
            font-weight: 600;
            font-size: 12px;
            padding: 7px 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
    </style>
</head>
<body>

    <!-- Header Mobile Approval -->
    <div class="header-mobile-appr">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="d-flex align-items-center gap-2">
                <span class="avatar avatar-sm bg-white bg-opacity-20 text-white rounded-circle p-2">
                    <i class="fa fa-stamp fs-16"></i>
                </span>
                <div>
                    <h5 class="mb-0 fw-bold text-white" style="letter-spacing: 0.3px;">Mobile Approval</h5>
                    <small class="text-white-50" style="font-size: 11px;">Antrean Persetujuan Cepat</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold shadow-sm" style="font-size: 11px;">
                    <i class="fa fa-layer-group text-primary me-1"></i> {{ $totalAllTasks }} Task
                </span>
                <a href="{{ url('hrd-approval-inbox') }}" class="btn btn-sm btn-outline-light rounded-circle px-2 py-1" title="Buka Versi Desktop Penuh">
                    <i class="fa fa-desktop"></i>
                </a>
            </div>
        </div>

        <!-- Search Input -->
        <form action="{{ url('hrd-approval-mobile') }}" method="GET" class="search-pill-input">
            @if($selectedCategory !== 'ALL')
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
            @endif
            @if($selectedPolicyId > 0)
                <input type="hidden" name="policy_id" value="{{ $selectedPolicyId }}">
            @endif
            @if($selectedStage > 0)
                <input type="hidden" name="stage_order" value="{{ $selectedStage }}">
            @endif
            <i class="fa fa-search"></i>
            <input type="text" name="q" class="form-control" placeholder="Cari No. Request, Pemohon, atau Deskripsi..." value="{{ $search }}">
        </form>
    </div>

    <!-- Category Chips Filter Horizontal Scroll -->
    <div class="category-scroll-bar mt-2">
        <a href="{{ url('hrd-approval-mobile?category=ALL' . ($search ? '&q=' . urlencode($search) : '')) }}" 
           class="cat-chip {{ $selectedCategory === 'ALL' ? 'active' : '' }}">
            <i class="fa fa-inbox"></i>
            <span>Semua</span>
            <span class="badge rounded-pill {{ $selectedCategory === 'ALL' ? 'bg-white text-primary' : 'bg-light text-muted border' }}">{{ $totalAllTasks }}</span>
        </a>

        @php
            $categoryMeta = [
                'REQUISITION' => ['label' => 'PTK', 'icon' => 'fa-user-plus'],
                'STATUS_CHANGE' => ['label' => 'Status Karyawan', 'icon' => 'fa-user-pen'],
                'PROCUREMENT' => ['label' => 'Procurement', 'icon' => 'fa-cart-shopping'],
                'GA_WORK_ORDER' => ['label' => 'GA Work Order', 'icon' => 'fa-screwdriver-wrench'],
                'GENERAL' => ['label' => 'General', 'icon' => 'fa-file-signature'],
            ];
        @endphp

        @foreach($policyTabs as $tab)
            @php
                $catKey = $tab['key'];
                $meta = $categoryMeta[$catKey] ?? [
                    'label' => $tab['label'],
                    'icon' => $tab['icon']
                ];
                $isAct = ($selectedCategory === $catKey);
                $count = $categoryCounts[$catKey] ?? $tab['task_count'] ?? 0;
            @endphp
            <a href="{{ url('hrd-approval-mobile?category=' . $catKey . ($search ? '&q=' . urlencode($search) : '')) }}" 
               class="cat-chip {{ $isAct ? 'active' : '' }}">
                <i class="fa {{ $meta['icon'] }}"></i>
                <span>{{ $meta['label'] }}</span>
                <span class="badge rounded-pill {{ $isAct ? 'bg-white text-primary' : 'bg-light text-muted border' }}">{{ $count }}</span>
            </a>
        @endforeach
    </div>

    <!-- Approval Flow Pipeline Box (Khusus Kategori Aktif) -->
    @if($activeTabInfo && !empty($activeTabInfo['policies']))
    <div class="policy-filter-card">
        <!-- Sub-filter Policy Chips -->
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="fw-bold small text-dark d-flex align-items-center">
                <i class="fa fa-route text-primary me-2"></i>Alur Approval Flow ({{ $activeTabInfo['label'] }})
            </span>
            @if($selectedPolicyId > 0 || $selectedStage > 0)
                <a href="{{ url('hrd-approval-mobile?category=' . $selectedCategory . ($search ? '&q=' . urlencode($search) : '')) }}" 
                   class="small text-danger text-decoration-none fw-semibold">
                    <i class="fa fa-rotate-left me-1"></i>Reset Filter
                </a>
            @endif
        </div>

        <!-- Policy Selector Pills -->
        <div class="d-flex flex-wrap gap-1 mb-2">
            <a href="{{ url('hrd-approval-mobile?category=' . $selectedCategory . ($search ? '&q=' . urlencode($search) : '')) }}"
               class="policy-sub-chip {{ $selectedPolicyId === 0 ? 'active' : '' }}">
                Semua Policy
            </a>
            @foreach($activeTabInfo['policies'] as $pol)
                <a href="{{ url('hrd-approval-mobile?category=' . $selectedCategory . '&policy_id=' . $pol['id'] . ($search ? '&q=' . urlencode($search) : '')) }}"
                   class="policy-sub-chip {{ $selectedPolicyId === $pol['id'] ? 'active' : '' }}">
                    <i class="fa fa-code-branch text-muted me-1"></i>{{ $pol['code'] }}
                    <span class="opacity-75">({{ count($pol['steps']) }} Step)</span>
                </a>
            @endforeach
        </div>

        <!-- Visual Step Pipeline Nodes -->
        <div class="d-flex flex-column gap-2 mt-1 pt-2 border-top">
            @foreach($activeTabInfo['policies'] as $pol)
                @if($selectedPolicyId === 0 || $selectedPolicyId === $pol['id'])
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-bold text-secondary" style="font-size: 11px;">
                            <i class="fa fa-sliders text-muted me-1"></i>{{ $pol['code'] }} &mdash; <span class="fw-normal">{{ $pol['name'] }}</span>
                        </small>
                    </div>

                    <div class="flow-step-scroll">
                        @forelse($pol['steps'] as $idx => $step)
                            @if($idx > 0)
                                <i class="fa fa-chevron-right m-step-arrow"></i>
                            @endif
                            @php
                                $isStepActive = ($selectedPolicyId === $pol['id'] && $selectedStage === $step['stage']);
                                $stepUrl = $isStepActive 
                                    ? url('hrd-approval-mobile?category=' . $selectedCategory . '&policy_id=' . $pol['id'] . ($search ? '&q=' . urlencode($search) : ''))
                                    : url('hrd-approval-mobile?category=' . $selectedCategory . '&policy_id=' . $pol['id'] . '&stage_order=' . $step['stage'] . ($search ? '&q=' . urlencode($search) : ''));
                            @endphp
                            <a href="{{ $stepUrl }}" class="mobile-step-node {{ $isStepActive ? 'active' : '' }}" title="{{ $step['label'] }}">
                                <span class="m-step-badge">S{{ $step['stage'] }}</span>
                                <span>{{ $step['label'] }}</span>
                                <small class="opacity-75" style="font-size: 9px;">(Lv.{{ $step['level'] }})</small>
                            </a>
                        @empty
                            <small class="text-muted fst-italic" style="font-size: 11px;">Belum ada step approval.</small>
                        @endforelse
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <!-- Container Antrean Task List -->
    <div class="container px-3 mt-2">
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <span class="small text-muted" style="font-size: 12px;">
                @if($selectedStage > 0)
                    Menampilkan Stage <strong>{{ $selectedStage }}</strong> &bull;
                @endif
                Total: <strong>{{ $tasks->total() }}</strong> Task Menunggu
            </span>
            @if($search)
                <span class="badge bg-light text-muted border" style="font-size: 10px;">Filter: "{{ $search }}"</span>
            @endif
        </div>

        @forelse($tasks as $t)
            @php
                $isClaimed = (strtoupper((string) $t->Task_Status) === 'CLAIMED');
                $isMyClaim = ($isClaimed && (int) $t->Claimed_By === $userId);
            @endphp
            <div class="task-card">
                <!-- Top Info Bar -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 fw-bold" style="font-size: 10px;">
                            {{ $t->Request_Type }}
                        </span>
                        <h6 class="fw-bold text-dark mt-1 mb-0" style="font-size: 14px;">
                            {{ $t->Request_No }}
                        </h6>
                    </div>
                    <div>
                        @if($isMyClaim)
                            <span class="badge bg-primary text-white rounded-pill px-2 py-1" style="font-size: 10px;">
                                <i class="fa fa-user-check me-1"></i> CLAIMED BY YOU
                            </span>
                        @elseif($isClaimed)
                            <span class="badge bg-secondary text-white rounded-pill px-2 py-1" style="font-size: 10px;">
                                <i class="fa fa-lock me-1"></i> CLAIMED
                            </span>
                        @else
                            <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 10px;">
                                <i class="fa fa-clock me-1"></i> PENDING
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Pemohon & Posisi -->
                <div class="d-flex flex-column gap-1 small text-secondary mb-2" style="font-size: 12px;">
                    <div>
                        <i class="fa fa-user text-muted me-1" style="width: 14px;"></i>
                        <span class="text-dark fw-semibold">{{ $t->Requester_Name }}</span>
                        @if($t->Department)
                            <span class="text-muted">({{ $t->Department }})</span>
                        @endif
                    </div>
                    <div>
                        <i class="fa fa-sitemap text-muted me-1" style="width: 14px;"></i>
                        <span>Posisi: <strong>{{ $t->Position_Name ?: ($t->Position_Code ?: 'Tahap Approval') }}</strong> (Stage {{ $t->Stage_Order }})</span>
                    </div>
                    @if($t->Policy_Code)
                    <div>
                        <i class="fa fa-code-branch text-muted me-1" style="width: 14px;"></i>
                        <span class="text-muted">Policy: {{ $t->Policy_Code }} {{ $t->Step_Label ? '&bull; ' . $t->Step_Label : '' }}</span>
                    </div>
                    @endif
                </div>

                <!-- Description / Amount if available -->
                @if($t->Description || $t->Amount > 0)
                <div class="bg-light p-2 rounded-3 border mb-2 small text-secondary" style="font-size: 11px;">
                    @if($t->Amount > 0)
                        <div class="fw-bold text-primary mb-1">
                            Nominal: Rp {{ number_format((float) $t->Amount, 0, ',', '.') }}
                        </div>
                    @endif
                    @if($t->Description)
                        <div class="text-truncate">{{ $t->Description }}</div>
                    @endif
                </div>
                @endif

                <!-- Footer Time & Action Buttons -->
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="text-muted" style="font-size: 10px;">
                        <i class="fa fa-calendar-alt me-1"></i>
                        {{ $t->Submitted_At ? date('d M Y H:i', strtotime($t->Submitted_At)) : '-' }}
                    </span>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-action-touch" onclick="bukaDetailMobile({{ $t->ID }});">
                            <i class="fa fa-eye"></i> Detail
                        </button>
                        <button type="button" class="btn btn-success text-white btn-action-touch" 
                                onclick="bukaModalActMobile({{ $t->ID }}, '{{ $t->Request_No }}', '{{ $t->Request_Type }}', '{{ $t->Requester_Name }}', 'APPROVE');">
                            <i class="fa fa-check"></i> Setujui
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-action-touch" 
                                onclick="bukaModalActMobile({{ $t->ID }}, '{{ $t->Request_No }}', '{{ $t->Request_Type }}', '{{ $t->Requester_Name }}', 'REJECT');">
                            <i class="fa fa-xmark"></i> Tolak
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 bg-white rounded-4 border shadow-sm mt-3">
                <i class="fa fa-check-circle fa-3x text-success mb-3 opacity-50"></i>
                <h6 class="fw-bold text-dark">Tidak Ada Antrean Approval</h6>
                <p class="text-muted small px-4 mb-2">Semua tugas persetujuan pada filter ini telah selesai diproses.</p>
                <a href="{{ url('hrd-approval-mobile') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fa fa-rotate me-1"></i> Segarkan Halaman
                </a>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $tasks->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Modal Detail Bottom Sheet Mobile -->
    <div class="modal fade" id="modalDetailMobile" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header py-3 border-bottom">
                    <h6 class="modal-title fw-bold text-primary mb-0">
                        <i class="fa fa-file-lines me-2"></i>Detail Dokumen Approval
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3" id="modalDetailMobileBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="small text-muted mt-2">Memuat detail dokumen...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Keputusan Approval Mobile (Approve / Reject) -->
    <div class="modal fade" id="modalActMobile" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header py-3 border-bottom" id="modalActMobileHeader">
                    <h6 class="modal-title fw-bold mb-0" id="modalActMobileTitle">Konfirmasi</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <input type="hidden" id="mobileActTaskId" />
                    <input type="hidden" id="mobileActAction" />

                    <div class="bg-light p-3 rounded-3 border mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">No. Request:</span>
                            <strong class="text-dark" id="mobileActRequestNo">-</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Tipe Request:</span>
                            <span class="badge bg-white text-dark border" id="mobileActRequestType">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Pemohon:</span>
                            <span class="fw-semibold text-dark" id="mobileActRequester">-</span>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark" id="mobileActNotesLabel">
                            Catatan Persetujuan:
                        </label>
                        <textarea class="form-control form-control-sm" id="mobileActNotes" rows="3" placeholder="Tuliskan catatan atau alasan..."></textarea>
                        <div class="invalid-feedback" id="mobileActNotesFeedback">
                            Alasan wajib diisi saat menolak permohonan.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm rounded-pill px-4 text-white shadow-sm" id="mobileActSubmitBtn" onclick="submitActMobile();">
                        Proses
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation Bar Mobile -->
    <div class="bottom-nav">
        <a href="{{ route('announcement.mobile.index') }}" class="nav-item-mobile">
            <i class="fa fa-newspaper"></i>
            <span>Berita</span>
        </a>
        <a href="{{ url('hrd-approval-mobile') }}" class="nav-item-mobile active">
            <i class="fa fa-stamp text-primary"></i>
            <span>Approval</span>
        </a>
        <a href="{{ route('attendance.mobile.index') }}" class="nav-item-mobile">
            <i class="fa fa-fingerprint"></i>
            <span>Absensi</span>
        </a>
        <a href="{{ route('employee.mobile.my-profile') }}" class="nav-item-mobile">
            <i class="fa fa-id-card"></i>
            <span>Profil</span>
        </a>
        <a href="{{ url('hrd-approval-inbox') }}" class="nav-item-mobile">
            <i class="fa fa-desktop"></i>
            <span>Desktop</span>
        </a>
    </div>

    <!-- Script Handling -->
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        var bsDetailModal = null;
        var bsActModal = null;
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.addEventListener('DOMContentLoaded', function () {
            bsDetailModal = new bootstrap.Modal(document.getElementById('modalDetailMobile'));
            bsActModal = new bootstrap.Modal(document.getElementById('modalActMobile'));
        });

        function bukaDetailMobile(taskId) {
            var body = document.getElementById('modalDetailMobileBody');
            body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="small text-muted mt-2">Memuat rincian dokumen...</div></div>';
            bsDetailModal.show();

            fetch('{{ url("hrd-approval-detail") }}?ID=' + encodeURIComponent(taskId), {
                headers: { 'Accept': 'text/html' }
            })
            .then(function(res) { return res.text(); })
            .then(function(html) {
                body.innerHTML = html;
            })
            .catch(function(err) {
                body.innerHTML = '<div class="alert alert-danger mb-0 small">Gagal memuat detail task permohonan.</div>';
            });
        }

        function bukaModalActMobile(taskId, reqNo, reqType, requester, action) {
            document.getElementById('mobileActTaskId').value = taskId;
            document.getElementById('mobileActAction').value = action;
            document.getElementById('mobileActRequestNo').innerText = reqNo;
            document.getElementById('mobileActRequestType').innerText = reqType;
            document.getElementById('mobileActRequester').innerText = requester;

            var notesInput = document.getElementById('mobileActNotes');
            notesInput.value = '';
            notesInput.classList.remove('is-invalid');

            var title = document.getElementById('modalActMobileTitle');
            var notesLabel = document.getElementById('mobileActNotesLabel');
            var btn = document.getElementById('mobileActSubmitBtn');

            if (action === 'APPROVE') {
                title.innerHTML = '<i class="fa fa-circle-check text-success me-2"></i>Setujui Permohonan';
                notesLabel.innerHTML = 'Catatan Persetujuan <span class="text-muted fw-normal">(Opsional)</span>:';
                btn.className = 'btn btn-success btn-sm rounded-pill px-4 text-white shadow-sm';
                btn.innerHTML = '<i class="fa fa-check me-1"></i> Ya, Setujui';
            } else {
                title.innerHTML = '<i class="fa fa-circle-xmark text-danger me-2"></i>Tolak Permohonan';
                notesLabel.innerHTML = 'Alasan Penolakan <span class="text-danger">*</span>:';
                btn.className = 'btn btn-danger btn-sm rounded-pill px-4 text-white shadow-sm';
                btn.innerHTML = '<i class="fa fa-xmark me-1"></i> Ya, Tolak';
            }

            bsActModal.show();
        }

        function submitActMobile() {
            var taskId = document.getElementById('mobileActTaskId').value;
            var action = document.getElementById('mobileActAction').value;
            var notesInput = document.getElementById('mobileActNotes');
            var notes = notesInput.value.trim();
            var btn = document.getElementById('mobileActSubmitBtn');

            if (action === 'REJECT' && notes === '') {
                notesInput.classList.add('is-invalid');
                notesInput.focus();
                return;
            }
            notesInput.classList.remove('is-invalid');

            var origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

            var formData = new URLSearchParams();
            formData.append('_token', csrfToken);
            formData.append('Task_ID', taskId);
            formData.append('Action', action);
            formData.append('Notes', notes);

            fetch('{{ url("hrd-approval-act") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData.toString()
            })
            .then(function(res) {
                return res.json().then(function(data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function(result) {
                btn.disabled = false;
                btn.innerHTML = origHtml;

                if (!result.ok || result.data.status === 'error') {
                    alert('Gagal: ' + (result.data.message || 'Terjadi kesalahan sistem'));
                    return;
                }

                bsActModal.hide();
                alert(result.data.message || 'Berhasil diproses!');
                location.reload();
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                alert('Terjadi kesalahan jaringan. Silakan coba kembali.');
            });
        }
    </script>
</body>
</html>
