@include('layout.plugins')
@include('layout.adminor.tab-head')

@php
    $resolvedTaskId = $taskId ?? ($_GET['ID'] ?? '');
@endphp

<div class="container-fluid py-3">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-camera me-2"></i>Upload Foto Karyawan</h6>
        </div>
        <div class="card-body">
            <form id="empPhotoUploadForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="Task_ID" value="{{ $resolvedTaskId }}">
                <div class="mb-3">
                    <label for="empPhotoFile" class="form-label fw-semibold">Pilih File Foto</label>
                    <input type="file" class="form-control" id="empPhotoFile" name="photo"
                        accept=".jpg,.jpeg,.png,.gif,.webp,image/*" required>
                    <div class="form-text">Format: JPG, JPEG, PNG, GIF, WEBP. Maksimal 5 MB.</div>
                </div>
                <div id="empPhotoMsg" class="mb-2"></div>
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" id="btnUploadEmpPhoto" class="btn btn-success text-white">
                        <i class="fa fa-upload me-1"></i> Upload Foto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#empPhotoUploadForm').on('submit', function(e) {
        e.preventDefault();
        var fileInput = document.getElementById('empPhotoFile');
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            $('#empPhotoMsg').html('<div class="alert alert-warning py-2 mb-0">Pilih file foto terlebih dahulu.</div>');
            return;
        }

        var formData = new FormData(this);
        var $btn = $('#btnUploadEmpPhoto');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah...');
        $('#empPhotoMsg').empty();

        $.ajax({
            url: '{{ url("employee-lists-detail-photo-upload") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res && res.status === 'success') {
                    $('#empPhotoMsg').html('<div class="alert alert-success py-2 mb-0">' + (res.message || 'Foto berhasil diperbarui.') + '</div>');
                    if (window.parent && typeof window.parent.onEmployeePhotoUploaded === 'function') {
                        window.parent.onEmployeePhotoUploaded(res.url);
                    } else if (typeof window.onEmployeePhotoUploaded === 'function') {
                        window.onEmployeePhotoUploaded(res.url);
                    }
                } else {
                    $('#empPhotoMsg').html('<div class="alert alert-danger py-2 mb-0">' + ((res && res.message) ? res.message : 'Gagal mengunggah foto.') + '</div>');
                }
                $btn.prop('disabled', false).html('<i class="fa fa-upload me-1"></i> Upload Foto');
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : ('Gagal mengunggah foto (' + xhr.status + ').');
                $('#empPhotoMsg').html('<div class="alert alert-danger py-2 mb-0">' + msg + '</div>');
                $btn.prop('disabled', false).html('<i class="fa fa-upload me-1"></i> Upload Foto');
            }
        });
    });
</script>
