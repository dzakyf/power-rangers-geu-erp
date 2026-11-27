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

        {{-- Jenjang Approval Lengkap (Stage 1 -> Selesai) --}}
        <div class="info-box">
            <div class="info-box-title">↳ Jenjang Approval Lengkap (Termasuk Stage Selesai / Mendatang)</div>
            <div class="d-flex flex-wrap align-items-center gap-2" id="detFullStages"></div>
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

function loadTaskDetail(taskId, rowEl) {
    activeTaskId = taskId;
    document.querySelectorAll('#poInboxTable tbody tr').forEach(r => r.classList.remove('selected-row'));
    if (rowEl) rowEl.classList.add('selected-row');

    const panel = document.getElementById('poDetailPanel');
    panel.style.display = 'block';

    fetch(`{{ url('/approval-purchase-order/detail') }}/${taskId}`)
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                alert(res.message || 'Gagal memuat detail task.');
                return;
            }
            const t = res.task;
            document.getElementById('detHeaderTitle').innerText = `Task #${t.ID} — ${t.po_no}`;
            document.getElementById('detHeaderSub').innerText = `${t.module_type} • Diajukan pada: ${t.submitted_at || '-'}`;

            const badge = document.getElementById('detStatusBadge');
            badge.innerText = t.Status;
            badge.className = 'badge px-3 py-2 ' + (
                t.Status === 'PENDING' ? 'bg-warning text-dark' :
                t.Status === 'APPROVED' ? 'bg-success text-white' :
                t.Status === 'REJECTED' ? 'bg-danger text-white' : 'bg-secondary text-white'
            );

            // Sembunyikan tombol aksi jika task bukan PENDING
            document.getElementById('quickActionBar').style.display = (t.Status === 'PENDING') ? 'flex' : 'none';

            // Informasi Permohonan PO
            document.getElementById('detPoNo').innerText = `: ${t.po_no}`;
            document.getElementById('detRefPr').innerText = `: ${t.pr_no || '-'} ${t.rfq_no ? '/ ' + t.rfq_no : ''}`;
            document.getElementById('detSupplier').innerText = `: ${t.supplier || '-'}`;
            document.getElementById('detRequester').innerText = `: ${t.requester || '-'}`;
            document.getElementById('detDept').innerText = `: ${t.po_department || t.req_department || '-'}`;
            document.getElementById('detTerm').innerText = `: ${t.payment_term || '-'}`;
            document.getElementById('detNominal').innerText = `: ${formatRupiah(t.po_total ?? t.req_amount)}`;

            // Status & Konfigurasi
            document.getElementById('detPolicy').innerText = `: ${t.policy_code || '-'} (${t.policy_name || '-'})`;
            document.getElementById('detPosition').innerText = `: ${t.Position_Code} - ${t.Position_Name}`;
            document.getElementById('detStageLevel').innerText = `: Stage ${t.Stage_Order} (Level ${t.Level_Number})`;
            document.getElementById('detMode').innerText = `: ${t.Approval_Mode || 'ALL'}`;
            document.getElementById('detTaskStatus').innerText = `: ${t.Status}`;
            document.getElementById('detActedBy').innerText = `: ${t.Acted_By ? t.Acted_By + ' (' + t.Acted_At + ')' : '-'}`;
            document.getElementById('detNotes').innerText = `: ${t.description || '-'}`;

            // Render Item PO
            const itemsBody = document.getElementById('detItemsBody');
            if (res.items && res.items.length > 0) {
                itemsBody.innerHTML = res.items.map((it, idx) => `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>${it.Item_Code || '-'}</td>
                        <td>${it.Item_Name || it.Description || '-'}</td>
                        <td class="text-end">${it.Qty || 0}</td>
                        <td>${it.Unit || it.UOM || '-'}</td>
                        <td class="text-end">${formatRupiah(it.Price || it.Unit_Price)}</td>
                        <td class="text-end fw-semibold">${formatRupiah(it.Subtotal || it.Total)}</td>
                    </tr>
                `).join('');
            } else {
                itemsBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">Tidak ada rincian item PO.</td></tr>`;
            }

            // Render Kandidat Approver
            const candBody = document.getElementById('detCandidatesBody');
            document.getElementById('detCandidateCount').innerText = `${(res.candidates || []).length} Kandidat`;
            if (res.candidates && res.candidates.length > 0) {
                candBody.innerHTML = res.candidates.map(c => `
                    <tr>
                        <td class="fw-semibold">${c.name}</td>
                        <td class="text-danger font-monospace">${c.username}</td>
                        <td>${c.dept || '-'}</td>
                        <td><span class="badge bg-info text-dark">HOLDER</span></td>
                        <td><span class="badge bg-success">${c.status || 'ACTIVE'}</span></td>
                    </tr>
                `).join('');
            } else {
                candBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Tidak ada kandidat terdaftar pada posisi ini.</td></tr>`;
            }

            // Render Jenjang Stage Lengkap
            const stagesWrap = document.getElementById('detFullStages');
            stagesWrap.innerHTML = (res.stages || []).map((st, idx) => {
                const cls = st.Status.toLowerCase();
                return `
                    <div class="stage-pill ${cls}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>Stage ${st.Stage_Order} (Lvl ${st.Level_Number})</strong>
                            <span class="badge bg-secondary">${st.Status}</span>
                        </div>
                        <div class="fw-semibold text-dark">${st.Position_Name || st.Position_Code}</div>
                        <small class="text-muted">${st.Acted_By ? 'Oleh: ' + st.Acted_By : 'Menunggu giliran'}</small>
                    </div>
                    ${idx < res.stages.length - 1 ? '<span class="fw-bold text-muted">→</span>' : ''}
                `;
            }).join('');

            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
</script>
@endsection