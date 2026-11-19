<div class="mt-3">
    <div class="card border-success shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white">
                <i class="fa fa-history me-2"></i>Asset Handover Log — {{ $ID }}
            </h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#AssetHandoverDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            @if ($logs->isEmpty())
                <div class="alert alert-light border text-muted mb-0">
                    <i class="fa fa-info-circle me-1"></i> Belum ada riwayat log untuk Handover No: <strong>{{ $ID }}</strong>.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover align-middle mb-0" style="font-size: 0.82rem;">
                        <thead class="table-success">
                            <tr class="text-nowrap">
                                <th>No</th>
                                <th>Action By</th>
                                <th>Action Date</th>
                                <th>Action Status</th>
                                <th>Handover Date</th>
                                <th>Status</th>
                                <th>User Name</th>
                                <th>User NIK</th>
                                <th>PIC Name</th>
                                <th>PIC Department</th>
                                <th>PIC Phone</th>
                                <th>Reference</th>
                                <th>Remark</th>
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
                                    <td class="text-nowrap">{{ $log->Handover_Date }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $log->Status }}</span></td>
                                    <td>{{ $log->User_Name }}</td>
                                    <td>{{ $log->User_NIK }}</td>
                                    <td>{{ $log->PIC_Name }}</td>
                                    <td>{{ $log->PIC_Department }}</td>
                                    <td>{{ $log->PIC_Phone }}</td>
                                    <td>{{ $log->Reference }}</td>
                                    <td>{{ $log->Remark }}</td>
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
