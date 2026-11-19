<div class="mt-3">
    <div class="card border-info shadow-sm">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white">
                <i class="fa fa-history me-2"></i>Asset Moving Log — {{ $ID }}
            </h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#DATAASSETSDTL1').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            @if ($logs->isEmpty())
                <div class="alert alert-light border text-muted mb-0">
                    <i class="fa fa-info-circle me-1"></i> Belum ada riwayat log untuk Request No: <strong>{{ $ID }}</strong>.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped table-hover align-middle mb-0" style="font-size: 0.82rem;">
                        <thead class="table-info">
                            <tr class="text-nowrap">
                                <th>No</th>
                                <th>Action By</th>
                                <th>Action Date</th>
                                <th>Action Status</th>
                                <th>DateTrx</th>
                                <th>UserTrx</th>
                                <th>User NIP</th>
                                <th>Department</th>
                                <th>Asset No</th>
                                <th>Serial No</th>
                                <th>Product Desc</th>
                                <th>Asset Description</th>
                                <th>Last Status</th>
                                <th>Current Location</th>
                                <th>New Location</th>
                                <th>Moving Reason</th>
                                <th>Moving Purpose</th>
                                <th>Moving Date</th>
                                <th>Recipient Name</th>
                                <th>Recipient NIK</th>
                                <th>Recipient Dept</th>
                                <th>Recipient Position</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $log->Action_By ?? '' }}</td>
                                    <td class="text-nowrap">{{ $log->Action_Date ?? '' }}</td>
                                    <td><span class="badge bg-secondary">{{ $log->Action_Status ?? '' }}</span></td>
                                    <td class="text-nowrap">{{ $log->DateTrx }}</td>
                                    <td>{{ $log->UserTrx }}</td>
                                    <td>{{ $log->User_NIP }}</td>
                                    <td>{{ $log->Department }}</td>
                                    <td>{{ $log->Asset_No }}</td>
                                    <td>{{ $log->SN }}</td>
                                    <td>{{ $log->Product_Description }}</td>
                                    <td>{{ $log->Asset_Description }}</td>
                                    <td>{{ $log->Last_Stat }}</td>
                                    <td>{{ $log->Current_Location }}</td>
                                    <td>{{ $log->New_Location }}</td>
                                    <td>{{ $log->Moving_Reason }}</td>
                                    <td>{{ $log->Moving_Purpose }}</td>
                                    <td class="text-nowrap">{{ $log->Moving_Date }}</td>
                                    <td>{{ $log->Receipent_Name }}</td>
                                    <td>{{ $log->Receipent_NIK }}</td>
                                    <td>{{ $log->Receipent_Department }}</td>
                                    <td>{{ $log->Receipent_Position }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $log->Status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
