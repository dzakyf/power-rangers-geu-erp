@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @php
        $backRoute = $doc->module_type === 'REGULATION'
            ? route('hrd.storage.regulation.index')
            : route('hrd.storage.information.index');
        $statusBadgeMap = [
            'ACTIVE'           => 'bg-success text-white',
            'SCHEDULED'        => 'bg-info text-white',
            'EXPIRED'          => 'bg-danger text-white',
            'WAITING_APPROVAL' => 'bg-warning text-dark',
            'DRAFT'            => 'bg-secondary text-white',
            'REJECTED'         => 'bg-danger text-white',
            'REVOKED'          => 'bg-dark text-white',
        ];
        $statusClass = $statusBadgeMap[$effectiveStatus] ?? 'bg-secondary text-white';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <a href="{{ $backRoute }}" class="btn btn-sm btn-outline-secondary rounded-pill">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </a>
                            <span class="badge bg-primary text-white">{{ $doc->module_type }}</span>
                            <span class="badge bg-info text-white">{{ \App\Models\HCGA\StorageDocument::getSubTypeLabel($doc->sub_type) }}</span>
                            @if($currentVersion)
                                <span class="badge bg-dark text-white">{{ $currentVersion->version_label }}</span>
                            @endif
                            <span class="badge {{ $statusClass }}">{{ $effectiveStatus }}</span>
                            @if($doc->document_number)
                                <span class="badge bg-light text-dark border">{{ $doc->document_number }}</span>
                            @endif
                        </div>
                        <h4 class="fw-bold text-dark mb-0 mt-2">{{ $doc->title }}</h4>
                    </div>

                    @if($isHcga)
                        <div class="d-flex flex-wrap gap-2">
                            @if($doc->status !== 'REVOKED')
                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="openVersionModal({{ $doc->id }})">
                                    <i class="fa fa-code-branch me-1"></i> Buat Versi Baru (v{{ $doc->versions->max('version_number') + 1 }})
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="openEditModal({{ $doc->id }})">
                                    <i class="fa fa-edit me-1"></i> Edit / Metadata
                                </button>
                                @if($effectiveStatus !== 'REVOKED')
                                    <button type="button" class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3" onclick="revokeDocument({{ $doc->id }})">
                                        <i class="fa fa-ban me-1"></i> Cabut (Revoke)
                                    </button>
                                @endif
                            @endif
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="deleteDocument({{ $doc->id }})">
                                <i class="fa fa-trash me-1"></i> Hapus
                            </button>
                        </div>
                    @endif
                </div>

                <div class="card-body">
                    @if($doc->status === 'REVOKED')
                        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
                            <i class="fa fa-exclamation-triangle fa-lg"></i>
                            <div>
                                <strong>Dokumen Dicabut ({{ $doc->revoked_at }}) oleh {{ $doc->revoked_by }}:</strong>
                                {{ $doc->revoked_reason }}
                            </div>
                        </div>
                    @endif

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tabOverview" type="button">
                                <i class="fa fa-file-alt me-1"></i> Ringkasan & Preview
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tabVersions" type="button">
                                <i class="fa fa-history me-1"></i> Riwayat Versi ({{ $doc->versions->count() }})
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tabReaders" type="button">
                                <i class="fa fa-check-double me-1"></i> Tracking Pembaca ({{ $readReport['total_acknowledged'] }}/{{ $readReport['total_read'] }})
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tabApproval" type="button">
                                <i class="fa fa-user-check me-1"></i> Riwayat Approval ({{ $approvalRequests->count() }})
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- TAB 1: Overview & Preview -->
                        <div class="tab-pane fade show active" id="tabOverview">
                            @if($currentVersion)
                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded bg-light">
                                            <div class="small text-muted">Masa Berlaku (Ketetapan)</div>
                                            <div class="fw-bold text-dark">{{ $currentVersion->validity_label }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded bg-light">
                                            <div class="small text-muted">Target Audience</div>
                                            <div class="fw-bold text-dark">{{ $currentVersion->target_label }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded bg-light">
                                            <div class="small text-muted">Integrasi Pengumuman Mobile</div>
                                            <div class="fw-bold text-dark">
                                                {{ $currentVersion->publish_to_mobile ? 'Aktif (Tersinkron)' : 'Tidak Dipublikasikan' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 border rounded bg-light">
                                            <div class="small text-muted">Wajib Konfirmasi Baca</div>
                                            <div class="fw-bold text-dark">
                                                {{ $currentVersion->require_read_receipt ? 'Ya (Read Receipt Aktif)' : 'Opsional' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($currentVersion->thumbnail)
                                    <div class="mb-3">
                                        <img src="{{ asset($currentVersion->thumbnail) }}" class="img-fluid rounded border" style="max-height: 240px; object-fit: cover;">
                                    </div>
                                @endif

                                @if($currentVersion->summary)
                                    <div class="alert alert-light border-start border-4 border-primary fst-italic text-secondary">
                                        {{ $currentVersion->summary }}
                                    </div>
                                @endif

                                <div class="p-3 border rounded bg-white mb-3" style="white-space: pre-line; line-height: 1.7;">
                                    {{ $currentVersion->content }}
                                </div>

                                @if($currentVersion->attachment_file)
                                    @php
                                        $ext = strtolower(pathinfo($currentVersion->attachment_file, PATHINFO_EXTENSION));
                                    @endphp
                                    <div class="card border mb-3">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-dark">
                                                <i class="fa fa-paperclip text-primary me-1"></i>
                                                {{ $currentVersion->attachment_name ?: basename($currentVersion->attachment_file) }}
                                            </span>
                                            <a href="{{ asset($currentVersion->attachment_file) }}" target="_blank" download class="btn btn-primary btn-sm">
                                                <i class="fa fa-download me-1"></i> Download Lampiran
                                            </a>
                                        </div>
                                        @if($ext === 'pdf')
                                            <div class="card-body p-0">
                                                <iframe src="{{ asset($currentVersion->attachment_file) }}" style="width:100%; height:520px; border:0;"></iframe>
                                            </div>
                                        @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                                            <div class="card-body text-center">
                                                <img src="{{ asset($currentVersion->attachment_file) }}" class="img-fluid rounded">
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- TAB 2: Version History -->
                        <div class="tab-pane fade" id="tabVersions">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Versi</th>
                                            <th>Status</th>
                                            <th>Masa Berlaku</th>
                                            <th>Changelog / Catatan Perubahan</th>
                                            <th>Visibilitas ke Karyawan</th>
                                            <th>Lampiran</th>
                                            <th>Publikasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($doc->versions as $ver)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-dark text-white">{{ $ver->version_label }}</span>
                                                </td>
                                                <td>
                                                    @php
                                                        $vBadge = $ver->version_status === 'ACTIVE' ? 'bg-success text-white'
                                                            : ($ver->version_status === 'SUPERSEDED' ? 'bg-secondary text-white'
                                                            : ($ver->version_status === 'WAITING_APPROVAL' ? 'bg-warning text-dark' : 'bg-light text-dark border'));
                                                    @endphp
                                                    <span class="badge {{ $vBadge }}">{{ $ver->version_status }}</span>
                                                </td>
                                                <td class="small">{{ $ver->validity_label }}</td>
                                                <td class="small">{{ $ver->changelog ?: '-' }}</td>
                                                <td>
                                                    @if($ver->version_status === 'SUPERSEDED' && $isHcga)
                                                        <select class="form-select form-select-sm" onchange="updateVersionVisibility({{ $ver->id }}, this.value)">
                                                            <option value="ADMIN_ONLY" {{ $ver->history_visibility === 'ADMIN_ONLY' ? 'selected' : '' }}>Arsip Admin Only</option>
                                                            <option value="EMPLOYEE_VISIBLE" {{ $ver->history_visibility === 'EMPLOYEE_VISIBLE' ? 'selected' : '' }}>Dapat Dilihat Karyawan</option>
                                                        </select>
                                                    @elseif($ver->version_status === 'ACTIVE')
                                                        <span class="badge bg-success text-white">Versi Aktif</span>
                                                    @else
                                                        <span class="small text-muted">{{ $ver->history_visibility }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($ver->attachment_file)
                                                        <a href="{{ asset($ver->attachment_file) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                            <i class="fa fa-download me-1"></i> File
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="small text-muted">
                                                    {{ $ver->published_at ?: $ver->created_date }}<br>
                                                    <span class="text-dark fw-semibold">{{ $ver->published_by ?: $ver->created_by }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 3: Read Receipts -->
                        <div class="tab-pane fade" id="tabReaders">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <div class="small text-muted">Target Karyawan Aktif</div>
                                        <div class="fs-5 fw-bold text-dark">{{ $readReport['total_target'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <div class="small text-muted">Sudah Membuka / Membaca</div>
                                        <div class="fs-5 fw-bold text-primary">{{ $readReport['total_read'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <div class="small text-muted">Konfirmasi Baca (Acknowledge)</div>
                                        <div class="fs-5 fw-bold text-success">{{ $readReport['total_acknowledged'] }} ({{ $readReport['percentage'] }}%)</div>
                                    </div>
                                </div>
                            </div>

                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: {{ $readReport['percentage'] }}%"></div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Username</th>
                                            <th>NIK</th>
                                            <th>Nama Karyawan</th>
                                            <th>Departemen</th>
                                            <th>Waktu Baca</th>
                                            <th>Konfirmasi Baca</th>
                                            <th>Sumber</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($readReport['receipts'] as $rec)
                                            <tr>
                                                <td>{{ $rec->username }}</td>
                                                <td>{{ $rec->emp_nik ?: '-' }}</td>
                                                <td class="fw-semibold">{{ $rec->emp_name ?: '-' }}</td>
                                                <td>{{ $rec->department ?: '-' }}</td>
                                                <td>{{ $rec->read_at }}</td>
                                                <td>
                                                    @if($rec->acknowledged_at)
                                                        <span class="badge bg-success text-white">{{ $rec->acknowledged_at }}</span>
                                                    @else
                                                        <span class="badge bg-light text-muted border">Belum Klik Konfirmasi</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge bg-info text-white">{{ $rec->source }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Belum ada karyawan yang membaca versi ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 4: Approval Logs -->
                        <div class="tab-pane fade" id="tabApproval">
                            @if($approvalRequests->isEmpty())
                                <div class="alert alert-light border text-muted">
                                    Dokumen ini dipublikasikan secara langsung tanpa melalui Approval Engine.
                                </div>
                            @else
                                @foreach($approvalRequests as $req)
                                    <div class="p-3 border rounded mb-3 bg-light">
                                        <div class="d-flex justify-content-between">
                                            <strong>{{ $req->Request_No }}</strong>
                                            <span class="badge bg-primary text-white">{{ $req->Status }}</span>
                                        </div>
                                        <div class="small text-muted">{{ $req->Description }} | Submitted: {{ $req->Submitted_At }}</div>
                                    </div>
                                @endforeach
                                @if($approvalLogs->isNotEmpty())
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Request ID</th>
                                                <th>Action</th>
                                                <th>Actor ID</th>
                                                <th>Note</th>
                                                <th>Waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($approvalLogs as $log)
                                                <tr>
                                                    <td>{{ $log->Request_ID }}</td>
                                                    <td><span class="badge bg-secondary text-white">{{ $log->Action }}</span></td>
                                                    <td>{{ $log->Actor_User_ID }}</td>
                                                    <td>{{ $log->Note ?: '-' }}</td>
                                                    <td>{{ $log->Created_At }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light py-2">
                    <h6 class="modal-title fw-bold text-dark" id="detailModalTitle">Form</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="detailModalBody">
                    <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showDetailModal(title, url) {
            $('#detailModalTitle').html(title);
            $('#detailModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            var el = document.getElementById('detailModal');
            var modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
            modal.show();
            $.get(url, function(html) {
                $('#detailModalBody').html(html);
            });
        }

        function openEditModal(id) {
            showDetailModal('<i class="fa fa-edit text-primary me-2"></i>Edit Dokumen / Metadata', "{{ route('hrd.storage.edit') }}?id=" + id);
        }

        function openVersionModal(id) {
            showDetailModal('<i class="fa fa-code-branch text-primary me-2"></i>Terbitkan Versi Baru', "{{ route('hrd.storage.version.create') }}?id=" + id);
        }

        function updateVersionVisibility(versionId, visibility) {
            $.post("{{ route('hrd.storage.version.visibility') }}", {
                _token: "{{ csrf_token() }}",
                version_id: versionId,
                history_visibility: visibility
            }).done(function(res) {
                alert(res.message);
            }).fail(function() {
                alert('Gagal memperbarui visibilitas versi.');
            });
        }

        function revokeDocument(id) {
            var reason = prompt('Masukkan alasan pencabutan (Revoke) dokumen ini:');
            if (!reason || reason.trim() === '') return;

            $.post("{{ route('hrd.storage.revoke') }}", {
                _token: "{{ csrf_token() }}",
                id: id,
                reason: reason
            }).done(function(res) {
                alert(res.message);
                window.location.reload();
            }).fail(function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal mencabut dokumen.');
            });
        }

        function deleteDocument(id) {
            if (!confirm('Hapus permanen dokumen ini beserta seluruh versi & pengumuman terkait?')) return;
            $.post("{{ route('hrd.storage.delete') }}", {
                _token: "{{ csrf_token() }}",
                id: id
            }).done(function(res) {
                alert(res.message);
                window.location.href = "{{ $backRoute }}";
            }).fail(function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal menghapus dokumen.');
            });
        }
    </script>
@endsection
