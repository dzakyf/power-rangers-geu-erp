<div class="mb-5 p-4 bg-white shadow-sm">
    <div id="surveyHistoryGridSection">
        <div>
            <div id="surveyHistoryGrid" style="width:auto;height:200px;background-color:white;overflow:hidden;margin-bottom:15px;"></div>
        </div>
        <div>
            <button id="btnAddHistory" class="btn btn-primary">Tambah</button>
            <button id="btnDeleteHistory" class="btn btn-danger">Hapus</button>
        </div>
    </div>
    <div id="surveFormSection" style="display:none;" class="m-8">
        <h5>Form Survey Lokasi HUB GEU EXPRESS</h5>
        <form id="surveyForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="Opportunity_Partner_Id" id="Opportunity_Partner_Id" value="" />
            <input type="hidden" name="Recommendation_Type" id="Recommendation_Type" value="MITRA" />
            <h6>A. Informasi Survey</h6>
            <!-- <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="Survey_At">Tanggal Survey</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <input type="date" name="Survey_At" id="Survey_At" class="form-control" required />
                </div>
            </div>
        </div> -->

            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="Province">Provinsi</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <select name="Province" id="Province" class="form-control" required>
                            <option value="">-- Pilih --</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="City">Kota / Kabupaten</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <select name="City" id="City" class="form-control" required disabled>
                            <option value="">-- Pilih --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="District">Kecamatan</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <select name="District" id="District" class="form-control" required disabled>
                            <option value="">-- Pilih --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="Subdistrict">Kelurahan</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <select name="Subdistrict" id="Subdistrict" class="form-control" required disabled>
                            <option value="">-- Pilih --</option>
                        </select>
                    </div>
                </div>
            </div>


            <h6>B. Informasi Lokasi</h6>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="Location_Owner_Name">Nama Pemilik Lokasi</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="text" name="Location_Owner_Name" id="Location_Owner_Name" class="form-control" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="Location_Type">Tipe Lokasi</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="text" name="Location_Type" id="Location_Type" class="form-control" required />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="Address">Alamat Lengkap</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="text" name="Address" id="Address" class="form-control" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="Latitude">Latitude / Longitude</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <input type="text" name="Latitude" id="Latitude" class="form-control" placeholder="Latitude" required />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <input type="text" name="Longitude" id="Longitude" class="form-control" placeholder="Longitude" required />
                    </div>
                </div>
                <!-- <div class="col-md-2">
                <button type="button" class="btn btn-outline-secondary btn-block" id="btnGetGps" title="Get current GPS position">
                    Get GPS
                </button>
                <small id="gpsStatus" class="text-muted"></small>
            </div> -->
            </div>

            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="Area">Luas Area</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="text" name="Area" id="Area" class="form-control" required />
                    </div>
                </div>
            </div>
            <!-- 
        <h6>C. Dokumentasi Foto Lokasi</h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Jenis Foto</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Sesuai</th>
                    <th scope="col">Tidak Sesuai</th>
                    <th scope="col">File</th>

                </tr>
            </thead>
            <tbody id="C_Checklist">
            </tbody>
        </table>

        <h6>D. Checklist Kriteria Lokasi</h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Kriteria</th>
                    <th scope="col">Parameter Penilaian</th>
                    <th scope="col">Standar Minimum</th>
                    <th scope="col">Sesuai</th>
                    <th scope="col">Tidak Sesuai</th>
                    <th scope="col">Catatan</th>

                </tr>
            </thead>
            <tbody id="D_Checklist">
            </tbody>
        </table>

        <h6>E. Kondisi Lingkungan</h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Parameter</th>
                    <th scope="col">Parameter Penilaian</th>
                    <th scope="col">Keterangan</th>

                </tr>
            </thead>
            <tbody id="E_Checklist">
            </tbody>
        </table>


        <h6>F. Estimasi Pengumpulan UCO</h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Submer</th>
                    <th scope="col">Jumlah Sumber</th>
                    <th scope="col">Estimasi UCO</th>
                    <th scope="col">Total Estimasi</th>
                </tr>
            </thead>
            <tbody id="F_Checklist">
            </tbody>
        </table>

        <div id="SurveyorAnalysis" style="display:none;">
            <h6>G. Analisis Surveyor</h6>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Parameter</th>
                        <th scope="col">Penilaian</th>

                    </tr>
                </thead>
                <tbody id="G_Checklist">
                </tbody>
            </table>
        </div>

        <div id="AnalysisResult" class="analysis-result" style="display:none;">
            <h6>Hasil Analisis</h6>
        </div> -->

            <!-- <h6>I. Persetujuan</h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Jabatan</th>
                    <th scope="col">Nama</th>
                    <th scope="col">Sudah Approve?</th>

                </tr>
            </thead>
            <tbody id="I_Checklist">
            </tbody>
        </table> -->

            <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
        </form>
    </div>
</div>

<script>
    function makeSelect2(selector, url, params, valueField, labelField) {
        $(selector).select2({
            placeholder: '-- Pilih --',
            allowClear: true,
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function(p) {
                    var extra = typeof params === 'function' ? params() : params;
                    return Object.assign({
                        q: p.term || ''
                    }, extra);
                },
                processResults: function(res) {
                    return {
                        results: res.data.map(function(row) {
                            return {
                                id: row[valueField],
                                text: row[labelField]
                            };
                        })
                    };
                },
                cache: true
            }
        });
        $(selector).next('.select2-container').css('width', '100%');
    }

    function setSelect2Val(selector, value) {
        var option = new Option(value, value, true, true);
        $(selector).append(option).trigger('change');
    }

    function resetSelect2(selector) {
        $(selector).val(null).trigger('change').prop('disabled', true);
        $(selector).empty().trigger('change');
    }

    var selectedSurveyId = null;

    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var oppId = urlParams.get('id');
        if (oppId) {
            $('#Opportunity_Partner_Id').val(oppId);
            prefillFromRegisteredLead(oppId);
            initSurveyHistoryGrid(oppId);
        }

        $('#btnAddHistory').on('click', function() {
            $('#surveFormSection').show();
            $(this).hide();
        });

        $('#btnDeleteHistory').on('click', function() {
            if (!selectedSurveyId) {
                alert('Pilih data terlebih dulu');
                return;
            }
            if (!confirm('Yakin ingin menghapus data survey ini?')) return;
            $.post('partner-survey-delete', {
                id: selectedSurveyId,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(res) {
                if (res.status === 'success') {
                    alert('Data berhasil dihapus');
                    initSurveyHistoryGrid(oppId);
                } else {
                    alert('Gagal: ' + (res.message || 'Unknown error'));
                }
            });
        });

        makeSelect2('#Province', 'master-province', {}, 'Province', 'Province');

        $('#Province').on('change', function() {
            resetSelect2('#City');
            resetSelect2('#District');
            resetSelect2('#Subdistrict');

            var province = $(this).val();
            if (!province) return;

            $('#City').prop('disabled', false);
            makeSelect2('#City', 'master-city', function() {
                return {
                    province: $('#Province').val()
                };
            }, 'City', 'City');
        });

        $('#City').on('change', function() {
            resetSelect2('#District');
            resetSelect2('#Subdistrict');

            var city = $(this).val();
            if (!city) return;

            $('#District').prop('disabled', false);
            makeSelect2('#District', 'master-district', function() {
                return {
                    province: $('#Province').val(),
                    city: $('#City').val()
                };
            }, 'District', 'District');
        });

        $('#District').on('change', function() {
            resetSelect2('#Subdistrict');

            var district = $(this).val();
            if (!district) return;

            $('#Subdistrict').prop('disabled', false);
            makeSelect2('#Subdistrict', 'master-subdistrict', function() {
                return {
                    province: $('#Province').val(),
                    city: $('#City').val(),
                    district: $('#District').val()
                };
            }, 'Sub_District', 'Sub_District');
        });


        function initSurveyHistoryGrid(oppId) {
            var grid = new dhtmlXGridObject("surveyHistoryGrid");
            grid.setImagePath("./dhtmlx/imgs/");
            grid.setHeader("No,Leads ID,Tipe,Tipe Rekom,Kota/Kabupaten,Kecamatan,Status Kelayakan,Tgl Survey");
            grid.setInitWidths("40");
            grid.setColAlign("center,left,center,left,left,left,left,left");
            grid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
            grid.setSkin("dhx_skyblue");
            grid.attachEvent("onRowSelect", function(id) {
                selectedSurveyId = grid.getRowAttribute(id, 'ID');
            });
            grid.init();
            grid.clearAndLoad("partner-survey-data-history?opportunity_partner_id=" + oppId);
        }

        function prefillFromRegisteredLead(oppId) {
            $.ajax({
                url: 'partner-leads',
                type: 'GET',
                data: {
                    type: 'registered',
                    id: oppId
                },
                success: function(res) {
                    var data = res.data || [];

                    console.log(data)
                    $('#Location_Type').val(data[0].location_status)
                    $('#Location_Owner_Name').val(data[0].full_name ?? data[0].pic_name)
                    $('#Latitude').val(data[0].latitude)
                    $('#Longitude').val(data[0].longitude)
                    if (!data || data.length === 0) return;

                    var lead = Array.isArray(data) ? data[0] : data;
                    if (!lead) return;

                    // Map form field names to lead data keys (case-insensitive match)
                    var textFields = [
                        'Location_Owner_Name', 'Address',
                    ];

                    textFields.forEach(function(field) {
                        var val = getValFromLead(lead, field);
                        if (val !== undefined && val !== null) {
                            $('#' + field).val(val);
                        }
                    });

                    // Prefill cascading selects (Province → City → District → Subdistrict)
                    var province = getValFromLead(lead, 'Province');
                    if (province) {
                        setSelect2Val('#Province', province);
                        $('#Province').trigger('change');

                        var city = getValFromLead(lead, 'City');
                        if (city) {
                            setTimeout(function() {
                                setSelect2Val('#City', city);
                                $('#City').trigger('change');

                                var district = getValFromLead(lead, 'District');
                                if (district) {
                                    setTimeout(function() {
                                        setSelect2Val('#District', district);
                                        $('#District').trigger('change');

                                        var subdistrict = getValFromLead(lead, 'Subdistrict');
                                        if (subdistrict) {
                                            setTimeout(function() {
                                                setSelect2Val('#Subdistrict', subdistrict);
                                            }, 600);
                                        }
                                    }, 600);
                                }
                            }, 600);
                        }
                    }
                },
                error: function() {
                    console.log('Failed to fetch registered lead data');
                }
            });
        }

        function getValFromLead(lead, field) {
            // Try exact match first, then case-insensitive
            if (lead[field] !== undefined) return lead[field];
            var keys = Object.keys(lead);
            for (var i = 0; i < keys.length; i++) {
                if (keys[i].toLowerCase() === field.toLowerCase()) {
                    return lead[keys[i]];
                }
            }
            return undefined;
        }

        $('#surveyForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            // for (const [key, value] of formData.entries()) {
            //     if (value instanceof File) {
            //         console.log(`${key}: File (${value.name})`);
            //     } else {
            //         console.log(`${key}: ${value}`);
            //     }
            // }




            $('#submitBtn').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: "partner-survey-submit",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    alert("Berhasil membuat data survey!");
                    initSurveyHistoryGrid(oppId)
                    updateOnboardingLog('SETUP', 'completed', 'Survey lokasi selesai', function() {
                        // SETUP stage marked complete; will reload on next visit
                    });
                },
                error: function(xhr, status, error) {
                    alert(xhr.responseJSON.message)


                    // Handle validation errors
                    // if (xhr.status === 422) {
                    //     let errors = xhr.responseJSON.errors;
                    //     let errorMessage = "Validation errors:\n";
                    //     $.each(errors, function(field, messages) {
                    //         errorMessage += field + ": " + messages[0] + "\n";
                    //     });
                    //     alert(errorMessage);
                    // } else {
                    //     alert("Telah terjadi kesalahan. Silakan coba lagi.");
                    // }
                },
                complete: function() {
                    $('#submitBtn').prop('disabled', false).text('Submit');
                }
            });
        });
    });
</script>