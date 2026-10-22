@extends('layout.mainlayout-asset')

@section('content-wrapper')
@csrf
<div class="main-content-header mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="h3 font-weight-bold mb-1 text-dark">
                <i class="feather icon-truck text-primary mr-2"></i>Operasional Kendaraan: Logbook & Fuel Tracking
            </h1>
            <ol class="breadcrumb bg-transparent p-0 mb-0">
                <li class="breadcrumb-item"><a href="home-asset">Asset</a></li>
                <li class="breadcrumb-item"><a href="asset-lists">Data Assets</a></li>
                <li class="breadcrumb-item active text-primary">Operasional Kendaraan</li>
            </ol>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="asset-lists" class="btn btn-outline-secondary btn-sm mr-1">
                <i class="feather icon-arrow-left mr-1"></i>Kembali ke Asset Lists
            </a>
            <button class="btn btn-primary btn-sm" onclick="refreshAllData()">
                <i class="feather icon-refresh-cw mr-1"></i>Refresh Data
            </button>
        </div>
    </div>
</div>

<!-- FILTER BAR -->
<div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
    <div class="card-body py-2 px-3">
        <div class="form-row align-items-center">
            <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                <label class="small font-weight-bold text-muted mb-1">Pilih Kendaraan:</label>
                <select id="filter_asset_no" class="form-control form-control-sm" onchange="applyFilter()">
                    <option value="ALL">-- Semua Kendaraan Armada --</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->Asset_No }}">
                            {{ $v->Registration_No ?: $v->Asset_No }} - {{ $v->Unit_Name ?: ($v->Product_Brand . ' ' . $v->Model) }} ({{ $v->Asset_Type ?: 'Kendaraan' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                <label class="small font-weight-bold text-muted mb-1">Periode Bulan:</label>
                <input type="month" id="filter_month" class="form-control form-control-sm" value="{{ date('Y-m') }}" onchange="applyFilter()">
            </div>
            <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                <label class="small font-weight-bold text-muted mb-1">Status Trip:</label>
                <select id="filter_status" class="form-control form-control-sm" onchange="applyFilter()">
                    <option value="ALL">Semua Status</option>
                    <option value="ON_GOING">Sedang Berjalan (Check-Out)</option>
                    <option value="COMPLETED">Selesai (Check-In)</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6 text-right mt-md-4">
                <button type="button" class="btn btn-outline-secondary btn-sm btn-block" onclick="resetFilter()">
                    <i class="feather icon-rotate-ccw mr-1"></i>Reset
                </button>
            </div>
        </div>
    </div>
</div>

<!-- KPI SUMMARY METRIC CARDS -->
<div class="row mb-3">
    <!-- Card 1: Total Jarak Tempuh -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase small font-weight-bold text-light opacity-75">Jarak Tempuh</span>
                    <h3 class="font-weight-bold my-1 text-white" id="kpi_total_km">0 KM</h3>
                    <small class="text-light" id="kpi_trips_sub">0 trip selesai • 0 berjalan</small>
                </div>
                <div class="p-3 bg-white text-primary rounded-circle shadow-sm" style="opacity: 0.95;">
                    <i class="feather icon-navigation" style="font-size: 24px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Pengeluaran BBM -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #f857a6 0%, #ff5858 100%); color: white;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase small font-weight-bold text-light opacity-75">Biaya BBM</span>
                    <h3 class="font-weight-bold my-1 text-white" id="kpi_fuel_cost">Rp 0</h3>
                    <small class="text-light" id="kpi_cost_per_km">Rata-rata: Rp 0 / KM</small>
                </div>
                <div class="p-3 bg-white text-danger rounded-circle shadow-sm" style="opacity: 0.95;">
                    <i class="feather icon-credit-card" style="font-size: 24px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Konsumsi Volume BBM -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase small font-weight-bold text-light opacity-75">Konsumsi BBM</span>
                    <h3 class="font-weight-bold my-1 text-white" id="kpi_total_liters">0 L</h3>
                    <small class="text-light" id="kpi_fuel_fills">0 kali pengisian</small>
                </div>
                <div class="p-3 bg-white text-success rounded-circle shadow-sm" style="opacity: 0.95;">
                    <i class="feather icon-droplet" style="font-size: 24px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Efisiensi Armada (KM/L) -->
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); color: white;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase small font-weight-bold text-light opacity-75">Efisiensi Armada</span>
                    <h3 class="font-weight-bold my-1 text-white" id="kpi_avg_efficiency">0 KM/L</h3>
                    <small class="text-light" id="kpi_efficiency_status">Status: Belum ada data</small>
                </div>
                <div class="p-3 bg-white text-purple rounded-circle shadow-sm" style="opacity: 0.95;">
                    <i class="feather icon-activity" style="font-size: 24px; color: #7F00FF;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MAIN TABS CONTAINER -->
<div class="card shadow-sm border-0" style="border-radius: 12px;">
    <div class="card-header bg-white border-bottom-0 pb-0 pt-3">
        <ul class="nav nav-pills" id="operationsTabs" role="tablist">
            <li class="nav-item mr-2">
                <a class="nav-link active font-weight-bold px-3 py-2" id="tab-logbook-btn" data-toggle="pill" href="#tab-logbook" role="tab" style="border-radius: 8px;">
                    <i class="feather icon-book mr-1"></i> Logbook Perjalanan (Trip Logs)
                </a>
            </li>
            <li class="nav-item mr-2">
                <a class="nav-link font-weight-bold px-3 py-2" id="tab-fuel-btn" data-toggle="pill" href="#tab-fuel" role="tab" style="border-radius: 8px;">
                    <i class="feather icon-droplet mr-1"></i> Fuel Tracking (Pencatatan BBM)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold px-3 py-2" id="tab-analytics-btn" data-toggle="pill" href="#tab-analytics" role="tab" style="border-radius: 8px;">
                    <i class="feather icon-pie-chart mr-1"></i> Analytics & Visualisasi Efisiensi
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body pt-3">
        <div class="tab-content" id="operationsTabsContent">

            <!-- TAB 1: LOGBOOK PERJALANAN -->
            <div class="tab-pane fade show active" id="tab-logbook" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <div class="mb-2 mb-sm-0">
                        <button type="button" class="btn btn-primary font-weight-bold mr-2" onclick="openNewTripModal()">
                            <i class="feather icon-plus-circle mr-1"></i> Catat Perjalanan Baru (Check-Out)
                        </button>
                        <button type="button" class="btn btn-outline-info mr-2" onclick="refreshLogbookGrid()">
                            <i class="feather icon-refresh-cw mr-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="GRID_LOGBOOK.toExcel('./export/generate.php')">
                            <i class="feather icon-download mr-1"></i> Export Excel (CSV)
                        </button>
                    </div>
                    <div>
                        <span class="badge badge-light p-2 font-weight-normal border text-muted">
                            <i class="feather icon-info text-primary mr-1"></i> Double click baris untuk melihat detail
                        </span>
                    </div>
                </div>

                <div id="GRID_LOGBOOK_CONTAINER" style="width:100%; height:450px; background-color:white; overflow:hidden; border: 1px solid #e0e0e0; border-radius: 6px;"></div>
            </div>

            <!-- TAB 2: FUEL TRACKING -->
            <div class="tab-pane fade" id="tab-fuel" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <div class="mb-2 mb-sm-0">
                        <button type="button" class="btn btn-primary font-weight-bold mr-2" onclick="openNewFuelModal()">
                            <i class="feather icon-plus-circle mr-1"></i> Catat Pengisian BBM
                        </button>
                        <button type="button" class="btn btn-outline-info mr-2" onclick="refreshFuelGrid()">
                            <i class="feather icon-refresh-cw mr-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="GRID_FUEL.toExcel('./export/generate.php')">
                            <i class="feather icon-download mr-1"></i> Export Excel (CSV)
                        </button>
                    </div>
                    <div>
                        <span class="badge badge-light p-2 font-weight-normal border text-muted">
                            <i class="feather icon-check-circle text-success mr-1"></i> Kalkulasi Rasio KM/L & Biaya/KM Otomatis
                        </span>
                    </div>
                </div>

                <div id="GRID_FUEL_CONTAINER" style="width:100%; height:450px; background-color:white; overflow:hidden; border: 1px solid #e0e0e0; border-radius: 6px;"></div>
            </div>

            <!-- TAB 3: ANALYTICS & VISUALISASI -->
            <div class="tab-pane fade" id="tab-analytics" role="tabpanel">
                <div class="row">
                    <!-- Chart 1: Tren Bulanan Jarak Tempuh vs Liter BBM -->
                    <div class="col-lg-7 mb-4">
                        <div class="card border shadow-sm h-100" style="border-radius: 8px;">
                            <div class="card-header bg-white py-2">
                                <h6 class="font-weight-bold text-dark m-0">
                                    <i class="feather icon-trending-up text-primary mr-1"></i> Tren Bulanan Jarak Tempuh (KM) & Konsumsi BBM (Liter)
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <canvas id="chartMonthlyTrend" height="240"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2: Perbandingan Efisiensi Armada (KM/L) -->
                    <div class="col-lg-5 mb-4">
                        <div class="card border shadow-sm h-100" style="border-radius: 8px;">
                            <div class="card-header bg-white py-2">
                                <h6 class="font-weight-bold text-dark m-0">
                                    <i class="feather icon-bar-chart text-success mr-1"></i> Rasio Efisiensi BBM per Armada (KM/Liter)
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                <canvas id="chartEfficiency" height="240"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Ringkasan Armada -->
                <div class="card border shadow-sm" style="border-radius: 8px;">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold text-dark m-0">
                            <i class="feather icon-list text-info mr-1"></i> Ringkasan Konsumsi & Efisiensi Tiap Kendaraan
                        </h6>
                        <span class="small text-muted" id="analytics_period_badge">Periode: Bulan Ini</span>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover table-striped mb-0 text-center" id="tableVehicleSummary">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Plat Nomor</th>
                                    <th class="text-left">Nama Kendaraan</th>
                                    <th>Total Jarak (KM)</th>
                                    <th>Total BBM (Liter)</th>
                                    <th>Total Biaya (Rp)</th>
                                    <th>Efisiensi (KM/L)</th>
                                    <th>Biaya per KM</th>
                                    <th>Status Efisiensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="9" class="py-3 text-muted">Memuat data ringkasan armada...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- MODAL 1: CATAT PERJALANAN BARU (CHECK-OUT) -->
<div class="modal fade" id="modalNewTrip" tabindex="-1" role="dialog" aria-labelledby="modalNewTripLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formNewTrip" onsubmit="submitNewTrip(event)" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modalNewTripLabel">
                        <i class="feather icon-play-circle mr-2"></i>Catat Perjalanan Baru (Check-Out Kendaraan)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Pilih Kendaraan <span class="text-danger">*</span></label>
                            <select name="Asset_No" id="trip_asset_no" class="form-control" required onchange="onTripVehicleChange()">
                                <option value="">-- Pilih Kendaraan --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->Asset_No }}">
                                        {{ $v->Registration_No ?: $v->Asset_No }} - {{ $v->Unit_Name ?: ($v->Product_Brand . ' ' . $v->Model) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="trip_vehicle_status"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Pengemudi / Driver <span class="text-danger">*</span></label>
                            <input type="text" name="Driver_Name" id="trip_driver_name" class="form-control" placeholder="Nama Driver / Pemakai" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Waktu Berangkat <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="Start_Date" id="trip_start_date" class="form-control" required value="{{ date('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Odometer Awal (KM) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="Start_KM" id="trip_start_km" class="form-control" placeholder="0" required>
                            <small class="text-muted">Terisi otomatis dari Odometer terakhir</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Departemen</label>
                            <input type="text" name="Department" id="trip_department" class="form-control" placeholder="HCGA / Ops / dll">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Tujuan Perjalanan <span class="text-danger">*</span></label>
                            <input type="text" name="Destination" class="form-control" placeholder="Contoh: Kantor Cabang, Gudang, Bandara" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Kondisi Kendaraan (Keluar)</label>
                            <select name="Vehicle_Condition_Out" class="form-control">
                                <option value="Baik & Prima">Baik & Prima</option>
                                <option value="Normal / Cukup">Normal / Cukup</option>
                                <option value="Perlu Perhatian (AC/Ban)">Perlu Perhatian (AC/Ban)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold">Keperluan / Keterangan Perjalanan</label>
                        <textarea name="Purpose" class="form-control" rows="2" placeholder="Jelaskan agenda / tujuan tugas perjalanan..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Foto Odometer Awal (Opsional)</label>
                            <input type="file" name="Photo_Odometer_Start" class="form-control-file" accept="image/*" onchange="previewImage(this, 'preview_odo_start')">
                            <div class="mt-2">
                                <img id="preview_odo_start" src="" style="max-height: 100px; display: none; border-radius: 6px; border: 1px solid #ccc;">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Catatan Tambahan</label>
                            <input type="text" name="Notes" class="form-control" placeholder="Catatan opsional...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitNewTrip" class="btn btn-primary font-weight-bold">
                        <i class="feather icon-check mr-1"></i> Simpan & Check-Out
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: SELESAIKAN PERJALANAN (CHECK-IN) -->
<div class="modal fade" id="modalFinishTrip" tabindex="-1" role="dialog" aria-labelledby="modalFinishTripLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formFinishTrip" onsubmit="submitFinishTrip(event)" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" id="finish_trip_id">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modalFinishTripLabel">
                        <i class="feather icon-check-circle mr-2"></i>Selesaikan Perjalanan (Check-In)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 mb-3" style="border-radius: 8px;">
                        <div class="d-flex justify-content-between">
                            <span class="font-weight-bold" id="finish_log_no">-</span>
                            <span class="badge badge-warning text-dark font-weight-bold" id="finish_reg_no">-</span>
                        </div>
                        <div class="small mt-1 text-muted">
                            KM Awal: <strong id="finish_start_km_label">0</strong> KM
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Waktu Kembali <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="End_Date" class="form-control" required value="{{ date('Y-m-d\TH:i') }}">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Odometer Akhir (KM) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" name="End_KM" id="finish_end_km" class="form-control form-control-lg font-weight-bold text-primary" placeholder="0" required oninput="calculateTripTotalKM()">
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">Total Jarak Tempuh:</small>
                            <span class="badge badge-success px-2 py-1" style="font-size: 14px;" id="badge_calculated_km">0 KM</span>
                        </div>
                        <small class="text-danger" id="finish_km_error" style="display: none;">KM Akhir harus lebih besar atau sama dengan KM Awal!</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Kondisi Kendaraan Saat Kembali</label>
                        <select name="Vehicle_Condition_In" class="form-control">
                            <option value="Baik & Bersih">Baik & Bersih</option>
                            <option value="Normal">Normal</option>
                            <option value="Kotor Perlu Cuci">Kotor Perlu Cuci</option>
                            <option value="Perlu Servis / Cek Kendala">Perlu Servis / Cek Kendala</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Foto Odometer Akhir (Opsional)</label>
                        <input type="file" name="Photo_Odometer_End" class="form-control-file" accept="image/*" onchange="previewImage(this, 'preview_odo_end')">
                        <div class="mt-2">
                            <img id="preview_odo_end" src="" style="max-height: 90px; display: none; border-radius: 6px; border: 1px solid #ccc;">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Catatan Pengembalian</label>
                        <input type="text" name="Notes" class="form-control" placeholder="Contoh: BBM tersisa separuh, ban baru dipompa">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitFinishTrip" class="btn btn-success font-weight-bold">
                        <i class="feather icon-check mr-1"></i> Simpan & Selesaikan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: CATAT PENGISIAN BBM (FUEL TRACKING) -->
<div class="modal fade" id="modalNewFuel" tabindex="-1" role="dialog" aria-labelledby="modalNewFuelLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="formNewFuel" onsubmit="submitNewFuel(event)" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modalNewFuelLabel">
                        <i class="feather icon-droplet mr-2"></i>Catat Pengisian Bahan Bakar (BBM)
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Pilih Kendaraan <span class="text-danger">*</span></label>
                            <select name="Asset_No" id="fuel_asset_no" class="form-control" required onchange="onFuelVehicleChange()">
                                <option value="">-- Pilih Kendaraan --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->Asset_No }}">
                                        {{ $v->Registration_No ?: $v->Asset_No }} - {{ $v->Unit_Name ?: ($v->Product_Brand . ' ' . $v->Model) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" id="fuel_last_km_info"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Driver / Petugas Pengisi <span class="text-danger">*</span></label>
                            <input type="text" name="Driver_Name" id="fuel_driver_name" class="form-control" placeholder="Nama Petugas / Driver" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Tanggal & Waktu Pengisian <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="Fill_Date" class="form-control" required value="{{ date('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Odometer Saat Pengisian (KM) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="Current_KM" id="fuel_current_km" class="form-control font-weight-bold" placeholder="0" required oninput="calculateFuelMetrics()">
                            <small class="text-muted" id="fuel_distance_calc">Jarak dari isi sebelumnya: 0 KM</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Jenis BBM <span class="text-danger">*</span></label>
                            <select name="Fuel_Type" id="fuel_type_select" class="form-control" required>
                                <option value="Pertalite">Pertalite (RON 90)</option>
                                <option value="Pertamax">Pertamax (RON 92)</option>
                                <option value="Pertamax Turbo">Pertamax Turbo (RON 98)</option>
                                <option value="Solar / Biosolar">Solar / Biosolar</option>
                                <option value="Dexlite">Dexlite (CN 51)</option>
                                <option value="Pertamina Dex">Pertamina Dex (CN 53)</option>
                                <option value="Shell / BP / Lainnya">Shell / BP / Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Volume Liter <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="Liters" id="fuel_liters" class="form-control form-control-lg font-weight-bold" placeholder="0.00" required oninput="calculateFuelMetrics()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Total Biaya (Rp) <span class="text-danger">*</span></label>
                            <input type="number" step="1" name="Total_Cost" id="fuel_total_cost" class="form-control form-control-lg font-weight-bold text-success" placeholder="0" required oninput="calculateFuelMetrics()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold text-muted">Kalkulasi Otomatis</label>
                            <div class="p-2 bg-light border rounded">
                                <div class="small d-flex justify-content-between mb-1">
                                    <span>Rp / Liter:</span>
                                    <strong id="calc_price_per_liter">-</strong>
                                </div>
                                <div class="small d-flex justify-content-between mb-1">
                                    <span>Konsumsi:</span>
                                    <strong class="text-primary" id="calc_km_per_liter">-</strong>
                                </div>
                                <div class="small d-flex justify-content-between">
                                    <span>Biaya / KM:</span>
                                    <strong class="text-success" id="calc_cost_per_km">-</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Lokasi SPBU / Nama SPBU</label>
                            <input type="text" name="Gas_Station" class="form-control" placeholder="Contoh: SPBU Pertamina 31.xxx / Jl. Sudirman">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Metode Pembayaran</label>
                            <select name="Payment_Method" class="form-control">
                                <option value="MyPertamina">MyPertamina / QRIS</option>
                                <option value="Reimburse">Reimburse Karyawan</option>
                                <option value="Kasbon Operasional">Kasbon Operasional (GA/Finance)</option>
                                <option value="Kartu Armada (Corporate Card)">Kartu Armada (Corporate Card)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Upload Foto Struk / Nota BBM</label>
                            <input type="file" name="Receipt_Photo" class="form-control-file" accept="image/*" onchange="previewImage(this, 'preview_receipt')">
                            <div class="mt-2">
                                <img id="preview_receipt" src="" style="max-height: 100px; display: none; border-radius: 6px; border: 1px solid #ccc;">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Upload Foto Odometer (Opsional)</label>
                            <input type="file" name="Odometer_Photo" class="form-control-file" accept="image/*" onchange="previewImage(this, 'preview_fuel_odo')">
                            <div class="mt-2">
                                <img id="preview_fuel_odo" src="" style="max-height: 100px; display: none; border-radius: 6px; border: 1px solid #ccc;">
                            </div>
                        </div>
                    </div>

                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="check_full_tank" name="Full_Tank" value="1" checked>
                        <label class="custom-control-label small font-weight-bold text-muted" for="check_full_tank">
                            Pengisian Full Tank (Disarankan dicentang agar rasio efisiensi KM/L akurat)
                        </label>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Catatan</label>
                        <input type="text" name="Notes" class="form-control" placeholder="Catatan tambahan...">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitNewFuel" class="btn btn-success font-weight-bold">
                        <i class="feather icon-check mr-1"></i> Simpan Catatan BBM
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPTS: DHTMLX + CHART.JS + CONTROLLER LOGIC -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
var BASE_OPS_URL = 'hrd-vehicle-operations';
var GRID_LOGBOOK = null;
var GRID_FUEL = null;
var CHART_TREND = null;
var CHART_EFFICIENCY = null;
var CURRENT_VEHICLE_INFO = null;
var FINISH_START_KM = 0;

$(document).ready(function() {
    initLogbookGrid();
    initFuelGrid();
    loadKPIData();

    // Trigger tab resize bila berpindah tab agar grid dhtmlx menyesuaikan lebar
    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        if (e.target.id === 'tab-logbook-btn' && GRID_LOGBOOK) {
            GRID_LOGBOOK.setSizes();
        } else if (e.target.id === 'tab-fuel-btn' && GRID_FUEL) {
            GRID_FUEL.setSizes();
        } else if (e.target.id === 'tab-analytics-btn') {
            loadKPIData();
        }
    });
});

/* ================= 1. DHTMLX GRID LOGBOOK ================= */
function initLogbookGrid() {
    GRID_LOGBOOK = new dhtmlXGridObject('GRID_LOGBOOK_CONTAINER');
    GRID_LOGBOOK.setImagePath("./dhtmlx/imgs/");
    GRID_LOGBOOK.setHeader("No,No Log,Status,Plat Nomor,Kendaraan,Driver,Departemen,Waktu Berangkat,Waktu Kembali,KM Awal,KM Akhir,Total KM,Tujuan,Keperluan,Kondisi,Foto Odo,Aksi");
    GRID_LOGBOOK.attachHeader("#rspan,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan");
    GRID_LOGBOOK.setInitWidths("45,130,90,110,140,120,100,125,125,85,85,85,140,150,110,95,120");
    GRID_LOGBOOK.setColAlign("center,left,center,center,left,left,left,center,center,right,right,right,left,left,left,center,center");
    GRID_LOGBOOK.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro,ro");
    GRID_LOGBOOK.setColSorting("int,str,str,str,str,str,str,str,str,int,int,int,str,str,str,str,str");
    GRID_LOGBOOK.setNumberFormat("0,000.0", 9, ".", ",");
    GRID_LOGBOOK.setNumberFormat("0,000.0", 10, ".", ",");
    GRID_LOGBOOK.setNumberFormat("0,000.0", 11, ".", ",");
    GRID_LOGBOOK.enableSmartRendering(true, 150);
    GRID_LOGBOOK.setSkin("dhx_skyblue");
    GRID_LOGBOOK.init();

    refreshLogbookGrid();
}

function refreshLogbookGrid() {
    if (!GRID_LOGBOOK) return;
    var assetNo = $('#filter_asset_no').val() || 'ALL';
    var month = $('#filter_month').val() || '';
    var status = $('#filter_status').val() || 'ALL';
    var url = BASE_OPS_URL + '-logbook-data?asset_no=' + encodeURIComponent(assetNo) + '&month=' + encodeURIComponent(month) + '&status=' + encodeURIComponent(status);
    GRID_LOGBOOK.clearAndLoad(url);
}

/* ================= 2. DHTMLX GRID FUEL ================= */
function initFuelGrid() {
    GRID_FUEL = new dhtmlXGridObject('GRID_FUEL_CONTAINER');
    GRID_FUEL.setImagePath("./dhtmlx/imgs/");
    GRID_FUEL.setHeader("No,No Transaksi,Tanggal,Plat Nomor,Kendaraan,Driver,Odometer (KM),Jarak (KM),Jenis BBM,Liter,Total Biaya (Rp),Rp/Liter,Efisiensi,Biaya/KM,SPBU,Pembayaran,Bukti Struk,Aksi");
    GRID_FUEL.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#select_filter,#numeric_filter,#numeric_filter,#numeric_filter,#rspan,#numeric_filter,#text_filter,#select_filter,#rspan,#rspan");
    GRID_FUEL.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan");
    GRID_FUEL.setInitWidths("45,130,125,110,140,120,95,85,110,75,105,85,95,90,130,120,75,70");
    GRID_FUEL.setColAlign("center,left,center,center,left,left,right,right,center,right,right,right,center,right,left,left,center,center");
    GRID_FUEL.setColTypes("ro,ro,ro,ro,ro,ro,ron,ron,ro,ron,ron,ron,ro,ron,ro,ro,ro,ro");
    GRID_FUEL.setColSorting("int,str,str,str,str,str,int,int,str,int,int,int,str,int,str,str,str,str");
    GRID_FUEL.setNumberFormat("0,000.0", 6, ".", ",");
    GRID_FUEL.setNumberFormat("0,000.0", 7, ".", ",");
    GRID_FUEL.setNumberFormat("0,000.00", 9, ".", ",");
    GRID_FUEL.setNumberFormat("0,000", 10, ".", ",");
    GRID_FUEL.setNumberFormat("0,000", 11, ".", ",");
    GRID_FUEL.setNumberFormat("0,000", 13, ".", ",");
    GRID_FUEL.enableSmartRendering(true, 150);
    GRID_FUEL.setSkin("dhx_skyblue");
    GRID_FUEL.init();

    refreshFuelGrid();
}

function refreshFuelGrid() {
    if (!GRID_FUEL) return;
    var assetNo = $('#filter_asset_no').val() || 'ALL';
    var month = $('#filter_month').val() || '';
    var url = BASE_OPS_URL + '-fuel-data?asset_no=' + encodeURIComponent(assetNo) + '&month=' + encodeURIComponent(month);
    GRID_FUEL.clearAndLoad(url);
}

/* ================= 3. KPI & ANALYTICS CHARTS ================= */
function loadKPIData() {
    var assetNo = $('#filter_asset_no').val() || 'ALL';
    var month = $('#filter_month').val() || '';

    $.getJSON(BASE_OPS_URL + '-kpi-data', { asset_no: assetNo, month: month }, function(res) {
        if (res.status === 'success' && res.data) {
            var sum = res.data.summary;

            $('#kpi_total_km').text(Number(sum.total_km).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + ' KM');
            $('#kpi_trips_sub').text(sum.total_trips + ' trip selesai • ' + sum.active_trips + ' berjalan');

            $('#kpi_fuel_cost').text('Rp ' + Number(sum.total_fuel_cost).toLocaleString('id-ID'));
            $('#kpi_cost_per_km').text('Rata-rata: Rp ' + Number(sum.avg_cost_per_km).toLocaleString('id-ID') + ' / KM');

            $('#kpi_total_liters').text(Number(sum.total_liters).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + ' L');
            $('#kpi_fuel_fills').text(sum.fuel_fill_count + ' pengisian • Rp ' + Number(sum.avg_cost_per_liter).toLocaleString('id-ID') + '/L');

            $('#kpi_avg_efficiency').text(Number(sum.avg_km_per_liter).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' KM/L');

            if (sum.avg_km_per_liter >= 10) {
                $('#kpi_efficiency_status').html('<span class="badge badge-success">Efisiensi Sangat Baik</span>');
            } else if (sum.avg_km_per_liter >= 7) {
                $('#kpi_efficiency_status').html('<span class="badge badge-warning text-dark">Efisiensi Cukup</span>');
            } else if (sum.avg_km_per_liter > 0) {
                $('#kpi_efficiency_status').html('<span class="badge badge-danger">Perlu Pengecekan</span>');
            } else {
                $('#kpi_efficiency_status').text('Status: Belum ada data');
            }

            renderCharts(res.data.chart_trend, res.data.vehicle_comparison);
            renderVehicleSummaryTable(res.data.vehicle_comparison);
        }
    });
}

function renderCharts(trendData, comparisonData) {
    // 1. Chart Monthly Trend (KM & Liters)
    var ctxTrend = document.getElementById('chartMonthlyTrend');
    if (ctxTrend) {
        if (CHART_TREND) CHART_TREND.destroy();

        CHART_TREND = new Chart(ctxTrend.getContext('2d'), {
            type: 'bar',
            data: {
                labels: trendData.labels,
                datasets: [
                    {
                        label: 'Jarak Tempuh (KM)',
                        data: trendData.km,
                        backgroundColor: 'rgba(30, 60, 114, 0.7)',
                        borderColor: '#1e3c72',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Konsumsi BBM (Liter)',
                        data: trendData.liters,
                        type: 'line',
                        borderColor: '#ff5858',
                        backgroundColor: 'rgba(255, 88, 88, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Jarak Tempuh (KM)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Liter BBM' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    // 2. Chart Efficiency Comparison
    var ctxEff = document.getElementById('chartEfficiency');
    if (ctxEff) {
        if (CHART_EFFICIENCY) CHART_EFFICIENCY.destroy();

        var labels = [];
        var values = [];
        var colors = [];

        (comparisonData || []).forEach(function(item) {
            labels.push(item.Plate);
            values.push(item.KmPerLiter);
            colors.push(item.KmPerLiter >= 10 ? '#38ef7d' : (item.KmPerLiter >= 7 ? '#f7b731' : '#eb3b5a'));
        });

        CHART_EFFICIENCY = new Chart(ctxEff.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Belum ada data'],
                datasets: [{
                    label: 'KM per Liter',
                    data: values.length ? values : [0],
                    backgroundColor: colors.length ? colors : ['#ccc'],
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Rasio KM/L' },
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

function renderVehicleSummaryTable(list) {
    var $tbody = $('#tableVehicleSummary tbody');
    $tbody.empty();

    if (!list || list.length === 0) {
        $tbody.append('<tr><td colspan="9" class="py-3 text-muted">Tidak ada transaksi kendaraan pada filter yang dipilih.</td></tr>');
        return;
    }

    list.forEach(function(item, idx) {
        var statusBadge = item.KmPerLiter >= 10 
            ? '<span class="badge badge-success px-2 py-1">Sangat Hemat</span>'
            : (item.KmPerLiter >= 7 
                ? '<span class="badge badge-warning text-dark px-2 py-1">Normal / Cukup</span>'
                : (item.KmPerLiter > 0 
                    ? '<span class="badge badge-danger px-2 py-1">Boros / Cek Servis</span>'
                    : '<span class="badge badge-secondary px-2 py-1">-</span>'));

        var html = '<tr>' +
            '<td>' + (idx + 1) + '</td>' +
            '<td class="font-weight-bold text-primary">' + item.Plate + '</td>' +
            '<td class="text-left">' + item.Name + '</td>' +
            '<td>' + Number(item.KM).toLocaleString('id-ID', { minimumFractionDigits: 1 }) + ' KM</td>' +
            '<td>' + Number(item.Liters).toLocaleString('id-ID', { minimumFractionDigits: 1 }) + ' L</td>' +
            '<td>Rp ' + Number(item.Cost).toLocaleString('id-ID') + '</td>' +
            '<td class="font-weight-bold">' + (item.KmPerLiter > 0 ? Number(item.KmPerLiter).toFixed(2) + ' KM/L' : '-') + '</td>' +
            '<td>' + (item.CostPerKM > 0 ? 'Rp ' + Number(item.CostPerKM).toLocaleString('id-ID') : '-') + '</td>' +
            '<td>' + statusBadge + '</td>' +
        '</tr>';
        $tbody.append(html);
    });
}

/* ================= 4. FILTER ACTIONS ================= */
function applyFilter() {
    refreshLogbookGrid();
    refreshFuelGrid();
    loadKPIData();
    $('#analytics_period_badge').text('Periode: ' + ($('#filter_month').val() || 'Semua'));
}

function resetFilter() {
    $('#filter_asset_no').val('ALL');
    $('#filter_month').val('{{ date("Y-m") }}');
    $('#filter_status').val('ALL');
    applyFilter();
}

function refreshAllData() {
    applyFilter();
}

/* ================= 5. MODAL CHECK-OUT LOGBOOK ================= */
function openNewTripModal() {
    $('#formNewTrip')[0].reset();
    $('#preview_odo_start').hide().attr('src', '');
    $('#trip_vehicle_status').text('');
    var selected = $('#filter_asset_no').val();
    if (selected && selected !== 'ALL') {
        $('#trip_asset_no').val(selected);
        onTripVehicleChange();
    }
    $('#modalNewTrip').modal('show');
}

function onTripVehicleChange() {
    var assetNo = $('#trip_asset_no').val();
    if (!assetNo) {
        $('#trip_start_km').val('');
        $('#trip_vehicle_status').text('');
        return;
    }

    $.getJSON(BASE_OPS_URL + '-vehicle-info', { asset_no: assetNo }, function(res) {
        if (res.status === 'success' && res.data) {
            CURRENT_VEHICLE_INFO = res.data;
            $('#trip_start_km').val(res.data.Current_KM);
            if (res.data.User_Responsible) {
                $('#trip_driver_name').val(res.data.User_Responsible);
            }
            if (res.data.Department) {
                $('#trip_department').val(res.data.Department);
            }

            if (res.data.Is_On_Trip) {
                $('#trip_vehicle_status').html('<span class="text-danger font-weight-bold"><i class="feather icon-alert-triangle mr-1"></i>Peringatan: Kendaraan ini sedang dalam perjalanan aktif (' + res.data.Active_Trip.Log_No + ')</span>');
                $('#btnSubmitNewTrip').prop('disabled', true);
            } else {
                $('#trip_vehicle_status').html('<span class="text-success"><i class="feather icon-check mr-1"></i>Siap digunakan • Odometer terakhir: ' + res.data.Current_KM + ' KM</span>');
                $('#btnSubmitNewTrip').prop('disabled', false);
            }
        }
    });
}

function submitNewTrip(e) {
    e.preventDefault();
    var form = document.getElementById('formNewTrip');
    var formData = new FormData(form);

    $('#btnSubmitNewTrip').prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...');

    $.ajax({
        url: BASE_OPS_URL + '-logbook-submit',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
            $('#modalNewTrip').modal('hide');
            alert(res.message);
            refreshLogbookGrid();
            loadKPIData();
        },
        error: function(xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
            alert('Error: ' + msg);
        },
        complete: function() {
            $('#btnSubmitNewTrip').prop('disabled', false).html('<i class="feather icon-check mr-1"></i> Simpan & Check-Out');
        }
    });
}

/* ================= 6. MODAL CHECK-IN (FINISH) LOGBOOK ================= */
function finishLogbook(id, logNo, regNo, startKM) {
    $('#finish_trip_id').val(id);
    $('#finish_log_no').text(logNo);
    $('#finish_reg_no').text(regNo);
    FINISH_START_KM = parseFloat(startKM) || 0;
    $('#finish_start_km_label').text(FINISH_START_KM.toLocaleString('id-ID', { minimumFractionDigits: 1 }));
    $('#finish_end_km').val(FINISH_START_KM);
    $('#preview_odo_end').hide().attr('src', '');
    $('#finish_km_error').hide();
    calculateTripTotalKM();
    $('#modalFinishTrip').modal('show');
}

function calculateTripTotalKM() {
    var endKM = parseFloat($('#finish_end_km').val()) || 0;
    if (endKM < FINISH_START_KM) {
        $('#finish_km_error').show();
        $('#btnSubmitFinishTrip').prop('disabled', true);
        $('#badge_calculated_km').removeClass('badge-success').addClass('badge-danger').text('Error: KM Akhir < Awal');
    } else {
        $('#finish_km_error').hide();
        $('#btnSubmitFinishTrip').prop('disabled', false);
        var total = (endKM - FINISH_START_KM).toFixed(1);
        $('#badge_calculated_km').removeClass('badge-danger').addClass('badge-success').text(Number(total).toLocaleString('id-ID') + ' KM');
    }
}

function submitFinishTrip(e) {
    e.preventDefault();
    var form = document.getElementById('formFinishTrip');
    var formData = new FormData(form);

    $('#btnSubmitFinishTrip').prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...');

    $.ajax({
        url: BASE_OPS_URL + '-logbook-finish',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
            $('#modalFinishTrip').modal('hide');
            alert(res.message);
            refreshLogbookGrid();
            loadKPIData();
        },
        error: function(xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
            alert('Error: ' + msg);
        },
        complete: function() {
            $('#btnSubmitFinishTrip').prop('disabled', false).html('<i class="feather icon-check mr-1"></i> Simpan & Selesaikan');
        }
    });
}

function deleteLogbook(id, logNo) {
    if (!confirm('Yakin ingin menghapus data logbook ' + logNo + '?')) return;
    $.post(BASE_OPS_URL + '-logbook-delete', { id: id, _token: '{{ csrf_token() }}' }, function(res) {
        alert(res.message);
        refreshLogbookGrid();
        loadKPIData();
    }).fail(function(xhr) {
        alert('Gagal menghapus data.');
    });
}

/* ================= 7. MODAL FUEL TRACKING ================= */
function openNewFuelModal() {
    $('#formNewFuel')[0].reset();
    $('#preview_receipt').hide().attr('src', '');
    $('#preview_fuel_odo').hide().attr('src', '');
    $('#fuel_last_km_info').text('');
    var selected = $('#filter_asset_no').val();
    if (selected && selected !== 'ALL') {
        $('#fuel_asset_no').val(selected);
        onFuelVehicleChange();
    }
    calculateFuelMetrics();
    $('#modalNewFuel').modal('show');
}

var LAST_FUEL_KM = 0;
function onFuelVehicleChange() {
    var assetNo = $('#fuel_asset_no').val();
    if (!assetNo) {
        $('#fuel_last_km_info').text('');
        return;
    }

    $.getJSON(BASE_OPS_URL + '-vehicle-info', { asset_no: assetNo }, function(res) {
        if (res.status === 'success' && res.data) {
            LAST_FUEL_KM = parseFloat(res.data.Last_Fuel_KM) || 0;
            $('#fuel_current_km').val(res.data.Current_KM);
            if (res.data.User_Responsible) {
                $('#fuel_driver_name').val(res.data.User_Responsible);
            }
            if (res.data.Last_Fuel_Type) {
                $('#fuel_type_select').val(res.data.Last_Fuel_Type);
            }
            $('#fuel_last_km_info').html('KM Pengisian Sebelumnya: <strong>' + LAST_FUEL_KM + ' KM</strong>');
            calculateFuelMetrics();
        }
    });
}

function calculateFuelMetrics() {
    var currentKM = parseFloat($('#fuel_current_km').val()) || 0;
    var liters = parseFloat($('#fuel_liters').val()) || 0;
    var totalCost = parseFloat($('#fuel_total_cost').val()) || 0;

    var distance = (currentKM > LAST_FUEL_KM && LAST_FUEL_KM > 0) ? (currentKM - LAST_FUEL_KM) : 0;
    $('#fuel_distance_calc').text('Jarak dari isi sebelumnya: ' + distance.toFixed(1) + ' KM');

    // Price per Liter
    if (liters > 0 && totalCost > 0) {
        var p = (totalCost / liters).toFixed(0);
        $('#calc_price_per_liter').text('Rp ' + Number(p).toLocaleString('id-ID'));
    } else {
        $('#calc_price_per_liter').text('-');
    }

    // KM per Liter
    if (distance > 0 && liters > 0) {
        var eff = (distance / liters).toFixed(2);
        $('#calc_km_per_liter').text(eff + ' KM/L');
    } else {
        $('#calc_km_per_liter').text('-');
    }

    // Cost per KM
    if (distance > 0 && totalCost > 0) {
        var c = (totalCost / distance).toFixed(0);
        $('#calc_cost_per_km').text('Rp ' + Number(c).toLocaleString('id-ID'));
    } else {
        $('#calc_cost_per_km').text('-');
    }
}

function submitNewFuel(e) {
    e.preventDefault();
    var form = document.getElementById('formNewFuel');
    var formData = new FormData(form);

    $('#btnSubmitNewFuel').prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span>Menyimpan...');

    $.ajax({
        url: BASE_OPS_URL + '-fuel-submit',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
            $('#modalNewFuel').modal('hide');
            alert(res.message);
            refreshFuelGrid();
            loadKPIData();
        },
        error: function(xhr) {
            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
            alert('Error: ' + msg);
        },
        complete: function() {
            $('#btnSubmitNewFuel').prop('disabled', false).html('<i class="feather icon-check mr-1"></i> Simpan Catatan BBM');
        }
    });
}

function deleteFuel(id, fuelNo) {
    if (!confirm('Yakin ingin menghapus data transaksi BBM ' + fuelNo + '?')) return;
    $.post(BASE_OPS_URL + '-fuel-delete', { id: id, _token: '{{ csrf_token() }}' }, function(res) {
        alert(res.message);
        refreshFuelGrid();
        loadKPIData();
    }).fail(function(xhr) {
        alert('Gagal menghapus data.');
    });
}

/* ================= 8. EXPORTS ================= */
function exportLogbook() {
    var assetNo = $('#filter_asset_no').val() || 'ALL';
    var month = $('#filter_month').val() || '';
    window.location.href = BASE_OPS_URL + '-export-logbook?asset_no=' + encodeURIComponent(assetNo) + '&month=' + encodeURIComponent(month);
}

function exportFuel() {
    var assetNo = $('#filter_asset_no').val() || 'ALL';
    var month = $('#filter_month').val() || '';
    window.location.href = BASE_OPS_URL + '-export-fuel?asset_no=' + encodeURIComponent(assetNo) + '&month=' + encodeURIComponent(month);
}

/* Preview Image Helper */
function previewImage(input, targetImgId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#' + targetImgId).attr('src', e.target.result).show();
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
