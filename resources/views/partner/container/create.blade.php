@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item"><a href="master-container">Container</a></li>
        <li class="breadcrumb-item active"><span class="active">Create</span></li>
    </ol>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-start">
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3 d-inline-block">Create Container (IBC)</h5>
                    <button class="btn btn-warning float-right m-3 px-3 py-2" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
                </div>
                <div class="card-body">
                    <form id="ContainerForm">
                        @csrf
                        <div class="form-group">
                            <label for="Name">Name <span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="Name" name="Name" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Volume">Volume</label>
                                <div class="input-group mb-2 mr-sm-2">
                                    <input type="number" step="0.01" class="form-control d-inline-block" id="Volume" name="Volume">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text bg-secondary">KG</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="Note">Note</label>
                            <textarea class="form-control" id="Note" name="Note" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="Warehouse_ID">Warehouse</label>
                            <select class="form-control" id="Warehouse_ID" name="Warehouse_ID" style="width:100%;">
                                <option value="">-- Pilih Warehouse --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Status">Status</label>
                            <select class="form-control" id="Status" name="Status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" id="SubmitContainer">Submit</button>
                        <button type="reset" class="btn btn-secondary ml-2">Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#Warehouse_ID').select2({
            placeholder: '-- Pilih Warehouse --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: 'partner-warehouse-data',
                dataType: 'json',
                delay: 250,
                data: function(p) { return { q: p.term || '' }; },
                processResults: function(res) {
                    return { results: (res.data || []).map(function(r) {
                        return { id: r.id, text: r.warehouse_location + ' - ' + (r.warehouse_task_id || '') };
                    })};
                },
                cache: true
            }
        });

        $('#SubmitContainer').on('click', function() {
        var form = document.getElementById('ContainerForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        $.ajax({
            url: 'master-container-submit',
            type: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil disimpan!');
                    window.location.href = 'master-container';
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
@endsection
