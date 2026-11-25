@if(!$prHeader)
    <div class="alert alert-danger mt-3" style="font-size: 12px;">
        Data Purchase Request tidak ditemukan.
    </div>
@else
@php
    // 1. Kumpulkan nomor Quotation
    $quotationNumbers = [];
    foreach ($rfqVendors as $v) {
        if (!empty($v->Quotation_No)) {
            $quotationNumbers[] = $v->Quotation_No . ' (' . $v->Supplier_ID . ')';
        } elseif (!empty($v->RFQ_No)) {
            $quotationNumbers[] = $v->RFQ_No;
        }
    }
    $quotationDisplay = count($quotationNumbers) > 0 ? implode(', ', $quotationNumbers) : '-';

   // 2. Cek item mana saja di PR ini yang SUDAH dibuatkan PO AKTIF (abaikan PO yang CANCELLED)
    $orderedItemsRaw = \Illuminate\Support\Facades\DB::table('purchase_order_dtl as pod')
        ->join('purchase_order as po', 'pod.PO_No', '=', 'po.PO_No')
        ->select('pod.Product_Code', 'pod.PO_No', 'po.Supplier')
        ->where('pod.PR_No', $prHeader->PR_No)
        ->where('po.Status', '!=', 'CANCELLED')
        ->get();

    $orderedMap = []; // [Product_Code => ['PO_No' => ..., 'Supplier' => ...]]
    foreach ($orderedItemsRaw as $ord) {
        $orderedMap[$ord->Product_Code] = [
            'PO_No'    => $ord->PO_No,
            'Supplier' => $ord->Supplier
        ];
    }

    // 3. Hitung peringkat harga per item (1 = Termurah, dst.)
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

<div class="card shadow-sm mt-3 mb-4" style="border-radius: 8px; border: 1.5px solid #0d47a1;">
    <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background-color: #0d47a1; color: #ffffff;">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            Vendor Comparison Matrix v3 &mdash; Split PO (1 PR Bisa Banyak PO per Item Terpilih)
        </h6>
        <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#ComparisonDiv, #MatrixDiv, #PODetailDiv').html('');" title="Tutup Matrix">
            &times; Tutup
        </button>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <!-- INFO PR & STATUS SPLIT PO -->
        <div class="row mb-3 align-items-end">
            <div class="col-md-7">
                <table class="table table-borderless table-sm mb-0" style="font-size: 12.5px;">
                    <tr>
                        <td style="width: 165px; color: #000;" class="font-weight-bold py-1">Quotation Number</td>
                        <td style="width: 15px; color: #000;" class="py-1">:</td>
                        <td class="font-weight-bold py-1" style="color: #0d47a1;">{{ $quotationDisplay }}</td>
                    </tr>
                    <tr>
                        <td style="color: #000;" class="font-weight-bold py-1">PR Number / Status</td>
                        <td style="color: #000;" class="py-1">:</td>
                        <td class="font-weight-bold py-1" style="color: #000;">
                            {{ $prHeader->PR_No }}
                            <span class="badge badge-info ml-1">{{ $prHeader->Status }}</span>
                            <small class="text-muted ml-1">({{ count($orderedMap) }} / {{ count($prItems) }} Item Sudah Jadi PO)</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-5 text-md-right mt-2 mt-md-0">
                <button type="button" class="btn btn-xs btn-outline-danger font-weight-bold mr-1" onclick="autoCentangTermurah()" title="Otomatis centang harga termurah (Rank 1) untuk semua item yang belum punya PO">
                    &#9889; Auto-Pilih Harga Termurah (Rank 1)
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold" onclick="resetSemuaCentang()">
                    Reset Centang
                </button>
            </div>
        </div>

        <div class="alert alert-info py-1 px-3 mb-2" style="font-size: 11.5px;">
            <strong>Panduan Split PO:</strong> Centang kotak di sebelah harga vendor pada item yang ingin dibeli dari vendor tersebut, lalu klik tombol <strong>"Buat PO Terpilih"</strong> di bagian bawah kolom vendor. Ulangi untuk vendor berikutnya pada sisa item.
        </div>

        @php
            $activePosList = \Illuminate\Support\Facades\DB::table('purchase_order as po')
                ->leftJoin('supplier as s', \Illuminate\Support\Facades\DB::raw('po.Supplier COLLATE utf8mb4_unicode_ci'), '=', \Illuminate\Support\Facades\DB::raw('s.Supplier_ID COLLATE utf8mb4_unicode_ci'))
                ->select('po.PO_No', 'po.Supplier', 'po.Total', 'po.Status', 's.Company_Name')
                ->where('po.PR_No', $prHeader->PR_No)
                ->where('po.Status', '!=', 'CANCELLED')
                ->orderBy('po.ID', 'ASC')
                ->get();
        @endphp

        @if(count($activePosList) > 0)
        <div class="mb-2 p-2 rounded" style="background-color: #f1f8e9; border: 1px solid #aed581;">
            <strong style="font-size: 11.5px; color: #33691e;">PO Aktif Terbit dari PR Ini:</strong>
            <div class="d-flex flex-wrap mt-1">
                @foreach($activePosList as $apo)
                    <div class="d-inline-flex align-items-center bg-white border rounded px-2 py-1 mr-2 mb-1 shadow-sm" style="font-size: 11px;">
                        <span class="font-weight-bold text-success mr-1">{{ $apo->PO_No }}</span>
                        <span class="text-muted mr-2">({{ $apo->Company_Name ?? $apo->Supplier }} &bull; Rp {{ number_format($apo->Total, 0, ',', '.') }})</span>
                        <a href="{{ url('po-print') }}?PO_No={{ urlencode($apo->PO_No) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-1 mr-1" title="Print PDF PO">
                            Print
                        </a>
                        <button type="button" class="btn btn-xs btn-danger py-0 px-1" onclick="batalkanPO('{{ $prHeader->PR_No }}', '{{ $apo->PO_No }}')" title="Batalkan PO ini agar item kembali ke awal">
                            &times; Cancel PO
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- TABEL MATRIX SPLIT PO -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm bg-white mb-3" style="font-size: 12.5px; border: 1.5px solid #000;">
                <thead>
                    <tr class="text-center" style="background-color: #ffffff; color: #000;">
                        <th rowspan="2" class="align-middle font-weight-bold" style="width: 23%; font-size: 13px; border: 1px solid #000; color: #000;">Item</th>
                        <th rowspan="2" class="align-middle font-weight-bold" style="width: 5%; font-size: 13px; border: 1px solid #000; color: #000;">Qty</th>
                        <th rowspan="2" class="align-middle font-weight-bold" style="width: 7%; font-size: 13px; border: 1px solid #000; color: #000;">Satuan</th>
                        @foreach($rfqVendors as $vendor)
                            <th class="align-middle font-weight-bold py-2" style="font-size: 13px; border: 1px solid #000; color: #000; background-color: #e3f2fd;">
                                {{ $vendor->Company_Name ?? $vendor->Supplier_ID }}
                            </th>
                        @endforeach
                    </tr>
                    <tr class="text-center" style="background-color: #ffffff;">
                        @foreach($rfqVendors as $vendor)
                            <th class="align-middle font-weight-normal py-1" style="font-size: 12px; border: 1px solid #000; color: #333;">
                                {{ $vendor->Supplier_ID }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($prItems as $item)
                        @php
                            $isItemOrdered = isset($orderedMap[$item->Product_Code]);
                            $orderedInfo   = $isItemOrdered ? $orderedMap[$item->Product_Code] : null;
                        @endphp
                    <tr style="{{ $isItemOrdered ? 'background-color: #f4f6f8;' : '' }}">
                        <td class="align-middle font-weight-bold" style="border: 1px solid #000; color: #000;">
                            {{ $item->Product_Desc }}
                            @if($isItemOrdered)
                                <br><span style="display:inline-block; background:#2e7d32; color:#fff; font-size:10px; padding:1px 6px; border-radius:3px; margin-top:2px;">
                                    &#10004; Sudah PO: {{ $orderedInfo['PO_No'] }}
                                </span>
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
                                $lineTotal   = $dtl ? (float) ($dtl->Total_Price > 0 ? $dtl->Total_Price : ($item->Qty * $quotedPrice)) : 0;
                                $rank = $itemRanks[$item->Product_Code][$vendor->Supplier_ID] ?? null;
                                $maxRank = isset($itemRanks[$item->Product_Code]) ? max($itemRanks[$item->Product_Code]) : 0;
                                $wonByThisVendor = $isItemOrdered && ($orderedInfo['Supplier'] === $vendor->Supplier_ID);
                            @endphp
                            <td class="text-right align-middle" style="border: 1px solid #000; white-space: nowrap; {{ $wonByThisVendor ? 'background-color: #e8f5e9;' : '' }}">
                                @if($quotedPrice > 0)
                                    <div class="d-flex justify-content-end align-items-center">
                                        {{-- Checkbox jika item belum pernah dibuatkan PO --}}
                                        @if(!$isItemOrdered)
                                            <input type="checkbox"
                                                class="chk-split-po mr-2"
                                                style="width: 16px; height: 16px; cursor: pointer;"
                                                data-supplier="{{ $vendor->Supplier_ID }}"
                                                data-product="{{ $item->Product_Code }}"
                                                data-rank="{{ $rank }}"
                                                data-linetotal="{{ $lineTotal }}"
                                                data-taxpct="{{ $vendor->Tax_Percent ?? 11 }}"
                                                title="Centang untuk membeli item ini dari {{ $vendor->Company_Name }}">
                                        @elseif($wonByThisVendor)
                                            <span style="color:#2e7d32; font-weight:bold; font-size:11px; margin-right:6px;" title="Dibeli dari vendor ini">&#9733; PO</span>
                                        @endif

                                        <span style="font-weight: bold; font-size: 13px; margin-right: 6px; color: #000;">
                                            {{ number_format($quotedPrice, 0, ',', '.') }}
                                        </span>

                                       {{-- Indikator Panah Kotak --}}
                                            @if($rank === 1)
                                                <span style="display:inline-flex; align-items:center;" title="Harga Termurah">
                                                    <span style="display:inline-block; width:18px; height:18px; line-height:18px; text-align:center; background-color:#388e3c; color:#ffffff; border-radius:3px; font-size:11px; font-weight:bold; margin-right:4px;">&#11015;</span>
                                                    <strong style="font-size:13px; color:#000;">1</strong>
                                                </span>
                                            @elseif($rank === $maxRank && $maxRank > 1)
                                                <span style="display:inline-flex; align-items:center;" title="Harga Termahal">
                                                    <span style="display:inline-block; width:18px; height:18px; line-height:18px; text-align:center; background-color:#d32f2f; color:#ffffff; border-radius:3px; font-size:11px; font-weight:bold; margin-right:4px;">&#11014;</span>
                                                    <strong style="font-size:13px; color:#000;">{{ $rank }}</strong>
                                                </span>
                                            @else
                                                <span style="display:inline-flex; align-items:center;" title="Harga Menengah">
                                                    <span style="display:inline-block; width:18px; height:18px; line-height:18px; text-align:center; background-color:#fbc02d; color:#ffffff; border-radius:3px; font-size:11px; font-weight:bold; margin-right:4px;">&#10145;</span>
                                                    <strong style="font-size:13px; color:#000;">{{ $rank }}</strong>
                                                </span>
                                            @endif
                                    </div>
                                @else
                                    <span class="text-muted font-italic">Belum ada harga</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>

                <!-- FOOTER RINGKASAN DINAMIS BERDASARKAN ITEM YANG DICENTANG PER VENDOR -->
                <tfoot style="background-color: #f8f9fa;">
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold" style="border: 1px solid #000; color: #000;">Subtotal Item Dicentang (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right font-weight-bold" id="sub-selected-{{ $vendor->Supplier_ID }}" style="border: 1px solid #000; color: #000;">
                                0
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold" style="border: 1px solid #000; color: #000;">Estimasi PPN (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right" id="tax-selected-{{ $vendor->Supplier_ID }}" style="border: 1px solid #000; color: #444;">
                                0
                            </td>
                        @endforeach
                    </tr>
                    <tr style="background-color: #e3f2fd;">
                        <td colspan="3" class="text-right font-weight-bold" style="border: 1px solid #000; font-size: 13px; color: #0d47a1;">TOTAL PO TERPILIH (Rp)</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-right font-weight-bold" id="total-selected-{{ $vendor->Supplier_ID }}" style="border: 1px solid #000; font-size: 13px; color: #0d47a1;">
                                Rp 0
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right font-weight-bold align-middle" style="border: 1px solid #000; color: #000;">Terbitkan Split PO</td>
                        @foreach($rfqVendors as $vendor)
                            <td class="text-center align-middle py-2" style="border: 1px solid #000;">
                                <button type="button"
                                    id="btn-split-po-{{ $vendor->Supplier_ID }}"
                                    class="btn btn-primary btn-sm font-weight-bold shadow-sm"
                                    disabled
                                    onclick="buatSplitPO('{{ $prHeader->PR_No }}', '{{ $vendor->RFQ_No }}', '{{ $vendor->Supplier_ID }}', '{{ addslashes($vendor->Company_Name ?? $vendor->Supplier_ID) }}')">
                                    &#10004; Buat PO (0 Item)
                                </button>
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
// 1. Cegah 1 item dicentang di 2 vendor berbeda secara bersamaan & hitung ulang total per vendor
$(document).off('change', '.chk-split-po').on('change', '.chk-split-po', function() {
    var productCode = $(this).data('product');
    if ($(this).is(':checked')) {
        // Uncheck vendor lain untuk item yang sama
        $('.chk-split-po[data-product="' + productCode + '"]').not(this).prop('checked', false);
    }
    hitungTotalSplitPO();
});

function hitungTotalSplitPO() {
    var vendorSummary = {};

    // Inisialisasi semua vendor dari tombol Buat PO yang ada di footer
    $('[id^="btn-split-po-"]').each(function() {
        var supId = $(this).attr('id').replace('btn-split-po-', '');
        vendorSummary[supId] = { count: 0, subtotal: 0, taxpct: 11 };
    });

    $('.chk-split-po').each(function() {
        var supId = $(this).attr('data-supplier');
        if (!vendorSummary[supId]) {
            vendorSummary[supId] = { count: 0, subtotal: 0, taxpct: 11 };
        }
        vendorSummary[supId].taxpct = parseFloat($(this).attr('data-taxpct')) || 11;

        if ($(this).is(':checked')) {
            vendorSummary[supId].count += 1;
            vendorSummary[supId].subtotal += parseFloat($(this).attr('data-linetotal')) || 0;
        }
    });

    $.each(vendorSummary, function(supId, data) {
        var taxVal   = data.subtotal * (data.taxpct / 100);
        var grandVal = data.subtotal + taxVal;

        $(document.getElementById('sub-selected-' + supId)).text(Math.round(data.subtotal).toLocaleString('id-ID'));
        $(document.getElementById('tax-selected-' + supId)).text(Math.round(taxVal).toLocaleString('id-ID'));
        $(document.getElementById('total-selected-' + supId)).text('Rp ' + Math.round(grandVal).toLocaleString('id-ID'));

        var btn = $(document.getElementById('btn-split-po-' + supId));
        if (data.count > 0) {
            btn.prop('disabled', false).html('&#10004; Buat PO (' + data.count + ' Item)');
        } else {
            btn.prop('disabled', true).html('&#10004; Buat PO (0 Item)');
        }
    });
}

function batalkanPO(prNo, poNo) {
    var alasan = prompt('Masukkan alasan pembatalan untuk ' + poNo + ':\n(Item di dalam PO ini akan dikembalikan agar bisa dipilih ulang di Matrix)');
    if (alasan === null) {
        return; // User menekan tombol Cancel pada prompt
    }

    $.post("{{ url('cancel-po') }}", {
        _token: "{{ csrf_token() }}",
        po_no: poNo,
        cancel_reason: alasan || 'Dibatalkan melalui Matrix v3'
    }, function(res) {
        if (res.status === 'success') {
            alert(res.message);
            if (typeof openComparisonMatrix === 'function') {
                openComparisonMatrix(prNo, 3);
            } else {
                location.reload();
            }
        } else {
            alert(res.message || 'Gagal membatalkan PO.');
        }
    }).fail(function(err) {
        var msg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Terjadi kesalahan saat membatalkan PO.';
        alert(msg);
    });
}




// 2. Fitur Cepat: Auto-centang semua harga termurah (Rank 1)
function autoCentangTermurah() {
    resetSemuaCentang();
    var handledProducts = {};
    $('.chk-split-po[data-rank="1"]').each(function() {
        var prod = $(this).data('product');
        if (!handledProducts[prod]) {
            $(this).prop('checked', true);
            handledProducts[prod] = true;
        }
    });
    hitungTotalSplitPO();
}

function resetSemuaCentang() {
    $('.chk-split-po').prop('checked', false);
    hitungTotalSplitPO();
}

// 3. Eksekusi Pembuatan Split PO ke Controller
function buatSplitPO(prNo, rfqNo, supplierId, vendorName) {
    var selectedProducts = [];
    $('.chk-split-po[data-supplier="' + supplierId + '"]:checked').each(function() {
        selectedProducts.push($(this).data('product'));
    });

    if (selectedProducts.length === 0) {
        alert('Silakan centang minimal 1 item untuk ' + vendorName + '!');
        return;
    }

    if (!confirm('Terbitkan Split PO untuk ' + vendorName + ' sebanyak ' + selectedProducts.length + ' item terpilih?')) {
        return;
    }

    $.post("{{ url('generate-split-po') }}", {
        _token: "{{ csrf_token() }}",
        pr_no: prNo,
        rfq_no: rfqNo,
        supplier_id: supplierId,
        product_codes: selectedProducts
    }, function(res) {
        if (res.status === 'success') {
            alert(res.message);
            // Refresh Matrix v3 agar item yang sudah jadi PO langsung terkunci & sisa item bisa dibuatkan PO ke vendor lain
            if (typeof openComparisonMatrix === 'function') {
                openComparisonMatrix(prNo, 3);
            } else {
                location.reload();
            }
        } else {
            alert(res.message || 'Gagal menerbitkan Split PO.');
        }
    }).fail(function(err) {
        var msg = (err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Terjadi kesalahan saat menerbitkan Split PO.';
        alert(msg);
    });
}
</script>
@endif