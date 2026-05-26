@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Create Pickup</span></li>
    </ol>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3">Create Pickup</h5>
                </div>
                <div class="card-body">
                    <form id="PickupForm">
                        @csrf
                        <div class="form-group">
                            <label for="WO_Number">WO Number</label>
                            <select class="form-control select2" id="WO_Number" name="WO_Number" style="width:100%"></select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Type_Pickup">Type Pickup</label>
                                <input type="text" class="form-control" id="Type_Pickup" name="Type_Pickup">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Coverage">Route / Coverage</label>
                                <input type="text" class="form-control" id="Coverage" name="Coverage">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Site">Site</label>
                                <input type="text" class="form-control" id="Site" name="Site">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Package">Package</label>
                                <input type="text" class="form-control" id="Package" name="Package" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Qty">Qty</label>
                                <input type="number" class="form-control" id="Qty" name="Qty" min="0" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Price">Price</label>
                                <input type="number" class="form-control" id="Price" name="Price" min="0" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="Description">Description</label>
                            <textarea class="form-control" id="Description" name="Description" rows="3"></textarea>
                        </div>

                        <button type="button" class="btn btn-primary mt-3" id="SubmitPickup">Submit</button>
                        <!-- <button type="reset" class="btn btn-secondary ml-2 mt-3">Reset</button> -->
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
        $('#WO_Number').select2({
            placeholder: '-- Pilih WO Number --',
            allowClear: true,
            ajax: {
                url: 'partner-work-order-data',
                dataType: 'json',
                delay: 250,
                data: function(p) {
                    return {
                        format: 'json',
                        q: p.term || ''
                    };
                },
                processResults: function(res) {
                    return {
                        results: res.data.map(function(row) {
                            return {
                                id: row.ID,
                                text: row.Code
                            };
                        })
                    };
                },
                cache: true
            }
        });

        $('#SubmitPickup').on('click', function() {
            var form = document.getElementById('PickupForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            var payload = new FormData(form);

            $.ajax({
                url: 'partner-pickup-create-submit',
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
                        $('#PickupForm')[0].reset();
                        $('#WO_Number').val(null).trigger('change');
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