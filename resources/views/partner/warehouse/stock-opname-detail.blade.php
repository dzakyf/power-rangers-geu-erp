@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Warehouse</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Warehouse</a></li>
        <li class="breadcrumb-item"><a href="partner-warehouse-stock-opname">Stock Opname</a></li>
        <li class="breadcrumb-item active"><span class="active">Detail Opname</span></li>
    </ol>
</div>

<div class="container-fluid py-4">
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="m-3">Stock Opname: {{ $data->Opname_Number }}</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4"><strong>Opname Number:</strong> {{ $data->Opname_Number }}</div>
                <div class="col-md-4"><strong>Date:</strong> {{ $data->Opname_Date }}</div>
                <div class="col-md-4"><strong>Status:</strong>
                    <span class="badge
                        {{ $data->Status == 'CLOSED' ? 'badge-success' : ($data->Status == 'COUNTED' ? 'badge-warning' : 'badge-secondary') }}">
                        {{ $data->Status }}
                    </span>
                </div>
                <div class="col-md-4 mt-2"><strong>System Qty:</strong> {{ $data->System_Qty }} L</div>
                <div class="col-md-4 mt-2"><strong>Physical Qty:</strong> {{ $data->Physical_Qty }} L</div>
                @if ($data->Status == 'COUNTED' || $data->Status == 'CLOSED')
                <div class="col-md-4 mt-2"><strong>Difference:</strong>
                    <span class="{{ $data->Difference > 0 ? 'text-success' : ($data->Difference < 0 ? 'text-danger' : '') }}">
                        {{ $data->Difference > 0 ? '+' : '' }}{{ $data->Difference }} L
                    </span>
                </div>
                @endif
            </div>

            @if ($data->Status != 'CLOSED')
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="physical_qty">Physical Quantity (L)</label>
                        <input type="number" step="0.01" class="form-control" id="physical_qty" name="physical_qty"
                               value="{{ $data->Physical_Qty ?: '' }}" {{ $data->Status == 'COUNTED' ? 'readonly' : '' }}>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="counted_by">Counted By</label>
                        <input type="text" class="form-control" id="counted_by" name="counted_by"
                               value="{{ $data->Counted_By }}" {{ $data->Status == 'COUNTED' ? 'readonly' : '' }}>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="1" {{ $data->Status == 'COUNTED' ? 'readonly' : '' }}>{{ $data->Notes }}</textarea>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                @if ($data->Status == 'DRAFT')
                <button class="btn btn-primary" id="btnSaveCount">Simpan Hasil Count</button>
                @endif
                @if ($data->Status == 'COUNTED')
                <div class="alert alert-info">
                    Difference: {{ $data->Difference > 0 ? '+' : '' }}{{ $data->Difference }} L
                    ({{ $data->Difference > 0 ? 'Surplus' : ($data->Difference < 0 ? 'Shortage' : 'Match') }})
                </div>
                <div class="form-group">
                    <label for="reason">Adjustment Reason</label>
                    <textarea class="form-control" id="reason" rows="2" placeholder="Reason for adjustment"></textarea>
                </div>
                <button class="btn btn-success" id="btnFinalize">Finalize & Adjust</button>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    @if ($data->Status == 'DRAFT')
    $('#btnSaveCount').on('click', function() {
        var qty = $('#physical_qty').val();
        if (qty === '') { alert('Physical Quantity wajib diisi'); return; }

        $.ajax({
            url: 'partner-warehouse-stock-opname-update',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: {{ $data->ID }},
                physical_qty: qty,
                counted_by: $('#counted_by').val(),
                notes: $('#notes').val()
            },
            success: function(res) {
                if (res.success) {
                    alert('Hasil count disimpan. Difference: ' + res.difference + ' L');
                    location.reload();
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
            }
        });
    });
    @endif

    @if ($data->Status == 'COUNTED')
    $('#btnFinalize').on('click', function() {
        if (!confirm('Finalize opname ini? Perbedaan stok akan dicatat sebagai adjustment.')) return;

        $.ajax({
            url: 'partner-warehouse-stock-opname-finalize',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: {{ $data->ID }},
                reason: $('#reason').val()
            },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    location.reload();
                } else {
                    alert('Gagal: ' + (res.message || ''));
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
            }
        });
    });
    @endif
</script>
@endsection
