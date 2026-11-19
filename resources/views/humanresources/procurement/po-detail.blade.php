<div class="card shadow-sm border-success mt-3 mb-4" style="border-radius: 10px; border-left: 5px solid #28a745;">
    <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-file-invoice mr-2"></i>Detail Purchase Order (PO) &mdash; {{ $poHeader->PO_No }}
        </h6>
        <div>
            <a href="{{ url('po-print') }}?PO_No={{ urlencode($poHeader->PO_No) }}" target="_blank" class="btn btn-sm btn-warning font-weight-bold py-0 px-2 mr-1">
                <i class="fas fa-print mr-1"></i> Cetak PDF PO
            </a>
            <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#PODetailDiv').html('');" title="Tutup Detail">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <!-- INFORMASI HEADER PO & VENDOR -->
        <div class="row mb-3 p-2 bg-light rounded border">
            <div class="col-md-3 mb-2">
                <span class="text-muted font-weight-bold">No. Purchase Order:</span><br>
                <span class="text-primary font-weight-bold" style="font-size: 13px;">{{ $poHeader->PO_No }}</span><br>
                <small class="text-muted">Tanggal: {{ $poHeader->PO_Date }}</small>
            </div>
            <div class="col-md-3 mb-2">
                <span class="text-muted font-weight-bold">Vendor / Supplier:</span><br>
                <span class="font-weight-bold text-dark">{{ $poHeader->Supplier }} - {{ $poHeader->Company_Name ?? 'Vendor' }}</span><br>
                <small class="text-muted">PIC Vendor: {{ $poHeader->Supplier_PIC ?? $poHeader->Contact_Person ?? '-' }}</small>
            </div>
            <div class="col-md-3 mb-2">
                <span class="text-muted font-weight-bold">Ref. PR / Department:</span><br>
                <span class="font-weight-bold">{{ $poHeader->PR_No ?? '-' }}</span><br>
                <small class="text-muted">Dept: {{ $poHeader->Department ?? '-' }} | Site: {{ $poHeader->PO_Dest ?? '-' }}</small>
            </div>
            <div class="col-md-3 mb-2 text-right">
                <span class="text-muted font-weight-bold">Status PO / Penerimaan:</span><br>
                <span class="badge badge-primary px-2 py-1">{{ $poHeader->Status ?? 'OPEN' }}</span>
                <span class="badge badge-warning px-2 py-1">Receive: {{ $poHeader->Status_Receive ?? 'OPEN' }}</span><br>
                <small class="text-muted">Termin: <strong>{{ $poHeader->Term_Payment ?? 'NET 30' }}</strong></small>
            </div>
            <div class="col-12 border-top pt-2 mt-1">
                <span class="text-muted font-weight-bold">Catatan / Referensi Dokumen:</span>
                <span class="text-dark ml-1">{{ $poHeader->Remark ?? '-' }}</span>
            </div>
        </div>

        <!-- TABEL RINCIAN ITEM PURCHASE ORDER -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm bg-white text-center" style="font-size: 11.5px;">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 4%;">No.</th>
                        <th style="width: 13%;">Kode Item</th>
                        <th style="width: 28%;">Deskripsi Barang / Jasa</th>
                        <th style="width: 8%;">Qty Order</th>
                        <th style="width: 8%;">Diterima</th>
                        <th style="width: 8%;">Sisa</th>
                        <th style="width: 7%;">Satuan</th>
                        <th style="width: 12%;">Harga Satuan (Rp)</th>
                        <th style="width: 12%;">Total Harga (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($poItems as $idx => $item)
                    <tr>
                        <td class="align-middle">{{ $idx + 1 }}</td>
                        <td class="align-middle font-weight-bold text-primary">{{ $item->Product_Code }}</td>
                        <td class="text-left align-middle">
                            <span class="font-weight-bold text-dark">{{ $item->Product_Desc }}</span>
                            @if(!empty($item->Specification))
                                <br><small class="text-muted">Spek: {{ $item->Specification }}</small>
                            @endif
                        </td>
                        <td class="align-middle font-weight-bold">{{ number_format($item->Qty, 0, ',', '.') }}</td>
                        <td class="align-middle text-success font-weight-bold">{{ number_format($item->Qty_Receive ?? 0, 0, ',', '.') }}</td>
                        <td class="align-middle text-danger font-weight-bold">{{ number_format($item->Qty_Remain ?? $item->Qty, 0, ',', '.') }}</td>
                        <td class="align-middle">{{ $item->Unit }}</td>
                        <td class="text-right align-middle">{{ number_format($item->Price ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right align-middle font-weight-bold">{{ number_format($item->Total ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-3">Tidak ada rincian item pada Purchase Order ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="text-right font-weight-bold">Subtotal (Rp):</td>
                        <td class="text-right font-weight-bold">{{ number_format($poHeader->Subtotal ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="8" class="text-right font-weight-bold">PPN (Rp):</td>
                        <td class="text-right text-muted">{{ number_format($poHeader->PPN ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="8" class="text-right font-weight-bold">Ongkos Kirim / Delivery Fee (Rp):</td>
                        <td class="text-right text-muted">{{ number_format($poHeader->Delivery_Fee ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="table-success font-weight-bold" style="font-size: 13px;">
                        <td colspan="8" class="text-right text-success">GRAND TOTAL PO (Rp):</td>
                        <td class="text-right text-success">Rp {{ number_format($poHeader->Total ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>