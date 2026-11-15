<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#064E3B">
    <title>Detail Approval {{ $task->Request_No }} - HCGA Mobile</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('humanresources.mobile.partials.theme')
    <style>
        .kv-row {
            display: flex;
            flex-direction: column;
            padding: 8px 0;
            border-bottom: 1px dashed var(--border-soft);
        }
        .kv-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .kv-label {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .kv-val {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-top: 2px;
            word-break: break-word;
        }
        .sub-item-card {
            background: #F8FAFC;
            border: 1px solid var(--border-soft);
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 8px;
        }
        .sub-item-card:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="finora-shell">
        <div class="finora-topbar">
            <a href="{{ route('approval.mobile.index') }}" class="finora-back-btn"><i class="fas fa-arrow-left"></i></a>
            <h1 class="fs-6 fw-bold m-0">Detail Approval</h1>
            <span style="width: 38px;"></span>
        </div>

        <div class="finora-hero">
            <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
                <span class="badge bg-white bg-opacity-25 text-white rounded-pill px-3 py-1" style="font-size: 11px;">
                    {{ $task->Request_Type }} &bull; Stage {{ $task->Stage_Order }}
                </span>
                @php
                    $st = strtoupper((string) $task->Task_Status);
                    $stClass = $st === 'APPROVED' ? 'bg-light text-success' : ($st === 'REJECTED' ? 'bg-danger text-white' : 'bg-warning text-dark');
                @endphp
                <span class="badge {{ $stClass }} rounded-pill px-3 py-1" style="font-size: 11px;">
                    {{ $task->Task_Status }}
                </span>
            </div>
            <div class="fs-5 fw-bold mb-1">{{ $task->Request_No }}</div>
            @if(!is_null($task->Amount) && (float) $task->Amount > 0)
                <div class="fw-bold mb-1">Rp {{ number_format((float) $task->Amount, 0, ',', '.') }}</div>
            @endif
            <div class="small opacity-75">
                Diajukan oleh {{ $task->Requester_Name ?? '-' }} &bull; {{ $task->Submitted_At ? date('d M Y H:i', strtotime($task->Submitted_At)) : '-' }}
            </div>
        </div>

        <!-- Card 1: Informasi Permohonan -->
        <div class="finora-card mb-3">
            <div class="fw-bold small text-uppercase text-muted mb-2">
                <i class="fas fa-file-lines text-success me-1"></i> Informasi Permohonan
            </div>
            <div class="kv-row">
                <span class="kv-label">Pemohon</span>
                <span class="kv-val">{{ $task->Requester_Name ?? '-' }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">Departemen / Lokasi</span>
                <span class="kv-val">{{ $task->Department ?? '-' }} &bull; {{ $task->Location ?? '-' }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">Posisi Approver &amp; Mode</span>
                <span class="kv-val">
                    {{ $task->Position_Name ?? $task->Position_Code ?? '-' }}
                    (Lv.{{ $task->Level_Number ?? '-' }}, {{ $task->Approval_Mode ?? 'ANY' }})
                </span>
            </div>
            <div class="kv-row">
                <span class="kv-label">Deskripsi Permohonan</span>
                <span class="kv-val fw-normal">{{ $task->Description ?: '-' }}</span>
            </div>
        </div>

        <!-- Card 2: Kandidat Approver (Vertical Stack) -->
        <div class="finora-card mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold small text-uppercase text-muted">
                    <i class="fas fa-users text-success me-1"></i> Kandidat Approver
                </span>
                <span class="finora-pill">{{ count($candidates) }}</span>
            </div>
            @forelse($candidates as $cand)
                <div class="sub-item-card">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold small">{{ $cand->User_Name ?? $cand->username }}</span>
                        <span class="badge bg-light text-dark border rounded-pill" style="font-size: 10px;">{{ $cand->Candidate_Type }}</span>
                    </div>
                    <div class="text-muted" style="font-size: 11px;">
                        {{ $cand->Position ?? '-' }} &bull; {{ $cand->Department ?? '-' }}
                    </div>
                </div>
            @empty
                <div class="text-muted small">Belum ada kandidat approver tercatat.</div>
            @endforelse
        </div>

        <!-- Card 3: Riwayat Approval Log (Vertical Timeline) -->
        <div class="finora-card mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold small text-uppercase text-muted">
                    <i class="fas fa-clock-rotate-left text-success me-1"></i> Riwayat Approval
                </span>
                <span class="finora-pill">{{ count($logs) }}</span>
            </div>
            @forelse($logs as $log)
                @php
                    $act = strtoupper((string) ($log->Action ?? ''));
                    $actClass = $act === 'APPROVED' ? 'bg-success text-white' : ($act === 'REJECTED' ? 'bg-danger text-white' : 'bg-secondary text-white');
                @endphp
                <div class="sub-item-card">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold small">{{ $log->Approver_Name ?? 'System' }}</span>
                        <span class="badge {{ $actClass }} rounded-pill" style="font-size: 10px;">{{ $log->Action }}</span>
                    </div>
                    <div class="text-muted" style="font-size: 11px;">
                        {{ $log->Action_Date ? date('d M Y H:i', strtotime($log->Action_Date)) : '-' }}
                    </div>
                    @if(!empty($log->Notes))
                        <div class="mt-1 small text-dark">{{ $log->Notes }}</div>
                    @endif
                </div>
            @empty
                <div class="text-muted small">Belum ada riwayat keputusan approval.</div>
            @endforelse
        </div>

        <!-- Card 4: Panel Aksi -->
        @if($canAct || $canClaim || $canRelease)
        <div class="finora-card mb-3">
            <div class="fw-bold small text-uppercase text-muted mb-2">
                <i class="fas fa-gavel text-success me-1"></i> Tindakan Approval
            </div>

            @if($canClaim && !$isMyClaim)
                <button type="button" class="btn btn-outline-success w-100 rounded-pill py-2 fw-bold mb-3" onclick="submitDetailAction('claim')">
                    <i class="fas fa-hand-pointer me-1"></i> Claim Task Ini
                </button>
            @endif

            @if($canAct)
                <div class="mb-3">
                    <label class="form-label small fw-bold">Catatan / Alasan (Wajib jika Menolak)</label>
                    <textarea id="detailActionNotes" class="form-control rounded-3" rows="3" placeholder="Tuliskan catatan persetujuan atau alasan penolakan..."></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-danger flex-fill rounded-pill py-2 fw-bold" onclick="submitDetailAction('reject')">
                        <i class="fas fa-times-circle me-1"></i> Tolak
                    </button>
                    <button type="button" class="btn finora-btn-primary flex-fill rounded-pill py-2 fw-bold" onclick="submitDetailAction('approve')">
                        <i class="fas fa-check-circle me-1"></i> Setujui
                    </button>
                </div>
            @endif

            @if($canRelease)
                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill py-2 fw-bold mt-2" onclick="submitDetailAction('release')">
                    <i class="fas fa-unlock me-1"></i> Release Claim
                </button>
            @endif
        </div>
        @endif
    </div>

    @include('humanresources.mobile.partials.bottom-nav', ['active' => 'approval'])

    <script>
        const taskId = @json((int) $task->ID);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const inboxUrl = @json(route('approval.mobile.index'));

        function submitDetailAction(type) {
            const notesEl = document.getElementById('detailActionNotes');
            const notes = notesEl ? notesEl.value.trim() : '';

            if (type === 'reject' && !notes) {
                alert('Alasan penolakan wajib diisi.');
                if (notesEl) notesEl.focus();
                return;
            }

            const endpointMap = {
                claim: '{{ url("hrd-approval-claim") }}',
                approve: '{{ url("hrd-approval-approve") }}',
                reject: '{{ url("hrd-approval-reject") }}',
                release: '{{ url("hrd-approval-release") }}'
            };

            fetch(endpointMap[type], {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ Task_ID: taskId, Notes: notes })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message || 'Berhasil diproses.');
                    window.location.href = inboxUrl;
                } else {
                    alert(data.message || 'Gagal memproses task.');
                }
            })
            .catch(() => alert('Terjadi kesalahan koneksi server.'));
        }
    </script>
</body>
</html>
