<div class="mt-3">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title m-2">Detail Permohonan Perawatan Kendaraan</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <input type="hidden" id="ID" name="ID" value="{{ $data->id ?? '' }}" />

                <!-- Header -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>No Transaksi</label>
                        <input type="text" class="form-control" value="{{ $data->No_Trx ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Jenis Perawatan</label>
                        <input type="text" class="form-control"
                            value="{{ ucfirst($data->Jenis_Perawatan ?? '') }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Status Approval Saat Ini</label>
                        <input type="text" class="form-control" value="{{ $data->Status ?? '' }}" readonly />
                        @php
                            $statusLabels = [
                                'APPROVAL_PIC'             => 'Menunggu PIC / Manager Dept',
                                'APPROVAL_ATASAN'          => 'Menunggu Atasan Departemen',
                                'APPROVAL_VP'              => 'Menunggu VP Departemen',
                                'APPROVAL_HCGA_MANAGER'    => 'Menunggu Manager HCGA',
                                'APPROVED'                 => 'Disetujui',
                                'REJECTED'                 => 'Ditolak',
                            ];
                            $label = $statusLabels[$data->Status ?? ''] ?? $data->Status;
                        @endphp
                        <span class="badge
                            @if(in_array($data->Status ?? '', ['APPROVAL_PIC','APPROVAL_ATASAN','APPROVAL_VP','APPROVAL_HCGA_MANAGER']))
                                badge-warning
                            @elseif($data->Status ?? '' === 'APPROVED')
                                badge-success
                            @else
                                badge-danger
                            @endif
                            ml-2">{{ $label }}</span>
                    </div>
                </div>

                <!-- Pengaju -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Nama Pengaju</label>
                        <input type="text" class="form-control" value="{{ $data->Nama_Pengaju ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Jabatan</label>
                        <input type="text" class="form-control" value="{{ $data->Jabatan ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Tanggal Pengajuan</label>
                        <input type="date" class="form-control" value="{{ $data->created_at ?? '' }}" readonly />
                    </div>
                </div>

                <!-- Kendaraan -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Kendaraan (Nama)</label>
                        <input type="text" class="form-control" value="{{ $data->Product_Description ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Asset No</label>
                        <input type="text" class="form-control" value="{{ $data->Asset_No ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Nomor Seri / SN</label>
                        <input type="text" class="form-control" value="{{ $data->SN ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Plat Nomor</label>
                        <input type="text" class="form-control" value="{{ $data->Plat_Nomor ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Kategori Kendaraan</label>
                        <input type="text" class="form-control" value="{{ $data->Kategori_Kendaraan ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Kilometer Saat Ini</label>
                        <input type="text" class="form-control"
                            value="{{ number_format($data->Kilometer_Saat_Ini ?? 0, 0, ',', '.') }}" readonly />
                    </div>
                </div>

                <!-- Preventif Detail -->
                @if(($data->Jenis_Perawatan ?? '') === 'preventif')
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Tipe Service Preventif</label>
                        <input type="text" class="form-control" value="{{ $data->Tipe_Service_Preventif ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Jadwal Perawatan (Bulan Ke-)</label>
                        <input type="text" class="form-control" value="{{ $data->Jadwal_Perawatan_Bulan ?? '' }}" readonly />
                    </div>
                </div>
                @endif

                <!-- Korektif Detail -->
                @if(($data->Jenis_Perawatan ?? '') === 'korektif')
                <div class="col-md-12 col-12">
                    <div class="form-group">
                        <label>Kerusakan Yang Ditemui</label>
                        <textarea class="form-control" rows="3" readonly>{{ $data->Kerusakan_Yang_Ditemui ?? '' }}</textarea>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Penyebab Diperkirakan</label>
                        <textarea class="form-control" rows="2" readonly>{{ $data->Penyebab_Diperkirakan ?? '' }}</textarea>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Komponen Yang Diganti / Diperbaiki</label>
                        <textarea class="form-control" rows="2" readonly>{{ $data->Komponen_Yang_Diganti ?? '' }}</textarea>
                    </div>
                </div>
                @endif

                <!-- Common -->
                <div class="col-md-12 col-12">
                    <div class="form-group">
                        <label>Kondisi Awal Kendaraan</label>
                        <textarea class="form-control" rows="2" readonly>{{ $data->Kondisi_Awal ?? '' }}</textarea>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Keterangan Lain</label>
                        <textarea class="form-control" rows="2" readonly>{{ $data->Keterangan_Lain ?? '' }}</textarea>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Estimasi Biaya (Rp)</label>
                        <input type="text" class="form-control"
                            value="{{ number_format($data->Estimasi_Biaya ?? 0, 0, ',', '.') }}" readonly />
                        @if(($data->Estimasi_Biaya ?? 0) > 2500000)
                            <span class="badge badge-danger ml-2">Perlu Approval Direktur</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Vendor / Bengkel</label>
                        <input type="text" class="form-control" value="{{ $data->Vendor_Bengkel ?? '' }}" readonly />
                    </div>
                </div>

                <!-- Action -->
                <div class="col-md-12 col-12 mt-3">
                    <button class="btn btn-primary" onclick="BackToList()">Kembali</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function BackToList() {
        location.reload();
    }
</script>
