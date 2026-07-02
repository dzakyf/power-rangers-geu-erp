<div class="row">
    <div class="py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="m-3">Update Work Order Status</h5>
            </div>
            <div class="card-body">
                <form id="StatusEditForm">
                    @csrf
                    <input type="hidden" class="form-control" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_Code">Code <span style="color:red;">*</span></label>
                        <input type="text" class="form-control" id="edit_Code" name="Code" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_Name">Name <span style="color:red;">*</span></label>
                        <input type="text" class="form-control" id="edit_Name" name="Name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_Description">Description</label>
                        <textarea class="form-control" id="edit_Description" name="Description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_Is_Active">Is Active</label>
                        <select class="form-control" id="edit_Is_Active" name="Is_Active">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary" id="SubmitEdit">Update</button>
                    <button type="reset" class="btn btn-secondary ml-2">Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $('#SubmitEdit').on('click', function() {
        var form = document.getElementById('StatusEditForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        $.ajax({
            url: 'master-work-order-status-edit',
            type: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil diupdate!');
                    $('#Grid1Detail').hide();
                    Grid1.clearAndLoad('master-work-order-status-data?format=xml');
                } else {
                    alert('Gagal: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
            }
        });
    });
</script>
