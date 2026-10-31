@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hrd-settlement-approval-index">Verifikasi Settlement</a></li>
            <li class="breadcrumb-item active"><span class="active">Detail Verifikasi</span></li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title m-0">Verifikasi Settlement : {{ $settlement->STTL_No }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Status</label>
                                <div>
                                    <b style="color:{{ $settlement->Status == 'APPROVED' ? 'green' : ($settlement->Status == 'REJECTED' ? 'red' : 'orange') }};">{{ $settlement->Status }}</b>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Departemen</label>
                                <div>{{ $settlement->Department }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Karyawan Pengaju</label>
                                <div>{{ $settlement->Requester_Name }} ({{ $settlement->Requester_NIK ?: '-' }})</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Biaya Advance yang Diterima</label>
                                <div>{{ $settlement->Advance_Nominal !== null ? number_format((float) $settlement->Advance_Nominal, 0, ',', '.') : '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Bank Transfer</label>
                                <div>{{ $settlement->Bank ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Berkas Invoice</label>
                                <div>
                                    @if ($invoice)
                                        <a href="hrd-settlement-invoice?ID={{ $settlement->ID }}" target="_blank"><i class="fas fa-file-download mr-1"></i>{{ $invoice->Filename }}</a>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>

                    <h5 class="mt-3">Rincian Penggunaan Biaya</h5>
                    <table class="table table-bordered" width="70%">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Pengeluaran</th>
                                <th>Jumlah (Rp)</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $total = 0; @endphp
                            @foreach ($expenses as $i => $e)
                                @php $total += (float) $e->Cost; @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $e->Expense }}</td>
                                    <td align="right">{{ number_format((float) $e->Cost, 0, ',', '.') }}</td>
                                    <td>{{ $e->Description }}</td>
                                </tr>
                            @endforeach
                            <tr class="font-weight-bold">
                                <td colspan="2">Total</td>
                                <td align="right">{{ number_format($total, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <h5 class="mt-3">Progress History</h5>
                    <table class="table table-bordered" width="70%">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Action</th>
                                <th>Actor</th>
                                <th>Note</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $i => $l)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $l->Action }}</td>
                                <td>{{ $l->Actor_Name }}</td>
                                <td>{{ $l->Note }}</td>
                                <td>{{ $l->Created_At }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if ($canApprove)
                    <div class="col-md-12 col-12">
                        <div class="form-group">
                            <label for="Note">Catatan / Note</label>
                            <textarea id="Note" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <button class="btn btn-danger" id="rejectBtn">REJECT</button>
                        <button class="btn btn-success" id="approveBtn">APPROVE</button>
                        <a href="hrd-settlement-approval-index" class="btn btn-secondary">Kembali</a>
                    </div>
                    @else
                    <div class="col-12 mt-2">
                        <a href="hrd-settlement-approval-index" class="btn btn-secondary">Kembali</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<script>
$('#approveBtn').click(function (e) {
    e.preventDefault();
    doVerification('1');
});
$('#rejectBtn').click(function (e) {
    e.preventDefault();
    if (!confirm('Yakin menolak request settlement ini?')) return;
    doVerification('2');
});

function doVerification(stat) {
    $.ajax({
        url: 'hrd-settlement-approval-submit',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: {
            ID: '{{ $settlement->ID }}',
            Approval: stat,
            Note: $('#Note').val()
        },
        success: function (data) {
            if (data.status === 'success') {
                alert('Verifikasi Settlement Submitted!');
                location.href = 'hrd-settlement-approval-index';
            } else {
                alert('Verifikasi Failed! ' + data.message);
            }
        },
        error: function (xhr) {
            var msg = 'Terjadi kesalahan server';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            alert(msg);
        }
    });
}
</script>
@endsection
