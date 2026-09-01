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
                    <h5 class="m-3 d-inline-block">Create Pickup</h5>
                    <button class="btn btn-warning float-right m-3 px-3 py-2" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
                </div>
                <div class="card-body">
                    <form id="PickupForm">
                        @csrf
                        <div class="form-group">
                            <label for="WO_Number">WO Number</label>
                            <select class="form-control select2" id="WO_Number" name="WO_Id" style="width:100%"></select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Type_Pickup">Type Pickup</label>
                                <select class="form-control" id="Type_Pickup" name="Type_Pickup">
                                    <option value="">-- Pilih --</option>
                                    <option value="mandiri">Mandiri</option>
                                    <option value="pickup">Pickup</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Coverage">Route / Coverage</label>
                                <input type="text" class="form-control" id="Coverage" name="Coverage">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Site">Site</label>
                                <select class="form-control select2" id="Site" name="Site" style="width:100%">
                                    <option value="">-- Pilih --</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Package">Kemasan</label>
                                <select class="form-control select2" id="Package" name="Package" style="width:100%" required>
                                    <option value="">-- Pilih --</option>
                                </select>
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
                                text: row.Code + ' — ' + row.POO_Name + ' | Dibuat oleh: ' + (row.POO_Created_By_Name || '-'),
                                price: row.POO_Price,
                                city: row.POO_City,
                                province: row.POO_Province,
                                nearestWarehouse: row.POO_Nearest_Warehouse
                            };
                        })
                    };
                },
                cache: true
            }
        });

        // Prefill Price, Coverage and Site from the POO linked to the selected
        // Work Order (Qty stays manual; Coverage/Site remain editable).
        $('#WO_Number').on('select2:select', function(e) {
            var data = e.params.data;
            $('#Price').val(data.price != null && data.price !== '' ? data.price : '');

            var coverage = data.province ? (data.province + ' - ' + (data.city || '')) : (data.city || '');
            $('#Coverage').val(coverage || '');

            if (data.nearestWarehouse) {
                // POO stores the warehouse id; resolve it to the Site option
                // label ("warehouse_task_id - warehouse_location").
                var label = whLabelMap[String(data.nearestWarehouse)];
                var matched = label ? $('#Site option').filter(function() {
                    var v = String($(this).val() || '');
                    var t = String($(this).text() || '');
                    return v === label || t === label;
                }) : $();
                if (matched.length) {
                    $('#Site').val(matched.first().val()).trigger('change');
                } else {
                    $('#Site').val(null).trigger('change');
                }
            }
        });

        // Load sites from warehouse API
        var whLabelMap = {};
        $.get('partner-warehouse-data', function(res) {
            var $site = $('#Site');
            $.each(res.data, function(_, item) {
                var label = item.warehouse_task_id + ' - ' + item.warehouse_location;
                whLabelMap[String(item.id)] = label;
                $site.append($('<option>', { value: label, text: label }));
            });
        });

        // Load packaging from master-packaging API
        $.get('master-packaging-data', function(res) {
            var $pkg = $('#Package');
            $.each(res.data, function(_, item) {
                $pkg.append($('<option>', { value: item.Name, text: item.Name }));
            });
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