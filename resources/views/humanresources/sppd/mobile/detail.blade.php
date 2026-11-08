<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail SPPD - {{ $sppd->SPPD_No }}</title>
    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: {{ (!empty($canApprove) || !empty($can_approve)) ? '95px' : '40px' }};
        }
        .header-nav {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #fff;
            padding: 16px 18px 20px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 14px rgba(30, 58, 138, 0.2);
        }
        .detail-card {
            background: #fff;
            border-radius: 14px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 16px;
            margin-bottom: 14px;
        }
        .detail-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }
        .detail-val {
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
        }
        .route-stop-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }
        .route-line {
            width: 2px;
            height: 22px;
            background-color: #cbd5e1;
            margin-left: 13px;
        }
        #sppdRouteMap {
            height: 260px;
            width: 100%;
            border-radius: 10px;
            background: #e2e8f0;
            z-index: 1;
        }
        .bottom-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            padding: 12px 18px;
            box-shadow: 0 -3px 14px rgba(0,0,0,0.09);
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <!-- Header Mobile Detail -->
    <div class="header-nav">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center">
                <a href="{{ route('sppd.mobile.index') }}" class="text-white me-3 fs-5"><i class="fa fa-arrow-left"></i></a>
                <div>
                    <h6 class="mb-0 fw-bold">{{ $sppd->SPPD_No }}</h6>
                    <small class="text-white-50" style="font-size: 11px;">
                        <i class="fa fa-calendar-alt me-1"></i> {{ $sppd->Date_Start ?: '-' }} s/d {{ $sppd->Date_Finish ?: '-' }}
                    </small>
                </div>
            </div>
            @php
                $badgeClass = 'bg-warning text-dark';
                if (in_array($sppd->Status, ['ACCEPTED', 'APPROVED'])) {
                    $badgeClass = 'bg-success text-white';
                } elseif ($sppd->Status === 'REJECTED') {
                    $badgeClass = 'bg-danger text-white';
                }
            @endphp
            <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2 shadow-sm">{{ $sppd->Status }}</span>
        </div>

        <!-- Tombol Aksi Download PDF -->
        <div class="d-flex gap-2 mt-2 pt-2 border-top border-white border-opacity-25">
            <a href="{{ url('hrd-sppd-print?ID=' . $sppd->ID) }}" target="_blank" class="btn btn-sm btn-light text-primary rounded-pill px-3 py-1 fw-bold flex-fill text-center" style="font-size: 11px;">
                <i class="fa fa-file-pdf text-danger me-1"></i> Form Pengajuan
            </a>
            @if(in_array($sppd->Status, ['ACCEPTED', 'APPROVED']))
            <a href="{{ url('hrd-sppd-print-approved?ID=' . $sppd->ID) }}" target="_blank" class="btn btn-sm btn-light text-success rounded-pill px-3 py-1 fw-bold flex-fill text-center" style="font-size: 11px;">
                <i class="fa fa-certificate text-success me-1"></i> Lembar Disetujui
            </a>
            @endif
        </div>
    </div>

    <div class="container px-3 mt-3">

        <!-- 1. Informasi Pokok SPPD -->
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="fw-bold text-dark" style="font-size: 13px;">
                    <i class="fa fa-info-circle text-primary me-1"></i> Informasi Pokok Perjalanan
                </span>
                <span class="badge bg-light text-dark border" style="font-size: 10px;">
                    {{ $sppd->Sppd_Type ?? 'NORMAL' }}
                </span>
            </div>

            <div class="row g-2">
                <div class="col-6">
                    <div class="detail-label">Karyawan Pemohon</div>
                    <div class="detail-val">{{ $sppd->Requester_Name }}</div>
                    <small class="text-muted d-block" style="font-size: 11px;">NIK: {{ $sppd->Requester_NIK ?: '-' }}</small>
                </div>
                <div class="col-6">
                    <div class="detail-label">Departemen</div>
                    <div class="detail-val">{{ $sppd->Department }}</div>
                </div>

                <div class="col-6">
                    <div class="detail-label">Periode Tanggal</div>
                    <div class="detail-val">{{ $sppd->Date_Start ?: '-' }}</div>
                    <small class="text-muted" style="font-size: 11px;">s/d {{ $sppd->Date_Finish ?: '-' }}</small>
                </div>
                <div class="col-6">
                    <div class="detail-label">Jadwal & Jam</div>
                    <div class="detail-val">{{ $sppd->Schedule_Date ?: '-' }}</div>
                    <small class="text-muted" style="font-size: 11px;">Jam: {{ $sppd->Departure_Time ?: '-' }} - {{ $sppd->Return_Time ?: '-' }}</small>
                </div>

                <div class="col-12 pt-2 border-top">
                    <div class="detail-label">Nama Kegiatan / Agenda</div>
                    <div class="detail-val text-dark">{{ $sppd->Program_Name ?: '-' }}</div>
                    @if($sppd->Description)
                        <small class="text-muted d-block mt-1">{{ $sppd->Description }}</small>
                    @endif
                </div>

                <!-- Khusus Perbantuan Sementara -->
                @if(($sppd->Sppd_Type ?? 'NORMAL') === 'PERBANTUAN')
                <div class="col-12 pt-2 border-top">
                    <div class="alert alert-warning py-2 px-3 mb-0 rounded-3">
                        <div class="detail-label text-warning mb-1">Perbantuan Sementara:</div>
                        <div class="fw-bold text-dark" style="font-size: 12.5px;">
                            {{ $sppd->Origin_Site ?: '-' }} &rarr; {{ $sppd->Target_Site ?: '-' }}
                        </div>
                        @if($sppd->Reason)
                        <small class="text-muted d-block mt-1">Alasan: {{ $sppd->Reason }}</small>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Approver & Status Engine -->
                <div class="col-12 pt-2 border-top">
                    <div class="detail-label">Approver & Verifikasi</div>
                    <div class="detail-val" style="font-size: 12.5px;">{{ $sppd->Approver_Name ?: 'Menunggu Penugasan' }}</div>
                    @if($sppd->Approval_Request_ID)
                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                        <i class="fa fa-shield-alt text-primary me-1"></i> Terhubung ke Workflow Engine Ref #{{ $sppd->Approval_Request_ID }}
                    </small>
                    @endif
                </div>

                @if($sppd->Approval_Note)
                <div class="col-12 pt-1">
                    <div class="alert alert-warning py-2 px-3 mb-0 rounded-3 small">
                        <strong><i class="fa fa-comment-dots me-1"></i> Catatan Approval:</strong><br>
                        {!! nl2br(e($sppd->Approval_Note)) !!}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- 2. Rute Perjalanan & Peta Interaktif -->
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="fw-bold text-dark" style="font-size: 13px;">
                    <i class="fa fa-map-marked-alt text-danger me-1"></i> Rute & Lokasi Perjalanan
                </span>
                @if($sppd->Entitlement_Scope)
                <span class="badge {{ $sppd->Entitlement_Scope === 'MEAL_ONLY' ? 'bg-secondary' : 'bg-primary' }} text-white" style="font-size: 10px;">
                    {{ $sppd->Entitlement_Scope === 'MEAL_ONLY' ? 'Hanya Uang Makan' : 'Hak Tunjangan Penuh' }}
                </span>
                @endif
            </div>

            <!-- Jarak & Estimasi Durasi -->
            <div class="d-flex align-items-center justify-content-between bg-light rounded-3 p-2 mb-3">
                <div class="text-center flex-fill border-end">
                    <div class="detail-label">Jarak Tempuh</div>
                    <div class="fw-bold text-primary" style="font-size: 13px;">
                        {{ $sppd->Distance_Km !== null ? number_format((float)$sppd->Distance_Km, 1) . ' km' : '-' }}
                    </div>
                </div>
                <div class="text-center flex-fill border-end">
                    <div class="detail-label">Estimasi Waktu</div>
                    <div class="fw-bold text-dark" style="font-size: 13px;">
                        @if($sppd->Duration_Min !== null)
                            {{ floor($sppd->Duration_Min / 60) }}j {{ $sppd->Duration_Min % 60 }}m
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="text-center flex-fill">
                    <div class="detail-label">Skema Hak</div>
                    <div class="fw-bold {{ $sppd->Entitlement_Scope === 'MEAL_ONLY' ? 'text-secondary' : 'text-success' }}" style="font-size: 12px;">
                        {{ $sppd->Entitlement_Scope ?: '-' }}
                    </div>
                </div>
            </div>

            <!-- Indikator Rute Asal -> Tujuan -->
            <div class="mb-3">
                <div class="d-flex align-items-start">
                    <div class="route-stop-icon bg-success bg-opacity-10 text-success me-2">
                        <i class="fa fa-circle"></i>
                    </div>
                    <div>
                        <div class="detail-label text-success">Titik Asal / Keberangkatan</div>
                        <div class="detail-val" style="font-size: 13px;">{{ $sppd->Route_Start ?: '-' }}</div>
                        @if(!empty($origin_coords))
                            <small class="text-muted" style="font-size: 10.5px;">
                                <i class="fa fa-map-pin me-1"></i> ({{ $origin_coords['lat'] }}, {{ $origin_coords['lng'] }}) &bull; {{ $origin_coords['name'] }}
                            </small>
                        @else
                            <small class="text-danger" style="font-size: 10.5px;">
                                <i class="fa fa-exclamation-triangle me-1"></i> Koordinat asal belum terdaftar di master lokasi
                            </small>
                        @endif
                    </div>
                </div>

                <div class="route-line"></div>

                <div class="d-flex align-items-start">
                    <div class="route-stop-icon bg-danger bg-opacity-10 text-danger me-2">
                        <i class="fa fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <div class="detail-label text-danger">Titik Tujuan / Destinasi</div>
                        <div class="detail-val" style="font-size: 13px;">
                            {{ $sppd->Destination ?: ($sppd->Route_Finish ?: '-') }}
                        </div>
                        @if(!empty($dest_coords))
                            <small class="text-muted" style="font-size: 10.5px;">
                                <i class="fa fa-map-pin me-1"></i> ({{ $dest_coords['lat'] }}, {{ $dest_coords['lng'] }})
                            </small>
                        @else
                            <small class="text-danger" style="font-size: 10.5px;">
                                <i class="fa fa-exclamation-triangle me-1"></i> Koordinat tujuan belum tersedia
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notice Status Koordinat Bila Tidak Lengkap -->
            @if(empty($origin_coords) && !empty($dest_coords))
                <div class="alert alert-warning py-2 px-3 mb-2 rounded-3 small">
                    <i class="fa fa-info-circle me-1"></i> Titik asal (<strong>{{ $sppd->Route_Start }}</strong>) belum memiliki koordinat terdaftar di master lokasi. Peta memusatkan lokasi titik tujuan.
                </div>
            @elseif(!empty($origin_coords) && empty($dest_coords))
                <div class="alert alert-warning py-2 px-3 mb-2 rounded-3 small">
                    <i class="fa fa-info-circle me-1"></i> Titik tujuan belum memiliki koordinat tersimpan. Peta memusatkan lokasi titik asal (<strong>{{ $sppd->Route_Start }}</strong>).
                </div>
            @elseif(empty($origin_coords) && empty($dest_coords))
                <div class="alert alert-secondary py-3 px-3 mb-2 rounded-3 text-center small">
                    <i class="fa fa-map-marked fa-2x d-block text-secondary mb-2"></i>
                    Koordinat titik asal maupun tujuan belum terdaftar di sistem sehingga peta visual tidak dapat dirender.
                </div>
            @endif

            <!-- Map Container Leaflet -->
            @if(!empty($origin_coords) || !empty($dest_coords))
                <div id="sppdRouteMap"></div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-1">
                    <small class="text-muted" style="font-size: 10.5px;">
                        &copy; OpenStreetMap contributors
                    </small>
                    @if(!empty($origin_coords) && !empty($dest_coords))
                    <a href="https://www.google.com/maps/dir/?api=1&origin={{ $origin_coords['lat'] }},{{ $origin_coords['lng'] }}&destination={{ $dest_coords['lat'] }},{{ $dest_coords['lng'] }}"
                       target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 11px;">
                        <i class="fa fa-directions me-1"></i> Buka Google Maps
                    </a>
                    @elseif(!empty($dest_coords))
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $dest_coords['lat'] }},{{ $dest_coords['lng'] }}"
                       target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1" style="font-size: 11px;">
                        <i class="fa fa-external-link-alt me-1"></i> Buka Google Maps
                    </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- 3. Rincian Biaya -->
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="fw-bold text-dark" style="font-size: 13px;">
                    <i class="fa fa-coins text-success me-1"></i> Rincian Komponen Biaya
                </span>
                <span class="badge bg-success bg-opacity-10 text-success fw-bold" style="font-size: 11px;">
                    {{ count($costs) }} Komponen
                </span>
            </div>

            @forelse($costs as $c)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <div class="fw-semibold text-dark" style="font-size: 12.5px;">{{ $c->Description }}</div>
                    </div>
                    <div class="fw-bold text-dark" style="font-size: 13px;">
                        Rp {{ number_format((float)$c->Cost, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-3 small">Tidak ada rincian komponen biaya.</div>
            @endforelse

            <div class="d-flex justify-content-between align-items-center pt-2 mt-1">
                <span class="fw-bold text-dark" style="font-size: 13.5px;">Total Estimasi Biaya</span>
                <span class="fw-bold text-success fs-6">
                    Rp {{ number_format((float)$sppd->Estimated_Cost, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- 4. Kebutuhan Mobil Operasional -->
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="fw-bold text-dark" style="font-size: 13px;">
                    <i class="fa fa-car text-info me-1"></i> Kebutuhan Mobil Operasional
                </span>
                @if($sppd->Need_Car)
                    <span class="badge bg-info text-white" style="font-size: 10px;"><i class="fa fa-check me-1"></i> Perlu Mobil</span>
                @else
                    <span class="badge bg-secondary text-white" style="font-size: 10px;">Tidak Perlu</span>
                @endif
            </div>

            @if($sppd->Need_Car)
                <div class="bg-light rounded-3 p-2 mb-2">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="detail-label">Jenis Mobil</div>
                            <div class="detail-val text-primary" style="font-size: 12.5px;">{{ $sppd->Car_Jenis_Mobil ?: '-' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="detail-label">Jadwal Pinjam</div>
                            <div class="detail-val" style="font-size: 12px;">{{ $sppd->Car_Tgl_Pinjam ?: '-' }} s/d {{ $sppd->Car_Tgl_Kembali ?: '-' }}</div>
                            <small class="text-muted" style="font-size: 10px;">Jam: {{ $sppd->Car_Jam_Pinjam ?: '-' }} - {{ $sppd->Car_Jam_Kembali ?: '-' }}</small>
                        </div>
                    </div>
                </div>

                @if(!empty($car_rents) && count($car_rents) > 0)
                    <div class="detail-label mt-2 mb-1">Booking Terkait:</div>
                    @foreach($car_rents as $cr)
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom small">
                        <div>
                            <strong>#{{ $cr->Car_Rent_No }}</strong>
                            <small class="text-muted d-block">{{ $cr->Jenis_Mobil }} &bull; {{ $cr->Tanggal_Peminjaman }}</small>
                        </div>
                        <span class="badge bg-light text-dark border">{{ $cr->Status }}</span>
                    </div>
                    @endforeach
                @endif
            @else
                <div class="text-muted text-center py-2 small">
                    SPPD ini tidak membutuhkan kendaraan operasional kantor.
                </div>
            @endif
        </div>

        <!-- 5. Riwayat Approval / Logs -->
        <div class="detail-card">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="fw-bold text-secondary" style="font-size: 13px;">
                    <i class="fa fa-history me-1"></i> Riwayat Progress
                </span>
                <span class="badge bg-light text-muted border" style="font-size: 10px;">{{ count($logs) }} Catatan</span>
            </div>

            @forelse($logs as $l)
                <div class="py-2 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-light text-dark border" style="font-size: 10px;">{{ $l->Action }}</span>
                        <small class="text-muted" style="font-size: 10px;">{{ $l->Created_At }}</small>
                    </div>
                    <small class="text-dark d-block fw-semibold mt-1" style="font-size: 12px;">{{ $l->Actor_Name }}</small>
                    @if($l->Note)
                        <small class="text-muted d-block" style="font-size: 11.5px;">{{ $l->Note }}</small>
                    @endif
                </div>
            @empty
                <div class="text-center text-muted py-2 small">Belum ada catatan riwayat progress.</div>
            @endforelse
        </div>
    </div>

    <!-- 6. Floating Action Bar Approval (Jika User Adalah Approver & Status Pending) -->
    @if(!empty($canApprove) || !empty($can_approve))
    <div class="bottom-action-bar">
        <button type="button" class="btn btn-outline-danger w-50 rounded-pill py-2 fw-bold" onclick="showApprovalModal(2)">
            <i class="fa fa-times me-1"></i> Tolak
        </button>
        <button type="button" class="btn btn-success w-50 rounded-pill py-2 fw-bold" onclick="showApprovalModal(1)">
            <i class="fa fa-check me-1"></i> Setujui
        </button>
    </div>

    <!-- Modal Catatan Approval -->
    <div class="modal fade" id="modalApproval" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 p-2">
                <div class="modal-header border-0 pb-1">
                    <h6 class="modal-title fw-bold" id="modalApprovalTitle">Konfirmasi Approval</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-2">
                    <input type="hidden" id="approvalAction">
                    <label class="form-label small text-muted mb-1">Catatan Keputusan (Opsional saat Approve, Wajib saat Tolak):</label>
                    <textarea id="approvalNote" class="form-control form-control-sm rounded-3" rows="3" placeholder="Tuliskan catatan..."></textarea>
                </div>
                <div class="modal-footer border-0 pt-1">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="btnSubmitApproval" onclick="submitApprovalAction()">Kirim</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        var apprModal;
        $(document).ready(function() {
            if ($('#modalApproval').length) {
                apprModal = new bootstrap.Modal(document.getElementById('modalApproval'));
            }

            // Inisialisasi Peta Rute Leaflet
            initRouteMap();
        });

        function initRouteMap() {
            var mapEl = document.getElementById('sppdRouteMap');
            if (!mapEl) return;

            var origin = @json($origin_coords);
            var dest   = @json($dest_coords);

            if (!origin && !dest) return;

            var initialCenter = [ -6.2088, 106.8456 ]; // Default Jakarta
            if (origin) {
                initialCenter = [ origin.lat, origin.lng ];
            } else if (dest) {
                initialCenter = [ dest.lat, dest.lng ];
            }

            var map = L.map('sppdRouteMap', {
                zoomControl: true,
                scrollWheelZoom: false
            }).setView(initialCenter, 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 19
            }).addTo(map);

            // Custom Icon Marker Asal (Hijau) & Tujuan (Merah)
            var greenIcon = new L.Icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            var redIcon = new L.Icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            var markers = [];

            if (origin) {
                var mStart = L.marker([origin.lat, origin.lng], { icon: greenIcon }).addTo(map);
                mStart.bindPopup("<strong>Titik Asal</strong><br>" + (origin.name || 'Lokasi Awal'));
                markers.push(mStart);
            }

            if (dest) {
                var mDest = L.marker([dest.lat, dest.lng], { icon: redIcon }).addTo(map);
                mDest.bindPopup("<strong>Titik Tujuan</strong><br>" + (dest.name || 'Lokasi Tujuan'));
                markers.push(mDest);
            }

            // Jika kedua titik ada, buat garis rute dan fit bounds
            if (origin && dest) {
                var latlngs = [
                    [ origin.lat, origin.lng ],
                    [ dest.lat, dest.lng ]
                ];

                var polyline = L.polyline(latlngs, {
                    color: '#2563eb',
                    weight: 4,
                    opacity: 0.85,
                    dashArray: '6, 8'
                }).addTo(map);

                var group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.2));
            } else if (markers.length === 1) {
                map.setView(markers[0].getLatLng(), 14);
            }

            setTimeout(function() {
                map.invalidateSize();
            }, 300);
        }

        function showApprovalModal(act) {
            $('#approvalAction').val(act);
            if (act === 1) {
                $('#modalApprovalTitle').text('Setujui Pengajuan SPPD');
                $('#btnSubmitApproval').removeClass('btn-danger').addClass('btn-success').text('Setujui (Approve)');
            } else {
                $('#modalApprovalTitle').text('Tolak Pengajuan SPPD');
                $('#btnSubmitApproval').removeClass('btn-success').addClass('btn-danger').text('Tolak (Reject)');
            }
            apprModal.show();
        }

        function submitApprovalAction() {
            var act = $('#approvalAction').val();
            var note = $('#approvalNote').val().trim();

            if (act === '2' && !note) {
                alert('Catatan penolakan wajib diisi!');
                return;
            }

            $('#btnSubmitApproval').prop('disabled', true).text('Memproses...');

            $.ajax({
                url: "{{ route('sppd.mobile.api.approval') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id: "{{ $sppd->ID }}",
                    action: act,
                    note: note
                },
                success: function(res) {
                    alert('Sukses: ' + res.message);
                    window.location.reload();
                },
                error: function(xhr) {
                    $('#btnSubmitApproval').prop('disabled', false).text('Kirim');
                    var msg = 'Gagal memproses approval';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                }
            });
        }
    </script>

</body>
</html>
