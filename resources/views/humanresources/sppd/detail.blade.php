@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @csrf
    <style>
        .sppd-page-wrapper {
            margin-bottom: 2rem;
        }

        .sppd-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .sppd-card-header {
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .sppd-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sppd-btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.45rem 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
        }

        .sppd-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .info-label {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }

        .info-card-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            height: 100%;
        }

        .badge-status {
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
        }
    </style>

    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
                Detail SPPD : {{ $sppd->SPPD_No }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                    <li class="breadcrumb-item"><a href="hrd-sppd-index">Perjalanan Dinas</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Detail SPPD</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $st = $sppd->Status;
                $stBg = in_array($st, ['ACCEPTED', 'APPROVED'])
                    ? 'badge bg-success text-white'
                    : ($st == 'REJECTED'
                        ? 'badge bg-danger text-white'
                        : 'badge bg-warning text-dark');
            @endphp
            <span class="{{ $stBg }} px-3 py-2 fs-12 shadow-sm">{{ $st }}</span>
            <button type="button" class="btn btn-outline-secondary sppd-btn" onclick="history.back();">
                <i class="fa fa-arrow-left"></i> Kembali
            </button>
        </div>
    </div>

    <div class="sppd-page-wrapper">

        <!-- Informasi Utama & Rute -->
        <div class="card sppd-card">
            <div class="sppd-card-header">
                <h4 class="sppd-card-title">
                    <i class="fa fa-info-circle text-primary"></i> Informasi Pokok Perjalanan Dinas
                </h4>
                <div>
                    <span class="badge bg-light text-dark border">Jenis: {{ $sppd->Sppd_Type ?? 'NORMAL' }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-card-box">
                            <div class="info-label">No SPPD</div>
                            <div class="info-value text-primary">{{ $sppd->SPPD_No }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-card-box">
                            <div class="info-label">Departemen</div>
                            <div class="info-value">{{ $sppd->Department }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-card-box">
                            <div class="info-label">Karyawan Pengaju</div>
                            <div class="info-value">{{ $sppd->Requester_Name }} <small
                                    class="text-muted">({{ $sppd->Requester_NIK ?: '-' }})</small></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-card-box">
                            <div class="info-label">Periode Tanggal</div>
                            <div class="info-value">{{ $sppd->Date_Start ?: '-' }} <span class="text-muted">s/d</span>
                                {{ $sppd->Date_Finish ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="info-card-box">
                            <div class="info-label">Rute Perjalanan</div>
                            <div class="info-value"><i class="fa fa-map-marker-alt text-danger me-1"></i>
                                {{ $sppd->Route_Start }} &rarr; {{ $sppd->Route_Finish ?: $sppd->Destination }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="info-card-box">
                            <div class="info-label">Tujuan Spesifik</div>
                            <div class="info-value">{{ $sppd->Destination }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="info-card-box">
                            <div class="info-label">Jadwal & Jam</div>
                            <div class="info-value">{{ $sppd->Schedule_Date }} <small
                                    class="text-muted">({{ $sppd->Departure_Time ?: '-' }} -
                                    {{ $sppd->Return_Time ?: '-' }})</small></div>
                        </div>
                    </div>

                    @if ($sppd->Distance_Km !== null || $sppd->Duration_Min !== null || $sppd->Entitlement_Scope)
                        <div class="col-md-12">
                            <div class="alert alert-info py-2 mb-0 d-flex align-items-center flex-wrap gap-3">
                                <div><i class="fa fa-road me-1"></i> <strong>Jarak:</strong>
                                    {{ $sppd->Distance_Km !== null ? $sppd->Distance_Km . ' km' : '-' }}</div>
                                <div><i class="fa fa-clock me-1"></i> <strong>Estimasi Durasi:</strong>
                                    {{ $sppd->Duration_Min !== null ? floor($sppd->Duration_Min / 60) . ' jam' . ($sppd->Duration_Min % 60 ? ' ' . $sppd->Duration_Min % 60 . ' mnt' : '') : '-' }}
                                </div>
                                <div><i class="fa fa-award me-1"></i> <strong>Skema Tunjangan:</strong> <span
                                        class="badge bg-primary text-white">{{ $sppd->Entitlement_Scope === 'MEAL_ONLY' ? 'Hanya Uang Makan' : ($sppd->Entitlement_Scope === 'FULL' ? 'Hak Penuh' : '-') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (($sppd->Sppd_Type ?? 'NORMAL') === 'PERBANTUAN')
                        <div class="col-md-6">
                            <div class="info-card-box border-warning bg-light">
                                <div class="info-label text-warning">Asal Site &rarr; Lokasi Perbantuan</div>
                                <div class="info-value">{{ $sppd->Origin_Site ?: '-' }} &rarr;
                                    {{ $sppd->Target_Site ?: '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card-box border-warning bg-light">
                                <div class="info-label text-warning">Alasan Perbantuan</div>
                                <div class="info-value">{{ $sppd->Reason ?: '-' }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <div class="info-card-box">
                            <div class="info-label">Nama Kegiatan / Agenda</div>
                            <div class="info-value">{{ $sppd->Program_Name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card-box">
                            <div class="info-label">Approver & Keterangan</div>
                            <div class="info-value">{{ $sppd->Approver_Name ?: '-' }} &mdash; <span
                                    class="text-muted">{{ $sppd->Description }}</span></div>
                        </div>
                    </div>

                    @if ($sppd->Approval_Note)
                        <div class="col-md-12">
                            <div class="alert alert-warning py-2 mb-0">
                                <strong><i class="fa fa-comment-dots me-1"></i> Catatan Approval:</strong>
                                {!! nl2br(e($sppd->Approval_Note)) !!}
                            </div>
                        </div>
                    @endif

                    @if (!empty($sppd->Dest_Lat) && !empty($sppd->Dest_Lng))
                        <div class="col-md-12">
                            <div class="card border shadow-none mb-0">
                                <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center">
                                    <span class="fw-bold"><i class="fa fa-map-marked-alt text-primary me-1"></i> Peta Lokasi
                                        Titik Tujuan ({{ $sppd->Dest_Lat }}, {{ $sppd->Dest_Lng }})</span>
                                    <a href="https://www.openstreetmap.org/?mlat={{ $sppd->Dest_Lat }}&mlon={{ $sppd->Dest_Lng }}#map=16/{{ $sppd->Dest_Lat }}/{{ $sppd->Dest_Lng }}"
                                        target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-external-link-alt"></i> Buka OpenStreetMap
                                    </a>
                                </div>
                                <div class="card-body p-0">
                                    <div id="destDetailMap" style="height:280px;background:#eee;border-radius:0 0 8px 8px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Rincian Biaya & Mobil Operasional -->
        <div class="row">
            <div class="col-lg-6 col-12">
                <div class="card sppd-card">
                    <div class="sppd-card-header">
                        <h4 class="sppd-card-title">
                            <i class="fa fa-coins text-success"></i> Rincian Biaya yang Timbul
                        </h4>
                        <span class="badge bg-success text-white">Total: Rp
                            {{ number_format((float) $sppd->Estimated_Cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50" class="text-center">No</th>
                                        <th>Keterangan Biaya</th>
                                        <th class="text-end" width="160">Biaya (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($costs as $i => $c)
                                        <tr>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td>{{ $c->Description }}</td>
                                            <td class="text-end font-weight-bold">
                                                {{ number_format((float) $c->Cost, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">Tidak ada komponen
                                                rincian biaya.</td>
                                        </tr>
                                    @endforelse
                                    <tr class="table-primary fw-bold">
                                        <td colspan="2" class="text-light text-end">Total Estimasi:</td>
                                        <td class="text-light text-end">Rp
                                            {{ number_format((float) $sppd->Estimated_Cost, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-12">
                <div class="card sppd-card">
                    <div class="sppd-card-header">
                        <h4 class="sppd-card-title">
                            <i class="fa fa-car text-info"></i> Kebutuhan Mobil Operasional
                        </h4>
                        @if ($sppd->Need_Car)
                            <span class="badge bg-info text-white"><i class="fa fa-check"></i> Membutuhkan Unit</span>
                        @else
                            <span class="badge bg-secondary text-white">Tidak Membutuhkan</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($sppd->Need_Car)
                            <div class="info-card-box mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="info-label">Jenis Mobil Diminta</div>
                                        <div class="info-value text-primary">{{ $sppd->Car_Jenis_Mobil ?: '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Waktu Peminjaman</div>
                                        <div class="info-value">{{ $sppd->Car_Tgl_Pinjam ?: '-' }} s/d
                                            {{ $sppd->Car_Tgl_Kembali ?: '-' }}</div>
                                        <small class="text-muted">Jam: {{ $sppd->Car_Jam_Pinjam ?: '-' }} -
                                            {{ $sppd->Car_Jam_Kembali ?: '-' }}</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-light border py-3 text-muted text-center mb-3">
                                <i class="fa fa-car-side fa-2x mb-2 d-block text-secondary"></i>
                                SPPD ini tidak membutuhkan mobil operasional kantor.
                            </div>
                        @endif

                        <h6 class="fw-bold mb-2"><i class="fa fa-link text-primary me-1"></i> Booking Mobil Terkait:</h6>
                        @if (($carRents ?? collect())->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No Booking</th>
                                            <th>Unit Mobil</th>
                                            <th>Tgl Pinjam</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($carRents as $c)
                                            <tr>
                                                <td><a href="hrd-car-rent-detail?Car_Rent_No={{ $c->Car_Rent_No }}"
                                                        class="fw-bold text-primary">{{ $c->Car_Rent_No }}</a></td>
                                                <td>{{ $c->Jenis_Mobil }}</td>
                                                <td>{{ $c->Tanggal_Peminjaman }}</td>
                                                <td><span class="badge bg-secondary">{{ $c->Status }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted small mb-0"><i class="fa fa-info-circle me-1"></i> Belum ada tiket
                                booking mobil terhubung untuk SPPD ini.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Settlement Terkait & History Progress -->
        <div class="row">
            <div class="col-lg-6 col-12">
                <div class="card sppd-card">
                    <div class="sppd-card-header">
                        <h4 class="sppd-card-title">
                            <i class="fa fa-receipt text-warning"></i> Settlement Terkait
                        </h4>
                    </div>
                    <div class="card-body p-0">
                        @if (($settlements ?? collect())->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No Settlement</th>
                                            <th>Status</th>
                                            <th class="text-end">Advance (Rp)</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($settlements as $s)
                                            <tr>
                                                <td><a href="hrd-settlement-detail?ID={{ $s->ID }}"
                                                        class="fw-bold text-primary">{{ $s->STTL_No }}</a></td>
                                                <td><span class="badge bg-secondary">{{ $s->Status }}</span></td>
                                                <td class="text-end">
                                                    {{ $s->Advance_Nominal !== null ? number_format((float) $s->Advance_Nominal, 0, ',', '.') : '-' }}
                                                </td>
                                                <td>{{ $s->Created_Date }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-3 text-center text-muted">
                                <small>Belum ada rekonsiliasi settlement untuk pengajuan SPPD ini.</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-12">
                <div class="card sppd-card">
                    <div class="sppd-card-header">
                        <h4 class="sppd-card-title">
                            <i class="fa fa-history text-secondary"></i> Progress History
                        </h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40" class="text-center">No</th>
                                        <th>Action</th>
                                        <th>Actor</th>
                                        <th>Note</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($logs as $i => $l)
                                        <tr>
                                            <td class="text-center">{{ $i + 1 }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $l->Action }}</span>
                                            </td>
                                            <td class="fw-bold">{{ $l->Actor_Name }}</td>
                                            <td>{{ $l->Note ?: '-' }}</td>
                                            <td class="text-muted small">{{ $l->Created_At }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">Belum ada riwayat
                                                aktivitas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating / Sticky Action Footer -->
        <div class="card sppd-card">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2 p-3">
                <div class="d-flex flex-wrap gap-2">
                    @if ($canPrint)
                        <a href="hrd-sppd-print?ID={{ $sppd->ID }}" class="btn btn-info text-white sppd-btn"
                            target="_blank">
                            <i class="fa fa-print"></i> Form Pengajuan
                        </a>
                    @endif
                    @if (in_array($sppd->Status, ['ACCEPTED', 'APPROVED']))
                        <a href="hrd-sppd-print-approved?ID={{ $sppd->ID }}" class="btn btn-success sppd-btn"
                            target="_blank">
                            <i class="fa fa-file-check"></i> Form Persetujuan
                        </a>
                    @endif
                    @if ($sppd->Status == 'ACCEPTED' && $sppd->Need_Car)
                        @if (($carRents ?? collect())->count() > 0)
                            <a href="hrd-car-rent-detail?Car_Rent_No={{ $carRents->first()->Car_Rent_No }}"
                                class="btn btn-outline-info sppd-btn">
                                <i class="fa fa-car"></i> Lihat Booking Mobil
                            </a>
                        @else
                            <button class="btn btn-warning text-dark sppd-btn" id="BtnCreateCarRent">
                                <i class="fa fa-car"></i> Buatkan Booking Mobil
                            </button>
                        @endif
                        <a href="hrd-car-rent-new?SPPD_ID={{ $sppd->ID }}" class="btn btn-outline-primary sppd-btn">
                            <i class="fa fa-external-link-alt"></i> Pinjam Mobil (Link SPPD)
                        </a>
                    @endif
                </div>

                <div class="d-flex gap-2">
                    @if ($canClose)
                        <button class="btn btn-danger sppd-btn" id="BtnCloseSppd">
                            <i class="fa fa-ban"></i> Close Request
                        </button>
                    @endif
                    <a href="hrd-sppd-index" class="btn btn-secondary sppd-btn">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
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
        (function() {
            var dLat = '{{ $sppd->Dest_Lat }}',
                dLng = '{{ $sppd->Dest_Lng }}';
            if (!dLat || !dLng || !$('#destDetailMap').length) return;

            function renderDestMap() {
                var lat = parseFloat(dLat),
                    lng = parseFloat(dLng);
                var map = L.map('destDetailMap').setView([lat, lng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);
                L.marker([lat, lng]).addTo(map).bindPopup('Tujuan: {{ addslashes($sppd->Destination) }}').openPopup();
                setTimeout(function() {
                    map.invalidateSize();
                }, 250);
            }
            if (typeof L !== 'undefined') {
                renderDestMap();
                return;
            }
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(link);
            var script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = renderDestMap;
            document.head.appendChild(script);
        })();
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
