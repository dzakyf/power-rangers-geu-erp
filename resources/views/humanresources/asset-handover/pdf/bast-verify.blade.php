<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi BAST — {{ $bastNo }}</title>
    <link rel="stylesheet" href="{{ asset('adminor/plugins/bootstrap/css/bootstrap.min.css') }}">
</head>
<body class="bg-light py-4">
<div class="container" style="max-width: 780px;">
    <div class="card shadow-sm border-0">
        <div class="card-header {{ $isValidSignature ? ($isOfficial ? 'bg-success text-white' : 'bg-warning text-dark') : 'bg-danger text-white' }}" @if($isValidSignature && !$isOfficial) style="background-color:#fff3cd;color:#664d03;" @endif>
            <h5 class="mb-0 fw-bold">
                @if(!$isValidSignature)
                    Tanda Tangan Digital Tidak Valid
                @elseif($isOfficial)
                    Dokumen BAST Resmi &amp; Terverifikasi
                @else
                    Dokumen BAST Berstatus DRAFT (Belum Final)
                @endif
            </h5>
        </div>
        <div class="card-body">
            @if(!$isValidSignature)
                <div class="alert alert-danger mb-0">
                    Parameter tanda tangan verifikasi HMAC untuk dokumen <strong>{{ $bastNo }}</strong> tidak cocok.
                </div>
            @else
                <div class="row g-2 small mb-3">
                    <div class="col-md-6"><strong>Nomor BAST Sistem:</strong> {{ $bastNo }}</div>
                    <div class="col-md-6"><strong>No. Handover:</strong> {{ $handover->Handover_No }}</div>
                    <div class="col-md-6"><strong>Jenis Dokumen:</strong> {{ $docType === 'RETURN' ? 'BAST Pengembalian (Return)' : 'BAST Serah Terima (Handover)' }}</div>
                    <div class="col-md-6"><strong>Status Transaksi:</strong> <span class="badge bg-primary text-white">{{ $handover->Status }}</span></div>
                    <div class="col-md-6"><strong>Lead Pemberi:</strong> {{ $handover->Lead_Name ?? $handover->User_Name }}</div>
                    <div class="col-md-6"><strong>PIC Penerima:</strong> {{ $handover->PIC_Name }} ({{ $handover->PIC_Department }})</div>
                    @if($docType === 'RETURN')
                        <div class="col-md-6"><strong>PIC Approval Return:</strong> {{ $handover->Return_Lead_Name ?? '-' }}</div>
                        <div class="col-md-6"><strong>Penerima Pengembalian:</strong> {{ $handover->Return_Receiver_Name ?? '-' }}</div>
                    @endif
                </div>

                <h6 class="fw-bold border-bottom pb-1 mb-2">Ringkasan Aset</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Asset No</th>
                                <th>Kode</th>
                                <th>Deskripsi</th>
                                <th class="text-end">Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $isReturnVerify = strtoupper((string) ($docType ?? '')) === 'RETURN';
                                $rowNo = 0;
                            @endphp
                            @foreach($items as $it)
                                @if($isReturnVerify && !in_array(strtoupper((string) ($it->Status ?? '')), ['RETURN_PENDING', 'RETURNED'], true))
                                    @continue
                                @endif
                                @php $rowNo++; @endphp
                                <tr>
                                    <td>{{ $rowNo }}</td>
                                    <td>{{ $it->Asset_No ?? '-' }}</td>
                                    <td>{{ $it->Product_Code }}</td>
                                    <td>{{ $it->Description }}</td>
                                    <td class="text-end">{{ $isReturnVerify ? ($it->Returned_Qty ?? $it->Qty) : $it->Qty }} {{ $it->Unit }}</td>
                                    <td>{{ $it->Status ?: '-' }}</td>
                                </tr>
                            @endforeach
                            @if($rowNo === 0)
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        {{ $isReturnVerify ? 'Belum ada item pengembalian tercatat.' : 'Tidak ada item tercatat.' }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(!empty($approvalTrail))
                    <h6 class="fw-bold border-bottom pb-1 mb-2">Jejak Approval Sistem</h6>
                    @include('humanresources.approval.partials.trail-table', ['trail' => $approvalTrail])
                @endif
            @endif
        </div>
    </div>
</div>
</body>
</html>
