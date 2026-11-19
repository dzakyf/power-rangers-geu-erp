<div class="mt-3">
    <div class="card border-info shadow-sm">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-file-alt me-2"></i>Detail Perbaikan Aset</h6>
            <button type="button" class="btn btn-light btn-sm" onclick="$('#ASSETFIXINGDTL').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <input type="hidden" id="ID" name="ID" value="{{ $data->ID ?? '' }}" />

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Informasi Pengajuan</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">No Transaksi</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->No_Trx ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Jenis Perbaikan</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Jenis_Perbaikan ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Status</label>
                    <input type="text" class="form-control form-control-sm bg-light fw-bold" value="{{ $data->Status ?? '' }}" readonly />
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

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Informasi Aset</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nama Aset</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Nama_Aset ?? '' }}" readonly />
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nomor Seri</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Nomor_Seri ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Deskripsi Aset</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Deskripsi_Aset ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Kondisi Aset</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Kondisi_Aset ?? '' }}" readonly />
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Lokasi Saat Ini</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Lokasi_Saat_Ini ?? '' }}" readonly />
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Perbaikan &amp; Approval</h6>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Alasan Perbaikan</label>
                    <textarea class="form-control form-control-sm bg-light" rows="3" readonly>{{ $data->Alasan_Perkiraan ?? '' }}</textarea>
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
                    <label class="form-label small fw-semibold">Vendor Yang Digunakan</label>
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $data->Vendor_Yang_Digunakan ?? '' }}" readonly />
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Approval Note</label>
                    <textarea class="form-control form-control-sm bg-light" rows="3" readonly>{{ $data->Approval_Note ?? '' }}</textarea>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <button type="button" class="btn btn-secondary btn-sm" onclick="$('#ASSETFIXINGDTL').html('')">
                    <i class="fa fa-arrow-left me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
