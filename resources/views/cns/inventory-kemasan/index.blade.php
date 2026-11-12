@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
@php
    $activeTab = session('active_tab', 'monitoring');
    $formatNum = function ($val) {
        if ($val === null || (float) $val == 0.0) {
            return '-';
        }
        return number_format((float) $val, 2, '.', ',');
    };
@endphp
<div class="main-content-header">
    <h1>Inventory</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="#">Inventory Non UCO</a></li>
        <li class="breadcrumb-item active"><span class="active">Inventory Kemasan</span></li>
    </ol>
</div>

<style>
    #KemasanGrid .xhdr {
        height: auto !important;
        min-height: 0 !important;
    }
    #KemasanGrid table.hdr td,
    #KemasanGrid .hdr tr:first-child td {
        background-color: #174375 !important;
        color: #000000   !important;
        font-weight: bold !important;
        text-align: center !important;
        vertical-align: middle !important;
        border-right: 1px solid #ffffff !important;
        font-size: 12px !important;
        padding: 4px 6px !important;
        line-height: 1.2 !important;
        height: auto !important;
    }
    #KemasanGrid table.hdr td div.hdrcell {
        padding: 0 4px !important;
        line-height: 1.2 !important;
        white-space: nowrap !important;
    }
    #KemasanGrid td {
        font-size: 12px !important;
        vertical-align: middle !important;
    }
    .badge-balance-card {
        background-color: #ffffff;
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 10px 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .badge-balance-title {
        font-size: 11px;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: bold;
    }
    .badge-balance-val {
        font-size: 20px;
        font-weight: bold;
        color: #174375;
    }
    .report-kemasan-table th,
    .report-kemasan-table td {
        border: 1px solid #495057 !important;
        text-align: center;
        vertical-align: middle !important;
        padding: 6px 10px;
        font-size: 12px;
        white-space: nowrap;
    }
    .report-kemasan-table thead tr.hdr-top th {
        background-color: #0089c7;
        color: #000000;
        font-weight: bold;
    }
    .report-kemasan-table thead tr.hdr-sub th {
        background-color: #d9e6f2;
        color: #000000;
        font-weight: bold;
    }
    .report-kemasan-table td.col-site {
        background-color: #ffff00;
        font-weight: bold;
        color: #000000;
    }
    .report-kemasan-table td.col-excess {
        background-color: #f4b400;
        font-weight: bold;
        color: #000000;
    }
    .report-kemasan-table td.col-max {
        background-color: #e9ecef;
    }
    .report-kemasan-table tfoot tr.row-total td {
        background-color: #d9e6f2;
        font-weight: bold;
        color: #000000;
    }
    .report-kemasan-table tfoot tr.row-total td.col-excess {
        background-color: #f4b400;
    }
    .card-header-tabs .nav-link {
        border: none !important;
        border-bottom: 3px solid transparent !important;
        color: #6c757d;
        padding: 12px 24px;
        font-size: 13px;
        border-radius: 0;
        background: transparent;
        transition: all 0.2s ease;
    }
    .card-header-tabs .nav-link:hover {
        color: #174375;
        border-bottom: 3px solid #ced4da !important;
    }
    .card-header-tabs .nav-link.active {
        color: #174375 !important;
        background: #ffffff !important;
        border-bottom: 3px solid #174375 !important;
        font-weight: bold;
    }
</style>

<div class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">INVENTORY KEMASAN (MUTASI STOK KEMASAN)</div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show w-100" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show w-100" role="alert">
            {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Main Card Container with Header Tabs (matching Inbound Movement) -->
    <div class="card w-100 shadow-sm mt-2" style="border: 1px solid #ced4da; border-radius: 4px;">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs card-header-tabs m-0 border-bottom-0" id="kemasanTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $activeTab === 'monitoring' ? 'active' : '' }}" id="tab-monitoring-link" data-bs-toggle="tab" data-bs-target="#tab-monitoring" data-toggle="tab" href="#tab-monitoring" role="tab" aria-controls="tab-monitoring" aria-selected="{{ $activeTab === 'monitoring' ? 'true' : 'false' }}" onclick="switchKemasanTab('monitoring'); return false;">
                        <i class="fa fa-line-chart text-primary mr-2"></i> Monitoring Mutasi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link font-weight-bold {{ $activeTab === 'report' ? 'active' : '' }}" id="tab-report-link" data-bs-toggle="tab" data-bs-target="#tab-report" data-toggle="tab" href="#tab-report" role="tab" aria-controls="tab-report" aria-selected="{{ $activeTab === 'report' ? 'true' : 'false' }}" onclick="switchKemasanTab('report'); return false;">
                        <i class="fa fa-table text-success mr-2"></i> Report Inventory Kemasan
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body p-3">
            <div class="tab-content" id="kemasanTabContent">
                <!-- Tab 1: Monitoring Mutasi -->
                <div class="tab-pane fade {{ $activeTab === 'monitoring' ? 'show active' : '' }}" id="tab-monitoring" role="tabpanel" aria-labelledby="tab-monitoring-link" style="display:{{ $activeTab === 'monitoring' ? 'block' : 'none' }};">
            <!-- @if(count($summaries) > 0)
            <div class="d-flex flex-wrap gap-2 w-100 mb-3 mt-3">
                @foreach($summaries as $sum)
                    <div class="badge-balance-card mr-2 mb-2" style="min-width: 220px;">
                        <div class="badge-balance-title mb-1">
                            @if(!empty($sum['site_name']))
                                <span class="badge badge-dark">{{ $sum['site_name'] }}</span>
                            @endif
                            @if(!empty($sum['is_ampas']))
                                <span class="badge badge-warning border">Ampas</span>
                            @else
                                <span class="badge badge-primary border">Kemasan</span>
                            @endif
                            {{ $sum['label'] ?? $sum['item_name'] }}
                        </div>
                        @if(empty($sum['is_ampas']) && isset($sum['max_stock']) && $sum['max_stock'] !== null)
                            <div class="d-flex justify-content-between text-center mt-1" style="gap: 10px;">
                                <div>
                                    <small class="text-muted d-block">Max Stock</small>
                                    <span class="font-weight-bold text-secondary">{{ number_format((float) $sum['max_stock'], 2, '.', ',') }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Qty</small>
                                    <span class="font-weight-bold text-primary">{{ number_format((float) ($sum['qty_balance'] ?? $sum['current_balance']), 2, '.', ',') }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Excess</small>
                                    <span class="font-weight-bold text-danger">{{ number_format((float) ($sum['excess_balance'] ?? 0), 2, '.', ',') }}</span>
                                </div>
                            </div>
                        @else
                            <div class="badge-balance-val">
                                {{ number_format((float) ($sum['qty_balance'] ?? $sum['current_balance']), 2, '.', ',') }} <small class="text-muted" style="font-size: 13px;">{{ $sum['unit'] }}</small>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @endif -->

            <div class="d-flex align-items-end flex-wrap gap-2 mb-3">
                <div class="m-1">
                    <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                    <input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:140px;" />
                </div>
                <div class="m-1">
                    <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                    <input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:140px;" />
                </div>
                <div class="m-1">
                    <label class="d-block mb-1"><small>Site</small></label>
                    <select id="filterSite" class="form-control form-control-sm" style="width:150px;" onchange="applyFilters()">
                        <option value="">Semua Site</option>
                        @foreach($sites as $siteId => $siteName)
                            <option value="{{ $siteId }}">{{ $siteName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="m-1">
                    <label class="d-block mb-1"><small>Nama Kemasan</small></label>
                    <select id="filterItemName" class="form-control form-control-sm" style="width:220px;" onchange="applyFilters()">
                        <option value="">Semua Kemasan</option>
                        @foreach($masterPackagings as $mp)
                            @php
                                $volText = \App\Services\CNS\KemasanInventoryService::formatVolume($mp->Volume);
                            @endphp
                            <option value="pkg:{{ $mp->Kemasan_ID }}">{{ $mp->Name }} ({{ $volText }} L)</option>
                        @endforeach
                        @foreach($existingItems as $ei)
                            <option value="manual:{{ $ei }}">{{ $ei }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="m-1">
                    <button class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                    <button class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
                </div>
            </div>

            <div id="KemasanGrid" style="width:100%;height:450px; background-color:white;overflow:hidden" class="mt-2 border"></div>
            <div class="mt-3 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-md btn-primary mr-2" data-bs-toggle="modal" data-bs-target="#modalManualMutation" data-toggle="modal" data-target="#modalManualMutation" onclick="$('#modalManualMutation').modal('show');">
                    Catat Mutasi Stok Kemasan
                </button>
            </div>
        </div>

        <!-- Tab 2: Report Inventory Kemasan -->
        <div class="tab-pane fade {{ $activeTab === 'report' ? 'show active' : '' }}" id="tab-report" role="tabpanel" aria-labelledby="tab-report-link" style="display:{{ $activeTab === 'report' ? 'block' : 'none' }};">
            <div class="table-responsive">
                <table class="table table-bordered table-sm report-kemasan-table mb-0">
                    <thead>
                        <tr class="hdr-top">
                            <th rowspan="2" style="width:45px;">No</th>
                            <th rowspan="2" style="min-width:130px;">Site</th>
                            @foreach($reportMatrix['kemasans'] as $mk)
                                @if($mk->isAmpas())
                                    <th rowspan="2">{{ $mk->Name }}</th>
                                @else
                                    <th colspan="3">{{ $mk->Name }}</th>
                                @endif
                            @endforeach
                            <th colspan="3">TOTAL</th>
                        </tr>
                        <tr class="hdr-sub">
                            @foreach($reportMatrix['kemasans'] as $mk)
                                @if(!$mk->isAmpas())
                                    <th>Max Stok</th>
                                    <th>{{ \App\Services\CNS\KemasanInventoryService::formatVolume($mk->Volume) }} Liter</th>
                                    <th>Excess</th>
                                @endif
                            @endforeach
                            <th>Max Stok</th>
                            <th>Qty</th>
                            <th>Excess</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportMatrix['rows'] as $row)
                            @php
                                $siteTotal = $reportMatrix['row_totals'][$row['site_id']] ?? ['max_stock' => 0, 'qty' => 0, 'excess' => 0];
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="col-site">{{ $row['site_name'] }}</td>
                                @foreach($reportMatrix['kemasans'] as $mk)
                                    @php
                                        $cell = $row['cells'][$mk->Kemasan_ID] ?? ['max_stock' => null, 'qty' => 0, 'excess' => 0];
                                    @endphp
                                    @if($mk->isAmpas())
                                        <td>{{ $formatNum($cell['qty']) }}</td>
                                    @else
                                        <td class="col-max">{{ $cell['max_stock'] !== null ? number_format((float) $cell['max_stock'], 2, '.', ',') : '-' }}</td>
                                        <td>{{ $formatNum($cell['qty']) }}</td>
                                        <td class="col-excess">{{ $formatNum($cell['excess']) }}</td>
                                    @endif
                                @endforeach
                                <td class="col-max font-weight-bold">{{ $formatNum($siteTotal['max_stock']) }}</td>
                                <td class="font-weight-bold">{{ $formatNum($siteTotal['qty']) }}</td>
                                <td class="col-excess font-weight-bold">{{ $formatNum($siteTotal['excess']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted">Belum ada data site.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($reportMatrix['rows']) > 0)
                    <tfoot>
                        <tr class="row-total">
                            <td colspan="2" class="text-center">TOTAL</td>
                            @foreach($reportMatrix['kemasans'] as $mk)
                                @php
                                    $colTotal = $reportMatrix['column_totals'][$mk->Kemasan_ID] ?? ['max_stock' => 0, 'qty' => 0, 'excess' => 0];
                                @endphp
                                @if($mk->isAmpas())
                                    <td>{{ $formatNum($colTotal['qty']) }}</td>
                                @else
                                    <td>{{ $formatNum($colTotal['max_stock']) }}</td>
                                    <td>{{ $formatNum($colTotal['qty']) }}</td>
                                    <td class="col-excess">{{ $formatNum($colTotal['excess']) }}</td>
                                @endif
                            @endforeach
                            @php
                                $grand = $reportMatrix['grand_totals'] ?? ['max_stock' => 0, 'qty' => 0, 'excess' => 0];
                            @endphp
                            <td>{{ $formatNum($grand['max_stock']) }}</td>
                            <td>{{ $formatNum($grand['qty']) }}</td>
                            <td class="col-excess">{{ $formatNum($grand['excess']) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            <div class="mt-3 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-md btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalSiteLimits" data-toggle="modal" data-target="#modalSiteLimits" onclick="$('#modalSiteLimits').modal('show');">
                    Atur Max Stok Site
                </button>
            </div>
        </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Catat Mutasi Manual Kemasan -->
<div class="modal fade" id="modalManualMutation" tabindex="-1" role="dialog" aria-labelledby="modalManualMutationLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('cns-inventory-kemasan.manual-mutation') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalManualMutationLabel"><i class="fa fa-pencil-square-o mr-1"></i> Catat Mutasi Stok Kemasan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="$('#modalManualMutation').modal('hide');">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required />
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold">Site <span class="text-danger">*</span></label>
                            <select name="site_id" class="form-control form-control-sm" required>
                                <option value="">-- Pilih Site --</option>
                                @foreach($sites as $siteId => $siteName)
                                    <option value="{{ $siteId }}">{{ $siteName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Dari Master Kemasan</label>
                        <select id="selectMasterPackaging" class="form-control form-control-sm" onchange="onSelectMasterPackaging(this)">
                            <option value="">-- Pilih dari Master Kemasan (Opsional) --</option>
                            @foreach($masterPackagings as $pkg)
                                @php
                                    $volText = \App\Services\CNS\KemasanInventoryService::formatVolume($pkg->Volume);
                                @endphp
                                <option value="{{ $pkg->Kemasan_ID }}" data-name="{{ $pkg->Name }}" data-unit="PCS">
                                    {{ $pkg->Name }} ({{ $volText }} L)
                                </option>
                            @endforeach
                            <option value="custom">-- Ketik Manual / Lainnya --</option>
                        </select>
                        <input type="hidden" name="packaging_id" id="inputPackagingId" value="" />
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold">Nama Kemasan <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" id="inputItemName" class="form-control form-control-sm" placeholder="Contoh: Flexibag 24KL / Jerigen 18L" required />
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Satuan <span class="text-danger">*</span></label>
                            <input type="text" name="unit" id="inputUnit" class="form-control form-control-sm text-center" value="PCS" required />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Tipe Mutasi <span class="text-danger">*</span></label>
                            <select name="type" id="selectMutationType" class="form-control form-control-sm" onchange="toggleOutTypeField()" required>
                                <option value="IN">IN (Masuk / Penerimaan)</option>
                                <option value="OUT">OUT (Keluar / Pemakaian)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4" id="groupOutType" style="display:none;">
                            <label class="font-weight-bold">Tipe OUT <span class="text-danger">*</span></label>
                            <select name="out_type" id="selectOutType" class="form-control form-control-sm" disabled>
                                <option value="DROP">Drop (Kurangi Qty)</option>
                                <option value="PENJUALAN">Penjualan (Kurangi Excess)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">Kuantiti <span class="text-danger">*</span></label>
                            <input type="text" inputmode="decimal" name="qty" class="form-control form-control-sm font-weight-bold text-right cns-decimal" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">No. Referensi / No. Surat Jalan / No. Dokumen <span class="text-danger">*</span></label>
                        <input type="text" name="reference_no" class="form-control form-control-sm" placeholder="Contoh: SJ-KMS-2026-001" required />
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Keterangan / Catatan</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Catatan penerimaan, pemakaian, atau penyesuaian..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#modalManualMutation').modal('hide');">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan Mutasi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Atur Max Stok Per Site -->
<div class="modal fade" id="modalSiteLimits" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 92vw;" role="document">
        <form action="{{ route('cns-inventory-kemasan.site-limits') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title"><i class="fa fa-sliders mr-1"></i> Atur Max Stok per Site</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="$('#modalSiteLimits').modal('hide');">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Kosongkan kolom jika ingin mengikuti nilai default Max Stock di Master Kemasan.</p>
                    <div class="table-responsive" style="max-height: 65vh;">
                        @php
                            $nonAmpasKemasans = $reportMatrix['kemasans']->reject(function ($mk) {
                                return $mk->isAmpas();
                            });
                        @endphp
                        <table class="table table-bordered table-sm table-striped align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="align-middle" style="min-width: 130px; white-space: nowrap;">Site</th>
                                    @foreach($nonAmpasKemasans as $mk)
                                        <th class="text-center align-middle" style="min-width: 140px; white-space: nowrap;">
                                            {{ $mk->Name }} ({{ \App\Services\CNS\KemasanInventoryService::formatVolume($mk->Volume) }} L)
                                            <small class="d-block text-muted">Default: {{ $mk->Max_Stock !== null ? \App\Services\CNS\KemasanInventoryService::formatVolume($mk->Max_Stock) : '-' }}</small>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportMatrix['rows'] as $row)
                                    @php
                                        $siteId = $row['site_id'];
                                        $siteName = $row['site_name'];
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold align-middle" style="white-space: nowrap;">{{ $siteName }}</td>
                                        @foreach($nonAmpasKemasans as $mk)
                                            @php
                                                $overrideVal = $reportMatrix['limits'][$mk->Kemasan_ID][$siteId] ?? '';
                                                $defaultPlaceholder = $mk->Max_Stock !== null ? \App\Services\CNS\KemasanInventoryService::formatVolume($mk->Max_Stock) : '';
                                            @endphp
                                            <td style="min-width: 140px;">
                                                <input type="text" inputmode="decimal"
                                                    name="limits[{{ $mk->Kemasan_ID }}][{{ $siteId }}]"
                                                    class="form-control form-control-sm text-end text-right cns-decimal"
                                                    style="min-width: 115px;"
                                                    value="{{ $overrideVal !== '' ? number_format((float) $overrideVal, 2, '.', ',') : '' }}"
                                                    placeholder="{{ $defaultPlaceholder }}" />
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal" onclick="$('#modalSiteLimits').modal('hide');">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan Pengaturan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    var KemasanGrid;
    var kemasanHeadersFitted = false;
    $(document).ready(function() {
        initKemasanGrid();
        loadGridData();
        $(window).on('resize', function() {
            if (KemasanGrid) {
                KemasanGrid.setSizes();
            }
        });
    });

    function switchKemasanTab(tab) {
        if (tab === 'report') {
            $('#tab-monitoring-link').removeClass('active').attr('aria-selected', 'false');
            $('#tab-report-link').addClass('active').attr('aria-selected', 'true');
            $('#tab-monitoring').removeClass('show active').css('display', 'none');
            $('#tab-report').addClass('show active').css('display', 'block');
        } else {
            $('#tab-report-link').removeClass('active').attr('aria-selected', 'false');
            $('#tab-monitoring-link').addClass('active').attr('aria-selected', 'true');
            $('#tab-report').removeClass('show active').css('display', 'none');
            $('#tab-monitoring').addClass('show active').css('display', 'block');

            setTimeout(function() {
                if (KemasanGrid) {
                    if (!kemasanHeadersFitted) {
                        fitKemasanColumnsToHeader();
                    } else {
                        KemasanGrid.setSizes();
                    }
                }
            }, 60);
        }
    }

    function onSelectMasterPackaging(select) {
        var opt = select.options[select.selectedIndex];
        var val = select.value;
        if (val && val !== 'custom') {
            $('#inputPackagingId').val(val);
            $('#inputItemName').val(opt.getAttribute('data-name'));
            $('#inputUnit').val(opt.getAttribute('data-unit') || 'PCS');
        } else {
            $('#inputPackagingId').val('');
            if (val === 'custom') {
                $('#inputItemName').val('').focus();
            }
        }
    }

    function toggleOutTypeField() {
        var isOut = $('#selectMutationType').val() === 'OUT';
        $('#groupOutType').toggle(isOut);
        $('#selectOutType').prop('disabled', !isOut).prop('required', isOut);
    }

    function fitKemasanColumnsToHeader() {
        if (!KemasanGrid || !$('#KemasanGrid').is(':visible')) return;
        var colCount = KemasanGrid.getColumnsNum();
        for (var i = 0; i < colCount; i++) {
            KemasanGrid.adjustColumnSize(i);
        }
        KemasanGrid.setSizes();
        kemasanHeadersFitted = true;
    }

    function initKemasanGrid() {
        KemasanGrid = new dhtmlXGridObject('KemasanGrid');
        KemasanGrid.setImagePath("./dhtmlx/imgs/");
        KemasanGrid.setSkin("dhx_skyblue");
        KemasanGrid.setHeader("No,Tanggal,Site,No. Referensi,Nama Kemasan,Satuan,Tipe Mutasi,Tipe OUT,Qty Masuk,Qty Keluar,Saldo Qty,Saldo Excess,Total Saldo,Keterangan,Dicatat Oleh");
        KemasanGrid.setInitWidths("45,85,75,115,130,70,95,85,90,90,85,105,95,*,105");
        KemasanGrid.setColAlign("center,center,center,left,left,center,center,center,right,right,right,right,right,left,left");
        KemasanGrid.setColSorting("int,date,str,str,str,str,str,str,int,int,int,int,int,str,str");
        KemasanGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        KemasanGrid.enableSmartRendering(true);
        KemasanGrid.init();
        fitKemasanColumnsToHeader();
        KemasanGrid.setSizes();
    }

    function loadGridData() {
        var params = [];
        var start     = $('#filterStartDate').val();
        var end       = $('#filterEndDate').val();
        var siteId    = $('#filterSite').val();
        var filterVal = $('#filterItemName').val();

        if (start)  params.push('start_date=' + encodeURIComponent(start));
        if (end)    params.push('end_date=' + encodeURIComponent(end));
        if (siteId) params.push('site_id=' + encodeURIComponent(siteId));
        if (filterVal) {
            if (filterVal.indexOf('pkg:') === 0) {
                params.push('packaging_id=' + encodeURIComponent(filterVal.substring(4)));
            } else if (filterVal.indexOf('manual:') === 0) {
                params.push('item_name=' + encodeURIComponent(filterVal.substring(7)));
                params.push('manual=1');
            } else {
                params.push('item_name=' + encodeURIComponent(filterVal));
            }
        }

        var url = "{{ route('cns-inventory-kemasan.xml') }}" + (params.length ? '?' + params.join('&') : '');
        KemasanGrid.clearAll();
        KemasanGrid.load(url);
    }

    function applyFilters() {
        loadGridData();
    }

    function resetFilters() {
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        $('#filterSite').val('');
        $('#filterItemName').val('');
        loadGridData();
    }
</script>
@endsection
