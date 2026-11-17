<div class="card shadow-sm border-primary mt-3 mb-4" style="border-radius: 10px; border-left: 5px solid #007bff;">
    <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-hand-holding-usd mr-2"></i>Input & Detail Penawaran Vendor (Quotation) &mdash; {{ $rfq->RFQ_No }}
        </h6>
        <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#RFQDiv').html('');" title="Tutup Detail">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <form id="form-simpan-quotation" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="rfq_no" value="{{ $rfq->RFQ_No }}">

            <!-- INFORMASI VENDOR & REFERENSI PR -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="font-weight-bold text-muted">No. RFQ / Referensi PR:</label>
                    <input type="text" class="form-control form-control-sm font-weight-bold text-primary bg-light" value="{{ $rfq->RFQ_No }} ({{ $rfq->PR_No }})" readonly>
                </div>
                <div class="col-md-4">
                    <label class="font-weight-bold text-muted">Vendor / Supplier Tujuan:</label>
                    <input type="text" class="form-control form-control-sm font-weight-bold bg-light" value="{{ $rfq->Supplier_ID }} - {{ $rfq->Company_Name ?? '-' }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold text-muted">Kontak / Telepon Vendor:</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $rfq->Contact_Person ?? '-' }} ({{ $rfq->Phone ?? '-' }})" readonly>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold text-muted">Est. Budget PR (Rp):</label>
                    <input type="text" class="form-control form-control-sm text-right font-weight-bold bg-light text-secondary" value="{{ number_format($rfq->Estimated_Budget ?? 0, 0, ',', '.') }}" readonly>
                </div>
            </div>

            <!-- DATA KOMERSIAL PENAWARAN VENDOR -->
            <div class="row mb-3 p-2 bg-light rounded border">
                <div class="col-md-3">
                    <label class="font-weight-bold">No. Surat Quotation Vendor <span class="text-danger">*</span>:</label>
                    <input type="text" class="form-control form-control-sm" name="quotation_no" value="{{ $rfq->Quotation_No ?? '' }}" placeholder="Contoh: QTN/VDR/2026/088" required>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold">Berlaku Sampai (Valid Until):</label>
                    <input type="date" class="form-control form-control-sm" name="valid_until" value="{{ $rfq->Valid_Until ?? date('Y-m-d', strtotime('+14 days')) }}">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold">Termin Pembayaran (Payment Terms):</label>
                    <select class="form-control form-control-sm" name="payment_terms">
                        <option value="NET 30" {{ ($rfq->Payment_Terms == 'NET 30') ? 'selected' : '' }}>NET 30 Hari</option>
                        <option value="NET 14" {{ ($rfq->Payment_Terms == 'NET 14') ? 'selected' : '' }}>NET 14 Hari</option>
                        <option value="COD / CASH" {{ ($rfq->Payment_Terms == 'COD / CASH') ? 'selected' : '' }}>COD / Cash Before Delivery</option>
                        <option value="DP 30% - PELUNASAN 70%" {{ ($rfq->Payment_Terms == 'DP 30% - PELUNASAN 70%') ? 'selected' : '' }}>DP 30% - Pelunasan 70%</option>
                        <option value="DP 50% - PELUNASAN 50%" {{ ($rfq->Payment_Terms == 'DP 50% - PELUNASAN 50%') ? 'selected' : '' }}>DP 50% - Pelunasan 50%</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="font-weight-bold">Upload File Penawaran Resmi Vendor (PDF/Gambar):</label>
                    <div class="d-flex align-items-center">
                        <input type="file" class="form-control-file form-control-sm" name="vendor_attachment" style="font-size: 11px;">
                        @if(!empty($rfq->Vendor_Attachment))
                            <a href="{{ asset($rfq->Vendor_Attachment) }}" target="_blank" class="btn btn-info text-white font-weight-bold ml-2 shadow-sm" style="padding: 2px 8px; font-size: 10px; white-space: nowrap;">
                                <i class="fas fa-eye"></i> Lihat PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TABEL ITEM PENAWARAN DARI rfq_dtl -->
            <h6 class="text-primary font-weight-bold mb-2" style="font-size: 12px;">
                <i class="fas fa-list-ol mr-1"></i> Rincian Harga Penawaran per Item (Sumber: <code>rfq_dtl</code>)
            </h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm bg-white" id="tabel-qtn-items" style="font-size: 11.5px;">
                    <thead class="thead-light text-center">
                        <tr>
                            <th style="width: 17%;">Product Code & Nama</th>
                            <th style="width: 14%;">Spesifikasi PR</th>
                            <th style="width: 6%;">Qty</th>
                            <th style="width: 6%;">Satuan</th>
                            <th style="width: 11%;">Est. Price PR (Rp)</th>
                            <th style="width: 13%;">Harga Vendor (Rp) <span class="text-danger">*</span></th>
                            <th style="width: 7%;">Diskon (%)</th>
                            <th style="width: 12%;">Total Harga (Rp)</th>
                            <th style="width: 6%;">Lead Time (Hari)</th>
                            <th style="width: 8%;">Catatan Vendor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rfq_items as $item)
                        <tr class="baris-qtn">
                            <td>
                                <input type="hidden" name="dtl_id[]" value="{{ $item->ID }}">
                                <span class="font-weight-bold text-primary">{{ $item->Product_Code }}</span><br>
                                <span class="text-dark">{{ $item->Product_Desc }}</span>
                            </td>
                            <td>{{ $item->Specification ?: '-' }}</td>
                            <td class="text-center align-middle">
                                <input type="number" class="form-control form-control-sm text-center bg-light qtn-qty" name="qty[]" value="{{ $item->Qty }}" readonly>
                            </td>
                            <td class="text-center align-middle">{{ $item->Unit }}</td>
                            <td class="text-right align-middle text-muted">
                                {{ number_format($item->Est_Price ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="align-middle">
                                <input type="number" class="form-control form-control-sm text-right font-weight-bold qtn-price" name="quoted_price[]" value="{{ $item->Quoted_Price > 0 ? $item->Quoted_Price : '' }}" min="0" step="any" placeholder="0" required>
                            </td>
                            <td class="align-middle">
                                <input type="number" class="form-control form-control-sm text-center qtn-disc" name="discount_percent[]" value="{{ $item->Discount_Percent ?? 0 }}" min="0" max="100" step="any">
                            </td>
                            <td class="align-middle">
                                <input type="text" class="form-control form-control-sm text-right font-weight-bold bg-light qtn-line-total" value="{{ number_format($item->Total_Price ?? 0, 0, ',', '.') }}" readonly>
                            </td>
                            <td class="align-middle">
                                <input type="number" class="form-control form-control-sm text-center" name="lead_time_days[]" value="{{ $item->Lead_Time_Days ?? '' }}" min="0" placeholder="Hari">
                            </td>
                            <td class="align-middle">
                                <input type="text" class="form-control form-control-sm" name="vendor_note[]" value="{{ $item->Vendor_Note ?? '' }}" placeholder="Merek / garansi...">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- RINGKASAN PAJAK, ONGKIR & GRAND TOTAL -->
            <div class="row mt-3">
                <div class="col-md-7">
                    <label class="font-weight-bold">Catatan Umum Penawaran (Remarks):</label>
                    <textarea class="form-control form-control-sm" name="remarks" rows="3" placeholder="Catatan tambahan terkait garansi, ketersediaan stok, atau syarat pengiriman...">{{ $rfq->Remarks ?? '' }}</textarea>
                </div>
                <div class="col-md-5">
                    <table class="table table-sm table-bordered bg-light mb-0" style="font-size: 12px;">
                        <tr>
                            <th class="w-50 align-middle">Subtotal Penawaran (Rp)</th>
                            <td class="text-right font-weight-bold" id="qtn_subtotal_display">{{ number_format($rfq->Subtotal_Amount ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="align-middle">
                                PPN (%)
                                <input type="number" id="qtn_tax_percent" name="tax_percent" class="form-control form-control-sm d-inline-block text-center ml-1" style="width: 65px;" value="{{ $rfq->Tax_Percent ?? 11 }}" min="0" step="any">
                            </th>
                            <td class="text-right" id="qtn_tax_display">{{ number_format($rfq->Tax_Amount ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th class="align-middle">Ongkos Kirim / Shipping (Rp)</th>
                            <td>
                                <input type="number" id="qtn_shipping_cost" name="shipping_cost" class="form-control form-control-sm text-right" value="{{ $rfq->Shipping_Cost ?? 0 }}" min="0" step="any">
                            </td>
                        </tr>
                        <tr class="table-primary">
                            <th class="align-middle text-primary" style="font-size: 13px;">GRAND TOTAL (Rp)</th>
                            <td class="text-right font-weight-bold text-primary" style="font-size: 14px;" id="qtn_grand_total_display">{{ number_format($rfq->Grand_Total ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- TOMBOL SIMPAN QUOTATION -->
            <div class="text-right mt-3">
                <button type="button" class="btn btn-secondary btn-sm mr-1" onclick="$('#RFQDiv').html('');">Batal</button>
                <button type="button" id="btn-simpan-qtn" class="btn btn-primary btn-sm font-weight-bold px-4 shadow-sm">
                    <i class="fas fa-save mr-1"></i> Simpan Penawaran Vendor (Quotation)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    function hitungQuotation() {
        var subtotal = 0;

        $('#tabel-qtn-items tbody tr.baris-qtn').each(function() {
            var qty   = parseFloat($(this).find('.qtn-qty').val()) || 0;
            var price = parseFloat($(this).find('.qtn-price').val()) || 0;
            var disc  = parseFloat($(this).find('.qtn-disc').val()) || 0;

            var gross     = qty * price;
            var discVal   = gross * (disc / 100);
            var lineTotal = gross - discVal;

            $(this).find('.qtn-line-total').val(lineTotal.toLocaleString('id-ID'));
            subtotal += lineTotal;
        });

        var taxPercent = parseFloat($('#qtn_tax_percent').val()) || 0;
        var taxAmount  = subtotal * (taxPercent / 100);
        var shipping   = parseFloat($('#qtn_shipping_cost').val()) || 0;
        var grandTotal = subtotal + taxAmount + shipping;

        $('#qtn_subtotal_display').text(subtotal.toLocaleString('id-ID'));
        $('#qtn_tax_display').text(taxAmount.toLocaleString('id-ID'));
        $('#qtn_grand_total_display').text(grandTotal.toLocaleString('id-ID'));
    }

    $(document).on('input change keyup', '.qtn-price, .qtn-disc, #qtn_tax_percent, #qtn_shipping_cost', function() {
        hitungQuotation();
    });

    hitungQuotation();

    // Simpan Quotation via AJAX
    $('#btn-simpan-qtn').off('click').on('click', function() {
        var formData = new FormData($('#form-simpan-quotation')[0]);

        $.ajax({
            url: "{{ url('qtn-store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    $('#RFQDiv').html('');
                    if (typeof RFQ !== 'undefined') {
                        RFQ.clearAll();
                        RFQ.load("{{ url('xml-purchasing-order-rfq') }}");
                    }
                }
            },
            error: function(err) {
                var errMsg = "Gagal menyimpan penawaran vendor.";
                if (err.responseJSON && err.responseJSON.message) {
                    errMsg = err.responseJSON.message;
                }
                alert(errMsg);
            }
        });
    });
});
</script>