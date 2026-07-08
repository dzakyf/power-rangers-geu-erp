@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Assessment</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/candidate/dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active"><span>Assessment</span></li>
    </ol>
</div>

<div class="card mb-4">
    <div class="card-body text-center py-5">
        <h4 class="text-muted">Online Assessment</h4>
        <p class="text-muted">Fitur assessment online akan segera tersedia. Pantau terus halaman ini untuk informasi terbaru.</p>
    </div>
</div>

@if($rec && ($rec->Psikotest_Score || $rec->Test_Score || $rec->Technical_Score))
<div class="card">
    <div class="card-header"><h5>Test Results from HR</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>Test Type</th><th>Score</th><th>Note</th><th>File</th></tr></thead>
                <tbody>
                    @if($rec->Psikotest_Score)
                    <tr>
                        <td>Psikotest</td>
                        <td>{{ $rec->Psikotest_Score }}</td>
                        <td>{{ $rec->Psikotest_Note ?? '-' }}</td>
                        <td>@if($rec->Psikotest_File)<a href="{{ asset($rec->Psikotest_File) }}" target="_blank">Download</a>@else-@endif</td>
                    </tr>
                    @endif
                    @if($rec->Test_Score)
                    <tr>
                        <td>User Test</td>
                        <td>{{ $rec->Test_Score }}</td>
                        <td>{{ $rec->Test_Note ?? '-' }}</td>
                        <td>@if($rec->Test_File)<a href="{{ asset($rec->Test_File) }}" target="_blank">Download</a>@else-@endif</td>
                    </tr>
                    @endif
                    @if($rec->Technical_Score)
                    <tr>
                        <td>Technical Test</td>
                        <td>{{ $rec->Technical_Score }}</td>
                        <td>{{ $rec->Technical_Note ?? '-' }}</td>
                        <td>@if($rec->Technical_File)<a href="{{ asset($rec->Technical_File) }}" target="_blank">Download</a>@else-@endif</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-4">
        <p class="text-muted">Belum ada hasil tes dari HR.</p>
    </div>
</div>
@endif
@endsection
