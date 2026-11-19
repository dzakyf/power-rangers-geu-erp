<div class="mt-3">
    <div class="card border-danger shadow-sm">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white">
                <i class="fa fa-history me-2"></i>Asset Disposal Log — {{ $ID }}
            </h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#AssetDisposalDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            @if ($logs->isEmpty())
                <div class="alert alert-light border text-muted mb-0">
                    <i class="fa fa-info-circle me-1"></i> Belum ada riwayat log untuk Disposal No: <strong>{{ $ID }}</strong>.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover align-middle mb-0" style="font-size: 0.82rem;">
                        <thead class="table-danger">
                            <tr class="text-nowrap">
                                <th>No</th>
                                <th>Action By</th>
                                <th>Action Date</th>
                                <th>Action Status</th>
                                <th>Disposal Date</th>
                                <th>Status</th>
                                <th>User Name</th>
                                <th>User Position</th>
                                <th>Asset Name</th>
                                <th>Asset Serial No</th>
                                <th>Asset Condition</th>
                                <th>Asset Location</th>
                                <th>Disposal Reason</th>
                                <th>Disposal Actual Date</th>
                                <th>Created By</th>
                                <th>Created Date</th>
                                <th>Updated By</th>
                                <th>Updated Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $log->Action_By }}</td>
                                    <td class="text-nowrap">{{ $log->Action_Date }}</td>
                                    <td><span class="badge bg-secondary">{{ $log->Action_Status }}</span></td>
                                    <td class="text-nowrap">{{ $log->Disposal_Date }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $log->Status }}</span></td>
                                    <td>{{ $log->User_Name }}</td>
                                    <td>{{ $log->User_Position }}</td>
                                    <td>{{ $log->Asset_Name }}</td>
                                    <td>{{ $log->Asset_Serial_No }}</td>
                                    <td>{{ $log->Asset_Condition }}</td>
                                    <td>{{ $log->Asset_Location }}</td>
                                    <td>{{ $log->Disposal_Reason }}</td>
                                    <td class="text-nowrap">{{ $log->Disposal_Actual_Date }}</td>
                                    <td>{{ $log->Created_By }}</td>
                                    <td class="text-nowrap">{{ $log->Created_Date }}</td>
                                    <td>{{ $log->Updated_By }}</td>
                                    <td class="text-nowrap">{{ $log->Updated_Date }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
