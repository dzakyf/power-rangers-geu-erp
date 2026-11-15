@extends('layout.mainlayout-bp')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Pengaturan</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Pengaturan</a></li>
        <li class="breadcrumb-item"><a href="#">Survey</a></li>
        <li class="breadcrumb-item active"><span class="active">Form Builder</span></li>
    </ol>
</div>

<div class="card mb-3">
    <div class="card-body">
        @include('partner.partials.index-title', ['title' => 'Form Survey Builder'])

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="alert alert-info">
        Form aktif dipakai untuk survey baru. Survey yang sudah ada tetap memakai versi form saat survey dibuat.
        Saat mengedit form aktif, perubahan otomatis disimpan sebagai draft versi baru.
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Versi</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Max Score</th>
                <th>Pass / Conditional</th>
                <th>Published</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($versions as $version)
                <tr>
                    <td>v{{ $version->Version_No }}</td>
                    <td>{{ $version->Name }}</td>
                    <td>
                        @if($version->Status === 'ACTIVE')
                            <span class="badge badge-success">ACTIVE</span>
                        @elseif($version->Status === 'DRAFT')
                            <span class="badge badge-warning">DRAFT</span>
                        @else
                            <span class="badge badge-secondary">ARCHIVED</span>
                        @endif
                    </td>
                    <td>{{ $version->Max_Score }}</td>
                    <td>{{ $version->Pass_Threshold_Pct }}% / {{ $version->Conditional_Threshold_Pct }}%</td>
                    <td>{{ $version->Published_At ?? '-' }}</td>
                    <td>
                        <a href="{{ url('partner-survey-form-builder?version=' . $version->ID) }}" class="btn btn-sm btn-info">Buka</a>
                        @if($version->Status !== 'ACTIVE')
                            <form action="{{ url('partner-survey-form-publish') }}" method="POST" class="d-inline" onsubmit="return confirm('Publish versi ini sebagai form aktif?');">
                                @csrf
                                <input type="hidden" name="version_id" value="{{ $version->ID }}">
                                <button type="submit" class="btn btn-sm btn-success">Publish</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Belum ada form survey.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        @if($active)
            <a href="{{ url('partner-survey-form-builder?version=' . $active->ID) }}" class="btn btn-primary">Edit Form Aktif</a>
        @endif
        <form action="{{ url('partner-survey-form-create-version') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="source_version" value="{{ $active->ID ?? '' }}">
            <button type="submit" class="btn btn-outline-primary">Buat Versi Baru</button>
        </form>
    </div>
    </div>
</div>
@endsection
