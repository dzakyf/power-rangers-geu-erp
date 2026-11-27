<!-- Modal Verifikasi, Koreksi Biaya, Konversi AP & Mapping COA Multi-Debet/Kredit -->
<div class="modal fade" id="FinVoucherProcessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width:94%;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h6 class="modal-title text-white mb-0" id="FinVpModalTitle">Verifikasi &amp; Penerbitan Voucher</h6>
                    <small class="text-white-50" id="FinVpModalSub"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="FormFinVoucherProcess" enctype="multipart/form-data">
                    <input type="hidden" id="fv_id" name="voucher_id" />
                    <input type="hidden" id="fv_source_module" />
                    <input type="hidden" id="fv_requested_channel" />

                    <!-- Jalur Penerbitan Final -->
                    <div class="card border-primary mb-3">
                        <div class="card-body py-2">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold mb-1">Keputusan Jalur Penerbitan Finance <span class="text-danger">*</span></label>
                                    <select id="fv_final_channel" name="Final_Channel" class="form-select form-select-sm fw-semibold" onchange="onFinChannelChange();">
                                        <option value="DIRECT">Pembayaran Langsung (Cash / Bank Out)</option>
                                        <option value="AP">Hutang Dagang (AP / Tenor Pembayaran)</option>
                                    </select>
                                </div>
                                <div class="col-md-3" id="fv_pay_method_wrap">
                                    <label class="form-label small fw-bold mb-1">Metode Pembayaran</label>
                                    <select id="fv_payment_method" name="Payment_Method" class="form-select form-select-sm" onchange="onFinChannelChange();">
                                        <option value="BANK">Transfer Bank (BANK_OUT)</option>
                                        <option value="CASH">Kas / Petty Cash (CASH_OUT)</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold mb-1">Tgl Posting Jurnal HO</label>
                                    <input type="date" id="fv_posting_date" name="Posting_Date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" />
                                </div>
                                <div class="col-md-3">
                                    <div id="fv_convert_badge" class="alert alert-warning py-1 px-2 mb-0 small d-none">
                                        <i class="fa fa-exchange-alt me-1"></i> Dikonversi dari <strong>DIRECT &rarr; AP</strong>
                                    </div>
                                    <div id="fv_req_badge" class="small text-muted"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rincian Vendor, PO, Faktur & Rekening -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small">Ref. PO (purchase_order)</label>
                            <input type="text" id="fv_po_ref" name="PO_References" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">No. Faktur / Reference</label>
                            <input type="text" id="fv_reference" name="Reference" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Tipe Invoice</label>
                            <select id="fv_inv_type" name="Inv_Type" class="form-select form-select-sm">
                                <option value="SINGLE">SINGLE</option>
                                <option value="PARTIAL">PARTIAL</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Tgl Invoice</label>
                            <input type="date" id="fv_inv_date" name="Inv_Vendor_Date" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Jatuh Tempo Utama</label>
                            <input type="date" id="fv_due_date" name="Due_Date" class="form-control form-control-sm" />
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small">Vendor ID</label>
                            <input type="text" id="fv_vendor_id" name="Vendor_ID" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Nama Vendor / Penerima</label>
                            <input type="text" id="fv_vendor_name" name="Vendor_Name" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Bank Vendor</label>
                            <input type="text" id="fv_bank_name" name="Bank_Name" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">No. Rek Vendor</label>
                            <input type="text" id="fv_bank_acc" name="Bank_Acc" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">A/N Rekening</label>
                            <input type="text" id="fv_bank_acc_name" name="Bank_Acc_Name" class="form-control form-control-sm" />
                        </div>
                    </div>

                    <!-- Koreksi Rincian Biaya & Pajak -->
                    <div class="card bg-light border mb-3">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small text-primary">Koreksi Komponen Biaya &amp; Pajak</span>
                                <button type="button" class="btn btn-xs btn-outline-primary" onclick="suggestFinCoaLines();">
                                    <i class="fa fa-magic me-1"></i> Auto-Fill COA dari Komponen &amp; Mapping
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-2">
                                    <label class="form-label small">Subtotal</label>
                                    <input type="number" step="0.01" min="0" id="fv_subtotal" name="Subtotal" class="form-control form-control-sm" oninput="recalcFinVoucherTotal();" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small">DP</label>
                                    <input type="number" step="0.01" min="0" id="fv_dp" name="DP" class="form-control form-control-sm" oninput="recalcFinVoucherTotal();" />
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Delivery Costs</label>
                                    <input type="number" step="0.01" min="0" id="fv_delivery" name="Delivery_Costs" class="form-control form-control-sm" oninput="recalcFinVoucherTotal();" />
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Mgmt Fee</label>
                                    <input type="number" step="0.01" min="0" id="fv_mgmt" name="Management_Fee" class="form-control form-control-sm" oninput="recalcFinVoucherTotal();" />
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small">Materai</label>
                                    <input type="number" step="0.01" min="0" id="fv_materai" name="Materai" class="form-control form-control-sm" oninput="recalcFinVoucherTotal();" />
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">PPN</label>
                                    <input type="number" step="0.01" min="0" id="fv_ppn" name="PPN" class="form-control form-control-sm" oninput="recalcFinVoucherTotal();" />
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Potongan PPh</label>
                                    <input type="number" step="0.01" min="0" id="fv_pph" name="PPH" class="form-control form-control-sm" oninput="recalcFinVoucherTotal();" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-success">Total Voucher (IDR)</label>
                                    <input type="number" step="0.01" min="1" id="fv_total" name="Total" class="form-control form-control-sm fw-bold text-success" />
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small">Perihal / Subject Payment</label>
                                    <input type="text" id="fv_subject" name="Subject_Payment" class="form-control form-control-sm" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Catatan Finance</label>
                                    <input type="text" id="fv_note" name="Finance_Note" class="form-control form-control-sm" placeholder="Catatan verifikasi..." />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Opsi Tambahan jika Direct Payment -->
                    <div id="fv_direct_extra" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small">Rekening Bank Internal (jika Bank Out)</label>
                            <select id="fv_bank_acc_ref" name="Bank_Acc_Ref" class="form-select form-select-sm">
                                <option value="">-- Sesuai COA Kredit --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">No. Referensi Pembayaran / Transfer</label>
                            <input type="text" id="fv_payment_ref" name="Payment_Ref" class="form-control form-control-sm" placeholder="ex: TRF/FIN/2026/001" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Upload Bukti Bayar Direct (Opsional)</label>
                            <input type="file" id="fv_proof_file" name="proof_file" class="form-control form-control-sm" accept="image/*,.pdf" />
                        </div>
                    </div>

                    <!-- Editor Multi-Debet & Multi-Kredit COA (branch_trial_balance -> branch_coa) -->
                    <style>
                        .fv-coa-table { border-collapse: separate; border-spacing: 0; }
                        .fv-coa-table thead th.fv-th-debet {
                            background-color: #e0f2fe !important;
                            color: #0369a1 !important;
                            border-bottom: 2px solid #0284c7 !important;
                        }
                        .fv-coa-table thead th.fv-th-credit {
                            background-color: #fef3c7 !important;
                            color: #b45309 !important;
                            border-bottom: 2px solid #d97706 !important;
                        }
                        .fv-coa-table tbody tr.fv-row-debet > td {
                            background-color: #f4faff;
                        }
                        .fv-coa-table tbody tr.fv-row-debet > td:first-child {
                            border-left: 5px solid #0284c7 !important;
                        }
                        .fv-coa-table tbody tr.fv-row-credit > td {
                            background-color: #fffbeb;
                        }
                        .fv-coa-table tbody tr.fv-row-credit > td:first-child {
                            border-left: 5px solid #d97706 !important;
                        }
                        .fv-side-switch {
                            display: inline-flex;
                            border-radius: 6px;
                            overflow: hidden;
                            border: 1px solid #cbd5e1;
                            background: #ffffff;
                            width: 100%;
                        }
                        .fv-side-switch .fv-side-btn {
                            flex: 1 1 50%;
                            border: 0;
                            background: transparent;
                            padding: 4px 5px;
                            font-size: 11px;
                            font-weight: 700;
                            letter-spacing: .3px;
                            color: #64748b;
                            cursor: pointer;
                            transition: all .15s ease;
                        }
                        .fv-side-switch .fv-side-btn:hover {
                            background: #f1f5f9;
                        }
                        .fv-side-switch .fv-side-btn.active-debet {
                            background: #0284c7;
                            color: #ffffff;
                        }
                        .fv-side-switch .fv-side-btn.active-credit {
                            background: #d97706;
                            color: #ffffff;
                        }
                        .fv-amt-box .input-group-text {
                            font-size: 11px;
                            font-weight: 700;
                            padding: 0.25rem 0.4rem;
                        }
                        .fv-amt-box.is-active-debet .input-group-text {
                            background-color: #0284c7;
                            border-color: #0284c7;
                            color: #ffffff;
                        }
                        .fv-amt-box.is-active-debet .form-control {
                            border-color: #0284c7;
                            background-color: #ffffff;
                            color: #0369a1;
                            font-weight: 700;
                        }
                        .fv-amt-box.is-active-credit .input-group-text {
                            background-color: #d97706;
                            border-color: #d97706;
                            color: #ffffff;
                        }
                        .fv-amt-box.is-active-credit .form-control {
                            border-color: #d97706;
                            background-color: #ffffff;
                            color: #b45309;
                            font-weight: 700;
                        }
                        .fv-amt-box.is-inactive .input-group-text {
                            background-color: #f1f5f9;
                            border-color: #cbd5e1;
                            color: #94a3b8;
                        }
                        .fv-amt-box.is-inactive .form-control {
                            border-style: dashed;
                            border-color: #cbd5e1;
                            background-color: #f8fafc;
                            color: #64748b;
                        }
                        .fv-btn-add-debet {
                            color: #0369a1;
                            border-color: #0284c7;
                            background-color: #f0f9ff;
                        }
                        .fv-btn-add-debet:hover {
                            background-color: #0284c7;
                            color: #ffffff;
                        }
                        .fv-btn-add-credit {
                            color: #b45309;
                            border-color: #d97706;
                            background-color: #fffbeb;
                        }
                        .fv-btn-add-credit:hover {
                            background-color: #d97706;
                            color: #ffffff;
                        }
                        .fv-guide-panel {
                            background: #f8fafc;
                            border: 1px solid #cbd5e1;
                            border-radius: 8px;
                        }
                        .fv-guide-table-wrap {
                            max-height: 240px;
                            overflow-y: auto;
                        }
                    </style>
                    <div class="card border mb-3">
                        <div class="card-header bg-white py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="fw-bold small text-dark"><i class="fa fa-sitemap me-1 text-primary"></i> Jurnal COA Multi-Debet &amp; Multi-Kredit (<code>branch_trial_balance</code> &rarr; <code>branch_coa</code> &rarr; <code>ho</code>)</span>
                                <span class="badge text-white" style="background-color:#0284c7;">DEBET</span>
                                <span class="badge text-white" style="background-color:#d97706;">KREDIT</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-xs btn-outline-primary fw-semibold" id="btnToggleFinCoaGuide" onclick="toggleFinCoaGuide();">
                                    <i class="fa fa-book-open me-1"></i> Panduan &amp; Daftar COA
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="sortFinCoaLines();" title="Kelompokkan baris Debet di atas dan Kredit di bawah">
                                    <i class="fa fa-sort-amount-down me-1"></i> Urutkan Debet &rarr; Kredit
                                </button>
                                <button type="button" class="btn btn-xs fv-btn-add-debet fw-semibold" onclick="addFinCoaLine('DEBET');">
                                    <i class="fa fa-arrow-up me-1"></i> + Tambah Debet
                                </button>
                                <button type="button" class="btn btn-xs fv-btn-add-credit fw-semibold" onclick="addFinCoaLine('CREDIT');">
                                    <i class="fa fa-arrow-down me-1"></i> + Tambah Kredit
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <div class="small text-muted mb-2 px-1">
                                <i class="fa fa-info-circle me-1 text-primary"></i> Klik tombol <strong>DEBET / KREDIT</strong> atau ketik langsung di kolom <strong>Debet</strong> (Biru) / <strong>Kredit</strong> (Amber) &mdash; warna baris akan otomatis menyesuaikan. Klik <strong>Panduan &amp; Daftar COA</strong> untuk referensi Trial Balance, default mapping, dan pilih cepat akun.
                            </div>

                            <!-- Collapsible Panel: Panduan Trial Balance, Default Mapping & Katalog Cepat COA -->
                            <div id="fvCoaGuidePanel" class="fv-guide-panel p-2 mb-2" style="display:none;">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-2 mb-2 border-bottom">
                                    <div class="btn-group btn-group-sm" role="tablist">
                                        <button type="button" class="btn btn-primary fw-semibold" id="fvGuideBtnMap" onclick="switchFinCoaGuideTab('tbmap');">
                                            <i class="fa fa-sitemap me-1"></i> Peta Trial Balance &amp; Bank
                                        </button>
                                        <button type="button" class="btn btn-outline-primary fw-semibold" id="fvGuideBtnDefaults" onclick="switchFinCoaGuideTab('defaults');">
                                            <i class="fa fa-sliders-h me-1"></i> Default Mapping Aktif
                                        </button>
                                        <button type="button" class="btn btn-outline-primary fw-semibold" id="fvGuideBtnCatalog" onclick="switchFinCoaGuideTab('catalog');">
                                            <i class="fa fa-list-ul me-1"></i> Cari &amp; Pilih COA (<code>branch_coa</code>)
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleFinCoaGuide(false);">
                                        <i class="fa fa-times me-1"></i> Tutup
                                    </button>
                                </div>

                                <!-- Tab 1: Peta Trial Balance & Bank Internal -->
                                <div id="fvGuideTabTbMap">
                                    <div class="row g-2">
                                        <div class="col-lg-8">
                                            <table class="table table-xs table-sm table-bordered bg-white align-middle mb-0 small">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:90px;">Sisi</th>
                                                        <th style="width:140px;">Kelompok TB</th>
                                                        <th>Komponen &amp; Peruntukan Akuntansi</th>
                                                        <th style="width:90px;" class="text-center">Cari</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><span class="badge text-white" style="background-color:#0284c7;">DEBET</span></td>
                                                        <td><code>5xx</code> / <code>6xx</code></td>
                                                        <td><strong>Beban Utama / Subtotal</strong> (<code>607</code>/<code>605</code>/<code>516</code> Maintenance/<code>618</code> Pajak), <strong>Ongkir</strong> (<code>606</code>/<code>517</code>), <strong>Mgmt Fee</strong> (<code>608</code>), <strong>Materai</strong> (<code>605</code>/<code>620</code>)</td>
                                                        <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openFinCoaGuideSearch('EXPENSE', '');">Lihat</button></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge text-white" style="background-color:#0284c7;">DEBET</span></td>
                                                        <td><code>A11</code>, <code>A12</code>, <code>A19</code></td>
                                                        <td><strong>Uang Muka / DP</strong> (<code>A11</code>) dan <strong>PPN Masukan</strong> (<code>A12</code>/<code>A19</code>)</td>
                                                        <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openFinCoaGuideSearch('DP_PPN', '');">Lihat</button></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge text-white" style="background-color:#d97706;">KREDIT</span></td>
                                                        <td><code>A1</code> &ndash; <code>A4</code></td>
                                                        <td><strong>Direct Payment</strong>: Kas / Petty Cash (<code>A1</code>/<code>A2</code>, prefix <code>1-11</code>) atau Bank Out (<code>A3</code>/<code>A4</code>, prefix <code>1-12</code>)</td>
                                                        <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openFinCoaGuideSearch('CASH_BANK', '');">Lihat</button></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge text-white" style="background-color:#d97706;">KREDIT</span></td>
                                                        <td><code>L1</code>, <code>L4</code>, <code>L6</code>, <code>L7</code></td>
                                                        <td><strong>AP &amp; Potongan Pajak</strong>: Hutang Dagang / Usaha (<code>L1</code>, prefix <code>2-1</code>) dan Hutang PPh 21/23/4(2) (<code>L7</code>)</td>
                                                        <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openFinCoaGuideSearch('LIABILITY', '');">Lihat</button></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="bg-white border rounded p-2 h-100 small">
                                                <div class="fw-bold text-dark mb-1"><i class="fa fa-university me-1 text-primary"></i> Rekening Bank Internal (Quick Kredit)</div>
                                                <div id="fvGuideBankQuickList" class="d-flex flex-column gap-1" style="max-height:155px; overflow-y:auto;">
                                                    <span class="text-muted">Memuat daftar bank...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab 2: Default Mapping Aktif (finance_coa_mapping) -->
                                <div id="fvGuideTabDefaults" style="display:none;">
                                    <div class="small text-muted mb-1">Mapping default COA aktif dari <code>finance_coa_mapping</code> (baris modul voucher ini disorot biru):</div>
                                    <div class="table-responsive fv-guide-table-wrap border bg-white rounded">
                                        <table class="table table-xs table-sm table-hover align-middle mb-0 small">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th style="width:140px;">Modul Sumber</th>
                                                    <th style="width:150px;">Komponen</th>
                                                    <th style="width:85px;">Sisi</th>
                                                    <th>Akun COA Default</th>
                                                    <th style="width:180px;">Kelompok TB</th>
                                                    <th style="width:95px;" class="text-end">Pakai</th>
                                                </tr>
                                            </thead>
                                            <tbody id="fvGuideDefaultsBody">
                                                <tr><td colspan="6" class="text-center text-muted py-3">Memuat mapping default...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Tab 3: Cari & Pilih COA (branch_coa) -->
                                <div id="fvGuideTabCatalog" style="display:none;">
                                    <div class="row g-2 align-items-center mb-2">
                                        <div class="col-md-5">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                                <input type="text" id="fvCoaGuideSearch" class="form-control" placeholder="Ketik kode COA, nama akun, atau kelompok TB..." oninput="renderFinCoaGuideList();" />
                                                <button type="button" class="btn btn-outline-secondary" onclick="$('#fvCoaGuideSearch').val(''); renderFinCoaGuideList();" title="Reset">&times;</button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <select id="fvCoaGuideTbFilter" class="form-select form-select-sm" onchange="renderFinCoaGuideList();">
                                                <option value="">-- Semua Kelompok TB --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 text-md-end">
                                            <span class="small text-muted" id="fvCoaGuideCount">0 akun</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1 mb-2" id="fvCoaGuideCatPills">
                                        <button type="button" class="btn btn-xs btn-dark fv-guide-cat-btn" data-cat="ALL" onclick="setFinCoaGuideCat('ALL');">Semua</button>
                                        <button type="button" class="btn btn-xs btn-outline-primary fv-guide-cat-btn" data-cat="EXPENSE" onclick="setFinCoaGuideCat('EXPENSE');">Beban (5xx / 6xx)</button>
                                        <button type="button" class="btn btn-xs btn-outline-primary fv-guide-cat-btn" data-cat="DP_PPN" onclick="setFinCoaGuideCat('DP_PPN');">DP &amp; PPN (A11–A19)</button>
                                        <button type="button" class="btn btn-xs btn-outline-warning text-dark fv-guide-cat-btn" data-cat="CASH_BANK" onclick="setFinCoaGuideCat('CASH_BANK');">Kas &amp; Bank (A1–A4)</button>
                                        <button type="button" class="btn btn-xs btn-outline-warning text-dark fv-guide-cat-btn" data-cat="LIABILITY" onclick="setFinCoaGuideCat('LIABILITY');">Hutang &amp; PPh (L1–L7)</button>
                                    </div>
                                    <div class="table-responsive fv-guide-table-wrap border bg-white rounded">
                                        <table class="table table-xs table-sm table-hover align-middle mb-0 small">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th style="width:120px;">Kode COA</th>
                                                    <th>Nama Akun (<code>branch_coa</code>)</th>
                                                    <th style="width:220px;">Kelompok Trial Balance</th>
                                                    <th style="width:165px;" class="text-end">Pakai ke Jurnal</th>
                                                </tr>
                                            </thead>
                                            <tbody id="fvCoaGuideBody">
                                                <tr><td colspan="4" class="text-center text-muted py-3">Memuat daftar COA...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-2 fv-coa-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:128px;">Posisi</th>
                                            <th style="width:205px;">Kelompok Trial Balance</th>
                                            <th style="min-width:250px;">Akun COA (<code>branch_coa</code>)</th>
                                            <th style="width:155px;" class="text-end fv-th-debet"><i class="fa fa-arrow-up me-1"></i> Debet (Rp)</th>
                                            <th style="width:155px;" class="text-end fv-th-credit"><i class="fa fa-arrow-down me-1"></i> Kredit (Rp)</th>
                                            <th style="width:85px;">Dept</th>
                                            <th style="min-width:140px;">Keterangan Baris</th>
                                            <th style="width:38px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="fvCoaLinesBody"></tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3">
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <button type="button" class="btn btn-xs btn-outline-dark" onclick="autoBalanceFinCredit();">
                                                        <i class="fa fa-balance-scale me-1"></i> Seimbangkan Kredit Otomatis
                                                    </button>
                                                    <span id="fvBalanceBadge" class="badge bg-secondary text-white">Belum Diisi</span>
                                                </div>
                                            </td>
                                            <td class="text-end" style="background-color:#e0f2fe;">
                                                <div class="small text-muted fw-normal">Total Debet</div>
                                                <strong style="color:#0369a1;" id="fvSumDebet">Rp 0</strong>
                                            </td>
                                            <td class="text-end" style="background-color:#fef3c7;">
                                                <div class="small text-muted fw-normal">Total Kredit</div>
                                                <strong style="color:#b45309;" id="fvSumCredit">Rp 0</strong>
                                            </td>
                                            <td colspan="3" class="small text-muted align-middle">
                                                <span id="fvBalanceDiffText">Debet harus = Kredit</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Editor Tenor Manual (Khusus Jalur AP) -->
                    <div id="fv_tenor_section" class="card border-info mb-2 d-none">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small text-info"><i class="fa fa-calendar-check me-1"></i> Jadwal Tenor / Termin Pembayaran Manual (Jalur AP)</span>
                            <button type="button" class="btn btn-xs btn-outline-info" onclick="addFinTenorRow();">+ Tambah Termin</button>
                        </div>
                        <div class="card-body p-2">
                            <table class="table table-sm table-bordered mb-1">
                                <thead class="table-light">
                                    <tr><th>Label Termin</th><th style="width:160px;">Jatuh Tempo</th><th style="width:160px;" class="text-end">Nominal</th><th>Catatan</th><th style="width:40px;"></th></tr>
                                </thead>
                                <tbody id="fvTenorBody"></tbody>
                            </table>
                            <div class="small text-end">Total Tenor: <strong id="fvTenorSum">Rp 0</strong> (<span id="fvTenorMatch"></span>)</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="rejectFromModal();">
                    <i class="fa fa-undo me-1"></i> Reject &amp; Kembalikan
                </button>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success btn-sm fw-semibold" id="btnSubmitFinVoucher" onclick="submitFinVoucherProcess();">
                        <i class="fa fa-check-circle me-1"></i> Approve &amp; Terbitkan ke Legacy + Jurnal HO
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
