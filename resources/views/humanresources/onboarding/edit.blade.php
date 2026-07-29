@csrf
<div class="card">
    <div class="card-header p-2">
        <h5 class="mb-0">Edit Material</h5>
    </div>
    <div class="card-body">
        <form id="editForm">
            @csrf
            <input type="hidden" name="id" value="{{ $material->id }}">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="Title">Judul <span class="text-danger">*</span></label>
                    <input type="text" id="Title" name="Title" class="form-control" value="{{ $material->Title }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="Category">Kategori</label>
                    <input type="text" id="Category" name="Category" class="form-control" value="{{ $material->Category }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="Description">Deskripsi</label>
                    <textarea id="Description" name="Description" class="form-control" rows="3">{{ $material->Description }}</textarea>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pdf_file">Ganti File</label>
                    <input type="file" id="pdf_file" name="pdf_file" class="form-control-file" accept=".pdf">
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" onclick="closeInlineForm()">Cancel</button>
                </div>
            </div>
        </form>
        <div id="formResult" class="mt-3"></div>
    </div>
</div>

<script>
    $('#editForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: '{{ url("/hrd-onboarding-materials-update") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $.blockUI();
            },
            complete: function() {
                $.unblockUI();
            },
            success: function(res) {
                if (res.status === 'success') {
                    alert('Material updated!');
                    closeInlineForm();
                } else {
                    alert(res.message || 'Error updating material.');
                }
            },
            error: function(xhr) {
                var msg = 'Error updating material.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    });
</script>