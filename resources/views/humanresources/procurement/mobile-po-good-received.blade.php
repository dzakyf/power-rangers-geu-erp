<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Good Received PO - Mobile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --gr-green-dark: #065f46;
            --gr-green-mid: #0f766e;
            --gr-green-main: #15803d;
            --gr-bg: #f3f6f4;
            --gr-border: #e2e8f0;
            --gr-text: #0f172a;
            --gr-muted: #64748b;
        }
        body {
            background: var(--gr-bg);
            color: var(--gr-text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding-bottom: 48px;
            margin: 0;
        }
        .m-wrap {
            max-width: 780px;
            margin: 0 auto;
            padding: 14px 16px 24px;
        }
        .m-header-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 14px;
            min-height: 38px;
        }
        .m-back-btn {
            position: absolute;
            left: 0;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid var(--gr-border);
            background: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--gr-text);
            text-decoration: none !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        }
        .m-page-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--gr-text);
            margin: 0;
        }

        /* Hero Card ala Tracking PR */
        .m-hero-card {
            background: linear-gradient(135deg, #065f46 0%, #0d9488 100%);
            border-radius: 18px;
            padding: 18px;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(6, 95, 70, 0.18);
            margin-bottom: 14px;
        }
        .m-hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }
        .m-hero-title {
            font-size: 16px;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 3px;
        }
        .m-hero-sub {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
        }
        .m-hero-badge {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.28);
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .m-search-box {
            background: #ffffff;
            border-radius: 12px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }
        .m-search-box i {
            color: #64748b;
            font-size: 13px;
        }
        .m-search-box input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 13px;
            color: var(--gr-text);
            background: transparent;
        }

        /* Filter Pills */
        .m-filter-pills {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
            margin-bottom: 8px;
            scrollbar-width: none;
        }
        .m-filter-pills::-webkit-scrollbar { display: none; }
        .m-pill {
            border: 1px solid var(--gr-border);
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 999px;
            white-space: nowrap;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .m-pill .pill-num {
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            padding: 1px 6px;
            border-radius: 999px;
        }
        .m-pill.active {
            background: #065f46;
            border-color: #065f46;
            color: #ffffff;
        }
        .m-pill.active .pill-num {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
        }
        .m-count-caption {
            font-size: 11.5px;
            color: var(--gr-muted);
            margin-bottom: 10px;
        }

        /* Card PO ala Tracking PR */
        .m-po-card {
            background: #ffffff;
            border: 1px solid var(--gr-border);
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }
        .m-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
        }
        .m-tag-cat {
            background: #dcfce7;
            color: #166534;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .m-badge {
            font-size: 10.5px;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 6px;
            text-transform: uppercase;
        }
        .m-badge-open { background: #e0f2fe; color: #0369a1; }
        .m-badge-partial { background: #fef9c3; color: #854d0e; }
        .m-badge-received { background: #dcfce7; color: #166534; }

        .m-po-number {
            font-size: 15px;
            font-weight: 800;
            color: var(--gr-text);
            margin-bottom: 6px;
        }
        .m-meta-row {
            font-size: 12px;
            color: #475569;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .m-meta-row i {
            width: 14px;
            color: #64748b;
            text-align: center;
        }
        .m-amount-highlight {
            color: #15803d;
            font-weight: 800;
        }

        /* Progress Bar Penerimaan */
        .m-progress-wrap {
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .m-progress-head {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 4px;
        }
        .m-progress-track {
            height: 6px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }
        .m-progress-fill {
            height: 100%;
            background: #16a34a;
            border-radius: 999px;
            transition: width 0.25s ease;
        }
        .m-progress-fill.is-partial {
            background: #d97706;
        }
        .m-progress-fill.is-empty {
            background: #94a3b8;
        }

        /* Expand Bar ala "Lihat Detail Jejak & Item" */
        .m-expand-btn {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
        }
        .m-expand-btn:hover {
            background: #f1f5f9;
        }
        .m-item-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 12px;
            margin-bottom: 8px;
        }
        .m-gr-chip {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-block;
            margin: 0 4px 4px 0;
        }

        /* Floating Label Styles (Tanpa Placeholder) */
        .m-float-group {
            position: relative;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 11px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .m-float-group:focus-within {
            border-color: #15803d;
            box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.12);
        }
        .m-float-group .m-float-control {
            width: 100%;
            border: none;
            outline: none;
            background: transparent;
            padding: 20px 12px 6px;
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.35;
            border-radius: 10px;
        }
        .m-float-group textarea.m-float-control {
            min-height: 68px;
            resize: vertical;
        }
        .m-float-group .m-float-label {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12.5px;
            font-weight: 600;
            color: #64748b;
            pointer-events: none;
            transition: all 0.15s ease;
            margin: 0;
        }
        .m-float-group.is-textarea .m-float-label {
            top: 16px;
            transform: none;
        }
        .m-float-group:focus-within .m-float-label,
        .m-float-group.has-value .m-float-label {
            top: 6px;
            transform: none;
            font-size: 10px;
            font-weight: 800;
            color: #15803d;
            letter-spacing: 0.2px;
        }
        .m-float-group.is-textarea:focus-within .m-float-label,
        .m-float-group.is-textarea.has-value .m-float-label {
            top: 6px;
            transform: none;
        }
    </style>
</head>
<body>
@php
    $totalAll      = $poReceiveList->count();
    $totalNeedRec  = $poReceiveList->filter(fn($x) => $x->can_receive && $x->status_receive !== 'PARTIAL')->count();
    $totalPartial  = $poReceiveList->filter(fn($x) => $x->can_receive && $x->status_receive === 'PARTIAL')->count();
    $totalReceived = $poReceiveList->filter(fn($x) => !$x->can_receive)->count();
@endphp

<div class="m-wrap">
    <div class="m-header-bar">
        <a href="javascript:history.back()" class="m-back-btn"><i class="fas fa-arrow-left"></i></a>
        <h1 class="m-page-title">Good Received PO</h1>
    </div>

    <!-- Hero Card ala Tracking PR -->
    <div class="m-hero-card">
        <div class="m-hero-top">
            <div>
                <div class="m-hero-title">Konfirmasi Penerimaan Barang PO</div>
                <div class="m-hero-sub">Pantau progress terima barang, surat jalan &amp; cetak GR</div>
            </div>
            <span class="m-hero-badge"><i class="fas fa-boxes mr-1"></i>{{ $totalAll }} PO</span>
        </div>
        <div class="m-search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="SearchPoMobile" aria-label="Cari nomor PO, ref PR, vendor, department">
        </div>
    </div>

    <!-- Filter Pills dengan Counter -->
    <div class="m-filter-pills">
        <button type="button" class="m-pill active" data-filter="ALL">
            Semua <span class="pill-num">{{ $totalAll }}</span>
        </button>
        <button type="button" class="m-pill" data-filter="ACTIONABLE">
            Perlu Diterima <span class="pill-num">{{ $totalNeedRec }}</span>
        </button>
        <button type="button" class="m-pill" data-filter="PARTIAL">
            Partial <span class="pill-num">{{ $totalPartial }}</span>
        </button>
        <button type="button" class="m-pill" data-filter="RECEIVED">
            Received / Done <span class="pill-num">{{ $totalReceived }}</span>
        </button>
    </div>

    <div class="m-count-caption" id="VisibleCountCaption">
        Menampilkan {{ $totalAll }} Purchase Order
    </div>

    <div id="PoMobileList">
    @forelse($poReceiveList as $po)
        @php
            $group = $po->can_receive
                ? ($po->status_receive === 'PARTIAL' ? 'PARTIAL' : 'ACTIONABLE')
                : 'RECEIVED';

            $badgeClass = (!$po->can_receive || $po->status_receive === 'RECEIVED')
                ? 'm-badge-received'
                : ($po->status_receive === 'PARTIAL' ? 'm-badge-partial' : 'm-badge-open');

            $totalOrderQty = 0;
            $totalRecQty   = 0;
            foreach ($po->items as $it) {
                $totalOrderQty += floatval($it->Qty ?? 0);
                $totalRecQty   += floatval($it->Qty_Receive ?? 0);
            }
            $pct = $totalOrderQty > 0 ? min(100, round(($totalRecQty / $totalOrderQty) * 100)) : ($po->can_receive ? 0 : 100);
            $fillClass = $pct >= 100 ? '' : ($pct > 0 ? 'is-partial' : 'is-empty');
            $catLabel  = ($po->department ?: '-') . ' • ' . ($po->po_date_label ?: '-');
        @endphp

        <div class="m-po-card po-card-item"
             data-group="{{ $group }}"
             data-search="{{ strtolower($po->po_no . ' ' . $po->pr_no . ' ' . $po->vendor . ' ' . $po->department) }}">

            <div class="m-card-top">
                <span class="m-tag-cat">{{ $catLabel }}</span>
                <span class="m-badge {{ $badgeClass }}">{{ $po->status_receive }}</span>
            </div>

            <div class="m-po-number">{{ $po->po_no }}</div>

            <div class="m-meta-row">
                <i class="fas fa-store"></i>
                <span><strong>{{ $po->vendor }}</strong></span>
            </div>
            <div class="m-meta-row">
                <i class="fas fa-file-alt"></i>
                <span>Ref PR: <strong>{{ $po->pr_no }}</strong> &bull; Sisa Qty: <strong class="text-danger">{{ number_format($po->total_remain, 0, ',', '.') }}</strong></span>
            </div>
            <div class="m-meta-row">
                <i class="fas fa-money-bill-wave"></i>
                <span>Nilai PO: <span class="m-amount-highlight">Rp {{ number_format($po->total, 0, ',', '.') }}</span></span>
            </div>

            @if(!empty($po->gr_history))
                <div class="mt-2">
                    @foreach($po->gr_history as $gr)
                        <span class="m-gr-chip"><i class="fas fa-check-circle mr-1"></i>{{ $gr->GR_No }} ({{ $gr->Incoming_Date }})</span>
                    @endforeach
                </div>
            @endif

            <!-- Progress Penerimaan -->
            <div class="m-progress-wrap">
                <div class="m-progress-head">
                    <span>
                        @if(!$po->can_receive)
                            Penerimaan Lengkap (Done)
                        @elseif($pct > 0)
                            Penerimaan Parsial ({{ number_format($totalRecQty, 0, ',', '.') }}/{{ number_format($totalOrderQty, 0, ',', '.') }})
                        @else
                            Menunggu Penerimaan Barang
                        @endif
                    </span>
                    <span>{{ $pct }}%</span>
                </div>
                <div class="m-progress-track">
                    <div class="m-progress-fill {{ $fillClass }}" style="width: {{ $pct }}%;"></div>
                </div>
            </div>

            <!-- Tombol Expand Detail & Form Penerimaan -->
            <button type="button" class="m-expand-btn btn-toggle-gr" data-target="#GrPanel_{{ $po->id }}">
                <span>
                    <i class="fas {{ $po->can_receive ? 'fa-box-open text-success' : 'fa-list text-secondary' }} mr-1"></i>
                    {{ $po->can_receive ? 'Input Good Received &amp; Detail Item' : 'Lihat Detail Item' }} ({{ count($po->items) }} Item)
                </span>
                <i class="fas fa-chevron-down"></i>
            </button>

            <div id="GrPanel_{{ $po->id }}" class="mt-3 pt-3 border-top" style="display: none;">
                <form class="form-receive-po" data-po-no="{{ $po->po_no }}" enctype="multipart/form-data">
                    <input type="hidden" name="po_no" value="{{ $po->po_no }}">

                    @foreach($po->items as $it)
                        <div class="m-item-box">
                            <div style="font-size: 12.5px; font-weight: 700; color: #0f172a;">
                                {{ $it->Product_Desc }}
                            </div>
                            <div style="font-size: 11px; color: #64748b;" class="mb-2">
                                Kode: <strong>{{ $it->Product_Code }}</strong> &bull;
                                Order: <strong>{{ $it->Qty }}</strong> {{ $it->Unit }} &bull;
                                Diterima: <strong>{{ $it->Qty_Receive }}</strong> &bull;
                                Sisa: <strong class="text-danger">{{ $it->Qty_Remain }}</strong>
                            </div>

                            @if($po->can_receive && $it->Qty_Remain > 0)
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <div class="m-float-group flex-fill">
                                        <input type="number"
                                               step="any"
                                               min="0"
                                               max="{{ $it->Qty_Remain }}"
                                               name="receive_qty[{{ $it->ID }}]"
                                               class="m-float-control qty-receive-input">
                                        <label class="m-float-label">Qty Terima (Max {{ $it->Qty_Remain }})</label>
                                    </div>
                                    <button type="button"
                                            class="btn btn-outline-success btn-sm btn-fill-remain"
                                            data-remain="{{ $it->Qty_Remain }}"
                                            style="white-space: nowrap; font-size: 11px; font-weight: 700; border-radius: 10px; padding: 10px 10px;">
                                        Isi Sisa ({{ $it->Qty_Remain }})
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if($po->can_receive)
                        <div class="m-float-group mb-2">
                            <input type="text" name="surat_jalan" class="m-float-control surat-jalan-input">
                            <label class="m-float-label">No. Surat Jalan / Resi</label>
                        </div>

                        <div class="form-group mb-2">
                            <div class="small font-weight-bold text-dark mb-1">Foto Bukti Penerimaan (Opsional, Max 5MB)</div>

                            <!-- Input file utama yang dikirim saat submit -->
                            <input type="file" name="receive_proof" accept="image/*" class="d-none proof-main-input">

                            <!-- Fallback input khusus kamera & galeri untuk browser non-Kodular -->
                            <input type="file" accept="image/*" capture="environment" class="d-none proof-camera-input">
                            <input type="file" accept="image/*" class="d-none proof-gallery-input">

                            <div class="d-flex" style="gap: 8px;">
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm flex-fill font-weight-bold btn-pick-camera"
                                        data-po-no="{{ $po->po_no }}"
                                        style="border-radius: 10px; padding: 8px 10px;">
                                    <i class="fas fa-camera mr-1"></i> Ambil dari Camera
                                </button>
                                <button type="button"
                                        class="btn btn-outline-success btn-sm flex-fill font-weight-bold btn-pick-gallery"
                                        data-po-no="{{ $po->po_no }}"
                                        style="border-radius: 10px; padding: 8px 10px;">
                                    <i class="fas fa-images mr-1"></i> Ambil dari Galeri
                                </button>
                            </div>

                            <div class="proof-preview-box mt-2" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between bg-light border rounded p-2">
                                    <div class="d-flex align-items-center" style="gap: 8px; min-width: 0;">
                                        <img src="" alt="Preview" class="proof-preview-img rounded" style="width: 46px; height: 46px; object-fit: cover;">
                                        <span class="small text-dark font-weight-bold text-truncate proof-file-name"></span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-clear-proof ml-2" title="Hapus Foto">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="m-float-group is-textarea mb-3">
                            <textarea name="receive_note" rows="2" class="m-float-control receive-note-input"></textarea>
                            <label class="m-float-label">Catatan Penerimaan</label>
                        </div>

                        <button type="submit" class="btn btn-success btn-sm btn-block font-weight-bold py-2 btn-submit-gr" style="border-radius: 11px;">
                            <i class="fas fa-check-circle mr-1"></i> Simpan Good Received ({{ $po->po_no }})
                        </button>
                    @endif
                </form>
            </div>
        </div>
    @empty
        <div class="m-po-card text-center text-muted py-4">Tidak ada data Purchase Order.</div>
    @endforelse
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

var activeFilter = 'ALL';
var activeKodularPoNo = null;

function syncFloatingLabels() {
    $('.m-float-group').each(function() {
        var $el = $(this).find('.m-float-control');
        var val = String($el.val() || '').trim();
        $(this).toggleClass('has-value', val.length > 0);
    });
}

$(document).on('input change blur', '.m-float-control', function() {
    var $group = $(this).closest('.m-float-group');
    var val = String($(this).val() || '').trim();
    $group.toggleClass('has-value', val.length > 0);
});

function applyMobileFilters() {
    var q = ($('#SearchPoMobile').val() || '').toLowerCase().trim();
    var visibleCount = 0;

    $('.po-card-item').each(function() {
        var grp = $(this).data('group');
        var txt = String($(this).data('search') || '');
        var matchGroup = (activeFilter === 'ALL') || (grp === activeFilter);
        var matchQuery = !q || txt.indexOf(q) !== -1;
        var show = matchGroup && matchQuery;
        $(this).toggle(show);
        if (show) visibleCount++;
    });

    $('#VisibleCountCaption').text('Menampilkan ' + visibleCount + ' Purchase Order');
}

$('.m-pill').on('click', function() {
    $('.m-pill').removeClass('active');
    $(this).addClass('active');
    activeFilter = $(this).data('filter');
    applyMobileFilters();
});

$('#SearchPoMobile').on('input', applyMobileFilters);

$('.btn-toggle-gr').on('click', function() {
    var $btn = $(this);
    var target = $btn.data('target');
    $(target).slideToggle(180, function() {
        syncFloatingLabels();
    });
    $btn.find('.fa-chevron-down, .fa-chevron-up').toggleClass('fa-chevron-down fa-chevron-up');
});

$('.btn-fill-remain').on('click', function() {
    var remain = $(this).data('remain');
    var $input = $(this).closest('.m-item-box').find('.qty-receive-input');
    $input.val(remain).trigger('change');
});

$('.form-receive-po').on('submit', function(e) {
    e.preventDefault();
    var $form = $(this);
    var $btn  = $form.find('.btn-submit-gr');
    var originalHtml = $btn.html();
    var formData = new FormData(this);

    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

    $.ajax({
        url: "{{ url('po-receive') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status === 'success') {
                alert(res.message || 'Penerimaan barang berhasil disimpan!');
                location.reload();
            } else {
                alert(res.message || 'Gagal menyimpan penerimaan barang.');
                $btn.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message)
                ? xhr.responseJSON.message
                : 'Terjadi kesalahan saat menyimpan data.';
            alert(msg);
            $btn.prop('disabled', false).html(originalHtml);
        }
    });
});

function attachFileToMainInput($form, file) {
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran foto maksimal 5 MB.');
        return;
    }

    var mainInput = $form.find('.proof-main-input')[0];
    if (typeof DataTransfer !== 'undefined') {
        var dt = new DataTransfer();
        dt.items.add(file);
        mainInput.files = dt.files;
    }

    var reader = new FileReader();
    reader.onload = function(e) {
        $form.find('.proof-preview-img').attr('src', e.target.result);
        $form.find('.proof-file-name').text(file.name);
        $form.find('.proof-preview-box').slideDown(140);
    };
    reader.readAsDataURL(file);
}

// Bersihkan whitespace / line-break hasil KIO4_Base641 dan ubah menjadi File
function kio4Base64ToFile(base64OrDataUrl, fileName) {
    var cleanName = String(fileName || '').trim();
    if (!cleanName) {
        cleanName = 'gr_proof_' + Date.now() + '.jpg';
    }
    cleanName = cleanName.split('/').pop().split('\\').pop();

    var raw = String(base64OrDataUrl || '').replace(/[\r\n\s]+/g, '').trim();
    if (!raw) return null;

    var mime = 'image/jpeg';
    var base64Body = raw;

    if (raw.indexOf('data:') === 0) {
        var commaIdx = raw.indexOf(',');
        if (commaIdx === -1) return null;
        var header = raw.substring(5, commaIdx);
        var mimePart = header.split(';')[0];
        if (mimePart) mime = mimePart;
        base64Body = raw.substring(commaIdx + 1);
    } else if (raw.indexOf('iVBORw0KGgo') === 0) {
        mime = 'image/png';
    } else if (raw.indexOf('R0lGOD') === 0) {
        mime = 'image/gif';
    }

    var binary = atob(base64Body);
    var len = binary.length;
    var u8arr = new Uint8Array(len);
    for (var i = 0; i < len; i++) {
        u8arr[i] = binary.charCodeAt(i);
    }

    return new File([u8arr], cleanName, { type: mime });
}

// Callback utama dari Kodular Web_Viewer1.Evaluate_JS (dari hasil KIO4_Base641)
window.receiveKodularPhoto = function(poNo, base64Data, fileName) {
    var targetPoNo = poNo || activeKodularPoNo;
    var $form = targetPoNo
        ? $('.form-receive-po[data-po-no="' + String(targetPoNo).replace(/"/g, '\\"') + '"]')
        : $('.form-receive-po').first();

    if (!$form.length) return;
    var file = kio4Base64ToFile(base64Data, fileName);
    if (file) {
        attachFileToMainInput($form, file);
    }
};

// Callback 2 parameter (paling ringkas dipanggil dari Evaluate_JS Kodular)
window.onKodularImagePicked = function(base64Data, fileName) {
    window.receiveKodularPhoto(activeKodularPoNo, base64Data, fileName);
};

// Kirim perintah OPEN_CAMERA / OPEN_GALLERY ke Kodular WebViewString
function sendKodularWebViewString(triggerName, poNo, fallbackSelector, $form) {
    activeKodularPoNo = poNo || '';

    if (window.AppInventor && typeof window.AppInventor.setWebViewString === 'function') {
        window.AppInventor.setWebViewString('');
        setTimeout(function() {
            window.AppInventor.setWebViewString(triggerName);
        }, 30);
        return;
    }

    var wv = window.Web_Viewer1 || window.WebView1 || null;
    if (wv && typeof wv.SetWebViewString === 'function') {
        wv.SetWebViewString('');
        setTimeout(function() {
            wv.SetWebViewString(triggerName);
        }, 30);
        return;
    }

    // Fallback jika dibuka di browser biasa (bukan Kodular WebView)
    $form.find(fallbackSelector).val('').trigger('click');
}

$('.btn-pick-camera').on('click', function() {
    var $form = $(this).closest('.form-receive-po');
    var poNo  = $(this).data('po-no') || $form.data('po-no') || '';
    sendKodularWebViewString('OPEN_CAMERA', poNo, '.proof-camera-input', $form);
});

$('.btn-pick-gallery').on('click', function() {
    var $form = $(this).closest('.form-receive-po');
    var poNo  = $(this).data('po-no') || $form.data('po-no') || '';
    sendKodularWebViewString('OPEN_GALLERY', poNo, '.proof-gallery-input', $form);
});

$('.proof-camera-input, .proof-gallery-input').on('change', function() {
    var file = this.files && this.files[0] ? this.files[0] : null;
    if (file) {
        attachFileToMainInput($(this).closest('.form-receive-po'), file);
    }
});

$('.btn-clear-proof').on('click', function() {
    var $form = $(this).closest('.form-receive-po');
    $form.find('.proof-main-input, .proof-camera-input, .proof-gallery-input').val('');
    $form.find('.proof-preview-img').attr('src', '');
    $form.find('.proof-file-name').text('');
    $form.find('.proof-preview-box').slideUp(140);
});

syncFloatingLabels();
</script>
</body>
</html>
