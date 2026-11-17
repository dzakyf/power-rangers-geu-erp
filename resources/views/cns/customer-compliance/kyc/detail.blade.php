@extends('layout.mainlayout-cns')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Detail Form KYC: {{ $kyc->kyc_no }}</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Contract</a></li>
        <li class="breadcrumb-item">Customer Compliance</li>
        <li class="breadcrumb-item"><a href="{{ route('cns-customer-compliance-kyc.index') }}">KYC</a></li>
        <li class="breadcrumb-item active"><span class="active">{{ $kyc->kyc_no }}</span></li>
    </ol>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<!-- Action Toolbar -->
<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('cns-customer-compliance-kyc.index') }}" class="btn btn-secondary mr-2">
                <i class="fa fa-arrow-left"></i> Kembali ke Daftar
            </a>
            <a href="{{ route('cns-customer-compliance-kyc.print', ['id' => $kyc->id]) }}" class="btn btn-primary mr-2" target="_blank">
                <i class="fa fa-print"></i> Cetak PDF KYC
            </a>
            <a href="{{ route('cns-customer-compliance-kyc.edit', ['id' => $kyc->id]) }}" class="btn btn-warning mr-2">
                <i class="fa fa-edit"></i> Edit Data
            </a>
        </div>
        <form action="{{ route('cns-customer-compliance-kyc.delete', ['id' => $kyc->id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus form KYC ini?');">
            @csrf
            <button type="submit" class="btn btn-danger">
                <i class="fa fa-trash"></i> Hapus
            </button>
        </form>
    </div>
</div>

<!-- Header / Metadata Card -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0 text-primary font-weight-bold">
            <i class="fa fa-info-circle mr-2"></i>Header & Status Dokumen
        </h5>
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
    </div>
</div>

<!-- General Company Information Card -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0 text-primary font-weight-bold">
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
<div class="card mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0 text-primary font-weight-bold">
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
<div class="card mb-3">
    <div class="card-header bg-light">
        <h5 class="mb-0 text-primary font-weight-bold">
            <i class="fa fa-paperclip mr-2"></i>Supporting Documents (Scanned Copies)
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-2">
                <div class="border rounded p-3 h-100 bg-light">
                    <h6 class="font-weight-bold text-dark mb-2">
                        <i class="fa fa-file-alt text-primary mr-2"></i>Commercial / Company Register Extract
                    </h6>
                    @if($kyc->doc_register_extract)
                        <p class="mb-2 text-muted small">File: {{ $kyc->doc_register_extract_name ?: basename($kyc->doc_register_extract) }}</p>
                        <a href="{{ route('cns-customer-compliance-kyc.download', ['id' => $kyc->id, 'type' => 'register']) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="fa fa-download"></i> Unduh Dokumen
                        </a>
                    @else
                        <span class="badge badge-secondary">Belum Diunggah</span>
                    @endif
                </div>
            </div>

            <div class="col-md-6 mb-2">
                <div class="border rounded p-3 h-100 bg-light">
                    <h6 class="font-weight-bold text-dark mb-2">
                        <i class="fa fa-certificate text-success mr-2"></i>Sustainability Certificate (e.g. ISCC) / Trading Certificate
                    </h6>
                    @if($kyc->doc_certificate)
                        <p class="mb-2 text-muted small">File: {{ $kyc->doc_certificate_name ?: basename($kyc->doc_certificate) }}</p>
                        <a href="{{ route('cns-customer-compliance-kyc.download', ['id' => $kyc->id, 'type' => 'certificate']) }}" class="btn btn-sm btn-outline-success" target="_blank">
                            <i class="fa fa-download"></i> Unduh Dokumen
                        </a>
                    @else
                        <span class="badge badge-secondary">Belum Diunggah</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audit Trail Footer -->
<div class="card mb-4 text-muted small">
    <div class="card-body py-2 d-flex justify-content-between">
        <span>Dibuat oleh: <strong>{{ $kyc->created_by ?: 'system' }}</strong> pada {{ $kyc->created_at ? $kyc->created_at->format('d/m/Y H:i') : '-' }}</span>
        <span>Terakhir diperbarui: <strong>{{ $kyc->updated_by ?: '-' }}</strong> pada {{ $kyc->updated_at ? $kyc->updated_at->format('d/m/Y H:i') : '-' }}</span>
    </div>
</div>
@endsection
