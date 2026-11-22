@php
    $resolvedTaskId = $taskId ?? ($_GET['ID'] ?? '');
    $docCategories = $categories ?? ['PHOTO', 'CV', 'IJAZAH', 'SERTIFIKAT', 'KTP', 'NPWP', 'KK', 'Akta', 'SIM'];
@endphp

<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0 fw-bold text-primary"><i class="fa fa-upload me-2"></i>Upload / Perbarui Dokumen Karyawan</h6>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#DivDocumentListsDetail').empty();">
            <i class="fa fa-times me-1"></i> Tutup
        </button>
    </div>
    <div class="card-body">
        <form id="empDocUploadForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="Task_ID" value="{{ $resolvedTaskId }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="empDocCategory" class="form-label fw-semibold">Kategori Dokumen</label>
                    <select class="form-select" id="empDocCategory" name="category" required>
                        @foreach ($docCategories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="empDocFile" class="form-label fw-semibold">Pilih File</label>
                    <input type="file" class="form-control" id="empDocFile" name="document_file"
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                    <div class="form-text">PDF, JPG, PNG, DOC/X, XLS/X (Maks. 10 MB).</div>
                </div>
                <div class="col-md-4">
                    <label for="empDocDesc" class="form-label fw-semibold">Keterangan / Nama Dokumen</label>
                    <input type="text" class="form-control" id="empDocDesc" name="description" maxlength="255"
                        placeholder="Opsional, misal: Ijazah S1">
                </div>
            </div>
            <div id="empDocUploadMsg" class="mt-3"></div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="submit" id="btnUploadEmpDoc" class="btn btn-primary">
                    <i class="fa fa-save me-1"></i> Simpan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $('#empDocUploadForm').on('submit', function(e) {
        e.preventDefault();
        var fileInput = document.getElementById('empDocFile');
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            $('#empDocUploadMsg').html('<div class="alert alert-warning py-2 mb-0">Pilih file dokumen terlebih dahulu.</div>');
            return;
        }

        var formData = new FormData(this);
        var $btn = $('#btnUploadEmpDoc');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah...');
        $('#empDocUploadMsg').empty();

        $.ajax({
            url: '{{ url("employee-lists-detail-document-upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res && res.status === 'success') {
                    $('#empDocUploadMsg').html('<div class="alert alert-success py-2 mb-0">' + (res.message || 'Dokumen berhasil diunggah.') + '</div>');
                    if (typeof refreshdataDocumentdetail === 'function') {
                        refreshdataDocumentdetail();
                    }
                    setTimeout(function() {
                        $('#DivDocumentListsDetail').empty();
                    }, 800);
                } else {
                    $('#empDocUploadMsg').html('<div class="alert alert-danger py-2 mb-0">' + ((res && res.message) ? res.message : 'Gagal mengunggah dokumen.') + '</div>');
                }
                $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Dokumen');
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : ('Gagal mengunggah dokumen (' + xhr.status + ').');
                $('#empDocUploadMsg').html('<div class="alert alert-danger py-2 mb-0">' + msg + '</div>');
                $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Simpan Dokumen');
            }
        });
    });
</script>
