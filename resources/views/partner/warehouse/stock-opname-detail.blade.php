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
                        {{ in_array($data->Status, ['COUNTED', 'CLOSED_PENDING']) ? 'badge-warning' : ($data->Status == 'CLOSED' ? 'badge-success' : 'badge-secondary') }}
                        {{ $data->Status }}
                    </span>
                </div>
                <div class="col-md-4 mt-2"><strong>System Qty:</strong> {{ $data->System_Qty }} L</div>
                <div class="col-md-4 mt-2"><strong>Physical Qty:</strong> {{ $data->Physical_Qty }} L</div>
                @if ($data->Status == 'COUNTED' || $data->Status == 'CLOSED' || $data->Status == 'CLOSED_PENDING')
                <div class="col-md-4 mt-2"><strong>Difference:</strong>
                    <span class="{{ $data->Difference > 0 ? 'text-success' : ($data->Difference < 0 ? 'text-danger' : '') }}">
                        {{ $data->Difference > 0 ? '+' : '' }}{{ $data->Difference }} L
                    </span>
                </div>
                @endif
            </div>

            @if ($data->Status == 'DRAFT')
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="physical_qty">Physical Quantity (L)</label>
                        <input type="number" step="0.01" class="form-control" id="physical_qty" name="physical_qty" value="{{ $data->Physical_Qty ?: '' }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="1">{{ $data->Notes }}</textarea>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary" id="btnSaveCount">Simpan Hasil Count</button>
            </div>
            @endif

            @if ($data->Status == 'COUNTED')
            <hr>
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

@if ($data->Status == 'CLOSED')
            <hr>
            <h6>Adjustment History</h6>
            @forelse ($data->adjustments as $adj)
            <div class="mb-1 p-2 border rounded">
                <span class="badge badge-{{ $adj->Approval_Status == 'APPROVED' ? 'success' : 'info' }}">{{ $adj->Approval_Status }}</span>
                <strong>{{ $adj->Qty_Before }} L → {{ $adj->Qty_After }} L</strong>
                ({{ $adj->Adjustment_Type }})
                <small class="text-muted">{{ $adj->Reason }}</small>
            </div>
            @empty
            <p class="text-muted">Tidak ada adjustment</p>
            @endforelse

            <button class="btn btn-primary mt-2" id="btnNewAdjustment">New Adjustment</button>

            <div id="newAdjustmentForm" style="display:none;" class="mt-3 p-3 border rounded">
                <div class="form-group">
                    <label>Current Physical Qty: <strong>{{ $data->Physical_Qty }} L</strong></label>
                </div>
                <div class="form-group">
                    <label for="new_physical_qty">Corrected Physical Qty (L)</label>
                    <input type="number" step="0.01" class="form-control" id="new_physical_qty">
                </div>
                <div class="form-group">
                    <label for="new_reason">Reason</label>
                    <textarea class="form-control" id="new_reason" rows="2"></textarea>
                </div>
                <button class="btn btn-success" id="btnSubmitAdjustment">Submit for Approval</button>
            </div>
            @endif

            @if ($data->Status == 'CLOSED_PENDING')
            <hr>
            <div class="alert alert-warning">
                <strong>Menunggu Persetujuan</strong>
                <p>Selisih {{ $data->Difference > 0 ? '+' : '' }}{{ $data->Difference }} L
                ({{ number_format(abs($data->Difference) / max($data->System_Qty, 1) * 100, 1) }}% dari system qty)
                melebihi batas toleransi. Diperlukan persetujuan Ops.</p>
            </div>
            <div class="form-group">
                <label for="approval_note">Catatan</label>
                <textarea class="form-control" id="approval_note" rows="2"></textarea>
            </div>
            <button class="btn btn-success" id="btnApprove">Setujui</button>
            <button class="btn btn-danger" id="btnReject">Tolak</button>
            @endif
        </div>
    </div>
</div>

<script>
    var opnameStatus = '{{ $data->Status }}';

    if (opnameStatus === 'DRAFT') {
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
    }

    if (opnameStatus === 'COUNTED') {
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
    }

    if (opnameStatus === 'CLOSED_PENDING') {
        $('#btnApprove').on('click', function() {
            $.ajax({
                url: 'partner-warehouse-stock-opname-action',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: {{ $data->adjustments->where("Approval_Status", "PENDING")->first()->ID ?? 0 }},
                    action: 'approve',
                    note: $('#approval_note').val()
                },
                success: function(res) {
                    if (res.success) { alert('Disetujui'); location.reload(); }
                    else { alert('Gagal: ' + res.message); }
                }
            });
        });

        $('#btnReject').on('click', function() {
            if (!confirm('Tolak adjustment ini?')) return;
            $.ajax({
                url: 'partner-warehouse-stock-opname-action',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: {{ $data->adjustments->where("Approval_Status", "PENDING")->first()->ID ?? 0 }},
                    action: 'reject',
                    note: $('#approval_note').val()
                },
                success: function(res) {
                    if (res.success) { alert('Ditolak'); location.reload(); }
                    else { alert('Gagal: ' + res.message); }
                }
            });
        });
    }


    if (opnameStatus === 'CLOSED') {
        $('#btnNewAdjustment').on('click', function() {
            $('#newAdjustmentForm').toggle();
        });

        $('#btnSubmitAdjustment').on('click', function() {
            var qty = $('#new_physical_qty').val();
            var reason = $('#new_reason').val();
            if (!qty) { alert('Corrected Physical Qty wajib diisi'); return; }

            $.ajax({
                url: 'partner-warehouse-stock-opname-add-adjustment',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    opname_id: {{ $data->ID }},
                    physical_qty: qty,
                    reason: reason
                },
                success: function(res) {
                    if (res.success) { alert(res.message); location.reload(); }
                    else { alert('Gagal: ' + res.message); }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                }
            });
        });
    }
</script>
@endsection
