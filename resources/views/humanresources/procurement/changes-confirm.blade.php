@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="main-content-header mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1 class="h3 font-weight-bold text-dark mb-1">Konfirmasi Perubahan Data PR</h1>
                <ol class="breadcrumb bg-transparent p-0 mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="/dashboard1" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/hrd-procurement-index" class="text-decoration-none">Procurement</a></li>
                    <li class="breadcrumb-item active text-warning font-weight-semibold">Perubahan Data</li>
                </ol>
            </div>
            <div class="mt-2 mt-md-0">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-xs">
                    <i class="fa fa-exclamation-triangle me-1"></i> Perlu Tindakan Pemohon
                </span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <span class="badge bg-warning text-dark p-2 me-2 rounded-circle">
                    <i class="fa fa-pencil fa-lg"></i>
                </span>
                <div>
                    <h5 class="card-title mb-0 font-weight-bold text-dark">Review Perubahan Dokumen Pengadaan</h5>
                    <small class="text-muted">Approver melakukan penyesuaian pada permohonan PR ini.</small>
                </div>
            </div>
            <span class="badge bg-light text-dark border px-3 py-2">
                No. PR: <strong class="text-primary">{{ $pr->PR_No }}</strong>
            </span>
        </div>

        <div class="card-body p-4">
            <div class="alert alert-warning border-0 shadow-xs rounded-3 d-flex align-items-start mb-4">
                <i class="fa fa-info-circle fa-2x me-3 text-warning"></i>
                <div>
                    <h6 class="font-weight-bold mb-1">Pemberitahuan Penting:</h6>
                    <p class="mb-0 small">Approver telah melakukan penyesuaian/perubahan rincian pada Purchase Request Anda. Mohon pelajari perbedaan data dan riwayat catatan di bawah ini, kemudian pilih apakah Anda <strong>Menyetujui</strong> atau <strong>Menolak</strong> perubahan tersebut.</p>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger border-0 rounded-3 mb-4">
                    <i class="fa fa-exclamation-circle me-1"></i> {{ session('error') }}
                </div>
            @endif

            <!-- Ringkasan Informasi Dokumen -->
            <div class="card border rounded-3 mb-4 bg-light">
                <div class="card-header bg-transparent border-0 pb-0 pt-3 px-3">
                    <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-file-text-o me-2 text-primary"></i>Informasi Purchase Request</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Nomor PR</span>
                            <strong class="text-dark">{{ $pr->PR_No }}</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Tanggal Pengajuan</span>
                            <span class="text-dark">{{ date('d M Y', strtotime($pr->PR_Date)) }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Diajukan Oleh</span>
                            <span class="text-dark">{{ $pr->Created_By }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Status Saat Ini</span>
                            <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">{{ $pr->Status }}</span>
                        </div>
                        <div class="col-md-3 mt-2">
                            <span class="text-muted small d-block">Departemen</span>
                            <span class="text-dark">{{ $pr->Department ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 mt-2">
                            <span class="text-muted small d-block">Kategori</span>
                            <span class="text-dark">{{ $pr->Category ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 mt-2">
                            <span class="text-muted small d-block">Vendor / Rekanan</span>
                            <span class="text-dark">{{ $pr->Supplier_Name ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 mt-2">
                            <span class="text-muted small d-block">Total Estimasi Budget</span>
                            <strong class="text-success">Rp {{ number_format($pr->Estimated_Budget ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Perubahan (Log) -->
            @if($changeLogs->isNotEmpty())
                <div class="mb-4">
                    <h6 class="font-weight-bold text-dark mb-3">
                        <i class="fa fa-history me-2 text-primary"></i>Catatan &amp; Riwayat Modifikasi Approver
                    </h6>
                    <div class="table-responsive border rounded-3 overflow-hidden shadow-xs">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 170px;">Tanggal &amp; Waktu</th>
                                    <th style="width: 150px;">Oleh</th>
                                    <th style="width: 180px;">Status Tindakan</th>
                                    <th>Detail Catatan &amp; Referensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($changeLogs as $log)
                                    <tr>
                                        <td class="ps-3 small text-muted">{{ $log->Action_Date }}</td>
                                        <td class="font-weight-semibold text-dark">{{ $log->Action_By }}</td>
                                        <td>
                                            <span class="badge bg-info text-white rounded-pill px-2 py-1">{{ $log->Action_Status }}</span>
                                        </td>
                                        <td class="small">
                                            <div class="text-dark"><strong>Note:</strong> {{ $log->Note ?? '-' }}</div>
                                            <div class="text-muted"><strong>Ref:</strong> {{ $log->Reference ?? '-' }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Detail Item Produk -->
            <div class="mb-4">
                <h6 class="font-weight-bold text-dark mb-3">
                    <i class="fa fa-cubes me-2 text-primary"></i>Rincian Barang / Layanan yang Disetujui
                </h6>
                <div class="table-responsive border rounded-3 overflow-hidden shadow-xs">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center ps-3" style="width: 50px;">No</th>
                                <th style="width: 140px;">Kode Produk</th>
                                <th>Deskripsi Item</th>
                                <th class="text-center" style="width: 80px;">Qty</th>
                                <th style="width: 100px;">Satuan</th>
                                <th class="text-end" style="width: 140px;">Harga Satuan</th>
                                <th class="text-end" style="width: 160px;">Subtotal</th>
                                <th class="text-center" style="width: 120px;">Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; @endphp
                            @foreach($details as $index => $detail)
                                @php 
                                    $sub = ($detail->Default_Price ?? 0) * ($detail->Qty ?? 0);
                                    $grandTotal += $sub;
                                @endphp
                                <tr>
                                    <td class="text-center ps-3 text-muted">{{ $index + 1 }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $detail->Product_Code ?? '-' }}</span></td>
                                    <td>
                                        <div class="font-weight-bold text-dark">{{ $detail->Product_Desc ?? '-' }}</div>
                                        @if(!empty($detail->Specification))
                                            <small class="text-muted"><i class="fa fa-info-circle me-1"></i>{{ $detail->Specification }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold text-primary">{{ $detail->Qty ?? 0 }}</td>
                                    <td>{{ $detail->Unit ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->Default_Price ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end font-weight-semibold text-dark">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                                    <td class="text-center"><span class="badge bg-secondary text-white">{{ $detail->Category ?? '-' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="6" class="text-end font-weight-bold">Total Nilai Perubahan:</th>
                                <th class="text-end font-weight-bold text-success">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Form Remark & Feedback -->
            <div class="card border rounded-3 p-3 mb-4 bg-light">
                <label for="Remark" class="form-label font-weight-bold text-dark">
                    <i class="fa fa-commenting-o me-1 text-primary"></i>Catatan Konfirmasi Pemohon (Opsional):
                </label>
                <textarea name="Remark" id="Remark" class="form-control" rows="3" placeholder="Masukkan alasan persetujuan atau catatan sanggahan jika menolak perubahan ini..."></textarea>
            </div>

            <!-- Action Buttons Footer -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top">
                <a href="/hrd-procurement-index" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Kembali ke List
                </a>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger px-4" onclick="rejectChanges()">
                        <i class="fa fa-times-circle me-1"></i> Tolak Perubahan
                    </button>
                    <button type="button" class="btn btn-success px-4 text-white shadow-xs" onclick="approveChanges()">
                        <i class="fa fa-check-circle me-1"></i> Setujui Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script>
function approveChanges() {
    var Remark = $('#Remark').val();
    var PR_No = '{{ $pr->PR_No }}';
    
    $.blockUI({ message: '<h4><i class="fa fa-spinner fa-spin"></i> Memproses Persetujuan...</h4>' });
    
    $.ajax({
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        url: '/hrd-procurement-approve-changes',
        type: 'POST',
        data: {
            PR_No: PR_No,
            Remark: Remark,
            Action: 'approve'
        },
        success: function(response) {
            $.unblockUI();
            if (response.status === 'success') {
                alert('Perubahan disetujui! PR dikembalikan ke tahap approval.');
                window.location.href = response.redirect || '/hrd-procurement-index';
            } else {
                alert('Gagal: ' + (response.message || 'Terjadi kesalahan saat memproses data'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.unblockUI();
            alert('Terjadi kesalahan sistem: ' + errorThrown);
        }
    });
}

function rejectChanges() {
    if (!confirm('Apakah Anda yakin menolak perubahan ini? PR akan dikembalikan ke pihak approver dengan catatan Anda.')) {
        return;
    }
    
    var Remark = $('#Remark').val();
    var PR_No = '{{ $pr->PR_No }}';
    
    $.blockUI({ message: '<h4><i class="fa fa-spinner fa-spin"></i> Memproses Penolakan...</h4>' });
    
    $.ajax({
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        url: '/hrd-procurement-approve-changes',
        type: 'POST',
        data: {
            PR_No: PR_No,
            Remark: Remark,
            Action: 'reject'
        },
        success: function(response) {
            $.unblockUI();
            if (response.status === 'success') {
                alert('Perubahan ditolak! Dokumen telah dikembalikan ke approver.');
                window.location.href = response.redirect || '/hrd-procurement-index';
            } else {
                alert('Gagal: ' + (response.message || 'Terjadi kesalahan'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.unblockUI();
            alert('Terjadi kesalahan sistem: ' + errorThrown);
        }
    });
}
</script>
@endsection
