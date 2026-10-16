@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <div class="main-content-header">
        <h1>Human Capital and General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hrd-sppd-approval-index">Approval SPPD</a></li>
            <li class="breadcrumb-item active"><span class="active">Detail Approval</span></li>
        </ol>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title m-0">Approval SPPD : {{ $sppd->SPPD_No }}</h4>
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
                                <div>{{ $sppd->Route_Start }} - {{ $sppd->Route_Finish ?: $sppd->Destination }}</div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Tanggal / Jam (Berangkat - Kembali)</label>
                                <div>{{ $sppd->Schedule_Date }} / {{ $sppd->Departure_Time ?: '-' }} - {{ $sppd->Return_Time ?: '-' }}</div>
                            </div>
                        </div>

                        @if (!empty($sppd->Dest_Lat) && !empty($sppd->Dest_Lng))
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Titik Tujuan{{ empty($sppd->Route_Finish) ? ' (Koordinat)' : '' }}</label>
                                <div>
                                    {{ $sppd->Dest_Lat }}, {{ $sppd->Dest_Lng }} &mdash;
                                    <a href="https://www.openstreetmap.org/?mlat={{ $sppd->Dest_Lat }}&mlon={{ $sppd->Dest_Lng }}#map=16/{{ $sppd->Dest_Lat }}/{{ $sppd->Dest_Lng }}" target="_blank">Lihat di OpenStreetMap</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Peta Titik Tujuan</label>
                                <div id="destApprMap" style="height:260px;background:#eee;"></div>
                            </div>
                        </div>
                        @endif

                        @if ($sppd->Distance_Km !== null || $sppd->Duration_Min !== null || $sppd->Entitlement_Scope)
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Jarak / Durasi / Skema</label>
                                <div>
                                    {{ $sppd->Distance_Km !== null ? $sppd->Distance_Km . ' km' : '-' }} &bull;
                                    {{ $sppd->Duration_Min !== null ? floor($sppd->Duration_Min / 60) . ' jam' . ($sppd->Duration_Min % 60 ? ' ' . ($sppd->Duration_Min % 60) . ' mnt' : '') : '-' }} &bull;
                                    {{ $sppd->Entitlement_Scope === 'MEAL_ONLY' ? 'Hanya Uang Makan' : ($sppd->Entitlement_Scope === 'FULL' ? 'Hak Penuh' : '-') }}
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label>Kegiatan</label>
                                <div>{{ $sppd->Program_Name }}</div>
                            </div>
                        </div>

                        <div class="col-md-12 col-12">
                            <div class="form-group">
                                <label>Keterangan</label>
                                <div>{{ $sppd->Description }}</div>
                            </div>
                        </div>

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
                            <tr><th width="220">Jenis Mobil</th><td>{{ $sppd->Car_Jenis_Mobil ?: '-' }}</td></tr>
                            <tr><th>Tgl Pinjam / Kembali</th><td>{{ $sppd->Car_Tgl_Pinjam ?: '-' }} s/d {{ $sppd->Car_Tgl_Kembali ?: '-' }} ({{ $sppd->Car_Jam_Pinjam ?: '-' }} - {{ $sppd->Car_Jam_Kembali ?: '-' }})</td></tr>
                        </table>
                        <small class="text-muted">Booking dibuat otomatis saat APPROVE; bila gagal (misal mobil bentrok) buat manual via menu Car Rent.</small>
                    @else
                        <p class="text-muted">SPPD ini tidak ditandai butuh mobil.</p>
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
                        <a href="hrd-sppd-approval-index" class="btn btn-secondary">Kembali</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<script>
(function() {
    var dLat = '{{ $sppd->Dest_Lat }}', dLng = '{{ $sppd->Dest_Lng }}';
    if (!dLat || !dLng || !$('#destApprMap').length) return;
    function renderApprMap() {
        var lat = parseFloat(dLat), lng = parseFloat(dLng);
        var map = L.map('destApprMap').setView([lat, lng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup('Tujuan: {{ addslashes($sppd->Destination) }}').openPopup();
        setTimeout(function() { map.invalidateSize(); }, 250);
    }
    if (typeof L !== 'undefined') {
        renderApprMap();
        return;
    }
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);
    var script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = renderApprMap;
    document.head.appendChild(script);
})();
$('#approveBtn').click(function (e) {
    e.preventDefault();
    doApproval('1');
});
$('#rejectBtn').click(function (e) {
    e.preventDefault();
    if (!confirm('Yakin menolak request SPPD ini?')) return;
    doApproval('2');
});

function doApproval(stat) {
    $.ajax({
        url: 'hrd-sppd-approval-submit',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: {
            ID: '{{ $sppd->ID }}',
            Approval: stat,
            Note: $('#Note').val()
        },
        success: function (data) {
            if (data.status === 'success') {
                alert('Approval SPPD Submitted!');
                location.href = 'hrd-sppd-approval-index';
            } else {
                alert('Approval Failed! ' + data.message);
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
