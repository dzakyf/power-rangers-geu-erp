<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>HCGA Mobile - Approval PR</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .hero-queue-card {
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

        /* PR Approval Card */
        .approval-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 18px;
            padding: 14px 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }
        .tag-proc {
            background: #ECFDF5;
            color: #047857;
            border: 1px solid #A7F3D0;
            font-size: 10.5px;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 8px;
            letter-spacing: 0.3px;
        }
        .tag-status-pending {
            background: #FEF3C7;
            color: #B45309;
            font-size: 10.5px;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 999px;
        }
        .tag-status-returned {
            background: #FEE2E2;
            color: #B91C1C;
            font-size: 10.5px;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 999px;
        }
        .pr-number-title {
            font-size: 14.5px;
            font-weight: 800;
            color: var(--text-dark);
            margin: 8px 0 6px;
        }
        .meta-line {
            font-size: 12px;
            color: #374151;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .meta-line i {
            color: var(--text-muted);
            width: 14px;
            text-align: center;
        }
        .budget-box {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 9px 12px;
            margin: 10px 0;
        }
        .budget-amount {
            font-size: 13px;
            font-weight: 800;
            color: var(--emerald-dark);
        }
        .budget-desc {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 2px;
            word-break: break-word;
        }
        .card-footer-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-top: 8px;
            border-top: 1px solid #F3F4F6;
        }
        .btn-action-sm {
            border-radius: 10px;
            padding: 6px 11px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
        }
        .btn-detail-outline {
            background: #FFFFFF;
            border-color: #D1D5DB;
            color: #111827;
        }
        .btn-approve-solid {
            background: #059669;
            color: #FFFFFF;
        }
        .btn-reject-outline {
            background: #FFFFFF;
            border-color: #FCA5A5;
            color: #DC2626;
        }

        .stage-history-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 9px 11px;
            margin-bottom: 10px;
        }
        .stage-history-title {
            font-size: 11px;
            font-weight: 800;
            color: var(--emerald-dark);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stage-step-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            padding: 5px 0;
            border-top: 1px dashed #E2E8F0;
            font-size: 11px;
        }
        .stage-step-row:first-of-type {
            border-top: none;
            padding-top: 0;
        }
        .stage-badge {
            font-size: 9.5px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .st-approved { background: #D1FAE5; color: #047857; }
        .st-pending  { background: #FEF3C7; color: #B45309; }
        .st-rejected { background: #FEE2E2; color: #B91C1C; }
        .st-waiting  { background: #E5E7EB; color: #4B5563; }
    </style>
</head>
<body>
    @php
        $totalCount = $prApprovals->count();
        $opsCount = $prApprovals->filter(fn($item) => str_contains(strtoupper($item->supplies_type), 'OPS') && !str_contains(strtoupper($item->supplies_type), 'NON'))->count();
        $nonOpsCount = $prApprovals->filter(fn($item) => str_contains(strtoupper($item->supplies_type), 'NON'))->count();
        $stageMap = $stageHistories ?? [];
    @endphp

    <div class="mobile-container">
        <div class="top-bar">
            <a href="{{ route('procurement.mobile.index') }}" class="btn-back-soft" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="h6 font-weight-bold mb-0 mx-auto">Approval PR</h1>
            <div style="width: 38px;"></div>
        </div>

        <div class="hero-queue-card">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <div class="font-weight-bold" style="font-size: 15px;">Antrean Persetujuan Cepat PR</div>
                    <div style="font-size: 12px; opacity: 0.9;">Tinjau &amp; proses Purchase Request yang masuk</div>
                </div>
                <span class="hero-badge">
                    <i class="fas fa-file-invoice"></i>
                    <span id="heroTaskCount">{{ $totalCount }} PR</span>
                </span>
            </div>

            <div class="hero-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="prApprovalSearch" placeholder="Cari nomor PR, department, pemohon..." oninput="applyPrFilters()">
            </div>
        </div>

        <div class="filter-chips">
            <button type="button" class="chip-btn active" data-filter="ALL" onclick="setPrFilter(this, 'ALL')">
                <i class="fas fa-layer-group"></i> Semua PR <span class="chip-count">{{ $totalCount }}</span>
            </button>
            <button type="button" class="chip-btn" data-filter="NON-OPS" onclick="setPrFilter(this, 'NON-OPS')">
                <i class="fas fa-folder"></i> Non-OPS <span class="chip-count">{{ $nonOpsCount }}</span>
            </button>
            <button type="button" class="chip-btn" data-filter="OPS" onclick="setPrFilter(this, 'OPS')">
                <i class="fas fa-folder-open"></i> OPS <span class="chip-count">{{ $opsCount }}</span>
            </button>
        </div>

        <div class="total-info" id="visiblePrInfo">Total: {{ $totalCount }} PR Menunggu</div>

        <div id="prApprovalList">
            @forelse ($prApprovals as $item)
                @php
                    $isOps = str_contains(strtoupper($item->supplies_type), 'OPS') && !str_contains(strtoupper($item->supplies_type), 'NON');
                    $groupKey = $isOps ? 'OPS' : 'NON-OPS';
                    $prStages = $stageMap[$item->pr_no] ?? [];
                    $stageSearch = collect($prStages)->map(function ($st) {
                        return ($st->position ?? '') . ' ' . ($st->actor ?? '') . ' ' . ($st->status ?? '') . ' ' . ($st->notes ?? '');
                    })->implode(' ');
                    $searchBlob = strtolower(
                        ($item->pr_no ?? '') . ' ' .
                        ($item->requester ?? '') . ' ' .
                        ($item->department ?? '') . ' ' .
                        ($item->position ?? '') . ' ' .
                        ($item->policy ?? '') . ' ' .
                        ($item->note ?? '') . ' ' .
                        $stageSearch
                    );
                    $detailUrl = url('purchase-request-detail-pending') . '?PR_No=' . urlencode($item->pr_no);
                @endphp

                <div class="approval-card pr-approval-item"
                     data-group="{{ $groupKey }}"
                     data-search="{{ $searchBlob }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="tag-proc">PROCUREMENT PR</span>
                        @if ($item->status === 'RETURNED')
                            <span class="tag-status-returned"><i class="fas fa-undo mr-1"></i> RETURNED</span>
                        @else
                            <span class="tag-status-pending"><i class="far fa-clock mr-1"></i> {{ $item->status }}</span>
                        @endif
                    </div>

                    <div class="pr-number-title">{{ $item->pr_no }}</div>

                    <div class="meta-line">
                        <i class="fas fa-user"></i>
                        <span><strong>{{ strtoupper($item->requester) }}</strong> ({{ $item->department }})</span>
                    </div>
                    <div class="meta-line">
                        <i class="fas fa-sitemap"></i>
                        <span>Posisi: <strong>{{ $item->position }}</strong> (Stage {{ $item->stage_order }})</span>
                    </div>
                    <div class="meta-line">
                        <i class="fas fa-code-branch"></i>
                        <span>Policy: {{ $item->policy }} &bull; {{ $item->supplies_type }}</span>
                    </div>

                    <div class="budget-box">
                        <div class="budget-amount">Nominal: Rp {{ number_format($item->estimated_budget, 0, ',', '.') }}</div>
                        <div class="budget-desc">
                            PR [{{ $item->supplies_type }} - {{ $item->category }} / {{ $item->sub_category }}]
                            No: {{ $item->pr_no }}
                            @if (!empty($item->note))
                                ({{ $item->note }})
                            @endif
                        </div>
                    </div>

                    @if (!empty($prStages))
    <div class="stage-history-box">
        <div class="stage-history-title">
            <span><i class="fas fa-history mr-1"></i> Riwayat Stage Approval</span>
            <span class="text-muted" style="font-weight: 600;">{{ count($prStages) }} Stage</span>
        </div>

        @foreach ($prStages as $st)
            @php
                $stUpper = strtoupper($st->status ?? 'PENDING');
                switch ($stUpper) {
                    case 'APPROVED':
                    case 'AUTO_APPROVED':
                        $stClass = 'st-approved';
                        break;
                    case 'REJECTED':
                    case 'CANCELLED':
                        $stClass = 'st-rejected';
                        break;
                    case 'PENDING':
                        $stClass = 'st-pending';
                        break;
                    default:
                        $stClass = 'st-waiting';
                        break;
                }
            @endphp
            <div class="stage-step-row">
                <div>
                    <div>
                        <strong>Stage {{ $st->stage }}</strong> &bull; {{ $st->position }}
                    </div>
                    @if (!empty($st->actor) || !empty($st->acted_at))
                        <div class="text-muted" style="font-size: 10px;">
                            {{ $st->actor ?: '-' }}{{ !empty($st->acted_at) ? ' • ' . $st->acted_at : '' }}
                        </div>
                    @endif
                    @if (!empty($st->notes) && $stUpper !== 'PENDING')
                        <div class="text-secondary mt-1" style="font-size: 10.5px;">
                            "{{ $st->notes }}"
                        </div>
                    @endif
                </div>
                <span class="stage-badge {{ $stClass }}">{{ $stUpper }}</span>
            </div>
        @endforeach
    </div>
@else
    <div class="stage-history-box text-muted" style="font-size: 10.5px;">
        Belum ada riwayat stage approval.
        @if (!empty($debugStageErr))
            <div class="text-danger mt-1">Err: {{ $debugStageErr }}</div>
        @endif
    </div>
@endif

                    <div class="card-footer-actions">
                        <div class="text-muted" style="font-size: 11px;">
                            <i class="far fa-calendar-alt mr-1"></i> {{ $item->date_label }}
                        </div>

                        <div class="d-flex align-items-center gap-1" style="gap: 6px;">
                            <a href="{{ $detailUrl }}" class="btn-action-sm btn-detail-outline">
                                <i class="far fa-eye"></i> Detail
                            </a>
                            <button type="button"
                                    class="btn-action-sm btn-approve-solid"
                                    onclick="prosesApprovalPR('{{ $item->pr_no }}', 'APPROVED', this)">
                                <i class="fas fa-check"></i> Setujui
                            </button>
                            <button type="button"
                                    class="btn-action-sm btn-reject-outline"
                                    onclick="prosesApprovalPR('{{ $item->pr_no }}', 'REJECTED', this)">
                                <i class="fas fa-times"></i> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="approval-card text-center text-muted small py-4">
                    Tidak ada antrean Approval PR saat ini.
                </div>
            @endforelse
        </div>
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'approval'])

    <script>
        var activePrGroup = 'ALL';

        function setPrFilter(btnEl, groupKey) {
            activePrGroup = groupKey;
            document.querySelectorAll('.chip-btn').forEach(function(b) {
                b.classList.remove('active');
            });
            btnEl.classList.add('active');
            applyPrFilters();
        }

        function applyPrFilters() {
            var q = (document.getElementById('prApprovalSearch')?.value || '').toLowerCase().trim();
            var visible = 0;

            document.querySelectorAll('.pr-approval-item').forEach(function(card) {
                var grp = card.getAttribute('data-group') || 'NON-OPS';
                var hay = card.getAttribute('data-search') || '';
                var matchGroup = (activePrGroup === 'ALL') || (grp === activePrGroup);
                var matchQuery = (q === '') || (hay.indexOf(q) !== -1);
                var show = matchGroup && matchQuery;

                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            var infoEl = document.getElementById('visiblePrInfo');
            if (infoEl) infoEl.textContent = 'Total: ' + visible + ' PR Menunggu';
        }

        function prosesApprovalPR(prNo, actionType, btnEl) {
            var isApprove = (actionType === 'APPROVED');

            Swal.fire({
                icon: isApprove ? 'question' : 'warning',
                title: isApprove ? 'Setujui PR ' + prNo + '?' : 'Tolak PR ' + prNo + '?',
                input: 'textarea',
                inputLabel: isApprove ? 'Catatan Approval (Opsional)' : 'Alasan Penolakan (Wajib Diisi)',
                inputPlaceholder: isApprove ? 'Tambahkan catatan jika diperlukan...' : 'Tuliskan alasan penolakan PR...',
                showCancelButton: true,
                confirmButtonText: isApprove ? 'Ya, Setujui' : 'Ya, Tolak',
                cancelButtonText: 'Batal',
                confirmButtonColor: isApprove ? '#059669' : '#DC2626',
                preConfirm: function(noteValue) {
                    var trimmed = (noteValue || '').trim();
                    if (!isApprove && trimmed === '') {
                        Swal.showValidationMessage('Alasan penolakan wajib diisi!');
                        return false;
                    }
                    return trimmed;
                }
            }).then(function(result) {
                if (!result.isConfirmed) return;

                var noteText = result.value || '';
                var originalHtml = btnEl.innerHTML;
                btnEl.disabled = true;
                btnEl.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                Swal.fire({
                    title: 'Memproses Approval PR...',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() { Swal.showLoading(); }
                });

                var formData = new FormData();
                formData.append('pr_no', prNo);
                formData.append('action', actionType);
                formData.append('approval_note', noteText);

                fetch("{{ url('purchase-request-approve') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(async function(resp) {
                    var data = {};
                    try { data = await resp.json(); } catch (err) {}
                    return { ok: resp.ok, data: data };
                })
                .then(function(res) {
                    btnEl.disabled = false;
                    btnEl.innerHTML = originalHtml;

                    var ok = res.ok && (res.data.status === 'success' || res.data.success !== false);
                    if (ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.data.message || ('PR ' + prNo + ' berhasil diproses.'),
                            confirmButtonColor: '#047857'
                        }).then(function() {
                            var card = btnEl.closest('.pr-approval-item');
                            if (card) card.remove();
                            var remaining = document.querySelectorAll('.pr-approval-item').length;
                            var badge = document.getElementById('heroTaskCount');
                            if (badge) badge.textContent = remaining + ' PR';
                            applyPrFilters();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memproses',
                            text: (res.data && res.data.message) ? res.data.message : 'Terjadi kesalahan saat memproses approval PR.',
                            confirmButtonColor: '#DC2626'
                        });
                    }
                })
                .catch(function() {
                    btnEl.disabled = false;
                    btnEl.innerHTML = originalHtml;
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Bermasalah',
                        text: 'Tidak dapat terhubung ke server. Silakan coba lagi.',
                        confirmButtonColor: '#DC2626'
                    });
                });
            });
        }
    </script>
</body>
</html>