@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<style>
    .po-policy-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .po-policy-card:hover, .po-policy-card.active {
        border-color: #16a34a;
        background: #f0fdf4;
    }
    .step-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 11px;
    }
    .step-badge .s-num {
        background: #0284c7;
        color: #fff;
        font-weight: 700;
        padding: 1px 5px;
        border-radius: 3px;
        font-size: 10px;
    }
    .inbox-table-wrap {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        overflow-x: auto;
        max-height: 310px;
    }
    .inbox-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .inbox-table thead th {
        background: #e2e8f0;
        color: #1e293b;
        font-weight: 600;
        padding: 6px 8px;
        border: 1px solid #cbd5e1;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .inbox-table thead tr.filter-row th {
        background: #f1f5f9;
        top: 29px;
        padding: 4px;
    }
    .inbox-table thead input, .inbox-table thead select {
        width: 100%;
        font-size: 11px;
        padding: 2px 4px;
        border: 1px solid #94a3b8;
        border-radius: 3px;
    }
    .inbox-table tbody tr {
        cursor: pointer;
    }
    .inbox-table tbody tr:hover {
        background: #f1f5f9;
    }
    .inbox-table tbody tr.selected-row {
        background: #bae6fd !important;
        font-weight: 500;
    }
    .inbox-table td {
        padding: 6px 8px;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .detail-panel {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 16px;
        margin-top: 14px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .info-box {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 12px 14px;
        height: 100%;
    }
    .info-box-title {
        font-weight: 700;
        font-size: 13px;
        color: #1e293b;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid #f1f5f9;
    }
    .kv-row {
        display: flex;
        font-size: 12.5px;
        margin-bottom: 6px;
    }
    .kv-label {
        width: 155px;
        color: #64748b;
        flex-shrink: 0;
    }
    .kv-val {
        font-weight: 600;
        color: #0f172a;
    }
    .stage-pill {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 12px;
        min-width: 190px;
    }
    .stage-pill.approved { border-color: #16a34a; background: #f0fdf4; }
    .stage-pill.pending  { border-color: #f59e0b; background: #fffbeb; }
    .stage-pill.waiting  { border-color: #cbd5e1; background: #f8fafc; opacity: 0.75; }
    .stage-pill.rejected { border-color: #dc2626; background: #fef2f2; }
</style>

<div class="container-fluid py-3">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h5 class="mb-0 fw-bold text-dark">Approval Purchase Order (PO) — Multi-Stage Inbox</h5>
            <small class="text-muted">Alur persetujuan Purchase Order berjenjang berdasarkan Policy & Nominal PO</small>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                Reset Filter Policy
            </button>
        </div>
    </div>

    {{-- 1. RINGKASAN POLICY & ALUR JENJANG PO (ATAS) --}}
    <div class="mb-2">
        @forelse($policies as $pol)
            <div class="po-policy-card" data-policy="{{ $pol->Policy_Code }}" onclick="filterByPolicy('{{ $pol->Policy_Code }}', this)">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="fw-bold text-primary" style="font-size: 12px; min-width: 145px;">
                        ✓ {{ $pol->Policy_Code }}
                    </span>
                    @foreach($pol->steps as $idx => $st)
                        <span class="step-badge">
                            <span class="s-num">S{{ $st->Stage_Order }}</span>
                            <strong>{{ $st->Position_Name ?? $st->Position_Code }}</strong>
                            <span class="text-muted">({{ $st->Approval_Mode ?? 'ALL' }})</span>
                        </span>
                        @if(!$loop->last)
                            <span class="text-muted fw-bold">&gt;</span>
                        @endif
                    @endforeach
                </div>
                <div class="text-end">
                    <small class="text-danger fw-semibold" style="font-size: 11px;">
                        {{ $pol->Description ?? $pol->Policy_Name }}
                    </small>
                </div>
            </div>
        @empty
            <div class="alert alert-light border py-2 mb-2" style="font-size: 12px;">
                Belum ada konfigurasi Policy khusus PO aktif. Menampilkan semua antrean approval PO.
            </div>
        @endforelse
    </div>

    {{-- 2. TABEL DAFTAR PO MENUNGGU APPROVAL (TENGAH) --}}
    <div class="inbox-table-wrap">
        <table class="inbox-table" id="poInboxTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>PO No</th>
                    <th>Ref PR</th>
                    <th>Supplier</th>
                    <th>Policy & Approval Step</th>
                    <th>Position</th>
                    <th>Stage</th>
                    <th>Level</th>
                    <th>Mode</th>
                    <th>Task Status</th>
                    <th>Total Nominal</th>
                    <th>Requester</th>
                    <th>Submitted</th>
                </tr>
                <tr class="filter-row">
                    <th></th>
                    <th><input type="text" onkeyup="applyTableFilter()" data-col="1" placeholder="Cari PO..."></th>
                    <th><input type="text" onkeyup="applyTableFilter()" data-col="2" placeholder="Cari PR..."></th>
                    <th><input type="text" onkeyup="applyTableFilter()" data-col="3" placeholder="Supplier..."></th>
                    <th><input type="text" id="filterPolicyInput" onkeyup="applyTableFilter()" data-col="4" placeholder="Policy..."></th>
                    <th><input type="text" onkeyup="applyTableFilter()" data-col="5" placeholder="Posisi..."></th>
                    <th><input type="text" onkeyup="applyTableFilter()" data-col="6" style="width:40px"></th>
                    <th><input type="text" onkeyup="applyTableFilter()" data-col="7" style="width:40px"></th>
                    <th>
                        <select onchange="applyTableFilter()" data-col="8">
                            <option value="">ALL/ANY</option>
                            <option value="ALL">ALL</option>
                            <option value="ANY">ANY</option>
                        </select>
                    </th>
                    <th>
                        <select onchange="applyTableFilter()" data-col="9" id="filterStatusSelect">
                            <option value="">Semua</option>
                            <option value="PENDING" selected>PENDING</option>
                            <option value="WAITING">WAITING</option>
                            <option value="APPROVED">APPROVED</option>
                            <option value="REJECTED">REJECTED</option>
                        </select>
                    </th>
                    <th></th>
                    <th><input type="text" onkeyup="applyTableFilter()" data-col="11" placeholder="Requester..."></th>
                    <th></th>
                </tr>
            </thead>
                <tbody>
                @forelse($tasks as $i => $t)
                    @php
                        $maxStage = $stageCounts[$t->request_id] ?? $t->stage;
                        $stStatus = strtoupper((string) ($t->task_status ?? ''));
                        if ($stStatus === 'PENDING') {
                            $badgeClass = 'bg-warning text-dark';
                        } elseif ($stStatus === 'APPROVED') {
                            $badgeClass = 'bg-success text-white';
                        } elseif ($stStatus === 'REJECTED') {
                            $badgeClass = 'bg-danger text-white';
                        } else {
                            $badgeClass = 'bg-secondary text-white';
                        }
                    @endphp
                    <tr onclick="loadTaskDetail({{ $t->task_id }}, this)" data-task-id="{{ $t->task_id }}">
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="fw-bold text-primary">{{ $t->po_no }}</td>
                        <td>{{ $t->pr_no ?? '-' }}</td>
                        <td>{{ $t->supplier ?? '-' }}</td>
                        <td>[{{ $t->policy_code ?? 'PO-STD' }}] Stage {{ $t->stage }}/{{ $maxStage }}: {{ $t->position_name }}</td>
                        <td>{{ $t->position_code }} - {{ $t->position_name }}</td>
                        <td class="text-center">{{ $t->stage }}</td>
                        <td class="text-center">{{ $t->level }}</td>
                        <td class="text-center">{{ $t->mode ?? 'ALL' }}</td>
                        <td class="text-center"><span class="badge {{ $badgeClass }}">{{ $t->task_status }}</span></td>
                        <td class="text-end fw-semibold">Rp {{ number_format($t->total_amount ?? 0, 2, ',', '.') }}</td>
                        <td>{{ $t->requester }}</td>
                        <td>{{ $t->submitted_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center py-4 text-muted">Tidak ada data task approval Purchase Order.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between mt-1">
        <small class="text-primary" style="font-size: 11px;">
            ⓘ Tips UX: Klik tab policy di atas untuk memfilter jenjang PO, atau klik baris tabel untuk meninjau item PO & melakukan Approve / Reject.
        </small>
        <small class="text-muted" style="font-size: 11px;">Menampilkan {{ count($tasks) }} task approval PO</small>
    </div>

    {{-- 3. PANEL DETAIL & AKSI CEPAT (BAWAH) --}}
    <div class="detail-panel" id="poDetailPanel" style="display: none;">
        {{-- Header Task + Tombol Aksi Cepat --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-2 mb-3 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-success bg-opacity-10 text-success rounded p-2 fw-bold">PO</div>
                <div>
                    <h6 class="mb-0 fw-bold" id="detHeaderTitle">Task #—</h6>
                    <small class="text-muted" id="detHeaderSub">PURCHASE ORDER • Diajukan pada: -</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark px-3 py-2" id="detStatusBadge">PENDING</span>
            </div>
        </div>

        {{-- Baris Aksi Cepat --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 bg-light p-2 rounded mb-3 border" id="quickActionBar">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <span class="text-danger fw-bold" style="font-size: 12px;">⚡ Aksi Cepat untuk Task ini:</span>
                <input type="text" id="approvalRemarks" class="form-control form-control-sm" style="max-width: 340px;" placeholder="Catatan / alasan approve atau reject (opsional)...">
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success fw-semibold" onclick="submitApprovalAction('APPROVE')">
                    ✓ Setujui (Approve)
                </button>
                <button type="button" class="btn btn-sm btn-danger fw-semibold" onclick="submitApprovalAction('REJECT')">
                    ✕ Tolak (Reject)
                </button>

                <button type="button" id="btnClaimTask" class="btn btn-sm btn-warning fw-semibold" style="display:none;" onclick="submitApprovalAction('CLAIM')">
                    ✋ Claim Task
                </button>

                <button type="button" class="btn btn-sm btn-dark fw-semibold" onclick="submitApprovalAction('FORCE_APPROVE')">
                    ⚡ Force Approve
                </button>
            </div>
        </div>

        {{-- Dua Kolom Utama: Informasi Permohonan & Status Konfigurasi --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="info-box">
                    <div class="info-box-title">ⓘ Informasi Permohonan Purchase Order</div>
                    <div class="kv-row"><div class="kv-label">No. Purchase Order</div><div class="kv-val" id="detPoNo">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Referensi PR / RFQ</div><div class="kv-val" id="detRefPr">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Vendor / Supplier</div><div class="kv-val text-primary" id="detSupplier">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Pemohon (Requester)</div><div class="kv-val" id="detRequester">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Departemen</div><div class="kv-val" id="detDept">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Term Pembayaran</div><div class="kv-val" id="detTerm">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Total Nominal PO</div><div class="kv-val text-success fs-6" id="detNominal">: Rp 0,00</div></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <div class="info-box-title">⇄ Status & Konfigurasi Approval</div>
                    <div class="kv-row"><div class="kv-label">Policy Approval</div><div class="kv-val" id="detPolicy">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Posisi Approver</div><div class="kv-val" id="detPosition">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Stage / Level</div><div class="kv-val" id="detStageLevel">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Mode Approval</div><div class="kv-val" id="detMode">: ALL</div></div>
                    <div class="kv-row"><div class="kv-label">Status Task</div><div class="kv-val" id="detTaskStatus">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Diproses Oleh / Waktu</div><div class="kv-val" id="detActedBy">: -</div></div>
                    <div class="kv-row"><div class="kv-label">Catatan Permohonan</div><div class="kv-val text-muted" id="detNotes">: -</div></div>
                </div>
            </div>
        </div>

        {{-- Rincian Item Barang/Jasa dalam PO --}}
        <div class="info-box mb-3">
            <div class="info-box-title">📦 Rincian Item Purchase Order</div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" style="font-size: 12px;">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode Item</th>
                            <th>Nama Item / Spesifikasi</th>
                            <th class="text-end">Qty</th>
                            <th>Satuan</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detItemsBody">
                        <tr><td colspan="7" class="text-center text-muted">Memuat item PO...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kandidat Approver Posisi Ini --}}
        <div class="info-box mb-3">
            <div class="info-box-title d-flex justify-content-between">
                <span>👥 Kandidat Approver Posisi Ini</span>
                <span class="badge bg-danger bg-opacity-10 text-danger" id="detCandidateCount">0 Kandidat</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size: 12px;">
                    <thead class="table-light">
                        <tr>
                            <th>NAMA PEGAWAI</th>
                            <th>USERNAME</th>
                            <th>DEPARTEMEN</th>
                            <th>TIPE KANDIDAT</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody id="detCandidatesBody"></tbody>
                </table>
            </div>
        </div>

        <!-- Jenjang Approval Lengkap (Termasuk Stage Dilewati / Mendatang) -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">
            <i class="bi bi-diagram-3 me-1"></i> Jenjang Approval Lengkap (Termasuk Stage Dilewati / Mendatang)
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" id="tblJenjangApproval">
                <thead class="table-light">
                    <tr>
                        <th style="width: 110px;">STAGE</th>
                        <th>JABATAN / POSISI</th>
                        <th style="width: 150px;">STATUS</th>
                        <th>EKSEKUTOR / KANDIDAT</th>
                        <th style="width: 165px;">WAKTU</th>
                        <th>KETERANGAN / ALASAN</th>
                    </tr>
                </thead>
                <tbody id="tbodyJenjangApproval">
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">Memuat jenjang approval...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Riwayat Persetujuan (Audit Trail) -->
<div class="card border-0 shadow-sm mt-3" id="cardAuditTrail">
    <div class="card-body">
        <h6 class="fw-bold mb-3">
            <i class="bi bi-activity me-1"></i> Riwayat Persetujuan (Audit Trail)
        </h6>
        <div class="table-responsive">
            <table class="table table-borderless table-hover align-middle mb-0" id="tblAuditTrail">
                <thead class="table-light border-bottom">
                    <tr>
                        <th style="width: 170px;">WAKTU</th>
                        <th style="width: 170px;">OLEH</th>
                        <th style="width: 150px;">AKSI</th>
                        <th style="width: 110px;">STAGE</th>
                        <th>CATATAN</th>
                    </tr>
                </thead>
                <tbody id="tbodyAuditTrail">
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">Belum ada riwayat aksi.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

{{-- Grant Self-Approval Exception (Administrator) --}}
<div class="card border-warning bg-warning bg-opacity-10 shadow-sm mt-3" id="cardSelfApprovalException">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
            <h6 class="fw-bold text-warning-emphasis mb-0">
                <i class="bi bi-shield-lock me-1"></i> Grant Self-Approval Exception (Administrator)
            </h6>
            <span class="badge bg-warning text-dark" id="badgeExceptionStatus" style="display: none;"></span>
        </div>
        <p class="text-muted mb-3" style="font-size: 12.5px;">
            Berikan izin khusus jika pemohon perlu menyetujui dokumen sendiri untuk situasi mendesak/khusus.
        </p>

        <div class="row g-3 align-items-end">
            <div class="col-md-7">
                <label for="exceptionReason" class="form-label fw-semibold small mb-1">
                    Alasan Exception (Wajib)
                </label>
                <input type="text"
                       id="exceptionReason"
                       class="form-control form-control-sm"
                       placeholder="Contoh: Situasi mendesak / approver berhalangan...">
            </div>
            <div class="col-md-3">
                <label for="exceptionExpiresAt" class="form-label fw-semibold small mb-1">
                    Berlaku Sampai (Opsional)
                </label>
                <input type="datetime-local"
                       id="exceptionExpiresAt"
                       class="form-control form-control-sm">
            </div>
            <div class="col-md-2 d-grid">
                <button type="button"
                        id="btnGrantException"
                        class="btn btn-sm btn-warning fw-semibold"
                        onclick="grantSelfApprovalException()">
                    <i class="bi bi-check2 me-1"></i> Berikan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let activeTaskId = null;

function formatRupiah(val) {
    const num = parseFloat(val || 0);
    return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function filterByPolicy(policyCode, el) {
    document.querySelectorAll('.po-policy-card').forEach(c => c.classList.remove('active'));
    if (el) el.classList.add('active');
    document.getElementById('filterPolicyInput').value = policyCode;
    applyTableFilter();
}

function resetFilters() {
    document.querySelectorAll('.po-policy-card').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('#poInboxTable thead input').forEach(i => i.value = '');
    document.getElementById('filterStatusSelect').value = '';
    applyTableFilter();
}

function applyTableFilter() {
    const table = document.getElementById('poInboxTable');
    const filters = [];
    table.querySelectorAll('thead tr.filter-row input, thead tr.filter-row select').forEach(el => {
        const col = parseInt(el.getAttribute('data-col'));
        const val = el.value.trim().toLowerCase();
        if (val) filters.push({ col, val });
    });

    table.querySelectorAll('tbody tr').forEach(tr => {
        if (tr.cells.length < 2) return;
        let show = true;
        for (const f of filters) {
            const cellText = (tr.cells[f.col]?.innerText || '').trim().toLowerCase();
            if (!cellText.includes(f.val)) {
                show = false;
                break;
            }
        }
        tr.style.display = show ? '' : 'none';
    });
}

function escapeHtml(val) {
    return String(val ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderStageStatusBadge(badgeCode, labelText) {
    const code = String(badgeCode || '').toUpperCase();
    let cls = 'bg-secondary text-white';

    if (code === 'PENDING') {
        cls = 'bg-warning text-dark';
    } else if (code === 'WAITING') {
        cls = 'bg-light text-secondary border';
    } else if (code === 'APPROVED' || code === 'AUTO_APPROVED') {
        cls = 'bg-success text-white';
    } else if (code === 'REJECTED') {
        cls = 'bg-danger text-white';
    } else if (code === 'SKIPPED' || code === 'CANCELLED') {
        cls = 'bg-dark text-white';
    }

    return `<span class="badge ${cls} px-2 py-1">${escapeHtml(labelText || code || '-')}</span>`;
}

function renderAuditActionBadge(actionCode) {
    const act = String(actionCode || '').toUpperCase();
    let cls = 'bg-info text-dark';

    if (act.includes('APPROVE')) {
        cls = 'bg-success text-white';
    } else if (act.includes('REJECT') || act.includes('CANCEL')) {
        cls = 'bg-danger text-white';
    } else if (act.includes('SUBMIT')) {
        cls = 'bg-primary text-white';
    } else if (act.includes('TASK') || act.includes('CLAIM')) {
        cls = 'bg-warning text-dark';
    }

    return `<span class="badge ${cls} px-2 py-1">${escapeHtml(act || 'ACTION')}</span>`;
}

function renderApprovalMatrixAndLogs(res) {
    const matrix = Array.isArray(res.approval_matrix) ? res.approval_matrix : [];
    const tbodyMatrix = document.getElementById('tbodyJenjangApproval');

    if (tbodyMatrix) {
        if (!matrix.length) {
            tbodyMatrix.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted py-3">
                        Belum ada konfigurasi jenjang approval untuk dokumen ini.
                    </td>
                </tr>`;
        } else {
            tbodyMatrix.innerHTML = matrix.map((row) => {
                const posCodeBadge = row.position_code
                    ? `<span class="badge bg-light text-primary border ms-1">${escapeHtml(row.position_code)}</span>`
                    : '';

                return `
                    <tr>
                        <td class="fw-semibold">${escapeHtml(row.stage_label || ('Stage ' + (row.stage_order || '-')))}</td>
                        <td>
                            <span class="fw-medium">${escapeHtml(row.position_name || '-')}</span>
                            ${posCodeBadge}
                        </td>
                        <td>${renderStageStatusBadge(row.status_badge, row.status_label)}</td>
                        <td>${escapeHtml(row.executor || '-')}</td>
                        <td>${escapeHtml(row.acted_at || '-')}</td>
                        <td class="text-muted">${escapeHtml(row.notes || '-')}</td>
                    </tr>`;
            }).join('');
        }
    }

    const logs = Array.isArray(res.audit_logs) ? res.audit_logs : [];
    const tbodyLogs = document.getElementById('tbodyAuditTrail');

    if (tbodyLogs) {
        if (!logs.length) {
            tbodyLogs.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">Belum ada riwayat aksi.</td>
                </tr>`;
        } else {
            tbodyLogs.innerHTML = logs.map((lg) => `
                <tr>
                    <td class="text-muted">${escapeHtml(lg.time || '-')}</td>
                    <td class="fw-semibold">${escapeHtml(lg.actor || '-')}</td>
                    <td>${renderAuditActionBadge(lg.action)}</td>
                    <td>${escapeHtml(lg.stage || 'Stage -')}</td>
                    <td class="text-muted">${escapeHtml(lg.notes || '-')}</td>
                </tr>
            `).join('');
        }
    }
}

function loadTaskDetail(taskId, rowEl) {
    activeTaskId = taskId;
    document.querySelectorAll('#poInboxTable tbody tr').forEach(r => r.classList.remove('selected-row'));
    if (rowEl) rowEl.classList.add('selected-row');

    const panel = document.getElementById('poDetailPanel');
    panel.style.display = 'block';






    // Tampilkan indikator memuat saat fetch berjalan
    document.getElementById('detHeaderTitle').innerText = `Task #${taskId} — Memuat...`;
    document.getElementById('detItemsBody').innerHTML =
        `<tr><td colspan="7" class="text-center text-muted">Memuat detail...</td></tr>`;
    document.getElementById('tbodyJenjangApproval').innerHTML =
        `<tr><td colspan="6" class="text-center text-muted py-3">Memuat jenjang approval...</td></tr>`;
    document.getElementById('tbodyAuditTrail').innerHTML =
        `<tr><td colspan="5" class="text-center text-muted py-3">Memuat riwayat aksi...</td></tr>`;

    fetch(`{{ url('/approval-purchase-request/detail') }}/${taskId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.success === false) {
            throw new Error(data.message || `Gagal memuat detail (HTTP ${res.status})`);
        }
        return data;
    })
    .then((res) => {
        const t = res.task || {};
        const docNo = t.pr_no || t.po_no || '-';

        document.getElementById('detHeaderTitle').innerText = `Task #${t.ID ?? t.task_id ?? taskId} — ${docNo}`;
        document.getElementById('detHeaderSub').innerText =
            `${t.module_type || 'PURCHASE_REQUEST'} • Diajukan pada: ${t.submitted_at || '-'}`;

        const st = (t.Status || t.task_status || 'PENDING').toUpperCase();
        const badge = document.getElementById('detStatusBadge');
        badge.innerText = st;
        badge.className = 'badge px-3 py-2 ' + (
            st === 'PENDING' ? 'bg-warning text-dark' :
            st === 'APPROVED' ? 'bg-success text-white' :
            st === 'REJECTED' ? 'bg-danger text-white' : 'bg-secondary text-white'
        );

        document.getElementById('quickActionBar').style.display = (st === 'PENDING') ? 'flex' : 'none';

        document.getElementById('detPoNo').innerText = `: ${docNo}`;
        document.getElementById('detRefPr').innerText = `: ${t.ref_pr || t.pr_no || '-'}`;
        document.getElementById('detSupplier').innerText = `: ${t.supplier || '-'}`;
        document.getElementById('detRequester').innerText = `: ${t.requester || '-'}`;
        document.getElementById('detDept').innerText = `: ${t.department || t.po_department || t.req_department || '-'}`;
        document.getElementById('detTerm').innerText = `: ${t.payment_term || t.urgency || '-'}`;
        document.getElementById('detNominal').innerText = `: ${formatRupiah(t.budget_amount ?? t.total_amount ?? t.po_total ?? t.req_amount)}`;

        document.getElementById('detPolicy').innerText = `: ${t.policy_code || '-'} (${t.policy_name || '-'})`;
        document.getElementById('detPosition').innerText = `: ${t.Position_Code || '-'} - ${t.Position_Name || '-'}`;
        document.getElementById('detStageLevel').innerText = `: Stage ${t.Stage_Order ?? 1} (Level ${t.Level_Number ?? 1})`;
        document.getElementById('detMode').innerText = `: ${t.Approval_Mode || 'ALL'}`;
        document.getElementById('detTaskStatus').innerText = `: ${st}`;
        document.getElementById('detActedBy').innerText = `: ${t.Acted_By ? t.Acted_By + (t.Acted_At ? ' (' + t.Acted_At + ')' : '') : '-'}`;
        document.getElementById('detNotes').innerText = `: ${t.description || '-'}`;

        const itemsBody = document.getElementById('detItemsBody');
        if (res.items && res.items.length > 0) {
            itemsBody.innerHTML = res.items.map((it, idx) => {
                const qty = parseFloat(it.Qty ?? 0);
                const price = parseFloat(
                    it.Default_Price ?? it.Price ?? it.Unit_Price ??
                    it.Master_UnitPrice ?? it.Master_Price ?? 0
                );
                const subtotal = parseFloat(it.Subtotal ?? it.Total ?? (qty * price));
                const prodCode = it.Product_Code || it.Item_Code || '-';
                const prodName = it.Product_Desc || it.Master_Description || it.Description || '-';
                const prodSpec = it.Specification || it.Master_Specification || '';
                const prodUnit = it.Unit || it.Master_Unit_Name || it.UOM || 'PCS';
                const prodMeta = [
                    it.Product_Brand ? `Brand: ${it.Product_Brand}` : '',
                    it.Category ? `Kat: ${it.Category}` : '',
                    it.Main_Category ? `${it.Main_Category}` : '',
                    (it.Qty_Stock !== undefined && it.Qty_Stock !== null) ? `Stok: ${it.Qty_Stock}` : ''
                ].filter(Boolean).join(' • ');

                return `
                    <tr>
                        <td>${idx + 1}</td>
                        <td class="font-monospace fw-semibold">${escapeHtml(prodCode)}</td>
                        <td>
                            <div class="fw-semibold">${escapeHtml(prodName)}</div>
                            ${prodSpec ? `<div class="text-muted" style="font-size:11px;">Spec: ${escapeHtml(prodSpec)}</div>` : ''}
                            ${prodMeta ? `<div class="text-secondary" style="font-size:10.5px;">${escapeHtml(prodMeta)}</div>` : ''}
                        </td>
                        <td class="text-end">${qty}</td>
                        <td>${escapeHtml(prodUnit)}</td>
                        <td class="text-end">${formatRupiah(price)}</td>
                        <td class="text-end fw-semibold">${formatRupiah(subtotal)}</td>
                    </tr>
                `;
            }).join('');
        } else {
            itemsBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">Tidak ada rincian item.</td></tr>`;
        }

        const candBody = document.getElementById('detCandidatesBody');
        document.getElementById('detCandidateCount').innerText = `${(res.candidates || []).length} Kandidat`;
        if (res.candidates && res.candidates.length > 0) {
            candBody.innerHTML = res.candidates.map(c => {
                const approvalFlag = c.pr_approval ? `PR_Approval: ${c.pr_approval}` : 'APPROVER';
                const userStatus = c.status || 'ACTIVE';
                const statusCls = String(userStatus).toUpperCase().includes('INACTIVE') || String(userStatus).toUpperCase() === '0'
                    ? 'bg-secondary'
                    : 'bg-success';

                return `
                    <tr>
                        <td class="fw-semibold">${escapeHtml(c.name || '-')}</td>
                        <td class="text-danger font-monospace">${escapeHtml(c.username || '-')}</td>
                        <td>
                            <div>${escapeHtml(c.dept || '-')}</div>
                            <small class="text-muted">${escapeHtml(c.position || '-')}</small>
                        </td>
                        <td><span class="badge bg-info text-dark">${escapeHtml(approvalFlag)}</span></td>
                        <td><span class="badge ${statusCls}">${escapeHtml(userStatus)}</span></td>
                    </tr>
                `;
            }).join('');
        } else {
            candBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Tidak ada kandidat user approval yang cocok pada posisi ini.</td></tr>`;
        }

        // Render Jenjang Approval Lengkap & Audit Trail
        renderApprovalMatrixAndLogs(res);

        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    })
    .catch((err) => {
        document.getElementById('detHeaderTitle').innerText = `Task #${taskId} — Gagal memuat`;
        document.getElementById('detItemsBody').innerHTML =
            `<tr><td colspan="7" class="text-center text-danger">${escapeHtml(err.message)}</td></tr>`;
    });
}

function submitApprovalAction(action) {
    if (!activeTaskId) return;
    const remarks = document.getElementById('approvalRemarks').value;

    if (action === 'REJECT' && !remarks.trim()) {
        alert('Mohon isi catatan / alasan penolakan terlebih dahulu.');
        return;
    }

    if (!confirm(`Yakin ingin melakukan ${action} pada task PO ini?`)) return;

    fetch(`{{ route('procurement.po.approval.action') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            task_id: activeTaskId,
            action: action,
            remarks: remarks
        })
    })
    .then(res => res.json())
    .then(res => {
        alert(res.message);
        if (res.success) {
            window.location.reload();
        }
    })
    .catch(() => alert('Terjadi kesalahan jaringan saat memproses approval.'));
}

// Otomatis pilih baris pertama saat halaman dibuka
document.addEventListener('DOMContentLoaded', () => {
    applyTableFilter();
    const firstVisibleRow = Array.from(document.querySelectorAll('#poInboxTable tbody tr'))
        .find(r => r.style.display !== 'none' && r.dataset.taskId);
    if (firstVisibleRow) {
        loadTaskDetail(firstVisibleRow.dataset.taskId, firstVisibleRow);
    }
});



function syncExceptionCard(res) {
    const card = document.getElementById('cardSelfApprovalException');
    const badge = document.getElementById('badgeExceptionStatus');
    const btn = document.getElementById('btnGrantException');
    const acc = res.access || {};
    const exc = res.self_approval_exception || null;

    if (!card) return;

    // Tampilkan card hanya jika user berhak (misal admin/authorized); jika ingin selalu tampil untuk admin, sesuaikan flag dari backend
    card.style.display = (acc.can_grant_exception !== false) ? 'block' : 'none';

    if (badge) {
        if (exc && exc.is_active) {
            badge.style.display = 'inline-block';
            badge.className = 'badge bg-success text-white';
            badge.innerText = exc.expires_at
                ? `Aktif s/d ${exc.expires_at}`
                : 'Exception Aktif';
        } else if (exc && exc.expired) {
            badge.style.display = 'inline-block';
            badge.className = 'badge bg-secondary text-white';
            badge.innerText = 'Exception Kedaluwarsa';
        } else {
            badge.style.display = 'none';
        }
    }

    if (btn) {
        btn.disabled = !activeTaskId;
    }
}

function grantSelfApprovalException() {
    if (!activeTaskId) {
        alert('Pilih task terlebih dahulu.');
        return;
    }

    const reasonEl = document.getElementById('exceptionReason');
    const expiresEl = document.getElementById('exceptionExpiresAt');
    const btn = document.getElementById('btnGrantException');

    const reason = (reasonEl?.value || '').trim();
    const expiresAt = (expiresEl?.value || '').trim();

    if (!reason) {
        alert('Alasan Exception wajib diisi.');
        reasonEl?.focus();
        return;
    }

    if (!confirm('Berikan Self-Approval Exception untuk task/dokumen ini?')) {
        return;
    }

    if (btn) btn.disabled = true;

    fetch(`{{ url('/approval-purchase-request/grant-exception') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            task_id: activeTaskId,
            reason: reason,
            expires_at: expiresAt || null
        })
    })
    .then(async (r) => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok || data.success === false) {
            throw new Error(data.message || `Gagal memberikan exception (HTTP ${r.status})`);
        }
        return data;
    })
    .then((data) => {
        alert(data.message || 'Self-Approval Exception berhasil diberikan.');
        if (reasonEl) reasonEl.value = '';
        if (expiresEl) expiresEl.value = '';
        // Muat ulang detail agar Audit Trail & status exception langsung terupdate
        const activeRow = document.querySelector(`#poInboxTable tbody tr[data-task-id="${activeTaskId}"]`);
        loadTaskDetail(activeTaskId, activeRow || null);
    })
    .catch((err) => {
        alert(err.message || 'Terjadi kesalahan saat memberikan exception.');
    })
    .finally(() => {
        if (btn) btn.disabled = false;
    });
}




</script>
@endsection