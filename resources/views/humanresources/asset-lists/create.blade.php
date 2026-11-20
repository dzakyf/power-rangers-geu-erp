<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 text-white fw-bold"><i class="fa fa-plus-circle me-2"></i>Tambah Data Asset Baru</h5>
            <small class="text-white-50">Lengkapi klasifikasi, informasi utama, dan dokumentasi aset di bawah ini.</small>
        </div>
        <button type="button" class="btn btn-light btn-sm fw-semibold" onclick="$('#DATAASSETSDTL').empty();">
            <i class="fa fa-times me-1"></i> Tutup
        </button>
    </div>
    <div class="card-body">
        <form id="FormNewAsset" enctype="multipart/form-data" novalidate>
            <!-- 0. Sumber Pengadaan (Manual / Dari PO) -->
            <div class="card mb-3 border-success">
                <div class="card-header bg-light fw-bold text-success d-flex align-items-center gap-2">
                    <span class="badge bg-success text-white rounded-pill">0</span>
                    <span>Sumber Pengadaan Asset</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sumber Input</label>
                            <select id="Incoming_Type" name="Incoming_Type" class="form-select form-control" onchange="toggleIncomingSource();">
                                <option value="MANUAL" selected>Input Manual</option>
                                <option value="PO">Dari Purchase Order (PO)</option>
                            </select>
                        </div>
                        <div class="col-md-5 po-source-group" style="display:none;">
                            <label class="form-label fw-semibold">Pilih Nomor PO <span class="text-danger">*</span></label>
                            <select id="PO_No" name="PO_No" class="form-select form-control" onchange="loadPoDetails(this.value);">
                                <option value="">-- Pilih PO --</option>
                                @foreach($poList ?? [] as $po)
                                    <option value="{{ $po->PO_No }}">{{ $po->PO_No }} ({{ $po->Purchase_Category ?? 'ASSET' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 po-source-group" style="display:none;">
                            <input type="hidden" id="PO_Item_Select" name="PO_Item_Select" value="" />
                            <input type="hidden" id="Item_Qty_Select" name="Item_Qty_Select" value="1" />
                            <small class="text-muted"><i class="fa fa-info-circle me-1"></i>Item PO akan dimuat otomatis ke tabel di bawah.</small>
                        </div>
                    </div>
                    <div id="PoItemsContainer" class="mt-3 po-source-group" style="display:none;"></div>
                </div>
            </div>

            <!-- 1. Sumber & Klasifikasi -->
            <div class="card mb-3 border">
                <div class="card-header bg-light fw-bold text-secondary d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white rounded-pill">1</span>
                    <span>Klasifikasi Asset</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Asset Category <span class="text-danger">*</span></label>
                            <select id="Asset_Category" name="Asset_Category" class="form-select form-control" onchange="loadSubCategory();" required>
                                <option value="">-- Pilih Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->Product_Group }}">{{ $cat->Product_Group }} - {{ $cat->Initial }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Asset Sub Category <span class="text-danger">*</span></label>
                            <div id="DivSubCategory">
                                <select id="Asset_Sub_Category" name="Asset_Sub_Category" class="form-select form-control" required>
                                    <option value="">-- Pilih Category Dahulu --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Asset Type</label>
                            <div id="DivAssetType">
                                <select id="Asset_Type" name="Asset_Type" class="form-select form-control">
                                    <option value="">-- Pilih Sub Category Dahulu --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Informasi Utama -->
            <div class="card mb-3 border">
                <div class="card-header bg-light fw-bold text-secondary d-flex align-items-center gap-2">
                    <span class="badge bg-primary text-white rounded-pill">2</span>
                    <span>Informasi Utama &amp; Lokasi</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Product Code</label>
                            <input type="text" name="Product_Code" class="form-control" placeholder="Kode produk / barang" />
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Product Description <span class="text-danger">*</span></label>
                            <input type="text" id="New_Product_Description" name="Product_Description" class="form-control" placeholder="Nama / deskripsi lengkap aset" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Brand</label>
                            <input type="text" name="Product_Brand" class="form-control" placeholder="Merk" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit Name</label>
                            <input type="text" name="Unit_Name" class="form-control" placeholder="Unit / Satuan" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="Model" class="form-control" placeholder="Tipe / Model" />
                        </div>
                        <div class="col-md-3 manual-only-field">
                            <label class="form-label">No Seri (SN)</label>
                            <input type="text" id="Manual_SN" name="SN" class="form-control" placeholder="Serial Number" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kondisi Aset</label>
                            <select name="Kondisi_Aset" class="form-select form-control">
                                <option value="BAIK" selected>BAIK</option>
                                <option value="RUSAK">RUSAK</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal / Tahun Perolehan</label>
                            <input type="date" name="Buy_Date" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga Beli (Buy Value)</label>
                            <input type="number" name="Buy_Val" class="form-control" min="0" placeholder="0" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Asset Location</label>
                            <select name="Asset_Location" class="form-select form-control" onchange="fillWhLocation(this.value);">
                                <option value="">-- Pilih Lokasi / Gudang --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->Gudang_ID }}">{{ $wh->Gudang_ID }} - {{ $wh->Description ?: $wh->Location }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <input type="text" name="Asset_Location_Department" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">User Sekarang (PIC)</label>
                            <input type="text" name="User_Responsible" class="form-control" placeholder="Nama pemegang aset" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">User Sebelumnya (Opsional)</label>
                            <input type="text" name="User_Sebelumnya" class="form-control" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" id="New_Asset_Location_Address" name="Asset_Location_Address" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City</label>
                            <input type="text" id="New_Asset_Location_City" name="Asset_Location_City" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Note</label>
                            <input type="text" name="Note" class="form-control" />
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Upload Dokumentasi Aset (Foto / PDF / DOC, maks. 5 MB/file)</label>
                            <input type="file" id="NewAssetDocuments" name="documents[]" class="form-control" multiple accept="image/*,.pdf,.doc,.docx" onchange="previewNewAssetDocuments(this);" />
                            <div id="NewAssetDocumentsPreview" class="row g-2 mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Detail Kendaraan (Muncul jika Sub Category = VEHICLE) -->
            <div id="vehicle-sections-new" class="card mb-3 border-info" style="display:none;">
                <div class="card-header bg-info bg-opacity-10 fw-bold text-primary d-flex align-items-center gap-2">
                    <span class="badge bg-info text-white rounded-pill">3</span>
                    <span>Detail Kendaraan, STNK &amp; KEUR/KIR</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Registration No (Plat)</label>
                            <input type="text" name="Registration_No" class="form-control" placeholder="B 1234 XYZ" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jenis / Tipe Kendaraan</label>
                            <input type="text" name="Product_Type" class="form-control" placeholder="MPV / Truk / Pickup" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No Rangka</label>
                            <input type="text" name="Part_No" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No Mesin</label>
                            <input type="text" id="Vehicle_SN" name="SN" class="form-control" disabled />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No BPKB</label>
                            <input type="text" name="BPKB_No" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">STNK Tahunan</label>
                            <input type="date" name="STNK_Tahunan" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">STNK 5 Tahunan</label>
                            <input type="date" name="STNK_5_Tahun" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jadwal KEUR / KIR</label>
                            <input type="date" name="Jadwal_KIR" class="form-control" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="$('#DATAASSETSDTL').empty();">
                    <i class="fa fa-times me-1"></i> Batal
                </button>
                <button type="button" id="BtnSaveNewAsset" class="btn btn-primary px-4 fw-bold" onclick="submitNewAsset();">
                    <i class="fa fa-save me-1"></i> Simpan Asset Baru
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function notifyCreate(icon, title, text) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: icon, title: title, text: text, confirmButtonText: 'OK' });
    } else {
        alert((title ? title + ': ' : '') + (text || ''));
    }
}

function loadSubCategory() {
    var cat = $('#Asset_Category').val();
    $('#Asset_Category').removeClass('is-invalid');
    if (!cat) {
        $('#DivSubCategory').html("<select id='Asset_Sub_Category' name='Asset_Sub_Category' class='form-select form-control' required><option value=''>-- Pilih Category Dahulu --</option></select>");
        $('#DivAssetType').html("<select id='Asset_Type' name='Asset_Type' class='form-select form-control'><option value=''>-- Pilih Sub Category Dahulu --</option></select>");
        toggleVehicleFieldsNew();
        return;
    }
    $('#DivSubCategory').html("<select class='form-select form-control' disabled><option>Memuat...</option></select>");
    $.get('hrd-asset-lists-sub-category', { Category: cat }, function(html) {
        $('#DivSubCategory').html(html);
        toggleVehicleFieldsNew();
    }).fail(function() {
        notifyCreate('error', 'Gagal', 'Gagal memuat daftar Sub Category.');
    });
}

function loadassettype() {
    var cat = $('#Asset_Category').val();
    var subCat = $('#Asset_Sub_Category').val();
    $('#Asset_Sub_Category').removeClass('is-invalid');
    if (!cat || !subCat) {
        toggleVehicleFieldsNew();
        return;
    }
    $.get('hrd-asset-lists-sub-sub-category', { Category: cat, Sub_Category: subCat }, function(html) {
        $('#DivAssetType').html(html);
    });
    toggleVehicleFieldsNew();
}

function toggleVehicleFieldsNew() {
    var isPo = $('#Incoming_Type').val() === 'PO';
    var val = ($('#Asset_Sub_Category').val() || '').toLowerCase();
    var isVehicle = val.indexOf('vehicle') !== -1 || val.indexOf('kendaraan') !== -1;

    if (isVehicle) {
        $('#vehicle-sections-new').show();
        $('#Vehicle_SN').prop('disabled', isPo);
        $('#Manual_SN').prop('disabled', true);
    } else {
        $('#vehicle-sections-new').hide();
        $('#Vehicle_SN').prop('disabled', true);
        $('#Manual_SN').prop('disabled', isPo);
    }
}

function fillWhLocation(wh) {
    if (!wh) return;
    $.ajax({
        url: 'hrd-asset-lists-new-get-wh',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: { wh: wh },
        success: function(res) {
            if (res && res.data) {
                $('#New_Asset_Location_Address').val(res.data.Address || '');
                $('#New_Asset_Location_City').val(res.data.City || '');
            }
        }
    });
}

function toggleIncomingSource() {
    var src = $('#Incoming_Type').val();
    if (src === 'PO') {
        $('.po-source-group').show();
        $('.manual-only-field').hide();
    } else {
        $('.po-source-group').hide();
        $('.manual-only-field').show();
        $('#PoItemsContainer').empty();
    }
    toggleVehicleFieldsNew();
}

function loadPoDetails(poNo) {
    if (!poNo) {
        $('#PoItemsContainer').empty();
        return;
    }
    $('#PoItemsContainer').html('<div class="text-muted small"><i class="fa fa-spinner fa-spin me-1"></i> Memuat item PO...</div>');
    $.ajax({
        url: 'hrd-asset-lists-new-from-po',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: { pono: poNo },
        success: function(res) {
            if (!res || !res.data || !res.data.Details || !res.data.Details.length) {
                $('#PoItemsContainer').html('<div class="alert alert-warning mb-0">Detail item PO tidak ditemukan.</div>');
                return;
            }
            var details = res.data.Details;
            var first = details[0];
            $('#PO_Item_Select').val(first.Product_Code || '');
            $('input[name="Product_Code"]').val(first.Product_Code || '');
            $('input[name="Product_Description"]').val(first.Product_Desc || '');
            $('input[name="Product_Brand"]').val(first.Product_Brand || '');

            var html = '<div class="table-responsive"><table class="table table-sm table-bordered mb-0"><thead class="table-light"><tr><th>Unit</th><th>Product Code</th><th>Deskripsi</th><th>No Seri (SN)</th><th>Harga</th><th>Kondisi</th></tr></thead><tbody>';
            var rowIdx = 0;
            details.forEach(function(d) {
                var qty = parseInt(d.Qty || 1, 10);
                var unitVal = qty > 0 ? Math.round((parseFloat(d.Total || 0)) / qty) : 0;
                for (var q = 0; q < qty; q++) {
                    html += '<tr>'
                        + '<td>#' + (rowIdx + 1) + '</td>'
                        + '<td><input type="text" name="items[' + rowIdx + '][Product_Code]" class="form-control form-control-sm" value="' + (d.Product_Code || '') + '" readonly /></td>'
                        + '<td><input type="text" name="items[' + rowIdx + '][Product_Description]" class="form-control form-control-sm" value="' + (d.Product_Desc || '') + '" /></td>'
                        + '<td><input type="text" name="items[' + rowIdx + '][SN]" class="form-control form-control-sm" value="' + (d.SN || '') + '" placeholder="No Seri" /></td>'
                        + '<td><input type="number" name="items[' + rowIdx + '][Buy_Val]" class="form-control form-control-sm" value="' + unitVal + '" /></td>'
                        + '<td><select name="items[' + rowIdx + '][Kondisi_Aset]" class="form-select form-select-sm"><option value="BAIK">BAIK</option><option value="RUSAK">RUSAK</option></select></td>'
                        + '</tr>';
                    rowIdx++;
                }
            });
            html += '</tbody></table></div>';
            $('#Item_Qty_Select').val(rowIdx);
            $('#PoItemsContainer').html(html);
        },
        error: function() {
            $('#PoItemsContainer').html('<div class="alert alert-danger mb-0">Gagal memuat detail PO.</div>');
        }
    });
}

function previewNewAssetDocuments(input) {
    var $preview = $('#NewAssetDocumentsPreview');
    $preview.empty();
    if (!input.files || !input.files.length) return;

    Array.from(input.files).forEach(function(file) {
        var sizeKb = Math.round(file.size / 1024);
        var isImage = file.type && file.type.indexOf('image/') === 0;
        if (isImage) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $preview.append(
                    '<div class="col-6 col-md-3 col-lg-2">' +
                    '  <div class="border rounded p-1 text-center h-100">' +
                    '    <img src="' + e.target.result + '" class="img-fluid rounded mb-1" style="height:75px;object-fit:cover;" />' +
                    '    <div class="small text-truncate">' + file.name + '</div>' +
                    '    <div class="text-muted" style="font-size:11px;">' + sizeKb + ' KB</div>' +
                    '  </div>' +
                    '</div>'
                );
            };
            reader.readAsDataURL(file);
        } else {
            $preview.append(
                '<div class="col-6 col-md-3 col-lg-2">' +
                '  <div class="border rounded p-2 text-center h-100 d-flex flex-column justify-content-center">' +
                '    <i class="fa fa-file-alt fa-2x text-secondary mb-1"></i>' +
                '    <div class="small text-truncate">' + file.name + '</div>' +
                '    <div class="text-muted" style="font-size:11px;">' + sizeKb + ' KB</div>' +
                '  </div>' +
                '</div>'
            );
        }
    });
}

function submitNewAsset() {
    $('#FormNewAsset .is-invalid').removeClass('is-invalid');
    var isPo = $('#Incoming_Type').val() === 'PO';
    var cat = $('#Asset_Category').val();
    var subCat = $('#Asset_Sub_Category').val();
    var desc = $.trim($('#New_Product_Description').val());
    var poNo = $('#PO_No').val();

    if (!cat) {
        $('#Asset_Category').addClass('is-invalid').focus();
        notifyCreate('warning', 'Validasi', 'Asset Category wajib dipilih.');
        return;
    }
    if (!subCat) {
        $('#Asset_Sub_Category').addClass('is-invalid').focus();
        notifyCreate('warning', 'Validasi', 'Asset Sub Category wajib dipilih.');
        return;
    }
    if (isPo && !poNo) {
        $('#PO_No').addClass('is-invalid').focus();
        notifyCreate('warning', 'Validasi', 'Nomor PO wajib dipilih.');
        return;
    }
    if (!isPo && !desc) {
        $('#New_Product_Description').addClass('is-invalid').focus();
        notifyCreate('warning', 'Validasi', 'Product Description wajib diisi.');
        return;
    }

    var btn = document.getElementById('BtnSaveNewAsset');
    var origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...';

    var fd = new FormData(document.getElementById('FormNewAsset'));
    $.ajax({
        url: 'hrd-asset-lists-new-submit',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: fd,
        processData: false,
        contentType: false,
        success: function(res) {
            notifyCreate('success', 'Berhasil', res.message || 'Asset berhasil disimpan.');
            $('#DATAASSETSDTL').empty();
            if (typeof DATAASSETSRefresh === 'function') { DATAASSETSRefresh(); }
            btn.disabled = false;
            btn.innerHTML = origHtml;
        },
        error: function(xhr) {
            notifyCreate('error', 'Gagal', (xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan asset baru.');
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    });
}
</script>
