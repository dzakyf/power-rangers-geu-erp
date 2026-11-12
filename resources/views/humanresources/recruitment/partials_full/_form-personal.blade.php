<div class="personal-information">
    <div class="section-title">
        <i class="fas fa-id-card"></i> Personal Information
    </div>

    <div class="info-box">
        <i class="fas fa-info-circle"></i>
        <strong>Information:</strong> Please fill in all required fields marked with <span class="text-danger">*</span>
    </div>

    <div class="row">
        {{-- Position dari master_position (dengan auto-link ke Requisition APPROVED) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Position">
                    Posisi Jabatan Dilamar <span class="required">*</span>
                </label>
                <select id="Position" name="Position" class="form-control select2" required onchange="handlePositionSelection(this)">
                    <option value="">-- Pilih Posisi (Master Position) --</option>
                    @php
                        $location = optional(Auth::user())->Location ?? ($publicLocation ?? 'GEU-HO');

                        // 1. Ambil seluruh posisi aktif dari master_position
                        $masterPositions = DB::table('master_position')
                            ->where('Status', 'ACTIVE')
                            ->orderBy('Position', 'ASC')
                            ->get();

                        // 2. Ambil requisition PTK APPROVED yang masih berlaku
                        $activeRequisitions = DB::table('requisition')
                            ->where('Status', 'APPROVED')
                            ->where(function($q) {
                                $q->whereNull('End_Date')
                                  ->orWhere('End_Date', '>=', date('Y-m-d'));
                            })
                            ->get()
                            ->keyBy('Job_Position');

                        foreach ($masterPositions as $pos) {
                            $hasActiveReq = isset($activeRequisitions[$pos->Position]);
                            $reqInfo = $hasActiveReq ? $activeRequisitions[$pos->Position] : null;
                            $reqTaskId = $reqInfo ? $reqInfo->Task_ID : '';
                            $badge = $hasActiveReq ? ' [✓ Lowongan Aktif PTK]' : '';
                            $dept = $pos->Department ? " ({$pos->Department})" : '';

                            echo "<option value='{$pos->Position}' data-req-id='{$reqTaskId}' data-has-req='" . ($hasActiveReq ? '1' : '0') . "'>{$pos->Position}{$dept}{$badge}</option>";
                        }
                    @endphp
                </select>
                <input type="hidden" id="Requisition_ID" name="Requisition_ID" value="">
                
                <div id="requisitionNotice" class="mt-2 small" style="display:none;">
                    <div class="alert alert-success py-1 px-2 mb-0 d-flex align-items-center">
                        <i class="fas fa-link me-2 text-success"></i>
                        <span>Otomatis terhubung ke Permintaan Tenaga Kerja (PTK): <strong id="ptkTaskNotice"></strong></span>
                    </div>
                </div>
                <div id="noRequisitionNotice" class="mt-2 small" style="display:none;">
                    <div class="alert alert-light border py-1 px-2 mb-0 text-muted d-flex align-items-center">
                        <i class="fas fa-info-circle me-2 text-info"></i>
                        <span>Posisi tersimpan ke <strong>Talent Pool</strong> untuk kebutuhan rekrutmen mendatang.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recruitment Type --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Recruitment_Type">
                    Recruitment Type <span class="required">*</span>
                </label>
                <select id="Recruitment_Type" name="Recruitment_Type" class="form-control" required>
                    <option value="INTERNAL">Internal</option>
                    <option value="EXTERNAL">External</option>
                </select>
                <small class="form-text text-muted">
                    Pilih jalur penerimaan pelamar (Internal atau External)
                </small>
            </div>
        </div>
    </div>

    <script>
    function handlePositionSelection(selectElem) {
        var selectedOption = $(selectElem).find('option:selected');
        var reqId = selectedOption.data('req-id');
        var hasReq = selectedOption.data('has-req');
        var posName = selectedOption.val();

        if (!posName) {
            $('#Requisition_ID').val('');
            $('#requisitionNotice').hide();
            $('#noRequisitionNotice').hide();
            return;
        }

        if (hasReq == '1' && reqId) {
            $('#Requisition_ID').val(reqId);
            $('#ptkTaskNotice').text(reqId);
            $('#requisitionNotice').slideDown();
            $('#noRequisitionNotice').hide();
        } else {
            $('#Requisition_ID').val('');
            $('#requisitionNotice').hide();
            $('#noRequisitionNotice').slideDown();
        }
    }
    </script>

    <hr class="my-4">

    <div class="row">
        {{-- Full Name --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Name">
                    Full Name <span class="required">*</span>
                </label>
                <input type="text" id="Name" name="Name" class="form-control" 
                       placeholder="Full name as per ID card" required maxlength="150">
                <div class="invalid-feedback">Please enter full name (min 3 characters)</div>
            </div>
        </div>

        {{-- Gender --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Gender">
                    Gender <span class="required">*</span>
                </label>
                <select id="Gender" name="Gender" class="form-control" required>
                    <option value="">-- Select Gender --</option>
                    <option value="MALE">Male</option>
                    <option value="FEMALE">Female</option>
                </select>
                <div class="invalid-feedback">Please select gender</div>
            </div>
        </div>

        {{-- Birth Place --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Birth_Place">
                    Birth Place <span class="required">*</span>
                </label>
                <input type="text" id="Birth_Place" name="Birth_Place" class="form-control" 
                       placeholder="e.g. Jakarta" required maxlength="150">
                <div class="invalid-feedback">Please enter birth place</div>
            </div>
        </div>

        {{-- Birth Date --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Birth_Date">
                    Birth Date <span class="required">*</span>
                </label>
                <input type="text" id="Birth_Date" name="Birth_Date" class="form-control datepicker" 
                       placeholder="DD-MM-YYYY" required readonly>
                <div class="invalid-feedback">Please select birth date</div>
            </div>
        </div>

        {{-- ID Card Number (KTP) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="KTP">
                    ID Card Number (KTP) <span class="required">*</span>
                </label>
                <input type="text" id="KTP" name="KTP" class="form-control" 
                       placeholder="16 digit ID number" required maxlength="20" 
                       pattern="[0-9]{16,20}">
                <div class="invalid-feedback">Please enter valid 16-digit ID number</div>
            </div>
        </div>

        {{-- Citizenship --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="kewarganegaraan">
                    Citizenship <span class="required">*</span>
                </label>
                <select id="kewarganegaraan" name="kewarganegaraan" class="form-control" required>
                    <option value="">-- Select Citizenship --</option>
                    <option value="WNI" selected>Indonesian (WNI)</option>
                    <option value="WNA">Foreign National (WNA)</option>
                </select>
                <div class="invalid-feedback">Please select citizenship</div>
            </div>
        </div>

        {{-- Religion --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Religion">
                    Religion <span class="required">*</span>
                </label>
                <select id="Religion" name="Religion" class="form-control" required>
                    <option value="">-- Select Religion --</option>
                    <option value="ISLAM">Islam</option>
                    <option value="KRISTEN PROTESTAN">Christian (Protestant)</option>
                    <option value="KATOLIK">Catholic</option>
                    <option value="HINDU">Hindu</option>
                    <option value="BUDHA">Buddhist</option>
                    <option value="KONGHUCU">Confucian</option>
                </select>
                <div class="invalid-feedback">Please select religion</div>
            </div>
        </div>

        {{-- Marital Status --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Marital_Status">
                    Marital Status <span class="required">*</span>
                </label>
                <select id="Marital_Status" name="Marital_Status" class="form-control" required>
                    <option value="">-- Select Marital Status --</option>
                    <option value="SINGLE">Single</option>
                    <option value="MARRIED">Married</option>
                    <option value="DIVORCED">Divorced</option>
                    <option value="WIDOWED">Widowed</option>
                </select>
                <div class="invalid-feedback">Please select marital status</div>
            </div>
        </div>

        {{-- Blood Type (Optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Blood">Blood Type</label>
                <select id="Blood" name="Blood" class="form-control">
                    <option value="">-- Select Blood Type --</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="AB">AB</option>
                    <option value="O">O</option>
                </select>
            </div>
        </div>

        {{-- Rhesus (Optional) --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Blood_Rhesus">Rhesus</label>
                <select id="Blood_Rhesus" name="Blood_Rhesus" class="form-control">
                    <option value="">-- Select Rhesus --</option>
                    <option value="+">Positive (+)</option>
                    <option value="-">Negative (-)</option>
                </select>
            </div>
        </div>

        {{-- Full Address --}}
        <div class="col-md-12">
            <div class="form-group">
                <label for="Address">
                    Alamat Lengkap (KTP) <span class="required">*</span>
                </label>
                <textarea id="Address" name="Address" class="form-control" rows="2" 
                          placeholder="Nama jalan, nomor rumah, RT/RW, lingkungan" 
                          required maxlength="250"></textarea>
                <div class="invalid-feedback">Harap isi alamat lengkap</div>
            </div>
        </div>

        {{-- Pencarian Cepat Kode Pos / Kelurahan / Kecamatan / Kota --}}
        <div class="col-md-12">
            <div class="form-group mb-2">
                <label class="form-label small fw-bold text-primary">
                    <i class="fas fa-search-location me-1"></i> Pencarian Cepat Wilayah (Master Kode Pos)
                </label>
                <div class="input-group">
                    <input type="text" id="kodepos_search_input" class="form-control" placeholder="Ketik Kode Pos, Kelurahan, Kecamatan, atau Kota... (Contoh: 10110 atau Gambir)">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="searchKodeposAuto()">
                            <i class="fas fa-search me-1"></i> Cari
                        </button>
                    </div>
                </div>
                <div id="kodepos_dropdown_results" class="list-group shadow position-absolute w-100 mt-1" style="z-index: 1050; max-height: 220px; overflow-y: auto; display: none;"></div>
                <small class="form-text text-muted">Ketik minimal 3 karakter untuk memilih dari database master kode pos Indonesia.</small>
            </div>
        </div>

        {{-- Provinsi --}}
        <div class="col-md-4">
            <div class="form-group">
                <label for="Province">Provinsi <span class="required">*</span></label>
                <select id="Province" name="Province" class="form-control" required>
                    <option value="">-- Pilih Provinsi --</option>
                </select>
                <div class="invalid-feedback">Harap pilih provinsi</div>
            </div>
        </div>

        {{-- Kota / Kabupaten --}}
        <div class="col-md-4">
            <div class="form-group">
                <label for="City">Kota / Kabupaten <span class="required">*</span></label>
                <select id="City" name="City" class="form-control" required disabled>
                    <option value="">-- Pilih Provinsi Dahulu --</option>
                </select>
                <div class="invalid-feedback">Harap pilih kota/kabupaten</div>
            </div>
        </div>

        {{-- Kecamatan --}}
        <div class="col-md-4">
            <div class="form-group">
                <label for="District">Kecamatan <span class="required">*</span></label>
                <select id="District" name="District" class="form-control" required disabled>
                    <option value="">-- Pilih Kota Dahulu --</option>
                </select>
                <div class="invalid-feedback">Harap pilih kecamatan</div>
            </div>
        </div>

        {{-- Kelurahan --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="Sub_District">Kelurahan / Desa <span class="required">*</span></label>
                <select id="Sub_District" name="Sub_District" class="form-control" required disabled>
                    <option value="">-- Pilih Kecamatan Dahulu --</option>
                </select>
                <div class="invalid-feedback">Harap pilih kelurahan/desa</div>
            </div>
        </div>

        {{-- Kode Pos --}}
        <div class="col-md-6">
            <div class="form-group">
                <label for="ZipCode">Kode Pos <span class="required">*</span></label>
                <div class="input-group">
                    <input type="text" id="ZipCode" name="ZipCode" class="form-control" placeholder="5 digit (Contoh: 10640)" required maxlength="10">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary" id="btnLookupZipcode" title="Cari Wilayah berdasarkan Kode Pos">
                            <i class="fas fa-magic text-primary"></i> <span class="d-none d-sm-inline">Terapkan</span>
                        </button>
                    </div>
                </div>
                <small class="form-text text-muted">Bisa ketik 5 digit kode pos lalu klik tombol sihir untuk auto-isi wilayah di atas.</small>
                <div class="invalid-feedback">Harap isi kode pos</div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Dialog Pilihan Wilayah jika 1 Kode Pos memiliki lebih dari 1 lokasi --}}
<div class="modal fade" id="modalSelectZipLocation" tabindex="-1" role="dialog" aria-labelledby="modalSelectZipLocationLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title font-weight-bold" id="modalSelectZipLocationLabel">
                    <i class="fas fa-map-marker-alt mr-2"></i>Pilih Wilayah untuk Kode Pos <span id="modalTargetZipcode" class="badge badge-light text-primary"></span>
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <p class="small text-muted mb-3">Kode pos ini terdaftar pada beberapa kelurahan/kecamatan. Silakan pilih wilayah yang tepat:</p>
                <div id="zipLocationOptionsList" class="list-group">
                    {{-- Dinamis diisi pilihan --}}
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
// State flag untuk mencegah trigger cascade rekursif saat reverse lookup dari ZipCode
var isApplyingZipcode = false;

$(document).ready(function() {
    loadProvincesList();

    // Event: Ganti Provinsi
    $('#Province').on('change', function() {
        var prov = $(this).val();
        resetAndClearDropdown('#City', '-- Pilih Kota / Kabupaten --');
        resetAndClearDropdown('#District', '-- Pilih Kota Dahulu --');
        resetAndClearDropdown('#Sub_District', '-- Pilih Kecamatan Dahulu --');
        if (!isApplyingZipcode) {
            $('#ZipCode').val('');
        }

        if (prov) {
            $('#City').prop('disabled', false);
            loadCitiesList(prov);
        }
    });

    // Event: Ganti Kota / Kabupaten
    $('#City').on('change', function() {
        var prov = $('#Province').val();
        var city = $(this).val();
        resetAndClearDropdown('#District', '-- Pilih Kecamatan --');
        resetAndClearDropdown('#Sub_District', '-- Pilih Kecamatan Dahulu --');
        if (!isApplyingZipcode) {
            $('#ZipCode').val('');
        }

        if (prov && city) {
            $('#District').prop('disabled', false);
            loadDistrictsList(prov, city);
        }
    });

    // Event: Ganti Kecamatan
    $('#District').on('change', function() {
        var prov = $('#Province').val();
        var city = $('#City').val();
        var district = $(this).val();
        resetAndClearDropdown('#Sub_District', '-- Pilih Kelurahan / Desa --');
        if (!isApplyingZipcode) {
            $('#ZipCode').val('');
        }

        if (prov && city && district) {
            $('#Sub_District').prop('disabled', false);
            loadSubDistrictsList(prov, city, district);
        }
    });

    // Event: Ganti Kelurahan / Desa -> Otomatis Set Kode Pos
    $('#Sub_District').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var zip = selectedOption.data('zipcode');
        if (zip && !isApplyingZipcode) {
            $('#ZipCode').val(zip).removeClass('is-invalid');
        }
    });

    // Event: Tombol / Ketik Kode Pos (Reverse Lookup)
    $('#btnLookupZipcode').on('click', function() {
        applyReverseZipcode();
    });

    $('#ZipCode').on('keyup', function(e) {
        var val = $(this).val().trim();
        if (val.length === 5 && e.key !== 'Tab' && e.key !== 'Shift') {
            applyReverseZipcode();
        }
    });
});

function resetAndClearDropdown(selector, placeholder) {
    $(selector).empty().append('<option value="">' + placeholder + '</option>').prop('disabled', true);
}

// 1. Muat daftar Provinsi
function loadProvincesList(callback) {
    $.get('{{ url("api/master-kodepos/provinces") }}', function(res) {
        if (res.status === 'success' && res.data) {
            var current = $('#Province').val();
            var options = '<option value="">-- Pilih Provinsi --</option>';
            res.data.forEach(function(prov) {
                options += '<option value="' + prov + '">' + prov + '</option>';
            });
            $('#Province').html(options);
            if (current) {
                $('#Province').val(current);
            }
            if (typeof callback === 'function') callback();
        }
    });
}

// 2. Muat daftar Kota / Kabupaten
function loadCitiesList(prov, selectedCity, callback) {
    $.get('{{ url("api/master-kodepos/cities") }}', { province: prov }, function(res) {
        if (res.status === 'success' && res.data) {
            var options = '<option value="">-- Pilih Kota / Kabupaten --</option>';
            res.data.forEach(function(c) {
                options += '<option value="' + c + '">' + c + '</option>';
            });
            $('#City').html(options).prop('disabled', false);
            if (selectedCity) {
                $('#City').val(selectedCity);
            }
            if (typeof callback === 'function') callback();
        }
    });
}

// 3. Muat daftar Kecamatan
function loadDistrictsList(prov, city, selectedDistrict, callback) {
    $.get('{{ url("api/master-kodepos/districts") }}', { province: prov, city: city }, function(res) {
        if (res.status === 'success' && res.data) {
            var options = '<option value="">-- Pilih Kecamatan --</option>';
            res.data.forEach(function(d) {
                options += '<option value="' + d + '">' + d + '</option>';
            });
            $('#District').html(options).prop('disabled', false);
            if (selectedDistrict) {
                $('#District').val(selectedDistrict);
            }
            if (typeof callback === 'function') callback();
        }
    });
}

// 4. Muat daftar Kelurahan / Desa
function loadSubDistrictsList(prov, city, district, selectedSubDistrict, callback) {
    $.get('{{ url("api/master-kodepos/subdistricts") }}', { province: prov, city: city, district: district }, function(res) {
        if (res.status === 'success' && res.data) {
            var options = '<option value="">-- Pilih Kelurahan / Desa --</option>';
            res.data.forEach(function(s) {
                options += '<option value="' + s.sub_district + '" data-zipcode="' + s.zipcode + '">' + s.sub_district + ' (' + s.zipcode + ')</option>';
            });
            $('#Sub_District').html(options).prop('disabled', false);
            if (selectedSubDistrict) {
                $('#Sub_District').val(selectedSubDistrict);
            }
            if (typeof callback === 'function') callback();
        }
    });
}

// 5. Reverse Lookup: Dari Kode Pos Mengisi Otomatis Provinsi -> Kota -> Kecamatan -> Kelurahan
function applyReverseZipcode() {
    var zipcode = $('#ZipCode').val().trim();
    if (zipcode.length < 5) {
        return;
    }

    $.get('{{ url("api/master-kodepos/by-zipcode") }}', { zipcode: zipcode }, function(res) {
        if (res.status === 'success' && res.data && res.data.length > 0) {
            var locations = res.data;

            if (locations.length === 1) {
                // Tepat 1 lokasi unik
                setCascadeLocation(locations[0]);
            } else {
                // Ada lebih dari 1 lokasi untuk kode pos ini -> Tampilkan Modal Prompt Pemilihan
                showZipSelectionPrompt(zipcode, locations);
            }
        }
    });
}

function showZipSelectionPrompt(zipcode, locations) {
    $('#modalTargetZipcode').text(zipcode);
    var list = $('#zipLocationOptionsList');
    list.empty();

    locations.forEach(function(item, idx) {
        var btn = $('<button type="button" class="list-group-item list-group-item-action py-2 px-3"></button>');
        btn.html(
            '<div class="d-flex w-100 justify-content-between align-items-center">' +
                '<h6 class="mb-1 font-weight-bold text-primary">' + item.sub_district + '</h6>' +
                '<span class="badge badge-info">' + item.zipcode + '</span>' +
            '</div>' +
            '<p class="mb-0 small text-dark">' +
                'Kec. ' + item.district + ', ' + item.city + ', ' + item.province +
            '</p>'
        );
        btn.on('click', function() {
            setCascadeLocation(item);
            $('#modalSelectZipLocation').modal('hide');
        });
        list.append(btn);
    });

    $('#modalSelectZipLocation').modal('show');
}

function setCascadeLocation(item) {
    isApplyingZipcode = true;

    $('#Province').val(item.province);
    loadCitiesList(item.province, item.city, function() {
        loadDistrictsList(item.province, item.city, item.district, function() {
            loadSubDistrictsList(item.province, item.city, item.district, item.sub_district, function() {
                $('#ZipCode').val(item.zipcode).removeClass('is-invalid');
                isApplyingZipcode = false;
            });
        });
    });
}

// Auto-suggest master_kodepos dari input pencarian cepat
var kodeposTimer = null;
$('#kodepos_search_input').on('keyup input', function() {
    clearTimeout(kodeposTimer);
    var q = $(this).val().trim();
    if (q.length < 3) {
        $('#kodepos_dropdown_results').hide().empty();
        return;
    }
    kodeposTimer = setTimeout(function() {
        searchKodeposAuto();
    }, 350);
});

function searchKodeposAuto() {
    var q = $('#kodepos_search_input').val().trim();
    if (q.length < 3) return;

    $.get('{{ url("api/master-kodepos/search") }}', { q: q }, function(res) {
        var box = $('#kodepos_dropdown_results');
        box.empty();
        if (res.status === 'success' && res.data && res.data.length > 0) {
            res.data.forEach(function(item) {
                var btn = $('<button type="button" class="list-group-item list-group-item-action py-2 px-3 small"></button>');
                btn.html('<strong>' + item.zipcode + '</strong> - ' + item.sub_district + ', Kec. ' + item.district + ', ' + item.city + ', ' + item.province);
                btn.on('click', function() {
                    setCascadeLocation(item);
                    box.hide().empty();
                    $('#kodepos_search_input').val('');
                });
                box.append(btn);
            });
            box.show();
        } else {
            box.html('<div class="list-group-item py-2 px-3 small text-muted">Data kode pos tidak ditemukan. Anda dapat memilih manual dari dropdown.</div>').show();
        }
    });
}

$(document).on('click', function(e) {
    if (!$(e.target).closest('#kodepos_search_input, #kodepos_dropdown_results').length) {
        $('#kodepos_dropdown_results').hide();
    }
});
</script>
