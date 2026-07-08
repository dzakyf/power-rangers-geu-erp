@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="main-content-header">
    <h1>Recruitment Progress</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/candidate/dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active"><span>Progress</span></li>
    </ol>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>Stage</th><th>Date</th><th>Status</th><th>Notes</th></tr></thead>
                <tbody>
                    @foreach($stages as $s)
                    <tr>
                        <td>{{ $s['stage'] }}</td>
                        <td>{{ $s['date'] ? date('d M Y', strtotime($s['date'])) : '-' }}</td>
                        <td>
                            @if(in_array($s['status'], ['Completed','INTERVIEWED','BUSINESS_REVIEW','ACCEPT']))
                            <span class="badge badge-success">{{ $s['status'] }}</span>
                            @elseif(in_array($s['status'], ['REJECT','CANCELED']))
                            <span class="badge badge-danger">{{ $s['status'] }}</span>
                            @elseif($s['status'] == 'PENDING')
                            <span class="badge badge-warning">{{ $s['status'] }}</span>
                            @else
                            <span class="badge badge-secondary">{{ $s['status'] }}</span>
                            @endif
                        </td>
                        <td>{{ $s['note'] ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
