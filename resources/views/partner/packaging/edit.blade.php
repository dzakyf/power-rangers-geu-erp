<div class="row">
    <div class="py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="m-3">Update Kemasan</h5>
            </div>
            <div class="card-body">
                <form id="PackagingEditForm">
                    @csrf
                    <input type="hidden" class="form-control" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_Name">Name <span style="color:red;">*</span></label>
                        <input type="text" class="form-control" id="edit_Name" name="Name" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_Capacity">Capacity</label>
                            <input type="number" step="0.01" class="form-control" id="edit_Capacity" name="Capacity">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="edit_Unit">Unit</label>
                            <input type="text" class="form-control" id="edit_Unit" name="Unit">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_Description">Description</label>
                        <textarea class="form-control" id="edit_Description" name="Description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_Status">Status</label>
                        <select class="form-control" id="edit_Status" name="Status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
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
        var form = document.getElementById('PackagingEditForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        $.ajax({
            url: 'master-packaging-edit',
            type: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil diupdate!');
                    $('#Grid1Detail').hide();
                    Grid1.clearAndLoad('master-packaging-data?format=xml');
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
