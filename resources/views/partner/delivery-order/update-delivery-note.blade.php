<div class="container-fluid">
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3 d-inline-block">Update Delivery Note</h5>
                    <button class="btn btn-warning float-right m-3 px-3 py-2" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
                </div>
                <div class="card-body">
                    <form id="UpdateDeliveryNoteForm">
                        @csrf
                        <input type="hidden" id="DN_DO_Id" name="DO_Id">

                        <div class="form-group">
                            <label for="Delivery_Note">Delivery Note Image</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="Delivery_Note" name="Delivery_Note" accept="image/*" required>
                                <label class="custom-file-label" for="Delivery_Note">Choose file...</label>
                            </div>
                            <div id="dnPhotoPreview" class="mt-2" style="display:none;">
                                <img id="dnPreviewImg" src="" alt="Preview" style="max-width:100%;max-height:300px;border:1px solid #ddd;border-radius:4px;">
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary mt-3" id="SubmitDeliveryNote">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#Delivery_Note').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#dnPreviewImg').attr('src', e.target.result);
                    $('#dnPhotoPreview').show();
                };
                reader.readAsDataURL(file);
                $(this).next('.custom-file-label').text(file.name);
            } else {
                $('#dnPhotoPreview').hide();
                $(this).next('.custom-file-label').text('Choose file...');
            }
        });

        $('#SubmitDeliveryNote').on('click', function() {
            var form = document.getElementById('UpdateDeliveryNoteForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var payload = new FormData(form);

            $.ajax({
                url: 'partner-delivery-order-update-note',
                type: 'POST',
                data: payload,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Data berhasil disimpan!');
                        form.reset();
                        $('#dnPhotoPreview').hide();
                        $('#Delivery_Note').next('.custom-file-label').text('Choose file...');
                    } else {
                        alert('Gagal: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                }
            });
        });
    });
</script>
