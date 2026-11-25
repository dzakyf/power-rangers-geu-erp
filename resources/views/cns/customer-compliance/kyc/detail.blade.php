@extends('layout.mainlayout-cns')

@section('content-wrapper')
@include('cns.partials.page-header', [
    'title' => 'Detail Form KYC: ' . $kyc->kyc_no,
    'breadcrumbs' => [
        ['label' => 'Customer Compliance'],
        ['label' => 'KYC', 'url' => route('cns-customer-compliance-kyc.index')],
        ['label' => $kyc->kyc_no],
    ],
])

@php
    $approvalStatus = $kyc->approval_status ?: \App\CnsCustomerKyc::STATUS_PENDING;
    $statusBadgeClass = $approvalStatus === \App\CnsCustomerKyc::STATUS_APPROVED
        ? 'bg-success'
        : ($approvalStatus === \App\CnsCustomerKyc::STATUS_REJECTED ? 'bg-danger' : 'bg-warning text-dark');
@endphp

<!-- Action Toolbar -->
<div class="card cns-card mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center" style="gap: 8px;">
        <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
            <a href="{{ route('cns-customer-compliance-kyc.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali ke Daftar
            </a>
            <a href="{{ route('cns-customer-compliance-kyc.print', ['id' => $kyc->id]) }}" class="btn btn-primary" target="_blank">
                <i class="fa fa-print"></i> Cetak PDF KYC
            </a>
            @if(!$kyc->isApproved())
                <a href="{{ route('cns-customer-compliance-kyc.edit', ['id' => $kyc->id]) }}" class="btn btn-warning">
                    <i class="fa fa-edit"></i> Edit Data
                </a>
            @endif
            @can('cns.customer_compliance_kyc.approve')
                @if(!$kyc->isApproved())
                    <form action="{{ route('cns-customer-compliance-kyc.approve', ['id' => $kyc->id]) }}" method="POST" class="d-inline" onsubmit="var f=this; if(f.dataset.confirmed==='1'){return true;} swal({title:'Konfirmasi Approval',text:'Approve form KYC ini?',icon:'info',buttons:['Batal','Approve']}).then(function(ok){if(ok){f.dataset.confirmed='1';f.submit();}}); return false;">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-check"></i> Approve
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#rejectKycModal">
                        <i class="fa fa-times"></i> Reject
                    </button>
                @endif
            @endcan
        </div>
        @if(!$kyc->isApproved())
            <form action="{{ route('cns-customer-compliance-kyc.delete', ['id' => $kyc->id]) }}" method="POST" onsubmit="var f=this; if(f.dataset.confirmed==='1'){return true;} swal({title:'Konfirmasi',text:'Yakin ingin menghapus form KYC ini?',icon:'warning',buttons:['Batal','Ya'],dangerMode:true}).then(function(ok){if(ok){f.dataset.confirmed='1';f.submit();}}); return false;">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="fa fa-trash"></i> Hapus
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Header / Metadata Card -->
<div class="card cns-card mb-3">
    <div class="card-header">
        <h5 class="card-title text-primary">
            <i class="fa fa-info-circle mr-2"></i>Header & Status Dokumen
        </h5>
        <span class="badge rounded-pill cns-status-badge {{ $statusBadgeClass }}">{{ $approvalStatus }}</span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <span class="text-muted d-block small">Nomor Dokumen KYC:</span>
                <strong class="h6 text-dark">{{ $kyc->kyc_no }}</strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted d-block small">Date:</span>
                <strong>{{ $kyc->form_date ? $kyc->form_date->format('d F Y') : '-' }}</strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted d-block small">Name of person filling in this form:</span>
                <strong>{{ $kyc->filler_name }}</strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted d-block small">Linked Partner:</span>
                @if($kyc->supplier)
                    <span class="badge badge-info">{{ $kyc->supplier->Company_Name }}</span>
                @else
                    <span class="text-muted">Standalone (Tidak Terhubung)</span>
                @endif
            </div>
        </div>
        @if($kyc->approved_by || $kyc->rejection_reason)
            <hr class="my-3">
            <div class="row">
                <div class="col-md-3">
                    <span class="text-muted d-block small">{{ $kyc->isRejected() ? 'Ditolak Oleh:' : 'Disetujui Oleh:' }}</span>
                    <strong>{{ $kyc->approved_by ?: '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block small">Waktu:</span>
                    <strong>{{ $kyc->approved_at ? $kyc->approved_at->format('d F Y H:i') : '-' }}</strong>
                </div>
                @if($kyc->rejection_reason)
                    <div class="col-md-6">
                        <span class="text-muted d-block small">Alasan Penolakan:</span>
                        <span class="text-danger font-weight-bold">{{ $kyc->rejection_reason }}</span>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

@can('cns.customer_compliance_kyc.approve')
    @if(!$kyc->isApproved())
        <div class="modal fade" id="rejectKycModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="{{ route('cns-customer-compliance-kyc.reject', ['id' => $kyc->id]) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Form KYC</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label for="rejection_reason">Alasan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" id="rejection_reason" rows="3" class="form-control" required maxlength="1000" placeholder="Tuliskan alasan penolakan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Reject KYC</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endcan

<!-- General Company Information Card -->
<div class="card cns-card mb-3">
    <div class="card-header">
        <h5 class="card-title text-primary">
            <i class="fa fa-building mr-2"></i>General Company Information
        </h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-striped mb-0">
            <tbody>
                <tr>
                    <th style="width: 35%;">a) Company name according to official Commercial Register</th>
                    <td><strong>{{ $kyc->company_name }}</strong></td>
                </tr>
                <tr>
                    <th>b) Company registration number</th>
                    <td>{{ $kyc->company_reg_number ?: '-' }}</td>
                </tr>
                <tr>
                    <th>c) Date of incorporation</th>
                    <td>{{ $kyc->incorporation_date ? $kyc->incorporation_date->format('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <th>d) Registered address</th>
                    <td>{!! nl2br(e($kyc->registered_address ?: '-')) !!}</td>
                </tr>
                <tr>
                    <th>e) Address of Admin + Accounting</th>
                    <td>{!! nl2br(e($kyc->admin_accounting_address ?: '-')) !!}</td>
                </tr>
                <tr>
                    <th>f) Type of legal entity</th>
                    <td>{{ $kyc->legal_entity_type ?: '-' }}</td>
                </tr>
                <tr>
                    <th>g) Value Added Tax (VAT) number</th>
                    <td>{{ $kyc->vat_number ?: '-' }}</td>
                </tr>
                <tr>
                    <th>h) Website address</th>
                    <td>
                        @if($kyc->website)
                            <a href="{{ Str::startsWith($kyc->website, ['http://', 'https://']) ? $kyc->website : 'https://' . $kyc->website }}" target="_blank">
                                {{ $kyc->website }} <i class="fa fa-external-link-alt small"></i>
                            </a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>i) Contact person for trading</th>
                    <td>{{ $kyc->trading_contact_person ?: '-' }}</td>
                </tr>
                <tr>
                    <th>j) Contact details for i)</th>
                    <td>{{ $kyc->trading_contact_details ?: '-' }}</td>
                </tr>
                <tr>
                    <th>k) Contact person for finance</th>
                    <td>{{ $kyc->finance_contact_person ?: '-' }}</td>
                </tr>
                <tr>
                    <th>l) Contact details for k)</th>
                    <td>{{ $kyc->finance_contact_details ?: '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Ownership & Management Card -->
<div class="card cns-card mb-3">
    <div class="card-header">
        <h5 class="card-title text-primary">
            <i class="fa fa-users mr-2"></i>Ownership & Management
        </h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-striped mb-0">
            <tbody>
                <tr>
                    <th style="width: 35%;">a) Business activities</th>
                    <td>{!! nl2br(e($kyc->business_activities ?: '-')) !!}</td>
                </tr>
                <tr>
                    <th>b) Countries of activities</th>
                    <td>{!! nl2br(e($kyc->countries_of_activities ?: '-')) !!}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Supporting Documents Card -->
<div class="card cns-card mb-3">
    <div class="card-header">
        <h5 class="card-title text-primary">
            <i class="fa fa-paperclip mr-2"></i>Supporting Documents (Scanned Copies)
        </h5>
    </div>
    <div class="card-body">
        @php
            $supportingDocs = [
                [
                    'type'  => 'register',
                    'label' => 'Commercial / Company Register Extract',
                    'icon'  => 'fa-file-alt text-primary',
                    'btn'   => 'btn-outline-primary',
                    'path'  => $kyc->doc_register_extract,
                    'name'  => $kyc->doc_register_extract_name,
                ],
                [
                    'type'  => 'certificate',
                    'label' => 'Sustainability Certificate (e.g. ISCC) / Trading Certificate',
                    'icon'  => 'fa-certificate text-success',
                    'btn'   => 'btn-outline-success',
                    'path'  => $kyc->doc_certificate,
                    'name'  => $kyc->doc_certificate_name,
                ],
            ];
        @endphp
        <div class="row">
            @foreach($supportingDocs as $doc)
                @php
                    $displayName = $doc['name'] ?: ($doc['path'] ? basename($doc['path']) : '');
                    $inlineUrl = route('cns-customer-compliance-kyc.download', ['id' => $kyc->id, 'type' => $doc['type']]);
                    $downloadUrl = route('cns-customer-compliance-kyc.download', ['id' => $kyc->id, 'type' => $doc['type'], 'download' => 1]);
                @endphp
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-3 h-100 bg-light">
                        <h6 class="font-weight-bold text-dark mb-2">
                            <i class="fa {{ $doc['icon'] }} mr-2"></i>{{ $doc['label'] }}
                        </h6>
                        @if($doc['path'])
                            <p class="mb-2 text-muted small">File: {{ $displayName }}</p>
                            <div class="d-flex flex-wrap" style="gap: 6px;">
                                <button type="button" class="btn btn-sm btn-info" onclick='window.cnsOpenDocPreview(@json($inlineUrl), @json($displayName))'>
                                    <i class="fa fa-eye"></i> Preview Dokumen
                                </button>
                                <a href="{{ $downloadUrl }}" class="btn btn-sm {{ $doc['btn'] }} no-cns-preview">
                                    <i class="fa fa-download"></i> Unduh Dokumen
                                </a>
                            </div>
                        @else
                            <span class="badge badge-secondary">Belum Diunggah</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Audit Trail Footer -->
<div class="card cns-card mb-4 text-muted small">
    <div class="card-body py-2 d-flex justify-content-between">
        <span>Dibuat oleh: <strong>{{ $kyc->created_by ?: 'system' }}</strong> pada {{ $kyc->created_at ? $kyc->created_at->format('d/m/Y H:i') : '-' }}</span>
        <span>Terakhir diperbarui: <strong>{{ $kyc->updated_by ?: '-' }}</strong> pada {{ $kyc->updated_at ? $kyc->updated_at->format('d/m/Y H:i') : '-' }}</span>
    </div>
</div>
@endsection
