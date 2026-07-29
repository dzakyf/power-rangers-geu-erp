@csrf
<div class="card">
    <div class="card-header p-2"><h5 class="mb-0">New Material</h5></div>
    <div class="card-body">
        <form id="createForm">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="Title">Title <span class="text-danger">*</span></label>
                    <input type="text" id="Title" name="Title" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="Category">Category</label>
                    <input type="text" id="Category" name="Category" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="Description">Description</label>
                    <textarea id="Description" name="Description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pdf_file">PDF File <span class="text-danger">*</span></label>
                    <input type="file" id="pdf_file" name="pdf_file" class="form-control-file" accept=".pdf" required>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Create</button>
                    <button type="button" class="btn btn-secondary" onclick="closeInlineForm()">Cancel</button>
                </div>
            </div>
        </form>
        <div id="formResult" class="mt-3"></div>
    </div>
</div>

<script>
$('#createForm').submit(function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: '{{ url("/hrd-onboarding-materials-submit") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() { $.blockUI(); },
        complete: function() { $.unblockUI(); },
        success: function(res) {
            if (res.status === 'success') {
                alert('Material created!');
                closeInlineForm();
            } else {
                alert(res.message || 'Error creating material.');
            }
        },
        error: function(xhr) {
            var msg = 'Error creating material.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            alert(msg);
        }
    });
});
</script>
