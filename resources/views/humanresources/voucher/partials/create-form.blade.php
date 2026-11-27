<!-- Inline Form Card Pengajuan Voucher & AP Manual (Anti Salah Klik / Tanpa Modal) -->
<div id="HrVoucherFormCard" class="card border-primary shadow-sm mb-4" style="display:none;">
    <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center py-3">
        <div>
            <h5 class="mb-0 text-white fw-bold" id="HrVoucherFormTitle">Form Pengajuan Voucher / AP Manual</h5>
            <small class="text-white-50">Form inline tetap terbuka selama Anda mengisi data (tidak tertutup jika salah klik area luar).</small>
        </div>
        <button type="button" class="btn btn-sm btn-light text-dark fw-semibold" onclick="closeHrVoucherForm();">
            <i class="fa fa-times me-1"></i> Tutup Form
        </button>
    </div>

    <div class="card-body bg-light bg-opacity-25 p-4">
        <form id="FormHrVoucherCreate" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" id="hr_v_id" value="">

            <!-- LANGKAH 1: Jenis Pengajuan, Referensi PO & Vendor -->
            <div class="card border mb-3 shadow-none">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold text-primary"><span class="badge bg-primary text-white me-2">1</span> Jenis Pengajuan, Referensi PO &amp; Vendor</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Jalur Pengajuan <span class="text-danger">*</span></label>
                            <select name="Requested_Channel" id="hr_v_channel" class="form-select" onchange="onHrChannelChange();">
                                <option value="AP">Hutang Dagang / AP (Masuk ke Receive From Other Dept)</option>
                                <option value="DIRECT">Pembayaran Langsung (Cash / Bank)</option>
                            </select>
                            <div class="form-text">AP akan masuk ke <code>account_payable</code> (status <code>PROPOSED</code>) setelah disetujui Atasan/HCGA.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kategori Pengeluaran</label>
                            <select name="Expense_Category" id="hr_v_category" class="form-select">
                                <option value="OPERASIONAL">Operasional Umum</option>
                                <option value="JASA VENDOR">Jasa Vendor / Kontraktor</option>
                                <option value="PEMELIHARAAN & PERBAIKAN">Pemeliharaan &amp; Perbaikan</option>
                                <option value="UTILITAS & SEWA">Utilitas, Listrik, Air &amp; Sewa</option>
                                <option value="PERLENGKAPAN KANTOR">ATK &amp; Perlengkapan Kantor</option>
                                <option value="PAJAK & PERIZINAN">Pajak &amp; Perizinan</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="hr_pay_method_wrap">
                            <label class="form-label fw-semibold">Metode Bayar Usulan</label>
                            <select name="Payment_Method" id="hr_v_pay_method" class="form-select">
                                <option value="BANK">Transfer Bank</option>
                                <option value="CASH">Cash / Kas Kecil</option>
                            </select>
                        </div>

                        <!-- Tarik Referensi PO -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tarik Referensi Purchase Order (PO) <span class="text-muted fw-normal">(Opsional)</span></label>
                            <div class="input-group">
                                <input type="text" id="hr_po_search_input" class="form-control" placeholder="Ketik No. PO, PR, atau Nama Vendor..." />
                                <button type="button" class="btn btn-outline-primary" onclick="searchHrPo();"><i class="fa fa-search me-1"></i> Cari PO</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearHrPoRef();">Reset</button>
                            </div>
                            <div id="hr_po_results" class="list-group mt-1 shadow-sm" style="max-height:180px; overflow:auto; display:none;"></div>
                            <input type="hidden" name="PO_References" id="hr_v_po_ref" />
                            <input type="hidden" name="PR_No" id="hr_v_pr_no" />
                            <div class="form-text" id="hr_po_selected_badge">Belum memilih PO (Pengajuan Manual Non-PO).</div>
                        </div>

                        <!-- Pilih Vendor -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor / Penerima Pembayaran <span class="text-danger">*</span></label>
                            <div class="input-group mb-2">
                                <input type="text" id="hr_sup_search_input" class="form-control" placeholder="Cari Vendor dari Master Supplier..." />
                                <button type="button" class="btn btn-outline-primary" onclick="searchHrSuppliers();"><i class="fa fa-search me-1"></i> Cari Vendor</button>
                            </div>
                            <div id="hr_sup_results" class="list-group mb-2 shadow-sm" style="max-height:180px; overflow:auto; display:none;"></div>
                            <div class="row g-2">
                                <div class="col-4"><input type="text" name="Vendor_ID" id="hr_v_vendor_id" class="form-control" placeholder="ID Vendor" /></div>
                                <div class="col-8"><input type="text" name="Vendor_Name" id="hr_v_vendor_name" class="form-control" placeholder="Nama Vendor / Penerima *" required /></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LANGKAH 2: Informasi Faktur & Rekening Bank -->
            <div class="card border mb-3 shadow-none">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold text-primary"><span class="badge bg-primary text-white me-2">2</span> Informasi Faktur &amp; Rekening Penerima</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tipe Invoice</label>
                            <select name="Inv_Type" id="hr_v_inv_type" class="form-select">
                                <option value="SINGLE">SINGLE</option>
                                <option value="PARTIAL">PARTIAL</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">No. Faktur / Invoice / Reference</label>
                            <input type="text" name="Reference" id="hr_v_reference" class="form-control" placeholder="Contoh: INV/2026/XI/001" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tanggal Invoice</label>
                            <input type="date" name="Inv_Vendor_Date" id="hr_v_inv_date" class="form-control" value="{{ date('Y-m-d') }}" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tanggal Terima</label>
                            <input type="date" name="Date_Received" id="hr_v_recv_date" class="form-control" value="{{ date('Y-m-d') }}" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Jatuh Tempo</label>
                            <input type="date" name="Due_Date" id="hr_v_due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}" />
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tipe Transfer Bank</label>
                            <select name="Bank_Type" id="hr_v_bank_type" class="form-select">
                                <option value="DIRECT TRANSFER">DIRECT TRANSFER</option>
                                <option value="VIRTUAL ACCOUNT">VIRTUAL ACCOUNT</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Nama Bank</label>
                            <input type="text" name="Bank_Name" id="hr_v_bank_name" class="form-control" placeholder="BCA / Mandiri / OCBC" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">No. Rekening</label>
                            <input type="text" name="Bank_Acc" id="hr_v_bank_acc" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Atas Nama Rekening</label>
                            <input type="text" name="Bank_Acc_Name" id="hr_v_bank_acc_name" class="form-control" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- LANGKAH 3: Rincian Biaya & Pajak -->
            <div class="card border mb-3 shadow-none">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold text-primary"><span class="badge bg-primary text-white me-2">3</span> Rincian Biaya, Pajak &amp; Keterangan</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Subtotal Biaya (DPP)</label>
                            <input type="number" step="0.01" min="0" name="Subtotal" id="hr_v_subtotal" class="form-control" value="0" oninput="recalcHrVoucherTotal();" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Uang Muka (DP)</label>
                            <input type="number" step="0.01" min="0" name="DP" id="hr_v_dp" class="form-control" value="0" oninput="recalcHrVoucherTotal();" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Biaya Kirim</label>
                            <input type="number" step="0.01" min="0" name="Delivery_Costs" id="hr_v_delivery" class="form-control" value="0" oninput="recalcHrVoucherTotal();" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Management Fee</label>
                            <input type="number" step="0.01" min="0" name="Management_Fee" id="hr_v_mgmt" class="form-control" value="0" oninput="recalcHrVoucherTotal();" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Materai &amp; Departemen</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" name="Materai" id="hr_v_materai" class="form-control" value="0" oninput="recalcHrVoucherTotal();" title="Materai" />
                                <input type="text" name="Department" id="hr_v_dept" class="form-control" value="{{ Auth::user()->Department ?? 'HCGA' }}" placeholder="Dept" />
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Tarif PPN</label>
                            <select name="PPN_Type" id="hr_v_ppn_type" class="form-select" onchange="autoCalcHrPpn();">
                                <option value="0">Tanpa PPN</option>
                                <option value="11">11%</option>
                                <option value="10">10%</option>
                                <option value="1.1">1.1%</option>
                                <option value="1">1%</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Nominal PPN</label>
                            <input type="number" step="0.01" min="0" name="PPN" id="hr_v_ppn" class="form-control" value="0" oninput="recalcHrVoucherTotal();" />
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Jenis PPh</label>
                            <select name="PPH_Type" id="hr_v_pph_type" class="form-select" onchange="autoCalcHrPph();">
                                <option value="0">Tanpa PPh</option>
                                <option value="PPH23_2">PPh 23 (2%)</option>
                                <option value="PPH23_4">PPh 23 (4%)</option>
                                <option value="PPH23_15">PPh 23 (15%)</option>
                                <option value="PPH42">PPh 4(2) (2%)</option>
                                <option value="PPH_21_5">PPh 21 (5%)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Potongan PPh</label>
                            <input type="number" step="0.01" min="0" name="PPH" id="hr_v_pph" class="form-control" value="0" oninput="recalcHrVoucherTotal();" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-success">TOTAL TAGIHAN / VOUCHER (IDR)</label>
                            <input type="number" step="0.01" min="1" name="Total" id="hr_v_total" class="form-control form-control-lg fw-bold text-success bg-white border-success" value="0" required />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Perihal / Judul Pembayaran <span class="text-danger">*</span></label>
                            <input type="text" name="Subject_Payment" id="hr_v_subject" class="form-control" required placeholder="Contoh: Tagihan Jasa Servis AC Kantor Bulan November" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Upload Lampiran Invoice / Penawaran (PDF/Gambar)</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx" />
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan / Deskripsi Tambahan</label>
                            <textarea name="Description" id="hr_v_desc" class="form-control" rows="2" placeholder="Penjelasan detail transaksi untuk pemeriksa Finance..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- LANGKAH 4: Pilihan Akun Pengeluaran (Debet) & Sumber Dana / Hutang (Kredit) — Ramah Non-Accounting -->
            <style>
                .hr-coa-table { border-collapse: separate; border-spacing: 0; }
                .hr-coa-table thead th.hr-th-debet {
                    background-color: #e0f2fe !important;
                    color: #0369a1 !important;
                    border-bottom: 2px solid #0284c7 !important;
                }
                .hr-coa-table thead th.hr-th-credit {
                    background-color: #fef3c7 !important;
                    color: #b45309 !important;
                    border-bottom: 2px solid #d97706 !important;
                }
                .hr-coa-table tbody tr.hr-row-debet > td {
                    background-color: #f4faff;
                }
                .hr-coa-table tbody tr.hr-row-debet > td:first-child {
                    border-left: 5px solid #0284c7 !important;
                }
                .hr-coa-table tbody tr.hr-row-credit > td {
                    background-color: #fffbeb;
                }
                .hr-coa-table tbody tr.hr-row-credit > td:first-child {
                    border-left: 5px solid #d97706 !important;
                }
                .hr-side-switch {
                    display: inline-flex;
                    border-radius: 6px;
                    overflow: hidden;
                    border: 1px solid #cbd5e1;
                    background: #ffffff;
                    width: 100%;
                }
                .hr-side-switch .hr-side-btn {
                    flex: 1 1 50%;
                    border: 0;
                    background: transparent;
                    padding: 4px 6px;
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: .3px;
                    color: #64748b;
                    cursor: pointer;
                    transition: all .15s ease;
                }
                .hr-side-switch .hr-side-btn:hover {
                    background: #f1f5f9;
                }
                .hr-side-switch .hr-side-btn.active-debet {
                    background: #0284c7;
                    color: #ffffff;
                }
                .hr-side-switch .hr-side-btn.active-credit {
                    background: #d97706;
                    color: #ffffff;
                }
                .hr-side-hint {
                    font-size: 11px;
                    font-weight: 600;
                    margin-top: 4px;
                    display: flex;
                    align-items: center;
                    gap: 4px;
                }
                .hr-side-hint.is-debet { color: #0369a1; }
                .hr-side-hint.is-credit { color: #b45309; }
                .hr-amt-box .input-group-text {
                    font-size: 11px;
                    font-weight: 700;
                    padding: 0.25rem 0.45rem;
                }
                .hr-amt-box.is-active-debet .input-group-text {
                    background-color: #0284c7;
                    border-color: #0284c7;
                    color: #ffffff;
                }
                .hr-amt-box.is-active-debet .form-control {
                    border-color: #0284c7;
                    background-color: #ffffff;
                    color: #0369a1;
                    font-weight: 700;
                }
                .hr-amt-box.is-active-credit .input-group-text {
                    background-color: #d97706;
                    border-color: #d97706;
                    color: #ffffff;
                }
                .hr-amt-box.is-active-credit .form-control {
                    border-color: #d97706;
                    background-color: #ffffff;
                    color: #b45309;
                    font-weight: 700;
                }
                .hr-amt-box.is-inactive .input-group-text {
                    background-color: #f1f5f9;
                    border-color: #cbd5e1;
                    color: #94a3b8;
                }
                .hr-amt-box.is-inactive .form-control {
                    border-style: dashed;
                    border-color: #cbd5e1;
                    background-color: #f8fafc;
                    color: #64748b;
                }
                .hr-badge-debet { background-color: #0284c7 !important; color: #ffffff !important; }
                .hr-badge-credit { background-color: #d97706 !important; color: #ffffff !important; }
                .hr-btn-add-debet {
                    color: #0369a1;
                    border-color: #0284c7;
                    background-color: #f0f9ff;
                }
                .hr-btn-add-debet:hover {
                    background-color: #0284c7;
                    color: #ffffff;
                }
                .hr-btn-add-credit {
                    color: #b45309;
                    border-color: #d97706;
                    background-color: #fffbeb;
                }
                .hr-btn-add-credit:hover {
                    background-color: #d97706;
                    color: #ffffff;
                }
                .hr-guide-panel {
                    background: #f8fafc;
                    border: 1px solid #cbd5e1;
                    border-radius: 8px;
                }
                .hr-guide-table-wrap {
                    max-height: 260px;
                    overflow-y: auto;
                }
            </style>
            <div class="card border mb-3 shadow-none">
                <div class="card-header bg-opacity-10 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <span class="badge bg-primary text-white me-2">4</span> Pilihan Akun Pengeluaran (Debet) &amp; Sumber Pembayaran / Hutang (Kredit)
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark" id="btnToggleHrCoaGuide" onclick="toggleHrCoaGuide();">
                            <i class="fa fa-book-open me-1"></i> Panduan &amp; Daftar COA
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="suggestHrCoaLines();">
                            <i class="fa fa-magic me-1"></i> Isi Otomatis Akun
                        </button>
                        <button type="button" class="btn btn-sm hr-btn-add-debet fw-semibold" onclick="addHrCoaRow('DEBET');">
                            <i class="fa fa-arrow-circle-up me-1"></i> + Tambah Keperluan (Debet)
                        </button>
                        <button type="button" class="btn btn-sm hr-btn-add-credit fw-semibold" onclick="addHrCoaRow('CREDIT');">
                            <i class="fa fa-arrow-circle-down me-1"></i> + Tambah Sumber / Hutang (Kredit)
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border py-2 mb-3 small">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                            <strong>Panduan Singkat (Untuk Non-Accounting):</strong>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge hr-badge-debet"><i class="fa fa-arrow-up me-1"></i> DEBET (Biru) = Keperluan / Biaya</span>
                                <span class="badge hr-badge-credit"><i class="fa fa-arrow-down me-1"></i> KREDIT (Amber) = Sumber Dana / Hutang</span>
                            </div>
                        </div>
                        <ul class="mb-0 ps-3 mt-1">
                            <li><strong>Kolom Debet (Biru &bull; Keperluan / Pengeluaran)</strong>: Isi nominal di kolom <strong>Debet</strong> sesuai <em>peruntukan uang</em> (Biaya Pemeliharaan, Biaya Jasa, Uang Muka, atau PPN Masukan).</li>
                            <li><strong>Kolom Kredit (Amber &bull; Sumber Dana / Hutang)</strong>: Isi nominal di kolom <strong>Kredit</strong> sesuai <em>sumber pembayaran</em> (jika AP pilih kelompok <strong>Hutang Dagang / Hutang Usaha</strong>; jika langsung pilih <strong>Kas / Bank</strong>; ditambah <strong>Hutang PPh</strong> bila ada potongan PPh).</li>
                            <li>Cukup klik tombol <strong>DEBET / KREDIT</strong> atau langsung ketik nominal pada kolom <strong>Debet</strong> atau <strong>Kredit</strong> di sisi kanan &mdash; warna baris akan otomatis menyesuaikan. Klik <strong>"Seimbangkan Kredit Otomatis"</strong> bila hanya mengisi sisi Debet, atau buka <strong>"Panduan &amp; Daftar COA"</strong> untuk cari &amp; pilih akun cepat.</li>
                        </ul>
                    </div>

                    <!-- Collapsible Panel: Panduan Kasus Umum & Daftar COA Interaktif -->
                    <div id="hrCoaGuidePanel" class="hr-guide-panel p-3 mb-3" style="display:none;">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-2 mb-2 border-bottom">
                            <div class="btn-group btn-group-sm" role="tablist">
                                <button type="button" class="btn btn-primary fw-semibold" id="hrGuideBtnScenarios" onclick="switchHrCoaGuideTab('scenarios');">
                                    <i class="fa fa-lightbulb me-1"></i> Panduan Kasus Umum
                                </button>
                                <button type="button" class="btn btn-outline-primary fw-semibold" id="hrGuideBtnCatalog" onclick="switchHrCoaGuideTab('catalog');">
                                    <i class="fa fa-list-ul me-1"></i> Daftar &amp; Cari COA (<code>branch_coa</code>)
                                </button>
                            </div>
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleHrCoaGuide(false);">
                                <i class="fa fa-times me-1"></i> Tutup Panduan
                            </button>
                        </div>

                        <!-- Tab 1: Panduan Kasus Umum -->
                        <div id="hrGuideTabScenarios">
                            <div id="hrGuideContextHint" class="alert alert-info py-2 px-3 small mb-2"></div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered bg-white align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:210px;">Jenis Pengeluaran / Kasus</th>
                                            <th><span class="badge hr-badge-debet me-1">DEBET</span> Masuk Akun Keperluan / Biaya</th>
                                            <th><span class="badge hr-badge-credit me-1">KREDIT</span> Masuk Akun Sumber / Hutang</th>
                                            <th style="width:125px;" class="text-center">Cari Cepat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold">Operasional Umum</td>
                                            <td>Biaya Operasional / Keperluan Umum <small class="text-muted">(Kelompok <code>607</code> / <code>605</code>)</small></td>
                                            <td>Hutang Usaha <small class="text-muted">(<code>L1</code> jika AP)</small> atau Kas/Bank <small class="text-muted">(<code>A1–A4</code> jika Direct)</small></td>
                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openHrCoaGuideSearch('EXPENSE', 'operasional');">Cari COA</button></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Jasa Vendor / Kontraktor (+ PPh)</td>
                                            <td>Biaya Jasa / Konsultan / Subkontraktor <small class="text-muted">(Kelompok <code>607</code> / <code>605</code>)</small></td>
                                            <td>Hutang Usaha / Kas / Bank + <strong>Hutang PPh 23/21/4(2)</strong> <small class="text-muted">(<code>L7</code>)</small></td>
                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openHrCoaGuideSearch('EXPENSE', 'jasa');">Cari COA</button></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Pemeliharaan &amp; Perbaikan</td>
                                            <td>Biaya Pemeliharaan Kendaraan / Gedung / AC <small class="text-muted">(Kelompok <code>516</code> / <code>607</code>)</small></td>
                                            <td>Hutang Usaha <small class="text-muted">(<code>L1</code>)</small> atau Kas/Bank <small class="text-muted">(<code>A1–A4</code>)</small></td>
                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openHrCoaGuideSearch('EXPENSE', 'pemeliharaan');">Cari COA</button></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Utilitas, Listrik, Air &amp; Sewa</td>
                                            <td>Biaya Listrik, Air, Internet, atau Sewa <small class="text-muted">(Kelompok <code>607</code> / <code>605</code>)</small></td>
                                            <td>Hutang Usaha <small class="text-muted">(<code>L1</code>)</small> atau Kas/Bank <small class="text-muted">(<code>A1–A4</code>)</small></td>
                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openHrCoaGuideSearch('EXPENSE', 'listrik');">Cari COA</button></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">ATK &amp; Perlengkapan Kantor</td>
                                            <td>Biaya ATK / Perlengkapan Kantor <small class="text-muted">(Kelompok <code>607</code> / <code>605</code>)</small></td>
                                            <td>Hutang Usaha <small class="text-muted">(<code>L1</code>)</small> atau Kas/Bank <small class="text-muted">(<code>A1–A4</code>)</small></td>
                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openHrCoaGuideSearch('EXPENSE', 'atk');">Cari COA</button></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">Pajak &amp; Perizinan (STNK/KEUR)</td>
                                            <td>Biaya Pajak / Perizinan / STNK <small class="text-muted">(Kelompok <code>618</code> / <code>607</code>)</small></td>
                                            <td>Kas/Bank <small class="text-muted">(<code>A1–A4</code>)</small> atau Hutang Usaha <small class="text-muted">(<code>L1</code>)</small></td>
                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openHrCoaGuideSearch('EXPENSE', 'pajak');">Cari COA</button></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold">PPN / Uang Muka (DP) / Ongkir</td>
                                            <td>PPN Masukan <small class="text-muted">(<code>A12/A19</code>)</small>, Uang Muka <small class="text-muted">(<code>A11</code>)</small>, Ongkir <small class="text-muted">(<code>606</code>)</small></td>
                                            <td>Digabung pada total sisi Kredit (Hutang Usaha / Kas / Bank)</td>
                                            <td class="text-center"><button type="button" class="btn btn-xs btn-outline-primary" onclick="openHrCoaGuideSearch('ALL', 'ppn');">Cari PPN</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: Daftar & Cari COA Interaktif -->
                        <div id="hrGuideTabCatalog" style="display:none;">
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-md-5">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                        <input type="text" id="hrCoaGuideSearch" class="form-control" placeholder="Cari kode atau nama COA (misal: pemeliharaan, bca, hutang, ppn)..." oninput="renderHrCoaGuideList();" />
                                        <button type="button" class="btn btn-outline-secondary" onclick="$('#hrCoaGuideSearch').val(''); renderHrCoaGuideList();" title="Reset">&times;</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select id="hrCoaGuideTbFilter" class="form-select form-select-sm" onchange="renderHrCoaGuideList();">
                                        <option value="">-- Semua Kelompok Trial Balance --</option>
                                    </select>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <span class="small text-muted" id="hrCoaGuideCount">0 akun</span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-2" id="hrCoaGuideCatPills">
                                <button type="button" class="btn btn-xs btn-dark hr-guide-cat-btn" data-cat="ALL" onclick="setHrCoaGuideCat('ALL');">Semua</button>
                                <button type="button" class="btn btn-xs btn-outline-primary hr-guide-cat-btn" data-cat="EXPENSE" onclick="setHrCoaGuideCat('EXPENSE');">Biaya / Beban (Debet)</button>
                                <button type="button" class="btn btn-xs btn-outline-warning text-dark hr-guide-cat-btn" data-cat="CASH_BANK" onclick="setHrCoaGuideCat('CASH_BANK');">Kas &amp; Bank (Kredit Direct)</button>
                                <button type="button" class="btn btn-xs btn-outline-warning text-dark hr-guide-cat-btn" data-cat="LIABILITY_AP" onclick="setHrCoaGuideCat('LIABILITY_AP');">Hutang &amp; Pajak (Kredit AP/PPh)</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary hr-guide-cat-btn" data-cat="OTHER" onclick="setHrCoaGuideCat('OTHER');">DP / PPN / Lainnya</button>
                            </div>
                            <div class="table-responsive hr-guide-table-wrap border bg-white rounded">
                                <table class="table table-xs table-sm table-hover align-middle mb-0 small">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th style="width:120px;">Kode COA</th>
                                            <th>Nama Akun (<code>branch_coa</code>)</th>
                                            <th style="width:210px;">Kelompok Trial Balance</th>
                                            <th style="width:115px;">Kategori</th>
                                            <th style="width:170px;" class="text-end">Pakai ke Baris</th>
                                        </tr>
                                    </thead>
                                    <tbody id="hrCoaGuideBody">
                                        <tr><td colspan="5" class="text-center text-muted py-3">Memuat daftar COA...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle bg-white mb-2 hr-coa-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:142px;">Posisi Akun</th>
                                    <th style="width:195px;">Kelompok (Trial Balance)</th>
                                    <th style="min-width:220px;">Nama / Deskripsi Akun (<code>branch_coa</code>)</th>
                                    <th style="width:160px;" class="text-end hr-th-debet">
                                        <i class="fa fa-arrow-up me-1"></i> Debet (Rp)<br><small class="fw-normal">Keperluan / Biaya</small>
                                    </th>
                                    <th style="width:160px;" class="text-end hr-th-credit">
                                        <i class="fa fa-arrow-down me-1"></i> Kredit (Rp)<br><small class="fw-normal">Sumber / Hutang</small>
                                    </th>
                                    <th style="width:155px;">Keterangan</th>
                                    <th style="width:42px;"></th>
                                </tr>
                            </thead>
                            <tbody id="hrCoaLinesBody"></tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <button type="button" class="btn btn-xs btn-outline-dark" onclick="autoBalanceHrCredit();">
                                                <i class="fa fa-balance-scale me-1"></i> Seimbangkan Kredit Otomatis
                                            </button>
                                            <span id="hrBalanceBadge" class="badge bg-secondary text-white">Opsional / Belum Diisi</span>
                                        </div>
                                    </td>
                                    <td class="text-end" style="background-color:#e0f2fe;">
                                        <div class="small text-muted">Total Debet</div>
                                        <strong style="color:#0369a1;" id="hrSumDebet">Rp 0</strong>
                                    </td>
                                    <td class="text-end" style="background-color:#fef3c7;">
                                        <div class="small text-muted">Total Kredit</div>
                                        <strong style="color:#b45309;" id="hrSumCredit">Rp 0</strong>
                                    </td>
                                    <td colspan="2" class="small text-muted align-middle">
                                        <span id="hrBalanceDiffText">Debet harus = Kredit</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- LANGKAH 5: Jadwal Tenor Manual (Khusus AP) -->
            <div id="hrTenorWrapper" class="card border mb-3 shadow-none">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-primary"><span class="badge bg-primary text-white me-2">5</span> Jadwal Tenor / Termin Pembayaran (Khusus AP)</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addHrTenorRow();">+ Tambah Termin</button>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Kosongkan bila hanya 1 kali jatuh tempo (mengikuti kolom <em>Jatuh Tempo</em> di Langkah 2), atau isi beberapa termin pembayaran manual.</p>
                    <table class="table table-sm table-bordered bg-white mb-0">
                        <thead class="table-light">
                            <tr><th>Label Termin</th><th style="width:180px;">Tanggal Jatuh Tempo</th><th style="width:180px;" class="text-end">Nominal (Rp)</th><th>Catatan</th><th style="width:45px;"></th></tr>
                        </thead>
                        <tbody id="hrTenorBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2">
                <button type="button" class="btn btn-outline-secondary" onclick="closeHrVoucherForm();">
                    <i class="fa fa-times me-1"></i> Batal / Tutup
                </button>
                <button type="button" class="btn btn-primary btn-lg px-4 fw-semibold" id="btnSubmitHrVoucher" onclick="submitHrVoucher();">
                    <i class="fa fa-paper-plane me-1"></i> Simpan &amp; Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
