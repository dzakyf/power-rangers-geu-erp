<div class="mt-3">
    <div class="card border-info shadow-sm mb-4">
        <div class="card-header bg-info text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0 text-white"><i class="fas fa-file-alt me-2"></i>Detail Peminjaman Mobil — {{ $cr->Car_Rent_No }}</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light btn-sm" onclick="LoadM('CARRENT_DTL', 'hrd-car-rent-edit?Car_Rent_No={{ urlencode($cr->Car_Rent_No) }}')">
                    <i class="fas fa-edit me-1"></i> Edit
                </button>
                <button type="button" class="btn btn-outline-light btn-sm" onclick="$('#CARRENT_DTL').html('')">
                    <i class="fas fa-times me-1"></i> Tutup
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-light fw-bold">
                            <i class="fas fa-user text-primary me-2"></i>Identitas Peminjam
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover align-middle mb-0">
                                    <tr><th style="width:38%">Nomor Induk Karyawan</th><td>{{ $cr->User_NIK }}</td></tr>
                                    <tr><th>Nama Lengkap</th><td>{{ $cr->User_Name }}</td></tr>
                                    <tr><th>Jabatan</th><td>{{ $cr->User_Jabatan }}</td></tr>
                                    <tr><th>Departemen</th><td>{{ $cr->User_Department }}</td></tr>
                                    <tr><th>Site / Office</th><td>{{ $cr->Site_Office }}</td></tr>
                                    <tr><th>Nomor Telepon</th><td>{{ $cr->Phone_Number ?? '-' }}</td></tr>
                                    <tr><th>Email Address</th><td>{{ $cr->Email ?? '-' }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 border">
                        <div class="card-header bg-light fw-bold">
                            <i class="fas fa-id-card text-primary me-2"></i>Informasi SIM &amp; Keperluan
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover align-middle mb-0">
                                    <tr>
                                        <th style="width:38%">Link SIM</th>
                                        <td>
                                            @if($cr->Sim_Link)
                                                <a href="{{ $cr->Sim_Link }}" target="_blank" class="text-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i> Buka Link SIM
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr><th>Keperluan</th><td>{{ $cr->Keperluan }}</td></tr>
                                    <tr>
                                        <th>Tipe Perjalanan</th>
                                        <td><span class="badge {{ ($cr->Trip_Type ?? 'NON_SPPD') == 'SPPD' ? 'bg-info text-dark' : 'bg-secondary' }}">{{ $cr->Trip_Type ?? 'NON_SPPD' }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Nomor SPPD</th>
                                        <td>
                                            @if(!empty($cr->SPPD_ID) && !empty($sppd))
                                                <a href="hrd-sppd-detail?ID={{ $cr->SPPD_ID }}" class="text-primary"><i class="fas fa-external-link-alt me-1"></i> {{ $cr->SPPD_No }}</a>
                                                <small class="text-muted d-block">Status SPPD: {{ $sppd->Status }} | Tujuan: {{ $sppd->Destination }}</small>
                                            @elseif(!empty($cr->SPPD_No))
                                                {{ $cr->SPPD_No }} <small class="text-muted">(arsip)</small>
                                            @else
                                                - <small class="text-muted">(Non-SPPD)</small>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr><th>Tujuan Penggunaan</th><td>{{ $cr->Tujuan_Penggunaan }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border mb-3">
                <div class="card-header bg-light fw-bold">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>Rincian Peminjaman
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0">
                            <tr>
                                <th style="width:25%">Tanggal Peminjaman</th>
                                <td>{{ date('d-m-Y', strtotime($cr->Tanggal_Peminjaman)) }}</td>
                                <th style="width:20%">Jam</th>
                                <td>{{ $cr->Jam_Peminjaman }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Pengembalian</th>
                                <td>{{ date('d-m-Y', strtotime($cr->Tanggal_Pengembalian)) }}</td>
                                <th>Jam</th>
                                <td>{{ $cr->Jam_Pengembalian }}</td>
                            </tr>
                            <tr>
                                <th>Jenis Mobil</th>
                                <td>{{ $cr->Jenis_Mobil }}</td>
                                <th>Nomor Formulir</th>
                                <td><strong>{{ $cr->Car_Rent_No }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border mb-3">
                <div class="card-header bg-light fw-bold">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>Kerusakan &amp; Denda
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0">
                            <tr>
                                <th style="width:25%">Kerusakan / Perbaikan / Denda</th>
                                <td>{{ $cr->Kerusakan_Denda ?? 'Tidak ada' }}</td>
                            </tr>
                            <tr>
                                <th>Bukti Pembayaran Denda</th>
                                <td>
                                    @if($cr->Bukti_Pembayaran)
                                        <a href="{{ $cr->Bukti_Pembayaran }}" target="_blank" class="text-primary">
                                            <i class="fas fa-paperclip me-1"></i> {{ $cr->Bukti_Pembayaran }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border mb-0">
                <div class="card-header bg-light fw-bold">
                    <i class="fas fa-info-circle text-info me-2"></i>Status &amp; Riwayat
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0">
                            <tr>
                                <th style="width:25%">Status</th>
                                <td>
                                    <span class="badge @if($cr->Status == 'CREATED') bg-warning text-dark @elseif($cr->Status == 'APPROVED') bg-success @elseif($cr->Status == 'REJECTED') bg-danger @else bg-secondary @endif">
                                        {{ $cr->Status }}
                                    </span>
                                </td>
                            </tr>
                            <tr><th>Dibuat Oleh</th><td>{{ $cr->Created_By ?? '-' }}</td></tr>
                            <tr><th>Tanggal Dibuat</th><td>{{ $cr->Created_Date ? date('d-m-Y H:i:s', strtotime($cr->Created_Date)) : '-' }}</td></tr>
                            <tr><th>Diupdate Oleh</th><td>{{ $cr->Updated_By ?? '-' }}</td></tr>
                            <tr><th>Tanggal Update</th><td>{{ $cr->Updated_Date ? date('d-m-Y H:i:s', strtotime($cr->Updated_Date)) : '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
