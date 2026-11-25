@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-3 pt-1 pb-2 flex-wrap gap-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Cetak QR Asset &amp; Lokasi (HCGA)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('hrd-asset-lists') }}" style="color: rgba(255, 255, 255, 0.8) !important;">Asset Lists</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Cetak QR</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ url('hrd-asset-lists-qr-print?mode=location') }}"
               class="btn btn-sm fw-semibold {{ $mode === 'location' ? 'btn-light text-primary' : 'btn-outline-light text-white' }}">
                <i class="fa fa-map-marker-alt me-1"></i> QR Lokasi / Site
            </a>
            <a href="{{ url('hrd-asset-lists-qr-print?mode=asset') }}"
               class="btn btn-sm fw-semibold {{ $mode === 'asset' ? 'btn-light text-primary' : 'btn-outline-light text-white' }}">
                <i class="fa fa-box me-1"></i> QR Aset Individual
            </a>
            <a href="{{ url('hrd-asset-lists') }}" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @php
        $checkedCodes = $hasFilter
            ? $selectedCodes
            : $items->map(function ($item) use ($mode) {
                return $mode === 'location' ? (string) $item->code : (string) $item->Asset_No;
            })->all();
    @endphp

    <style>
        .qr-print-page {
            width: 190mm;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 4mm;
            border-radius: 6px;
        }
        .qr-print-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 4mm;
            align-content: flex-start;
        }
        .qr-print-card {
            width: 60mm;
            height: 82mm;
            box-sizing: border-box;
            border: 1px solid #0d3b66;
            padding: 3mm;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            background: #fff;
        }
        .qr-print-card img {
            width: 44mm;
            height: 44mm;
            display: block;
            margin: 0 auto;
        }
        .qr-print-card .qr-name {
            font-size: 9pt;
            font-weight: 700;
            line-height: 1.15;
            margin-top: 2mm;
            max-height: 10.5mm;
            overflow: hidden;
            color: #0d3b66;
        }
        .qr-print-card .qr-code {
            font-family: Consolas, monospace;
            font-size: 7.5pt;
            font-weight: 700;
            line-height: 1.2;
            margin-top: 1mm;
        }
        .qr-print-card .qr-meta {
            font-size: 6.5pt;
            color: #444;
            line-height: 1.2;
            margin-top: 1mm;
            max-height: 11mm;
            overflow: hidden;
        }

        .qr-print-grid.compact {
            gap: 3.5mm;
        }
        .qr-print-card.compact {
            width: 88mm;
            height: 48mm;
            padding: 2.5mm 3mm;
            border: 1.5px solid #007bff;
            border-radius: 4px;
            display: flex;
            flex-direction: row;
            align-items: center;
            text-align: left;
            background: #fff;
        }
        .qr-print-card.compact img {
            width: 32mm;
            height: 32mm;
            margin: 0 3mm 0 0;
            flex-shrink: 0;
        }
        .qr-print-card.compact .info-box {
            flex-grow: 1;
            font-size: 7.5pt;
            line-height: 1.35;
            overflow: hidden;
        }
        .qr-print-card.compact .qr-name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #007bff;
            margin: 0 0 1.5mm 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 1mm;
            max-height: 11mm;
        }
        .qr-print-card.compact .qr-code {
            display: inline-block;
            background: #e3f2fd;
            color: #0d47a1;
            padding: 1mm 2mm;
            border-radius: 3px;
            font-family: Consolas, monospace;
            font-size: 7pt;
            margin-top: 1.5mm;
            font-weight: bold;
        }
        .qr-print-card.compact .qr-meta {
            font-size: 6.5pt;
            color: #444;
            margin-top: 1mm;
            max-height: none;
        }
        .search-box {
            max-width: 280px;
        }
    </style>

    @if (!empty($warning))
        <div class="alert alert-warning mt-2">{{ $warning }}</div>
    @endif

    @if ($mode === 'asset')
        <div class="card mb-3 shadow-sm">
            <div class="card-body py-2">
                <form method="get" action="{{ url('hrd-asset-lists-qr-print') }}" class="d-flex flex-wrap align-items-center gap-2">
                    <input type="hidden" name="mode" value="asset" />
                    <input type="hidden" name="layout" value="{{ $layout }}" />
                    <span class="small fw-bold text-secondary me-1">Filter Daftar Aset:</span>
                    <select name="category" class="form-select form-select-sm" style="width:auto">
                        <option value="">Semua Category</option>
                        @foreach(($filterOptions['categories'] ?? []) as $cat)
                            <option value="{{ $cat }}" {{ ($activeFilters['category'] ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    <select name="sub_category" class="form-select form-select-sm" style="width:auto">
                        <option value="">Semua Sub Category</option>
                        @foreach(($filterOptions['subCategories'] ?? []) as $subCat)
                            <option value="{{ $subCat }}" {{ ($activeFilters['sub_category'] ?? '') === $subCat ? 'selected' : '' }}>{{ $subCat }}</option>
                        @endforeach
                    </select>
                    <select name="location" class="form-select form-select-sm" style="width:auto; max-width:220px;">
                        <option value="">Semua Asset Location</option>
                        @foreach(($filterOptions['locations'] ?? []) as $loc)
                            <option value="{{ $loc['code'] }}" {{ ($activeFilters['location'] ?? '') === $loc['code'] ? 'selected' : '' }}>{{ $loc['label'] }}</option>
                        @endforeach
                        <option value="__UNASSIGNED__" {{ ($activeFilters['location'] ?? '') === '__UNASSIGNED__' ? 'selected' : '' }}>Belum Ada Lokasi</option>
                    </select>
                    <select name="status" class="form-select form-select-sm" style="width:auto">
                        <option value="">Semua Status</option>
                        @foreach(['ACTIVE', 'NEW', 'MAINTENANCE', 'DISPOSED', 'INACTIVE'] as $st)
                            <option value="{{ $st }}" {{ ($activeFilters['status'] ?? '') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                    <select name="kondisi" class="form-select form-select-sm" style="width:auto">
                        <option value="">Semua Kondisi</option>
                        @foreach(['BAIK', 'RUSAK'] as $knd)
                            <option value="{{ $knd }}" {{ ($activeFilters['kondisi'] ?? '') === $knd ? 'selected' : '' }}>{{ $knd }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter me-1"></i> Terapkan</button>
                    <a href="{{ url('hrd-asset-lists-qr-print?mode=asset&layout=' . urlencode($layout)) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </form>
            </div>
        </div>
    @endif

    <form id="AssetQrPrintForm" method="post" action="{{ url('hrd-asset-lists-qr-print') }}" class="no-print mb-3" target="_blank">
        @csrf
        <input type="hidden" name="mode" value="{{ $mode }}" />
        <input type="hidden" name="filter" value="1" />
        @if ($mode === 'asset')
            @foreach ($activeFilters as $fk => $fv)
                @if ($fv !== '')
                    <input type="hidden" name="{{ $fk }}" value="{{ $fv }}" />
                @endif
            @endforeach
        @endif

        <div class="card shadow-sm">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <strong>
                        {{ $mode === 'location' ? 'Pilih Asset Location / Site yang Dicetak' : 'Pilih Aset Individual yang Dicetak' }}
                    </strong>
                    <span class="badge badge-primary bg-primary text-white" id="selectedBadge">Terpilih: 0 / {{ $items->count() }}</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" id="filterSearch" class="form-control form-control-sm search-box" placeholder="Cari kode / nama / deskripsi...">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="AssetQrSelectAll(true)">Pilih Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="AssetQrSelectAll(false)">Hilangkan Pilihan</button>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group row border-bottom pb-3 mb-3">
                    <label class="col-sm-2 col-form-label fw-bold">Format Cetak</label>
                    <div class="col-sm-10 d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input type="radio" id="layoutCard" name="layout" value="card" class="form-check-input layout-radio" {{ ($layout ?? 'card') === 'card' ? 'checked' : '' }}>
                            <label class="form-check-label" for="layoutCard">
                                <strong>Format Kartu (Lengkap)</strong>
                                <small class="text-muted d-block">Cocok untuk label lokasi, rak, ruangan, atau dokumen aset (Header HCGA &amp; detail ringkas)</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="radio" id="layoutCompact" name="layout" value="compact" class="form-check-input layout-radio" {{ ($layout ?? 'card') === 'compact' ? 'checked' : '' }}>
                            <label class="form-check-label" for="layoutCompact">
                                <strong>Format Stiker Horizontal</strong>
                                <small class="text-muted d-block">Stiker horizontal 88x48mm 2 kolom (QR Code + kode &amp; informasi ringkas)</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row" id="checkboxContainer" style="max-height: 320px; overflow-y: auto;">
                    @forelse ($items as $idx => $item)
                        @php
                            $itemCode = $mode === 'location' ? (string) $item->code : (string) $item->Asset_No;
                            $itemTitle = $mode === 'location'
                                ? (string) $item->name
                                : ((string) ($item->Product_Description ?: $item->Asset_No));
                            $itemMeta = $mode === 'location'
                                ? ($itemCode . ' • ' . (int) $item->asset_count . ' aset')
                                : ($itemCode . ($item->Asset_Location ? ' • ' . $item->Asset_Location : ''));
                            $searchStr = strtolower($itemCode . ' ' . $itemTitle . ' ' . ($mode === 'location' ? ($item->subtitle ?? '') : (($item->Product_Brand ?? '') . ' ' . ($item->Registration_No ?? '') . ' ' . ($item->Asset_Location ?? ''))));
                            $cbId = 'AssetQrCb_' . $idx;
                        @endphp
                        <div class="col-md-4 mb-2 checkbox-item" data-search="{{ $searchStr }}">
                            <div class="form-check">
                                <input class="form-check-input qr-checkbox" type="checkbox" name="codes[]" value="{{ $itemCode }}" id="{{ $cbId }}" data-idx="{{ $idx }}" {{ in_array($itemCode, $checkedCodes, true) ? 'checked' : '' }} onchange="onQrCheckChanged()" />
                                <label class="form-check-label" for="{{ $cbId }}">
                                    <strong>{{ $itemTitle }}</strong>
                                    <small class="text-muted d-block">{{ $itemMeta }}</small>
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="col-md-12 text-muted">Tidak ada data yang tersedia untuk dicetak.</div>
                    @endforelse
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="submit" name="action" value="print" class="btn btn-success btn-sm" onclick="return AssetQrRequireSelection();">
                        <i class="fa fa-print me-1"></i> Cetak PDF (mPDF)
                    </button>
                    <button type="submit" name="action" value="download" class="btn btn-outline-success btn-sm" onclick="return AssetQrRequireSelection();">
                        <i class="fa fa-download me-1"></i> Unduh PDF
                    </button>
                    <a class="btn btn-secondary btn-sm" href="{{ url('hrd-asset-lists') }}">Kembali</a>
                </div>
                <span class="text-muted small">Scan QR membuka PDF daftar/detail aset + lampiran secara langsung tanpa login.</span>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Live Preview Pilihan (<span id="layoutNameLabel">{{ ($layout ?? 'card') === 'compact' ? 'Format Stiker Horizontal' : 'Format Kartu Lengkap' }}</span>)</h5>
        <span class="text-muted small" id="previewCountLabel">Menampilkan 0 kartu</span>
    </div>

    <div id="noSelectionAlert" class="alert alert-secondary text-center my-3" style="display: none;">
        Tidak ada item yang dipilih. Centang minimal satu item di atas untuk melihat preview kartu.
    </div>

    <div class="qr-print-page shadow-sm">
        <div class="qr-print-grid {{ ($layout ?? 'card') === 'compact' ? 'compact' : '' }}" id="qrPreviewGrid">
            @foreach ($items as $idx => $item)
                @php
                    $itemCode = $mode === 'location' ? (string) $item->code : (string) $item->Asset_No;
                    $isCheckedItem = in_array($itemCode, $checkedCodes, true);
                    $isCompactLayout = ($layout ?? 'card') === 'compact';
                    $imgUrl = url('hrd-asset-lists-qr-image?mode=' . urlencode($mode) . '&code=' . urlencode($itemCode));
                    $displayTitle = $mode === 'location'
                        ? (string) $item->name
                        : (string) ($item->Product_Description ?: $item->Asset_No);
                @endphp

                <div class="qr-print-card compact preview-card-compact" id="preview_compact_{{ $idx }}" style="display: {{ ($isCheckedItem && $isCompactLayout) ? 'flex' : 'none' }};">
                    <img src="{{ $imgUrl }}" alt="{{ $itemCode }}" loading="lazy" />
                    <div class="info-box">
                        <div class="qr-name">{{ $displayTitle }}</div>
                        <div class="qr-meta">
                            @if ($mode === 'location')
                                <strong>Site:</strong> {{ $item->subtitle ?: '-' }}<br>
                                <strong>Total Aset:</strong> {{ (int) $item->asset_count }} unit
                            @else
                                <strong>Lokasi:</strong> {{ $item->Asset_Location ?: '-' }}<br>
                                <strong>Status:</strong> {{ $item->Status ?: '-' }} ({{ $item->Kondisi_Aset ?: 'BAIK' }})
                            @endif
                        </div>
                        <div class="qr-code">{{ $itemCode }}</div>
                    </div>
                </div>

                <div class="qr-print-card preview-card-card" id="preview_card_{{ $idx }}" style="display: {{ ($isCheckedItem && !$isCompactLayout) ? 'flex' : 'none' }};">
                    <img src="{{ $imgUrl }}" alt="{{ $itemCode }}" loading="lazy" />
                    <div class="qr-name">{{ $displayTitle }}</div>
                    <div class="qr-code">{{ $itemCode }}</div>
                    <div class="qr-meta">
                        @if ($mode === 'location')
                            LOCATION QR
                            @if (!empty($item->subtitle)) | {{ $item->subtitle }} @endif
                            | {{ (int) $item->asset_count }} Aset
                        @else
                            {{ $item->Asset_Sub_Category ?: ($item->Asset_Category ?: 'ASSET') }}
                            @if (!empty($item->Asset_Location)) | {{ $item->Asset_Location }} @endif
                            @if (!empty($item->Registration_No)) | {{ $item->Registration_No }} @elseif (!empty($item->SN)) | SN: {{ $item->SN }} @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        var totalItems = {{ $items->count() }};

        function getActiveLayout() {
            var checked = document.querySelector('input[name="layout"]:checked');
            return checked ? checked.value : 'card';
        }

        function updateLivePreview() {
            var layout = getActiveLayout();
            var grid = document.getElementById('qrPreviewGrid');
            var selectedCheckboxes = document.querySelectorAll('.qr-checkbox:checked');
            var selectedCount = selectedCheckboxes.length;

            document.getElementById('selectedBadge').innerText = 'Terpilih: ' + selectedCount + ' / ' + totalItems;
            document.getElementById('previewCountLabel').innerText = 'Menampilkan ' + selectedCount + ' kartu';
            document.getElementById('layoutNameLabel').innerText = (layout === 'compact') ? 'Format Stiker Horizontal' : 'Format Kartu Lengkap';

            if (layout === 'compact') {
                grid.classList.add('compact');
            } else {
                grid.classList.remove('compact');
            }

            document.querySelectorAll('.preview-card-compact, .preview-card-card').forEach(function(el) {
                el.style.display = 'none';
            });

            if (selectedCount > 0) {
                document.getElementById('noSelectionAlert').style.display = 'none';
                selectedCheckboxes.forEach(function(cb) {
                    var idx = cb.getAttribute('data-idx');
                    var card = document.getElementById('preview_' + layout + '_' + idx);
                    if (card) {
                        card.style.display = 'flex';
                    }
                });
            } else {
                document.getElementById('noSelectionAlert').style.display = 'block';
            }
        }

        function onQrCheckChanged() {
            updateLivePreview();
        }

        function AssetQrSelectAll(state) {
            document.querySelectorAll('.checkbox-item').forEach(function(item) {
                if (item.style.display !== 'none') {
                    var cb = item.querySelector('.qr-checkbox');
                    if (cb) {
                        cb.checked = state;
                    }
                }
            });
            updateLivePreview();
        }

        function AssetQrRequireSelection() {
            if (document.querySelectorAll('.qr-checkbox:checked').length === 0) {
                alert('Pilih minimal satu item sebelum mencetak PDF.');
                return false;
            }
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.layout-radio').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    updateLivePreview();
                });
            });

            var searchInput = document.getElementById('filterSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    var query = this.value.toLowerCase().trim();
                    document.querySelectorAll('.checkbox-item').forEach(function(item) {
                        var text = item.getAttribute('data-search') || '';
                        if (query === '' || text.indexOf(query) !== -1) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }

            updateLivePreview();
        });
    </script>
@endsection
