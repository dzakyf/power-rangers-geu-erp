@if(!$prHeader)
    <div class="alert alert-danger mt-3" style="font-size: 12px;">
        Data Purchase Request tidak ditemukan.
    </div>
@else
@php
    // Kumpulkan nomor Quotation / RFQ yang masuk
    $quotationNumbers = [];
    foreach ($rfqVendors as $v) {
        if (!empty($v->Quotation_No)) {
            $quotationNumbers[] = $v->Quotation_No . ' (' . $v->Supplier_ID . ')';
        } elseif (!empty($v->RFQ_No)) {
            $quotationNumbers[] = $v->RFQ_No;
        }
    }
    $quotationDisplay = count($quotationNumbers) > 0 ? implode(', ', $quotationNumbers) : '-';

    // Hitung peringkat harga per item (1 = Termurah, dst.)
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

<div class="card shadow-sm mt-3 mb-4" style="border-radius: 8px; border: 1px solid #2e7d32;">
    <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background-color: #1b5e20; color: #ffffff;">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            Vendor Comparison Matrix v2 (Perbandingan Penawaran Harga)
        </h6>
        <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#ComparisonDiv, #MatrixDiv, #PODetailDiv').html('');" title="Tutup Matrix">
            &times; Tutup
        </button>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <!-- INFORMASI NOMOR QUOTATION & PR NUMBER + LEGENDA PANAH -->
        <div class="row mb-3 align-items-end">
            <div class="col-md-7">
                <table class="table table-borderless table-sm mb-0" style="font-size: 13px;">
                    <tr>
                        <td style="width: 165px; color: #000;" class="font-weight-bold py-1">Quotation Number</td>
                        <td style="width: 15px; color: #000;" class="py-1">:</td>
                        <td class="font-weight-bold py-1" style="color: #1b5e20;">{{ $quotationDisplay }}</td>
                    </tr>
                    <tr>
                        <td style="color: #000;" class="font-weight-bold py-1">PR Number</td>
                        <td style="color: #000;" class="py-1">:</td>
                        <td class="font-weight-bold py-1" style="color: #000;">{{ $prHeader->PR_No }} <small class="text-muted">({{ $prHeader->Department ?? '-' }})</small></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-5 text-md-right mt-2 mt-md-0">
                <span style="display:inline-block; background:#ffebee; border:1px solid #c62828; color:#b71c1c; padding:2px 7px; border-radius:4px; font-weight:bold; font-size:11px; margin-right:4px;">
                    <span style="background:#d32f2f; color:#fff; padding:0 4px; border-radius:2px;">&#11015;</span> 1 = Termurah
                </span>
                <span style="display:inline-block; background:#fff8e1; border:1px solid #f57f17; color:#e65100; padding:2px 7px; border-radius:4px; font-weight:bold; font-size:11px; margin-right:4px;">
                    <span style="background:#fbc02d; color:#fff; padding:0 4px; border-radius:2px;">&#10145;</span> 2 = Menengah
                </span>
                <span style="display:inline-block; background:#e8f5e9; border:1px solid #2e7d32; color:#1b5e20; padding:2px 7px; border-radius:4px; font-weight:bold; font-size:11px;">
                    <span style="background:#388e3c; color:#fff; padding:0 4px; border-radius:2px;">&#11014;</span> Tertinggi
                </span>
            </div>
        </div>

        <!-- TABEL MATRIX PERBANDINGAN HARGA -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm bg-white mb-3" style="font-size: 12.5px; border: 1.5px solid #000;">
                <thead>
                    <!-- BARIS HEADER 1: NAMA VENDOR -->
                    <tr class="text-center" style="background-color: #ffffff; color: #000;">
                        <th rowspan="2" class="align-middle font-weight-bold" style="width: 24%; font-size: 13px; border: 1px solid #000; color: #000;">Item</th>
                        <th rowspan="2" class="align-middle font-weight-bold" style="width: 6%; font-size: 13px; border: 1px solid #000; color: #000;">Qty</th>
                        <th rowspan="2" class="align-middle font-weight-bold" style="width: 8%; font-size: 13px; border: 1px solid #000; color: #000;">Satuan</th>
                        @foreach($rfqVendors as $vendor)
                            <th class="align-middle font-weight-bold py-2" style="font-size: 13px; border: 1px solid #000; color: #000; background-color: #f8f9fa;">
                                {{ $vendor->Company_Name ?? $vendor->Supplier_ID }}
                            </th>
                        @endforeach
                    </tr>
                    <!-- BARIS HEADER 2: CODE VENDOR -->
                    <tr class="text-center" style="background-color: #ffffff;">
                        @foreach($rfqVendors as $vendor)
                            <th class="align-middle font-weight-normal py-1" style="font-size: 12px; border: 1px solid #000; color: #333;">
                                {{ $vendor->Supplier_ID }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($prItems as $item)
                    <tr>
                        <td class="align-middle font-weight-bold" style="border: 1px solid #000; color: #000;">
                            {{ $item->Product_Desc }}
                            @if(!empty($item->Specification))
                                <br><small style="color: #555; font-weight: normal;">{{ $item->Specification }}</small>
                            @endif
                        </td>
                        <td class="text-center align-middle" style="border: 1px solid #000; color: #000;">
                            {{ number_format($item->Qty, 0, ',', '.') }}
                        </td>
                        <td class="text-center align-middle" style="border: 1px solid #000; color: #000;">
                            {{ $item->Unit }}
                        </td>

                        @foreach($rfqVendors as $vendor)
                            @php
                                $dtl = $rfqDetails[$vendor->Supplier_ID][$item->Product_Code] ?? null;
                                $quotedPrice = $dtl ? (float) ($dtl->Quoted_Price ?? 0) : 0;
                                $rank = $itemRanks[$item->Product_Code][$vendor->Supplier_ID] ?? null;
                                $maxRank = isset($itemRanks[$item->Product_Code]) ? max($itemRanks[$item->Product_Code]) : 0;
                            @endphp
                            <td class="text-right align-middle" style="border: 1px solid #000; white-space: nowrap; color: #000;">
                                @if($quotedPrice > 0)
                                    <span style="font-weight: bold; font-size: 13px; margin-right: 6px; color: #000;">
                                        {{ number_format($quotedPrice, 0, ',', '.') }}
                                    </span>

                                    {{-- Indikator Panah Kotak & Angka Peringkat (CSS Murni) --}}
                                    @if($rank === 1)
                                        <span title="Harga Terendah (Rank 1)" style="display:inline-flex; align-items:center;">
                                            <span style="display:inline-block; width:18px; height:18px; line-height:18px; text-align:center; background-color:#d32f2f; color:#ffffff; border-radius:3px; font-size:11px; font-weight:bold; margin-right:4px;">&#11015;</span>
                                            <strong style="font-size:13px; color:#000;">1</strong>
                                        </span>
                                    @elseif($rank === $maxRank && $maxRank > 1)
                                        <span title="Harga Tertinggi (Rank {{ $rank }})" style="display:inline-flex; align-items:center;">
                                            <span style="display:inline-block; width:18px; height:18px; line-height:18px; text-align:center; background-color:#388e3c; color:#ffffff; border-radius:3px; font-size:11px; font-weight:bold; margin-right:4px;">&#11014;</span>
                                            <strong style="font-size:13px; color:#000;">{{ $rank }}</strong>
                                        </span>
                                    @else
                                        <span title="Harga Menengah (Rank {{ $rank }})" style="display:inline-flex; align-items:center;">
                                            <span style="display:inline-block; width:18px; height:18px; line-height:18px; text-align:center; background-color:#fbc02d; color:#ffffff; border-radius:3px; font-size:11px; font-weight:bold; margin-right:4px;">&#10145;</span>
                                            <strong style="font-size:13px; color:#000;">{{ $rank }}</strong>
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
                        <td colspan="{{ 3 + count($rfqVendors) }}" class="text-center text-muted py-3" style="border: 1px solid #000;">
                            Tidak ada item pada Purchase Request ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                <!-- FOOTER RINGKASAN SUBTOTAL, PPN, ONGKIR, GRAND TOTAL & TOMBOL PILIH PEMENANG -->
                <tfoot style="background-color: #f8f9fa;">
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold" style="border: 1px solid #000; color: #000;">Subtotal (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right font-weight-bold" style="border: 1px solid #000; color: #000;">
                                {{ number_format($vendor->Subtotal_Amount ?? 0, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold" style="border: 1px solid #000; color: #000;">PPN (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right" style="border: 1px solid #000; color: #444;">
                                {{ number_format($vendor->Tax_Amount ?? 0, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold" style="border: 1px solid #000; color: #000;">Ongkos Kirim (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right" style="border: 1px solid #000; color: #444;">
                                {{ number_format($vendor->Shipping_Cost ?? 0, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr style="background-color: #c8e6c9;">
                        <td colspan="3" class="text-right font-weight-bold" style="border: 1px solid #000; font-size: 13px; color: #1b5e20;">GRAND TOTAL (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right font-weight-bold" style="border: 1px solid #000; font-size: 13px; color: #1b5e20;">
                                Rp {{ number_format($vendor->Grand_Total ?? 0, 0, ',', '.') }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold align-middle" style="border: 1px solid #000; color: #000;">Termin Pembayaran</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-center align-middle" style="border: 1px solid #000; color: #000;">
                                {{ $vendor->Payment_Terms ?? '-' }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold align-middle" style="border: 1px solid #000; color: #000;">Keputusan / Terbitkan PO</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-center align-middle py-2" style="border: 1px solid #000;">
                                @if($vendor->Status === 'AWARDED')
                                    <span style="display:inline-block; background-color:#2e7d32; color:#ffffff; font-weight:bold; padding:4px 12px; border-radius:4px; font-size:11.5px;">
                                        &#9733; PEMENANG (PO TERBIT)
                                    </span>
                                @elseif($vendor->Status === 'CLOSED')
                                    <span style="display:inline-block; background-color:#757575; color:#ffffff; font-weight:bold; padding:4px 10px; border-radius:4px; font-size:11px;">
                                        TIDAK TERPILIH
                                    </span>
                                @elseif(($vendor->Grand_Total ?? 0) > 0)
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm"
                                        onclick="pilihVendorGeneratePO('{{ $prHeader->PR_No }}', '{{ $vendor->RFQ_No }}', '{{ $vendor->Supplier_ID }}', '{{ addslashes($vendor->Company_Name ?? $vendor->Supplier_ID) }}')">
                                        &#10004; Pilih &amp; Buat PO
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
            if (typeof openComparisonMatrix === 'function') {
                openComparisonMatrix(prNo, 2);
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