@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('hrd-ga-cleaning-index') }}">Cleaning Management</a></li>
            <li class="breadcrumb-item active"><span class="active">Cetak Label QR Area</span></li>
        </ol>
    </div>

    @php
        $checkedIds = $hasFilter
            ? $selectedIds
            : $allAreas->pluck('id')->map(function ($id) { return (int) $id; })->all();
    @endphp

    <style>
        .qr-print-page {
            width: 190mm;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
        }
        .qr-print-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 3.5mm;
            align-content: flex-start;
        }
        .qr-print-card {
            width: 88mm;
            height: 48mm;
            box-sizing: border-box;
            border: 1.5px solid #007bff;
            border-radius: 4px;
            padding: 2.5mm 3mm;
            display: flex;
            flex-direction: row;
            align-items: center;
            text-align: left;
            background: #fff;
            page-break-inside: avoid;
        }
        .qr-print-card img {
            width: 32mm;
            height: 32mm;
            margin: 0 3mm 0 0;
            flex-shrink: 0;
        }
        .qr-print-card .info-box {
            flex-grow: 1;
            font-size: 7.5pt;
            line-height: 1.35;
        }
        .qr-print-card .qr-name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #007bff;
            margin: 0 0 1.5mm 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 1mm;
        }
        .qr-print-card .qr-code {
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
        .qr-print-card .qr-meta {
            font-size: 6.5pt;
            color: #444;
            margin-top: 1mm;
        }
        .search-box {
            max-width: 280px;
        }
    </style>

    <form id="CleaningQrPrintForm" method="get" action="{{ url('hrd-ga-cleaning-area-print-qr') }}" class="no-print mb-3" target="_blank">
        <input type="hidden" name="filter" value="1" />
        <div class="card mt-3">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center">
                    <strong>Pilih Area Pembersihan yang Ingin Dicetak</strong>
                    <span class="badge bg-primary text-white badge-primary ms-2 ml-2" id="selectedBadge">Terpilih: 0 / {{ $allAreas->count() }}</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" id="filterSearch" class="form-control form-control-sm search-box" placeholder="Cari nama area / kode...">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="CleaningQrSelectAll(true)">Pilih Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="CleaningQrSelectAll(false)">Hilangkan Pilihan</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row" id="checkboxContainer">
                    @forelse ($allAreas as $area)
                        <div class="col-md-4 mb-2 checkbox-item" data-search="{{ strtolower($area->Area_Name . ' ' . $area->Area_Code . ' ' . $area->Qr_Code . ' ' . $area->Building . ' ' . $area->Floor . ' ' . $area->Category) }}">
                            <div class="form-check">
                                <input class="form-check-input area-checkbox" type="checkbox" name="ids[]" value="{{ $area->id }}" id="AreaCheck{{ $area->id }}" {{ in_array((int) $area->id, $checkedIds, true) ? 'checked' : '' }} onchange="onAreaCheckChanged()" />
                                <label class="form-check-label" for="AreaCheck{{ $area->id }}">
                                    <strong>{{ $area->Area_Name }}</strong>
                                    <small class="text-muted d-block">{{ $area->Building }} Lt. {{ $area->Floor }} ({{ $area->Category }})</small>
                                    <span class="badge bg-light text-dark badge-light border">{{ $area->Qr_Code ?: $area->Area_Code }}</span>
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="col-md-12 text-muted">Belum ada area pembersihan aktif.</div>
                    @endforelse
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap align-items-center gap-2">
                <button type="submit" name="action" value="pdf" class="btn btn-primary btn-sm" onclick="return CleaningQrRequireSelection();">
                    <i class="fa fa-print me-1 me-1"></i> Cetak mPDF (A4)
                </button>
                <button type="submit" name="action" value="download" class="btn btn-outline-success btn-sm" onclick="return CleaningQrRequireSelection();">
                    <i class="fa fa-download me-1 me-1"></i> Unduh PDF
                </button>
                <a class="btn btn-secondary btn-sm" href="{{ url('hrd-ga-cleaning-index') }}">
                    <i class="fa fa-arrow-left me-1 me-1"></i> Kembali ke Cleaning
                </a>
                <span class="text-muted small ms-auto ml-auto">Preview layar langsung otomatis ter-update tanpa reload halaman.</span>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Live Preview Label QR Area</h5>
        <span class="text-muted small" id="previewCountLabel">Menampilkan 0 kartu</span>
    </div>

    <div class="qr-print-page">
        <div class="qr-print-grid" id="areaPreviewGrid">
            @foreach ($allAreas as $area)
                <div class="qr-print-card preview-card" id="preview_area_{{ $area->id }}" data-id="{{ $area->id }}" style="display: none;">
                    <img src="{{ url('hrd-ga-cleaning-area-qr-image?code=' . urlencode($area->Qr_Code ?: $area->Area_Code)) }}" alt="{{ $area->Area_Code }}" />
                    <div class="info-box">
                        <div class="qr-name">{{ $area->Area_Name }}</div>
                        <div class="qr-meta">
                            <strong>Gedung:</strong> {{ $area->Building }}<br>
                            <strong>Lantai:</strong> {{ $area->Floor }}<br>
                            <strong>Kategori:</strong> {{ $area->Category }}
                        </div>
                        <div class="qr-code">{{ $area->Qr_Code ?: $area->Area_Code }}</div>
                    </div>
                </div>
            @endforeach
            <div id="noSelectionAlert" class="alert alert-warning w-100" style="display: none;">
                Tidak ada area yang dipilih. Centang minimal satu area di atas untuk melihat preview dan mencetak.
            </div>
        </div>
    </div>

    <script>
        var totalAreas = {{ $allAreas->count() }};

        function updateLivePreview() {
            var selectedCheckboxes = document.querySelectorAll('.area-checkbox:checked');
            var selectedCount = selectedCheckboxes.length;

            // Update badge dan counter
            document.getElementById('selectedBadge').innerText = 'Terpilih: ' + selectedCount + ' / ' + totalAreas;
            document.getElementById('previewCountLabel').innerText = 'Menampilkan ' + selectedCount + ' kartu';

            // Sembunyikan semua preview card terlebih dahulu
            document.querySelectorAll('.preview-card').forEach(function(el) {
                el.style.display = 'none';
            });

            // Tampilkan hanya yang dicentang
            if (selectedCount > 0) {
                document.getElementById('noSelectionAlert').style.display = 'none';
                selectedCheckboxes.forEach(function(cb) {
                    var id = cb.value;
                    var card = document.getElementById('preview_area_' + id);
                    if (card) {
                        card.style.display = 'flex';
                    }
                });
            } else {
                document.getElementById('noSelectionAlert').style.display = 'block';
            }
        }

        function onAreaCheckChanged() {
            updateLivePreview();
        }

        function CleaningQrSelectAll(state) {
            document.querySelectorAll('.checkbox-item').forEach(function(item) {
                if (item.style.display !== 'none') {
                    var cb = item.querySelector('.area-checkbox');
                    if (cb) {
                        cb.checked = state;
                    }
                }
            });
            updateLivePreview();
        }

        function CleaningQrRequireSelection() {
            if (document.querySelectorAll('.area-checkbox:checked').length === 0) {
                alert('Pilih minimal satu area pembersihan sebelum mencetak PDF.');
                return false;
            }
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Live Search
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

            // Inisialisasi awal preview
            updateLivePreview();
        });
    </script>
@endsection
