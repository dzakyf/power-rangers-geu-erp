@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Candidate Dashboard</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item active"><span>Dashboard</span></li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-body">
                <h4>Welcome, {{ $rec->Name ?? Auth::user()->Name }}</h4>
                <p class="text-muted">
                    Position Applied: <strong>{{ $position ?? '-' }}</strong>
                    &middot; Applied: <strong>{{ $createdDate ? date('d M Y', strtotime($createdDate)) : '-' }}</strong>
                    &middot; Status: <strong>{{ $currentStatus }}</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card mb-4">
            <div class="card-header"><h5>Recruitment Progress</h5></div>
            <div class="card-body">
                <div class="row">
                    @foreach($steps as $step)
                    <div class="col-2 text-center">
                        <div style="width:40px;height:40px;border-radius:50%;margin:0 auto;background:{{ $step['done'] ? '#28a745' : '#ddd' }};color:#fff;line-height:40px;font-weight:bold;">
                            {{ $step['done'] ? '✓' : $loop->iteration }}
                        </div>
                        <small>{{ $step['label'] }}</small><br>
                        @if($step['date'])<small class="text-muted">{{ date('d/m', strtotime($step['date'])) }}</small>@endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4 text-center">
            <div class="card-body">
                <h5>{{ $currentStatus }}</h5>
                <p class="text-muted">Application Status</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mb-4 text-center">
            <div class="card-body">
                <h5>{{ $documentsCount }}/6</h5>
                <p class="text-muted">Documents</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mb-4 text-center">
            <div class="card-body">
                <h5>{{ $hasTest ? '✓' : '-' }}</h5>
                <p class="text-muted">Assessment</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mb-4 text-center">
            <div class="card-body">
                <h5>{{ $hasInterview ? '✓' : ($hasFinalInterview ? '✓' : '-') }}</h5>
                <p class="text-muted">Interview</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body text-center">
                <a href="{{ url('/candidate/profile') }}" class="btn btn-primary mr-2">My Profile</a>
                <a href="{{ url('/candidate/documents') }}" class="btn btn-info mr-2">Documents</a>
                <a href="{{ url('/candidate/assessment') }}" class="btn btn-warning mr-2">Assessment</a>
                <a href="{{ url('/candidate/progress') }}" class="btn btn-secondary">Progress</a>
            </div>
        </div>
    </div>
</div>
@endsection
