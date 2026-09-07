@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Survey</span>
        </li>
    </ol>
</div>
<div class="mb-5 p-4 bg-white shadow-sm">
    <h5>Form Survey Lokasi HUB GEU EXPRESS</h5>
    <form id="surveyForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="Opportunity_Partner_Id" id="Opportunity_Partner_Id" value="" />
        <input type="hidden" name="Recommendation_Type" id="Opportunity_Partner_Id" value="MITRA" />
        <input type="hidden" name="Parent_Id" id="Parent_Id">
        <input type="hidden" name="Suggestion_Type" id="Suggestion_Type" value="REPLY">
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

        -->
        <!-- <h6>D. Checklist Kriteria Lokasi</h6>
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
        var parentId = urlParams.get('parent_id');
        if (parentId) {
            $('#Parent_Id').val(parentId);
            getSurveyData(parentId);
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

        function getSurveyData(id) {
            $.ajax({
                url: 'partner-survey-data?format=json&id=' + id,
                type: 'GET',
                success: function(res) {
                    var data = res.data || [];
                    if (!data || data.length === 0) return;

                    var d = Array.isArray(data) ? data[0] : data;
                    if (!d) return;

                    setSelect2Val('#Province', d.Province);
                    $('#Province').trigger('change');

                    setTimeout(function() {
                        setSelect2Val('#City', d.City);
                        $('#City').trigger('change');

                        setTimeout(function() {
                            setSelect2Val('#District', d.District);
                            $('#District').trigger('change');

                            setTimeout(function() {
                                setSelect2Val('#Subdistrict', d.Subdistrict);
                            }, 600);
                        }, 600);
                    }, 600);

                    $('#Location_Owner_Name').val(d.Location_Owner_Name);
                    $('#Location_Type').val(d.Location_Type);
                    $('#Address').val(d.Address);
                    $('#Latitude').val(d.Latitude);
                    $('#Longitude').val(d.Longitude);
                    $('#Area').val(d.Area);
                    $('#Opportunity_Partner_Id').val(d.Opportunity_Partner_Id);
                },
                error: function() {
                    console.log('Failed to fetch survey data');
                }
            });
        }



    // C. Dokumentasi Foto Lokasi (restored: MITRA can attach location photos)
    const photoData = [{
            no: 1,
            jenisFoto: 'Foto Tampak Depan',
            keterangan: 'Foto Area Lokasi'
        },
        {
            no: 2,
            jenisFoto: 'Foto Area Operasional',
            keterangan: 'Area untuk Booth / Mobil'
        },
        {
            no: 3,
            jenisFoto: 'Foto Akses Kendaraan',
            keterangan: 'Jalan Masuk'
        },
        {
            no: 4,
            jenisFoto: 'Foto Lingkungan Sekitar',
            keterangan: 'Kondisi Sekitar'
        }
    ];

    function renderTableLocationDocumentation() {
        let html = '';
        $.each(photoData, function(index, item) {
            const radioId = `C_c${index+1}`;
            const fileInputId = `${radioId}_File`;
            html += '<tr>';
            html += '    <td>' + item.no + '</td>';
            html += '    <td>' + item.jenisFoto + '</td>';
            html += '    <td>' + item.keterangan + '</td>';
            html += '    <td class="radio-group"><input type="radio" name="' + radioId + '" value="1" id="' + radioId + '"></td>';
            html += '    <td class="radio-group"><input type="radio" name="' + radioId + '" value="0" id="' + radioId + '_0"></td>';
            html += '    <td>';
            html += '        <input type="file" name="' + fileInputId + '" id="' + fileInputId + '" style="display: none;">';
            html += '        <button class="upload-btn" type="button" onclick="$(\'#' + fileInputId + '\').click();">Upload</button>';
            html += '        <span class="file-name" id="fileName_' + index + '">No file chosen</span>';
            html += '    </td>';
            html += '</tr>';
        });
        $('#C_Checklist').html(html);
    }

    $(function() { renderTableLocationDocumentation(); });

    // Show the chosen file name next to the Upload button (photo docs)
    $(document).on('change', 'input[type="file"][id^="C_c"][id$="_File"]', function() {
        var m = this.id.match(/^C_c(\d+)_File$/);
        if (!m) { return; }
        var $label = $('#fileName_' + (parseInt(m[1], 10) - 1));
        if ($label.length) {
            $label.text(this.files.length ? this.files[0].name : 'No file chosen');
        }
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
@endsection