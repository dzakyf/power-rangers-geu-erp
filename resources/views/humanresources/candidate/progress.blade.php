@extends('layout.mainlayout-candidate')

@section('content-wrapper')
<div class="main-content-header mb-4">
    <h1 class="h3 font-weight-bold text-gray-800">Recruitment Progress & Timeline</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/candidate/dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active"><span>Progress</span></li>
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
                <span class="badge badge-pill badge-{{ $notif['color'] }} p-2">Agenda Aktif</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa fa-list-ol mr-2"></i>Status Tahapan Rekrutmen HCGA</h6>
        <span class="badge badge-info py-1 px-3">Posisi: {{ $rec->Position ?? '-' }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th style="width: 250px;">Tahapan Rekrutmen</th>
                        <th style="width: 170px;">Tanggal / Jadwal</th>
                        <th style="width: 180px;">Status Evaluasi</th>
                        <th>Keterangan / Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stages as $s)
                    <tr>
                        <td class="text-center font-weight-bold text-muted">{{ $s['no'] }}</td>
                        <td class="font-weight-bold text-dark">
                            {{ $s['stage'] }}
                        </td>
                        <td>
                            @if($s['date'])
                                <i class="fa fa-calendar-o mr-1 text-muted"></i>
                                <span>{{ date('d M Y', strtotime($s['date'])) }}</span>
                                @if(strlen($s['date']) > 10 && date('H:i', strtotime($s['date'])) !== '00:00')
                                    <small class="text-muted d-block">{{ date('H:i', strtotime($s['date'])) }} WIB</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if(in_array($s['raw_status'], ['ACCEPT', 'HIRED', 'CLOSED']) || $s['status'] === 'Completed')
                                <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i>{{ $s['status'] }}</span>
                            @elseif(in_array($s['raw_status'], ['REJECT', 'CANCELED', 'DECLINED']))
                                <span class="badge badge-danger px-2 py-1"><i class="fa fa-times mr-1"></i>{{ $s['status'] }}</span>
                            @elseif($s['raw_status'] === 'SEEK')
                                <span class="badge badge-info px-2 py-1"><i class="fa fa-spinner fa-spin mr-1"></i>{{ $s['status'] }}</span>
                            @elseif($s['raw_status'] === 'PENDING')
                                <span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-clock-o mr-1"></i>{{ $s['status'] }}</span>
                            @else
                                <span class="badge badge-light text-muted px-2 py-1">{{ $s['status'] }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-secondary small">{{ $s['note'] ?: '-' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
