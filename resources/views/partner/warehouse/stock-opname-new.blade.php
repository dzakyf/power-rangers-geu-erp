@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Warehouse</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Warehouse</a></li>
        <li class="breadcrumb-item"><a href="partner-warehouse-stock-opname">Stock Opname</a></li>
        <li class="breadcrumb-item active"><span class="active">Buat Opname</span></li>
    </ol>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-start">
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-3">Buat Stock Opname</h5>
                </div>
                <div class="card-body">
                    <form id="opnameForm">
                        @csrf
                        <div class="form-group">
                            <label for="warehouse_id">Warehouse <span style="color:red;">*</span></label>
                            <select class="form-control select2" id="warehouse_id" name="warehouse_id" style="width:100%" required>
                                <option value="">-- Pilih Warehouse --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="opname_date">Opname Date</label>
                            <input type="date" class="form-control" id="opname_date" name="opname_date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="system_qty">System Quantity (L)</label>
                            <input type="number" step="0.01" class="form-control" id="system_qty" name="system_qty" value="0">
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        <button type="button" class="btn btn-primary" id="submitBtn">Submit</button>
                        <button type="reset" class="btn btn-secondary ml-2">Reset</button>
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
        $('.select2').select2({ width: '100%' });

        // Load warehouses
        $.get('partner-warehouse-data', function(res) {
            var $wh = $('#warehouse_id');
            $.each(res.data || [], function(_, item) {
                var label = item.warehouse_task_id + ' — ' + item.warehouse_location;
                $wh.append($('<option>', { value: item.id, text: label }));
            });
        });

        $('#submitBtn').on('click', function() {
            var form = document.getElementById('opnameForm');
            if (!form.warehouse_id.value) {
                alert('Warehouse wajib diisi');
                return;
            }

            $.ajax({
                url: 'partner-warehouse-stock-opname-submit',
                type: 'POST',
                data: new FormData(form),
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        alert('Opname berhasil dibuat: ' + res.opname_number);
                        window.location.href = 'partner-warehouse-stock-opname';
                    } else {
                        alert('Gagal: ' + (res.message || ''));
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
