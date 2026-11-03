@if(!$task)
<div class="alert alert-warning d-flex align-items-center">
    <i class="fe fe-alert-triangle me-2 fs-18"></i>
    <span>Task approval tidak ditemukan atau telah diproses.</span>
</div>
@else
@php
    $taskStatus = strtoupper((string) $task->Task_Status);
    $statusBadgeClass = 'bg-secondary text-white';
    if ($taskStatus === 'PENDING') {
        $statusBadgeClass = 'bg-warning text-dark';
    } elseif ($taskStatus === 'CLAIMED') {
        $statusBadgeClass = 'bg-primary text-white';
    } elseif ($taskStatus === 'APPROVED') {
        $statusBadgeClass = 'bg-success text-white';
    } elseif ($taskStatus === 'REJECTED') {
        $statusBadgeClass = 'bg-danger text-white';
    } elseif ($taskStatus === 'HELD') {
        $statusBadgeClass = 'bg-secondary text-white';
    }
@endphp

<div class="card shadow-sm border" id="ApprovalTaskDetailCard" data-task-id="{{ $task->ID }}" data-request-no="{{ $task->Request_No }}" data-request-type="{{ $task->Request_Type }}" data-requester="{{ $task->Requester_Name }}" data-position="{{ $task->Position_Code }} - {{ $task->Position_Name }}">
    {{-- Card Header --}}
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
        <div class="d-flex align-items-center">
            <span class="avatar avatar-md bg-primary-transparent text-primary rounded-circle me-3">
                <i class="fe fe-file-text fs-18"></i>
            </span>
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    Task #{{ $task->ID }} &mdash; <span class="text-primary">{{ $task->Request_No }}</span>
                </h5>
                <small class="text-muted">
                    <span class="fw-semibold">{{ $task->Request_Type }}</span> &bull; Diajukan pada: {{ $task->Submitted_At ?: '-' }}
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $statusBadgeClass }} px-3 py-2 fs-12">
                <i class="fe {{ $taskStatus === 'APPROVED' ? 'fe-check-circle' : ($taskStatus === 'REJECTED' ? 'fe-x-circle' : 'fe-clock') }} me-1"></i>
                {{ $taskStatus === 'CLAIMED' ? ($isMyClaim ? 'CLAIMED BY YOU' : 'CLAIMED') : $taskStatus }}
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="ApprovalInboxCloseDetail();" title="Tutup Detail">
                <i class="fe fe-x"></i>
            </button>
        </div>
    </div>

    {{-- Quick Action Bar --}}
    <div class="bg-light p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center">
            <i class="fe fe-zap text-warning me-2 fs-16"></i>
            <span class="fw-semibold text-secondary small">Aksi Cepat untuk Task ini:</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($canAct)
            <button type="button" class="btn btn-success btn-sm shadow-sm" onclick="ApprovalInboxAct('APPROVE', {{ $task->ID }});">
                <i class="fe fe-check me-1"></i> Setujui (Approve)
            </button>
            <button type="button" class="btn btn-danger btn-sm shadow-sm" onclick="ApprovalInboxAct('REJECT', {{ $task->ID }});">
                <i class="fe fe-x me-1"></i> Tolak (Reject)
            </button>
            @endif

            @if($canClaim)
            <button type="button" class="btn btn-primary btn-sm shadow-sm" onclick="ApprovalInboxClaim({{ $task->ID }});">
                <i class="fe fe-user-check me-1"></i> Claim Task
            </button>
            @endif

            @if($canRelease)
            <button type="button" class="btn btn-warning btn-sm shadow-sm" onclick="ApprovalInboxRelease({{ $task->ID }});">
                <i class="fe fe-unlock me-1"></i> Release Claim
            </button>
            @endif
        </div>
    </div>

    {{-- Card Body --}}
    <div class="card-body">
        {{-- Section 1: Detail Grid --}}
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 bg-white">
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                        <i class="fe fe-info me-2"></i>Informasi Permohonan
                    </h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted fw-semibold" style="width: 150px;">No. Request</th>
                                <td class="fw-bold text-dark">: {{ $task->Request_No }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Tipe Permohonan</th>
                                <td>: <span class="badge bg-light text-dark border">{{ $task->Request_Type }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Pemohon</th>
                                <td class="fw-semibold">: {{ $task->Requester_Name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Departemen</th>
                                <td>: {{ $task->Department ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Lokasi / Company</th>
                                <td>: {{ $task->Location ?: '-' }} / {{ $task->Company_ID ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Nominal / Nilai</th>
                                <td class="fw-bold text-success">: Rp {{ number_format((float) $task->Amount, 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Revisi / Status Dokumen</th>
                                <td>: Rev {{ $task->Revision_No }} &bull; <span class="badge bg-info text-white">{{ $task->Request_Status }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 bg-white">
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                        <i class="fe fe-sliders me-2"></i>Status & Konfigurasi Approval
                    </h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="text-muted fw-semibold" style="width: 150px;">Posisi Approver</th>
                                <td class="fw-bold text-dark">: {{ $task->Position_Code }} - {{ $task->Position_Name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Stage / Level</th>
                                <td>: Stage {{ $task->Stage_Order }} (Level {{ $task->Level_Number }})</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Mode Approval</th>
                                <td>: <span class="badge bg-light text-dark border">{{ $task->Approval_Mode }}{{ $task->Quorum ? ' (Quorum: ' . $task->Quorum . ')' : '' }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Status Task</th>
                                <td>: <span class="badge {{ $statusBadgeClass }}">{{ $task->Task_Status }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Claimed By / At</th>
                                <td>: {{ $task->Claimed_By ? ('User #' . $task->Claimed_By . ' (' . ($task->Claimed_At ?: '-') . ')') : '-' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">Catatan Tertahan (Held)</th>
                                <td>: <span class="{{ $task->Held_Reason ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $task->Held_Reason ?: '-' }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Section 2: Description --}}
        <div class="mt-4">
            <h6 class="fw-bold text-dark mb-2">
                <i class="fe fe-align-left me-2 text-primary"></i>Deskripsi / Catatan Permohonan
            </h6>
            <div class="p-3 bg-light rounded border text-muted" style="min-height: 50px;">
                {{ $task->Description ? nl2br(e($task->Description)) : 'Tidak ada keterangan tambahan dari pemohon.' }}
            </div>
        </div>

        {{-- Section 3: Candidates --}}
        <div class="mt-4">
            <h6 class="fw-bold text-dark mb-2 d-flex justify-content-between align-items-center">
                <span><i class="fe fe-users me-2 text-primary"></i>Kandidat Approver Posisi Ini</span>
                <span class="badge bg-light text-secondary border">{{ $candidates->count() }} Kandidat</span>
            </h6>
            <div class="table-responsive border rounded">
                <table class="table table-sm table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nama Pegawai</th>
                            <th>Username</th>
                            <th>Posisi / Dept</th>
                            <th>Tipe Kandidat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($candidates as $candidate)
                        @php
                            $isCurrentUser = ((int) $candidate->User_Id === (int) Auth::id());
                        @endphp
                        <tr class="{{ $isCurrentUser ? 'table-primary' : '' }}">
                            <td class="ps-3 fw-semibold">
                                {{ $candidate->User_Name }}
                                @if($isCurrentUser)
                                    <span class="badge bg-primary text-white ms-1">Anda</span>
                                @endif
                            </td>
                            <td><code>{{ $candidate->username }}</code></td>
                            <td>{{ $candidate->Position ?: '-' }} @if($candidate->Department) &bull; <small class="text-muted">{{ $candidate->Department }}</small> @endif</td>
                            <td>
                                <span class="badge {{ $candidate->Candidate_Type === 'HOLDER' ? 'bg-info text-white' : 'bg-warning text-dark' }}">
                                    {{ $candidate->Candidate_Type }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $candidate->Status === 'ACTIVE' ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                    {{ $candidate->Status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">Belum ada kandidat aktif yang terhubung.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 4: History / Audit Logs --}}
        <div class="mt-4">
            <h6 class="fw-bold text-dark mb-2">
                <i class="fe fe-activity me-2 text-primary"></i>Riwayat Persetujuan (Audit Trail)
            </h6>
            <div class="table-responsive border rounded">
                <table class="table table-sm table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 170px;">Waktu</th>
                            <th>Oleh</th>
                            <th>Aksi</th>
                            <th>Stage</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        @php
                            $actionUpper = strtoupper((string) $log->Action);
                            $actionBadge = 'bg-secondary text-white';
                            if (in_array($actionUpper, ['APPROVE', 'APPROVED', 'FINAL_APPROVED'])) {
                                $actionBadge = 'bg-success text-white';
                            } elseif (in_array($actionUpper, ['REJECT', 'REJECTED'])) {
                                $actionBadge = 'bg-danger text-white';
                            } elseif (in_array($actionUpper, ['CLAIMED', 'CLAIM'])) {
                                $actionBadge = 'bg-primary text-white';
                            } elseif (in_array($actionUpper, ['SUBMITTED'])) {
                                $actionBadge = 'bg-info text-white';
                            } elseif (in_array($actionUpper, ['CLAIM_RELEASED', 'RELEASED'])) {
                                $actionBadge = 'bg-warning text-dark';
                            }
                        @endphp
                        <tr>
                            <td class="ps-3 small text-muted">{{ $log->Action_Date }}</td>
                            <td class="fw-semibold">{{ $log->Approver_Name ?: ($log->Is_System ? 'SYSTEM' : ('User #' . $log->Approver_Id)) }}</td>
                            <td><span class="badge {{ $actionBadge }}">{{ $log->Action }}</span></td>
                            <td>Stage {{ $log->Stage_Order ?: '-' }}</td>
                            <td class="text-secondary">{{ $log->Notes ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">Belum ada riwayat aktivitas pada permohonan ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 5: Admin Self-Approval Exception --}}
        @if($isAdmin && in_array(strtoupper((string) $task->Request_Status), ['IN_PROGRESS', 'BLOCKED'], true))
        <div class="card border border-warning mt-4 bg-light">
            <div class="card-header bg-warning-transparent text-warning-dark border-bottom py-2">
                <strong class="d-flex align-items-center">
                    <i class="fe fe-shield me-2"></i>Grant Self-Approval Exception (Administrator)
                </strong>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Berikan izin khusus jika pemohon perlu menyetujui dokumen sendiri untuk situasi mendesak/khusus.
                </p>
                <form method="POST" action="{{ url('hrd-approval-self-exception') }}">
                    @csrf
                    <input type="hidden" name="Request_ID" value="{{ $task->Request_ID }}" />
                    <input type="hidden" name="User_Id" value="{{ $task->Requester_Id }}" />
                    <div class="row g-2 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Alasan Exception (Wajib)</label>
                            <input type="text" name="Reason" class="form-control form-control-sm" placeholder="Jelaskan dasar pemberian exception..." required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Berlaku Sampai (Opsional)</label>
                            <input type="datetime-local" name="Expires_At" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                <i class="fe fe-check me-1"></i> Berikan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endif
