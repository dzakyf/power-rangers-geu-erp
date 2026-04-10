@extends('layout.mainlayout-mitra')
@section('content-wrapper')
@csrf
<!-- Main Content Header -->

<h4>Dashboard Survey</h4>
<div class="row mt-3">

    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Target Kota</h5>
                <div class="row">
                    <div class="col-sm-1">
                        <h3 class="card-text">{{$statistic->Total_All}}</h3>
                    </div>
                    <div class="col-sm">
                        <p class="card-text">Wilayah</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Lokasi Disurvey</h5>
                <div class="row">
                    <div class="col-sm-1">
                        <h3 class="card-text">{{$statistic->Total_All}}</h3>
                    </div>
                    <div class="col-sm">
                        <p class="card-text">Wilayah</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Hub Disetujui</h5>
                <div class="row">
                    <div class="col-sm-2">
                        <h3 class="card-text">{{$statistic->Total_Done_Survey}}</h3>
                    </div>
                    <div class="col-sm">
                        <p class="card-text">Wilayah</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Survey Kritis (>90 Hari)</h5>
                <div class="row">
                    <div class="col-sm-1">
                        <h3 class="card-text">{{$statistic->Total_Expired}}</h3>
                    </div>
                    <div class="col-sm">
                        <p class="card-text">Wilayah</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row mt-3">
    <div class="col-sm-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Usulan Lokasi Kemitraan</h5>
                <div class="row m-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th>Aging (days)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locationCounterWStatus as $index => $entry)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $entry->City  }}</td>
                                    <td>{{ $entry->Status }}</td>
                                    <td>{{ $entry->Days_Since_Created }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pengajuan Lain</h5>
                <div class="row">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Alamat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($newestEntry as $index => $entry)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $entry->Alternative  }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>

</script>
@endsection