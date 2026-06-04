<div class="mb-5 p-4 bg-white shadow-sm">
    <h5>Form Survey Lokasi HUB GEU EXPRESS</h5>
    <form id="surveyForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="Opportunity_Partner_Id" id="Opportunity_Partner_Id" value="" />
        <input type="hidden" name="Recommendation_Type" id="Opportunity_Partner_Id" value="MITRA" />
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
                    <label for="Latitude">Latitude</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <input type="text" name="Latitude" id="Latitude" class="form-control" required />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="Longitude">Longitude</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <input type="text" name="Longitude" id="Longitude" class="form-control" required />
                </div>
            </div>
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

        {{-- <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button> --}}
    </form>
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

    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var oppId = urlParams.get('id');
        if (oppId) {
            $('#Opportunity_Partner_Id').val(oppId);
        }

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

            $.each(locationAssessmentData, function(index, item) {

                const fileInput = document.getElementById(`C_c${index+1}_File`);

                if (fileInput && fileInput.files.length > 0) {
                    formData.append(`C_c${index+1}_File`, fileInput.files[0]);

                    // formData.append('file_' + index + '_name', fileInput.files[0].name);
                    // formData.append('file_' + index + '_size', fileInput.files[0].size);
                }
            });


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

                    //$('#surveyForm')[0].reset();

                },
                error: function(xhr, status, error) {
                    console.log('Error details:', xhr.responseText);

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