<div class="mt-3">
    <div class="card border-info shadow-sm">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white"><i class="fa fa-file-alt me-2"></i>Detail Form Pemusnahan Aset</h6>
            <button type="button" id="CloseAssetDisposalDetail" name="CloseAssetDisposalDetail" class="btn btn-light btn-sm" onclick="$('#AssetDisposalDiv').html('')">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
        <div class="card-body">
            <div id="ContentPages">
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Identitas Pemohon</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Nomor Formulir</label>
                        <input type="text" id="Disposal_No" class="form-control form-control-sm bg-light" readonly value="{{ $ad->Disposal_No }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tanggal Pengajuan</label>
                        <input type="text" id="Disposal_Date" class="form-control form-control-sm bg-light" readonly value="{{ $ad->Disposal_Date }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Nama Pemohon</label>
                        <input type="text" id="User_Name" class="form-control form-control-sm bg-light" readonly value="{{ $ad->User_Name }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Jabatan</label>
                        <input type="text" id="User_Position" class="form-control form-control-sm bg-light" readonly value="{{ $ad->User_Position }}" />
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Informasi Aset / Equipment</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Aset / Equipment</label>
                        <input type="text" id="Asset_Name" class="form-control form-control-sm bg-light" readonly value="{{ $ad->Asset_Name }}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nomor Seri</label>
                        <input type="text" id="Asset_Serial_No" class="form-control form-control-sm bg-light" readonly value="{{ $ad->Asset_Serial_No }}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kondisi Aset / Equipment</label>
                        <input type="text" id="Asset_Condition" class="form-control form-control-sm bg-light" readonly value="{{ $ad->Asset_Condition }}" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Lokasi Saat Ini</label>
                        <input type="text" id="Asset_Location" class="form-control form-control-sm bg-light" readonly value="{{ $ad->Asset_Location }}" />
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-semibold">Deskripsi Aset / Equipment</label>
                        <textarea id="Asset_Description" class="form-control form-control-sm bg-light" rows="2" readonly>{{ $ad->Asset_Description }}</textarea>
                    </div>
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Detail Pemusnahan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Alasan Pemusnahan</label>
                        <textarea id="Disposal_Reason" class="form-control form-control-sm bg-light" rows="3" readonly>{{ $ad->Disposal_Reason }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tanggal Pemusnahan</label>
                        <input type="text" id="Disposal_Actual_Date" class="form-control form-control-sm bg-light" readonly value="{{ $ad->Disposal_Actual_Date }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <input type="text" id="Status" class="form-control form-control-sm bg-light fw-bold" readonly value="{{ $ad->Status }}" />
                    </div>
                    @if (!empty($ad->Approval_Note))
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Catatan Approval</label>
                            <textarea id="Approval_Note" class="form-control form-control-sm bg-light" rows="2" readonly>{{ $ad->Approval_Note }}</textarea>
                        </div>
                    @endif
                </div>

                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">4. Persetujuan</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Diajukan Oleh / Mengetahui</th>
                                <th>Nama</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>Pemohon</strong></td><td>{{ $ad->Pemohon_Name }}</td><td>{{ $ad->Pemohon_Date }}</td></tr>
                            <tr><td><strong>Kepala Divisi</strong></td><td>{{ $ad->Kadiv_Name }}</td><td>{{ $ad->Kadiv_Date }}</td></tr>
                            <tr><td><strong>Ast. Man. Ops. Support &amp; Controller</strong></td><td>{{ $ad->AstOps_Name }}</td><td>{{ $ad->AstOps_Date }}</td></tr>
                            <tr><td><strong>VP Operation</strong></td><td>{{ $ad->VPO_Name }}</td><td>{{ $ad->VPO_Date }}</td></tr>
                            <tr><td><strong>Manager HCGA</strong></td><td>{{ $ad->HCGA_Name }}</td><td>{{ $ad->HCGA_Date }}</td></tr>
                            <tr><td><strong>Direktur</strong></td><td>{{ $ad->Direktur_Name }}</td><td>{{ $ad->Direktur_Date }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
