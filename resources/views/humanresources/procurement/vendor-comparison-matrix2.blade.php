@if(!$prHeader)
    <div class="alert alert-danger mt-3" style="font-size: 12px;">
        <i class="fas fa-exclamation-triangle mr-1"></i> Data Purchase Request tidak ditemukan.
    </div>
@else
@php
    // Kumpulkan nomor Quotation / RFQ yang masuk untuk header dokumen
    $quotationNumbers = [];
    foreach ($rfqVendors as $v) {
        if (!empty($v->Quotation_No)) {
            $quotationNumbers[] = $v->Quotation_No . ' (' . $v->Supplier_ID . ')';
        } elseif (!empty($v->RFQ_No)) {
            $quotationNumbers[] = $v->RFQ_No;
        }
    }
    $quotationDisplay = count($quotationNumbers) > 0 ? implode(', ', $quotationNumbers) : '-';

    // Hitung peringkat harga per item (1 = Termurah, 2 = Menengah, 3+ = Termahal)
    $itemRanks = [];
    foreach ($prItems as $item) {
        $pricesForThisItem = [];
        foreach ($rfqVendors as $v) {
            $dtl = $rfqDetails[$v->Supplier_ID][$item->Product_Code] ?? null;
            $price = $dtl ? (float) ($dtl->Quoted_Price ?? 0) : 0;
            if ($price > 0) {
                $pricesForThisItem[$v->Supplier_ID] = $price;
            }
        }

        $uniquePrices = array_values(array_unique($pricesForThisItem));
        sort($uniquePrices);

        foreach ($pricesForThisItem as $supId => $pVal) {
            $rankIndex = array_search($pVal, $uniquePrices);
            $itemRanks[$item->Product_Code][$supId] = ($rankIndex !== false) ? ($rankIndex + 1) : null;
        }
    }
@endphp

<div class="card shadow-sm border-primary mt-3 mb-4" style="border-radius: 10px; border-left: 5px solid #007bff;">
    <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-balance-scale mr-2"></i>Vendor Comparison Matrix v2 (Perbandingan Penawaran Harga)
        </h6>
        <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#MatrixDiv, #PODetailDiv').html('');" title="Tutup Matrix">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <!-- INFORMASI NOMOR QUOTATION & PR NUMBER -->
        <div class="row mb-3">
            <div class="col-md-8">
                <table class="table table-borderless table-sm mb-0" style="font-size: 12.5px;">
                    <tr>
                        <td style="width: 170px;" class="font-weight-bold text-dark py-1">Quotation Number</td>
                        <td style="width: 15px;" class="py-1">:</td>
                        <td class="font-weight-bold text-primary py-1">{{ $quotationDisplay }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold text-dark py-1">PR Number</td>
                        <td class="py-1">:</td>
                        <td class="font-weight-bold text-dark py-1">{{ $prHeader->PR_No }} <small class="text-muted">({{ $prHeader->Department ?? '-' }})</small></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-4 text-md-right align-self-end">
                <span class="badge badge-danger px-2 py-1 mr-1"><i class="fas fa-arrow-down"></i> 1 = Termurah</span>
                <span class="badge badge-warning text-dark px-2 py-1 mr-1"><i class="fas fa-arrow-right"></i> 2 = Menengah</span>
                <span class="badge badge-success px-2 py-1"><i class="fas fa-arrow-up"></i> 3+ = Tertinggi</span>
            </div>
        </div>

        <!-- TABEL MATRIX PERBANDINGAN HARGA -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm bg-white mb-3" style="font-size: 12px; border: 1.5px solid #343a40;">
                <thead>
                    <!-- BARIS HEADER 1: NAMA VENDOR -->
                    <tr class="text-center" style="background-color: #f8f9fa;">
                        <th rowspan="2" class="align-middle font-weight-bold" style="width: 24%; font-size: 13px; border: 1px solid #495057;">Item</th>
                        <th rowspan="2" class="align-middle font-weight-bold" style="width: 6%; font-size: 13px; border: 1px solid #495057;">Qty</th>
                        <th rowspan="2" class="align-middle font-weight-bold" style="width: 8%; font-size: 13px; border: 1px solid #495057;">Satuan</th>
                        @foreach($rfqVendors as $vendor)
                            <th class="align-middle font-weight-bold text-dark py-2" style="font-size: 13px; border: 1px solid #495057; background-color: #eef5f9;">
                                {{ $vendor->Company_Name ?? $vendor->Supplier_ID }}
                            </th>
                        @endforeach
                    </tr>
                    <!-- BARIS HEADER 2: CODE VENDOR -->
                    <tr class="text-center" style="background-color: #ffffff;">
                        @foreach($rfqVendors as $vendor)
                            <th class="align-middle font-weight-normal text-muted py-1" style="font-size: 12px; border: 1px solid #495057;">
                                {{ $vendor->Supplier_ID }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($prItems as $item)
                    <tr>
                        <td class="align-middle font-weight-bold text-dark" style="border: 1px solid #495057;">
                            {{ $item->Product_Desc }}
                            @if(!empty($item->Specification))
                                <br><small class="text-muted font-weight-normal">{{ $item->Specification }}</small>
                            @endif
                        </td>
                        <td class="text-center align-middle" style="border: 1px solid #495057;">
                            {{ number_format($item->Qty, 0, ',', '.') }}
                        </td>
                        <td class="text-center align-middle" style="border: 1px solid #495057;">
                            {{ $item->Unit }}
                        </td>

                        @foreach($rfqVendors as $vendor)
                            @php
                                $dtl = $rfqDetails[$vendor->Supplier_ID][$item->Product_Code] ?? null;
                                $quotedPrice = $dtl ? (float) ($dtl->Quoted_Price ?? 0) : 0;
                                $rank = $itemRanks[$item->Product_Code][$vendor->Supplier_ID] ?? null;
                                $totalVendorsWithPrice = isset($itemRanks[$item->Product_Code]) ? count($itemRanks[$item->Product_Code]) : 0;
                            @endphp
                            <td class="text-right align-middle" style="border: 1px solid #495057; white-space: nowrap;">
                                @if($quotedPrice > 0)
                                    <span class="font-weight-bold text-dark mr-2" style="font-size: 12.5px;">
                                        {{ number_format($quotedPrice, 0, ',', '.') }}
                                    </span>

                                    {{-- Indikator Panah & Angka Peringkat --}}
                                    @if($rank === 1)
                                        <span class="d-inline-flex align-items-center" title="Harga Terendah (Rank 1)">
                                            <span class="badge badge-danger p-1 mr-1" style="font-size: 10px; line-height: 1;">
                                                <i class="fas fa-arrow-down"></i>
                                            </span>
                                            <strong class="text-dark">1</strong>
                                        </span>
                                    @elseif($rank === $totalVendorsWithPrice && $totalVendorsWithPrice > 2)
                                        <span class="d-inline-flex align-items-center" title="Harga Tertinggi (Rank {{ $rank }})">
                                            <span class="badge badge-success p-1 mr-1" style="font-size: 10px; line-height: 1;">
                                                <i class="fas fa-arrow-up"></i>
                                            </span>
                                            <strong class="text-dark">{{ $rank }}</strong>
                                        </span>
                                    @else
                                        <span class="d-inline-flex align-items-center" title="Harga Menengah (Rank {{ $rank }})">
                                            <span class="badge badge-warning text-white p-1 mr-1" style="font-size: 10px; line-height: 1;">
                                                <i class="fas fa-arrow-right"></i>
                                            </span>
                                            <strong class="text-dark">{{ $rank }}</strong>
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted font-italic">Belum ada harga</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 3 + count($rfqVendors) }}" class="text-center text-muted py-3">
                            Tidak ada item pada Purchase Request ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                <!-- FOOTER RINGKASAN SUBTOTAL, PPN, ONGKIR, GRAND TOTAL & TOMBOL PILIH PEMENANG (GENERATE PO) -->
                <tfoot style="background-color: #f8f9fa;">
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold" style="border: 1px solid #495057;">Subtotal (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right font-weight-bold" style="border: 1px solid #495057;">
                                {{ number_format($vendor->Subtotal_Amount ?? 0, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold" style="border: 1px solid #495057;">PPN (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right text-muted" style="border: 1px solid #495057;">
                                {{ number_format($vendor->Tax_Amount ?? 0, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold" style="border: 1px solid #495057;">Ongkos Kirim (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right text-muted" style="border: 1px solid #495057;">
                                {{ number_format($vendor->Shipping_Cost ?? 0, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr class="table-primary">
                        <td colspan="3" class="text-right font-weight-bold text-primary" style="border: 1px solid #495057; font-size: 13px;">GRAND TOTAL (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right font-weight-bold text-primary" style="border: 1px solid #495057; font-size: 13px;">
                                Rp {{ number_format($vendor->Grand_Total ?? 0, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold align-middle" style="border: 1px solid #495057;">Termin Pembayaran</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-center align-middle" style="border: 1px solid #495057;">
                                {{ $vendor->Payment_Terms ?? '-' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold align-middle" style="border: 1px solid #495057;">Keputusan / Terbitkan PO</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-center align-middle py-2" style="border: 1px solid #495057;">
                                @if($vendor->Status === 'AWARDED')
                                    <span class="badge badge-success px-3 py-1"><i class="fas fa-trophy mr-1"></i> PEMENANG (PO TERBIT)</span>
                                @elseif($vendor->Status === 'CLOSED')
                                    <span class="badge badge-secondary px-2 py-1">TIDAK TERPILIH</span>
                                @elseif(($vendor->Grand_Total ?? 0) > 0)
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm"
                                        onclick="pilihVendorGeneratePO('{{ $prHeader->PR_No }}', '{{ $vendor->RFQ_No }}', '{{ $vendor->Supplier_ID }}', '{{ addslashes($vendor->Company_Name ?? $vendor->Supplier_ID) }}')">
                                        <i class="fas fa-check-circle mr-1"></i> Pilih &amp; Buat PO
                                    </button>
                                @else
                                    <span class="text-muted font-italic">Menunggu Penawaran</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
function pilihVendorGeneratePO(prNo, rfqNo, supplierId, vendorName) {
    if (!confirm('Apakah Anda yakin memilih ' + vendorName + ' sebagai pemenang dan menerbitkan Purchase Order (PO)?')) {
        return;
    }

    $.post("{{ url('generate-po') }}", {
        _token: "{{ csrf_token() }}",
        pr_no: prNo,
        rfq_no: rfqNo,
        supplier_id: supplierId
    }, function(res) {
        if (res.status === 'success') {
            alert(res.message);
            if (typeof loadComparisonMatrix === 'function') {
                loadComparisonMatrix(prNo);
            } else {
                location.reload();
            }
        } else {
            alert(res.message || 'Gagal menerbitkan PO.');
        }
    }).fail(function(err) {
        var msg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Terjadi kesalahan saat menerbitkan PO.';
        alert(msg);
    });
}
</script>
@endif