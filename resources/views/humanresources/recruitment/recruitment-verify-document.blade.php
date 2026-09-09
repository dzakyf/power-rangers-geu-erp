<?php
/**
 * recruitment-verify-document.blade.php
 * Halaman verifikasi dokumen kandidat - tahap 2 dari alur recruitment
 * 
 * Alur: Input Candidate → Verifikasi Dokumen → Psikotest → Interview HR → ...
 * 
 * Halaman ini menampilkan:
 * - Informasi dasar kandidat
 * - Daftar dokumen yang diupload
 * - Form catatan verifikasi
 * - Tombol Approve (lanjut ke Psikotest) / Reject
 */
?>
@extends('layout.mainlayout')

@section('content-wrapper')
<style>
    .verify-card {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-created {
        background-color: #e2e3e5;
        color: #383d41;
    }
    .document-item {
        border-left: 3px solid #ddd;
        padding-left: 12px;
        margin-bottom: 12px;
        transition: border-color 0.2s;
    }
    .document-item:hover {
        border-left-color: #699;
    }
    .document-item.has-file {
        border-left-color: #28a745;
    }
    .action-btn {
        padding: 10px 24px;
        border-radius: 4px;
        font-weight: 600;
    }
</style>

<div class="container-fluid p-4">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="text-dark">📋 Verifikasi Dokumen Kandidat</h3>
            <p class="text-muted mb-0">Tahap 2 dari alur recruitment - Review dokumen sebelum lanjut ke Psikotest</p>
        </div>
        <a href="{{ url('hrd-recruitment-management') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali ke Management
        </a>
    </div>

    {{-- Progress Indicator --}}
    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-warning" style="width: 25%;">
                            <small>Tahap 1: Input Candidate (Selesai)</small>
                        </div>
                        <div class="progress-bar bg-primary" style="width: 25%; margin-left: 25%;">
                            <small>Tahap 2: Verifikasi Dokumen (Current)</small>
                        </div>
                        <div class="progress-bar bg-light" style="width: 25%; margin-left: 50%;">
                            <small>Tahap 3: Psikotest</small>
                        </div>
                        <div class="progress-bar bg-light" style="width: 25%; margin-left: 75%;">
                            <small>Tahap 4+: Interview & Lebih Lanjut</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Candidate Card --}}
    <div class="card mb-4 verify-card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">
                <i class="fa fa-user-circle mr-2"></i> Informasi Kandidat
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th style="width: 150px;">Task ID</th>
                            <td class="font-weight-bold font-monospace">{{ $recruitment->Task_ID }}</td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td>{{ $recruitment->Name }}</td>
                        </tr>
                        <tr>
                            <th>Gender</th>
                            <td>
                                <span class="status-badge {{ $recruitment->Gender === 'MALE' ? 'bg-info' : 'bgpink' }}">
                                    {{ $recruitment->Gender }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Birth Date</th>
                            <td>{{ $recruitment->Birth_Date ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Birth Place</th>
                            <td>{{ $recruitment->Birth_Place ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Citizenship</th>
                            <td>{{ $recruitment->kewarganegaraan ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th style="width: 150px;">Status</th>
                            <td>
                                <span class="status-badge status-cancelled">
                                    {{ $recruitment->Status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Document Status</th>
                            <td>
                                @if($recruitment->Document_Status === 'ACCEPT')
                                    <span class="status-badge bg-success text-white">ACCEPT</span>
                                @elseif($recruitment->Document_Status === 'PENDING')
                                    <span class="status-badge bg-warning text-dark">PENDING</span>
                                @else
                                    <span class="status-badge bg-secondary">{{ $recruitment->Document_Status }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Requisition ID</th>
                            <td class="font-monospace">{{ $recruitment->Requisition_ID }}</td>
                        </tr>
                        <tr>
                            <th>Created By</th>
                            <td>{{ $recruitment->Created_By }}</td>
                        </tr>
                        <tr>
                            <th>Created Date</th>
                            <td>{{ $recruitment->Created_Date }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-12 mt-3">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th style="width: 150px;">Address</th>
                            <td>{{ $recruitment->Address ?? '-' }}, {{ $recruitment->City ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $recruitment->Phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>WhatsApp</th>
                            <td>{{ $recruitment->Handphone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                @if($recruitment->Email)
                                    <a href="mailto:{{ $recruitment->Email }}" class="text-decoration-none">
                                        {{ $recruitment->Email }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Last Education</th>
                            <td>{{ $recruitment->Last_Education ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Education From</th>
                            <td>{{ $recruitment->Last_Education_From ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Marital Status</th>
                            <td>{{ $recruitment->Marital_Status ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Religion</th>
                            <td>{{ $recruitment->Religion ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Dokumen Card --}}
    <div class="card mb-4 verify-card">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0">
                <i class="fa fa-file-pdf mr-2"></i> Dokumen Kandidat
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-bold mb-2">📷 Foto</h6>
                    <div class="document-item {{ $recruitment->Foto ? 'has-file' : '' }}">
                        @if($recruitment->Foto)
                            <img src="{{ asset($recruitment->Foto) }}" alt="Foto Kandidat" 
                                 class="img-thumbnail" style="width: 200px; height: auto; object-fit: cover;">
                            <small class="text-success mt-1 d-block">
                                <i class="fa fa-check-circle"></i> Dokumen tersedia
                            </small>
                        @else
                            <span class="text-danger"><i class="fa fa-exclamation-circle"></i> Dokumen tidak tersedia</span>
                        @endif
                    </div>
                    
                    <h6 class="font-weight-bold mb-2 mt-4">📄 CV / Resume</h6>
                    <div class="document-item {{ $recruitment->CV_File ? 'has-file' : '' }}">
                        @if($recruitment->CV_File)
                            <div>
                                <i class="fa fa-file-pdf text-danger fa-2x d-block"></i>
                                <a href="{{ asset($recruitment->CV_File) }}" target="_blank" class="text-decoration-none">
                                    Lihat CV ({{ basename($recruitment->CV_File) }})
                                </a>
                                <small class="text-success mt-1 d-block">
                                    <i class="fa fa-check-circle"></i> Dokumen tersedia
                                </small>
                            </div>
                        @else
                            <span class="text-danger"><i class="fa fa-exclamation-circle"></i> Dokumen tidak tersedia</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold mb-2">🎓 Ijazah</h6>
                    <div class="document-item {{ $recruitment->Education_Certificate_File ? 'has-file' : '' }}">
                        @if(isset($recruitment->Education_Certificate_File) && $recruitment->Education_Certificate_File)
                            <div>
                                <i class="fa fa-file-pdf text-danger fa-2x d-block"></i>
                                <a href="{{ asset($recruitment->Education_Certificate_File) }}" target="_blank" class="text-decoration-none">
                                    Lihat Ijazah ({{ basename($recruitment->Education_Certificate_File) }})
                                </a>
                                <small class="text-success mt-1 d-block">
                                    <i class="fa fa-check-circle"></i> Dokumen tersedia
                                </small>
                            </div>
                        @else
                            <span class="text-danger"><i class="fa fa-exclamation-circle"></i> Dokumen tidak tersedia</span>
                        @endif
                    </div>
                    
                    <h6 class="font-weight-bold mb-2 mt-4">📜 Sertifikat</h6>
                    <div class="document-item {{ $recruitment->Certificate_File ? 'has-file' : '' }}">
                        @if(isset($recruitment->Certificate_File) && $recruitment->Certificate_File)
                            <div>
                                <i class="fa fa-file-pdf text-danger fa-2x d-block"></i>
                                <a href="{{ asset($recruitment->Certificate_File) }}" target="_blank" class="text-decoration-none">
                                    Lihat Sertifikat ({{ basename($recruitment->Certificate_File) }})
                                </a>
                                <small class="text-success mt-1 d-block">
                                    <i class="fa fa-check-circle"></i> Dokumen tersedia
                                </small>
                            </div>
                        @else
                            <span class="text-danger"><i class="fa fa-exclamation-circle"></i> Dokumen tidak tersedia</span>
                        @endif
                    </div>

                    <h6 class="font-weight-bold mb-2 mt-4">🪪 KTP / Identitas</h6>
                    <div class="document-item {{ $recruitment->KTP_File ? 'has-file' : '' }}">
                        @if(isset($recruitment->KTP_File) && $recruitment->KTP_File)
                            <div>
                                <i class="fa fa-file-pdf text-danger fa-2x d-block"></i>
                                <a href="{{ asset($recruitment->KTP_File) }}" target="_blank" class="text-decoration-none">
                                    Lihat KTP ({{ basename($recruitment->KTP_File) }})
                                </a>
                                <small class="text-success mt-1 d-block">
                                    <i class="fa fa-check-circle"></i> Dokumen tersedia
                                </small>
                            </div>
                        @else
                            <span class="text-danger"><i class="fa fa-exclamation-circle"></i> Dokumen tidak tersedia</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Verifikasi --}}
    <div class="card mb-4 verify-card">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0">
                <i class="fa fa-edit mr-2"></i> Verifikasi & Catatan
            </h5>
        </div>
        <div class="card-body">
            <form id="verifyDocumentForm">
                {{ csrf_field() }}
                <input type="hidden" id="candidate_id" name="id" value="{{ $recruitment->ID }}">
                
                <div class="form-group">
                    <label for="note">Catatan Verifikasi (opsional)</label>
                    <textarea id="note" name="note" class="form-control" rows="3" 
                              placeholder="Tambahkan catatan jika perlu..."></textarea>
                    <small class="text-muted">Catatan akan disimpan sebagai Document_Note</small>
                </div>
            </form>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card verify-card">
                <div class="card-body">
                    <h5 class="font-weight-bold mb-3">
                        <i class="fa fa-cog mr-2"></i> Tindakan
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" id="btnApprove" class="btn btn-success btn-lg btn-block action-btn">
                                <i class="fa fa-check-circle mr-2"></i> Approve & Lanjut ke Psikotest
                            </button>
                            <small class="text-muted d-block mt-2">
                                <i class="fa fa-info-circle"></i> Kandidat akan dipindahkan ke tahap Psikotest setelah verifikasi
                            </small>
                        </div>
                        <div class="col-md-6">
                            <button type="button" id="btnReject" class="btn btn-danger btn-lg btn-block action-btn">
                                <i class="fa fa-times-circle mr-2"></i> Tolak Kandidat
                            </button>
                            <small class="text-muted d-block mt-2">
                                <i class="fa fa-info-circle"></i> Kandidat akan ditolak dengan alasan yang diberikan
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Indicator --}}
    <div id="processingModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-2 mb-0">Memproses...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Result Alert --}}
    <div id="resultAlert" class="alert alert-dismissible fade show" style="display: none;"></div>

    {{-- JavaScript --}}
    <script>
        $(function() {
            // Handle Approve
            $('#btnApprove').click(function() {
                var id = $('#candidate_id').val();
                var note = $('#note').val();
                
                if (!id) {
                    alert('Error: ID kandidat tidak ditemukan');
                    return;
                }
                
                $('#processingModal').modal('show');
                
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ url('hrd-recruitment-verify-document') }}",
                    type: "POST",
                    data: { id: id, note: note },
                    success: function(data) {
                        $('#processingModal').modal('hide');
                        var response = JSON.parse(data);
                        if (response.status === 'success') {
                            showAlert('success', response.message);
                            // Redirect ke halaman recruitment-psikotest setelah delay
                            setTimeout(function() {
                                window.location.href = "{{ url('hrd-recruitment-psikotest') }}";
                            }, 2000);
                        } else {
                            showAlert('error', response.message || 'Gagal memverifikasi dokumen');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#processingModal').modal('hide');
                        var response = jqXHR.responseJSON;
                        if (response && response.message) {
                            showAlert('error', response.message);
                        } else {
                            showAlert('error', 'Terjadi kesalahan: ' + textStatus);
                        }
                    }
                });
            });
            
            // Handle Reject
            $('#btnReject').click(function() {
                var id = $('#candidate_id').val();
                var reason = prompt('Alasan menolak kandidat:');
                
                if (!reason || reason.trim() === '') {
                    alert('Alasan harus diisi untuk menolak kandidat');
                    return;
                }
                
                if (!id) {
                    alert('Error: ID kandidat tidak ditemukan');
                    return;
                }
                
                $('#processingModal').modal('show');
                
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ url('hrd-recruitment-reject-document') }}",
                    type: "POST",
                    data: { id: id, reason: reason },
                    success: function(data) {
                        $('#processingModal').modal('hide');
                        var response = JSON.parse(data);
                        if (response.status === 'success') {
                            showAlert('warning', response.message);
                            setTimeout(function() {
                                window.location.href = "{{ url('hrd-recruitment-management') }}";
                            }, 2000);
                        } else {
                            showAlert('error', response.message || 'Gagal menolak kandidat');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#processingModal').modal('hide');
                        var response = jqXHR.responseJSON;
                        if (response && response.message) {
                            showAlert('error', response.message);
                        } else {
                            showAlert('error', 'Terjadi kesalahan: ' + textStatus);
                        }
                    }
                });
            });
        });
        
        function showAlert(type, message) {
            var alertHtml = '';
            var alertClass = '';
            
            if (type === 'success') {
                alertClass = 'alert-success';
            } else if (type === 'warning') {
                alertClass = 'alert-warning';
            } else {
                alertClass = 'alert-danger';
            }
            
            alertHtml = '<i class="fa fa-' + (type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-circle' : 'exclamation-circle') + ' mr-2"></i> ' + message;
            
            var alertDiv = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                          alertHtml + 
                          '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                          '</div>';
            
            $('#resultAlert').html(alertDiv).show();
            
            // Auto hide after 5 seconds
            setTimeout(function() {
                $('#resultAlert').alert('close');
            }, 5000);
        }
    </script>
</div>
@endsection
