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
            <div class="card-header bg-primary text-white p-2">
                <h4 class="card-title m-0 d-inline-block">Detail Request SPPD : {{ $sppd->SPPD_No }}</h4>
                <button type="button" class="float-right btn btn-secondary" onclick="history.back();"><i class="fa fa-times"></i> Close</button>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Status</label>
                            <div>
                                <b style="color:{{ in_array($sppd->Status, ['ACCEPTED','APPROVED']) ? 'green' : ($sppd->Status == 'REJECTED' ? 'red' : 'orange') }};">{{ $sppd->Status }}</b>
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
                            <label>Jenis SPPD</label>
                            <div>{{ $sppd->Sppd_Type ?? 'NORMAL' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Periode</label>
                            <div>{{ $sppd->Date_Start ?: '-' }} s/d {{ $sppd->Date_Finish ?: '-' }}</div>
                        </div>
                    </div>

                    @if (($sppd->Sppd_Type ?? 'NORMAL') === 'PERBANTUAN')
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Asal Site</label>
                            <div>{{ $sppd->Origin_Site ?: '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Lokasi Perbantuan</label>
                            <div>{{ $sppd->Target_Site ?: '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-12 col-12">
                        <div class="form-group">
                            <label>Alasan Perbantuan</label>
                            <div>{{ $sppd->Reason ?: '-' }}</div>
                        </div>
                    </div>
                    @endif

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

                <h5 class="mt-3">Kebutuhan Mobil Operasional</h5>
                @if ($sppd->Need_Car)
                <table class="table table-bordered" width="60%">
                    <tr>
                        <th width="220">Jenis Mobil</th>
                        <td>{{ $sppd->Car_Jenis_Mobil ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tgl Pinjam / Kembali</th>
                        <td>{{ $sppd->Car_Tgl_Pinjam ?: '-' }} s/d {{ $sppd->Car_Tgl_Kembali ?: '-' }} ({{ $sppd->Car_Jam_Pinjam ?: '-' }} - {{ $sppd->Car_Jam_Kembali ?: '-' }})</td>
                    </tr>
                </table>
                @else
                <p class="text-muted">SPPD ini tidak ditandai butuh mobil.</p>
                @endif

                <h5 class="mt-3">Booking Mobil Terkait</h5>
                @if (($carRents ?? collect())->count() > 0)
                <table class="table table-bordered" width="80%">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Car Rent No</th>
                            <th>Jenis Mobil</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($carRents as $i => $c)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><a href="hrd-car-rent-detail?Car_Rent_No={{ $c->Car_Rent_No }}" style="color:blue;">{{ $c->Car_Rent_No }}</a></td>
                            <td>{{ $c->Jenis_Mobil }}</td>
                            <td>{{ $c->Tanggal_Peminjaman }}</td>
                            <td>{{ $c->Tanggal_Pengembalian }}</td>
                            <td>{{ $c->Status }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-muted">Belum ada booking mobil untuk SPPD ini.</p>
                @endif

                <h5 class="mt-3">Settlement Terkait</h5>
                @if (($settlements ?? collect())->count() > 0)
                <table class="table table-bordered" width="80%">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>No Settlement</th>
                            <th>Status</th>
                            <th>Biaya Advance</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($settlements as $i => $s)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><a href="hrd-settlement-detail?ID={{ $s->ID }}" style="color:blue;">{{ $s->STTL_No }}</a></td>
                            <td>{{ $s->Status }}</td>
                            <td align="right">{{ $s->Advance_Nominal !== null ? number_format((float) $s->Advance_Nominal, 0, ',', '.') : '-' }}</td>
                            <td>{{ $s->Created_Date }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-muted">Belum ada settlement untuk SPPD ini.</p>
                @endif

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
                    @if ($canPrint)
                    <a href="hrd-sppd-print?ID={{ $sppd->ID }}" class="btn btn-info" target="_blank">Download Form Pengajuan</a>
                    @endif
                    @if (in_array($sppd->Status, ['ACCEPTED','APPROVED']))
                    <a href="hrd-sppd-print-approved?ID={{ $sppd->ID }}" class="btn btn-success" target="_blank">Download Form Persetujuan</a>
                    @endif
                    @if ($sppd->Status == 'ACCEPTED' && $sppd->Need_Car)
                    @if (($carRents ?? collect())->count() > 0)
                    <a href="hrd-car-rent-detail?Car_Rent_No={{ $carRents->first()->Car_Rent_No }}" class="btn btn-info">Lihat Booking Mobil</a>
                    @else
                    <button class="btn btn-warning" id="BtnCreateCarRent">Buatkan Booking Mobil</button>
                    @endif
                    <a href="hrd-car-rent-new?SPPD_ID={{ $sppd->ID }}" class="btn btn-outline-primary">Pinjam Mobil (Link SPPD)</a>
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
    $('#BtnCreateCarRent').click(function(e) {
        e.preventDefault();
        if (!confirm('Buatkan booking mobil dari SPPD ini?')) return;
        $.ajax({
            url: 'hrd-sppd-create-car-rent',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            data: {
                ID: '{{ $sppd->ID }}'
            },
            success: function(data) {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal! ' + data.message);
                }
            },
            error: function(xhr) {
                var msg = 'Terjadi kesalahan server';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });
    $('#BtnCloseSppd').click(function(e) {
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
            success: function(data) {
                if (data.status === 'success') {
                    alert('Request SPPD Closed!');
                    location.href = 'hrd-sppd-detail?ID={{ $sppd->ID }}';
                } else {
                    alert('Close Failed! ' + data.message);
                }
            },
            error: function(xhr) {
                var msg = 'Terjadi kesalahan server';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            }
        });
    });
</script>
@endsection