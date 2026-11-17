@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    @csrf

    <style>
        .settlement-page-wrapper {
            margin-bottom: 2.5rem;
        }


        .settlement-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .settlement-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.9rem 1.25rem;
            font-weight: 700;
            font-size: 0.95rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 10px 10px 0 0;
        }

        .settlement-section-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .settlement-btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.55rem 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
        }

        .settlement-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .form-label-custom {
            font-size: 0.825rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        .table-expense th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .total-summary-card {
            background: #f8fafc;
            border-left: 4px solid #0d6efd;
            padding: 1rem 1.25rem;
            border-radius: 8px;
        }

        /* Interactive Settlement Bank Section Styling */
        .bank-option-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.15rem;
            cursor: pointer;
            background: #ffffff;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            height: 100%;
        }

        .bank-option-card:hover {
            border-color: #198754;
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.12);
            transform: translateY(-2px);
        }

        .bank-option-card.selected {
            border: 2px solid #198754 !important;
            background-color: #f0fdf4 !important;
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.16) !important;
        }

        .bank-option-card .bank-badge {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
        }

        .bank-acc-no {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            color: #0f172a;
        }

        .btn-copy-acc {
            padding: 0.25rem 0.65rem;
            font-size: 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.15s ease;
        }

        .employee-bank-box {
            border: 1.5px solid #3b82f6;
            border-radius: 12px;
            background: #eff6ff;
            padding: 1.15rem 1.35rem;
        }

        .settlement-status-banner {
            border-radius: 12px;
            padding: 1.15rem 1.35rem;
            margin-bottom: 1.25rem;
            transition: all 0.3s ease;
        }

        /* Modernized Advance Input, Confirmed Bank & Dropzone Styling */
        .advance-input-card {
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .advance-input-card:focus-within {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .confirmed-bank-card {
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            background: #ffffff;
            padding: 1.1rem 1.35rem;
            transition: all 0.2s ease;
        }

        .invoice-upload-dropzone {
            border: 2px dashed #94a3b8;
            border-radius: 12px;
            background: #f8fafc;
            padding: 1.5rem 1.25rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .invoice-upload-dropzone:hover,
        .invoice-upload-dropzone.dragover {
            border-color: #2563eb;
            background: #eff6ff;
            transform: translateY(-1px);
        }

        .file-selected-box {
            border: 1.5px solid #86efac;
            background: #f0fdf4;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            transition: all 0.25s ease;
        }
    </style>

    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
                Travel, Expense & Settlement
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('hrd-settlement-index') }}">Settlement</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Buat
                        Pengajuan Settlement</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ url('hrd-settlement-index') }}" class="btn btn-outline-secondary settlement-btn">
                <i class="fa fa-arrow-left"></i> Kembali ke List
            </a>
        </div>
    </div>

    <div class="settlement-page-wrapper">
        <div class="card settlement-card">
            <div class="card-body p-4">
                <form id="settlementForm" enctype="multipart/form-data">

                    <!-- Section 1: Informasi Dasar -->
                    <div class="settlement-section-card">
                        <div class="settlement-section-header">
                            <i class="fa fa-id-card text-primary"></i> Data Pokok & Referensi Pengajuan
                        </div>
                        <div class="p-3">
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <label for="STTL_No" class="form-label-custom">No. Settlement</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i
                                                class="fa fa-barcode"></i></span>
                                        <input type="text" id="STTL_No" class="form-control bg-light font-weight-bold"
                                            name="STTL_No" value="{{ $STTL_No }}" readonly />
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="Requester_NIK" class="form-label-custom">NIK Pemohon</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i
                                                class="fa fa-id-badge"></i></span>
                                        <input type="text" id="Requester_NIK" class="form-control bg-light"
                                            name="Requester_NIK" value="{{ $user->Emp_ID ?: '-' }}" readonly />
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="Requester_Name" class="form-label-custom">Nama Karyawan Pemohon
                                        <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i class="fa fa-user"></i></span>
                                        <input type="text" id="Requester_Name" class="form-control" name="Requester_Name"
                                            value="{{ $user->Name ?: $user->username }}" required />
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <label for="Department" class="form-label-custom">Departemen <span
                                            class="text-danger">*</span></label>
                                    <select id="Department" class="form-control select2" name="Department" required>
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach ($departments as $d)
                                            <option value="{{ $d->Department }}">{{ $d->Department }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12 col-12">
                                    <label for="SPPD_ID" class="form-label-custom">Referensi SPPD
                                        (Opsional)</label>
                                    <select id="SPPD_ID" class="form-control select2">
                                        <option value="">-- Tanpa Referensi SPPD --</option>
                                        @foreach ($sppdList ?? collect() as $sp)
                                            <option value="{{ $sp->ID }}">{{ $sp->SPPD_No }} —
                                                {{ $sp->Destination }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-primary mt-1 d-block fw-semibold" id="sppdInfo"></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Rincian Pengeluaran -->
                    <div class="settlement-section-card">
                        <div class="settlement-section-header justify-content-between">
                            <span><i class="fa fa-receipt text-primary"></i> Rincian Penggunaan Biaya
                                Real</span>
                            <button type="button" class="btn btn-primary btn-sm settlement-btn" onclick="tambahRow();">
                                <i class="fa fa-plus"></i> Tambah Item Biaya
                            </button>
                        </div>
                        <div class="p-3">
                            <div class="table-responsive">
                                <table id="tblExpense" class="table table-bordered table-expense align-middle mb-2">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 220px;">Jenis Pengeluaran <span
                                                    class="text-danger">*</span></th>
                                            <th style="width: 200px;">Jumlah (Rp) <span class="text-danger">*</span></th>
                                            <th>Keterangan / Keperluan</th>
                                            <th style="width: 60px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input type="text" id="Expense_0" class="form-control"
                                                    placeholder="Contoh: Tiket, Penginapan, Makan" required />
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light text-muted">Rp</span>
                                                    <input type="text" id="Cost_0"
                                                        class="form-control text-end cost-input" placeholder="0"
                                                        onkeyup="hitungTotal();" required />
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" id="Desc_0" class="form-control"
                                                    placeholder="Catatan tambahan keperluan pengeluaran" />
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                    onclick="hapusRow(this);" title="Hapus baris">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="total-summary-card mt-3">
                                <div class="row align-items-center g-3">
                                    <div class="col-md-3 col-6">
                                        <span class="text-muted small text-uppercase fw-bold">Rincian Real (Manual):</span>
                                        <div class="h5 mb-0 fw-bold text-primary">
                                            Rp <span id="TotalManualCost">0</span>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Dari tabel di atas</small>
                                    </div>
                                    <div class="col-md-3 col-6" id="MatrixAllowanceBox" style="display: none;">
                                        <span class="text-muted small text-uppercase fw-bold">Tunjangan Matriks
                                            SPPD:</span>
                                        <div class="h5 mb-0 fw-bold text-info">
                                            + Rp <span id="MatrixAllowanceNominal">0</span>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Otomatis sesuai
                                            regulasi</small>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <span class="text-muted small text-uppercase fw-bold">Total Diperhitungkan:</span>
                                        <div class="h4 mb-0 fw-bold text-dark">
                                            Rp <span id="TotalCost">0</span>
                                        </div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Total Real + Tunjangan
                                            Matriks</small>
                                    </div>
                                    <div class="col-md-3 col-6 text-md-end" id="SelisihBox" style="display: none;">
                                        <span class="text-muted small text-uppercase fw-bold" id="SelisihLabel">Reimburse
                                            / Lebih Bayar:</span>
                                        <div class="h5 mb-0 fw-bold" id="SelisihNominal">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Advance & Pencairan / Dokumen -->
                    <div class="settlement-section-card">
                        <div class="settlement-section-header justify-content-between align-items-center">
                            <span>
                                <i class="fa fa-money-check-alt text-primary"></i> Advance, Rekening & Bukti Struk/Invoice
                            </span>
                            <span id="settlementHeaderBadge" class="badge bg-light text-muted border px-2 py-1"
                                style="font-size: 0.78rem;">
                                <i class="fa fa-calculator me-1"></i> Mode: Otomatis
                            </span>
                        </div>
                        <div class="p-3">
                            <div class="row g-3">
                                <!-- Sub-row 1: Advance Nominal & Live Status Direction Card -->
                                <div class="col-md-6 col-12">
                                    <div
                                        class="advance-input-card p-3 shadow-sm h-100 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label for="Advance_Nominal"
                                                class="form-label-custom mb-0 text-dark fw-bold d-flex align-items-center gap-2">
                                                <i class="fa fa-hand-holding-usd text-primary fs-6"></i> Biaya Uang Muka /
                                                Advance yang Diterima
                                            </label>
                                            <span class="badge bg-light text-secondary border font-monospace px-2 py-1"
                                                style="font-size: 0.72rem;">IDR (Rp)</span>
                                        </div>
                                        <div class="input-group input-group-lg rounded-3 overflow-hidden border my-1">
                                            <span
                                                class="input-group-text bg-light border-0 text-primary fw-bold px-3 fs-5">Rp</span>
                                            <input type="text" id="Advance_Nominal"
                                                class="form-control border-0 fw-bold text-dark fs-5 shadow-none"
                                                name="Advance_Nominal" placeholder="0" onkeyup="hitungTotal();" />
                                            <button type="button" class="btn btn-light border-0 text-muted px-3"
                                                onclick="$('#Advance_Nominal').val('').trigger('keyup');"
                                                title="Hapus nominal advance">
                                                <i class="fa fa-times-circle"></i>
                                            </button>
                                        </div>
                                        <div
                                            class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top">
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <i class="fa fa-info-circle text-primary me-1"></i> Kosongkan / isi 0 jika
                                                pengajuan adalah reimbursement murni tanpa advance.
                                            </small>
                                            <button type="button"
                                                class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill"
                                                style="font-size: 0.72rem;"
                                                onclick="$('#Advance_Nominal').val('0').trigger('keyup');">
                                                Set Rp 0
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div id="settlementDirectionBox"
                                        class="p-3 rounded-3 border bg-white shadow-sm h-100 d-flex flex-column justify-content-between">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span
                                                class="form-label-custom mb-0 text-dark fw-bold d-flex align-items-center gap-2">
                                                <i class="fa fa-exchange-alt text-primary fs-6"></i> Status & Arah Aliran
                                                Dana Settlement
                                            </span>
                                            <span id="settlementHeaderBadge"
                                                class="badge bg-light text-muted border px-2 py-1"
                                                style="font-size: 0.72rem;">
                                                <i class="fa fa-calculator me-1"></i> Menunggu Input
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between py-2">
                                            <div class="d-flex align-items-center gap-3">
                                                <div id="dirIconWrapper"
                                                    class="p-2 rounded-circle bg-light text-muted d-flex align-items-center justify-content-center shadow-xs"
                                                    style="width: 44px; height: 44px;">
                                                    <i id="dirIcon" class="fa fa-info-circle fa-lg"></i>
                                                </div>
                                                <div>
                                                    <div id="dirTitle" class="fw-bold text-dark"
                                                        style="font-size: 0.95rem;">Belum ada perhitungan</div>
                                                    <div id="dirDesc" class="text-muted" style="font-size: 0.76rem;">
                                                        Masukkan rincian biaya atau nominal advance</div>
                                                </div>
                                            </div>
                                            <div id="dirBadge" class="h5 mb-0 fw-bold text-secondary font-monospace">-
                                            </div>
                                        </div>
                                        <div class="border-top pt-2 mt-2">
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                <i class="fa fa-shield-alt text-success me-1"></i> Dihitung otomatis dari
                                                selisih total biaya real vs uang muka.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Settlement Bank Interactive Container -->
                                <div class="col-12 mt-3">
                                    <!-- Case 1: Lebih Bayar (Pengembalian ke Perusahaan) -->
                                    <div id="panelLebihBayar" style="display: none;">
                                        <div class="settlement-status-banner border border-success bg-white shadow-sm">
                                            <div
                                                class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-success text-white px-2 py-1"><i
                                                            class="fa fa-undo-alt me-1"></i> LEBIH BAYAR</span>
                                                    <span class="fw-bold text-success fs-6">Pengembalian Dana ke
                                                        Perusahaan</span>
                                                </div>
                                                <div class="text-end">
                                                    <span class="text-muted small">Nominal yang Wajib Dikembalikan:</span>
                                                    <span class="fw-bold text-success fs-5 ms-1" id="lbNominalText">Rp
                                                        0</span>
                                                </div>
                                            </div>
                                            <p class="small text-muted mb-3">
                                                Uang muka yang diterima melebihi pengeluaran aktual. Karyawan pemohon
                                                <strong>wajib mengembalikan sisa dana</strong> tersebut dengan mentransfer
                                                ke salah satu rekening resmi kantor di bawah ini:
                                            </p>

                                            <label class="form-label-custom mb-2">
                                                <i class="fa fa-university text-success me-1"></i> Pilih Rekening Bank
                                                Kantor Tujuan Transfer Pengembalian:
                                            </label>

                                            <div class="row g-3" id="companyBankList">
                                                @foreach ($companyBanks ?? [] as $idx => $cb)
                                                    @php
                                                        $bankCode = strtoupper($cb->Bank_Name);
                                                        $badgeColor = 'bg-primary text-white';
                                                        if (strpos($bankCode, 'BCA') !== false) {
                                                            $badgeColor = 'bg-primary text-white';
                                                        } elseif (strpos($bankCode, 'MANDIRI') !== false) {
                                                            $badgeColor = 'bg-warning text-dark';
                                                        } elseif (strpos($bankCode, 'BRI') !== false) {
                                                            $badgeColor = 'bg-info text-white';
                                                        } elseif (strpos($bankCode, 'DANAMON') !== false) {
                                                            $badgeColor = 'bg-danger text-white';
                                                        }
                                                        $bankStrVal =
                                                            $cb->Bank_Name .
                                                            ' - ' .
                                                            $cb->Acc_No .
                                                            ' a.n. ' .
                                                            $cb->Acc_Hold .
                                                            ($cb->Bank_Branch
                                                                ? ' (Cab. ' . $cb->Bank_Branch . ')'
                                                                : '');
                                                    @endphp
                                                    <div class="col-lg-4 col-md-6 col-12">
                                                        <div class="bank-option-card company-bank-card {{ $idx === 0 ? 'selected' : '' }}"
                                                            data-bank-str="{{ $bankStrVal }}"
                                                            data-acc-no="{{ $cb->Acc_No }}"
                                                            onclick="pilihRekeningKantor(this);">
                                                            <div
                                                                class="d-flex justify-content-between align-items-start mb-2">
                                                                <span
                                                                    class="bank-badge {{ $badgeColor }}">{{ $cb->Bank_Name }}</span>
                                                                <span
                                                                    class="badge-status-check text-success {{ $idx === 0 ? '' : 'd-none' }}">
                                                                    <i class="fa fa-check-circle fs-6"></i>
                                                                </span>
                                                            </div>
                                                            <div
                                                                class="d-flex align-items-center justify-content-between my-2">
                                                                <span class="bank-acc-no">{{ $cb->Acc_No }}</span>
                                                                <button type="button"
                                                                    class="btn btn-outline-secondary btn-copy-acc"
                                                                    onclick="salinKeClipboard('{{ $cb->Acc_No }}', this, event);"
                                                                    title="Salin nomor rekening">
                                                                    <i class="fa fa-copy"></i> <span>Salin</span>
                                                                </button>
                                                            </div>
                                                            <div class="small fw-semibold text-truncate text-secondary"
                                                                title="{{ $cb->Acc_Hold }}">
                                                                <i class="fa fa-user-circle me-1 text-muted"></i>a.n.
                                                                {{ $cb->Acc_Hold }}
                                                            </div>
                                                            @if (!empty($cb->Bank_Branch))
                                                                <div class="small text-muted text-truncate mt-1"
                                                                    style="font-size: 0.735rem;">
                                                                    <i class="fa fa-map-marker-alt me-1"></i>Cab.
                                                                    {{ $cb->Bank_Branch }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="alert alert-warning d-flex align-items-center gap-2 mt-3 mb-0 p-2 rounded-2"
                                                style="font-size: 0.8rem;">
                                                <i class="fa fa-exclamation-triangle text-warning fa-lg"></i>
                                                <div>
                                                    <strong>Perhatian Pengembalian:</strong> Setelah melakukan transfer
                                                    pengembalian ke rekening kantor di atas, <strong>unggah bukti
                                                        transfer</strong> bersama struk/invoice pengeluaran pada lampiran di
                                                    bawah.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Case 2: Kurang Bayar (Reimbursement Karyawan) -->
                                    <div id="panelKurangBayar" style="display: none;">
                                        <div class="settlement-status-banner border border-primary bg-white shadow-sm">
                                            <div
                                                class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-danger text-white px-2 py-1"><i
                                                            class="fa fa-hand-holding-usd me-1"></i> KURANG BAYAR</span>
                                                    <span class="fw-bold text-primary fs-6">Pencairan Reimbursement ke
                                                        Karyawan</span>
                                                </div>
                                                <div class="text-end">
                                                    <span class="text-muted small">Nominal Reimbursement:</span>
                                                    <span class="fw-bold text-danger fs-5 ms-1" id="kbNominalText">Rp
                                                        0</span>
                                                </div>
                                            </div>
                                            <p class="small text-muted mb-3">
                                                Total realisasi biaya melebihi uang muka yang diterima (atau tanpa uang
                                                muka). Perusahaan akan mentransfer pencairan dana reimbursement ke rekening
                                                karyawan pemohon berikut:
                                            </p>

                                            <div class="employee-bank-box shadow-sm mb-3">
                                                <div class="row align-items-center g-3">
                                                    <div class="col-md-7 col-12">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <span class="badge bg-primary text-white"
                                                                id="empBankBadge">Bank Karyawan</span>
                                                            <span class="badge bg-success text-white"
                                                                id="empBankVerifiedBadge">
                                                                <i class="fa fa-check-circle me-1"></i> Rekening Terdaftar
                                                            </span>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-3 my-2">
                                                            <div class="bank-acc-no text-primary"
                                                                id="empBankAccNoDisplay">-</div>
                                                            <button type="button"
                                                                class="btn btn-outline-primary btn-copy-acc"
                                                                id="btnCopyEmpAcc"
                                                                onclick="salinRekeningKaryawan(this, event);"
                                                                title="Salin nomor rekening">
                                                                <i class="fa fa-copy"></i> <span>Salin</span>
                                                            </button>
                                                        </div>
                                                        <div class="small fw-semibold text-dark">
                                                            <i class="fa fa-user me-1 text-primary"></i> Atas Nama: <span
                                                                id="empBankAccNameDisplay">-</span>
                                                        </div>
                                                        <div class="small text-muted" id="empBankBankNameDisplay">
                                                            <i class="fa fa-university me-1 text-muted"></i> Bank: -
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 col-12 text-md-end">
                                                        <div class="form-check form-switch d-inline-block text-start">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="chkCustomEmpBank"
                                                                onchange="toggleCustomBankInput(this);">
                                                            <label
                                                                class="form-check-label small fw-semibold text-secondary"
                                                                for="chkCustomEmpBank">
                                                                Gunakan rekening lain
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Form input alternatif jika rekening belum terdaftar / ingin ubah -->
                                            <div id="customEmpBankForm" class="p-3 border rounded-3 bg-light mb-2"
                                                style="display: none;">
                                                <h6 class="fw-bold small text-dark mb-2"><i
                                                        class="fa fa-edit me-1 text-primary"></i> Masukkan Detail Rekening
                                                    Alternatif:</h6>
                                                <div class="row g-2">
                                                    <div class="col-md-4 col-12">
                                                        <input type="text" id="custom_bank_name"
                                                            class="form-control form-control-sm"
                                                            placeholder="Nama Bank (misal: BCA / Mandiri)"
                                                            onkeyup="updateBankFromCustom();">
                                                    </div>
                                                    <div class="col-md-4 col-12">
                                                        <input type="text" id="custom_acc_no"
                                                            class="form-control form-control-sm"
                                                            placeholder="Nomor Rekening"
                                                            onkeyup="updateBankFromCustom();">
                                                    </div>
                                                    <div class="col-md-4 col-12">
                                                        <input type="text" id="custom_acc_name"
                                                            class="form-control form-control-sm"
                                                            placeholder="Atas Nama Pemilik Rekening"
                                                            onkeyup="updateBankFromCustom();">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Case 3: Pas (Sesuai Nilai Advance) -->
                                    <div id="panelPas" style="display: none;">
                                        <div class="settlement-status-banner border border-info bg-white shadow-sm">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="p-3 bg-light rounded-circle text-info border">
                                                    <i class="fa fa-balance-scale fa-2x"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold text-dark mb-1">
                                                        <i class="fa fa-check-circle text-info me-1"></i> Biaya Realisasi
                                                        Pas Sesuai Advance (Nihil Selisih)
                                                    </h6>
                                                    <p class="small text-muted mb-0">
                                                        Total realisasi pengeluaran sama persis dengan dana uang muka yang
                                                        diterima (Rp 0 selisih). Tidak ada transaksi pengembalian ke kantor
                                                        maupun reimbursement ke rekening karyawan.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Case 4: Netral / Belum ada kalkulasi -->
                                    <div id="panelNeutral">
                                        <div class="p-3 border rounded-2 bg-light text-center text-muted small">
                                            <i class="fa fa-coins me-1"></i> Silakan lengkapi rincian penggunaan biaya atau
                                            isi nominal Advance untuk melihat instruksi rekening secara otomatis.
                                        </div>
                                    </div>
                                </div>

                                <!-- Field Sinkronisasi Bank Terpilih - Modern Card -->
                                <div class="col-md-12 col-12 mt-2">
                                    <div class="confirmed-bank-card shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa fa-check-circle text-success fs-5"></i>
                                                <div>
                                                    <span class="fw-bold text-dark fs-6">
                                                        Keterangan Rekening Bank Terkonfirmasi
                                                    </span>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                                        Tercantum resmi pada lembar pengajuan settlement untuk instruksi
                                                        transfer
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="badge bg-secondary text-white px-2 py-1" id="bankTypeBadge"
                                                style="font-size: 0.75rem;">
                                                Otomatis
                                            </span>
                                        </div>

                                        <div id="bankDisplayBox"
                                            class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2 my-2">
                                            <div class="d-flex align-items-center gap-3 text-truncate">
                                                <div class="p-2 rounded-circle bg-white text-primary border shadow-xs d-flex align-items-center justify-content-center"
                                                    style="width: 40px; height: 40px;">
                                                    <i class="fa fa-university fs-6"></i>
                                                </div>
                                                <div class="text-truncate">
                                                    <div class="fw-bold text-dark font-monospace text-truncate"
                                                        id="bankDisplayCurrent" style="font-size: 0.95rem;">
                                                        (Belum ada rekening terpilih - lengkapi rincian biaya / advance di
                                                        atas)
                                                    </div>
                                                    <small class="text-muted" id="bankDisplayHint"
                                                        style="font-size: 0.74rem;">
                                                        <i class="fa fa-sync-alt me-1 text-primary"></i> Tersinkronisasi
                                                        otomatis dengan status Lebih Bayar / Kurang Bayar
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-outline-secondary btn-copy-acc"
                                                    id="btnCopyBankCurrent" onclick="salinBankTerkonfirmasi(this, event);"
                                                    title="Salin teks rekening">
                                                    <i class="fa fa-copy"></i> <span>Salin</span>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-outline-primary btn-sm rounded-2 px-2 py-1"
                                                    id="btnEditBankManual" onclick="toggleEditBankManual();"
                                                    title="Sesuaikan teks rekening secara manual"
                                                    style="font-size: 0.76rem;">
                                                    <i class="fa fa-pen me-1"></i> Edit Manual
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Mode Edit Manual Input -->
                                        <div id="bankManualEditForm" class="p-3 border rounded-3 bg-white mt-2"
                                            style="display: none;">
                                            <label class="form-label-custom small mb-1 text-primary">
                                                <i class="fa fa-edit me-1"></i> Ubah Teks Rekening Manual untuk Dokumen:
                                            </label>
                                            <div class="input-group">
                                                <input type="text" id="bankManualInput" class="form-control"
                                                    placeholder="Contoh: BCA 1234567890 a.n. Nama Akun" />
                                                <button type="button" class="btn btn-success"
                                                    onclick="simpanBankManual();">
                                                    <i class="fa fa-check me-1"></i> Terapkan
                                                </button>
                                                <button type="button" class="btn btn-light border"
                                                    onclick="batalBankManual();">
                                                    Batal
                                                </button>
                                            </div>
                                            <small class="text-muted mt-1 d-block" style="font-size: 0.74rem;">
                                                Perubahan manual akan mengunci nilai rekening settlement dan mengabaikan
                                                auto-fill kartu sampai tombol reset ditekan.
                                            </small>
                                        </div>

                                        <!-- Hidden input for Form Submission compatibility -->
                                        <input type="hidden" id="Bank" name="Bank" />
                                    </div>
                                </div>

                                <!-- Berkas Bukti / Invoice / Struk Pengeluaran - Modern Dropzone & Preview -->
                                <div class="col-md-12 col-12 mt-3">
                                    <label class="form-label-custom d-flex align-items-center gap-2 mb-2">
                                        <i class="fa fa-receipt text-primary"></i> Berkas Bukti / Invoice / Struk
                                        Pengeluaran <span class="text-danger">*</span>
                                    </label>

                                    <!-- Hidden native file input -->
                                    <input type="file" id="Invoice_File" name="Invoice_File"
                                        accept=".pdf,.jpg,.jpeg,.png" style="display: none;"
                                        onchange="handleInvoiceFileSelect(this);" required />

                                    <!-- Interactive Upload Dropzone -->
                                    <div id="invoiceDropzone" class="invoice-upload-dropzone shadow-sm"
                                        onclick="$('#Invoice_File').trigger('click');">
                                        <div class="py-2">
                                            <div class="p-3 rounded-circle bg-light d-inline-flex align-items-center justify-content-center text-primary mb-2 shadow-xs"
                                                style="width: 58px; height: 58px;">
                                                <i class="fa fa-cloud-upload-alt fa-2x"></i>
                                            </div>
                                            <div class="fw-bold text-dark fs-6 mb-1">
                                                Klik atau Seret Berkas Nota / Kwitansi / Struk ke Sini
                                            </div>
                                            <div class="text-muted small">
                                                Mendukung format: <strong class="text-dark">PDF, JPG, JPEG, PNG</strong>
                                                (Maksimal ukuran 5MB)
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Interactive File Selected Preview Box -->
                                    <div id="invoiceFilePreview" class="file-selected-box shadow-sm mt-2"
                                        style="display: none;">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-3 text-truncate">
                                                <div class="p-2 rounded-3 bg-white text-success border d-flex align-items-center justify-content-center shadow-xs"
                                                    style="width: 44px; height: 44px;">
                                                    <i id="invoiceFileIcon" class="fa fa-file-pdf fs-4 text-danger"></i>
                                                </div>
                                                <div class="text-truncate">
                                                    <div id="invoiceFileName" class="fw-bold text-dark text-truncate"
                                                        style="max-width: 380px;">-</div>
                                                    <small id="invoiceFileSize" class="text-muted font-monospace">0
                                                        KB</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-success text-white px-2 py-1"><i
                                                        class="fa fa-check me-1"></i> Berkas Siap Diunggah</span>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="$('#Invoice_File').trigger('click');">
                                                    <i class="fa fa-redo me-1"></i> Ganti
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="clearInvoiceFile();" title="Hapus berkas terpilih">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dynamic Hint Banner -->
                                    <div id="fileUploadHint" class="mt-2 small text-muted">
                                        <i class="fa fa-info-circle me-1"></i> Format berkas yang didukung: <strong>PDF,
                                            JPG, JPEG, PNG</strong> (Ukuran maksimal 5MB). Lampirkan rekapan atau kwitansi
                                        resmi.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4">
                        <a href="{{ url('hrd-settlement-index') }}" class="btn btn-secondary settlement-btn">
                            <i class="fa fa-times"></i> Batal
                        </a>
                        <button type="button" class="btn btn-success settlement-btn" id="submitBtn">
                            <i class="fa fa-paper-plane"></i> Submit Request Settlement
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @php
        $sppdMap = collect($sppdList ?? [])->mapWithKeys(function ($s) use ($sppdCosts, $sppdMatrixCosts) {
            $matrixCost = (float) ($sppdMatrixCosts[$s->ID] ?? 0);
            return [
                $s->ID => [
                    'no' => $s->SPPD_No,
                    'dept' => $s->Department,
                    'dest' => $s->Destination,
                    'cost' => (float) $s->Estimated_Cost,
                    'matrix_cost' => $matrixCost,
                    'requester_name' => $s->Requester_Name ?? '',
                    'requester_nik' => $s->Requester_NIK ?? '',
                    'requester_bank_name' => $s->Requester_Bank_Name ?? '',
                    'requester_bank_acc_no' => $s->Requester_Bank_Account_No ?? '',
                    'requester_bank_acc_name' => $s->Requester_Bank_Account_Name ?? '',
                    'items' => $sppdCosts[$s->ID] ?? [],
                ],
            ];
        });
    @endphp

    <script>
        var SPPD_MAP = {!! json_encode($sppdMap) !!};
        var USER_BANKS_MAP = {!! json_encode($userBanksMap ?? []) !!};
        var currentMatrixAllowance = 0;

        var defaultUserBank = {
            bank_name: "{{ $user->Bank_Name ?? '' }}",
            account_no: "{{ $user->Bank_Account_No ?? '' }}",
            account_name: "{{ $user->Bank_Account_Name ?: ($user->Name ?: $user->username) }}"
        };
        var currentEmployeeBank = Object.assign({}, defaultUserBank);
        var isManualBankEdit = false;

        $(function() {
            $('#SPPD_ID').select2({
                width: '100%',
                placeholder: '-- Tanpa Referensi SPPD --',
                allowClear: true
            });
            $('#Department').select2({
                width: '100%',
                placeholder: '-- Pilih Departemen --',
                allowClear: true
            });

            updateEmployeeBankCardUI();

            $('#SPPD_ID').on('change', function() {
                var s = SPPD_MAP[$(this).val()];
                if (s) {
                    currentMatrixAllowance = Number(s.matrix_cost || 0);
                    if (s.dept) {
                        $('#Department').val(s.dept).trigger('change');
                    }
                    if (s.cost) {
                        $('#Advance_Nominal').val(s.cost);
                    }
                    if (s.requester_name) {
                        $('#Requester_Name').val(s.requester_name);
                    }
                    if (s.requester_nik) {
                        $('#Requester_NIK').val(s.requester_nik);
                    }

                    if (s.requester_bank_acc_no) {
                        currentEmployeeBank = {
                            bank_name: s.requester_bank_name || 'Bank',
                            account_no: s.requester_bank_acc_no,
                            account_name: s.requester_bank_acc_name || s.requester_name
                        };
                    } else {
                        var u = USER_BANKS_MAP[s.requester_nik];
                        if (u && u.Bank_Account_No) {
                            currentEmployeeBank = {
                                bank_name: u.Bank_Name || 'Bank',
                                account_no: u.Bank_Account_No,
                                account_name: u.Bank_Account_Name || u.Name
                            };
                        } else {
                            currentEmployeeBank = {
                                bank_name: '',
                                account_no: '',
                                account_name: s.requester_name || ''
                            };
                        }
                    }

                    var infoHtml = '<i class="fa fa-info-circle me-1"></i> Ref SPPD: ' + s.no +
                        ' | Pemohon: ' + (s.requester_name ? (s.requester_name + ' (' + s.requester_nik +
                            ')') : '-') +
                        ' | Tujuan: ' + (s.dest || '-') + ' | Estimasi Biaya SPPD: Rp ' + Number(s.cost ||
                            0).toLocaleString('id-ID');

                    if (currentMatrixAllowance > 0) {
                        infoHtml +=
                            ' | <span class="badge bg-primary text-white ms-1">Tunjangan Matriks: Rp ' +
                            currentMatrixAllowance.toLocaleString('id-ID') + '</span>';
                    }

                    if (s.items && s.items.length > 0) {
                        infoHtml +=
                            ' <span class="badge bg-success text-white ms-1"><i class="fa fa-check-circle me-1"></i>' +
                            s.items.length + ' item manual dimuat</span>';
                        populateExpensesFromSppd(s.items);
                    } else {
                        infoHtml +=
                            ' <span class="badge bg-info text-white ms-1"><i class="fa fa-info-circle me-1"></i>Biaya otomatis matriks SPPD tidak ditarik ke rincian</span>';
                        resetExpensesToSingleEmptyRow();
                    }
                    $('#sppdInfo').html(infoHtml);
                } else {
                    currentMatrixAllowance = 0;
                    $('#sppdInfo').text('');
                    currentEmployeeBank = Object.assign({}, defaultUserBank);
                    resetExpensesToSingleEmptyRow();
                }

                updateEmployeeBankCardUI();
                hitungTotal();
            });
        });

        $('#Requester_NIK').on('blur change', function() {
            var nik = $.trim($(this).val());
            if (nik && USER_BANKS_MAP[nik] && USER_BANKS_MAP[nik].Bank_Account_No) {
                var u = USER_BANKS_MAP[nik];
                currentEmployeeBank = {
                    bank_name: u.Bank_Name || 'Bank',
                    account_no: u.Bank_Account_No,
                    account_name: u.Bank_Account_Name || u.Name
                };
                updateEmployeeBankCardUI();
                hitungTotal();
            }
        });

        // Drag & Drop listener for Invoice Dropzone
        var $dropzone = $('#invoiceDropzone');
        $dropzone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });
        $dropzone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });
        $dropzone.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            if (files && files.length > 0) {
                $('#Invoice_File')[0].files = files;
                handleInvoiceFileSelect($('#Invoice_File')[0]);
            }
        });

        hitungTotal();

        function salinKeClipboard(text, btn, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            if (!text) return;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    feedbackSalin(btn);
                }).catch(function() {
                    fallbackCopyText(text, btn);
                });
            } else {
                fallbackCopyText(text, btn);
            }
        }

        function fallbackCopyText(text, btn) {
            var tempInput = document.createElement("input");
            tempInput.style.position = "absolute";
            tempInput.style.left = "-9999px";
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            try {
                document.execCommand("copy");
                feedbackSalin(btn);
            } catch (err) {
                alert("Gagal menyalin nomor rekening: " + text);
            }
            document.body.removeChild(tempInput);
        }

        function feedbackSalin(btn) {
            var $b = $(btn);
            var originalHtml = $b.html();
            $b.html('<i class="fa fa-check text-success"></i> <span>Tersalin!</span>')
                .addClass('btn-success text-white')
                .removeClass('btn-outline-secondary btn-outline-primary');
            setTimeout(function() {
                $b.html(originalHtml)
                    .removeClass('btn-success text-white')
                    .addClass($b.attr('id') === 'btnCopyEmpAcc' ? 'btn-outline-primary' : 'btn-outline-secondary');
            }, 1600);
        }

        function salinRekeningKaryawan(btn, event) {
            var accNo = currentEmployeeBank.account_no || '';
            if ($('#chkCustomEmpBank').is(':checked')) {
                accNo = $.trim($('#custom_acc_no').val());
            }
            salinKeClipboard(accNo, btn, event);
        }

        function setQuickAdvance(val) {
            $('#Advance_Nominal').val(val);
            hitungTotal();
        }

        function salinBankTerkonfirmasi(btn, event) {
            var text = $('#Bank').val() || $('#bankDisplayCurrent').text();
            salinKeClipboard(text, btn, event);
        }

        function toggleEditBankManual() {
            var curr = $('#Bank').val();
            $('#bankManualInput').val(curr);
            $('#bankManualEditForm').slideToggle(150);
        }

        function simpanBankManual() {
            var val = $.trim($('#bankManualInput').val());
            if (val !== '') {
                isManualBankEdit = true;
                $('#Bank').val(val);
                $('#bankDisplayCurrent').text(val);
                $('#bankTypeBadge').text('Manual (Terkunci)').attr('class', 'badge bg-warning text-dark px-2 py-1');
                $('#bankManualEditForm').slideUp(150);
            } else {
                alert('Teks rekening tidak boleh kosong');
            }
        }

        function batalBankManual() {
            $('#bankManualEditForm').slideUp(150);
        }

        function handleInvoiceFileSelect(input) {
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var fileName = file.name;
                var fileSize = (file.size / 1024).toFixed(1) + ' KB';
                if (file.size > 1024 * 1024) {
                    fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                }

                var ext = fileName.split('.').pop().toLowerCase();
                var iconClass = 'fa-file';
                if (ext === 'pdf') iconClass = 'fa-file-pdf text-danger';
                else if (['jpg', 'jpeg', 'png'].indexOf(ext) !== -1) iconClass = 'fa-file-image text-primary';

                $('#invoiceFileIcon').attr('class', 'fa ' + iconClass + ' fs-4');
                $('#invoiceFileName').text(fileName);
                $('#invoiceFileSize').text(fileSize);
                $('#invoiceFilePreview').slideDown(150);
                $('#invoiceDropzone').slideUp(150);
            } else {
                clearInvoiceFile();
            }
        }

        function clearInvoiceFile() {
            $('#Invoice_File').val('');
            $('#invoiceFilePreview').slideUp(150);
            $('#invoiceDropzone').slideDown(150);
        }

        function pilihRekeningKantor(el) {
            $('.company-bank-card').removeClass('selected').find('.badge-status-check').addClass('d-none');
            $(el).addClass('selected').find('.badge-status-check').removeClass('d-none');
            var bankStr = $(el).data('bank-str') || '';
            $('#Bank').val(bankStr);
            $('#bankDisplayCurrent').text(bankStr);
            $('#bankTypeBadge').text('Rekening Kantor (Terpilih)').attr('class', 'badge bg-success text-white');
        }

        function updateEmployeeBankCardUI() {
            var hasAcc = currentEmployeeBank && currentEmployeeBank.account_no && $.trim(currentEmployeeBank.account_no) !==
                '';
            if (hasAcc) {
                $('#empBankBadge').text(currentEmployeeBank.bank_name || 'Bank');
                $('#empBankVerifiedBadge').html('<i class="fa fa-check-circle me-1"></i> Rekening Terdaftar').attr('class',
                    'badge bg-success text-white');
                $('#empBankAccNoDisplay').text(currentEmployeeBank.account_no);
                $('#empBankAccNameDisplay').text(currentEmployeeBank.account_name || $('#Requester_Name').val() || '-');
                $('#empBankBankNameDisplay').html('<i class="fa fa-university me-1 text-muted"></i> Bank: ' + (
                    currentEmployeeBank.bank_name || '-'));
                $('#btnCopyEmpAcc').show();
                if (!$('#chkCustomEmpBank').is(':checked')) {
                    $('#customEmpBankForm').hide();
                }
            } else {
                $('#empBankBadge').text('Belum Terdaftar');
                $('#empBankVerifiedBadge').html('<i class="fa fa-exclamation-triangle me-1"></i> Rekening Belum Tersimpan')
                    .attr('class', 'badge bg-warning text-dark');
                $('#empBankAccNoDisplay').text('(Belum ada nomor rekening)');
                $('#empBankAccNameDisplay').text($('#Requester_Name').val() || '-');
                $('#empBankBankNameDisplay').html(
                    '<i class="fa fa-info-circle me-1 text-warning"></i> Silakan lengkapi rekening pencairan di bawah');
                $('#btnCopyEmpAcc').hide();
                $('#chkCustomEmpBank').prop('checked', true);
                $('#customEmpBankForm').show();
            }
        }

        function toggleCustomBankInput(chk) {
            if ($(chk).is(':checked')) {
                $('#customEmpBankForm').slideDown(150);
                updateBankFromCustom();
            } else {
                $('#customEmpBankForm').slideUp(150);
                syncEmployeeBankToField();
            }
        }

        function updateBankFromCustom() {
            var bName = $.trim($('#custom_bank_name').val());
            var bAcc = $.trim($('#custom_acc_no').val());
            var bHold = $.trim($('#custom_acc_name').val()) || $('#Requester_Name').val() || '-';
            if (bName || bAcc) {
                var str = (bName || 'Bank') + ' - ' + (bAcc || '-') + ' a.n. ' + bHold;
                $('#Bank').val(str);
                $('#bankDisplayCurrent').text(str);
                $('#bankTypeBadge').text('Rekening Karyawan (Manual/Alternatif)').attr('class', 'badge bg-info text-white');
                $('#empBankAccNoDisplay').text(bAcc || '-');
                $('#empBankAccNameDisplay').text(bHold);
                $('#empBankBankNameDisplay').html('<i class="fa fa-university me-1 text-muted"></i> Bank: ' + (bName ||
                    '-'));
            } else {
                syncEmployeeBankToField();
            }
        }

        function syncEmployeeBankToField() {
            if ($('#chkCustomEmpBank').is(':checked')) {
                updateBankFromCustom();
                return;
            }
            var hasAcc = currentEmployeeBank && currentEmployeeBank.account_no && $.trim(currentEmployeeBank.account_no) !==
                '';
            if (hasAcc) {
                var str = currentEmployeeBank.bank_name + ' - ' + currentEmployeeBank.account_no + ' a.n. ' + (
                    currentEmployeeBank.account_name || $('#Requester_Name').val());
                $('#Bank').val(str);
                $('#bankDisplayCurrent').text(str);
                $('#bankTypeBadge').text('Rekening Karyawan (Otomatis)').attr('class', 'badge bg-primary text-white');
            } else {
                var customStr = $.trim($('#custom_bank_name').val()) + ' - ' + $.trim($('#custom_acc_no').val()) +
                    ' a.n. ' + ($.trim($('#custom_acc_name').val()) || $('#Requester_Name').val());
                if ($.trim($('#custom_acc_no').val()) !== '') {
                    $('#Bank').val(customStr);
                    $('#bankDisplayCurrent').text(customStr);
                } else {
                    $('#Bank').val('');
                    $('#bankDisplayCurrent').text('(Belum ada nomor rekening karyawan - isi form alternatif di bawah)');
                }
                $('#bankTypeBadge').text('Rekening Karyawan (Belum Lengkap)').attr('class', 'badge bg-warning text-dark');
            }
        }

        function resetExpensesToSingleEmptyRow() {
            var $tbody = $("#tblExpense tbody");
            $tbody.empty();
            rowExpense = 0;
            var html = "<tr>" +
                "<td><input type='text' id='Expense_0' class='form-control' placeholder='Contoh: Tiket, Penginapan, Makan' required /></td>" +
                "<td><div class='input-group'><span class='input-group-text bg-light text-muted'>Rp</span><input type='text' id='Cost_0' class='form-control text-end cost-input' placeholder='0' onkeyup='hitungTotal();' required /></div></td>" +
                "<td><input type='text' id='Desc_0' class='form-control' placeholder='Catatan tambahan keperluan pengeluaran' /></td>" +
                "<td class='text-center'><button type='button' class='btn btn-outline-danger btn-sm' onclick='hapusRow(this);' title='Hapus baris'><i class='fa fa-trash'></i></button></td>" +
                "</tr>";
            $tbody.append(html);
            rowExpense = 1;
            hitungTotal();
        }

        function populateExpensesFromSppd(items) {
            var $tbody = $("#tblExpense tbody");
            $tbody.empty();
            rowExpense = 0;

            items.forEach(function(item, idx) {
                var costVal = Number(item.Cost || 0);
                var html = "<tr>" +
                    "<td><input type='text' id='Expense_" + rowExpense +
                    "' class='form-control' value='" + (item.Expense || '') +
                    "' placeholder='Contoh: Tiket, Hotel, BBM' required /></td>" +
                    "<td><div class='input-group'><span class='input-group-text bg-light text-muted'>Rp</span><input type='text' id='Cost_" +
                    rowExpense +
                    "' class='form-control text-end cost-input' value='" + costVal +
                    "' placeholder='0' onkeyup='hitungTotal();' required /></div></td>" +
                    "<td><input type='text' id='Desc_" + rowExpense +
                    "' class='form-control' value='" + (item.Description || '') +
                    "' placeholder='Keterangan keperluan' /></td>" +
                    "<td class='text-center'><button type='button' class='btn btn-outline-danger btn-sm' onclick='hapusRow(this);' title='Hapus baris'><i class='fa fa-trash'></i></button></td>" +
                    "</tr>";
                $tbody.append(html);
                rowExpense++;
            });

            hitungTotal();
        }

        var rowExpense = 1;

        function tambahRow() {
            var html = "<tr>" +
                "<td><input type='text' id='Expense_" + rowExpense +
                "' class='form-control' placeholder='Contoh: Tiket, Hotel, BBM' required /></td>" +
                "<td><div class='input-group'><span class='input-group-text bg-light text-muted'>Rp</span><input type='text' id='Cost_" +
                rowExpense +
                "' class='form-control text-end cost-input' placeholder='0' onkeyup='hitungTotal();' required /></div></td>" +
                "<td><input type='text' id='Desc_" + rowExpense +
                "' class='form-control' placeholder='Keterangan keperluan' /></td>" +
                "<td class='text-center'><button type='button' class='btn btn-outline-danger btn-sm' onclick='hapusRow(this);' title='Hapus baris'><i class='fa fa-trash'></i></button></td>" +
                "</tr>";
            $("#tblExpense tbody").append(html);
            rowExpense++;
        }

        function hapusRow(btn) {
            if ($("#tblExpense tbody tr").length <= 1) {
                alert("Minimal harus ada satu baris rincian biaya!");
                return;
            }
            $(btn).closest("tr").remove();
            hitungTotal();
        }

        function hitungTotal() {
            var totalReal = 0;
            $("[id^='Cost_']").each(function() {
                var raw = $(this).val().replace(/\./g, "").replace(/,/g, ".");
                var v = parseFloat(raw);
                if (!isNaN(v)) totalReal += v;
            });

            var matrixVal = currentMatrixAllowance || 0;
            var totalDiperhitungkan = totalReal + matrixVal;

            $("#TotalManualCost").text(totalReal.toLocaleString("id-ID"));
            $("#TotalCost").text(totalDiperhitungkan.toLocaleString("id-ID"));

            if (matrixVal > 0) {
                $("#MatrixAllowanceBox").show();
                $("#MatrixAllowanceNominal").text(matrixVal.toLocaleString("id-ID"));
            } else {
                $("#MatrixAllowanceBox").hide();
            }

            // Kalkulasi selisih dengan advance
            var advanceRaw = $("#Advance_Nominal").val().replace(/\./g, "").replace(/,/g, ".");
            var advanceVal = parseFloat(advanceRaw) || 0;
            var selisih = totalDiperhitungkan - advanceVal;

            if (totalDiperhitungkan === 0 && advanceVal === 0) {
                $('#panelLebihBayar').hide();
                $('#panelKurangBayar').hide();
                $('#panelPas').hide();
                $('#panelNeutral').show();
                $('#settlementDirectionBox').removeClass('border-success border-danger border-info').addClass('bg-light');
                $('#dirIcon').attr('class', 'fa fa-info-circle text-muted fa-lg');
                $('#dirTitle').text('Belum ada perhitungan');
                $('#dirDesc').text('Masukkan rincian biaya atau nominal advance');
                $('#dirBadge').text('-').attr('class', 'h6 mb-0 fw-bold text-secondary');
                $('#SelisihBox').hide();
                $('#fileUploadHint').html(
                    '<i class="fa fa-info-circle me-1"></i> Format berkas yang didukung: <strong>PDF, JPG, JPEG, PNG</strong> (Ukuran maksimal 5MB). Lampirkan rekapan atau kwitansi resmi.'
                );
                if (!isManualBankEdit) {
                    $('#Bank').val('');
                    $('#bankDisplayCurrent').text(
                        '(Belum ada rekening terpilih - lengkapi rincian biaya / advance di atas)');
                    $('#bankTypeBadge').text('Belum Terpilih').attr('class', 'badge bg-light text-muted border px-2 py-1');
                }
                $('#settlementHeaderBadge').html('<i class="fa fa-calculator me-1"></i> Menunggu Input').attr('class',
                    'badge bg-light text-muted border px-2 py-1');
            } else if (advanceVal > 0 && selisih < 0) {
                // LEBIH BAYAR (Pengembalian ke Perusahaan)
                var lbNominal = Math.abs(selisih);
                $('#panelLebihBayar').slideDown(180);
                $('#panelKurangBayar').hide();
                $('#panelPas').hide();
                $('#panelNeutral').hide();

                $('#lbNominalText').text('Rp ' + lbNominal.toLocaleString('id-ID'));
                $('#SelisihBox').show();
                $('#SelisihLabel').text('Lebih Bayar (Pengembalian ke Perusahaan):');
                $('#SelisihNominal').html('<span class="text-success">Rp ' + lbNominal.toLocaleString('id-ID') + '</span>');

                $('#settlementDirectionBox').removeClass('border-danger border-info bg-light').addClass(
                    'border-success bg-white');
                $('#dirIcon').attr('class', 'fa fa-undo-alt text-success fa-lg');
                $('#dirTitle').text('Lebih Bayar (Pengembalian ke Kantor)');
                $('#dirDesc').text('Karyawan wajib mengembalikan kelebihan dana');
                $('#dirBadge').text('Rp ' + lbNominal.toLocaleString('id-ID')).attr('class',
                    'h6 mb-0 fw-bold text-success');

                $('#fileUploadHint').html(
                    '<span class="badge bg-success text-white me-1"><i class="fa fa-info-circle me-1"></i> Wajib Bukti Pengembalian</span> Lampirkan <strong>struk pengeluaran beserta bukti transfer pengembalian sisa dana ke rekening kantor</strong>.'
                );

                // Auto-select company bank if not yet selected
                if (!isManualBankEdit) {
                    var $selectedCompanyBank = $('.company-bank-card.selected');
                    if ($selectedCompanyBank.length === 0) {
                        $selectedCompanyBank = $('.company-bank-card').first();
                        $selectedCompanyBank.addClass('selected').find('.badge-status-check').removeClass('d-none');
                    }
                    var companyBankStr = $selectedCompanyBank.data('bank-str') || '';
                    $('#Bank').val(companyBankStr);
                    $('#bankDisplayCurrent').text(companyBankStr);
                    $('#bankTypeBadge').text('Rekening Kantor (Lebih Bayar)').attr('class', 'badge bg-success text-white');
                }
                $('#settlementHeaderBadge').html('<i class="fa fa-undo-alt me-1"></i> Mode: Lebih Bayar').attr('class',
                    'badge bg-success text-white px-2 py-1');

            } else if (selisih > 0) {
                // KURANG BAYAR (Reimbursement Karyawan)
                var kbNominal = selisih;
                $('#panelKurangBayar').slideDown(180);
                $('#panelLebihBayar').hide();
                $('#panelPas').hide();
                $('#panelNeutral').hide();

                $('#kbNominalText').text('Rp ' + kbNominal.toLocaleString('id-ID'));
                $('#SelisihBox').show();
                $('#SelisihLabel').text(advanceVal > 0 ? 'Kurang Bayar (Reimbursement Karyawan):' :
                    'Reimbursement Karyawan (Tanpa Advance):');
                $('#SelisihNominal').html('<span class="text-danger">Rp ' + kbNominal.toLocaleString('id-ID') + '</span>');

                $('#settlementDirectionBox').removeClass('border-success border-info bg-light').addClass(
                    'border-danger bg-white');
                $('#dirIcon').attr('class', 'fa fa-hand-holding-usd text-danger fa-lg');
                $('#dirTitle').text('Kurang Bayar (Reimbursement)');
                $('#dirDesc').text('Perusahaan akan mentransfer kekurangan dana ke rekening karyawan');
                $('#dirBadge').text('Rp ' + kbNominal.toLocaleString('id-ID')).attr('class', 'h6 mb-0 fw-bold text-danger');

                $('#fileUploadHint').html(
                    '<span class="badge bg-primary text-white me-1"><i class="fa fa-file-invoice me-1"></i> Struk Pengeluaran</span> Lampirkan berkas invoice/struk pengeluaran asli yang sah.'
                );

                if (!isManualBankEdit) {
                    syncEmployeeBankToField();
                }
                $('#settlementHeaderBadge').html('<i class="fa fa-wallet me-1"></i> Mode: Reimbursement').attr('class',
                    'badge bg-danger text-white px-2 py-1');

            } else {
                // PAS (Sesuai Advance)
                $('#panelPas').slideDown(180);
                $('#panelLebihBayar').hide();
                $('#panelKurangBayar').hide();
                $('#panelNeutral').hide();

                $('#SelisihBox').show();
                $('#SelisihLabel').text('Status Biaya:');
                $('#SelisihNominal').html('<span class="text-primary">Pas (Sesuai Advance)</span>');

                $('#settlementDirectionBox').removeClass('border-success border-danger bg-light').addClass(
                    'border-info bg-white');
                $('#dirIcon').attr('class', 'fa fa-check-circle text-info fa-lg');
                $('#dirTitle').text('Biaya Sesuai Nilai Advance');
                $('#dirDesc').text('Nihil selisih pengembalian / reimbursement');
                $('#dirBadge').text('Rp 0').attr('class', 'h6 mb-0 fw-bold text-info');

                $('#fileUploadHint').html(
                    '<span class="badge bg-info text-white me-1"><i class="fa fa-file-invoice me-1"></i> Struk Pengeluaran</span> Lampirkan berkas invoice/struk pengeluaran sesuai total advance.'
                );

                if (!isManualBankEdit) {
                    $('#Bank').val('Sesuai Nilai Advance (Nihil Selisih)');
                    $('#bankDisplayCurrent').text('Sesuai Nilai Advance (Nihil Selisih)');
                    $('#bankTypeBadge').text('Nihil Selisih').attr('class', 'badge bg-info text-white');
                }
                $('#settlementHeaderBadge').html('<i class="fa fa-check-circle me-1"></i> Mode: Pas').attr('class',
                    'badge bg-info text-white px-2 py-1');
            }
        }

        $('#submitBtn').click(function(e) {
            e.preventDefault();

            if ($.trim($("#Department").val()) === "") {
                alert("Field Departemen wajib dipilih!");
                $("#Department").focus();
                return;
            }
            if ($.trim($("#Requester_Name").val()) === "") {
                alert("Nama Karyawan Pemohon wajib diisi!");
                $("#Requester_Name").focus();
                return;
            }
            if ($("#Invoice_File")[0].files.length === 0) {
                alert("Berkas Invoice / Kwitansi pengeluaran wajib diupload!");
                $("#Invoice_File").focus();
                return;
            }

            var hasRow = false;
            $("[id^='Cost_']").each(function() {
                var v = parseFloat($(this).val().replace(/\./g, "").replace(/,/g, "."));
                if (!isNaN(v) && v > 0) hasRow = true;
            });
            if (!hasRow) {
                alert("Rincian penggunaan biaya wajib diisi minimal satu baris dengan nilai lebih dari 0!");
                return;
            }

            if ($.trim($("#Bank").val()) === "") {
                alert("Keterangan Rekening Bank wajib diisi atau dipilih!");
                $("#Bank").focus();
                return;
            }

            var formData = new FormData();
            formData.append('STTL_No', $('#STTL_No').val());
            formData.append('Requester_NIK', $('#Requester_NIK').val());
            formData.append('Department', $('#Department').val());
            formData.append('Requester_Name', $('#Requester_Name').val());
            formData.append('SPPD_ID', $('#SPPD_ID').val());
            formData.append('Advance_Nominal', $('#Advance_Nominal').val());
            formData.append('Bank', $('#Bank').val());
            formData.append('Invoice_File', $('#Invoice_File')[0].files[0]);

            $("[id^='Expense_']").each(function() {
                var idx = $(this).attr('id').replace('Expense_', '');
                formData.append('Expense_' + idx, $(this).val());
                formData.append('Cost_' + idx, $('#Cost_' + idx).val());
                formData.append('Desc_' + idx, $('#Desc_' + idx).val());
            });

            var $btn = $('#submitBtn');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                url: 'hrd-settlement-new-submit',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    $btn.prop('disabled', false).html(
                        '<i class="fa fa-paper-plane"></i> Submit Request Settlement');
                    if (data.status === 'success') {
                        alert('Request Settlement Berhasil Dikirim!');
                        location.href = 'hrd-settlement-index';
                    } else {
                        alert('Gagal submit pengajuan: ' + data.message);
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(
                        '<i class="fa fa-paper-plane"></i> Submit Request Settlement');
                    var msg = 'Terjadi kesalahan sistem saat mengirim data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                }
            });
        });
    </script>
@endsection
