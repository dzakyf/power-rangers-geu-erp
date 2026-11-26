@if(!$header)
    <div class="alert alert-danger mb-0" style="font-size: 12px;">
        <i class="fas fa-exclamation-triangle mr-1"></i> Data Purchase Request tidak ditemukan.
    </div>
@else
<div class="card border-success shadow-sm" style="border-radius: 10px; border-left: 5px solid #28a745;">
    <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 font-weight-bold" style="font-size: 13px;">
            <i class="fas fa-file-circle-plus mr-2"></i>Detail PR &amp; Form Create RFQ (Multi-Vendor) — {{ $header->PR_No }}
        </h6>
        <span class="badge badge-light text-success font-weight-bold">Status PR: {{ $header->Status }}</span>
    </div>

    <div class="card-body bg-white" style="font-size: 12px;">
        <!-- BARIS 1: INFORMASI PR UTAMA (READ-ONLY) -->
        <div class="row mb-3">
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Nomor PR:</label>
                <input type="text" class="form-control form-control-sm font-weight-bold text-primary bg-light" value="{{ $header->PR_No }}" readonly>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Tanggal PR:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->PR_Date ?? '-' }}" readonly>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Department:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Department ?? '-' }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="font-weight-bold text-muted">Location:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Location ?? 'GEU-HO' }}" readonly>
            </div>
        </div>

        <!-- BARIS 2: KATEGORI & URGENSI (READ-ONLY) -->
        <div class="row mb-3">
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Jenis Pengadaan (SK Approval):</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Supplies_Type ?? 'NON-OPS' }}" readonly>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Kategori Anggaran (Main Category):</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Category ?? '-' }}" readonly>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Kategori Pengadaan:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Procurement_Category ?? '-' }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="font-weight-bold text-muted">Sub Kategori (Category):</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Sub_Category ?? '-' }}" readonly>
            </div>
        </div>

        <!-- BARIS 3: URGENSI, HOPE DATE & COST CENTER (READ-ONLY) -->
        <div class="row mb-3">
            <div class="col-md-4 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Tingkat Urgensi PR:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Urgency ?? 'Normal' }}" readonly>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Hope Date (Target Kedatangan):</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Hope_Date ?? '-' }}" readonly>
            </div>
            <div class="col-md-4">
                <label class="font-weight-bold text-muted">Cost Center:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->Cost_Center ?? '-' }}" readonly>
            </div>
        </div>

        <!-- BARIS 4: PIC, KONTAK, SITE TUJUAN & ESTIMATED BUDGET (READ-ONLY) -->
        <div class="row mb-3">
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">PIC (Penanggung Jawab):</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->PIC ?? '-' }}" readonly>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Phone PIC:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->PIC_Phone ?? '-' }}" readonly>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Email PIC:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->PIC_Email ?? '-' }}" readonly>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label class="font-weight-bold text-muted">Site Tujuan:</label>
                <input type="text" class="form-control form-control-sm bg-light" value="{{ $header->PIC_Address ?? '-' }}" readonly>
            </div>
            <div class="col-md-2">
                <label class="font-weight-bold text-success">Estimated Budget (Rp):</label>
                <input type="text" class="form-control form-control-sm font-weight-bold text-right text-success bg-light"
                       value="{{ number_format($header->Estimated_Budget ?? 0, 0, ',', '.') }}" readonly>
            </div>
        </div>

        <!-- CATATAN PR & RIWAYAT PERCAKAPAN (READ-ONLY) -->
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="font-weight-bold text-muted">Catatan / Note PR:</label>
                <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $header->Note ?? '-' }}</textarea>
            </div>
        </div>

        @if(isset($noteHistories) && count($noteHistories) > 0)
        <div class="row mb-3">
            <div class="col-md-12">
                <label class="font-weight-bold text-info">
                    <i class="fas fa-history mr-1"></i> Riwayat Percakapan / History Note:
                </label>
                <div class="border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
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

       
        <!-- RINCIAN MATERIAL / MULTI PRODUK (READ-ONLY) -->
<div class="mb-3">
    <h6 class="text-success font-weight-bold mb-2" style="font-size: 12px;">
        <i class="fas fa-boxes mr-1"></i> Rincian Material / Multi Produk (Approved Items)
    </h6>
    <div class="table-responsive">
        <table class="table table-bordered table-sm bg-white mb-0" style="font-size: 11.5px;">
            <thead class="thead-light text-center">
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 14%;">Product Code</th>
                    <th style="width: 21%;">Nama Item / Deskripsi</th>
                    <th style="width: 16%;">Spesifikasi (Spec)</th>
                    <th style="width: 7%;">Qty</th>
                    <th style="width: 7%;">Satuan</th>
                    <th style="width: 11%;">Est. Price (Rp)</th>
                    <th style="width: 12%;">Subtotal (Rp)</th>
                    <th style="width: 8%;">Lampiran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($details as $idx => $item)
                @php
                    $itemPrice    = floatval($item->Default_Price ?? $item->Price ?? 0);
                    $itemQty      = floatval($item->Qty ?? 0);
                    $itemSubtotal = $itemQty * $itemPrice;

                    $rawAtt = trim((string) ($item->Attachment ?? ''));
                    $attUrl = '';
                    if ($rawAtt !== '') {
                        if (preg_match('/^https?:\/\//i', $rawAtt)) {
                            $attUrl = $rawAtt;
                        } else {
                            $attUrl = asset(ltrim($rawAtt, '/'));
                        }
                    }
                    $ext = strtolower(pathinfo(parse_url($attUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-weight-bold">{{ $item->Product_Code }}</td>
                    <td>{{ $item->Product_Desc }}</td>
                    <td>{{ $item->Specification ?? '-' }}</td>
                    <td class="text-right">{{ $itemQty }}</td>
                    <td class="text-center">{{ $item->Unit ?? 'PCS' }}</td>
                    <td class="text-right">{{ number_format($itemPrice, 0, ',', '.') }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($attUrl !== '')
                            <button type="button"
                                    class="btn btn-xs btn-outline-info font-weight-bold"
                                    title="Preview Berkas Lampiran ({{ $item->Product_Code }})"
                                    onclick="openPrItemAttachmentPreview(@json($attUrl), @json($item->Product_Code . ' - ' . $item->Product_Desc), {{ $isImage ? 'true' : 'false' }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">Tidak ada item rincian pada PR ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

        <!-- HISTORI / TRACKING PERSETUJUAN BERJENJANG (APPROVAL ENGINE) -->
        <div class="mb-3 border rounded overflow-hidden">
            <div class="bg-info text-white px-3 py-2 d-flex justify-content-between align-items-center" style="font-size: 12px;">
                <span class="font-weight-bold">
                    <i class="fas fa-sitemap mr-1"></i> Histori &amp; Tracking Persetujuan Berjenjang — {{ $header->PR_No }}
                </span>
                <span class="badge badge-light text-info font-weight-bold">
                    Status Request: {{ $approvalReq->Status ?? ($header->Status ?? '-') }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0 bg-white" style="font-size: 11.5px;">
                    <thead class="thead-light text-center">
                        <tr>
                            <th style="width: 8%;">Stage</th>
                            <th style="width: 24%;">Jabatan Approver</th>
                            <th style="width: 15%;">Status Task</th>
                            <th style="width: 20%;">Dieksekusi Oleh</th>
                            <th style="width: 15%;">Waktu</th>
                            <th style="width: 18%;">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($approvalTasks) && count($approvalTasks) > 0)
                            @foreach($approvalTasks as $task)
                                @php
                                    $tStatus = strtoupper(trim((string) ($task->Task_Status ?? ($task->Status ?? 'PENDING'))));
                                    $posLabel = trim((string) ($task->Position_Name ?? '')) ?: (trim((string) ($task->Position_Code ?? '')) ?: 'Approver');
                                    $actorLabel = trim((string) ($task->Acted_By_Name ?? ($task->Acted_By_Username ?? ($task->Acted_By ?? '-')))) ?: '-';
                                    $badgeClass = 'badge-secondary';
                                    if (in_array($tStatus, ['APPROVED', 'COMPLETED'], true)) {
                                        $badgeClass = 'badge-success';
                                    } elseif (in_array($tStatus, ['REJECTED', 'CANCELLED'], true)) {
                                        $badgeClass = 'badge-danger';
                                    } elseif (in_array($tStatus, ['PENDING', 'WAITING'], true)) {
                                        $badgeClass = 'badge-warning text-dark';
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold">{{ $task->Stage_Order ?? ($task->Level_Number ?? 1) }}</td>
                                    <td>{{ $posLabel }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $badgeClass }}">{{ $tStatus }}</span>
                                    </td>
                                    <td>{{ $actorLabel }}</td>
                                    <td class="text-center">{{ $task->Acted_At ?? '-' }}</td>
                                    <td>{{ $task->Task_Notes ?? ($task->Notes ?? '-') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="text-center font-weight-bold">1</td>
                                <td>Approval PR</td>
                                <td class="text-center">
                                    <span class="badge badge-success">{{ $header->Status ?? 'APPROVED' }}</span>
                                </td>
                                <td>{{ $header->Approval_User ?? '-' }}</td>
                                <td class="text-center">{{ $header->Approval_Date ?? '-' }}</td>
                                <td>{{ $header->Note ?? '-' }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <hr class="my-3">

        <!-- FORM INPUT VENDOR & TANGGAL RFQ (MULTI-VENDOR) -->
        <div class="bg-light p-3 rounded border border-success mb-3">
            <h6 class="font-weight-bold text-success mb-2" style="font-size: 12px;">
                <i class="fas fa-handshake mr-1"></i> Input Vendor / Supplier Penerima RFQ
            </h6>

            <div class="row align-items-end">
                <div class="col-md-7 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark mb-1">Pilih Vendor / Supplier Tujuan *</label>
                    <select id="rfq_created_supplier_id" class="form-control form-control-sm select2-rfq-created" style="width: 100%;">
                        <option value="">-- Ketik &amp; Pilih Vendor / Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->Supplier_ID }}">
                                {{ $sup->Supplier_ID }} - {{ $sup->Company_Name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark mb-1">Tanggal RFQ *</label>
                    <input type="date" id="rfq_created_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-success btn-sm font-weight-bold shadow-sm w-100" onclick="submitRFQFromApprovedPR('{{ $header->PR_No }}')">
                        <i class="fas fa-plus mr-1"></i> + Buat RFQ
                    </button>
                </div>
            </div>
        </div>

        <!-- DAFTAR RFQ YANG SUDAH TERBIT UNTUK PR INI -->
        <div>
            <label class="font-weight-bold text-primary mb-1" style="font-size: 12px;">
                <i class="fas fa-list mr-1"></i> Daftar RFQ Terbit untuk PR {{ $header->PR_No }}:
            </label>
            @if(isset($listRfqs) && count($listRfqs) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-sm bg-white mb-0" style="font-size: 11.5px;">
                    <thead class="thead-light text-center">
                        <tr>
                            <th style="width: 24%;">No. RFQ</th>
                            <th style="width: 14%;">Tanggal RFQ</th>
                            <th style="width: 14%;">ID Vendor</th>
                            <th style="width: 28%;">Nama Perusahaan (Supplier)</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 10%;">Print</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listRfqs as $rfq)
                        <tr>
                            <td class="font-weight-bold text-primary text-center">{{ $rfq->RFQ_No }}</td>
                            <td class="text-center">{{ $rfq->RFQ_Date }}</td>
                            <td class="text-center">{{ $rfq->Supplier_ID }}</td>
                            <td>{{ $rfq->Company_Name }}</td>
                            <td class="text-center"><span class="badge badge-info">{{ $rfq->Status }}</span></td>
                            <td class="text-center">
                                <a href="{{ url('rfq-print') }}?rfq={{ urlencode($rfq->RFQ_No) }}" target="_blank" class="btn btn-xs btn-outline-primary font-weight-bold">
                                    <i class="fas fa-print"></i> Print
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-warning py-2 px-3 mb-0" style="font-size: 11.5px;">
                <i class="fas fa-info-circle mr-1"></i> Belum ada RFQ untuk PR ini. Pilih vendor di atas lalu klik <strong>+ Buat RFQ</strong>.
            </div>
            @endif
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2-rfq-created').select2({
            width: '100%',
            placeholder: '-- Ketik untuk mencari vendor --',
            allowClear: true
        });
    }
});

function submitRFQFromApprovedPR(prNo) {
    var supplierId = $('#rfq_created_supplier_id').val();
    var rfqDate    = $('#rfq_created_date').val();

    if (!supplierId) {
        alert('Silakan pilih Vendor / Supplier terlebih dahulu!');
        $('#rfq_created_supplier_id').focus();
        return;
    }

    if (!rfqDate) {
        alert('Silakan tentukan Tanggal RFQ terlebih dahulu!');
        $('#rfq_created_date').focus();
        return;
    }

    if (!confirm('Buat RFQ baru untuk PR ' + prNo + ' kepada vendor terpilih?')) {
        return;
    }

    var $btn = $('button[onclick*="submitRFQFromApprovedPR"]');
    $btn.prop('disabled', true);

    $.ajax({
        url: "{{ url('purchase-request-to-rfq') }}",
        type: "POST",
        dataType: "json",
        data: {
            _token: "{{ csrf_token() }}",
            pr_no: prNo,
            supplier_id: supplierId,
            rfq_date: rfqDate
        },
        success: function(res) {
            var isOk = (res && (res.status === 'success' || res.success === true || !!res.rfq_no));
            if (isOk) {
                alert((res && res.message) ? res.message : 'RFQ berhasil dibuat!');

                // Tutup paksa panel Detail PR & Form Create RFQ
                $('#RFQCreateWrapper').hide();
                $('#RFQCreateDetailContainer').empty();
                if (typeof window.closeCreateRFQPanel === 'function') {
                    window.closeCreateRFQPanel();
                }

                // Refresh grid utama RFQ dengan cache buster
                if (typeof window.refreshRFQGrid === 'function') {
                    window.refreshRFQGrid();
                } else if (typeof RFQ !== 'undefined') {
                    RFQ.clearAll();
                    var sep = "{{ url('xml-purchasing-order-rfq') }}".indexOf('?') > -1 ? '&' : '?';
                    RFQ.load("{{ url('xml-purchasing-order-rfq') }}" + sep + "_t=" + new Date().getTime());
                }

                // Scroll kembali ke atas tabel daftar RFQ
                $('html, body').animate({ scrollTop: 0 }, 300);
            } else {
                alert((res && res.message) ? res.message : 'Gagal membuat RFQ.');
            }
        },
        error: function(err) {
            console.error('Error submitRFQFromApprovedPR:', err);
            var msg = (err.responseJSON && err.responseJSON.message)
                ? err.responseJSON.message
                : ('Terjadi kesalahan saat membuat RFQ (HTTP ' + err.status + ').');
            alert(msg);
        },
        complete: function() {
            $btn.prop('disabled', false);
        }
    });
}

function refreshRFQGrid() {
    if (typeof RFQ !== 'undefined') {
        RFQ.clearAll();
        RFQ.load("{{ url('xml-purchasing-order-rfq') }}");
    }
    $('#RFQEditPanel').empty();
    $('#RFQPreviewPanel').empty();
}
</script>
@endif