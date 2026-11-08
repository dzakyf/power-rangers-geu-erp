@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    @csrf
    <style>
        .sppd-page-wrapper {
            margin-bottom: 2rem;
        }

        .sppd-hero-header {
            background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
            border-radius: 14px;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 4px 14px rgba(180, 83, 9, 0.15);
            color: #fff;
            margin-top: 1rem;
            margin-bottom: 1.5rem;
        }

        .sppd-hero-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .sppd-hero-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .sppd-breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }

        .sppd-breadcrumb .breadcrumb-item,
        .sppd-breadcrumb .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .sppd-breadcrumb .breadcrumb-item.active {
            color: #fff;
            font-weight: 600;
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
    </style>

    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
                Approval SPPD : {{ $sppd->SPPD_No }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                    <li class="breadcrumb-item"><a href="hrd-sppd-approval-index">Approval SPPD</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Detail Approval</li>
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
            <a href="hrd-sppd-approval-index" class="btn btn-outline-secondary sppd-btn">
                <i class="fa fa-arrow-left"></i> Kembali ke Antrean
            </a>
        </div>
    </div>

    <div class="sppd-page-wrapper">

        <!-- Informasi Pokok Permohonan SPPD -->
        <div class="card sppd-card">
            <div class="sppd-card-header">
                <h4 class="sppd-card-title">
                    <i class="fa fa-clipboard-check text-warning"></i> Lembar Verifikasi SPPD
                </h4>
                <span class="badge bg-light text-dark border">Jenis: {{ $sppd->Sppd_Type ?? 'NORMAL' }}</span>
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
                            <div class="info-label">Karyawan Pemohon</div>
                            <div class="info-value">{{ $sppd->Requester_Name }} <small
                                    class="text-muted">({{ $sppd->Requester_NIK ?: '-' }})</small></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="info-card-box">
                            <div class="info-label">Jadwal & Jam</div>
                            <div class="info-value">{{ $sppd->Schedule_Date }} <small
                                    class="text-muted">({{ $sppd->Departure_Time ?: '-' }} -
                                    {{ $sppd->Return_Time ?: '-' }})</small></div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="info-card-box">
                            <div class="info-label">Rute & Titik Tujuan</div>
                            <div class="info-value"><i class="fa fa-map-marker-alt text-danger me-1"></i>
                                {{ $sppd->Route_Start }} &rarr; {{ $sppd->Route_Finish ?: $sppd->Destination }}</div>
                            <small class="text-muted">Tujuan: {{ $sppd->Destination }}</small>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="info-card-box">
                            <div class="info-label">Agenda Kegiatan</div>
                            <div class="info-value">{{ $sppd->Program_Name }}</div>
                            <small class="text-muted">{{ $sppd->Description }}</small>
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
                                    <div id="destApprMap" style="height:260px;background:#eee;border-radius:0 0 8px 8px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Rincian Biaya & Logistik Mobil -->
        <div class="row">
            <div class="col-lg-6 col-12">
                <div class="card sppd-card">
                    <div class="sppd-card-header">
                        <h4 class="sppd-card-title">
                            <i class="fa fa-coins text-success"></i> Rincian Biaya Pengajuan
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
                                            <td colspan="3" class="text-center text-muted py-3">Tidak ada komponen biaya
                                                diajukan.</td>
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
                                        <div class="info-label">Jenis Mobil</div>
                                        <div class="info-value text-primary">{{ $sppd->Car_Jenis_Mobil ?: '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">Waktu Sewa/Pinjam</div>
                                        <div class="info-value">{{ $sppd->Car_Tgl_Pinjam ?: '-' }} s/d
                                            {{ $sppd->Car_Tgl_Kembali ?: '-' }}</div>
                                        <small class="text-muted">Jam: {{ $sppd->Car_Jam_Pinjam ?: '-' }} -
                                            {{ $sppd->Car_Jam_Kembali ?: '-' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-light border py-2 mb-0">
                                <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Booking mobil akan
                                    otomatis dibuat saat Anda klik <strong>APPROVE</strong>. Jika jadwal unit bentrok,
                                    pemohon dapat mengajukan reservasi manual via menu Car Rent.</small>
                            </div>
                        @else
                            <div class="alert alert-light border py-3 text-muted text-center mb-0">
                                <i class="fa fa-car-side fa-2x mb-2 d-block text-secondary"></i>
                                SPPD ini tidak membutuhkan mobil operasional kantor.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- History Singkat -->
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
                                            <td colspan="5" class="text-center text-muted py-2">Belum ada riwayat
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

        <!-- Form Aksi Keputusan Approver -->
        @if ($canApprove)
            <div class="card sppd-card border-warning shadow">
                <div class="sppd-card-header bg-warning bg-opacity-10">
                    <h4 class="sppd-card-title text-dark">
                        <i class="fa fa-check-circle text-warning"></i> Tindakan Keputusan Approver
                    </h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="Note" class="form-label fw-bold">Catatan / Komentar Persetujuan (Opsional saat
                            Approve, Wajib saat Reject):</label>
                        <textarea id="Note" class="form-control" rows="3"
                            placeholder="Tuliskan catatan arahan, catatan budget, atau alasan penolakan bila diperlukan..."></textarea>
                    </div>
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                        <div class="d-flex gap-2">
                            <button class="btn btn-success sppd-btn px-4" id="approveBtn">
                                <i class="fa fa-check"></i> SETUJUI (APPROVE)
                            </button>
                            <button class="btn btn-danger sppd-btn px-4" id="rejectBtn">
                                <i class="fa fa-times"></i> TOLAK (REJECT)
                            </button>
                        </div>
                        <a href="hrd-sppd-approval-index" class="btn btn-secondary sppd-btn">
                            <i class="fa fa-arrow-left"></i> Kembali ke Daftar Antrean
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        (function() {
            var dLat = '{{ $sppd->Dest_Lat }}',
                dLng = '{{ $sppd->Dest_Lng }}';
            if (!dLat || !dLng || !$('#destApprMap').length) return;

            function renderApprMap() {
                var lat = parseFloat(dLat),
                    lng = parseFloat(dLng);
                var map = L.map('destApprMap').setView([lat, lng], 14);
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
        $('#approveBtn').click(function(e) {
            e.preventDefault();
            doApproval('1');
        });
        $('#rejectBtn').click(function(e) {
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
                success: function(data) {
                    if (data.status === 'success') {
                        alert('Approval SPPD Submitted!');
                        location.href = 'hrd-sppd-approval-index';
                    } else {
                        alert('Approval Failed! ' + data.message);
                    }
                },
                error: function(xhr) {
                    var msg = 'Terjadi kesalahan server';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                }
            });
        }
    </script>
@endsection
