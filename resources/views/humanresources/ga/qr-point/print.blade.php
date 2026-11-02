@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="main-content-header">
        <h1>General Affairs</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('hrd-ga-qr-point-index') }}">QR Point</a></li>
            <li class="breadcrumb-item active"><span class="active">Cetak QR Point</span></li>
        </ol>
    </div>

    @php
        $checkedIds = $hasFilter
            ? $selectedIds
            : $points->pluck('ID')->map(function ($id) { return (int) $id; })->all();
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
            gap: 4mm;
            align-content: flex-start;
        }
        .qr-print-card {
            width: 60mm;
            height: 80mm;
            box-sizing: border-box;
            border: 1px solid #444;
            padding: 3mm;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            background: #fff;
        }
        .qr-print-card img {
            width: 45mm;
            height: 45mm;
            display: block;
            margin: 0 auto;
        }
        .qr-print-card .qr-name {
            font-size: 9.5pt;
            font-weight: 700;
            line-height: 1.15;
            margin-top: 2mm;
            max-height: 11mm;
            overflow: hidden;
        }
        .qr-print-card .qr-code {
            font-family: Consolas, monospace;
            font-size: 7.5pt;
            line-height: 1.2;
            margin-top: 1mm;
        }
        .qr-print-card .qr-meta {
            font-size: 6.5pt;
            color: #444;
            line-height: 1.2;
            margin-top: 1mm;
            max-height: 10mm;
            overflow: hidden;
        }

        /* Mode Compact (Stiker Horizontal) di Preview Layar */
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
        }
        .qr-print-card.compact .qr-name {
            font-size: 8.5pt;
            font-weight: bold;
            color: #007bff;
            margin: 0 0 1.5mm 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 1mm;
            max-height: none;
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
        <div class="alert alert-warning mt-3">{{ $warning }}</div>
    @endif

    <form id="GAQrPrintForm" method="get" action="{{ url('hrd-ga-qr-point-print') }}" class="no-print mb-3" target="_blank">
        <input type="hidden" name="filter" value="1" />
        <div class="card mt-3">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <strong>Pilih QR Point yang Dicetak</strong>
                    <span class="badge badge-primary bg-primary text-white" id="selectedBadge">Terpilih: 0 / {{ $points->count() }}</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <input type="text" id="filterSearch" class="form-control form-control-sm search-box" placeholder="Cari nama / kode QR...">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="GAQrSelectAll(true)">Pilih Semua</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="GAQrSelectAll(false)">Hilangkan Pilihan</button>
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
                                <small class="text-muted d-block">Cocok untuk pintu, dinding, ruangan, fasilitas umum (Header HCGA & Detail Lokasi)</small>
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="radio" id="layoutCompact" name="layout" value="compact" class="form-check-input layout-radio" {{ ($layout ?? 'card') === 'compact' ? 'checked' : '' }}>
                            <label class="form-check-label" for="layoutCompact">
                                <strong>Format Stiker Horizontal (Mirip Cleaning)</strong>
                                <small class="text-muted d-block">Stiker horizontal 88x48mm 2 kolom (QR Code + Detail Ruangan Ringkas)</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row" id="checkboxContainer">
                    @forelse ($points as $point)
                        <div class="col-md-4 mb-2 checkbox-item" data-search="{{ strtolower($point->Name . ' ' . $point->QR_Code . ' ' . $point->Building . ' ' . $point->Floor) }}">
                            <div class="form-check">
                                <input class="form-check-input qr-checkbox" type="checkbox" name="ids[]" value="{{ $point->ID }}" id="GAQrPrint{{ $point->ID }}" {{ in_array((int) $point->ID, $checkedIds, true) ? 'checked' : '' }} onchange="onQrCheckChanged()" />
                                <label class="form-check-label" for="GAQrPrint{{ $point->ID }}">
                                    <strong>{{ $point->Name }}</strong>
                                    <small class="text-muted d-block">{{ $point->QR_Code }}</small>
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="col-md-12 text-muted">Belum ada QR point aktif.</div>
                    @endforelse
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <button type="submit" name="action" value="print" class="btn btn-success btn-sm" onclick="return GAQrRequireSelection();">
                        <i class="mdi mdi-printer"></i> Cetak PDF (mPDF)
                    </button>
                    <button type="submit" name="action" value="download" class="btn btn-outline-success btn-sm" onclick="return GAQrRequireSelection();">
                        <i class="mdi mdi-download"></i> Unduh PDF
                    </button>
                    <a class="btn btn-secondary btn-sm" href="{{ url('hrd-ga-qr-point-index') }}">Kembali</a>
                </div>
                <span class="text-muted small">Preview layar langsung otomatis ter-update tanpa reload halaman.</span>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Live Preview Pilihan (<span id="layoutNameLabel">{{ ($layout ?? 'card') === 'compact' ? 'Format Stiker Horizontal' : 'Format Kartu Lengkap' }}</span>)</h5>
        <span class="text-muted small" id="previewCountLabel">Menampilkan 0 kartu</span>
    </div>

    <div id="noSelectionAlert" class="alert alert-secondary text-center my-3" style="display: none;">
        Tidak ada QR Point yang dipilih. Centang minimal satu lokasi di atas untuk melihat preview kartu.
    </div>

    <div class="qr-print-page">
        <div class="qr-print-grid {{ ($layout ?? 'card') === 'compact' ? 'compact' : '' }}" id="qrPreviewGrid">
            @foreach ($points as $point)
                @php
                    $isCheckedPoint = in_array((int) $point->ID, $checkedIds, true);
                    $isCompactLayout = ($layout ?? 'card') === 'compact';
                @endphp
                <div class="qr-print-card compact preview-card-compact" id="preview_compact_{{ $point->ID }}" style="display: {{ ($isCheckedPoint && $isCompactLayout) ? 'flex' : 'none' }};">
                    <img src="{{ url('hrd-ga-qr-point-image?code=' . urlencode($point->QR_Code)) }}" alt="{{ $point->QR_Code }}" />
                    <div class="info-box">
                        <div class="qr-name">{{ $point->Name }}</div>
                        <div class="qr-meta">
                            <strong>Tipe:</strong> {{ $point->Point_Type }}<br>
                            <strong>Lokasi:</strong> {{ $point->Building ?: '-' }} @if ($point->Floor) Lt. {{ $point->Floor }} @endif
                        </div>
                        <div class="qr-code">{{ $point->QR_Code }}</div>
                    </div>
                </div>

                <div class="qr-print-card preview-card-card" id="preview_card_{{ $point->ID }}" style="display: {{ ($isCheckedPoint && !$isCompactLayout) ? 'flex' : 'none' }};">
                    <img src="{{ url('hrd-ga-qr-point-image?code=' . urlencode($point->QR_Code)) }}" alt="{{ $point->QR_Code }}" />
                    <div class="qr-name">{{ $point->Name }}</div>
                    <div class="qr-code">{{ $point->QR_Code }}</div>
                    <div class="qr-meta">
                        {{ $point->Point_Type }}
                        @if ($point->Building) | {{ $point->Building }} @endif
                        @if ($point->Floor) | Lt. {{ $point->Floor }} @endif
                        @if ($point->Room) | {{ $point->Room }} @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        var totalPoints = {{ $points->count() }};

        function getActiveLayout() {
            var checked = document.querySelector('input[name="layout"]:checked');
            return checked ? checked.value : 'card';
        }

        function updateLivePreview() {
            var layout = getActiveLayout();
            var grid = document.getElementById('qrPreviewGrid');
            var selectedCheckboxes = document.querySelectorAll('.qr-checkbox:checked');
            var selectedCount = selectedCheckboxes.length;

            // Update badge dan label
            document.getElementById('selectedBadge').innerText = 'Terpilih: ' + selectedCount + ' / ' + totalPoints;
            document.getElementById('previewCountLabel').innerText = 'Menampilkan ' + selectedCount + ' kartu';
            document.getElementById('layoutNameLabel').innerText = (layout === 'compact') ? 'Format Stiker Horizontal' : 'Format Kartu Lengkap';

            // Set class grid
            if (layout === 'compact') {
                grid.classList.add('compact');
            } else {
                grid.classList.remove('compact');
            }

            // Sembunyikan semua preview card terlebih dahulu
            document.querySelectorAll('.preview-card-compact, .preview-card-card').forEach(function(el) {
                el.style.display = 'none';
            });

            // Tampilkan hanya yang dipilih sesuai layout aktif
            if (selectedCount > 0) {
                document.getElementById('noSelectionAlert').style.display = 'none';
                selectedCheckboxes.forEach(function(cb) {
                    var id = cb.value;
                    var card = document.getElementById('preview_' + layout + '_' + id);
                    if (card) {
                        card.style.display = (layout === 'compact') ? 'flex' : 'flex';
                    }
                });
            } else {
                document.getElementById('noSelectionAlert').style.display = 'block';
            }
        }

        function onQrCheckChanged() {
            updateLivePreview();
        }

        function GAQrSelectAll(state) {
            // Hanya centang/hilangkan yang terlihat (jika sedang difilter cari) atau semua
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

        function GAQrRequireSelection() {
            if (document.querySelectorAll('.qr-checkbox:checked').length === 0) {
                alert('Pilih minimal satu QR point sebelum mencetak PDF.');
                return false;
            }
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Listener perubahan radio layout
            document.querySelectorAll('.layout-radio').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    updateLivePreview();
                });
            });

            // Search filter real-time
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
