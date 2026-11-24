@php
    $proofPath = null;
    if (!empty($grHeader->Remark) && preg_match('/Bukti:\s*(uploads\/goods_received\/[^\s|\]]+)/i', $grHeader->Remark, $matches)) {
        $proofPath = trim($matches[1]);
    }
@endphp

<div class="card border-info mt-3 shadow-sm" style="border-radius: 8px;">
    <div class="card-header bg-info text-white py-2 d-flex justify-content-between align-items-center">
        <span class="font-weight-bold" style="font-size: 12.5px;">
            <i class="fas fa-list-alt mr-1"></i> Rincian Barang Diterima &mdash; {{ $grHeader->GR_No }}
        </span>
        <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#GRDetailDiv').html('');">
            &times; Tutup
        </button>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <!-- INFORMASI HEADER GOODS RECEIVED -->
        <div class="row mb-3 p-2 bg-light rounded border">
            <div class="col-md-3 mb-2 mb-md-0">
                <small class="text-muted font-weight-bold">No. GR / Tanggal Masuk:</small><br>
                <strong class="text-info" style="font-size: 13px;">{{ $grHeader->GR_No }}</strong><br>
                <span>Masuk: {{ $grHeader->Incoming_Date ?? '-' }}</span>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <small class="text-muted font-weight-bold">Referensi PO / PR:</small><br>
                <strong>PO: {{ $grHeader->PO_No ?? '-' }}</strong><br>
                <span>PR: {{ $grHeader->PR_No ?? '-' }}</span>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <small class="text-muted font-weight-bold">Vendor / Lokasi:</small><br>
                <strong>{{ $grHeader->Supplier ? ($grHeader->Supplier . ' - ' . ($grHeader->Company_Name ?? '')) : '-' }}</strong><br>
                <span>Lokasi: {{ $grHeader->Location ?? '-' }} | Dept: {{ $grHeader->Department ?? '-' }}</span>
            </div>
            <div class="col-md-3 text-md-right">
                <small class="text-muted font-weight-bold">Status / Referensi SJ:</small><br>
                <span class="badge badge-success">{{ $grHeader->Status ?? 'OPEN' }}</span><br>
                <span>Ref / SJ: <strong>{{ $grHeader->Reference ?? '-' }}</strong></span>
            </div>
            @if(!empty($grHeader->Remark))
            <div class="col-12 border-top pt-2 mt-2">
                <small class="text-muted font-weight-bold">Catatan:</small>
                <span class="text-dark ml-1">{{ $grHeader->Remark }}</span>
            </div>
            @endif

            @if(!empty($proofPath))
            <div class="col-12 border-top pt-2 mt-2">
                <small class="text-muted font-weight-bold d-block mb-1">Foto Bukti Penerima Barang:</small>
                <div class="d-flex align-items-center flex-wrap">
                    <a href="{{ asset($proofPath) }}" target="_blank" title="Klik untuk melihat ukuran penuh">
                        <img src="{{ asset($proofPath) }}"
                             alt="Bukti Penerima Barang {{ $grHeader->GR_No }}"
                             class="img-thumbnail mr-3 mb-1"
                             style="max-height: 150px; max-width: 240px; object-fit: cover; cursor: pointer;">
                    </a>
                    <div class="mb-1">
                        <div class="text-muted mb-1" style="font-size: 11px;">File: {{ basename($proofPath) }}</div>
                        <a href="{{ asset($proofPath) }}" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2">
                            <i class="fas fa-external-link-alt mr-1"></i> Buka Foto Penuh
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- TABEL RINCIAN ITEM (good_receive_dtl) -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0 text-center" style="font-size: 11.5px;">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 4%;">No</th>
                        <th style="width: 13%;">Product Code</th>
                        <th style="width: 23%;">Deskripsi Item</th>
                        <th style="width: 11%;">Barcode / SN</th>
                        <th style="width: 9%;">Qty PO</th>
                        <th style="width: 10%;">Qty Terima</th>
                        <th style="width: 7%;">Satuan</th>
                        <th style="width: 11%;">Value PO (Rp)</th>
                        <th style="width: 12%;">Value Terima (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sumQtyRec = 0;
                        $sumValRec = 0;
                    @endphp
                    @forelse($grItems as $idx => $d)
                        @php
                            $qtyRec = floatval($d->Qty_Receive ?? 0);
                            $valRec = floatval($d->Value_Receive ?? 0);
                            $sumQtyRec += $qtyRec;
                            $sumValRec += $valRec;
                        @endphp
                        <tr>
                            <td class="align-middle">{{ $idx + 1 }}</td>
                            <td class="align-middle font-weight-bold text-primary">{{ $d->Product_Code }}</td>
                            <td class="text-left align-middle">{{ $d->Product_Desc ?? $d->Product_Code }}</td>
                            <td class="align-middle">{{ $d->Barcode ?: ($d->SN ?: '-') }}</td>
                            <td class="align-middle">{{ number_format($d->Qty_PO ?? 0, 0, ',', '.') }}</td>
                            <td class="align-middle font-weight-bold text-success">{{ number_format($qtyRec, 0, ',', '.') }}</td>
                            <td class="align-middle">{{ $d->Unit ?? 'PCS' }}</td>
                            <td class="text-right align-middle">{{ number_format($d->Value_PO ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right align-middle font-weight-bold text-success">{{ number_format($valRec, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">Tidak ada rincian item pada Goods Received ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($grItems) > 0)
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td colspan="5" class="text-right">Total Diterima:</td>
                        <td class="text-success">{{ number_format($sumQtyRec, 0, ',', '.') }}</td>
                        <td colspan="2" class="text-right">Total Nilai Diterima (Rp):</td>
                        <td class="text-right text-success">Rp {{ number_format($sumValRec, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>