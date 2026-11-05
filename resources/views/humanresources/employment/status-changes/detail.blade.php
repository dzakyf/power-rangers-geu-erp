<div class="main-content-header">
    <h1>FORM PERUBAHAN STATUS KARYAWAN</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard1">Human Resources</a></li>
        <li class="breadcrumb-item active"><span class="active">Detail {{ $sc->form_number ?: '#' . $sc->id }}</span></li>
    </ol>
</div>

<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white font-weight-bold p-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0" style="color:azure"><i class="fas fa-file-alt mr-2"></i>Detail Form Perubahan Status</h5>
        <div>
            @if(in_array(strtoupper((string) ($sc->status ?: 'DRAFT')), ['DRAFT', 'REJECTED']))
                <button type="button" class="btn btn-success btn-sm mr-2" id="btnDetailSubmitApproval" onclick="submitStatusChangeApproval({{ $sc->id }})">
                    <i class="fas fa-paper-plane"></i> Ajukan Approval
                </button>
            @endif
            <button type="button" class="btn btn-warning btn-sm mr-2" onclick="window.location.href='hrd-status-change-edit?ID={{ $sc->id }}'">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button type="button" class="btn btn-info btn-sm mr-2" onclick="window.open('hrd-status-change-print?ID={{ $sc->id }}', '_blank')">
                <i class="fas fa-file-pdf"></i> Print
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='hrd-status-change-index?jenis={{ $sc->jenis ?: 'MUTATION' }}'">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
        </div>
    </div>
    <div class="card-body">
        <dl class="row small">
            <dt class="col-md-3">Nomor Form</dt><dd class="col-md-9">{{ $sc->form_number ?: '-' }}</dd>
            <dt class="col-md-3">Status Pengajuan</dt>
            <dd class="col-md-9">
                @php
                    $badgeMap = [
                        'DRAFT'       => 'badge-secondary bg-secondary text-white',
                        'SUBMITTED'   => 'badge-primary bg-primary text-white',
                        'IN_APPROVAL' => 'badge-info bg-info text-white',
                        'APPROVED'    => 'badge-success bg-success text-white',
                        'REJECTED'    => 'badge-danger bg-danger text-white',
                        'CANCELLED'   => 'badge-dark bg-dark text-white',
                    ];
                    $st = strtoupper((string) ($sc->status ?: 'DRAFT'));
                    $badgeCls = $badgeMap[$st] ?? 'badge-secondary bg-secondary text-white';
                @endphp
                <span class="badge {{ $badgeCls }} px-2 py-1">{{ $st }}</span>
                @if($sc->approval_request_id)
                    <span class="text-muted ms-2">(Approval Request #{{ $sc->approval_request_id }})</span>
                @endif
            </dd>
            <dt class="col-md-3">Jenis Perubahan</dt><dd class="col-md-9">{{ $sc->jenis ?: 'MUTATION' }} <small class="text-muted">{{ ($sc->jenis ?: 'MUTATION') == 'PROMOTION' ? '(Promosi)' : '(Mutasi)' }}</small></dd>
            <dt class="col-md-3">Nama Karyawan</dt><dd class="col-md-9">{{ $sc->employee_name }}</dd>
            <dt class="col-md-3">NIK</dt><dd class="col-md-9">{{ $sc->nik }}</dd>
            <dt class="col-md-3">Tanggal Join</dt><dd class="col-md-9">{{ $sc->join_date ? \Carbon\Carbon::parse($sc->join_date)->format('d/m/Y') : '-' }}</dd>
            <dt class="col-md-3">Tanggal Efektif</dt><dd class="col-md-9">{{ $sc->effective_date ? \Carbon\Carbon::parse($sc->effective_date)->format('d/m/Y') : '-' }}</dd>
        </dl>

        @php
            $comparerFields = [
                'position'          => 'Jabatan',
                'division'          => 'Divisi',
                'department'        => 'Departemen',
                'grade'             => 'Grade',
                'work_location'     => 'Lokasi Kerja',
                'employment_status' => 'Status Kekaryawanan',
                'marital_dependents'=> 'Status Perkawinan & Tanggungan',
                'salary'            => 'Salary (Rp)',
                'benefit'           => 'Benefit',
                'description'       => 'Deskripsi',
            ];
        @endphp

        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr><th style="width:30%">Item</th><th style="width:35%">Saat Ini</th><th style="width:35%">Usulan</th></tr>
                </thead>
                <tbody>
                    @foreach($comparerFields as $field => $label)
                    <tr>
                        <th>{{ $label }}</th>
                        @if($field == 'position') 
                        <td>{{ $masterPositions[$sc->{'current_' . $field}] ?? ($sc->{'current_' . $field} ?: '-') }}</td>
                        <td>{{ $masterPositions[$sc->{'proposed_' . $field}] ?? ($sc->{'proposed_' . $field} ?: '-') }}</td>
                        @else
                        <td>{{ $sc->{'current_' . $field} ?: '-' }}</td>
                        <td>{{ $sc->{'proposed_' . $field} ?: '-' }}</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h6 style="font-weight:bold; margin:16px 0 10px 0;"><i class="fas fa-tasks mr-1"></i> Alur Persetujuan (Approval Policy Engine)</h6>
        @if(!empty($tasks) && count($tasks) > 0)
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 8%">Stage</th>
                            <th style="width: 25%">Peran / Level Approver</th>
                            <th style="width: 20%">Status Task</th>
                            <th style="width: 25%">Petugas / Diproses Oleh</th>
                            <th style="width: 22%">Waktu Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $t)
                        @php
                            $taskSt = strtoupper((string) $t->Status);
                            $badgeT = 'badge-secondary bg-secondary text-white';
                            if ($taskSt === 'PENDING') $badgeT = 'badge-warning bg-warning text-dark';
                            elseif ($taskSt === 'CLAIMED') $badgeT = 'badge-info bg-info text-white';
                            elseif ($taskSt === 'APPROVED') $badgeT = 'badge-success bg-success text-white';
                            elseif ($taskSt === 'REJECTED') $badgeT = 'badge-danger bg-danger text-white';
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold">Stage {{ $t->Stage_Order }}</td>
                            <td>{{ $t->Label ?? ($t->Position_Name ?: 'Level ' . $t->Level_Number) }}</td>
                            <td><span class="badge {{ $badgeT }} px-2 py-1">{{ $taskSt }}</span></td>
                            <td>{{ ($t->Acted_By_Name ?? $t->Claimed_By_Name) ? ($t->Acted_By_Name ?: $t->Claimed_By_Name) . ' (' . ($t->Acted_By_Username ?: $t->Claimed_By_Username) . ')' : '-' }}</td>
                            <td>{{ $t->Acted_At ? \Carbon\Carbon::parse($t->Acted_At)->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-light border small mb-3">
                <i class="fas fa-info-circle text-info mr-1"></i> Form ini belum diajukan ke Approval Engine atau masih berstatus Draft.
            </div>
        @endif

        {{-- Panel Aksi Persetujuan jika User memiliki Pending Task --}}
        @if(!empty($pendingTask))
            <div class="card border-warning mb-3">
                <div class="card-header bg-warning text-dark font-weight-bold">
                    <i class="fas fa-user-check mr-1"></i> Tindakan Persetujuan Anda (Stage {{ $pendingTask->Stage_Order }} - {{ $pendingTask->Label ?? ($pendingTask->Position_Name ?: 'Approver') }})
                    @if(strtoupper((string) $pendingTask->Status) === 'CLAIMED')
                        <span class="badge bg-dark text-white ms-2">Telah Anda Claim</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Catatan / Alasan (Opsional untuk Approve, Wajib untuk Reject):</label>
                        <textarea id="apprActionNotes" class="form-control form-control-sm" rows="2" placeholder="Tuliskan catatan..."></textarea>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success btn-sm me-2" onclick="actStatusChangeApproval({{ $sc->id }}, 'APPROVE')">
                            <i class="fas fa-check mr-1"></i> Setujui (Approve)
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="actStatusChangeApproval({{ $sc->id }}, 'REJECT')">
                            <i class="fas fa-times mr-1"></i> Tolak (Reject)
                        </button>
                    </div>
                </div>
            </div>
        @elseif(!empty($claimedByOther))
            <div class="alert alert-warning border-warning small mb-3">
                <i class="fas fa-lock mr-1"></i>
                <strong>Task Sedang Di-claim Approver Lain:</strong>
                Tugas persetujuan pada Stage {{ $claimedByOther->Stage_Order }} ({{ $claimedByOther->Label ?? ($claimedByOther->Position_Name ?: 'Approver') }}) saat ini sedang di-claim oleh
                <strong>{{ $claimedByOther->Claimed_By_Name ?: $claimedByOther->Claimed_By_Username }}</strong>
                @if($claimedByOther->Claimed_At)
                    pada {{ \Carbon\Carbon::parse($claimedByOther->Claimed_At)->format('d/m/Y H:i') }}
                @endif.
                Tombol persetujuan dikunci untuk mencegah konflik/double-approval.
            </div>
        @endif

        {{-- Log Riwayat Approval --}}
        @if(!empty($logs) && count($logs) > 0)
            <h6 style="font-weight:bold; margin:16px 0 10px 0;"><i class="fas fa-history mr-1"></i> Catatan & Riwayat Aktivitas Approval</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm small">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 20%">Waktu</th>
                            <th style="width: 25%">Pengguna</th>
                            <th style="width: 20%">Aksi</th>
                            <th style="width: 35%">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $l)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($l->Action_Date)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $l->User_Name ? $l->User_Name . ' (' . $l->Username . ')' : ($l->Username ?: 'System') }}</td>
                            <td><span class="badge badge-info bg-info text-white">{{ $l->Action }}</span></td>
                            <td>{{ $l->Notes ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h6 style="font-weight:bold; margin:16px 0 10px 0;"><i class="fas fa-file-signature mr-1"></i> Lembar Tanda Tangan Dokumen</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr><th>Diusulkan oleh</th><th>Dept. Head Asal</th><th>Dept. Head Tujuan</th><th>Diverifikasi HCGA</th><th>Mengetahui</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $sc->proposed_by ?: '-' }}</td>
                        <td>{{ $sc->origin_department_head ?: '-' }}</td>
                        <td>{{ $sc->destination_department_head ?: '-' }}</td>
                        <td>{{ $sc->hcga_verifier ?: '-' }}</td>
                        <td>{{ $sc->director_approver ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function submitStatusChangeApproval(id) {
    if (!confirm('Ajukan form perubahan status ini ke Approval Engine?')) return;
    $.blockUI({ message: '<div class="small">Mengajukan approval...</div>' });
    $.ajax({
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        url: 'hrd-status-change-submit-approval',
        type: 'POST',
        data: { ID: id },
        success: function(res) {
            $.unblockUI();
            if (res.status === 'success') {
                alert(res.message || 'Form berhasil diajukan!');
                LoadM('STCH_DTL', 'hrd-status-change-detail?ID=' + id);
                if (typeof STCHLoad === 'function') STCHLoad();
            } else {
                alert(res.message || 'Gagal mengajukan approval.');
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            $.unblockUI();
            alert('Error: ' + (xhr.responseText || errorThrown));
        }
    });
}

function actStatusChangeApproval(id, action) {
    var notes = $('#apprActionNotes').val() || '';
    if (action === 'REJECT' && $.trim(notes) === '') {
        alert('Mohon tuliskan alasan penolakan pada kolom catatan.');
        $('#apprActionNotes').focus();
        return;
    }

    var confirmMsg = action === 'APPROVE' ? 'Setujui form perubahan status ini?' : 'Tolak form perubahan status ini?';
    if (!confirm(confirmMsg)) return;

    $.blockUI({ message: '<div class="small">Memproses persetujuan...</div>' });
    $.ajax({
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        url: 'hrd-status-change-action',
        type: 'POST',
        data: {
            ID: id,
            action: action,
            notes: notes
        },
        success: function(res) {
            $.unblockUI();
            if (res.status === 'success') {
                alert(res.message || 'Persetujuan berhasil diproses!');
                LoadM('STCH_DTL', 'hrd-status-change-detail?ID=' + id);
                if (typeof STCHLoad === 'function') STCHLoad();
            } else {
                alert(res.message || 'Gagal memproses persetujuan.');
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            $.unblockUI();
            alert('Error: ' + (xhr.responseText || errorThrown));
        }
    });
}
</script>