<style>
    .ptk-stepper-track {
        display: flex;
        align-items: stretch;
        gap: 0.75rem;
        overflow-x: auto;
        padding-bottom: 0.25rem;
    }

    .ptk-step-box {
        flex: 1;
        min-width: 175px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 0.75rem;
        background: #fff;
        position: relative;
    }

    .ptk-step-box.step-done {
        border-color: rgba(25, 135, 84, 0.35);
        background: rgba(25, 135, 84, 0.03);
    }

    .ptk-step-box.step-active {
        border-color: rgba(255, 193, 7, 0.65);
        background: rgba(255, 193, 7, 0.06);
    }

    .ptk-step-box.step-rejected {
        border-color: rgba(220, 53, 69, 0.45);
        background: rgba(220, 53, 69, 0.04);
    }
</style>

<div class="card shadow-sm border mt-3 mb-4" id="RequisitionDetailCard" data-ptk-id="{{ $ptk->ID }}">
    <div
        class="card-header bg-white py-2 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-sm bg-primary-transparent text-primary rounded-2">
                <i class="fa fa-file-contract"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold text-dark fs-14">Detail Permintaan Tenaga Kerja (PTK)</h6>
                <small class="text-muted">{{ $ptk->Task_ID }} &bull; {{ $ptk->Job_Position }}</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $status = strtoupper($ptk->Status ?? 'CREATED');
                $badgeColor = 'secondary';
                if (in_array($status, ['APPROVED', 'COMPLETED'])) {
                    $badgeColor = 'success';
                } elseif (in_array($status, ['WAITING_APPROVAL', 'ON_PROGRESS', 'CREATED', 'PENDING'])) {
                    $badgeColor = 'warning text-dark';
                } elseif (in_array($status, ['REJECTED', 'CANCELED'])) {
                    $badgeColor = 'danger';
                }
            @endphp
            <span class="badge bg-{{ $badgeColor }} px-3 py-2">{{ $status }}</span>
            <a href="{{ url('hrd-requisition-prints?Task=' . $ptk->Task_ID) }}" target="_blank"
                class="btn btn-outline-primary btn-sm">
                <i class="fa fa-print me-1"></i> Cetak PDF
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm"
                onclick="$('#divlistRequisition').html('');">
                <i class="fa fa-xmark me-1"></i> Tutup
            </button>
        </div>
    </div>

    <div class="card-body p-3">
        {{-- Visual Progress Stepper dari approval_policy & approval_policy_step --}}
        <div class="mb-3 p-3 bg-light rounded border">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                <span class="fw-bold text-dark fs-13">
                    <i class="fa fa-diagram-project text-primary me-1"></i> Progress Tahapan Approval PTK
                    @if (!empty($matchedPolicy))
                        <span class="badge bg-white text-dark border ms-1 fw-normal">{{ $matchedPolicy->Policy_Name }}
                            ({{ $matchedPolicy->Policy_Code }})</span>
                    @endif
                </span>
                @if (!empty($approvalRequest))
                    <span class="small text-muted">
                        Stage Saat Ini: <strong>Stage {{ $approvalRequest->Current_Stage ?? 1 }}</strong>
                    </span>
                @endif
            </div>

            @php
                // Map status dari approvalTasks per Stage_Order
                $taskByStage = [];
                foreach ($approvalTasks as $t) {
                    $taskByStage[$t->Stage_Order] = $t;
                }
                $legacyByLevel = [];
                foreach ($legacyLogs as $l) {
                    $lvl = $l->Level ?? ($l->Approved_Level ?? 0);
                    $legacyByLevel[$lvl] = $l;
                }
                $currentStage = $approvalRequest->Current_Stage ?? 1;
            @endphp

            @if (count($policySteps) > 0)
                <div class="ptk-stepper-track">
                    {{-- Step 0: Submitted --}}
                    <div class="ptk-step-box step-done">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-success">SUBMITTED</span>
                            <small class="text-muted">#0</small>
                        </div>
                        <div class="fw-bold text-dark fs-13">Pemohon PTK</div>
                        <div class="small text-muted text-truncate">{{ $ptk->Created_By ?? '-' }}</div>
                        <div class="text-muted mt-1" style="font-size: 0.7rem;">
                            {{ !empty($ptk->Created_Date) ? date('d M Y H:i', strtotime($ptk->Created_Date)) : '-' }}
                        </div>
                    </div>

                    @foreach ($policySteps as $step)
                        @php
                            $stg = (int) $step->Stage_Order;
                            $tRow = $taskByStage[$stg] ?? null;
                            $stepClass = '';
                            $stepBadge = '<span class="badge bg-secondary">MENUNGGU</span>';
                            $actorText = $step->Position_Code ?: 'Level ' . $step->Level_Number;
                            $timeText = '-';

                            if ($tRow) {
                                $ts = strtoupper($tRow->Status ?? 'PENDING');
                                if ($ts === 'APPROVED') {
                                    $stepClass = 'step-done';
                                    $stepBadge =
                                        '<span class="badge bg-success"><i class="fa fa-check me-1"></i>APPROVED</span>';
                                    $actorText = $tRow->Acted_By_Name ?: ($tRow->Acted_By_Username ?: $actorText);
                                    $timeText = !empty($tRow->Acted_At)
                                        ? date('d M Y H:i', strtotime($tRow->Acted_At))
                                        : '-';
                                } elseif ($ts === 'REJECTED') {
                                    $stepClass = 'step-rejected';
                                    $stepBadge =
                                        '<span class="badge bg-danger"><i class="fa fa-xmark me-1"></i>REJECTED</span>';
                                    $actorText = $tRow->Acted_By_Name ?: ($tRow->Acted_By_Username ?: $actorText);
                                    $timeText = !empty($tRow->Acted_At)
                                        ? date('d M Y H:i', strtotime($tRow->Acted_At))
                                        : '-';
                                } elseif ($stg == $currentStage && in_array($ts, ['PENDING', 'CLAIMED'])) {
                                    $stepClass = 'step-active';
                                    $stepBadge =
                                        '<span class="badge bg-warning text-dark"><i class="fa fa-clock me-1"></i>PROSES</span>';
                                    if (!empty($tRow->Candidate_Usernames) && $tRow->Candidate_Usernames !== '-') {
                                        $actorText = $tRow->Candidate_Usernames;
                                    }
                                }
                            } elseif ($status === 'APPROVED') {
                                $stepClass = 'step-done';
                                $stepBadge =
                                    '<span class="badge bg-success"><i class="fa fa-check me-1"></i>APPROVED</span>';
                            }
                        @endphp
                        <div class="ptk-step-box {{ $stepClass }}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                {!! $stepBadge !!}
                                <small class="text-muted">Stage {{ $stg }}</small>
                            </div>
                            <div class="fw-bold text-dark fs-13">{{ $step->Label ?: 'Level ' . $step->Level_Number }}
                            </div>
                            <div class="small text-muted text-truncate" title="{{ $actorText }}">
                                <i class="fa fa-user-tie me-1"></i> {{ $actorText }}
                            </div>
                            <div class="text-muted mt-1" style="font-size: 0.7rem;">{{ $timeText }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted small">
                    <i class="fa fa-info-circle me-1"></i> Belum ada tahapan step spesifik yang terhubung untuk posisi
                    ini.
                </div>
            @endif
        </div>

        {{-- Grid Informasi PTK --}}
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 bg-white">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 fs-13">
                        <i class="fa fa-id-badge text-primary me-1"></i> Informasi Posisi &amp; Formasi
                    </h6>
                    <table class="table table-sm table-borderless mb-0 small">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 38%;">Nomor PTK</td>
                                <td class="fw-bold text-dark">: {{ $ptk->Task_ID }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Posisi Jabatan</td>
                                <td class="fw-bold text-primary">: {{ $ptk->Job_Position }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Departemen</td>
                                <td>: {{ $ptk->Department ?? ($position->Department ?? '-') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Site Penempatan</td>
                                <td>: {{ $ptk->Placement ?? '-' }} ({{ $ptk->Location ?? '-' }})</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jumlah Permintaan</td>
                                <td>: <strong>{{ $ptk->Qty }} Orang</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Alokasi &amp; Prioritas</td>
                                <td>
                                    : <span
                                        class="badge bg-light text-dark border">{{ $ptk->Allocation == 'NEW' ? 'Karyawan Baru' : 'Replacement' }}</span>
                                    <span
                                        class="badge bg-{{ $ptk->Priority == 'URGENT' ? 'danger' : ($ptk->Priority == 'HIGH' ? 'warning text-dark' : 'secondary') }}">{{ $ptk->Priority }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Alasan Permintaan</td>
                                <td>: {{ $ptk->Request_Desc ?? ($ptk->Request_Type ?? '-') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="border rounded p-3 h-100 bg-white">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-2 fs-13">
                        <i class="fa fa-calendar-check text-primary me-1"></i> Jadwal Kerja &amp; Uraian Tugas
                    </h6>
                    <table class="table table-sm table-borderless mb-2 small">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 38%;">Work Schedule</td>
                                <td class="fw-semibold">: {{ $workCalendar->Name ?? ($ptk->Work_Schedule ?? '-') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal Mulai &ndash; Selesai</td>
                                <td>
                                    : <span
                                        class="text-success fw-semibold">{{ !empty($ptk->Start_Date) ? date('d M Y', strtotime($ptk->Start_Date)) : '-' }}</span>
                                    s/d
                                    <span
                                        class="text-danger fw-semibold">{{ !empty($ptk->End_Date) ? date('d M Y', strtotime($ptk->End_Date)) : '-' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Diajukan Oleh</td>
                                <td>: {{ $ptk->Created_By ?? '-' }}
                                    ({{ !empty($ptk->Created_Date) ? date('d M Y H:i', strtotime($ptk->Created_Date)) : '-' }})
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row g-2 mt-1">
                        <div class="col-md-6">
                            <div class="p-2 bg-light rounded border small">
                                <div class="fw-semibold text-dark mb-1">Deskripsi Tugas:</div>
                                <div class="text-muted"
                                    style="white-space: pre-wrap; max-height: 90px; overflow-y: auto;">
                                    {{ $ptk->Description ?: '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 bg-light rounded border small">
                                <div class="fw-semibold text-dark mb-1">Kualifikasi / Requirement:</div>
                                <div class="text-muted"
                                    style="white-space: pre-wrap; max-height: 90px; overflow-y: auto;">
                                    {{ $ptk->Requirement ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
