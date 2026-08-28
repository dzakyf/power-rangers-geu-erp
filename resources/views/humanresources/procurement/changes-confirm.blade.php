@extends('layout.mainlayout')

@section('content-wrapper')
<div class="container-fluid">
    <!-- Header -->
    <div class="main-content-header">
        <h1>CONFIRMATION CHANGES</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard1">HOME</a></li>
            <li class="breadcrumb-item"><a href="/hrd-procurement-index">PROCUREMENT</a></li>
            <li class="breadcrumb-item active">PERUBAHAN DATA</li>
        </ol>
    </div>

    <div class="card">
        <div class="card-header bg-warning text-dark py-3">
            <h5 class="mb-0">
                <i class="fa fa-exclamation-triangle mr-2"></i>
                Ada Perubahan Data pada PR Anda
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <strong>Informasi:</strong>
                Approver telah melakukan perubahan pada Purchase Request ini.
                Silakan review perubahan berikut dan pilih apakah Anda setuju atau menolak.
            </div>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- PR Info -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th style="width:25%">PR No</th>
                            <td style="width:25%"><strong>{{ $pr->PR_No }}</strong></td>
                            <th style="width:25%">Created By</th>
                            <td style="width:25%">{{ $pr->Created_By }}</td>
                        </tr>
                        <tr>
                            <th>PR Date</th>
                            <td>{{ date('Y-m-d', strtotime($pr->PR_Date)) }}</td>
                            <th>Status</th>
                            <td class="badge bg-warning text-dark">{{ $pr->Status }}</td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>{{ $pr->Category ?? '-' }}</td>
                            <th>Department</th>
                            <td>{{ $pr->Department ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Supplier</th>
                            <td>{{ $pr->Supplier_Name ?? '-' }}</td>
                            <th>Estimated Budget</th>
                            <td>Rp {{ number_format($pr->Estimated_Budget ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Detail Perubahan (dari log) -->
            @if($changeLogs->isNotEmpty())
                <div class="mb-4">
                    <h5 class="mb-3">
                        <i class="fa fa-history mr-2"></i>
                        Riwayat Perubahan
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Oleh</th>
                                    <th>Status Perubahan</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($changeLogs as $log)
                                    <tr>
                                        <td>{{ $log->Action_Date }}</td>
                                        <td>{{ $log->Action_By }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $log->Action_Status }}</span>
                                        </td>
                                        <td style="font-size:12px;">
                                            <strong>Note:</strong> {{ $log->Note ?? '-' }}<br>
                                            <strong>Reference:</strong> {{ $log->Reference ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Perbedaan Data (original vs current) -->
            <div class="mb-4">
                <h5 class="mb-3">
                    <i class="fa fa-code-branch mr-2"></i>
                    Perbandingan Data
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Original Data (saat dibuat)</h6>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th>PIC</th>
                                <td>{{ $pr->PIC ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>PIC Phone</th>
                                <td>{{ $pr->PIC_Phone ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>PIC Address</th>
                                <td>{{ $pr->PIC_Address ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td>{{ $pr->Category ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Estimated Budget</th>
                                <td>Rp {{ number_format($pr->Estimated_Budget ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning">Data Terbaru (oleh Approver)</h6>
                        <table class="table table-sm table-bordered">
                            <tr>
                                <th>PIC</th>
                                <td>{{ $pr->PIC ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>PIC Phone</th>
                                <td>{{ $pr->PIC_Phone ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>PIC Address</th>
                                <td>{{ $pr->PIC_Address ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Category</th>
                                <td>{{ $pr->Category ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Estimated Budget</th>
                                <td>Rp {{ number_format($pr->Estimated_Budget ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detail Produk -->
            <div class="mb-4">
                <h5 class="mb-3">
                    <i class="fa fa-shopping-cart mr-2"></i>
                    Detail Produk
                </h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Product Code</th>
                                <th>Product Desc</th>
                                <th>Qty</th>
                                <th>Specification</th>
                                <th>Unit</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $index => $detail)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $detail->Product_Code ?? '-' }}</td>
                                    <td>{{ $detail->Product_Desc ?? '-' }}</td>
                                    <td>{{ $detail->Qty ?? 0 }}</td>
                                    <td>{{ $detail->Specification ?? '-' }}</td>
                                    <td>{{ $detail->Unit ?? '-' }}</td>
                                    <td>Rp {{ number_format($detail->Default_Price ?? 0, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format(($detail->Default_Price ?? 0) * ($detail->Qty ?? 0), 0, ',', '.') }}</td>
                                    <td>{{ $detail->Category ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form Remark -->
            <div class="mb-4">
                <label class="font-weight-bold">Alasan/Keterangan (Optional)</label>
                <textarea name="Remark" id="Remark" class="form-control" rows="3" placeholder="Tulis alasan jika diperlukan..."></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a href="/hrd-procurement-index" class="btn btn-secondary">
                    <i class="fa fa-times"></i> Batal
                </a>
                <button type="button" class="btn btn-danger" onclick="rejectChanges()">
                    <i class="fa fa-times-circle"></i> Tolak Perubahan
                </button>
                <button type="button" class="btn btn-success" onclick="approveChanges()">
                    <i class="fa fa-check-circle"></i> Setujui Perubahan
                </button>
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
    
    $.blockUI({ message: 'Processing...' });
    
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
                alert('Error: ' + (response.message || 'Gagal menyetujui perubahan'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.unblockUI();
            alert('Error: ' + errorThrown);
        }
    });
}

function rejectChanges() {
    if (!confirm('Apakah Anda yakin menolak perubahan ini? PR akan dikembalikan ke approver.')) {
        return;
    }
    
    var Remark = $('#Remark').val();
    var PR_No = '{{ $pr->PR_No }}';
    
    $.blockUI({ message: 'Processing...' });
    
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
                alert('Perubahan ditolak! PR dikembalikan ke approver.');
                window.location.href = response.redirect || '/hrd-procurement-index';
            } else {
                alert('Error: ' + (response.message || 'Gagal menolak perubahan'));
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.unblockUI();
            alert('Error: ' + errorThrown);
        }
    });
}
</script>
@endsection