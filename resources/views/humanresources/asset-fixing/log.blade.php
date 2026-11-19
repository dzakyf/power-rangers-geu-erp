<div class="mt-3">
    <div class="card border-success shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white">
                <i class="fa fa-history me-2"></i>Asset Fixing Log — Request ID: {{ $ID }}
            </h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#ASSETFIXINGDTL').html('')">
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
                        <thead class="table-success">
                            <tr class="text-nowrap">
                                <th>No</th>
                                <th>Action By</th>
                                <th>Action Date</th>
                                <th>Action Status</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Status</th>
                                <th>Jenis Perbaikan</th>
                                <th>Nama Pengaju</th>
                                <th>Jabatan</th>
                                <th>Nama Aset</th>
                                <th>Nomor Seri</th>
                                <th>Deskripsi Aset</th>
                                <th>Kondisi Aset</th>
                                <th>Lokasi Saat Ini</th>
                                <th>Alasan Perkiraan</th>
                                <th>Estimasi Biaya</th>
                                <th>Vendor</th>
                                <th>Approval User</th>
                                <th>Approval Datetime</th>
                                <th>Department</th>
                                <th>User NIP</th>
                                <th>User Trx</th>
                                <th>Approval Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $log->Action_By }}</td>
                                    <td class="text-nowrap">{{ $log->Action_Date }}</td>
                                    <td><span class="badge bg-secondary">{{ $log->Action_Status }}</span></td>
                                    <td class="text-nowrap">{{ $log->Tanggal_Pengajuan }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $log->Status }}</span></td>
                                    <td>{{ $log->Jenis_Perbaikan }}</td>
                                    <td>{{ $log->Nama_Pengaju }}</td>
                                    <td>{{ $log->Jabatan }}</td>
                                    <td>{{ $log->Nama_Aset }}</td>
                                    <td>{{ $log->Nomor_Seri }}</td>
                                    <td>{{ $log->Deskripsi_Aset }}</td>
                                    <td>{{ $log->Kondisi_Aset }}</td>
                                    <td>{{ $log->Lokasi_Saat_Ini }}</td>
                                    <td>{{ $log->Alasan_Perkiraan }}</td>
                                    <td class="text-end text-nowrap">{{ number_format($log->Estimasi_Biaya ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ $log->Vendor_Yang_Digunakan }}</td>
                                    <td>{{ $log->Approval_User }}</td>
                                    <td class="text-nowrap">{{ $log->Approval_Datetime }}</td>
                                    <td>{{ $log->Department }}</td>
                                    <td>{{ $log->User_NIP }}</td>
                                    <td>{{ $log->User_Trx }}</td>
                                    <td>{{ $log->Approval_Note }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
