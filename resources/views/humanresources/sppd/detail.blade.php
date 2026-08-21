@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hrd-sppd-index">Perjalanan Dinas</a></li>
            <li class="breadcrumb-item active"><span class="active">Detail SPPD</span></li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title m-0">Detail Request SPPD : {{ $sppd->SPPD_No }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Status</label>
                                <div>
                                    <b style="color:{{ $sppd->Status == 'ACCEPTED' ? 'green' : ($sppd->Status == 'REJECTED' ? 'red' : 'orange') }};">{{ $sppd->Status }}</b>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>No SPPD</label>
                                <div>{{ $sppd->SPPD_No }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Departemen</label>
                                <div>{{ $sppd->Department }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Karyawan Pengaju</label>
                                <div>{{ $sppd->Requester_Name }} ({{ $sppd->Requester_NIK ?: '-' }})</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Tujuan Perjalanan Dinas</label>
                                <div>{{ $sppd->Destination }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Rute</label>
                                <div>{{ $sppd->Route_Start }} - {{ $sppd->Route_Finish }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Tanggal / Jam Kegiatan</label>
                                <div>{{ $sppd->Schedule_Date }} / {{ $sppd->Schedule_Time }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Kegiatan</label>
                                <div>{{ $sppd->Program_Name }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Keterangan</label>
                                <div>{{ $sppd->Description }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Approver</label>
                                <div>{{ $sppd->Approver_Name ?: '-' }}</div>
                            </div>
                        </div>

                        @if ($sppd->Approval_Note)
                        <div class="col-md-12 col-12">
                            <div class="form-group">
                                <label>Catatan Approval</label>
                                <div>{!! nl2br(e($sppd->Approval_Note)) !!}</div>
                            </div>
                        </div>
                        @endif

                    </div>

                    <h5 class="mt-3">Rincian Biaya yang Timbul</h5>
                    <table class="table table-bordered" width="60%">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Biaya (Rp)</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($costs as $i => $c)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td align="right">{{ number_format((float) $c->Cost, 0, ',', '.') }}</td>
                                <td>{{ $c->Description }}</td>
                            </tr>
                            @endforeach
                            <tr class="font-weight-bold">
                                <td colspan="1">Total</td>
                                <td align="right">{{ number_format((float) $sppd->Estimated_Cost, 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <h5 class="mt-3">Progress History</h5>
                    <table class="table table-bordered" width="60%">
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

                    <div class="col-12 mt-2">
                        <a href="hrd-sppd-print?ID={{ $sppd->ID }}" class="btn btn-info" target="_blank">Download Form Pengajuan</a>
                        @if ($sppd->Status == 'ACCEPTED')
                            <a href="hrd-sppd-print-approved?ID={{ $sppd->ID }}" class="btn btn-success" target="_blank">Download Form Persetujuan</a>
                        @endif
                        @if ($canClose)
                            <button class="btn btn-danger" id="BtnCloseSppd">Close Request</button>
                        @endif
                        <a href="hrd-sppd-index" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
$('#BtnCloseSppd').click(function (e) {
    e.preventDefault();
    if (!confirm('Apakah Anda yakin menutup request SPPD ini?')) return;

    $.ajax({
        url: 'hrd-sppd-close',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: {
            ID: '{{ $sppd->ID }}'
        },
        success: function (data) {
            if (data.status === 'success') {
                alert('Request SPPD Closed!');
                location.href = 'hrd-sppd-detail?ID={{ $sppd->ID }}';
            } else {
                alert('Close Failed! ' + data.message);
            }
        },
        error: function (xhr) {
            var msg = 'Terjadi kesalahan server';
            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            alert(msg);
        }
    });
});
</script>
@endsection
