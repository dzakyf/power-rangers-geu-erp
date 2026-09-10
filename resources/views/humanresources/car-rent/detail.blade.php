<div class="main-content-header">
    <h1>PEMINJAMAN MOBIL OPERASIONAL</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard1">Human Resources</a></li>
        <li class="breadcrumb-item active"><span class="active">Detail Peminjaman — {{ $cr->Car_Rent_No }}</span></li>
    </ol>
</div>

<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white font-weight-bold p-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0" style="color:azure"><i class="fas fa-file-alt mr-2"></i>Detail Formulir Peminjaman Mobil</h5>
        <div>
            <button type="button" class="btn btn-info btn-sm mr-2" onclick="window.location.href='hrd-car-rent-edit?Car_Rent_No={{ $cr->Car_Rent_No }}'">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='hrd-car-rent-index'">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Identitas Peminjam -->
            <div class="col-md-6">
                <div class="card mb-3 border-light bg-light">
                    <div class="card-header bg-warning text-dark font-weight-bold">
                        <i class="fas fa-user mr-2"></i>Identitas Peminjam
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th style="width:35%">Nomor Induk Karyawan</th>
                                <td>{{ $cr->User_NIK }}</td>
                            </tr>
                            <tr>
                                <th>Nama Lengkap</th>
                                <td>{{ $cr->User_Name }}</td>
                            </tr>
                            <tr>
                                <th>Jabatan</th>
                                <td>{{ $cr->User_Jabatan }}</td>
                            </tr>
                            <tr>
                                <th>Departemen</th>
                                <td>{{ $cr->User_Department }}</td>
                            </tr>
                            <tr>
                                <th>Site / Office</th>
                                <td>{{ $cr->Site_Office }}</td>
                            </tr>
                            <tr>
                                <th>Nomor Telepon</th>
                                <td>{{ $cr->Phone_Number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Email Address</th>
                                <td>{{ $cr->Email ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Informasi SIM & Keperluan -->
            <div class="col-md-6">
                <div class="card mb-3 border-light bg-light">
                    <div class="card-header bg-warning text-dark font-weight-bold">
                        <i class="fas fa-id-card mr-2"></i>Informasi SIM & Keperluan
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th style="width:35%">Link SIM</th>
                                <td>
                                    @if($cr->Sim_Link)
                                        <a href="{{ $cr->Sim_Link }}" target="_blank" class="text-primary">
                                            <i class="fas fa-external-link-alt"></i> Buka Link SIM
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Keperluan</th>
                                <td>{{ $cr->Keperluan }}</td>
                            </tr>
                            <tr>
                                <th>Tipe Perjalanan</th>
                                <td><span class="badge {{ ($cr->Trip_Type ?? 'NON_SPPD') == 'SPPD' ? 'bg-info' : 'bg-secondary' }}">{{ $cr->Trip_Type ?? 'NON_SPPD' }}</span></td>
                            </tr>
                            <tr>
                                <th>Nomor SPPD</th>
                                <td>
                                    @if(!empty($cr->SPPD_ID) && !empty($sppd))
                                        <a href="hrd-sppd-detail?ID={{ $cr->SPPD_ID }}" class="text-primary"><i class="fas fa-external-link-alt"></i> {{ $cr->SPPD_No }}</a>
                                        <small class="text-muted d-block">Status SPPD: {{ $sppd->Status }} | Tujuan: {{ $sppd->Destination }}</small>
                                    @elseif(!empty($cr->SPPD_No))
                                        {{ $cr->SPPD_No }} <small class="text-muted">(arsip)</small>
                                    @else
                                        - <small class="text-muted">(Non-SPPD)</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tujuan Penggunaan</th>
                                <td>{{ $cr->Tujuan_Penggunaan }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rincian Peminjaman -->
        <div class="card mb-3 border-light bg-light">
            <div class="card-header bg-warning text-dark font-weight-bold">
                <i class="fas fa-calendar-alt mr-2"></i>Rincian Peminjaman
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <tr>
                        <th style="width:35%">Tanggal Peminjaman</th>
                        <td>{{ date('d-m-Y', strtotime($cr->Tanggal_Peminjaman)) }}</td>
                        <th style="width:15%">Jam</th>
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

        <!-- Kerusakan & Denda -->
        <div class="card mb-3 border-light bg-light">
            <div class="card-header bg-warning text-dark font-weight-bold">
                <i class="fas fa-exclamation-triangle mr-2"></i>Kerusakan & Denda
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <tr>
                        <th style="width:35%">Kerusakan / Perbaikan / Denda</th>
                        <td>{{ $cr->Kerusakan_Denda ?? 'Tidak ada' }}</td>
                    </tr>
                    <tr>
                        <th>Bukti Pembayaran Denda</th>
                        <td>
                            @if($cr->Bukti_Pembayaran)
                                <a href="{{ $cr->Bukti_Pembayaran }}" target="_blank" class="text-primary">
                                    <i class="fas fa-paperclip"></i> {{ $cr->Bukti_Pembayaran }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Status & Tanggal Buat -->
        <div class="card mb-3 border-light bg-light">
            <div class="card-header bg-warning text-dark font-weight-bold">
                <i class="fas fa-info-circle mr-2"></i>Status & Riwayat
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <tr>
                        <th style="width:35%">Status</th>
                        <td>
                            <span class="badge @if($cr->Status == 'CREATED') bg-warning @elseif($cr->Status == 'APPROVED') bg-success @elseif($cr->Status == 'REJECTED') bg-danger @else bg-secondary @endif">
                                {{ $cr->Status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Dibuat Oleh</th>
                        <td>{{ $cr->Created_By ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Dibuat</th>
                        <td>{{ $cr->Created_Date ? date('d-m-Y H:i:s', strtotime($cr->Created_Date)) : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Diupdate Oleh</th>
                        <td>{{ $cr->Updated_By ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Update</th>
                        <td>{{ $cr->Updated_Date ? date('d-m-Y H:i:s', strtotime($cr->Updated_Date)) : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
