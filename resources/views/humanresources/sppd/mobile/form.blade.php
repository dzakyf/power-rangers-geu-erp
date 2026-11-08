<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Form Pengajuan SPPD Mobile</title>
    <!-- Bootstrap 5 & FontAwesome -->
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding-bottom: 85px;
        }
        .header-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #fff;
            padding: 18px 20px 22px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }
        .form-section-card {
            background: #fff;
            border-radius: 14px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 16px;
            margin-bottom: 12px;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }
        .total-cost-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 8px 14px;
            color: #166534;
            font-size: 14px;
            font-weight: 700;
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            height: 65px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 1000;
        }
        .nav-item-mobile {
            text-align: center;
            color: #8e9aab;
            text-decoration: none;
            font-size: 11px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .nav-item-mobile.active {
            color: #1e3a8a;
            font-weight: bold;
        }
        .nav-item-mobile i {
            font-size: 20px;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

    <!-- Header Mobile -->
    <div class="header-card">
        <div class="d-flex align-items-center">
            <a href="{{ route('sppd.mobile.index') }}" class="text-white me-3 fs-5"><i class="fa fa-arrow-left"></i></a>
            <div>
                <h6 class="mb-0 fw-bold">Pengajuan SPPD Baru</h6>
                <small class="text-white-50">Kalkulasi Otomatis Matriks & Rute OSRM</small>
            </div>
        </div>
    </div>

    <div class="container px-3 mt-3">
        <form id="mobileSppdForm">
            <!-- 1. Identitas Dokumen & Pemohon -->
            <div class="form-section-card">
                <div class="section-title"><i class="fa fa-id-card me-1"></i> 1. Dokumen & Pemohon</div>
                <div class="mb-2">
                    <label class="form-label">No SPPD</label>
                    <input type="text" id="SPPD_No" class="form-control form-control-sm bg-light fw-bold text-primary" name="SPPD_No" value="{{ $SPPD_No }}" readonly />
                </div>
                <div class="mb-2">
                    <label class="form-label">Jenis SPPD</label>
                    <select name="Sppd_Type" id="Sppd_Type" class="form-select form-select-sm" onchange="toggleType();">
                        <option value="NORMAL">Regular SPPD (Maks 14 Hari)</option>
                        <option value="PERBANTUAN">Perbantuan Sementara (> 14 Hari)</option>
                        <option value="RELOKASI" disabled>Relokasi/Mutasi (Belum Tersedia)</option>
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label">NIK Pemohon</label>
                        <input type="text" id="Requester_NIK" class="form-control form-control-sm bg-light" name="Requester_NIK" value="{{ $user->Emp_ID ?: '' }}" readonly />
                    </div>
                    <div class="col-6">
                        <label class="form-label">Nama Pemohon</label>
                        <input type="text" id="Requester_Name" class="form-control form-control-sm" name="Requester_Name" value="{{ $user->Name ?: $user->username }}" />
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Departemen <span class="text-danger">*</span></label>
                    <select name="Department" id="Department" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->Department }}" {{ ($user->Department == $dept->Department) ? 'selected' : '' }}>
                                {{ $dept->Department }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="Date_Start" id="Date_Start" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" onchange="updateAllowanceInfo();" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="Date_Finish" id="Date_Finish" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" onchange="updateAllowanceInfo();" required>
                    </div>
                </div>
            </div>

            <!-- 2. Rute, Tujuan & Perhitungan Jarak -->
            <div class="form-section-card">
                <div class="section-title"><i class="fa fa-route me-1"></i> 2. Rute & Detail Lokasi</div>
                <div class="mb-2">
                    <label class="form-label">Lokasi Asal (Rute Start) <span class="text-danger">*</span></label>
                    <select name="Route_Start" id="Route_Start" class="form-select form-select-sm" required>
                        <option value="">-- Pilih Lokasi Asal --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->Location_ID }}">{{ $loc->Name }} ({{ $loc->Location_ID }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Metode Rute Tujuan</label>
                    <select id="Rute_Mode" class="form-select form-select-sm" onchange="updateRuteMode();">
                        <option value="KANTOR">Lokasi Kantor Internal</option>
                        <option value="TITIK">Pilih Titik Nama / Peta</option>
                        <option value="KOORDINAT">Koordinat Spesifik (Lat / Long)</option>
                    </select>
                </div>

                <!-- Input Kantor -->
                <div class="mb-2" id="ruteKantor">
                    <label class="form-label">Lokasi Kantor (Tujuan)</label>
                    <select name="Route_Finish" id="Route_Finish" class="form-select form-select-sm">
                        <option value="">-- Pilih Kantor Tujuan --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->Location_ID }}">{{ $loc->Name }} ({{ $loc->Location_ID }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Input Titik Peta -->
                <div class="mb-2" id="ruteTitik" style="display: none;">
                    <label class="form-label">Tujuan Perjalanan Dinas</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="Destination" id="Destination" class="form-control form-control-sm" placeholder="cth: Pelindo / Jakarta">
                        <button type="button" class="btn btn-outline-primary" id="btnPickDestPoint">
                            <i class="fa fa-map-marker-alt"></i> Peta
                        </button>
                    </div>
                    <div class="small mt-1 text-muted" id="destPointInfo"></div>
                </div>

                <!-- Input Koordinat -->
                <div class="mb-2" id="ruteKoordinat" style="display: none;">
                    <label class="form-label">Koordinat Tujuan (Lat / Long)</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="Coord_Lat" class="form-control" placeholder="Latitude">
                        <input type="text" id="Coord_Lng" class="form-control" placeholder="Longitude">
                        <button type="button" class="btn btn-outline-primary" id="btnPickCoordPoint">
                            <i class="fa fa-map-marker-alt"></i> Peta
                        </button>
                    </div>
                    <div class="small mt-1 text-muted" id="coordPointInfo"></div>
                </div>
                <input type="hidden" id="Dest_Lat" name="Dest_Lat" />
                <input type="hidden" id="Dest_Lng" name="Dest_Lng" />

                <div class="mb-2">
                    <label class="form-label">Tanggal Kegiatan</label>
                    <input type="date" name="Schedule_Date" id="Schedule_Date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label">Jam Berangkat <span class="text-danger">*</span></label>
                        <input type="time" name="Departure_Time" id="Departure_Time" class="form-control form-control-sm" value="08:00" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Jam Kembali <span class="text-danger">*</span></label>
                        <input type="time" name="Return_Time" id="Return_Time" class="form-control form-control-sm" value="17:00" required>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Agenda / Nama Kegiatan <span class="text-danger">*</span></label>
                    <input type="text" name="Program_Name" id="Program_Name" class="form-control form-control-sm" placeholder="contoh: Koordinasi Tender & Meeting Mitra" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Keterangan Tambahan</label>
                    <textarea name="Description" id="Description" class="form-control form-control-sm" rows="2" placeholder="Uraian ringkas tujuan dinas..."></textarea>
                </div>

                <!-- Route Scope & Policy Badge Banner -->
                <div id="routeScopeInfo" class="mt-2"></div>
                <div id="allowanceInfo" class="mt-2"></div>
            </div>

            <!-- Bagian Perbantuan Sementara (Conditional) -->
            <div class="form-section-card border border-warning" id="pbSection" style="display: none;">
                <div class="section-title text-warning"><i class="fa fa-people-carry me-1"></i> Penugasan Perbantuan</div>
                <div class="mb-2">
                    <label class="form-label">Asal Site (Homebase)</label>
                    <select name="Origin_Site" id="Origin_Site" class="form-select form-select-sm">
                        <option value="">-- Pilih Lokasi Asal --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->Location_ID }}">{{ $loc->Name }} ({{ $loc->Location_ID }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Lokasi Perbantuan (Target Site)</label>
                    <select name="Target_Site" id="Target_Site" class="form-select form-select-sm">
                        <option value="">-- Pilih Lokasi Tujuan --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->Location_ID }}">{{ $loc->Name }} ({{ $loc->Location_ID }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Alasan Penugasan Perbantuan</label>
                    <textarea name="Reason" id="Reason" class="form-control form-control-sm" rows="2" placeholder="Urgensi kebutuhan perbantuan..."></textarea>
                </div>
            </div>

            <!-- 3. Rincian Estimasi Biaya (Regulasi + Manual) -->
            <div class="form-section-card">
                <div class="section-title"><i class="fa fa-coins me-1"></i> 3. Komponen Biaya Regulasi</div>
                <div class="table-responsive">
                    <table id="tblCost" class="table table-sm table-bordered align-middle mb-2">
                        <thead class="table-light small">
                            <tr>
                                <th width="120">Nominal (Rp)</th>
                                <th>Keterangan Komponen</th>
                                <th width="40" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dinamis di-render oleh buildRegulationRows() -->
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <div class="total-cost-box">
                        Total: Rp <span id="TotalCost">0</span>
                    </div>
                    <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Matriks otomatis</small>
                </div>
                <input type="hidden" id="Estimated_Cost" name="Estimated_Cost" value="0" />
            </div>

            <!-- 4. Kebutuhan Mobil Operasional -->
            <div class="form-section-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="section-title mb-0"><i class="fa fa-car me-1"></i> 4. Mobil Operasional</span>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="Need_Car" id="Need_Car" value="1" onchange="toggleNeedCar();">
                    </div>
                </div>
                <div id="NeedCarBody" style="display: none;" class="mt-2 pt-2 border-top">
                    <div class="mb-2">
                        <label class="form-label">Preferensi Jenis Mobil</label>
                        <select name="Car_Jenis_Mobil" id="Car_Jenis_Mobil" class="form-select form-select-sm">
                            <option value="">-- Pilih Jenis Mobil --</option>
                            @foreach($fleetList as $m)
                                <option value="{{ $m }}">{{ ($m == 'Other') ? 'Lainnya' : $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label">Tgl Pinjam</label>
                            <input type="date" name="Car_Tgl_Pinjam" id="Car_Tgl_Pinjam" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tgl Kembali</label>
                            <input type="date" name="Car_Tgl_Kembali" id="Car_Tgl_Kembali" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Jam Pinjam</label>
                            <input type="time" name="Car_Jam_Pinjam" id="Car_Jam_Pinjam" class="form-control form-control-sm" value="08:00">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam Kembali</label>
                            <input type="time" name="Car_Jam_Kembali" id="Car_Jam_Kembali" class="form-control form-control-sm" value="17:00">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm" id="btnSubmit">
                <i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan SPPD
            </button>
        </form>
    </div>

    <!-- Modal Peta Leaflet untuk Mobile -->
    <div class="modal fade" id="modalMapPicker" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 p-2">
                <div class="modal-header border-0 py-2">
                    <h6 class="modal-title fw-bold"><i class="fa fa-map-marker-alt text-danger me-1"></i> Pilih Titik Tujuan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-2">
                    <div id="destPickerMap" style="height:320px; border-radius: 10px;"></div>
                    <small class="text-muted d-block mt-2">Ketuk pada peta untuk meletakkan titik lokasi tujuan.</small>
                    <div class="mt-1 small"><strong>Koordinat:</strong> <span id="destPickerCoord">-</span></div>
                </div>
                <div class="modal-footer border-0 py-2">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" onclick="applyDestPoint();">Gunakan Titik</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Nav Mobile Bar -->
    <div class="bottom-nav">
        <a href="{{ route('sppd.mobile.index') }}" class="nav-item-mobile">
            <i class="fa fa-list-check"></i>
            <span>Daftar SPPD</span>
        </a>
        <a href="{{ route('sppd.mobile.form') }}" class="nav-item-mobile active">
            <i class="fa fa-circle-plus"></i>
            <span>Buat SPPD</span>
        </a>
        <a href="{{ route('sppd.mobile.approval') }}" class="nav-item-mobile">
            <i class="fa fa-stamp"></i>
            <span>Approval</span>
        </a>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('adminor/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        var matrixData = null;
        var userJobLevel = "{{ $jobLevel ?? 'pelaksana' }}";
        var routeScope = null;        // 'MEAL_ONLY' | 'FULL'
        var routeDistanceKm = null;
        var routeDurationMin = null;
        var routeResolved = false;

        var TYPE_CODES = {
            NORMAL: 'RS',
            PERBANTUAN: 'PS',
            RELOKASI: 'RL'
        };
        var TYPE_COUNTS = @json($typeCounts ?? []);
        var SPPD_YMD = "{{ $ymd ?? date('ymd') }}";

        function updateSppdNo() {
            var t = $('#Sppd_Type').val();
            var code = TYPE_CODES[t] || 'RS';
            var seq = ((TYPE_COUNTS[t] || 0) + 1);
            $('#SPPD_No').val('SPPD/' + code + '/GEU/' + SPPD_YMD + '-' + seq);
        }

        function fmtDuration(min) {
            if (min === null || min === undefined || isNaN(min)) return '-';
            var h = Math.floor(min / 60), m = min % 60;
            return h + ' jam' + (m ? ' ' + m + ' mnt' : '');
        }

        function fetchRouteInfo(origin, cb) {
            $.ajax({
                url: '{{ url("hrd-sppd-route-info") }}',
                type: 'GET',
                data: {
                    Route_Start: origin,
                    Route_Finish: $('#Route_Finish').val(),
                    Dest_Lat: $('#Dest_Lat').val(),
                    Dest_Lng: $('#Dest_Lng').val(),
                    Departure_Time: $('#Departure_Time').val(),
                    Return_Time: $('#Return_Time').val()
                },
                success: function(resp) {
                    if (resp.status === 'success' && resp.data) {
                        cb(true, resp.data.distance_km, resp.data.duration_min);
                    } else {
                        cb(false);
                    }
                },
                error: function() {
                    cb(false);
                }
            });
        }

        function updateRouteScope() {
            var sppdType = $('#Sppd_Type').val();
            var origin = $('#Route_Start').val();

            if (sppdType !== 'NORMAL') {
                routeScope = 'FULL';
                routeResolved = false;
                routeDistanceKm = null;
                routeDurationMin = null;
                renderScopeBanner();
                updateAllowanceInfo();
                if (origin) {
                    fetchRouteInfo(origin, function(ok, dist, dur) {
                        if (ok) {
                            routeDistanceKm = dist;
                            routeDurationMin = dur;
                            routeResolved = true;
                            renderScopeBanner();
                        }
                    });
                }
                return;
            }

            if (!origin) {
                routeScope = null;
                routeResolved = false;
                routeDistanceKm = null;
                routeDurationMin = null;
                renderScopeBanner();
                updateAllowanceInfo();
                return;
            }

            fetchRouteInfo(origin, function(ok, dist, dur) {
                if (!ok) {
                    routeScope = null;
                    routeResolved = false;
                    routeDistanceKm = null;
                    routeDurationMin = null;
                } else {
                    routeDistanceKm = dist;
                    routeDurationMin = dur;
                    routeResolved = true;
                    routeScope = (dist !== null && dur !== null && dist < 120 && dur < 480) ? 'MEAL_ONLY' : 'FULL';
                }
                renderScopeBanner();
                updateAllowanceInfo();
            });
        }

        function renderScopeBanner() {
            var sppdType = $('#Sppd_Type').val();
            if (sppdType !== 'NORMAL') {
                var extra = (routeDistanceKm !== null) ? 'Jarak: ' + routeDistanceKm + ' km &bull; ' : '';
                $('#routeScopeInfo').html('<div class="alert alert-success py-2 mb-0 small"><strong>Perbantuan Sementara:</strong> ' + extra + 'hak penuh (di luar kriteria 120 km / 8 jam).</div>');
                return;
            }
            if (!routeResolved || routeDistanceKm === null) {
                $('#routeScopeInfo').html('<div class="alert alert-warning py-2 mb-0 small"><strong>Rute belum dapat dihitung.</strong> Pastikan lokasi asal &amp; tujuan lengkap serta isi jam berangkat/kembali.</div>');
                return;
            }
            var isMeal = (routeScope === 'MEAL_ONLY');
            var css = isMeal ? 'alert-info' : 'alert-primary';
            var label = isMeal
                ? '&lt; 120 km &amp; &lt; 8 jam &rarr; <strong>hanya Uang Makan</strong>'
                : '&ge; 120 km atau &ge; 8 jam &rarr; <strong>hak penuh</strong>';
            $('#routeScopeInfo').html('<div class="alert ' + css + ' py-2 mb-0 small">Jarak: <strong>' + routeDistanceKm + ' km</strong> &bull; Durasi: <strong>' + fmtDuration(routeDurationMin) + '</strong> &bull; ' + label + '</div>');
        }

        function updateRuteMode() {
            var m = $('#Rute_Mode').val();
            if (m === 'TITIK') {
                $('#ruteKantor').hide();
                $('#ruteKoordinat').hide();
                $('#ruteTitik').show();
                $('#Route_Finish').val('');
            } else if (m === 'KOORDINAT') {
                $('#ruteKantor').hide();
                $('#ruteTitik').hide();
                $('#ruteKoordinat').show();
                $('#Route_Finish').val('');
                if ($('#Dest_Lat').val() && $('#Dest_Lng').val() && $.trim($('#Coord_Lat').val()) === '') {
                    $('#Coord_Lat').val($('#Dest_Lat').val());
                    $('#Coord_Lng').val($('#Dest_Lng').val());
                }
                syncCoordToDest();
            } else {
                $('#ruteKantor').show();
                $('#ruteTitik').hide();
                $('#ruteKoordinat').hide();
                $('#Destination').val('');
                $('#Dest_Lat').val('');
                $('#Dest_Lng').val('');
                $('#destPointInfo').html('');
            }
            updateRouteScope();
        }

        function toggleType() {
            var t = $('#Sppd_Type').val();
            $('#pbSection').toggle(t === 'PERBANTUAN');
            if (t === 'RELOKASI') {
                $('#Sppd_Type').val('NORMAL');
                toggleType();
                alert('Relokasi/Mutasi belum tersedia. Silakan pilih jenis lain.');
            }
            updateSppdNo();
            updateRouteScope();
        }

        function toggleNeedCar() {
            var need = $('#Need_Car').is(':checked');
            $('#NeedCarBody').toggle(need);
            if (need) {
                if (!$('#Car_Tgl_Pinjam').val()) $('#Car_Tgl_Pinjam').val($('#Date_Start').val());
                if (!$('#Car_Tgl_Kembali').val()) $('#Car_Tgl_Kembali').val($('#Date_Finish').val());
            }
        }

        function moneyID(v) {
            return Number(v).toLocaleString('id-ID');
        }

        function updateAllowanceInfo() {
            if (!matrixData) return;
            var sppdType = $('#Sppd_Type').val();
            var tripKey = sppdType === 'NORMAL' ? 'tugas_kerja' : (sppdType === 'PERBANTUAN' ? 'perbantuan_sementara' : 'relokasi');
            var trip = matrixData.trip_types[tripKey];
            if (!trip || !trip.allowances || !trip.allowances[userJobLevel]) return;

            var allowance = trip.allowances[userJobLevel];
            var info = '<div class="alert alert-light border py-2 mb-0 small"><strong>Hak Tunjangan (' + userJobLevel.replace(/_/g, ' ').toUpperCase() + '):</strong><br>';
            if (allowance.daily) {
                if ((allowance.daily.meal || 0) > 0) info += 'Uang Makan: Rp ' + moneyID(allowance.daily.meal) + '/hari &bull; ';
                if ((allowance.daily.pocket || 0) > 0) info += 'Uang Saku: Rp ' + moneyID(allowance.daily.pocket) + '/hari<br>';
            }
            if (allowance.transport && allowance.transport.ticket_max) {
                info += 'Tiket Maks: Rp ' + moneyID(allowance.transport.ticket_max) + '<br>';
            }
            if (allowance.transport && allowance.transport.reimburse_max_per_night) {
                info += 'Penginapan: Rp ' + moneyID(allowance.transport.reimburse_max_per_night) + '/malam<br>';
            }
            info += '</div>';

            $('#allowanceInfo').html(info);
            buildRegulationRows(allowance);
        }

        function collectManualRows() {
            var manuals = [];
            $('#tblCost tbody tr').not('.reg-row').each(function() {
                manuals.push({
                    cost: $(this).find("input[id^='Cost_']").val() || '',
                    desc: $(this).find("input[id^='Desc_']").val() || ''
                });
            });
            return manuals;
        }

        function buildRegulationRows(allowance) {
            var manuals = collectManualRows();
            var a = Date.parse($('#Date_Start').val());
            var b = Date.parse($('#Date_Finish').val());
            var hasDates = (!isNaN(a) && !isNaN(b) && b >= a);
            var days = hasDates ? Math.round((b - a) / 86400000) + 1 : 1;
            var nights = Math.max(days - 1, 0);
            var weeks = hasDates ? Math.ceil(days / 7) : 1;
            var months = hasDates ? Math.ceil(days / 30) : 1;

            var reg = [];
            var t = allowance.transport || {};
            var acc = allowance.accommodation || {};
            var daily = allowance.daily || {};
            var isTugasKerja = ($('#Sppd_Type').val() === 'NORMAL');

            if (isTugasKerja && routeScope === 'MEAL_ONLY') {
                if (daily.meal) {
                    reg.push({ cost: daily.meal * days, desc: 'Uang Makan (Rp ' + moneyID(daily.meal) + '/hari \u00d7 ' + days + ' hari)' });
                }
                renderCostRows(reg, manuals);
                return;
            }

            if (t.ticket_max) {
                reg.push({ cost: t.ticket_max, desc: 'Tiket PP (Maks. Rp ' + moneyID(t.ticket_max) + ')' });
            }
            if (isTugasKerja && t.reimburse_max_per_night && nights > 0) {
                reg.push({ cost: t.reimburse_max_per_night * nights, desc: 'Penginapan (Rp ' + moneyID(t.reimburse_max_per_night) + '/malam \u00d7 ' + nights + ' malam)' });
            }
            if (daily.meal) {
                reg.push({ cost: daily.meal * days, desc: 'Uang Makan (Rp ' + moneyID(daily.meal) + '/hari \u00d7 ' + days + ' hari)' });
            }
            if (daily.pocket) {
                reg.push({ cost: daily.pocket * days, desc: 'Uang Saku (Rp ' + moneyID(daily.pocket) + '/hari \u00d7 ' + days + ' hari)' });
            }
            if (allowance.assistance && allowance.assistance.kos_per_month) {
                reg.push({ cost: allowance.assistance.kos_per_month * months, desc: 'Uang Kos/Bantuan (' + months + ' bulan)' });
            }
            if (allowance.mobilization && allowance.mobilization.cost_per_week) {
                reg.push({ cost: allowance.mobilization.cost_per_week * weeks, desc: 'Uang Perbantuan (' + weeks + ' minggu)' });
            }

            renderCostRows(reg, manuals);
        }

        var rowCost = 1;
        function renderCostRows(regRows, manuals) {
            var escapeAttr = function(s) {
                return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
            };
            var $tb = $('#tblCost tbody').empty();
            var idx = 0;

            $.each(regRows, function(_, r) {
                $tb.append(
                    "<tr class='reg-row' data-reg='1'>" +
                    "<td><input type='number' id='Cost_" + idx + "' class='form-control form-control-sm bg-light fw-bold' value='" + escapeAttr(r.cost) + "' readonly /></td>" +
                    "<td><input type='text' id='Desc_" + idx + "' class='form-control form-control-sm bg-light' value=\"" + escapeAttr(r.desc) + "\" readonly /></td>" +
                    "<td class='text-center'><i class='fa fa-lock text-muted' title='Regulasi Master'></i></td>" +
                    "</tr>"
                );
                idx++;
            });

            if (!manuals.length) manuals.push({ cost: '', desc: '' });
            $.each(manuals, function(i, m) {
                var btn = (i === 0)
                    ? "<button type='button' class='btn btn-success btn-sm p-1 px-2' onclick='tambahRow();'>+</button>"
                    : "<button type='button' class='btn btn-danger btn-sm p-1 px-2' onclick='hapusRow(this);'>-</button>";
                $tb.append(
                    "<tr>" +
                    "<td><input type='number' id='Cost_" + idx + "' class='form-control form-control-sm' onkeyup='hitungTotal();' value='" + escapeAttr(m.cost) + "' placeholder='0' /></td>" +
                    "<td><input type='text' id='Desc_" + idx + "' class='form-control form-control-sm' value=\"" + escapeAttr(m.desc) + "\" placeholder='Keterangan' /></td>" +
                    "<td class='text-center'>" + btn + "</td>" +
                    "</tr>"
                );
                idx++;
            });

            rowCost = idx;
            hitungTotal();
        }

        function tambahRow() {
            var html = "<tr>" +
                "<td><input type='number' id='Cost_" + rowCost + "' class='form-control form-control-sm' onkeyup='hitungTotal();' placeholder='0' /></td>" +
                "<td><input type='text' id='Desc_" + rowCost + "' class='form-control form-control-sm' placeholder='Keterangan' /></td>" +
                "<td class='text-center'><button type='button' class='btn btn-danger btn-sm p-1 px-2' onclick='hapusRow(this);'>-</button></td>" +
                "</tr>";
            $("#tblCost tbody").append(html);
            rowCost++;
        }

        function hapusRow(btn) {
            $(btn).closest("tr").remove();
            hitungTotal();
        }

        function hitungTotal() {
            var total = 0;
            $("[id^='Cost_']").each(function() {
                var v = parseFloat($(this).val());
                if (!isNaN(v)) total += v;
            });
            $("#TotalCost").text(total.toLocaleString("id-ID"));
            $("#Estimated_Cost").val(total);
        }

        // ================= Leaflet Mobile Map Picker ==================
        var mapPicker = null, markerPicker = null, tempLatLng = null, mapModalObj = null;

        function validCoord(lat, lng) {
            if (lat === '' || lng === '' || lat === null || lng === null) return false;
            var la = parseFloat(String(lat).replace(',', '.'));
            var ln = parseFloat(String(lng).replace(',', '.'));
            if (isNaN(la) || isNaN(ln)) return false;
            return la >= -90 && la <= 90 && ln >= -180 && ln <= 180;
        }

        function syncCoordToDest() {
            if ($('#Rute_Mode').val() !== 'KOORDINAT') return;
            var lat = $.trim($('#Coord_Lat').val()).replace(',', '.');
            var lng = $.trim($('#Coord_Lng').val()).replace(',', '.');
            if (!validCoord(lat, lng)) {
                $('#Dest_Lat').val('');
                $('#Dest_Lng').val('');
                $('#coordPointInfo').html('<span class="text-danger small">Isi Latitude (-90 s/d 90) & Longitude yang valid.</span>');
                updateRouteScope();
                return;
            }
            var la = parseFloat(lat).toFixed(6), ln = parseFloat(lng).toFixed(6);
            $('#Dest_Lat').val(la);
            $('#Dest_Lng').val(ln);
            $('#coordPointInfo').html('Titik: ' + la + ', ' + ln);
            if ($.trim($('#Destination').val()) === '') {
                $('#Destination').val(la + ', ' + ln);
            }
            updateRouteScope();
        }

        function openMapModal() {
            if (!mapModalObj) {
                mapModalObj = new bootstrap.Modal(document.getElementById('modalMapPicker'));
            }
            mapModalObj.show();

            setTimeout(function() {
                var start = [-6.2, 106.8];
                if ($('#Dest_Lat').val() && $('#Dest_Lng').val()) {
                    start = [parseFloat($('#Dest_Lat').val()), parseFloat($('#Dest_Lng').val())];
                }
                if (!mapPicker) {
                    mapPicker = L.map('destPickerMap').setView(start, 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap',
                        maxZoom: 19
                    }).addTo(mapPicker);

                    markerPicker = L.marker(start, { draggable: true }).addTo(mapPicker);

                    function onPoint(latlng) {
                        tempLatLng = latlng;
                        markerPicker.setLatLng(latlng);
                        $('#destPickerCoord').text(latlng.lat.toFixed(6) + ', ' + latlng.lng.toFixed(6));
                    }

                    mapPicker.on('click', function(e) { onPoint(e.latlng); });
                    markerPicker.on('dragend', function(e) { onPoint(e.target.getLatLng()); });
                } else {
                    mapPicker.setView(start, 13);
                    markerPicker.setLatLng(start);
                }
                tempLatLng = { lat: start[0], lng: start[1] };
                $('#destPickerCoord').text(start[0].toFixed(6) + ', ' + start[1].toFixed(6));
                mapPicker.invalidateSize();
            }, 300);
        }

        function applyDestPoint() {
            if (!tempLatLng) return;
            var lat = tempLatLng.lat.toFixed(6), lng = tempLatLng.lng.toFixed(6);
            $('#Dest_Lat').val(lat);
            $('#Dest_Lng').val(lng);
            $('#destPointInfo').html('Titik: ' + lat + ', ' + lng);
            if ($('#Rute_Mode').val() === 'KOORDINAT') {
                $('#Coord_Lat').val(lat);
                $('#Coord_Lng').val(lng);
            }
            updateRouteScope();
            mapModalObj.hide();

            // Reverse geocode Nominatim
            $.get('https://nominatim.openstreetmap.org/reverse', {
                format: 'jsonv2',
                lat: lat,
                lon: lng,
                zoom: 16
            }).done(function(res) {
                if (res && res.display_name) {
                    $('#Destination').val(res.display_name);
                }
            });
        }

        $('#btnPickDestPoint, #btnPickCoordPoint').on('click', openMapModal);

        // ================= Ready Initialization ==================
        $(document).ready(function() {
            updateSppdNo();
            updateRuteMode();

            $.get('{{ url("hrd-sppd-matrix") }}', function(resp) {
                if (resp.status === 'success') {
                    matrixData = resp.data;
                    updateRouteScope();
                }
            });

            $('#Route_Start, #Route_Finish, #Departure_Time, #Return_Time').on('change', function() {
                updateRouteScope();
            });

            $('#Coord_Lat, #Coord_Lng').on('change blur', function() {
                syncCoordToDest();
            });
        });

        // ================= Form Submission ==================
        $('#mobileSppdForm').submit(function(e) {
            e.preventDefault();

            var a = new Date($('#Date_Start').val());
            var b = new Date($('#Date_Finish').val());
            if (b < a) {
                alert('Tanggal selesai tidak boleh sebelum tanggal mulai!');
                return;
            }
            var span = Math.floor((b - a) / 86400000) + 1;
            var st = $('#Sppd_Type').val();
            if (st === 'NORMAL' && span > 14) {
                alert('SPPD Regular maksimal berdurasi 14 hari!');
                return;
            }
            if (st === 'PERBANTUAN' && span <= 14) {
                alert('Perbantuan Sementara harus berdurasi lebih dari 14 hari!');
                return;
            }

            if (st === 'NORMAL' && !(routeResolved && routeScope)) {
                alert('Jarak & durasi rute belum dapat dihitung. Pastikan asal, tujuan & jam perjalanan diisi lengkap.');
                return;
            }

            if (parseFloat($('#Estimated_Cost').val()) <= 0) {
                alert('Estimasi biaya perjalanan dinas belum terisi!');
                return;
            }

            var formData = {};
            $.each($(this).serializeArray(), function(i, field) {
                formData[field.name] = field.value;
            });
            formData['Need_Car'] = $('#Need_Car').is(':checked') ? 1 : 0;

            // Masukkan array rincian biaya
            var costs = [];
            $("[id^='Cost_']").each(function() {
                var idx = $(this).attr('id').replace('Cost_', '');
                var c = $(this).val();
                var d = $('#Desc_' + idx).val();
                if (c && parseFloat(c) > 0) {
                    costs.push({ Cost: c, Description: d });
                }
            });
            formData['costs'] = costs;

            $('#btnSubmit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Memproses SPPD...');

            $.ajax({
                url: "{{ route('sppd.mobile.api.submit') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(res) {
                    alert('Sukses! ' + res.message);
                    window.location.href = "{{ route('sppd.mobile.index') }}";
                },
                error: function(xhr) {
                    $('#btnSubmit').prop('disabled', false).html('<i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan SPPD');
                    var msg = 'Gagal menyimpan pengajuan SPPD';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                }
            });
        });
    </script>
</body>
</html>
