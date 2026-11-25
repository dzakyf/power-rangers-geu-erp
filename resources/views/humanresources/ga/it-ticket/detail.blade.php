@php
    $status = strtoupper((string) $ticket->Ticket_Status);
    $mode = strtoupper((string) ($ticket->Workflow_Mode ?? 'IT_SUPPORT'));
    $actionType = strtoupper((string) ($ticket->Action_Type ?? 'CORRECTIVE'));
    $badge = [
        'OPEN'                     => 'info',
        'IN_PROGRESS'              => 'primary',
        'TROUBLESHOOTING'          => 'warning',
        'ESCALATED'                => 'danger',
        'VALIDATED'                => 'primary',
        'ROOT_CAUSE_ANALYSIS'      => 'warning',
        'RECOVERY'                 => 'warning',
        'TESTING'                  => 'info',
        'SYSTEM_NORMAL'            => 'success',
        'WAITING_MANAGER_APPROVAL' => 'warning',
        'DEVICE_PREPARATION'       => 'primary',
        'ASSIGNED_TO_USER'         => 'info',
        'HANDOVER'                 => 'success',
        'RESOLVED'                 => 'success',
        'USER_CONFIRMED'           => 'success',
        'CLOSED'                   => 'secondary',
        'REJECTED'                 => 'danger',
        'REOPENED'                 => 'warning',
        'ON_HOLD'                  => 'dark',
    ][$status] ?? 'secondary';

    $modeLabel = [
        'IT_SUPPORT'        => 'Tiket Bantuan IT',
        'IT_INCIDENT'       => 'Gangguan Sistem',
        'IT_DEVICE_REQUEST' => 'Pengajuan Perangkat IT',
    ][$mode] ?? $mode;

    $canEdit = can_menu('SI01158', 'edit');
    $curUser = auth()->user();
    $isReporter = $curUser && (
        (int) ($ticket->Reporter_User_Id ?? 0) === (int) $curUser->id
        || (string) ($ticket->Created_By ?? '') === (string) $curUser->username
        || strtoupper((string) ($curUser->Priv_ID ?? '')) === 'ADM'
        || strtolower((string) ($curUser->username ?? '')) === 'admin'
    );
@endphp

<div class="mt-3" id="ITTicketDetailContainer">
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h4 class="card-title m-1">Tiket IT {{ $ticket->Ticket_No }}</h4>
                <span class="badge badge-info bg-info text-white">{{ $modeLabel }}</span>
                <span class="badge badge-{{ $actionType === 'PREVENTIVE' ? 'success' : 'warning' }} bg-{{ $actionType === 'PREVENTIVE' ? 'success text-white' : 'warning text-dark' }}">
                    {{ $actionType }} ACTION
                </span>
            </div>
            <span class="badge badge-{{ $badge }} bg-{{ $badge }} {{ $badge === 'warning' ? 'text-dark' : 'text-white' }} p-2">{{ $status }}</span>
        </div>
        <div class="card-body">
            <input type="hidden" id="ITTicketDetailID" value="{{ $ticket->ID }}" />

            @include('humanresources.ga.ticket.partials.stepper', ['steps' => $progressSteps])

            @if ($status === 'REJECTED' && !empty($ticket->Rejection_Reason))
                <div class="alert alert-danger">
                    <strong>Alasan Penolakan:</strong> {{ $ticket->Rejection_Reason }}
                </div>
            @endif

            @if (!empty($relatedIncident))
                <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <strong>Dieskalasi ke Gangguan Sistem:</strong>
                        {{ $relatedIncident->Ticket_No }} &mdash; {{ $relatedIncident->Ticket_Subject }}
                        <span class="badge bg-dark text-white ms-1">{{ $relatedIncident->Ticket_Status }}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="ITOpenRelated({{ (int) $relatedIncident->ID }})">Buka Incident</button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-7">
                    <h5>{{ $ticket->Ticket_Subject }}</h5>
                    <p class="text-muted mb-1">{{ $ticket->Category_Name ?: 'IT Support' }} {{ $ticket->Subcategory_Name ? '/ ' . $ticket->Subcategory_Name : '' }}</p>
                    <p>{{ $ticket->Ga_Description ?: $ticket->Description }}</p>

                    @if (!empty($ticket->Device_Spec))
                        <div class="border rounded p-2 mb-2 bg-light small">
                            <strong>Spesifikasi Perangkat:</strong> {{ $ticket->Device_Spec }}
                        </div>
                    @endif
                    @if (!empty($ticket->Diagnosis_Note))
                        <div class="border rounded p-2 mb-2 small">
                            <strong>Diagnosa Masalah:</strong> {{ $ticket->Diagnosis_Note }}
                        </div>
                    @endif
                    @if (!empty($ticket->Root_Cause))
                        <div class="border rounded p-2 mb-2 bg-light small">
                            <strong>Root Cause:</strong> {{ $ticket->Root_Cause }}
                        </div>
                    @endif
                    @if (!empty($ticket->Recovery_Note))
                        <div class="border rounded p-2 mb-2 small">
                            <strong>Perbaikan / Recovery:</strong> {{ $ticket->Recovery_Note }}
                        </div>
                    @endif
                    @if (!empty($ticket->Testing_Status))
                        <div class="border rounded p-2 mb-2 small">
                            <strong>Status Pengujian Sistem:</strong>
                            <span class="badge bg-{{ $ticket->Testing_Status === 'NORMAL' ? 'success text-white' : ($ticket->Testing_Status === 'NOT_NORMAL' ? 'danger text-white' : 'warning text-dark') }}">{{ $ticket->Testing_Status }}</span>
                            {{ $ticket->Testing_Note ?: '' }}
                        </div>
                    @endif
                    @if (!empty($ticket->Kb_Flag))
                        <div class="alert alert-success py-2 small mb-2">
                            <strong>Knowledge Base ({{ $ticket->Kb_Title ?: $ticket->Ticket_Subject }}):</strong><br />
                            {{ $ticket->Kb_Solution }}
                        </div>
                    @endif
                    @if (!empty($ticket->User_Confirmed_At))
                        <div class="alert alert-info py-2 small mb-2">
                            <strong>Konfirmasi User ({{ $ticket->User_Confirmed_By }}, Rating: {{ (int) $ticket->User_Rating }}/5):</strong>
                            {{ $ticket->User_Feedback ?: 'Terkonfirmasi selesai.' }}
                        </div>
                    @endif
                    @if (!empty($ticket->Monitoring_Note) || !empty($ticket->Backup_Verified))
                        <div class="border rounded p-2 mb-2 bg-light small">
                            <strong>Monitoring &amp; Backup Verifikasi:</strong>
                            @if (!empty($ticket->Backup_Verified))
                                <span class="badge bg-success text-white ms-1">Backup Terverifikasi ({{ $ticket->Backup_Verified_At }})</span>
                            @endif
                            <div>{{ $ticket->Monitoring_Note ?: '-' }} {{ $ticket->Backup_Note ? ' | Backup: ' . $ticket->Backup_Note : '' }}</div>
                        </div>
                    @endif
                </div>
                <div class="col-md-5">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th style="width:38%">Prioritas</th><td>{{ $ticket->Priority }}</td></tr>
                        <tr><th>Reporter</th><td>{{ $ticket->Reporter_Name ?: $ticket->Reporter }} ({{ $ticket->Reporter_Department ?: $ticket->Department }})</td></tr>
                        <tr><th>Assignee IT</th><td>{{ $ticket->Assignee_Name ?: ($ticket->Team_Name ?: '-') }}</td></tr>
                        <tr><th>SLA</th><td>{{ $ticket->Sla_Name ?: '-' }}</td></tr>
                        <tr><th>Response Due</th><td>{{ $ticket->Response_Due_At ?: '-' }} @if ($sla['Response_Overdue'])<span class="badge badge-danger bg-danger text-white">OVERDUE</span>@endif</td></tr>
                        <tr><th>Resolve Due</th><td>{{ $ticket->Resolve_Due_At ?: '-' }} @if ($sla['Resolve_Overdue'])<span class="badge badge-danger bg-danger text-white">OVERDUE</span>@endif</td></tr>
                        @if ($mode === 'IT_DEVICE_REQUEST')
                            <tr><th>Asset No</th><td>{{ $ticket->Device_Asset_No ?: '-' }}</td></tr>
                            <tr><th>Purchase Request</th><td>{{ $ticket->PR_No ?: '-' }}</td></tr>
                        @endif
                        <tr><th>Dibuat</th><td>{{ $ticket->Created_Date }} oleh {{ $ticket->Created_By }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                @if ($mode === 'IT_DEVICE_REQUEST' && $ticket->Dept_Approval_Request_ID)
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="ITTicketSyncApproval()">Sync Approval Manager</button>
                @endif
                @if ($canEdit && !in_array($status, ['CLOSED', 'REJECTED'], true))
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="$('#ITKbQuickCard').toggle()">Cek Knowledge Base</button>
                @endif
                <button type="button" class="btn btn-secondary btn-sm" onclick="ITTicketCloseDetail()">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Quick Knowledge Base Search Widget --}}
    <div class="card mt-3" id="ITKbQuickCard" style="display:none;">
        <div class="card-header"><h5 class="card-title m-1">Pencarian Cepat Catatan Knowledge Base</h5></div>
        <div class="card-body">
            <div class="input-group input-group-sm mb-2">
                <input type="text" id="ITKbQuickQuery" class="form-control" value="{{ $ticket->Ticket_Subject }}" placeholder="Cari gejala / solusi terdahulu..." />
                <button type="button" class="btn btn-primary" onclick="ITQuickKbSearch()">Cari</button>
            </div>
            <div id="ITKbQuickResult" class="small text-muted">Klik Cari untuk melihat solusi tiket IT terdahulu.</div>
        </div>
    </div>

    {{-- Konfirmasi & Feedback User (saat RESOLVED atau HANDOVER) --}}
    @if ($isReporter && in_array($status, ['RESOLVED', 'HANDOVER'], true))
        <div class="card mt-3 border-success">
            <div class="card-header bg-success text-white"><h5 class="card-title m-1 text-white">Konfirmasi Penyelesaian &amp; Feedback User</h5></div>
            <div class="card-body">
                <form id="ITTicketFeedbackForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label>Hasil Penanganan <span class="text-danger">*</span></label>
                                <select name="Is_Resolved" id="ITFeedbackResolved" class="form-control">
                                    <option value="1">Sudah Selesai / Perangkat Sesuai</option>
                                    <option value="0">Belum Selesai (Buka Ulang Tiket)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3" id="ITFeedbackRatingWrap">
                            <div class="form-group mb-3">
                                <label>Rating Layanan (1 - 5) <span class="text-danger">*</span></label>
                                <select name="User_Rating" class="form-control">
                                    <option value="5">5 - Sangat Puas</option>
                                    <option value="4">4 - Puas</option>
                                    <option value="3">3 - Cukup</option>
                                    <option value="2">2 - Kurang</option>
                                    <option value="1">1 - Tidak Puas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Feedback / Catatan User</label>
                                <textarea name="User_Feedback" class="form-control" rows="2" maxlength="5000" placeholder="Tuliskan feedback atau kendala bila masih ada..."></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">Kirim Konfirmasi</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Pengajuan Perangkat IT: Assign Asset & Serah Terima / Eskalasi PR --}}
    @if ($canEdit && $mode === 'IT_DEVICE_REQUEST' && in_array($status, ['OPEN', 'DEVICE_PREPARATION', 'ASSIGNED_TO_USER', 'ON_HOLD'], true))
        <div class="card mt-3 border-info">
            <div class="card-header bg-info text-white"><h5 class="card-title m-1 text-white">Persiapan, Assign &amp; Serah Terima Perangkat IT</h5></div>
            <div class="card-body">
                <form id="ITDeviceHandoverForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <input type="hidden" name="action" id="ITDeviceActionInput" value="handover" />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Pilih Perangkat dari Master Asset (asset_list) <span class="text-danger">*</span></label>
                                <select name="Device_Asset_No" class="form-control" required>
                                    <option value="">-- pilih aset --</option>
                                    @foreach ($assets as $asset)
                                        <option value="{{ $asset->Asset_No }}" {{ $ticket->Device_Asset_No === $asset->Asset_No ? 'selected' : '' }}>
                                            {{ $asset->Asset_No }} - {{ $asset->Product_Description }} ({{ $asset->Asset_Location ?: '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Penerima Perangkat (User)</label>
                                <select name="Device_Assigned_User_Id" class="form-control">
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}" {{ (int) ($ticket->Device_Assigned_User_Id ?: $ticket->Reporter_User_Id) === (int) $u->id ? 'selected' : '' }}>
                                            {{ $u->Name ?: $u->username }} ({{ $u->Department }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label>Catatan Serah Terima</label>
                                <input type="text" name="Handover_Note" class="form-control" maxlength="5000" placeholder="Contoh: Unit + Charger + Tas diserahkan kondisi baik" />
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="$('#ITDeviceActionInput').val('assign'); $('#ITDeviceHandoverForm').submit();">Assign ke User</button>
                        <button type="button" class="btn btn-info btn-sm text-white" onclick="$('#ITDeviceActionInput').val('handover'); $('#ITDeviceHandoverForm').submit();">Serah Terima &amp; Sync Aset</button>
                        @if (empty($ticket->PR_No))
                            <button type="button" class="btn btn-outline-warning btn-sm text-dark" onclick="$('#ITDevicePrCard').toggle()">Stok Kosong? Ajukan Purchase Request (PR)</button>
                        @endif
                    </div>
                </form>

                @if (empty($ticket->PR_No))
                    <div id="ITDevicePrCard" class="border rounded p-3 mt-3 bg-light" style="display:none;">
                        <h6>Ajukan Purchase Request Pengadaan Perangkat</h6>
                        <form id="ITDevicePrForm">
                            @csrf
                            <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group mb-2">
                                        <label>Nama Barang <span class="text-danger">*</span></label>
                                        <input type="text" name="Item_Name" class="form-control form-control-sm" value="{{ $ticket->Ticket_Subject }}" required />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-2">
                                        <label>Qty</label>
                                        <input type="number" name="Qty" class="form-control form-control-sm" value="1" min="1" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label>Estimasi Harga Satuan</label>
                                        <input type="number" name="Est_Price" class="form-control form-control-sm" value="0" min="0" />
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-warning btn-sm text-dark mb-2">Buat PR</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Shortcut Testing Sistem untuk Gangguan Sistem --}}
    @if ($canEdit && $mode === 'IT_INCIDENT' && $status === 'TESTING')
        <div class="card mt-3 border-primary">
            <div class="card-header bg-primary text-white"><h5 class="card-title m-1 text-white">Hasil Pengujian Sistem (Update Normal?)</h5></div>
            <div class="card-body">
                <div class="form-group mb-2">
                    <label>Catatan Hasil Pengujian <span class="text-danger">*</span></label>
                    <input type="text" id="ITTestingResultNote" class="form-control" placeholder="Tuliskan hasil testing server / jaringan / aplikasi..." />
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-success btn-sm" onclick="ITQuickTestingDecision('SYSTEM_NORMAL')">Ya — Sistem Normal</button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="ITQuickTestingDecision('RECOVERY')">Tidak — Perbaikan Lagi (Recovery)</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Form Transisi & Tracking IT Support --}}
    @if ($canEdit && !empty($allowedTransitions))
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title m-1">Tindak Lanjut &amp; Ubah Status IT</h5></div>
            <div class="card-body">
                <form id="ITTicketTransitionForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Tahap / Status Baru</label>
                                <select name="status" id="ITTicketNextStatus" class="form-control">
                                    @foreach ($allowedTransitions as $tr)
                                        <option value="{{ $tr }}">{{ $tr }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label>Catatan Penanganan</label>
                                <input type="text" name="note" id="ITTicketTransitionNote" class="form-control" maxlength="5000" placeholder="Catatan progres / penyelesaian..." />
                            </div>
                        </div>

                        <div class="col-md-12 ITFieldGroup" data-for="TROUBLESHOOTING" style="display:none;">
                            <div class="form-group mb-3">
                                <label>Diagnosa Masalah <span class="text-danger">*</span></label>
                                <textarea name="Diagnosis_Note" class="form-control" rows="2" maxlength="5000" placeholder="Hasil analisa awal / diagnosa masalah perangkat atau sistem...">{{ $ticket->Diagnosis_Note }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12 ITFieldGroup" data-for="ROOT_CAUSE_ANALYSIS" style="display:none;">
                            <div class="form-group mb-3">
                                <label>Analisis Root Cause <span class="text-danger">*</span></label>
                                <textarea name="Root_Cause" class="form-control" rows="2" maxlength="5000" placeholder="Akar penyebab gangguan sistem...">{{ $ticket->Root_Cause }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12 ITFieldGroup" data-for="RECOVERY" style="display:none;">
                            <div class="form-group mb-3">
                                <label>Catatan Perbaikan / Recovery <span class="text-danger">*</span></label>
                                <textarea name="Recovery_Note" class="form-control" rows="2" maxlength="5000" placeholder="Tindakan perbaikan server / jaringan / sistem...">{{ $ticket->Recovery_Note }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12 ITFieldGroup" data-for="TESTING" style="display:none;">
                            <div class="form-group mb-3">
                                <label>Rencana / Catatan Testing Sistem <span class="text-danger">*</span></label>
                                <textarea name="Testing_Note" class="form-control" rows="2" maxlength="5000" placeholder="Hasil pengujian pasca perbaikan...">{{ $ticket->Testing_Note }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12 ITFieldGroup" data-for="RESOLVED,SYSTEM_NORMAL,CLOSED" style="display:none;">
                            <div class="border rounded p-2 mb-3 bg-light">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="Kb_Flag" value="1" class="form-check-input" id="ITKbFlagCheck" {{ !empty($ticket->Kb_Flag) ? 'checked' : '' }} />
                                    <label class="form-check-label fw-semibold" for="ITKbFlagCheck">Simpan solusi ini sebagai Catatan Knowledge Base (KB)</label>
                                </div>
                                <div class="row" id="ITKbFieldsWrap" style="{{ !empty($ticket->Kb_Flag) ? '' : 'display:none;' }}">
                                    <div class="col-md-5">
                                        <input type="text" name="Kb_Title" class="form-control form-control-sm mb-2" value="{{ $ticket->Kb_Title ?: $ticket->Ticket_Subject }}" placeholder="Judul Knowledge Base" />
                                    </div>
                                    <div class="col-md-7">
                                        <input type="text" name="Kb_Solution" class="form-control form-control-sm mb-2" value="{{ $ticket->Kb_Solution }}" placeholder="Ringkasan langkah solusi untuk referensi KB" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 ITFieldGroup" data-for="CLOSED" style="display:none;">
                            <div class="border rounded p-2 mb-3 bg-light">
                                <h6 class="mb-2">Catatan Monitoring, Maintenance &amp; Backup Verifikasi</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" name="Monitoring_Note" class="form-control form-control-sm mb-2" value="{{ $ticket->Monitoring_Note }}" placeholder="Catatan monitoring & maintenance pasca penanganan" />
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-1">
                                            <input type="checkbox" name="Backup_Verified" value="1" class="form-check-input" id="ITBackupVerifiedCheck" {{ !empty($ticket->Backup_Verified) ? 'checked' : '' }} />
                                            <label class="form-check-label" for="ITBackupVerifiedCheck">Backup &amp; Verifikasi Terkonfirmasi</label>
                                        </div>
                                        <input type="text" name="Backup_Note" class="form-control form-control-sm" value="{{ $ticket->Backup_Note }}" placeholder="Catatan backup / verifikasi (opsional)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">Simpan Perubahan Status</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Eskalasi Tiket Bantuan IT --}}
    @if ($canEdit && $mode === 'IT_SUPPORT' && in_array($status, ['IN_PROGRESS', 'TROUBLESHOOTING', 'ESCALATED', 'ON_HOLD'], true))
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title m-1">Eskalasi Masalah IT</h5></div>
            <div class="card-body">
                <form id="ITTicketEscalateForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label>Alasan Eskalasi <span class="text-danger">*</span></label>
                                <input type="text" name="Escalation_Note" class="form-control" maxlength="5000" required placeholder="Contoh: Perlu penanganan tim Infrastruktur / Gangguan Sistem" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-2">
                                <label>Eskalasi ke Team</label>
                                <select name="Assignee_Team_ID" class="form-control">
                                    <option value="">-- tetap --</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->ID }}">{{ $team->Name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input type="checkbox" name="Spawn_Incident" value="1" class="form-check-input" id="ITSpawnIncidentCheck" />
                                <label class="form-check-label" for="ITSpawnIncidentCheck">Buat Tiket Gangguan Sistem</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm">Eskalasi Tiket</button>
                </form>
            </div>
        </div>
    @endif

    {{-- Assign / Reassign IT --}}
    @if ($canEdit && !in_array($status, ['CLOSED', 'REJECTED'], true))
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title m-1">Assign / Reassign Teknisi IT</h5></div>
            <div class="card-body">
                <form id="ITTicketAssignForm">
                    @csrf
                    <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                    <div class="row">
                        <div class="col-md-3">
                            <select name="Assignee_Type" id="ITAssignType" class="form-control">
                                <option value="USER">User / Teknisi IT</option>
                                <option value="TEAM">Team IT</option>
                            </select>
                        </div>
                        <div class="col-md-4 ITAssignField" data-type="USER">
                            <select name="Assignee_User_Id" class="form-control">
                                <option value="">-- pilih teknisi --</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->Name ?: $u->username }} ({{ $u->Department }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 ITAssignField" data-type="TEAM" style="display:none;">
                            <select name="Assignee_Team_ID" class="form-control">
                                <option value="">-- pilih team --</option>
                                @foreach ($teams as $t)
                                    <option value="{{ $t->ID }}">{{ $t->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="Reason" class="form-control" placeholder="Alasan assignment (opsional)" />
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Assign</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Komentar --}}
    <div class="card mt-3">
        <div class="card-header"><h5 class="card-title m-1">Komentar &amp; Komunikasi</h5></div>
        <div class="card-body">
            <form id="ITTicketCommentForm">
                @csrf
                <input type="hidden" name="ID" value="{{ $ticket->ID }}" />
                <div class="form-group mb-2">
                    <textarea name="Comment" class="form-control" rows="2" maxlength="5000" required placeholder="Tulis pesan atau update progres..."></textarea>
                </div>
                @if ($canEdit)
                    <div class="form-check mb-2">
                        <input type="checkbox" name="Is_Internal" value="1" class="form-check-input" id="ITTicketInternalComment" />
                        <label class="form-check-label" for="ITTicketInternalComment">Catatan internal IT</label>
                    </div>
                @endif
                <button type="submit" class="btn btn-info btn-sm text-white">Kirim Komentar</button>
            </form>
        </div>
    </div>

    {{-- Tabs History, Komentar, Assignment, Lampiran, Approval --}}
    <div class="card mt-3">
        <div class="card-body">
            <ul class="nav nav-tabs" id="ITTicketDetailTabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" href="#ITTabHistory" role="tab">Riwayat &amp; Logs ({{ $history->count() }})</a></li>
                <li class="nav-item"><a class="nav-link" href="#ITTabComments" role="tab">Komentar ({{ $comments->count() }})</a></li>
                <li class="nav-item"><a class="nav-link" href="#ITTabAssignments" role="tab">Assignment ({{ $assignments->count() }})</a></li>
                <li class="nav-item"><a class="nav-link" href="#ITTabAttachments" role="tab">Lampiran ({{ $attachments->count() }})</a></li>
                @if (!empty($approvalTimeline))
                    <li class="nav-item"><a class="nav-link" href="#ITTabApproval" role="tab">Approval Manager</a></li>
                @endif
            </ul>
            <div class="tab-content pt-3" id="ITTicketDetailTabContent">
                <div class="tab-pane fade show active" id="ITTabHistory">
                    <table class="table table-sm">
                        <thead><tr><th>Waktu</th><th>Dari</th><th>Ke</th><th>Oleh</th><th>Catatan</th></tr></thead>
                        <tbody>
                        @foreach ($history as $row)
                            <tr>
                                <td>{{ $row->Changed_At }}</td>
                                <td>{{ $row->From_Status ?: '-' }}</td>
                                <td><span class="badge bg-secondary text-white">{{ $row->To_Status }}</span></td>
                                <td>{{ $row->Changed_By }}</td>
                                <td>{{ $row->Note }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="ITTabComments">
                    @foreach ($comments as $comment)
                        <div class="border-bottom pb-2 mb-2">
                            <strong>{{ $comment->User_Name ?: $comment->Created_By }}</strong>
                            <small class="text-muted">{{ $comment->Created_At }}</small>
                            @if ($comment->Is_Internal)<span class="badge badge-warning bg-warning text-dark">INTERNAL</span>@endif
                            <div>{{ $comment->Comment }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="tab-pane fade" id="ITTabAssignments">
                    <table class="table table-sm">
                        <thead><tr><th>Waktu</th><th>Tipe</th><th>Assignee</th><th>Oleh</th><th>Alasan</th><th>Current</th></tr></thead>
                        <tbody>
                        @foreach ($assignments as $a)
                            <tr>
                                <td>{{ $a->Assigned_At }}</td>
                                <td>{{ $a->Assignee_Type }}</td>
                                <td>{{ $a->User_Name ?: ($a->Team_Name ?: '-') }}</td>
                                <td>{{ $a->Assigned_By }}</td>
                                <td>{{ $a->Reason }}</td>
                                <td>{{ $a->Is_Current ? 'YES' : 'NO' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="ITTabAttachments">
                    @if ($attachments->isEmpty())
                        <div class="text-muted small">Tidak ada lampiran.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($attachments as $att)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span>{{ $att->File_Name }} <small class="text-muted">({{ number_format(($att->File_Size ?: 0) / 1024, 1) }} KB, {{ $att->Uploaded_By }})</small></span>
                                    <a href="{{ url('hrd-it-ticket-attachment?attachment_id=' . $att->ID) }}" target="_blank" class="btn btn-xs btn-outline-primary">Unduh</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                @if (!empty($approvalTimeline))
                    <div class="tab-pane fade" id="ITTabApproval">
                        <div class="mb-2 small">
                            <strong>Request No:</strong> {{ $approvalTimeline['request']->Request_No }}
                            &bull; <strong>Status:</strong> <span class="badge bg-info text-white">{{ $approvalTimeline['request']->Status }}</span>
                        </div>
                        <table class="table table-sm">
                            <thead><tr><th>Stage</th><th>Aksi</th><th>Oleh</th><th>Catatan</th><th>Waktu</th></tr></thead>
                            <tbody>
                            @foreach ($approvalTimeline['logs'] as $log)
                                <tr>
                                    <td>{{ $log->Stage_Order ?: '-' }}</td>
                                    <td><span class="badge bg-primary text-white">{{ $log->Action }}</span></td>
                                    <td>{{ $log->Actor_Name ?: ($log->Actor_Username ?: 'System') }}</td>
                                    <td>{{ $log->Notes ?: '-' }}</td>
                                    <td>{{ $log->Action_Date }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var ticketId = {{ (int) $ticket->ID }};

    $('#ITTicketDetailTabs a[href^="#"]').on('click', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');
        $('#ITTicketDetailTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        $('#ITTicketDetailTabContent > .tab-pane').removeClass('show active');
        $(target).addClass('show active');
    });

    window.ITTicketCloseDetail = function () {
        if ($('#ITTICKETDTL').length) {
            $('#ITTICKETDTL').empty();
        } else if ($('#ITMYASSIGNDTL').length) {
            $('#ITMYASSIGNDTL').empty();
        } else {
            window.location.href = '{{ url('hrd-it-ticket-index') }}';
        }
    };

    window.ITOpenRelated = function (id) {
        if ($('#ITTICKETDTL').length) {
            LoadM('ITTICKETDTL', 'hrd-it-ticket-detail?partial=1&ID=' + encodeURIComponent(id));
        } else {
            window.location.href = '{{ url('hrd-it-ticket-detail') }}?ID=' + encodeURIComponent(id);
        }
    };

    function syncTransitionFields() {
        var st = $('#ITTicketNextStatus').val() || '';
        $('.ITFieldGroup').each(function () {
            var targets = String($(this).data('for') || '').split(',');
            var match = targets.indexOf(st) !== -1;
            $(this).toggle(match);
        });
    }
    $('#ITTicketNextStatus').on('change', syncTransitionFields);
    syncTransitionFields();

    $('#ITKbFlagCheck').on('change', function () {
        $('#ITKbFieldsWrap').toggle(this.checked);
    });

    $('#ITAssignType').on('change', function () {
        var t = $(this).val();
        $('.ITAssignField').each(function () {
            $(this).toggle($(this).data('type') === t);
        });
    });

    $('#ITFeedbackResolved').on('change', function () {
        $('#ITFeedbackRatingWrap').toggle($(this).val() === '1');
    });

    window.ITQuickKbSearch = function () {
        var q = $('#ITKbQuickQuery').val() || '';
        $.get('hrd-it-ticket-kb-search', {q: q}, function (res) {
            var rows = (res && res.items) ? res.items : [];
            if (!rows.length) {
                $('#ITKbQuickResult').html('<span class="text-muted">Tidak ada artikel KB yang cocok.</span>');
                return;
            }
            var html = '<div class="list-group">';
            rows.forEach(function (r) {
                html += '<div class="list-group-item py-2">' +
                    '<div class="fw-bold">' + $('<div>').text(r.Kb_Title || r.Ticket_Subject).html() + ' <small class="text-muted">(' + r.Ticket_No + ')</small></div>' +
                    '<div><strong>Solusi:</strong> ' + $('<div>').text(r.Kb_Solution || '-').html() + '</div>' +
                    '</div>';
            });
            html += '</div>';
            $('#ITKbQuickResult').html(html);
        });
    };

    function afterChange(message) {
        alert(message);
        if (window.ITTicketReloadDetail) {
            window.ITTicketReloadDetail(ticketId);
        } else {
            location.reload();
        }
        if (window.ITTicketRefreshGrid) {
            window.ITTicketRefreshGrid();
        }
    }

    function postForm(formSelector, url, defaultSuccess) {
        var form = $(formSelector);
        var btn = form.find('button[type=submit]');
        btn.prop('disabled', true);
        $.post(url, form.serialize(), function (res) {
            if (res.status === 'success') {
                afterChange(res.message || defaultSuccess);
            } else {
                alert(res.message || 'Gagal memproses.');
            }
        }).fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memproses.');
        }).always(function () {
            btn.prop('disabled', false);
        });
    }

    window.ITQuickTestingDecision = function (targetStatus) {
        var note = $('#ITTestingResultNote').val() || '';
        if (!note.trim()) {
            alert('Catatan hasil pengujian sistem wajib diisi.');
            return;
        }
        $.post('hrd-it-ticket-status', {
            _token: '{{ csrf_token() }}',
            ID: ticketId,
            status: targetStatus,
            note: note,
            Testing_Note: note,
            Recovery_Note: note
        }, function (res) {
            if (res.status === 'success') afterChange(res.message);
            else alert(res.message || 'Gagal memperbarui status.');
        }).fail(function (xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal memperbarui status.');
        });
    };

    window.ITTicketSyncApproval = function () {
        $.post('hrd-it-ticket-approval-sync', {_token: '{{ csrf_token() }}', ID: ticketId}, function (res) {
            if (res.status === 'success') afterChange(res.message);
        });
    };

    $('#ITTicketTransitionForm').on('submit', function (e) {
        e.preventDefault();
        postForm('#ITTicketTransitionForm', 'hrd-it-ticket-status', 'Status tiket IT diperbarui.');
    });

    $('#ITTicketEscalateForm').on('submit', function (e) {
        e.preventDefault();
        postForm('#ITTicketEscalateForm', 'hrd-it-ticket-escalate', 'Tiket dieskalasi.');
    });

    $('#ITTicketFeedbackForm').on('submit', function (e) {
        e.preventDefault();
        postForm('#ITTicketFeedbackForm', 'hrd-it-ticket-feedback', 'Feedback tersimpan.');
    });

    $('#ITDeviceHandoverForm').on('submit', function (e) {
        e.preventDefault();
        postForm('#ITDeviceHandoverForm', 'hrd-it-ticket-device-handover', 'Perangkat diproses.');
    });

    $('#ITDevicePrForm').on('submit', function (e) {
        e.preventDefault();
        postForm('#ITDevicePrForm', 'hrd-it-ticket-device-pr', 'PR dibuat.');
    });

    $('#ITTicketAssignForm').on('submit', function (e) {
        e.preventDefault();
        postForm('#ITTicketAssignForm', 'hrd-it-ticket-assign', 'Assignment disimpan.');
    });

    $('#ITTicketCommentForm').on('submit', function (e) {
        e.preventDefault();
        postForm('#ITTicketCommentForm', 'hrd-it-ticket-comment', 'Komentar dikirim.');
    });
})();
</script>
