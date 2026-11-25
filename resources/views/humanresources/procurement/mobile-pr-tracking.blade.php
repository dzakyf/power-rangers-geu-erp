<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#064E3B">
    <title>HCGA Mobile - Tracking PR</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --emerald-dark: #064E3B;
            --emerald-primary: #047857;
            --emerald-soft: #ECFDF5;
            --bg-canvas: #F6F8F7;
            --text-dark: #111827;
            --text-muted: #6B7280;
            --border-soft: #E5EBE8;
        }
        body {
            background-color: var(--bg-canvas);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            padding-bottom: 96px;
        }
        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
            padding: 16px 16px 24px;
        }
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .btn-back-soft {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            color: var(--text-dark);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            flex-shrink: 0;
        }
        .hero-card {
            background: linear-gradient(135deg, #064E3B 0%, #059669 100%);
            border-radius: 20px;
            padding: 16px;
            color: #FFFFFF;
            margin-bottom: 14px;
            box-shadow: 0 10px 24px rgba(6, 78, 59, 0.16);
        }
        .hero-badge {
            background: rgba(255, 255, 255, 0.18);
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .hero-search-wrap {
            margin-top: 12px;
            background: #FFFFFF;
            border: 2px solid #A7F3D0;
            border-radius: 14px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .hero-search-wrap i {
            color: var(--emerald-primary);
            font-size: 14px;
        }
        .hero-search-wrap input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 13px;
            color: var(--text-dark);
            background: transparent;
        }

        /* Filter Chips */
        .filter-chips {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
            margin-bottom: 10px;
            scrollbar-width: none;
        }
        .filter-chips::-webkit-scrollbar { display: none; }
        .chip-btn {
            border: 1px solid var(--border-soft);
            background: #FFFFFF;
            color: var(--text-dark);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            cursor: pointer;
        }
        .chip-btn.active {
            background: var(--emerald-dark);
            color: #FFFFFF;
            border-color: var(--emerald-dark);
        }
        .chip-count {
            background: rgba(0, 0, 0, 0.08);
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 11px;
        }
        .chip-btn.active .chip-count {
            background: rgba(255, 255, 255, 0.22);
        }
        .total-info {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 12px;
            font-weight: 600;
        }

        /* Tracking Card */
        .track-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            padding: 14px 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }
        .tag-supplies {
            background: #ECFDF5;
            color: #047857;
            border: 1px solid #A7F3D0;
            font-size: 10.5px;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 8px;
        }
        .status-pill {
            font-size: 10.5px;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 999px;
        }
        .pill-pending  { background: #FEF3C7; color: #B45309; }
        .pill-approved { background: #D1FAE5; color: #047857; }
        .pill-pogr     { background: #DBEAFE; color: #1D4ED8; }
        .pill-rejected { background: #FEE2E2; color: #B91C1C; }

        .pr-title {
            font-size: 14.5px;
            font-weight: 800;
            color: var(--text-dark);
            margin: 8px 0 4px;
        }
        .meta-line {
            font-size: 12px;
            color: #374151;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .meta-line i {
            color: var(--text-muted);
            width: 14px;
            text-align: center;
        }

        /* Progress Bar */
        .progress-wrap {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 9px 11px;
            margin: 10px 0;
        }
        .progress-bar-bg {
            height: 6px;
            background: #E5E7EB;
            border-radius: 999px;
            overflow: hidden;
            margin: 6px 0 8px;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 0.3s ease;
        }
        .milestone-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
        }
        .milestone-row span.done {
            color: var(--emerald-primary);
        }
        .milestone-row span.fail {
            color: #DC2626;
        }

        /* Expandable Timeline */
        .btn-toggle-detail {
            width: 100%;
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
            color: var(--emerald-dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }
        .timeline-panel {
            display: none;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #E5E7EB;
        }
        .timeline-panel.open {
            display: block;
        }
        .tl-list {
            position: relative;
            padding-left: 18px;
            margin: 0;
            list-style: none;
        }
        .tl-list::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 4px;
            bottom: 4px;
            width: 2px;
            background: #E2E8F0;
        }
        .tl-item {
            position: relative;
            padding-bottom: 11px;
            font-size: 11.5px;
        }
        .tl-item:last-child {
            padding-bottom: 0;
        }
        .tl-dot {
            position: absolute;
            left: -18px;
            top: 3px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #FFFFFF;
            box-shadow: 0 0 0 1.5px #CBD5E1;
            background: #94A3B8;
        }
        .tl-dot.done    { background: #059669; box-shadow: 0 0 0 1.5px #059669; }
        .tl-dot.pending { background: #F59E0B; box-shadow: 0 0 0 1.5px #F59E0B; }
        .tl-dot.reject  { background: #DC2626; box-shadow: 0 0 0 1.5px #DC2626; }

        .sub-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 8px 10px;
            margin-top: 8px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    @php
        $totalAll      = $trackingList->count();
        $totalPending  = $trackingList->where('filter_group', 'PENDING')->count();
        $totalApproved = $trackingList->where('filter_group', 'APPROVED')->count();
        $totalPoGr     = $trackingList->where('filter_group', 'PO_GR')->count();
        $totalRejected = $trackingList->where('filter_group', 'REJECTED')->count();
    @endphp

    <div class="mobile-container">
        <div class="top-bar">
            <a href="{{ route('procurement.mobile.index') }}" class="btn-back-soft" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="h6 font-weight-bold mb-0 mx-auto">Tracking PR</h1>
            <div style="width: 38px;"></div>
        </div>

        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <div class="font-weight-bold" style="font-size: 15px;">Lacak Progres Purchase Request</div>
                    <div style="font-size: 12px; opacity: 0.9;">Pantau status PR, approval berjenjang, PO &amp; GR</div>
                </div>
                <span class="hero-badge">
                    <i class="fas fa-route"></i>
                    <span>{{ $totalAll }} PR</span>
                </span>
            </div>

            <div class="hero-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="trackingSearchInput" placeholder="Cari nomor PR, pemohon, dept, PO, item..." oninput="filterTrackingPR()">
            </div>
        </div>

        <div class="filter-chips">
            <button type="button" class="chip-btn active" onclick="setTrackingFilter(this, 'ALL')">
                Semua <span class="chip-count">{{ $totalAll }}</span>
            </button>
            <button type="button" class="chip-btn" onclick="setTrackingFilter(this, 'PENDING')">
                Approval <span class="chip-count">{{ $totalPending }}</span>
            </button>
            <button type="button" class="chip-btn" onclick="setTrackingFilter(this, 'APPROVED')">
                Approved <span class="chip-count">{{ $totalApproved }}</span>
            </button>
            <button type="button" class="chip-btn" onclick="setTrackingFilter(this, 'PO_GR')">
                PO / GR <span class="chip-count">{{ $totalPoGr }}</span>
            </button>
            <button type="button" class="chip-btn" onclick="setTrackingFilter(this, 'REJECTED')">
                Rejected <span class="chip-count">{{ $totalRejected }}</span>
            </button>
        </div>

        <div class="total-info" id="trackingVisibleCount">Menampilkan {{ $totalAll }} Purchase Request</div>

        <div id="trackingCardList">
            @forelse ($trackingList as $item)
                @php
                    switch ($item->filter_group) {
                        case 'APPROVED':
                            $pillClass = 'pill-approved';
                            break;
                        case 'PO_GR':
                            $pillClass = 'pill-pogr';
                            break;
                        case 'REJECTED':
                            $pillClass = 'pill-rejected';
                            break;
                        default:
                            $pillClass = 'pill-pending';
                            break;
                    }
                    $barColor = ($item->filter_group === 'REJECTED') ? '#DC2626' : '#059669';

                    $itemNames = collect($item->items)->pluck('Product_Desc')->implode(' ');
                    $poNos     = collect($item->pos)->pluck('PO_No')->implode(' ');
                    $grNos     = collect($item->grs)->pluck('GR_No')->implode(' ');
                    $searchHaystack = strtolower(
                        $item->pr_no . ' ' .
                        $item->requester . ' ' .
                        $item->department . ' ' .
                        $item->policy . ' ' .
                        $item->stage_label . ' ' .
                        $itemNames . ' ' . $poNos . ' ' . $grNos
                    );
                @endphp

                <div class="track-card tracking-pr-item"
                     data-group="{{ $item->filter_group }}"
                     data-search="{{ $searchHaystack }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="tag-supplies">{{ $item->supplies_type }} &bull; {{ $item->category }}</span>
                        <span class="status-pill {{ $pillClass }}">{{ $item->status }}</span>
                    </div>

                    <div class="pr-title">{{ $item->pr_no }}</div>

                    <div class="meta-line">
                        <i class="fas fa-user"></i>
                        <span><strong>{{ strtoupper($item->requester) }}</strong> ({{ $item->department }}) &bull; {{ $item->pr_date_label }}</span>
                    </div>
                    <div class="meta-line">
                        <i class="fas fa-code-branch"></i>
                        <span>Policy: <strong>{{ $item->policy }}</strong></span>
                    </div>
                    <div class="meta-line">
                        <i class="fas fa-coins"></i>
                        <span>Estimasi: <strong class="text-success">Rp {{ number_format($item->estimated_budget, 0, ',', '.') }}</strong></span>
                    </div>

                    <div class="progress-wrap">
                        <div class="d-flex justify-content-between align-items-center" style="font-size: 11px;">
                            <span class="font-weight-bold text-dark">{{ $item->stage_label }}</span>
                            <span class="text-muted font-weight-bold">{{ $item->progress_pct }}%</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: {{ $item->progress_pct }}%; background: {{ $barColor }};"></div>
                        </div>
                        <div class="milestone-row">
                            <span class="done"><i class="fas fa-check-circle"></i> PR</span>
                            <span class="{{ in_array($item->filter_group, ['APPROVED', 'PO_GR']) ? 'done' : ($item->filter_group === 'REJECTED' ? 'fail' : '') }}">
                                <i class="fas {{ $item->filter_group === 'REJECTED' ? 'fa-times-circle' : 'fa-user-check' }}"></i> Approval
                            </span>
                            <span class="{{ !empty($item->pos) || $item->filter_group === 'PO_GR' ? 'done' : '' }}">
                                <i class="fas fa-file-contract"></i> PO
                            </span>
                            <span class="{{ !empty($item->grs) ? 'done' : '' }}">
                                <i class="fas fa-box-open"></i> GR
                            </span>
                        </div>
                    </div>

                    <button type="button" class="btn-toggle-detail" onclick="toggleTimelinePanel('panel-{{ $item->pr_id }}', this)">
                        <span><i class="fas fa-stream mr-1"></i> Lihat Detail Jejak &amp; Item ({{ count($item->items) }} Item)</span>
                        <i class="fas fa-chevron-down toggle-chevron"></i>
                    </button>

                    <div class="timeline-panel" id="panel-{{ $item->pr_id }}">
                        <ul class="tl-list">
                            <!-- 1. PR Dibuat -->
                            <li class="tl-item">
                                <span class="tl-dot done"></span>
                                <div class="font-weight-bold text-dark">Purchase Request Dibuat</div>
                                <div class="text-muted" style="font-size: 10.5px;">
                                    Oleh {{ $item->requester }} ({{ $item->department }}) &bull; {{ $item->pr_date_label }}
                                    @if ($item->hope_date_label !== '-')
                                        &bull; Target: {{ $item->hope_date_label }}
                                    @endif
                                </div>
                                @if (!empty($item->note))
                                    <div class="text-secondary mt-1" style="font-size: 10.5px;">"{{ $item->note }}"</div>
                                @endif
                            </li>

                            <!-- 2. Tahapan Approval -->
                            @forelse ($item->stages as $st)
                                @php
                                    $stUp = strtoupper($st->status);
                                    $dotCls = in_array($stUp, ['APPROVED', 'AUTO_APPROVED'])
                                        ? 'done'
                                        : (in_array($stUp, ['REJECTED', 'CANCELLED']) ? 'reject' : 'pending');
                                @endphp
                                <li class="tl-item">
                                    <span class="tl-dot {{ $dotCls }}"></span>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><strong>Approval Stage {{ $st->stage }}</strong> &bull; {{ $st->position }}</span>
                                        <span class="badge badge-light border" style="font-size: 9.5px;">{{ $stUp }}</span>
                                    </div>
                                    @if (!empty($st->actor) || !empty($st->acted_at))
                                        <div class="text-muted" style="font-size: 10.5px;">
                                            {{ $st->actor ?: '-' }}{{ !empty($st->acted_at) ? ' • ' . $st->acted_at : '' }}
                                        </div>
                                    @endif
                                    @if (!empty($st->notes) && $stUp !== 'PENDING')
                                        <div class="text-secondary mt-1" style="font-size: 10.5px;">"{{ $st->notes }}"</div>
                                    @endif
                                </li>
                            @empty
                                <li class="tl-item">
                                    <span class="tl-dot {{ $item->filter_group === 'REJECTED' ? 'reject' : ($item->filter_group !== 'PENDING' ? 'done' : 'pending') }}"></span>
                                    <div class="font-weight-bold">Status Approval: {{ $item->status }}</div>
                                </li>
                            @endforelse

                            <!-- 3. RFQ -->
                            @if (!empty($item->rfqs))
                                <li class="tl-item">
                                    <span class="tl-dot done"></span>
                                    <div class="font-weight-bold text-dark">RFQ Vendor ({{ count($item->rfqs) }} Penawaran)</div>
                                    @foreach ($item->rfqs as $rfq)
                                        <div class="text-muted" style="font-size: 10.5px;">
                                            &bull; {{ $rfq->RFQ_No }} ({{ $rfq->Company_Name ?? $rfq->Supplier_ID }}) — <strong>{{ $rfq->Status }}</strong>
                                        </div>
                                    @endforeach
                                </li>
                            @endif

                            <!-- 4. Purchase Order -->
                            @if (!empty($item->pos))
                                <li class="tl-item">
                                    <span class="tl-dot done"></span>
                                    <div class="font-weight-bold text-dark">Purchase Order ({{ count($item->pos) }} PO)</div>
                                    @foreach ($item->pos as $po)
                                        <div class="text-muted" style="font-size: 10.5px;">
                                            &bull; <strong>{{ $po->PO_No }}</strong> ({{ $po->Company_Name ?? $po->Supplier }}) &bull; Rp {{ number_format($po->Total ?? 0, 0, ',', '.') }} [{{ $po->Status }}]
                                        </div>
                                    @endforeach
                                </li>
                            @endif

                            <!-- 5. Goods Received -->
                            @if (!empty($item->grs))
                                <li class="tl-item">
                                    <span class="tl-dot done"></span>
                                    <div class="font-weight-bold text-success">Goods Received ({{ count($item->grs) }} GR)</div>
                                    @foreach ($item->grs as $gr)
                                        <div class="text-muted" style="font-size: 10.5px;">
                                            &bull; <strong>{{ $gr->GR_No }}</strong> ({{ $gr->Incoming_Date ?? '-' }}) &bull; Status: {{ $gr->Status }}
                                        </div>
                                    @endforeach
                                </li>
                            @endif
                        </ul>

                        <!-- Rincian Item PR -->
                        @if (!empty($item->items))
                            <div class="sub-box">
                                <div class="font-weight-bold text-dark mb-1"><i class="fas fa-boxes mr-1 text-success"></i> Rincian Item PR:</div>
                                @foreach ($item->items as $it)
                                    <div class="text-secondary" style="font-size: 10.5px;">
                                        &bull; {{ $it->Product_Desc }} ({{ $it->Qty }} {{ $it->Unit }})
                                        @if (!empty($it->Default_Price))
                                            — Rp {{ number_format($it->Default_Price * $it->Qty, 0, ',', '.') }}
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="track-card text-center text-muted small py-4">
                    Belum ada data Purchase Request untuk dilacak.
                </div>
            @endforelse
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'home'])

    <script>
        var activeTrackGroup = 'ALL';

        function setTrackingFilter(btnEl, groupKey) {
            activeTrackGroup = groupKey;
            document.querySelectorAll('.chip-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            btnEl.classList.add('active');
            filterTrackingPR();
        }

        function filterTrackingPR() {
            var q = (document.getElementById('trackingSearchInput')?.value || '').toLowerCase().trim();
            var shown = 0;

            document.querySelectorAll('.tracking-pr-item').forEach(function(card) {
                var grp = card.getAttribute('data-group') || 'PENDING';
                var hay = card.getAttribute('data-search') || '';
                var matchGrp = (activeTrackGroup === 'ALL') || (grp === activeTrackGroup);
                var matchTxt = (q === '') || (hay.indexOf(q) !== -1);
                var ok = matchGrp && matchTxt;

                card.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });

            var info = document.getElementById('trackingVisibleCount');
            if (info) info.textContent = 'Menampilkan ' + shown + ' Purchase Request';
        }

        function toggleTimelinePanel(panelId, btnEl) {
            var panel = document.getElementById(panelId);
            if (!panel) return;
            var isOpen = panel.classList.toggle('open');
            var icon = btnEl.querySelector('.toggle-chevron');
            if (icon) {
                icon.className = isOpen ? 'fas fa-chevron-up toggle-chevron' : 'fas fa-chevron-down toggle-chevron';
            }
        }
    </script>
</body>
</html>