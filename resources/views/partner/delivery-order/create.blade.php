@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Create Surat Jalan</span></li>
    </ol>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3 d-inline-block">Create Surat Jalan</h5>
                    <button class="btn btn-warning float-right m-3 px-3 py-2" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
                </div>
                <div class="card-body">
                    <form id="DeliveryOrderForm">
                        @csrf

                        <div class="form-group">
                            <label for="WarehouseFilter">Gudang</label>
                            <select class="form-control" id="WarehouseFilter" style="width:100%">
                                <option value="">-- Pilih Gudang --</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="PO_Id">PO Number</label>
                            <select class="form-control select2" id="PO_Id" name="PO_Id[]" style="width:100%" multiple></select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="DO_Date">Surat Jalan Date</label>
                                <input type="date" class="form-control" id="DO_Date" name="DO_Date" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="DO_Status">Surat Jalan Status</label>
                                <select class="form-control" id="DO_Status" name="DO_Status">
                                    <option value="">-- Pilih --</option>
                                    <option value="PROGRESS">PROGRESS</option>
                                    <option value="PICKUP">PICKUP</option>
                                    <option value="DONE">DONE</option>
                                    <option value="CANCEL">CANCEL</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="Start_Time">Start Time</label>
                                <input type="time" class="form-control" id="Start_Time" name="Start_Time">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="Finish_Time">Finish Time</label>
                                <input type="time" class="form-control" id="Finish_Time" name="Finish_Time">
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary mt-3" id="SubmitDO">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    function loadWarehouseOptions() {
        $.ajax({
            url: 'partner-warehouse-data',
            type: 'GET',
            success: function(res) {
                var data = res.data || [];
                var $select = $('#WarehouseFilter');
                $select.empty();
                $select.append('<option value="">-- Pilih Gudang --</option>');
                $.each(data, function(i, item) {
                    $select.append('<option value="' + item.id + '">' + item.warehouse_task_id + ' - ' + item.warehouse_location + '</option>');
                });
            }
        });
    }

    $(document).ready(function() {
        loadWarehouseOptions();

        $('#PO_Id').select2({
            placeholder: '-- Pilih PO Number --',
            multiple: true,
            closeOnSelect: false,
            allowClear: true,
            ajax: {
                url: 'partner-pickup-data',
                dataType: 'json',
                delay: 250,
                data: function(p) {
                    return {
                        format: 'json',
                        q: p.term || '',
                        whid: $('#WarehouseFilter').val() || ''
                    };
                },
                processResults: function(res) {
                    return {
                        results: res.data.map(function(row) {
                            return {
                                id: row.ID,
                                text: row.Code + ' \u2014 ' + (row.POO_Name || '') + ' | Dibuat oleh: ' + (row.POO_Created_By_Name || '-')
                            };
                        })
                    };
                },
                cache: true
            }
        });

        $('#WarehouseFilter').on('change', function() {
            $('#PO_Id').val(null).trigger('change');
        });

        // After selecting a PO, auto-select the Surat Jalan status as PROGRESS
        // (still overridable by the user).
        $('#PO_Id').on('change', function() {
            var sel = $(this).val();
            if (sel && sel.length > 0 && !$('#DO_Status').val()) {
                $('#DO_Status').val('PROGRESS');
            }
        });

        $('#SubmitDO').on('click', function() {
            var form = document.getElementById('DeliveryOrderForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var payload = new FormData(form);

            $.ajax({
                url: 'partner-delivery-order-create-submit',
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
                        $('#PO_Id').val(null).trigger('change');
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
