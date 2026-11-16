<div class="card shadow-sm border-primary mt-3 mb-4" style="border-radius: 10px; border-left: 5px solid #28a745;">
    <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-balance-scale mr-2"></i>Vendor Comparison Matrix (Matriks Perbandingan Harga) &mdash; PR: {{ $prHeader->PR_No }}
        </h6>
        <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#ComparisonDiv').html('');" title="Tutup Matrix">
            <i class="fas fa-times"></i> Tutup
        </button>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <!-- INFORMASI RINGKAS PR -->
        <div class="row mb-3 p-2 bg-light rounded border">
            <div class="col-md-3">
                <span class="text-muted font-weight-bold">No. Purchase Request:</span><br>
                <span class="text-primary font-weight-bold" style="font-size: 13px;">{{ $prHeader->PR_No }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted font-weight-bold">Department / Tanggal:</span><br>
                <span>{{ $prHeader->Department }} / {{ $prHeader->PR_Date }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted font-weight-bold">Estimasi Budget PR:</span><br>
                <span class="text-secondary font-weight-bold">Rp {{ number_format($prHeader->Estimated_Budget ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="col-md-3 text-right">
                <span class="text-muted font-weight-bold">Total Vendor Merespons:</span><br>
                <span class="badge badge-success px-2 py-1" style="font-size: 12px;">{{ $rfqVendors->count() }} Vendor</span>
            </div>
        </div>

        @if($rfqVendors->isEmpty())
            <div class="alert alert-warning text-center">Belum ada RFQ atau vendor yang dikaitkan dengan PR ini.</div>
        @else
            <!-- TABEL MATRIKS PERBANDINGAN SIDE-BY-SIDE -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm bg-white text-center" style="font-size: 11.5px;">
                    <thead class="thead-dark">
                        <tr>
                            <th rowspan="2" class="align-middle" style="width: 22%;">Item Kebutuhan (PR)</th>
                            <th rowspan="2" class="align-middle" style="width: 6%;">Qty</th>
                            <th rowspan="2" class="align-middle" style="width: 6%;">Satuan</th>
                            @foreach($rfqVendors as $vendor)
                                <th colspan="2" class="border-left text-uppercase bg-primary text-white">
                                    {{ $vendor->Supplier_ID }}<br>
                                    <small style="font-size: 10px; font-weight: normal;">{{ $vendor->Company_Name ?? 'Vendor' }}</small>
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($rfqVendors as $vendor)
                                <th class="border-left bg-light text-dark" style="width: 14%;">Harga Satuan (Rp)</th>
                                <th class="bg-light text-dark" style="width: 15%;">Total Harga (Rp)</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prItems as $item)
                        <tr>
                            <td class="text-left align-middle">
                                <span class="font-weight-bold text-primary">{{ $item->Product_Code }}</span><br>
                                <span class="text-dark">{{ $item->Product_Desc }}</span>
                            </td>
                            <td class="align-middle font-weight-bold">{{ $item->Qty }}</td>
                            <td class="align-middle">{{ $item->Unit }}</td>

                            @foreach($rfqVendors as $vendor)
                                @php
                                    $detail = $rfqDetails[$vendor->Supplier_ID][$item->Product_Code] ?? null;
                                    $quotedPrice = $detail ? $detail->Quoted_Price : 0;
                                    $lineTotal = $detail ? $detail->Total_Price : 0;
                                @endphp
                                <td class="border-left text-right align-middle">
                                    {{ $quotedPrice > 0 ? number_format($quotedPrice, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-right align-middle font-weight-bold">
                                    {{ $lineTotal > 0 ? number_format($lineTotal, 0, ',', '.') : '-' }}
                                </td>
                            @endforeach
                        </tr>
                        @endforeach

                        <!-- RINGKASAN KOMERSIAL DI BAWAH MATRIKS -->
                        <tr class="table-secondary font-weight-bold">
                            <td colspan="3" class="text-right align-middle">No. Surat Quotation:</td>
                            @foreach($rfqVendors as $vendor)
                                <td colspan="2" class="border-left text-center align-middle text-primary">
                                    {{ $vendor->Quotation_No ?? '-' }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right align-middle font-weight-bold">Termin Pembayaran:</td>
                            @foreach($rfqVendors as $vendor)
                                <td colspan="2" class="border-left text-center align-middle">
                                    {{ $vendor->Payment_Terms ?? '-' }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right align-middle font-weight-bold">Subtotal Penawaran (Rp):</td>
                            @foreach($rfqVendors as $vendor)
                                <td colspan="2" class="border-left text-right align-middle">
                                    {{ number_format($vendor->Subtotal_Amount ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right align-middle font-weight-bold">PPN & Ongkir (Rp):</td>
                            @foreach($rfqVendors as $vendor)
                                <td colspan="2" class="border-left text-right align-middle text-muted">
                                    PPN: {{ number_format($vendor->Tax_Amount ?? 0, 0, ',', '.') }}<br>
                                    Ongkir: {{ number_format($vendor->Shipping_Cost ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                        </tr>
                        <tr class="table-success font-weight-bold" style="font-size: 13px;">
                            <td colspan="3" class="text-right align-middle text-success">GRAND TOTAL PENAWARAN (Rp):</td>
                            @foreach($rfqVendors as $vendor)
                                <td colspan="2" class="border-left text-right align-middle text-success">
                                    Rp {{ number_format($vendor->Grand_Total ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right align-middle font-weight-bold">Aksi Pilihan Vendor (Awarding PO):</td>
                            @foreach($rfqVendors as $vendor)
                                <td colspan="2" class="border-left text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-success font-weight-bold shadow-sm py-1 px-3" onclick="alert('Fitur Generate PO untuk Vendor {{ $vendor->Supplier_ID }} akan segera diproses!');">
                                        <i class="fas fa-check-circle mr-1"></i> Pilih Vendor Ini (PO)
                                    </button>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>