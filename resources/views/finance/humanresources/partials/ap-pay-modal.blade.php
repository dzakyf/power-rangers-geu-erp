<!-- Modal Pelunasan Fleksibel Treasury (Partial / Full) untuk AP HR/GA -->
<div class="modal fade" id="FinApPayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title text-white" id="FinApPayTitle">Pembayaran Hutang Dagang (AP) Treasury</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="FormFinApPay" enctype="multipart/form-data">
                    <input type="hidden" id="ap_pay_voucher_id" name="voucher_id" />
                    <div class="alert alert-light border py-2 mb-3">
                        <div class="row text-center">
                            <div class="col-4"><small class="text-muted d-block">Total Tagihan AP</small><strong id="ap_pay_total_txt">Rp 0</strong></div>
                            <div class="col-4"><small class="text-muted d-block">Sudah Dibayar</small><strong class="text-success" id="ap_pay_paid_txt">Rp 0</strong></div>
                            <div class="col-4"><small class="text-muted d-block">Sisa Hutang</small><strong class="text-danger" id="ap_pay_remain_txt">Rp 0</strong></div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Alokasi Termin / Tenor (Opsional)</label>
                            <select id="ap_pay_tenor_id" name="Tenor_ID" class="form-select form-select-sm" onchange="onSelectPayTenor();">
                                <option value="">-- Bebas / FIFO --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Tanggal Bayar <span class="text-danger">*</span></label>
                            <input type="date" name="Payment_Date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Nominal Bayar (Partial/Full) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="1" id="ap_pay_amount" name="Amount" class="form-control form-control-sm fw-bold" required />
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Jalur Kas / Bank</label>
                            <select id="ap_pay_channel" name="Payment_Channel" class="form-select form-select-sm" onchange="reloadApPayCoaOptions();">
                                <option value="BANK">Bank Out (BANK_OUT)</option>
                                <option value="CASH">Cash Out (CASH_OUT)</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Akun COA Kas / Bank Sumber <span class="text-danger">*</span></label>
                            <select id="ap_pay_acc_no" name="Payment_Acc_No" class="form-select form-select-sm" required></select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small">Rekening Bank Internal</label>
                            <select id="ap_pay_bank_ref" name="Bank_Acc_Ref" class="form-select form-select-sm">
                                <option value="">-- Otomatis dari COA --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">No. Referensi Transfer</label>
                            <input type="text" name="Payment_Ref" class="form-control form-control-sm" placeholder="ex: TRF/AP/2026/001" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Upload Bukti Bayar</label>
                            <input type="file" name="proof_file" class="form-control form-control-sm" accept="image/*,.pdf" />
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Catatan Treasury</label>
                            <input type="text" name="Note" class="form-control form-control-sm" placeholder="Catatan pembayaran parsial / pelunasan..." />
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm" onclick="submitFinApPay();">Catat Pembayaran &amp; Posting Jurnal HO</button>
            </div>
        </div>
    </div>
</div>
