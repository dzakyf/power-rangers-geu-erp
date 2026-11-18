@php
    $tglx = function ($v) {
        return ($v === null || $v === '' || $v === '0000-00-00') ? '-' : $v;
    };
    $isVehicle = stripos((string) ($asset->Asset_Sub_Category ?? ''), 'VEHICLE') !== false
        || stripos((string) ($asset->Asset_Sub_Category ?? ''), 'KENDARAAN') !== false
        || !empty($asset->Registration_No);
@endphp

<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 text-white">
            <i class="fa fa-box me-2"></i>Detail Data Asset: {{ $asset->Asset_No }}
            @if(!empty($asset->Registration_No))
                <span class="badge bg-light text-dark ms-2">{{ $asset->Registration_No }}</span>
            @endif
        </h5>
        <div class="d-flex gap-2">
            @if($isVehicle)
                <a href="hrd-vehicle-operations" target="_blank" class="btn btn-warning btn-sm fw-bold text-dark">
                    <i class="fa fa-gas-pump me-1"></i> Logbook &amp; Fuel Tracking
                </a>
            @endif
            <button type="button" class="btn btn-light btn-sm" onclick="$('#DATAASSETSDTL, #VEHICLEDTL').empty();">
                <i class="fa fa-times me-1"></i> Tutup
            </button>
        </div>
    </div>

    <div class="card-body">
        @if($isVehicle)
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">STNK Tahunan</div>
                        <div class="fw-bold">{{ $tglx($asset->STNK_Tahunan ?? null) }}</div>
                    </div>
                    <span class="badge {{ $docStatus['stnk_1']['badge_class'] }}">{{ $docStatus['stnk_1']['label'] }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">STNK 5 Tahunan</div>
                        <div class="fw-bold">{{ $tglx($asset->STNK_5_Tahun ?? null) }}</div>
                    </div>
                    <span class="badge {{ $docStatus['stnk_5']['badge_class'] }}">{{ $docStatus['stnk_5']['label'] }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted">Jadwal KEUR / KIR</div>
                        <div class="fw-bold">{{ $tglx($asset->Jadwal_KIR ?? null) }}</div>
                    </div>
                    <span class="badge {{ $docStatus['keur']['badge_class'] }}">{{ $docStatus['keur']['label'] }}</span>
                </div>
            </div>
        </div>
        @endif

        <div id="DivDetailAsset">
            <input type="hidden" id="ID" />

            <!-- 1. Identitas & Klasifikasi -->
            <div class="card mb-3 border">
                <div class="card-header bg-light fw-bold text-secondary">1. Identitas &amp; Klasifikasi Asset</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Asset No</label>
                            <input type="text" id="Asset_No" class="form-control" readonly />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">PO No</label>
                            <input type="text" id="PO_No" class="form-control" readonly />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Asset Category</label>
                            <input type="text" id="Asset_Category" class="form-control" readonly />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Asset Sub Category</label>
                            <input type="text" id="Asset_Sub_Category" class="form-control" readonly />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Asset Type</label>
                            <input type="text" id="Asset_Type" class="form-control" readonly />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Product Code</label>
                            <input type="text" id="Product_Code" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Product Description</label>
                            <input type="text" id="Product_Description" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Brand</label>
                            <input type="text" id="Product_Brand" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit Name</label>
                            <input type="text" id="Unit_Name" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Model</label>
                            <input type="text" id="Model" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select id="Status" class="form-select form-control">
                                <option value="NEW">NEW</option>
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="MAINTENANCE">MAINTENANCE</option>
                                <option value="DISPOSED">DISPOSED</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Lokasi & Penanggung Jawab -->
            <div class="card mb-3 border">
                <div class="card-header bg-light fw-bold text-secondary">2. Lokasi &amp; Penanggung Jawab</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Asset Location</label>
                            <input type="text" id="Asset_Location" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <input type="text" id="Asset_Location_Department" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City</label>
                            <input type="text" id="Asset_Location_City" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">User Responsible (PIC)</label>
                            <input type="text" id="User_Responsible" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" id="Asset_Location_Address" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">User Sebelumnya</label>
                            <input type="text" id="User_Sebelumnya" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bank Acc</label>
                            <select id="Bank_Acc" class="form-select form-control">
                                <option value="">-- Pilih Bank --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->Acc_No }}">{{ $bank->Acc_No }} - {{ $bank->Bank_Name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Pembelian & Nilai -->
            <div class="card mb-3 border">
                <div class="card-header bg-light fw-bold text-secondary">3. Pembelian, Leasing &amp; Barcode</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Buy Date</label>
                            <input type="text" id="Buy_Date" class="form-control" placeholder="YYYY-MM-DD" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Buy Value</label>
                            <input type="number" id="Buy_Val" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Leasing Vendor</label>
                            <input type="text" id="Leasing_Vendor" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Contract / Leasing No</label>
                            <input type="text" id="Leasing_No" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Barcode Kode</label>
                            <input type="text" id="Barcode_Kode" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Barcode PSAK</label>
                            <input type="text" id="Barcode_Kode_PSAK" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Note</label>
                            <input type="text" id="Note" class="form-control" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Spesifikasi Kendaraan -->
            <div id="vehicle-sections-detail" class="card mb-3 border-info" style="{{ $isVehicle ? '' : 'display:none;' }}">
                <div class="card-header bg-info bg-opacity-10 fw-bold text-primary">4. Detail Kendaraan, STNK &amp; KEUR/KIR</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Registration No (Plat)</label>
                            <input type="text" id="Registration_No" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jenis / Tipe Kendaraan</label>
                            <input type="text" id="Product_Type" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bahan Bakar</label>
                            <input type="text" id="Source_Production" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">CC / Kapasitas</label>
                            <input type="text" id="Capacity" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No Rangka</label>
                            <input type="text" id="Part_No" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No Mesin</label>
                            <input type="text" id="SN" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No BPKB</label>
                            <input type="text" id="BPKB_No" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Asuransi</label>
                            <input type="text" id="Asuransi" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">STNK Tahunan</label>
                            <input type="date" id="STNK_Tahunan" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">STNK 5 Tahunan</label>
                            <input type="date" id="STNK_5_Tahun" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jadwal KEUR / KIR</label>
                            <input type="date" id="Jadwal_KIR" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Berkas KIR Terakhir</label>
                            <input type="text" id="Berkas_KIR" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">KM Saat Ini</label>
                            <input type="number" id="KM_Saat_Ini" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">KM Jadwal PM</label>
                            <input type="number" id="KM_Jadwal_PM" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Interval PM (KM)</label>
                            <input type="number" id="Interval_PM" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subsidi MyPertamina</label>
                            <input type="text" id="Subsid_Mypertamina" class="form-control" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <button type="button" id="SubmitEditAsset" class="btn btn-primary px-4">
                    <i class="fa fa-save me-1"></i> Simpan Perubahan Data Asset
                </button>
            </div>
        </div>

        <!-- 5. Pembaruan Append-Only STNK & KEUR -->
        <div class="card mb-3 border-warning">
            <div class="card-header bg-warning bg-opacity-25 fw-bold text-dark">
                <i class="fa fa-sync-alt me-2"></i>Pembaruan STNK &amp; KEUR/KIR (Tercatat di Riwayat)
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold">Update STNK</h6>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small">Jenis STNK</label>
                                <select id="STNK_Upd_Jenis" class="form-select form-control">
                                    <option value="TAHUNAN">STNK Tahunan</option>
                                    <option value="5_TAHUNAN">STNK 5 Tahunan</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Tgl Berlaku Baru</label>
                                <input type="date" id="STNK_Upd_Tanggal" class="form-control" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Catatan</label>
                                <input type="text" id="STNK_Upd_Catatan" class="form-control" placeholder="ex: Lunas tahunan" />
                            </div>
                        </div>
                        <button type="button" id="BtnUpdSTNK" class="btn btn-warning btn-sm mt-2">
                            <i class="fa fa-save me-1"></i> Update STNK
                        </button>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Update KEUR / KIR</h6>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small">Tanggal KIR</label>
                                <input type="date" id="KIR_Upd_Tanggal" class="form-control" onchange="updateKIRHint();" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Berkas KIR</label>
                                <input type="text" id="KIR_Upd_Berkas" class="form-control" placeholder="No/Nama Berkas" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Catatan</label>
                                <input type="text" id="KIR_Upd_Catatan" class="form-control" placeholder="ex: Lulus uji" />
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">Jadwal KIR berikutnya (+6 bln): <b id="KIR_NextHint">-</b></small>
                            <button type="button" id="BtnUpdKIR" class="btn btn-warning btn-sm">
                                <i class="fa fa-save me-1"></i> Update KEUR/KIR
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Riwayat STNK & KIR -->
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card border h-100">
                    <div class="card-header bg-light fw-bold small">Riwayat STNK (Append-Only)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:200px;overflow-y:auto;">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Jenis</th>
                                        <th>Tgl Berlaku Baru</th>
                                        <th>Tgl Sebelumnya</th>
                                        <th>Catatan</th>
                                        <th>Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stnkLogs as $log)
                                    <tr>
                                        <td>{{ $log->Jenis }}</td>
                                        <td>{{ $tglx($log->Tanggal_Efektif) }}</td>
                                        <td>{{ $tglx($log->Tanggal_Sebelumnya) }}</td>
                                        <td>{{ $log->Catatan }}</td>
                                        <td>{{ $log->Updated_By }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-2">Belum ada riwayat STNK</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border h-100">
                    <div class="card-header bg-light fw-bold small">Riwayat KEUR / KIR (Append-Only)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:200px;overflow-y:auto;">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Ke</th>
                                        <th>Tgl KIR</th>
                                        <th>Jadwal Berikutnya</th>
                                        <th>Berkas</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kirLogs as $log)
                                    <tr>
                                        <td>{{ $log->Ke }}</td>
                                        <td>{{ $tglx($log->Tanggal_KIR) }}</td>
                                        <td>{{ $tglx($log->Jadwal_KIR_Berikutnya) }}</td>
                                        <td>{{ $log->Berkas }}</td>
                                        <td>{{ $log->Catatan }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center text-muted py-2">Belum ada riwayat KEUR/KIR</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7. Foto Asset -->
        <div class="card border">
            <div class="card-header bg-light fw-bold small d-flex justify-content-between align-items-center">
                <span>Foto Asset</span>
                <div class="d-flex gap-2 align-items-center">
                    <input type="file" id="AssetPhotoInput" class="form-control form-control-sm" multiple accept="image/*" style="width:auto;" />
                    <button type="button" class="btn btn-sm btn-primary" onclick="uploadAssetPhotos();">Upload</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @forelse($photos as $photo)
                    <div class="col-6 col-md-3 col-lg-2 text-center">
                        <div class="border rounded p-1">
                            <img src="{{ asset('filevendor/' . $photo->Path) }}" class="img-fluid rounded mb-1" style="height:100px;object-fit:cover;" alt="Asset Photo" />
                            <button type="button" class="btn btn-xs btn-outline-danger w-100" onclick="deleteAssetPhoto({{ $photo->ID }});">Hapus</button>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-muted small">Belum ada foto aset.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var assetData = @json($asset);
    Object.keys(assetData).forEach(function(key) {
        var el = document.getElementById(key);
        if (el) {
            var val = assetData[key];
            if (val === '0000-00-00' || val === null) { val = ''; }
            el.value = val;
        }
    });
})();

function reloadAssetDetail() {
    var assetNo = $('#Asset_No').val();
    var targetId = document.getElementById('VEHICLEDTL') ? 'VEHICLEDTL' : 'DATAASSETSDTL';
    LoadM(targetId, 'hrd-asset-lists-detail?Task_ID=' + encodeURIComponent(assetNo));
    if (typeof DATAASSETSRefresh === 'function') { DATAASSETSRefresh(); }
    if (typeof VEHICLERefresh === 'function') { VEHICLERefresh(); }
}

function updateKIRHint() {
    var tgl = $('#KIR_Upd_Tanggal').val();
    if (!tgl) { $('#KIR_NextHint').text('-'); return; }
    var d = new Date(tgl);
    if (isNaN(d.getTime())) { $('#KIR_NextHint').text('-'); return; }
    d.setMonth(d.getMonth() + 6);
    var mm = ('0' + (d.getMonth() + 1)).slice(-2);
    var dd = ('0' + d.getDate()).slice(-2);
    $('#KIR_NextHint').text(d.getFullYear() + '-' + mm + '-' + dd);
}

$('#SubmitEditAsset').on('click', function() {
    var payload = {};
    $('#DivDetailAsset input, #DivDetailAsset select, #DivDetailAsset textarea').each(function() {
        var id = $(this).attr('id');
        if (id) {
            payload[id] = $(this).val();
        }
    });
    $.ajax({
        url: 'hrd-asset-lists-detail-submit',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: payload,
        success: function(res) {
            alert(res.message || 'Data Asset berhasil disimpan.');
            reloadAssetDetail();
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan data asset.');
        }
    });
});

$('#BtnUpdSTNK').on('click', function() {
    var tgl = $('#STNK_Upd_Tanggal').val();
    if (!tgl) { alert('Tanggal berlaku STNK wajib diisi!'); return; }
    $.ajax({
        url: 'hrd-asset-lists-stnk-update',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: {
            Asset_No: $('#Asset_No').val(),
            Jenis: $('#STNK_Upd_Jenis').val(),
            Tanggal_Berlaku: tgl,
            Catatan: $('#STNK_Upd_Catatan').val()
        },
        success: function(res) {
            alert(res.message || 'STNK diperbarui!');
            reloadAssetDetail();
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memperbarui STNK.');
        }
    });
});

$('#BtnUpdKIR').on('click', function() {
    var tgl = $('#KIR_Upd_Tanggal').val();
    if (!tgl) { alert('Tanggal KIR wajib diisi!'); return; }
    $.ajax({
        url: 'hrd-asset-lists-kir-update',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: {
            Asset_No: $('#Asset_No').val(),
            Tanggal_KIR: tgl,
            Berkas_KIR: $('#KIR_Upd_Berkas').val(),
            Catatan: $('#KIR_Upd_Catatan').val()
        },
        success: function(res) {
            alert(res.message || 'KEUR/KIR diperbarui!');
            reloadAssetDetail();
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal memperbarui KEUR/KIR.');
        }
    });
});

function uploadAssetPhotos() {
    var input = document.getElementById('AssetPhotoInput');
    if (!input.files || !input.files.length) {
        alert('Pilih file foto terlebih dahulu.');
        return;
    }
    var fd = new FormData();
    fd.append('Asset_No', $('#Asset_No').val());
    for (var i = 0; i < input.files.length; i++) {
        fd.append('photos[]', input.files[i]);
    }
    $.ajax({
        url: 'hrd-asset-lists-photo-upload',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: fd,
        processData: false,
        contentType: false,
        success: function(res) {
            alert(res.message || 'Foto diunggah.');
            reloadAssetDetail();
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal mengunggah foto.');
        }
    });
}

function deleteAssetPhoto(photoId) {
    if (!confirm('Hapus foto ini?')) return;
    $.ajax({
        url: 'hrd-asset-lists-photo-delete',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: { Photo_ID: photoId, Asset_No: $('#Asset_No').val() },
        success: function(res) {
            alert(res.message || 'Foto dihapus.');
            reloadAssetDetail();
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menghapus foto.');
        }
    });
}
</script>
