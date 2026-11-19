<div class="mt-3">
    <div class="card border-info shadow-sm">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-car me-2"></i>Detail Perawatan Kendaraan</h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#VMDTL').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <input type="hidden" id="ID" name="ID" value="{{ $data->id ?? '' }}" />

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Pengajuan</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">No Transaksi</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->No_Trx ?? '' }}" readonly />
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Jenis Perawatan</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ ucfirst($data->Jenis_Perawatan ?? '') }}" readonly />
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Status</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $data->Status ?? '' }}" readonly />
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Nomor Formulir</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Nomor_Formulir ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Nama Pengaju</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Nama_Pengaju ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Jabatan</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Jabatan ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tanggal Pengajuan</label>
                    <input type="date" class="form-control form-control-sm bg-light" value="{{ $data->Tanggal_Pengajuan ?? '' }}" readonly />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Informasi Kendaraan</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kendaraan (Nama)</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Product_Description ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Asset No</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Asset_No ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Nomor Seri / SN</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->SN ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Plat Nomor</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Plat_Nomor ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kategori Kendaraan</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Kategori_Kendaraan ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kilometer Saat Ini</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ number_format($data->Kilometer_Saat_Ini ?? 0, 0, ',', '.') }}" readonly />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Pekerjaan &amp; Estimasi</h6>
            <div class="row g-3">
                @if(($data->Jenis_Perawatan ?? '') === 'preventif')
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Tipe Service Preventif</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Tipe_Service_Preventif ?? '' }}" readonly />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Jadwal Perawatan (Bulan Ke-)</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Jadwal_Perawatan_Bulan ?? '' }}" readonly />
                    </div>
                @endif

                @if(($data->Jenis_Perawatan ?? '') === 'korektif')
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Kerusakan Yang Ditemui</label>
                        <textarea class="form-control form-control-sm bg-light" rows="3" readonly>{{ $data->Kerusakan_Yang_Ditemui ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Penyebab Diperkirakan</label>
                        <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $data->Penyebab_Diperkirakan ?? '' }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Komponen Yang Diganti / Diperbaiki</label>
                        <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $data->Komponen_Yang_Diganti ?? '' }}</textarea>
                    </div>
                @endif

                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Kondisi Awal Kendaraan</label>
                    <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $data->Kondisi_Awal ?? '' }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Estimasi Biaya (Rp)</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ number_format($data->Estimasi_Biaya ?? 0, 0, ',', '.') }}" readonly />
                        @if(($data->Estimasi_Biaya ?? 0) > 2500000)
                            <span class="badge bg-danger text-nowrap">Perlu Approval Direktur</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Vendor / Bengkel</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Vendor_Bengkel ?? '' }}" readonly />
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Keterangan Lain</label>
                    <textarea class="form-control form-control-sm bg-light" rows="2" readonly>{{ $data->Keterangan_Lain ?? '' }}</textarea>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="button" class="btn btn-secondary btn-sm" onclick="$('#VMDTL').html('')">
                    <i class="fa fa-arrow-left me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
