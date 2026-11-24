<form id="storageVersionForm" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="document_id" value="{{ $doc->id }}">

    <div class="alert alert-primary py-2 px-3 small mb-3 d-flex align-items-center justify-content-between">
        <div>
            <strong>Dokumen:</strong> {{ $doc->document_number ?: ('#' . $doc->id) }} &mdash; {{ $doc->title }}
        </div>
        <span class="badge bg-primary text-white">Versi Baru: v{{ $nextNumber }}</span>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Kategori / Tipe</label>
            <select name="sub_type" class="form-select">
                @foreach($subTypes as $code => $label)
                    <option value="{{ $code }}" {{ $doc->sub_type === $code ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Nomor Dokumen</label>
            <input type="text" name="document_number" class="form-control" value="{{ $doc->document_number }}">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Departemen Pemilik</label>
            <select name="owner_department" class="form-select">
                <option value="">-- Pilih Departemen --</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ $doc->owner_department === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-dark">Judul Dokumen (v{{ $nextNumber }}) <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="{{ $prevVersion->title ?? $doc->title }}" required>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-dark">Catatan Perubahan / Changelog (v{{ $nextNumber }}) <span class="text-danger">*</span></label>
            <textarea name="changelog" class="form-control" rows="2" placeholder="Jelaskan poin-poin perubahan dari versi sebelumnya..." required></textarea>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-dark">Ringkasan Singkat</label>
            <input type="text" name="summary" class="form-control" maxlength="500" value="{{ $prevVersion->summary ?? '' }}">
        </div>

        <div class="col-12">
            <label class="form-label fw-bold text-dark">Isi / Ketentuan Lengkap <span class="text-danger">*</span></label>
            <textarea name="content" class="form-control" rows="5" required>{{ $prevVersion->content ?? '' }}</textarea>
        </div>

        <!-- Ketetapan Masa Berlaku -->
        <div class="col-12">
            <div class="p-3 border rounded bg-light">
                <h6 class="fw-bold text-dark mb-2"><i class="fa fa-calendar-check text-primary me-1"></i> Ketetapan Masa Berlaku (v{{ $nextNumber }})</h6>
                @php
                    $vMode = $prevVersion ? $prevVersion->validity_mode : 'PERPETUAL';
                    $effFrom = date('Y-m-d');
                    $effUntil = $prevVersion && $prevVersion->effective_until ? $prevVersion->effective_until : '';
                @endphp
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="validity_mode" id="verValPerpetual" value="PERPETUAL"
                                    {{ $vMode === 'PERPETUAL' ? 'checked' : '' }} onchange="toggleVerValidity()">
                                <label class="form-check-label small fw-semibold" for="verValPerpetual">Selalu Aktif</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="validity_mode" id="verValFixed" value="FIXED_RANGE"
                                    {{ $vMode === 'FIXED_RANGE' ? 'checked' : '' }} onchange="toggleVerValidity()">
                                <label class="form-check-label small fw-semibold" for="verValFixed">Rentang Tanggal</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Berlaku Dari <span class="text-danger">*</span></label>
                        <input type="date" name="effective_from" class="form-control form-control-sm" value="{{ $effFrom }}" required>
                    </div>
                    <div class="col-md-4" id="verEffUntilWrap" style="{{ $vMode === 'PERPETUAL' ? 'display:none;' : '' }}">
                        <label class="form-label small fw-semibold">Berlaku Sampai <span class="text-danger">*</span></label>
                        <input type="date" name="effective_until" id="verEffUntilInput" class="form-control form-control-sm" value="{{ $effUntil }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Opsi Versioning & Sinkronisasi Pengumuman -->
        <div class="col-md-6">
            <div class="p-3 border rounded bg-light h-100">
                <h6 class="fw-bold text-dark mb-2"><i class="fa fa-history text-primary me-1"></i> Visibilitas Versi Lama (v{{ $doc->current_version_number }})</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="previous_version_visibility" id="visAdmin" value="ADMIN_ONLY" checked>
                    <label class="form-check-label small" for="visAdmin">
                        <strong>Arsipkan (Khusus Admin/HCGA)</strong> — Karyawan di Mobile Apps hanya melihat versi terbaru.
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="previous_version_visibility" id="visEmp" value="EMPLOYEE_VISIBLE">
                    <label class="form-check-label small" for="visEmp">
                        <strong>Dapat Dilihat Karyawan</strong> — Karyawan dapat membuka riwayat versi lama di detail Mobile Apps.
                    </label>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="p-3 border rounded bg-light h-100">
                <h6 class="fw-bold text-dark mb-2"><i class="fa fa-bullhorn text-primary me-1"></i> Sinkronisasi Pengumuman Mobile</h6>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="publish_to_mobile" value="0">
                    <input class="form-check-input" type="checkbox" name="publish_to_mobile" value="1" id="verPubMobile"
                        {{ (!$prevVersion || $prevVersion->publish_to_mobile) ? 'checked' : '' }}>
                    <label class="form-check-label small fw-semibold" for="verPubMobile">Publikasikan ke Pengumuman Mobile</label>
                </div>
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="announcement_sync_mode" id="syncUpdate" value="UPDATE_EXISTING" checked>
                    <label class="form-check-label small" for="syncUpdate"><strong>Update Pengumuman Lama</strong> (perbarui item & naikkan ke atas)</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="announcement_sync_mode" id="syncNew" value="CREATE_NEW">
                    <label class="form-check-label small" for="syncNew"><strong>Buat Pengumuman Baru</strong> untuk versi v{{ $nextNumber }}</label>
                </div>
                <div class="form-check ms-3">
                    <input type="hidden" name="archive_old_announcement" value="0">
                    <input class="form-check-input" type="checkbox" name="archive_old_announcement" value="1" id="chkArchiveOld" checked>
                    <label class="form-check-label small text-muted" for="chkArchiveOld">Otomatis arsipkan/unpublish pengumuman versi lama jika buat baru</label>
                </div>
            </div>
        </div>

        <!-- Target Audience & Read Receipt -->
        <div class="col-md-6">
            <div class="p-3 border rounded bg-light h-100">
                <h6 class="fw-bold text-dark mb-2"><i class="fa fa-users text-primary me-1"></i> Target Audience</h6>
                @php
                    $aud = $prevVersion ? $prevVersion->target_audience : 'ALL';
                    $selectedDepts = ($prevVersion && is_array($prevVersion->target_departments)) ? $prevVersion->target_departments : [];
                @endphp
                <div class="d-flex gap-3 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="target_audience" id="verAudAll" value="ALL"
                            {{ $aud !== 'DEPARTMENT' ? 'checked' : '' }} onchange="toggleVerDepts()">
                        <label class="form-check-label small fw-semibold" for="verAudAll">Semua Karyawan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="target_audience" id="verAudDept" value="DEPARTMENT"
                            {{ $aud === 'DEPARTMENT' ? 'checked' : '' }} onchange="toggleVerDepts()">
                        <label class="form-check-label small fw-semibold" for="verAudDept">Departemen Tertentu</label>
                    </div>
                </div>
                <div id="verDeptWrap" style="{{ $aud === 'DEPARTMENT' ? '' : 'display:none;' }}">
                    <div class="border rounded bg-white p-2" style="max-height: 110px; overflow-y: auto;">
                        <div class="row g-1">
                            @foreach($departments as $dept)
                                <div class="col-6">
                                    <div class="form-check form-check-sm">
                                        <input class="form-check-input" type="checkbox" name="target_departments[]"
                                            value="{{ $dept }}" id="verDept_{{ $loop->index }}"
                                            {{ in_array($dept, $selectedDepts) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="verDept_{{ $loop->index }}">{{ $dept }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="require_read_receipt" value="1" id="verReqRead"
                        {{ ($prevVersion && $prevVersion->require_read_receipt) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="verReqRead">Wajib Konfirmasi Baca untuk v{{ $nextNumber }}</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_pinned" value="1" id="verPin"
                        {{ ($prevVersion && $prevVersion->is_pinned) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="verPin">Sematkan (Pinned)</label>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold text-dark">Upload File Dokumen Baru (Opsional)</label>
            <input type="file" name="attachment" class="form-control mb-2" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
            @if($prevVersion && $prevVersion->attachment_file)
                <div class="p-2 border rounded bg-light small text-muted mb-2">
                    <i class="fa fa-paperclip me-1"></i>Lampiran versi sebelumnya: <strong>{{ $prevVersion->attachment_name ?: basename($prevVersion->attachment_file) }}</strong>
                    (otomatis dipakai jika tidak upload file baru).
                </div>
            @endif
            <label class="form-label fw-bold text-dark">Ganti Thumbnail (Opsional)</label>
            <input type="file" name="thumbnail" class="form-control" accept="image/*">
        </div>
    </div>

    <hr class="my-4">
    <input type="hidden" name="submit_action" id="verSubmitAction" value="PUBLISH">

    <div class="d-flex flex-wrap justify-content-end gap-2">
        <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-outline-secondary px-3" onclick="submitVersionForm('DRAFT')">
            <i class="fa fa-save me-1"></i> Simpan Draft v{{ $nextNumber }}
        </button>
        @if($approvalMode === 'REQUIRED')
            <button type="button" class="btn btn-warning text-dark fw-bold px-4" onclick="submitVersionForm('SUBMIT_APPROVAL')">
                <i class="fa fa-paper-plane me-1"></i> Ajukan Approval v{{ $nextNumber }}
            </button>
        @elseif($approvalMode === 'OPTIONAL')
            <button type="button" class="btn btn-outline-warning text-dark fw-semibold px-3" onclick="submitVersionForm('SUBMIT_APPROVAL')">
                <i class="fa fa-user-check me-1"></i> Ajukan Approval
            </button>
            <button type="button" class="btn btn-primary fw-bold px-4" onclick="submitVersionForm('PUBLISH')">
                <i class="fa fa-check-circle me-1"></i> Terbitkan Versi v{{ $nextNumber }}
            </button>
        @else
            <button type="button" class="btn btn-primary fw-bold px-4" onclick="submitVersionForm('PUBLISH')">
                <i class="fa fa-check-circle me-1"></i> Terbitkan Versi v{{ $nextNumber }}
            </button>
        @endif
    </div>
</form>

<script>
    function toggleVerValidity() {
        var isFixed = document.getElementById('verValFixed').checked;
        document.getElementById('verEffUntilWrap').style.display = isFixed ? 'block' : 'none';
        document.getElementById('verEffUntilInput').required = isFixed;
    }

    function toggleVerDepts() {
        var isDept = document.getElementById('verAudDept').checked;
        document.getElementById('verDeptWrap').style.display = isDept ? 'block' : 'none';
    }

    function submitVersionForm(action) {
        var form = document.getElementById('storageVersionForm');
        if (!form.reportValidity()) {
            return;
        }
        document.getElementById('verSubmitAction').value = action;
        var formData = new FormData(form);
        $('#storageVersionForm button').prop('disabled', true);

        $.ajax({
            url: "{{ route('hrd.storage.version.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                alert(res.message || 'Versi baru berhasil diproses!');
                window.location.reload();
            },
            error: function(xhr) {
                $('#storageVersionForm button').prop('disabled', false);
                var msg = xhr.responseJSON?.message || 'Gagal menerbitkan versi baru.';
                if (xhr.responseJSON?.errors) {
                    msg += "\n" + Object.values(xhr.responseJSON.errors).flat().join("\n");
                }
                alert(msg);
            }
        });
    }
</script>
