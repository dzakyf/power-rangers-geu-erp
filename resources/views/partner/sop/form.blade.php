@php
    $isEdit = isset($data) && $data;
    $status = $isEdit ? ($data->status ?: 'NORMAL') : 'NORMAL';
@endphp

<div class="mt-3">
    <div class="JudulContent" style="color:#699;">{{ $isEdit ? 'EDIT SOP' : 'NEW SOP' }}</div>
    <div class="card">
        <div class="card-body">
            <form id="SopForm">
                @if($isEdit)
                    <input type="hidden" name="ID" id="ID" value="{{ $data->id }}" />
                @endif

                <h6 class="font-weight-bold border-bottom pb-1 mt-2" style="color:#1a56a0;">Informasi SOP</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category">Category <span style="color:red;">*</span></label>
                            <input type="text" name="category" id="category" class="form-control shadow-sm" value="{{ $isEdit ? $data->category : '' }}" required />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control shadow-sm">
                                <option value="NORMAL" {{ $status === 'NORMAL' ? 'selected' : '' }}>Normal</option>
                                <option value="STOP" {{ $status === 'STOP' ? 'selected' : '' }}>Stop</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="category_description">Description</label>
                    <textarea name="category_description" id="category_description" rows="3" class="form-control shadow-sm">{{ $isEdit ? $data->category_description : '' }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="parameter_list">Parameter List</label>
                            <textarea name="parameter_list" id="parameter_list" rows="4" class="form-control shadow-sm">{{ $isEdit ? $data->parameter_list : '' }}</textarea>
                            <small class="text-muted">Pisahkan parameter dengan koma.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="parameter_score">Parameter Score</label>
                            <textarea name="parameter_score" id="parameter_score" rows="4" class="form-control shadow-sm">{{ $isEdit ? $data->parameter_score : '' }}</textarea>
                            <small class="text-muted">Pisahkan score dengan koma.</small>
                        </div>
                    </div>
                </div>
            </form>

            <div class="mt-2">
                <button type="button" class="btn btn-success" id="SubmitSop">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}</button>
                <button type="button" class="btn btn-secondary" onclick="$('#SopFormDiv').html('');">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var isEdit = {{ $isEdit ? 'true' : 'false' }};

        $('#SubmitSop').on('click', function () {
            var form = document.getElementById('SopForm');
            if (!form.reportValidity()) {
                return;
            }

            var formData = new FormData(form);
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: isEdit ? 'partner-master-sop-detail-submit' : 'partner-master-sop-new-submit',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.status === 'success') {
                        alert(res.message || 'Data berhasil disimpan.');
                        if (typeof refreshdata === 'function') {
                            refreshdata();
                        }
                        $('#SopFormDiv').html('');
                    } else {
                        alert(res.message || 'Gagal menyimpan data.');
                    }
                },
                error: function (xhr) {
                    var msg = 'Gagal menyimpan data.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            var first = Object.keys(xhr.responseJSON.errors)[0];
                            msg = xhr.responseJSON.errors[first][0];
                        } else if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                    }
                    alert(msg);
                }
            });
        });
    })();
</script>
