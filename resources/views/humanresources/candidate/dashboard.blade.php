@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="main-content-header mb-4">
    <h1 class="h3 font-weight-bold text-gray-800">Candidate Dashboard</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item active"><span>Dashboard</span></li>
    </ol>
</div>

<!-- Alert Notifikasi Jadwal Interview / Agenda Recruitment -->
@if(!empty($notifications))
<div class="row mb-4">
    <div class="col-12">
        @foreach($notifications as $notif)
        <div class="alert alert-{{ $notif['color'] }} shadow-sm border-left-{{ $notif['color'] }} d-flex align-items-center justify-content-between flex-wrap gap-2 py-3 px-4 mb-3" style="border-left: 5px solid;">
            <div class="d-flex align-items-center">
                <div class="mr-3 text-{{ $notif['color'] }}" style="font-size: 28px;">
                    <i class="fa {{ $notif['icon'] }}"></i>
                </div>
                <div>
                    <h5 class="alert-heading mb-1 font-weight-bold">
                        {{ $notif['title'] }} 
                        <span class="badge badge-{{ $notif['color'] }} ml-2">{{ $notif['badge'] }}</span>
                    </h5>
                    <p class="mb-0">
                        @if($notif['schedule'])
                        <span class="mr-3">
                            <i class="fa fa-clock-o mr-1"></i> <strong>{{ date('d M Y - H:i', strtotime($notif['schedule'])) }} WIB</strong>
                        </span>
                        @endif
                        @if($notif['location'])
                        <span class="mr-3">
                            <i class="fa fa-map-marker mr-1"></i> {{ $notif['location'] }}
                        </span>
                        @endif
                    </p>
                    @if($notif['note'])
                    <small class="text-muted d-block mt-1 font-italic">{{ $notif['note'] }}</small>
                    @endif
                </div>
            </div>
            <div>
                <a href="{{ url('/candidate/progress') }}" class="btn btn-sm btn-{{ $notif['color'] }} font-weight-bold px-3">
                    Lihat Progress <i class="fa fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Welcome Card -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="font-weight-bold text-primary mb-1">Selamat Datang, {{ $rec->Name ?? Auth::user()->Name }}</h4>
                        <p class="text-muted mb-0">
                            Posisi yang Dilamar: <strong class="text-dark">{{ $position ?? '-' }}</strong> &middot; 
                            Tanggal Lamar: <strong class="text-dark">{{ $createdDate ? date('d M Y', strtotime($createdDate)) : '-' }}</strong> &middot; 
                            Status Saat Ini: <span class="badge badge-primary px-2 py-1">{{ $currentStatus }}</span>
                        </p>
                    </div>
                    <div class="mt-2 mt-md-0">
                        <a href="{{ url('/candidate/progress') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-line-chart mr-1"></i> Detail Progress
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recruitment Progress Tracker (8 Tahap Sesuai Alur Standar) -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-tasks mr-2"></i>Alur & Tahapan Rekrutmen (8 Tahap)</h6>
            </div>
            <div class="card-body p-4">
                <div class="row text-center">
                    @foreach($steps as $step)
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="p-2 rounded" style="background-color: {{ $step['done'] ? '#e8f5e9' : ($step['current'] ? '#fff8e1' : '#f8f9fa') }}; border: 1px solid {{ $step['done'] ? '#c8e6c9' : ($step['current'] ? '#ffe082' : '#e9ecef') }};">
                            <div style="width:36px;height:36px;border-radius:50%;margin:0 auto 8px;background:{{ $step['done'] ? '#28a745' : ($step['current'] ? '#ffc107' : '#ced4da') }};color:{{ $step['current'] ? '#212529' : '#fff' }};line-height:36px;font-weight:bold;font-size:14px;">
                                @if($step['done'])
                                    ✓
                                @elseif($step['current'])
                                    <i class="fa fa-spinner fa-spin"></i>
                                @else
                                    {{ $step['no'] }}
                                @endif
                            </div>
                            <span class="d-block font-weight-bold small text-dark">{{ $step['label'] }}</span>
                            @if($step['date'])
                                <small class="text-muted d-block">{{ date('d M Y', strtotime($step['date'])) }}</small>
                            @else
                                <small class="text-muted d-block">-</small>
                            @endif
                            <span class="badge {{ $step['done'] ? 'badge-success' : ($step['current'] ? 'badge-warning text-dark' : 'badge-light text-muted') }} mt-1">
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

<!-- Ringkasan Kartu Info -->
<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-4 text-center">
            <div class="card-body">
                <div class="text-muted small font-weight-bold text-uppercase mb-1">Status Lamaran</div>
                <h5 class="font-weight-bold text-primary mb-0">{{ $currentStatus }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-4 text-center">
            <div class="card-body">
                <div class="text-muted small font-weight-bold text-uppercase mb-1">Kelengkapan Dokumen</div>
                <h5 class="font-weight-bold {{ $documentsCount >= 6 ? 'text-success' : 'text-warning' }} mb-0">{{ $documentsCount }} / 6 Dokumen</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-4 text-center">
            <div class="card-body">
                <div class="text-muted small font-weight-bold text-uppercase mb-1">Hasil Asesmen / Tes</div>
                <h5 class="font-weight-bold {{ $hasTest ? 'text-success' : 'text-muted' }} mb-0">{{ $hasTest ? 'Sudah Diuji' : 'Belum Ada' }}</h5>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 mb-4 text-center">
            <div class="card-body">
                <div class="text-muted small font-weight-bold text-uppercase mb-1">Status Interview</div>
                <h5 class="font-weight-bold {{ $hasInterview ? 'text-success' : 'text-muted' }} mb-0">{{ $hasInterview ? 'Lulus HR' : ($hasFinalInterview ? 'Lulus User' : 'Pending') }}</h5>
            </div>
        </div>
    </div>
</div>

<!-- Navigasi Menu Cepat -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body text-center p-4">
                <a href="{{ url('/candidate/profile') }}" class="btn btn-outline-primary px-4 py-2 mr-2 mb-2">
                    <i class="fa fa-user mr-1"></i> Data Profil Saya
                </a>
                <a href="{{ url('/candidate/documents') }}" class="btn btn-outline-info px-4 py-2 mr-2 mb-2">
                    <i class="fa fa-upload mr-1"></i> Unggah Dokumen
                </a>
                <a href="{{ url('/candidate/assessment') }}" class="btn btn-outline-warning px-4 py-2 mr-2 mb-2">
                    <i class="fa fa-pencil-square-o mr-1"></i> Riwayat Asesmen
                </a>
                <a href="{{ url('/candidate/progress') }}" class="btn btn-primary px-4 py-2 mb-2">
                    <i class="fa fa-line-chart mr-1"></i> Timeline Rekrutmen
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
