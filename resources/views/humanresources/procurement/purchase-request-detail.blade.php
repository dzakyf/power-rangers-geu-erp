@if(!$header)
    <div class="alert alert-danger mt-3" style="font-size: 12px;">
        <i class="fas fa-exclamation-triangle mr-1"></i> Data Purchase Request tidak ditemukan.
    </div>
@else
@php
    // Ambil data PO terkait PR ini (jika sudah dibuatkan PO)
    $relatedPo = \Illuminate\Support\Facades\DB::table('purchase_order')
        ->where('PR_No', $header->PR_No)
        ->orderBy('ID', 'DESC')
        ->first();

    // Tentukan warna badge & border berdasarkan status PR
    $statusUpper = strtoupper($header->Status ?? 'PENDING');
    $borderColor = '#17a2b8'; // default info
    $headerClass = 'bg-info text-white';
    if ($statusUpper === 'APPROVED' || $statusUpper === 'PO_CREATED') {
        $borderColor = '#28a745';
        $headerClass = 'bg-success text-white';
    } elseif ($statusUpper === 'REJECTED') {
        $borderColor = '#dc3545';
        $headerClass = 'bg-danger text-white';
    } elseif ($statusUpper === 'PENDING' || $statusUpper === 'WAITING_APPROVAL') {
        $borderColor = '#ffc107';
        $headerClass = 'bg-warning text-dark';
    }

    // Ambil nama department jika tersimpan sebagai ID
    $deptDisplay = $header->Department;
    if (isset($departments)) {
        foreach ($departments as $dept) {
            if ($dept->Department_ID == $header->Department || $dept->Department == $header->Department) {
                $deptDisplay = $dept->Department . ' (' . $dept->Department_ID . ')';
                break;
            }
        }
    }
@endphp

<div class="card shadow-sm mt-3 mb-4" style="border-radius: 10px; border-left: 5px solid {{ $borderColor }};">
    <!-- CARD HEADER + TOMBOL PRINT PR & PRINT PO -->
    <div class="card-header {{ $headerClass }} py-2 d-flex justify-content-between align-items-center flex-wrap">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-file-alt mr-2"></i>Detail Purchase Request (Status: {{ $header->Status }}) &mdash; {{ $header->PR_No }}
        </h6>
        <div class="d-flex align-items-center mt-1 mt-md-0">
            <!-- Tombol Print PR -->
            <a href="{{ url('print-pr') }}?PR_No={{ urlencode($header->PR_No) }}" target="_blank" class="btn btn-sm btn-light text-dark font-weight-bold py-0 px-2 mr-1 shadow-sm" title="Cetak Dokumen Purchase Request">
                <i class="fas fa-print text-primary mr-1"></i> Print PR
            </a>

            <!-- Tombol Print PO (Aktif jika PO sudah terbit) -->
            @if($relatedPo)
                <a href="{{ url('po-print') }}?PO_No={{ urlencode($relatedPo->PO_No) }}" target="_blank" class="btn btn-sm btn-warning text-dark font-weight-bold py-0 px-2 mr-2 shadow-sm" title="Cetak Dokumen Purchase Order ({{ $relatedPo->PO_No }})">
                    <i class="fas fa-print mr-1"></i> Print PO ({{ $relatedPo->PO_No }})
                </a>
            @else
                <button type="button" class="btn btn-sm btn-secondary py-0 px-2 mr-2" disabled title="Purchase Order belum diterbitkan untuk PR ini">
                    <i class="fas fa-print mr-1"></i> Print PO (Belum Ada PO)
                </button>
            @endif

            <!-- Tombol Tutup -->
            <button type="button" class="btn btn-sm btn-dark py-0 px-2" onclick="$('#PRDiv').html('');" title="Tutup Detail">
                <i class="fas fa-times"></i> Tutup
            </button>
        </div>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <!-- HEADER INFORMASI PR UTAMA (READ-ONLY) -->
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-muted mb-1">Nomor PR:</label>
                <input type="text" class="form-control form-control-sm font-weight-bold text-primary bg-light" value="{{ $header->PR_No }}" readonly>
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-muted mb-1">Tanggal PR:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->PR_Date }}" readonly>
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-muted mb-1">Department:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $deptDisplay }}" readonly>
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-muted mb-1">Location:</label>
                <input type="text" class="form-control form-control-sm bg-light font-weight-bold" value="{{ $header->Location ?? '-' }}" readonly>
            </div>
        </div>

        <!-- INFORMASI TAMBAHAN (PIC, SITE, ESTIMATED BUDGET) -->
        <div class="row mb-3">
            <div class="col-md-2 mb-2">
                <label class="font-weight-bold text-muted mb-1">PIC (Penanggung Jawab):</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->PIC ?? $header->PIC_Name ?? '-' }}" readonly>
            </div>
            <div class="col-md-2 mb-2">
                <label class="font-weight-bold text-muted mb-1">Phone PIC:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->PIC_Phone ?? '-' }}" readonly>
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-muted mb-1">Email PIC:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->PIC_Email ?? '-' }}" readonly>
            </div>
            <div class="col-md-3 mb-2">
                <label class="font-weight-bold text-muted mb-1">Site Tujuan:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->PIC_Address ?? '-' }}" readonly>
            </div>
            <div class="col-md-2 mb-2">
                <label class="font-weight-bold text-primary mb-1">Estimated Budget (Rp):</label>
                <input type="text" class="form-control form-control-sm font-weight-bold text-right text-primary bg-light" value="{{ number_format($header->Estimated_Budget ?? 0, 0, ',', '.') }}" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold text-muted mb-1">Kategori Anggaran (Main Category):</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Category ?? '-' }}" readonly>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold text-muted mb-1">Sub Kategori (Category):</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Sub_Category ?? '-' }}" readonly>
            </div>
            <div class="col-md-4 mb-2">
                <label class="font-weight-bold text-muted mb-1">Dibuat / Disetujui Oleh:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="Req: {{ $header->Created_By ?? '-' }} | Appr: {{ $header->Approval_User ?? '-' }}" readonly>
            </div>
        </div>

        @if(!empty($header->Note))
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="font-weight-bold text-muted mb-1">Catatan Terakhir PR:</label>
                <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $header->Note }}</textarea>
            </div>
        </div>
        @endif

        <!-- TAMPILAN RIWAYAT / HISTORY NOTE DARI pr_notes_history -->
        @if(isset($noteHistories) && count($noteHistories) > 0)
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="font-weight-bold text-info"><i class="fas fa-history mr-1"></i> Riwayat Percakapan / History Note:</label>
                <div class="border rounded p-2 bg-light" style="max-height: 160px; overflow-y: auto;">
                    @foreach($noteHistories as $hist)
                        <div class="border-bottom mb-2 pb-1 bg-white p-2 rounded shadow-sm">
                            <div class="d-flex justify-content-between">
                                <span class="font-weight-bold text-primary" style="font-size: 11px;">
                                    <i class="fas fa-user-circle mr-1"></i> {{ $hist->Created_By ?? 'System' }} 
                                    <span class="badge badge-secondary ml-1" style="font-size: 9px;">{{ $hist->Action_Status ?? 'NOTE' }}</span>
                                </span>
                                <span class="text-muted" style="font-size: 10px;">{{ $hist->Created_Date ?? '' }}</span>
                            </div>
                            <p class="mb-0 text-dark mt-1" style="font-size: 11.5px;">{{ $hist->Note }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <hr class="my-3">

        <!-- HEADER TABEL MULTI PRODUK (READ-ONLY) -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="text-success font-weight-bold mb-0" style="font-size: 12px;">
                <i class="fas fa-boxes mr-1"></i> Rincian Material / Multi Produk
            </h6>
        </div>

        <!-- TABEL READ-ONLY ITEM -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm bg-white" style="font-size: 12px;">
                <thead class="thead-light text-center">
                    <tr>
                        <th style="width: 4%;">No.</th>
                        <th style="width: 15%;">Product Code</th>
                        <th style="width: 22%;">Nama Item / Deskripsi</th>
                        <th style="width: 20%;">Spesifikasi (Spec)</th>
                        <th style="width: 6%;">Qty</th>
                        <th style="width: 7%;">Satuan</th>
                        <th style="width: 9%;">Est. Price (Rp)</th>
                        <th style="width: 10%;">Subtotal (Rp)</th>
                        <th style="width: 7%;">Lampiran</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotalCalc = 0; @endphp
                    @forelse($details as $idx => $item)
                        @php
                            $itemPrice = $item->Default_Price ?? $item->Price ?? 0;
                            $itemSubtotal = ($item->Qty ?? 1) * $itemPrice;
                            $grandTotalCalc += $itemSubtotal;
                        @endphp
                    <tr>
                        <td class="text-center align-middle">{{ $idx + 1 }}</td>
                        <td class="align-middle font-weight-bold text-primary">{{ $item->Product_Code }}</td>
                        <td class="align-middle">{{ $item->Product_Desc }}</td>
                        <td class="align-middle">{{ $item->Specification ?: '-' }}</td>
                        <td class="text-center align-middle font-weight-bold">{{ number_format($item->Qty, 0, ',', '.') }}</td>
                        <td class="text-center align-middle">{{ $item->Unit }}</td>
                        <td class="text-right align-middle">{{ number_format($itemPrice, 0, ',', '.') }}</td>
                        <td class="text-right align-middle font-weight-bold">{{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                        <td class="text-center align-middle">
                            @if(!empty($item->Attachment))
                                <a href="{{ asset($item->Attachment) }}" target="_blank" class="btn btn-info text-white font-weight-bold d-inline-block shadow-sm" style="padding: 2px 8px; font-size: 10.5px; line-height: 1.4; border-radius: 3px;" title="Lihat Lampiran">
                                    <i class="fas fa-eye mr-1"></i> Lihat File
                                </a>
                            @else
                                <span class="text-muted" style="font-size: 10.5px;">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-3">Tidak ada rincian item produk pada PR ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-light font-weight-bold">
                        <td colspan="7" class="text-right text-dark">TOTAL ESTIMASI BUDGET (Rp):</td>
                        <td class="text-right text-primary" style="font-size: 13px;">Rp {{ number_format($grandTotalCalc, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif