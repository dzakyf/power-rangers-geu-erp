@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    @csrf

    <style>
        .rmb-page-wrapper {
            margin-bottom: 2.5rem;
        }

        .rmb-hero-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            border-radius: 14px;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 4px 14px rgba(30, 58, 138, 0.15);
            color: #fff;
            margin-top: 1rem;
            margin-bottom: 1.5rem;
        }

        .rmb-hero-header h2 {
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .rmb-hero-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .rmb-breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
        }

        .rmb-breadcrumb .breadcrumb-item,
        .rmb-breadcrumb .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
        }

        .rmb-breadcrumb .breadcrumb-item.active {
            color: #fff;
            font-weight: 600;
        }

        .rmb-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .rmb-section-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .rmb-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.9rem 1.25rem;
            font-weight: 700;
            font-size: 0.95rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rmb-btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.55rem 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
        }

        .rmb-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .form-label-custom {
            font-size: 0.825rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.35rem;
        }

        .table-expense thead th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            vertical-align: middle;
        }

        .total-summary-card {
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 1.1rem 1.25rem;
            border-radius: 8px;
        }

        /* Interactive Bank & Upload Section */
        .employee-bank-box {
            border: 1.5px solid #3b82f6;
            border-radius: 12px;
            background: #eff6ff;
            padding: 1.15rem 1.35rem;
            transition: all 0.25s ease;
        }
        .bank-badge {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
        }
        .bank-acc-no {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 1.2rem;
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
        .file-selected-card {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            border-radius: 8px;
            padding: 0.65rem 0.9rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
    </style>

    <div class="rmb-page-wrapper">
        <!-- Hero Header -->
        <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
            <div>
                <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
                    <i class="fa fa-receipt text-white me-2"></i> New Reimbursement
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                        <li class="breadcrumb-item"><a href="#">Kompensasi & Benefit</a></li>
                        <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Reimbursement
                            Management</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Main Form Container -->
        <div class="card rmb-card">
            <div class="card-body p-4">

                <!-- Section 1: Informasi Pengaju & Reimbursement -->
                <div class="rmb-section-card shadow-sm">
                    <div class="rmb-section-header">
                        <i class="fa fa-id-card text-primary"></i> 1. Informasi Dokumen & Pemohon
                    </div>
                    <div class="p-3 p-md-4">
                        <div class="row g-3">
                            <div class="col-md-6 col-12">
                                <label for="Reimbursement_No" class="form-label-custom">No Reimbursement (Otomatis)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa fa-barcode"></i></span>
                                    <input type="text" id="Reimbursement_No"
                                        class="form-control bg-light fw-bold text-primary" name="Reimbursement_No"
                                        value="{{ $Reimbursement_No }}" readonly />
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="User_Responsibility_ID" class="form-label-custom">
                                    NIK Pemohon
                                    @if (!empty($canChooseEmployee))
                                        <span class="badge bg-primary text-white ms-1"><i class="fa fa-user-check me-1"></i>Pilih Karyawan</span>
                                    @endif
                                    <span class="text-danger">*</span>
                                </label>
                                @if (!empty($canChooseEmployee))
                                    <select id="User_Responsibility_ID" class="form-control select2" name="User_Responsibility_ID" style="width:100%;">
                                        <option value="">-- Pilih NIK / Karyawan Pemohon --</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->NIK }}"
                                                data-name="{{ $emp->Name }}"
                                                data-dept="{{ $emp->Department }}"
                                                data-position="{{ $emp->Position }}"
                                                data-bank="{{ $emp->Payment_Reference }}"
                                                data-acc="{{ $emp->Payment_Reference2 }}"
                                                data-an="{{ $emp->Payment_Reference3 }}"
                                                data-branch="{{ $emp->Payment_Reference_Branch }}"
                                                {{ ($user->Emp_ID == $emp->NIK) ? 'selected' : '' }}>
                                                {{ $emp->NIK }} - {{ $emp->Name }} ({{ $emp->Department }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted"><i class="fa fa-info-circle me-1"></i>Anda memiliki hak akses untuk memilih karyawan pengaju reimbursement.</small>
                                @else
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i class="fa fa-id-badge"></i></span>
                                        <input type="text" id="User_Responsibility_ID" class="form-control bg-light"
                                            name="User_Responsibility_ID" value="{{ $user->Emp_ID ?: '' }}" readonly />
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="Department" class="form-label-custom">Departemen <span
                                        class="text-danger">*</span></label>
                                <select id="Department" class="form-control select2" name="Department" style="width:100%;">
                                    <option value="">-- Pilih Departemen --</option>
                                    @foreach ($departments as $d)
                                        <option value="{{ $d->Initial }}">{{ $d->Department }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="User_Responsibility_Name" class="form-label-custom">Nama Karyawan Pengaju <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa fa-user"></i></span>
                                    <input type="text" id="User_Responsibility_Name" class="form-control"
                                        name="User_Responsibility_Name" value="{{ $user->Name ?: $user->username }}" />
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="Reference_No" class="form-label-custom">No Referensi / SPPD / STTL
                                    (Opsional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i
                                            class="fa fa-paperclip"></i></span>
                                    <input type="text" id="Reference_No" class="form-control" name="Reference_No"
                                        placeholder="Contoh: SPPD/GA/2603/001 atau STTL/..." />
                                </div>
                                <small class="text-muted">Isi nomor SPPD / Settlement jika reimbursement ini terhubung
                                    dengan dinas luar.</small>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="Reimbursement_Date" class="form-label-custom">Tanggal Reimbursement <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i
                                            class="fa fa-calendar-alt"></i></span>
                                    <input type="text" id="Reimbursement_Date" class="form-control"
                                        name="Reimbursement_Date" placeholder="YYYY-MM-DD" value="{{ date('Y-m-d') }}" />
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <label for="Attachment_File" class="form-label-custom">
                                    <i class="fa fa-receipt text-primary me-1"></i> Upload Berkas / Bukti Nota / Kwitansi <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fa fa-cloud-upload-alt"></i></span>
                                    <input type="file" id="Attachment_File" class="form-control" name="Attachment_File" accept=".pdf,.jpg,.jpeg,.png" onchange="handleFileSelect(this);" />
                                </div>
                                <div id="filePreviewBox" class="mt-2" style="display: none;">
                                    <div class="file-selected-card shadow-sm">
                                        <div class="d-flex align-items-center gap-2 text-truncate">
                                            <i id="filePreviewIcon" class="fa fa-file text-success fs-5"></i>
                                            <div>
                                                <div id="filePreviewName" class="fw-bold small text-dark text-truncate" style="max-width: 280px;">-</div>
                                                <small id="filePreviewSize" class="text-muted">0 KB</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success text-white px-2 py-1"><i class="fa fa-check me-1"></i> Siap Diupload</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2" onclick="clearSelectedFile();" title="Hapus berkas terpilih">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1"><i class="fa fa-info-circle me-1"></i> Format berkas yang didukung: <strong>PDF, JPG, JPEG, PNG</strong> (Maksimal 5MB).</small>
                            </div>

                            <div class="col-12">
                                <label for="Description" class="form-label-custom">Keterangan / Keperluan Reimbursement
                                    <span class="text-danger">*</span></label>
                                <textarea id="Description" class="form-control" name="Description" rows="3"
                                    placeholder="Tuliskan tujuan dan penjelasan reimbursement secara lengkap..."></textarea>
                            </div>

                            <!-- Informasi Rekening Bank Pemohon (Master Employment / Payroll) - Interaktif -->
                            <div class="col-12 mt-3">
                                <div class="card border rounded-3 p-3 shadow-sm bg-white" style="border-color: #cbd5e1 !important;">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa fa-university text-primary fs-5"></i>
                                            <div>
                                                <span class="fw-bold text-dark fs-6">
                                                    Rekening Bank Pencairan Reimbursement
                                                </span>
                                                <div class="text-muted small" style="font-size: 0.76rem;">
                                                    Terintegrasi dengan data master payroll &amp; perbankan karyawan
                                                </div>
                                            </div>
                                        </div>
                                        <span id="bankBcaBadge" class="badge bg-primary text-white px-2 py-1 fs-7">
                                            {{ !empty($defaultBank['is_bca']) ? 'BCA' : (!empty($defaultBank['bank_name']) ? $defaultBank['bank_name'] : 'BELUM TERDAFTAR') }}
                                        </span>
                                    </div>

                                    <!-- Interactive Verified Employee Bank Box -->
                                    <div class="employee-bank-box shadow-sm mb-2">
                                        <div class="row align-items-center g-3">
                                            <div class="col-md-7 col-12">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <span class="badge bg-primary text-white" id="empBankBadge">
                                                        {{ $defaultBank['bank_name'] ?: 'Bank Karyawan' }}
                                                    </span>
                                                    <span class="badge {{ !empty($defaultBank['bank_acc']) ? 'bg-success text-white' : 'bg-warning text-dark' }}" id="bankVerifiedBadge">
                                                        <i class="fa {{ !empty($defaultBank['bank_acc']) ? 'fa-check-circle' : 'fa-exclamation-triangle' }} me-1"></i>
                                                        {{ !empty($defaultBank['bank_acc']) ? 'Rekening Terdaftar' : 'Belum Terdaftar' }}
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center gap-3 my-2">
                                                    <div class="bank-acc-no text-primary" id="empBankAccNoDisplay">
                                                        {{ $defaultBank['bank_acc'] ?: '(Belum ada nomor rekening)' }}
                                                    </div>
                                                    <button type="button" class="btn btn-outline-primary btn-copy-acc" id="btnCopyEmpAcc"
                                                        onclick="salinRekeningPemohon(this, event);"
                                                        title="Salin nomor rekening"
                                                        style="{{ empty($defaultBank['bank_acc']) ? 'display: none;' : '' }}">
                                                        <i class="fa fa-copy"></i> <span>Salin</span>
                                                    </button>
                                                </div>
                                                <div class="small fw-semibold text-dark">
                                                    <i class="fa fa-user me-1 text-primary"></i> Atas Nama: <span id="empBankAccNameDisplay">{{ $defaultBank['bank_an'] ?: '-' }}</span>
                                                </div>
                                                <div class="small text-muted" id="empBankBranchDisplay">
                                                    <i class="fa fa-map-marker-alt me-1 text-muted"></i> Cabang: <span>{{ $defaultBank['bank_branch'] ?: '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-5 col-12 text-md-end">
                                                <div class="form-check form-switch d-inline-block text-start">
                                                    <input class="form-check-input" type="checkbox" id="chkCustomEmpBank" onchange="toggleCustomBankInput(this);">
                                                    <label class="form-check-label small fw-semibold text-secondary" for="chkCustomEmpBank">
                                                        Gunakan rekening alternatif lain
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Form input alternatif jika rekening belum terdaftar / ingin ubah -->
                                    <div id="customEmpBankForm" class="p-3 border rounded-3 bg-light mt-2" style="display: none;">
                                        <h6 class="fw-bold small text-dark mb-2">
                                            <i class="fa fa-edit me-1 text-primary"></i> Masukkan Detail Rekening Alternatif Pencairan:
                                        </h6>
                                        <div class="row g-2">
                                            <div class="col-md-3 col-sm-6">
                                                <label class="form-label-custom small mb-1">Nama Bank</label>
                                                <input type="text" id="custom_bank_name" class="form-control form-control-sm"
                                                    placeholder="Contoh: BCA / Mandiri / BRI" onkeyup="updateBankFromCustom();" />
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <label class="form-label-custom small mb-1">No. Rekening</label>
                                                <input type="text" id="custom_acc_no" class="form-control form-control-sm font-monospace fw-bold"
                                                    placeholder="Nomor rekening transfer" onkeyup="updateBankFromCustom();" />
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <label class="form-label-custom small mb-1">Atas Nama Rekening</label>
                                                <input type="text" id="custom_acc_name" class="form-control form-control-sm"
                                                    placeholder="Nama pemilik rekening" onkeyup="updateBankFromCustom();" />
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <label class="form-label-custom small mb-1">Cabang Bank</label>
                                                <input type="text" id="custom_acc_branch" class="form-control form-control-sm"
                                                    placeholder="Cabang bank (opsional)" onkeyup="updateBankFromCustom();" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hidden Inputs untuk Menjaga Kompatibilitas Backend / Form Submission -->
                                    <input type="hidden" id="Bank_Name" name="Bank_Name" value="{{ $defaultBank['bank_name'] ?? '' }}" />
                                    <input type="hidden" id="Bank_Account_No" name="Bank_Account_No" value="{{ $defaultBank['bank_acc'] ?? '' }}" />
                                    <input type="hidden" id="Bank_Account_Name" name="Bank_Account_Name" value="{{ $defaultBank['bank_an'] ?? '' }}" />
                                    <input type="hidden" id="Bank_Branch" name="Bank_Branch" value="{{ $defaultBank['bank_branch'] ?? '' }}" />

                                    <small class="text-muted mt-2 d-block">
                                        <i class="fa fa-info-circle me-1 text-primary"></i> Dana reimbursement akan ditransfer langsung oleh Bagian Keuangan/Finance ke nomor rekening yang tertera di atas.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Rincian Biaya -->
                <div class="rmb-section-card shadow-sm">
                    <div class="rmb-section-header justify-content-between flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa fa-list-ol text-primary"></i> 2. Rincian Pengeluaran / Biaya
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rmb-btn" onclick="tambahRow();">
                            <i class="fa fa-plus"></i> Tambah Baris
                        </button>
                    </div>
                    <div class="p-3 p-md-4">
                        <div class="table-responsive">
                            <table id="tblRincian"
                                class="table table-hover table-bordered table-expense align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="24%">No Bukti / Reference</th>
                                        <th width="44%">Deskripsi / Keterangan Pengeluaran</th>
                                        <th width="24%">Jumlah Nominal (Rp)</th>
                                        <th width="8%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" id="Ref_0" class="form-control form-control-sm"
                                                placeholder="No Struk / Nota / Kwitansi" />
                                        </td>
                                        <td>
                                            <input type="text" id="Remark_0" class="form-control form-control-sm"
                                                placeholder="Keterangan pengeluaran" />
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" id="Amount_0"
                                                    class="form-control text-end fw-semibold amount-input" placeholder="0"
                                                    onkeyup="formatAmountInput(this); hitungTotal();" />
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="hapusRow(this);" title="Hapus baris">
                                                <i class="fa fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Ringkasan Total Biaya -->
                        <div class="row mt-4 align-items-center">
                            <div class="col-md-6 text-muted small">
                                <i class="fa fa-info-circle me-1 text-primary"></i> Pastikan nota atau bukti fisik/digital
                                pengeluaran disimpan untuk diserahkan ke tim Finance.
                            </div>
                            <div class="col-md-6">
                                <div
                                    class="total-summary-card d-flex justify-content-between align-items-center shadow-sm">
                                    <div>
                                        <div class="text-muted small fw-semibold">TOTAL BIAYA REIMBURSEMENT</div>
                                        <h4 class="mb-0 text-primary fw-bold">Rp <span id="TotalCost">0</span></h4>
                                    </div>
                                    <i class="fa fa-wallet fa-2x text-primary opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Form -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2">
                    <a href="hrd-reimbursement-index" class="btn btn-secondary rmb-btn">
                        <i class="fa fa-arrow-left"></i> Kembali ke Daftar
                    </a>
                    <button type="button" class="btn btn-success rmb-btn px-4 shadow-sm" id="submitBtn">
                        <i class="fa fa-paper-plane"></i> Kirim Pengajuan Reimbursement
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "-- Pilih --",
                allowClear: true
            });

            $('#User_Responsibility_ID').on('change', function() {
                var opt = $(this).find('option:selected');
                var nik = $(this).val();
                if (!nik) return;

                var name   = opt.data('name') || '';
                var dept   = opt.data('dept') || '';
                var bank   = opt.data('bank') || '';
                var acc    = opt.data('acc') || '';
                var an     = opt.data('an') || '';
                var branch = opt.data('branch') || '';

                if (name) {
                    $('#User_Responsibility_Name').val(name);
                }
                if (dept) {
                    $('#Department').val(dept).trigger('change');
                }
                updateBankDisplay(bank, acc, an, branch, name);
            });
        });

        function updateBankDisplay(bank, acc, an, branch, fallbackName) {
            var bName   = bank || '';
            var bAcc    = acc || '';
            var bAn     = an || fallbackName || '';
            var bBranch = branch || '';

            $('#Bank_Name').val(bName);
            $('#Bank_Account_No').val(bAcc);
            $('#Bank_Account_Name').val(bAn);
            $('#Bank_Branch').val(bBranch);

            var bUpper = (bName || '').toUpperCase();
            var isBca = bUpper.indexOf('BCA') !== -1 || bUpper.indexOf('CENTRAL ASIA') !== -1;

            if (isBca) {
                $('#bankBcaBadge')
                    .removeClass('bg-secondary bg-warning text-dark')
                    .addClass('bg-primary text-white')
                    .text('BCA');
            } else if (bName !== '') {
                $('#bankBcaBadge')
                    .removeClass('bg-primary bg-warning text-dark')
                    .addClass('bg-secondary text-white')
                    .text(bName);
            } else {
                $('#bankBcaBadge')
                    .removeClass('bg-primary bg-secondary text-white')
                    .addClass('bg-warning text-dark')
                    .text('BELUM TERDAFTAR');
            }

            // Update interactive employee card
            if (bAcc !== '') {
                $('#empBankBadge').text(bName || 'Bank Karyawan');
                $('#bankVerifiedBadge')
                    .removeClass('bg-warning text-dark')
                    .addClass('bg-success text-white')
                    .html('<i class="fa fa-check-circle me-1"></i> Rekening Terdaftar');
                $('#empBankAccNoDisplay').text(bAcc);
                $('#empBankAccNameDisplay').text(bAn || '-');
                $('#empBankBranchDisplay').html('<i class="fa fa-map-marker-alt me-1 text-muted"></i> Cabang: ' + (bBranch || '-'));
                $('#btnCopyEmpAcc').show();
                if (!$('#chkCustomEmpBank').is(':checked')) {
                    $('#customEmpBankForm').hide();
                }
            } else {
                $('#empBankBadge').text('Belum Terdaftar');
                $('#bankVerifiedBadge')
                    .removeClass('bg-success text-white')
                    .addClass('bg-warning text-dark')
                    .html('<i class="fa fa-exclamation-triangle me-1"></i> Rekening Belum Tersimpan');
                $('#empBankAccNoDisplay').text('(Belum ada nomor rekening)');
                $('#empBankAccNameDisplay').text(bAn || '-');
                $('#empBankBranchDisplay').html('<i class="fa fa-info-circle me-1 text-warning"></i> Silakan lengkapi rekening di formulir alternatif');
                $('#btnCopyEmpAcc').hide();
                $('#chkCustomEmpBank').prop('checked', true);
                $('#customEmpBankForm').show();
            }
        }

        function toggleCustomBankInput(chk) {
            if ($(chk).is(':checked')) {
                $('#customEmpBankForm').slideDown(180);
                $('#custom_bank_name').val($('#Bank_Name').val());
                $('#custom_acc_no').val($('#Bank_Account_No').val());
                $('#custom_acc_name').val($('#Bank_Account_Name').val());
                $('#custom_acc_branch').val($('#Bank_Branch').val());
            } else {
                $('#customEmpBankForm').slideUp(180);
                var opt = $('#User_Responsibility_ID').find('option:selected');
                var fallbackBank = opt.data('bank') || "{{ $defaultBank['bank_name'] ?? '' }}";
                var fallbackAcc = opt.data('acc') || "{{ $defaultBank['bank_acc'] ?? '' }}";
                var fallbackAn = opt.data('an') || "{{ $defaultBank['bank_an'] ?? '' }}";
                var fallbackBranch = opt.data('branch') || "{{ $defaultBank['bank_branch'] ?? '' }}";
                updateBankDisplay(fallbackBank, fallbackAcc, fallbackAn, fallbackBranch, fallbackAn);
            }
        }

        function updateBankFromCustom() {
            var bName = $.trim($('#custom_bank_name').val());
            var bAcc = $.trim($('#custom_acc_no').val());
            var bAn = $.trim($('#custom_acc_name').val()) || $('#User_Responsibility_Name').val();
            var bBranch = $.trim($('#custom_acc_branch').val());

            $('#Bank_Name').val(bName);
            $('#Bank_Account_No').val(bAcc);
            $('#Bank_Account_Name').val(bAn);
            $('#Bank_Branch').val(bBranch);

            if (bAcc !== '') {
                $('#empBankAccNoDisplay').text(bAcc);
                $('#btnCopyEmpAcc').show();
            } else {
                $('#empBankAccNoDisplay').text('(Belum ada nomor rekening)');
                $('#btnCopyEmpAcc').hide();
            }
            $('#empBankBadge').text(bName || 'Custom Bank');
            $('#empBankAccNameDisplay').text(bAn || '-');
            $('#empBankBranchDisplay').html('<i class="fa fa-map-marker-alt me-1 text-muted"></i> Cabang: ' + (bBranch || '-'));
            $('#bankVerifiedBadge')
                .removeClass('bg-success text-white')
                .addClass('bg-info text-white')
                .html('<i class="fa fa-pen me-1"></i> Rekening Alternatif');
        }

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
                    fallbackCopy(text, btn);
                });
            } else {
                fallbackCopy(text, btn);
            }
        }

        function fallbackCopy(text, btn) {
            var temp = document.createElement("input");
            temp.style.position = "absolute";
            temp.style.left = "-9999px";
            temp.value = text;
            document.body.appendChild(temp);
            temp.select();
            try {
                document.execCommand("copy");
                feedbackSalin(btn);
            } catch (e) {
                alert("Nomor rekening: " + text);
            }
            document.body.removeChild(temp);
        }

        function feedbackSalin(btn) {
            var $b = $(btn);
            var oldHtml = $b.html();
            $b.html('<i class="fa fa-check text-success"></i> <span>Tersalin!</span>')
              .addClass('btn-success text-white')
              .removeClass('btn-outline-primary');
            setTimeout(function() {
                $b.html(oldHtml).removeClass('btn-success text-white').addClass('btn-outline-primary');
            }, 1600);
        }

        function salinRekeningPemohon(btn, event) {
            var acc = $('#Bank_Account_No').val();
            salinKeClipboard(acc, btn, event);
        }

        function handleFileSelect(input) {
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

                $('#filePreviewIcon').attr('class', 'fa ' + iconClass + ' fs-5');
                $('#filePreviewName').text(fileName);
                $('#filePreviewSize').text(fileSize);
                $('#filePreviewBox').slideDown(150);
            } else {
                clearSelectedFile();
            }
        }

        function clearSelectedFile() {
            $('#Attachment_File').val('');
            $('#filePreviewBox').slideUp(150);
        }

        var myCalendar;
        myCalendar = new dhtmlXCalendarObject(["Reimbursement_Date"]);
        myCalendar.setDateFormat("%Y-%m-%d");

        var rowRincian = 1;

        function tambahRow() {
            var html = "<tr>" +
                "<td><input type='text' id='Ref_" + rowRincian +
                "' class='form-control form-control-sm' placeholder='No Struk / Nota / Kwitansi' /></td>" +
                "<td><input type='text' id='Remark_" + rowRincian +
                "' class='form-control form-control-sm' placeholder='Keterangan pengeluaran' /></td>" +
                "<td><div class='input-group input-group-sm'><span class='input-group-text'>Rp</span><input type='text' id='Amount_" +
                rowRincian +
                "' class='form-control text-end fw-semibold amount-input' placeholder='0' onkeyup='formatAmountInput(this); hitungTotal();' /></div></td>" +
                "<td class='text-center'><button type='button' class='btn btn-outline-danger btn-sm' onclick='hapusRow(this);' title='Hapus baris'><i class='fa fa-trash-alt'></i></button></td>" +
                "</tr>";
            $("#tblRincian tbody").append(html);
            rowRincian++;
        }

        function hapusRow(btn) {
            if ($("#tblRincian tbody tr").length <= 1) {
                alert("Minimal harus ada satu baris rincian biaya!");
                return;
            }
            $(btn).closest("tr").remove();
            hitungTotal();
        }

        function formatAmountInput(elem) {
            var val = $(elem).val().replace(/[^0-9]/g, '');
            if (val === '') {
                $(elem).val('');
            } else {
                var num = parseInt(val, 10);
                $(elem).val(num.toLocaleString('id-ID'));
            }
        }

        function hitungTotal() {
            var total = 0;
            $("[id^='Amount_']").each(function() {
                var raw = $(this).val().replace(/\./g, "").replace(/,/g, ".");
                var v = parseFloat(raw);
                if (!isNaN(v)) total += v;
            });
            $("#TotalCost").text(total.toLocaleString("id-ID"));
        }

        $('#submitBtn').click(function(e) {
            e.preventDefault();

            var nikVal = $('#User_Responsibility_ID').val();
            if (!nikVal || $.trim(nikVal) === '') {
                alert("NIK Pemohon wajib diisi / dipilih!");
                $('#User_Responsibility_ID').focus();
                return;
            }

            var listId = ['Department', 'User_Responsibility_Name', 'Reimbursement_Date', 'Description'];
            for (var i = 0; i < listId.length; i++) {
                if ($.trim($("#" + listId[i]).val()) === "") {
                    var labelName = listId[i].replace(/_/g, " ");
                    alert("Field " + labelName + " wajib diisi!");
                    $("#" + listId[i]).focus();
                    return;
                }
            }

            var hasRow = false;
            $("[id^='Amount_']").each(function() {
                var raw = $(this).val().replace(/\./g, "").replace(/,/g, ".");
                var v = parseFloat(raw);
                if (!isNaN(v) && v > 0) hasRow = true;
            });
            if (!hasRow) {
                alert("Rincian biaya wajib diisi minimal satu baris dengan nominal lebih dari 0!");
                return;
            }

            var formData = new FormData();
            formData.append('Reimbursement_No', $('#Reimbursement_No').val());
            formData.append('User_Responsibility_ID', $('#User_Responsibility_ID').val());
            formData.append('Department', $('#Department').val());
            formData.append('User_Responsibility_Name', $('#User_Responsibility_Name').val());
            formData.append('Reference_No', $('#Reference_No').val());
            formData.append('Reimbursement_Date', $('#Reimbursement_Date').val());
            formData.append('Description', $('#Description').val());
            formData.append('Bank_Name', $('#Bank_Name').val());
            formData.append('Bank_Account_No', $('#Bank_Account_No').val());
            formData.append('Bank_Account_Name', $('#Bank_Account_Name').val());
            formData.append('Bank_Branch', $('#Bank_Branch').val());

            var fileInput = $('#Attachment_File')[0];
            if (fileInput && fileInput.files && fileInput.files.length > 0) {
                formData.append('Attachment_File', fileInput.files[0]);
            }

            $("[id^='Amount_']").each(function() {
                var idx = $(this).attr('id').replace('Amount_', '');
                formData.append('Amount_' + idx, $(this).val());
                formData.append('Remark_' + idx, $('#Remark_' + idx).val());
                formData.append('Ref_' + idx, $('#Ref_' + idx).val());
            });

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: 'hrd-reimbursement-new-submit',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status === 'success') {
                        alert('Request Reimbursement Berhasil Diajukan!');
                        location.href = 'hrd-reimbursement-index';
                    } else {
                        alert('Gagal submit! ' + res.message);
                        $btn.prop('disabled', false).html(
                            '<i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan Reimbursement');
                    }
                },
                error: function(xhr) {
                    var msg = 'Terjadi kesalahan server';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    alert(msg);
                    $btn.prop('disabled', false).html(
                        '<i class="fa fa-paper-plane me-1"></i> Kirim Pengajuan Reimbursement');
                }
            });
        });
    </script>
@endsection
