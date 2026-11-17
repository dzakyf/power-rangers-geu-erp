@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto">Candidate Dashboard</h4>
            <span class="text-muted mt-1 tx-13 ms-2 mb-0">/ Portal Kandidat</span>
        </div>
    </div>
</div>

@if(!empty($isDefaultPassword))
<div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-sm mb-4" role="alert">
    <div>
        <i class="fa fa-exclamation-triangle me-2"></i>
        <strong>Keamanan Akun:</strong> Anda masih menggunakan password default. Demi keamanan data lamaran Anda, silakan segera ubah password Anda.
    </div>
    <a href="{{ url('/candidate/password') }}" class="btn btn-sm btn-warning fw-bold">
        <i class="fa fa-key me-1"></i> Ubah Password
    </a>
</div>
@endif

@if(!empty($notifications))
<div class="row mb-3">
    <div class="col-12">
        @foreach($notifications as $notif)
        <div class="alert alert-{{ $notif['color'] }} shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2 py-3 px-4 mb-3" style="border-left: 5px solid;">
            <div class="d-flex align-items-center">
                <div class="me-3 text-{{ $notif['color'] }}" style="font-size: 26px;">
                    <i class="fa {{ $notif['icon'] }}"></i>
                </div>
                <div>
                    <h5 class="alert-heading mb-1 fw-bold">
                        {{ $notif['title'] }}
                        <span class="badge bg-{{ $notif['color'] }} text-white ms-2">{{ $notif['badge'] }}</span>
                    </h5>
                    <p class="mb-0">
                        @if($notif['schedule'])
                        <span class="me-3">
                            <i class="fa fa-clock me-1"></i> <strong>{{ date('d M Y - H:i', strtotime($notif['schedule'])) }} WIB</strong>
                        </span>
                        @endif
                        @if($notif['location'])
                        <span class="me-3">
                            <i class="fa fa-map-marker-alt me-1"></i> {{ $notif['location'] }}
                        </span>
                        @endif
                    </p>
                    @if($notif['note'])
                    <small class="text-muted d-block mt-1 fst-italic">{{ $notif['note'] }}</small>
                    @endif
                </div>
            </div>
            <div>
                <a href="{{ url('/candidate/progress') }}" class="btn btn-sm btn-{{ $notif['color'] }} fw-bold px-3">
                    Lihat & Konfirmasi <i class="fa fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold text-primary mb-1">Selamat Datang, {{ $rec->Name ?? Auth::user()->Name }}</h4>
                        <p class="text-muted mb-0">
                            Posisi yang Dilamar: <strong class="text-dark">{{ $position ?? '-' }}</strong> &middot;
                            Tanggal Lamar: <strong class="text-dark">{{ $createdDate ? date('d M Y', strtotime($createdDate)) : '-' }}</strong> &middot;
                            Status Saat Ini: <span class="badge bg-primary text-white px-2 py-1">{{ $currentStatus }}</span>
                        </p>
                    </div>
                    <div>
                        <a href="{{ url('/candidate/progress') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-chart-line me-1"></i> Detail Progress
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 fw-bold text-primary"><i class="fa fa-tasks me-2"></i>Alur & Tahapan Rekrutmen (8 Tahap)</h6>
            </div>
            <div class="card-body p-4">
                <div class="row text-center">
                    @foreach($steps as $step)
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="p-3 rounded h-100" style="background-color: {{ $step['done'] ? '#e8f5e9' : ($step['current'] ? '#fff8e1' : '#f8f9fa') }}; border: 1px solid {{ $step['done'] ? '#c8e6c9' : ($step['current'] ? '#ffe082' : '#e9ecef') }};">
                            <div style="width:36px;height:36px;border-radius:50%;margin:0 auto 8px;background:{{ $step['done'] ? '#28a745' : ($step['current'] ? '#ffc107' : '#ced4da') }};color:{{ $step['current'] ? '#212529' : '#fff' }};line-height:36px;font-weight:bold;font-size:14px;">
                                @if($step['done'])
                                    <i class="fa fa-check"></i>
                                @elseif($step['current'])
                                    <i class="fa fa-spinner fa-spin"></i>
                                @else
                                    {{ $step['no'] }}
                                @endif
                            </div>
                            <span class="d-block fw-bold small text-dark">{{ $step['label'] }}</span>
                            <small class="text-muted d-block mt-1">
                                {{ $step['date'] ? date('d M Y', strtotime($step['date'])) : '-' }}
                            </small>
                            <span class="badge {{ $step['done'] ? 'bg-success text-white' : ($step['current'] ? 'bg-warning text-dark' : 'bg-light text-muted') }} mt-2">
                                {{ $step['done'] ? 'Selesai' : ($step['current'] ? 'Sedang Berjalan' : 'Menunggu') }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-4 text-center">
            <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase mb-1">Status Lamaran</div>
                <h5 class="fw-bold text-primary mb-0">{{ $currentStatus }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-4 text-center">
            <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase mb-1">Kelengkapan Dokumen</div>
                <h5 class="fw-bold {{ $documentsCount >= 6 ? 'text-success' : 'text-warning' }} mb-0">{{ $documentsCount }} / 6 Dokumen</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-4 text-center">
            <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase mb-1">Hasil Asesmen / Tes</div>
                <h5 class="fw-bold {{ $hasTest ? 'text-success' : 'text-muted' }} mb-0">{{ $hasTest ? 'Sudah Diuji' : 'Belum Ada' }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-4 text-center">
            <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase mb-1">Status Interview</div>
                <h5 class="fw-bold {{ $hasInterview ? 'text-success' : 'text-muted' }} mb-0">{{ $hasInterview ? 'Lulus HR' : ($hasFinalInterview ? 'Lulus User' : 'Pending') }}</h5>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body text-center p-4">
                <a href="{{ url('/candidate/profile') }}" class="btn btn-outline-primary px-4 py-2 me-2 mb-2">
                    <i class="fa fa-user me-1"></i> Data Diri & Profil
                </a>
                <a href="{{ url('/candidate/documents') }}" class="btn btn-outline-info px-4 py-2 me-2 mb-2">
                    <i class="fa fa-upload me-1"></i> Unggah Dokumen
                </a>
                <a href="{{ url('/candidate/assessment') }}" class="btn btn-outline-warning px-4 py-2 me-2 mb-2">
                    <i class="fa fa-clipboard-check me-1"></i> Riwayat Asesmen
                </a>
                <a href="{{ url('/candidate/progress') }}" class="btn btn-primary px-4 py-2 mb-2">
                    <i class="fa fa-chart-line me-1"></i> Timeline & Konfirmasi
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
