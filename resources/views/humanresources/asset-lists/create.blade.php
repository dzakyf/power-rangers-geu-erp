<div class="card border-primary shadow-sm mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white"><i class="fa fa-plus-circle me-2"></i>Tambah Data Asset Baru</h5>
        <button type="button" class="btn btn-light btn-sm" onclick="$('#DATAASSETSDTL').empty();">
            <i class="fa fa-times me-1"></i> Tutup
        </button>
    </div>
    <div class="card-body">
        <form id="FormNewAsset">
            <!-- 1. Sumber & Klasifikasi -->
            <div class="card mb-3 border">
                <div class="card-header bg-light fw-bold text-secondary">1. Klasifikasi Asset</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Asset Category <span class="text-danger">*</span></label>
                            <select id="Asset_Category" name="Asset_Category" class="form-select form-control" onchange="loadSubCategory();">
                                <option value="">-- Pilih Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->Product_Group }}">{{ $cat->Product_Group }} - {{ $cat->Initial }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Asset Sub Category <span class="text-danger">*</span></label>
                            <div id="DivSubCategory">
                                <select id="Asset_Sub_Category" name="Asset_Sub_Category" class="form-select form-control">
                                    <option value="">-- Pilih Category Dahulu --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Asset Type</label>
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
                <div class="card-header bg-light fw-bold text-secondary">2. Informasi Utama &amp; Lokasi</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Product Code</label>
                            <input type="text" name="Product_Code" class="form-control" />
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Product Description <span class="text-danger">*</span></label>
                            <input type="text" name="Product_Description" class="form-control" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Brand</label>
                            <input type="text" name="Product_Brand" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit Name</label>
                            <input type="text" name="Unit_Name" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Model</label>
                            <input type="text" name="Model" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Buy Date</label>
                            <input type="date" name="Buy_Date" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Buy Value</label>
                            <input type="number" name="Buy_Val" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Asset Location</label>
                            <select name="Asset_Location" class="form-select form-control" onchange="fillWhLocation(this.value);">
                                <option value="">-- Pilih Lokasi / Gudang --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->Gudang_ID }}">{{ $wh->Gudang_ID }} - {{ $wh->Gudang_Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <input type="text" name="Asset_Location_Department" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">User Responsible (PIC)</label>
                            <input type="text" name="User_Responsible" class="form-control" />
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
                    </div>
                </div>
            </div>

            <!-- 3. Detail Kendaraan (Muncul jika Sub Category = VEHICLE) -->
            <div id="vehicle-sections-new" class="card mb-3 border-info" style="display:none;">
                <div class="card-header bg-info bg-opacity-10 fw-bold text-primary">3. Detail Kendaraan, STNK &amp; KEUR/KIR</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Registration No (Plat)</label>
                            <input type="text" name="Registration_No" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jenis / Tipe Kendaraan</label>
                            <input type="text" name="Product_Type" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No Rangka</label>
                            <input type="text" name="Part_No" class="form-control" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">No Mesin</label>
                            <input type="text" name="SN" class="form-control" />
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
                <button type="button" class="btn btn-secondary" onclick="$('#DATAASSETSDTL').empty();">Batal</button>
                <button type="button" id="BtnSaveNewAsset" class="btn btn-primary px-4" onclick="submitNewAsset();">
                    <i class="fa fa-save me-1"></i> Simpan Asset Baru
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function loadSubCategory() {
    var cat = $('#Asset_Category').val();
    if (!cat) return;
    $.get('hrd-asset-lists-sub-category', { Category: cat }, function(html) {
        $('#DivSubCategory').html(html);
    });
}

function loadassettype() {
    var cat = $('#Asset_Category').val();
    var subCat = $('#Asset_Sub_Category').val();
    if (!cat || !subCat) return;
    $.get('hrd-asset-lists-sub-sub-category', { Category: cat, Sub_Category: subCat }, function(html) {
        $('#DivAssetType').html(html);
    });
    toggleVehicleFieldsNew();
}

function toggleVehicleFieldsNew() {
    var val = ($('#Asset_Sub_Category').val() || '').toLowerCase();
    if (val.indexOf('vehicle') !== -1 || val.indexOf('kendaraan') !== -1) {
        $('#vehicle-sections-new').show();
    } else {
        $('#vehicle-sections-new').hide();
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

function submitNewAsset() {
    var btn = document.getElementById('BtnSaveNewAsset');
    btn.disabled = true;
    $.ajax({
        url: 'hrd-asset-lists-new-submit',
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() || '{{ csrf_token() }}' },
        data: $('#FormNewAsset').serialize(),
        success: function(res) {
            alert(res.message || 'Asset berhasil disimpan.');
            $('#DATAASSETSDTL').empty();
            if (typeof DATAASSETSRefresh === 'function') { DATAASSETSRefresh(); }
            btn.disabled = false;
        },
        error: function(xhr) {
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Gagal menyimpan asset baru.');
            btn.disabled = false;
        }
    });
}
</script>
