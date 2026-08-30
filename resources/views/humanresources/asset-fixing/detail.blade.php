<div class="mt-3">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title m-2">Detail Perbaikan Aset</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <input type="hidden" id="ID" name="ID" value="{{ $data->ID ?? '' }}" />

                <!-- Header -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>No Transaksi</label>
                        <input type="text" class="form-control" value="{{ $data->No_Trx ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Jenis Perbaikan</label>
                        <input type="text" class="form-control" value="{{ $data->Jenis_Perbaikan ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Status</label>
                        <input type="text" class="form-control" value="{{ $data->Status ?? '' }}" readonly />
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
                        <input type="date" class="form-control" value="{{ $data->Tanggal_Pengajuan ?? '' }}" readonly />
                    </div>
                </div>

                <!-- Aset -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Nama Aset</label>
                        <input type="text" class="form-control" value="{{ $data->Nama_Aset ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Nomor Seri</label>
                        <input type="text" class="form-control" value="{{ $data->Nomor_Seri ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Deskripsi Aset</label>
                        <input type="text" class="form-control" value="{{ $data->Deskripsi_Aset ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Kondisi Aset</label>
                        <input type="text" class="form-control" value="{{ $data->Kondisi_Aset ?? '' }}" readonly />
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Lokasi Saat Ini</label>
                        <input type="text" class="form-control" value="{{ $data->Lokasi_Saat_Ini ?? '' }}" readonly />
                    </div>
                </div>

                <!-- Detail Perbaikan -->
                <div class="col-md-12 col-12">
                    <div class="form-group">
                        <label>Alasan Perbaikan</label>
                        <textarea class="form-control" rows="3" readonly>{{ $data->Alasan_Perkiraan ?? '' }}</textarea>
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Estimasi Biaya (Rp)</label>
                        <input type="text" class="form-control" value="{{ number_format($data->Estimasi_Biaya ?? 0, 0, ',', '.') }}" readonly />
                        @if(($data->Estimasi_Biaya ?? 0) > 2500000)
                            <span class="badge badge-danger ml-2">Perlu Approval Direktur</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Vendor Yang Digunakan</label>
                        <input type="text" class="form-control" value="{{ $data->Vendor_Yang_Digunakan ?? '' }}" readonly />
                    </div>
                </div>

                <!-- Approval Note -->
                <div class="col-md-12 col-12">
                    <div class="form-group">
                        <label>Approval Note</label>
                        <textarea class="form-control" rows="4" readonly>{{ $data->Approval_Note ?? '' }}</textarea>
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
