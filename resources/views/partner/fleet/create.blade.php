@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Create Fleet</span></li>
    </ol>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3">Create Fleet</h5>
                </div>
                <div class="card-body">
                    <form id="FleetForm">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Police_Number">Police Number</label>
                                <input type="text" class="form-control" id="Police_Number" name="Police_Number" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Fleet_Brand">Fleet Brand</label>
                                <input type="text" class="form-control" id="Fleet_Brand" name="Fleet_Brand" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Warehouse_Id">Warehouse</label>
                                <select class="form-control select2" id="Warehouse_Id" name="Warehouse_Id" style="width:100%"></select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Status">Status</label>
                                <select class="form-control" id="Status" name="Status" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="INACTIVE">INACTIVE</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="Description">Description</label>
                            <textarea class="form-control" id="Description" name="Description" rows="3"></textarea>
                        </div>

                        <button type="button" class="btn btn-primary mt-3" id="SubmitFleet">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#Warehouse_Id').select2({
            placeholder: '-- Pilih Warehouse --',
            allowClear: true,
            ajax: {
                url: 'partner-warehouse-data',
                dataType: 'json',
                delay: 250,
                data: function(p) {
                    return { q: p.term || '' };
                },
                processResults: function(res) {
                    return {
                        results: res.data.map(function(row) {
                            return { id: row.id, text: row.warehouse_task_id + ' — ' + row.warehouse_location };
                        })
                    };
                },
                cache: true
            }
        });

        $('#SubmitFleet').on('click', function() {
            var form = document.getElementById('FleetForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var payload = new FormData(form);

            $.ajax({
                url: 'master-fleet-create-submit',
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