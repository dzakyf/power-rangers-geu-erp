@extends('layout.mainlayout-bp')

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
<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
    <h5>Form Survey Lokasi HUB GEU EXPRESS</h5>
    <form id="surveyForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="ID" id="ID">
        <input type="hidden" name="Parent_Id" id="Parent_Id">
        <input type="hidden" name="Suggestion_Type" id="Suggestion_Type" value="ORIGINAL">
        <h6>A. Informasi Survey</h6>
        <div class="row">
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
        </div>

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

        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="Surveyor_Name">Nama Surveyor </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <input type="text" name="Surveyor_Name" id="Surveyor_Name" class="form-control" required />
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
        </div>

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
    const photoData = [{
            no: 1,
            jenisFoto: "Foto Tampak Depan",
            keterangan: "Foto Area Lokasi"
        },
        {
            no: 2,
            jenisFoto: "Foto Area Operasional",
            keterangan: "Area untuk Booth / Mobil"
        },
        {
            no: 3,
            jenisFoto: "Foto Akses Kendaraan",
            keterangan: "Jalan Masuk"
        },
        {
            no: 4,
            jenisFoto: "Foto Lingkungan Sekitar",
            keterangan: "Kondisi Sekitar"
        }
    ];

    const locationAssessmentData = [{
            no: 1,
            criteria: "Luas Area Operasional",
            parameter: "Area yang tersedia untuk penempatan equipment",
            min_standard: "Minimal 3 meter x 2 meter (6 m²)"
        },
        {
            no: 2,
            criteria: "Jenis Area",
            parameter: "Kondisi area penempatan operasional",
            min_standard: "Outdoor / Semi terbuka, tidak berada di dalam ruangan tertutup"
        },
        {
            no: 3,
            criteria: "Akses Kendaraan Operasional",
            parameter: "Kemudahan kendaraan masuk ke area hub",
            min_standard: "Kendaraan roda 4 dapat masuk dan melakukan aktivitas loading/unloading"
        },
        {
            no: 4,
            criteria: "Area Parkir",
            parameter: "Ketersediaan parkir kendaraan operasional",
            min_standard: "Minimal 1 mobil dan 3 motor"
        },
        {
            no: 5,
            criteria: "Ketersediaan Listrik",
            parameter: "Sumber listrik untuk operasional peralatan",
            min_standard: "Minimal 1.300 watt atau sumber listrik stabil"
        },
        {
            no: 6,
            criteria: "Ketersediaan Air Bersih",
            parameter: "Air untuk kegiatan kebersihan operasional",
            min_standard: "Tersedia sumber air bersih (kran / sumur / PDAM)"
        },
        {
            no: 7,
            criteria: "Kondisi Lingkungan",
            parameter: "Lingkungan sekitar mendukung aktivitas operasional",
            min_standard: "Area aman, tidak rawan banjir, tidak mengganggu aktivitas sekitar"
        },
        {
            no: 8,
            criteria: "Keamanan Lokasi",
            parameter: "Tingkat keamanan area operasional",
            min_standard: "Area memiliki pengawasan / tidak rawan pencurian"
        },
        {
            no: 9,
            criteria: "Akses Jalan Utama",
            parameter: "Kemudahan akses ke jalur transportasi utama",
            min_standard: "Lokasi mudah dijangkau kendaraan operasional"
        }
    ];

    const environtmentAssessmentData = [{
            no: 1,
            parameter: "Kepadatan Area",
            parameter_evaluation: "Tingkat aktivitas dan kepadatan bangunan/usaha di sekitar lokasi dalam radius ±200–500 meter.",
            explanation: "Tinggi: area pasar, pusat kuliner, atau ruko ramai.\nSedang: area campuran ruko dan perumahan.\nRendah: area perumahan sepi atau lahan kosong."
        },
        {
            no: 2,
            parameter: "Aktivitas Ekonomi",
            parameter_evaluation: "Jenis aktivitas usaha di sekitar lokasi yang berpotensi menghasilkan minyak jelantah.",
            explanation: "Tinggi: >15 usaha kuliner.\nSedang: 5–15 usaha kuliner.\nRendah: <5 usaha kuliner."
        },
        {
            no: 3,
            parameter: "Potensi Sumber UCO",
            parameter_evaluation: "Perkiraan jumlah sumber penghasil minyak jelantah di sekitar lokasi dalam radius operasional.",
            explanation: "Tinggi: >20 sumber UCO.\nSedang: 10–20 sumber UCO.\nRendah: <10 sumber UCO."
        },
        {
            no: 4,
            parameter: "Keamanan Lokasi",
            parameter_evaluation: "Indikator: ada penjaga keamanan, CCTV, penerangan malam, tidak rawan pencurian atau vandalisme.",
            explanation: "Baik: area memiliki keamanan aktif (security/CCTV).\nCukup: area ramai namun tanpa sistem keamanan khusus.\nKurang: lokasi sepi atau rawan gangguan keamanan."
        }
    ];

    const ucoPotentialData = [{
            no: 1,
            source: "Restoran",
            source_value: 1500,
            uco_estimation: 27000,
            total_estimation: 0
        },
        {
            no: 2,
            source: "Warung makan",
            source_value: 2800,
            uco_estimation: 50400,
            total_estimation: 0

        },
        {
            no: 3,
            source: "Hotel",
            source_value: 450,
            uco_estimation: 32400,
            total_estimation: 0
        },
        {
            no: 4,
            source: "Rumah Tangga",
            source_value: 11000,
            uco_estimation: 11000,
            total_estimation: 0
        },
        {
            no: 5,
            source: "Total",
            source_value: 0,
            uco_estimation: 0,
            total_estimation: 0
        }
    ];

    const surveyorAnalysisData = [{
            no: 1,
            parameter: "Potensi Supply",
            parameter_evaluation: 0
        },
        {
            no: 2,
            parameter: "Akses Operasional",
            parameter_evaluation: 0
        },
        {
            no: 3,
            parameter: "Lingkungan",
            parameter_evaluation: 0
        }, {
            no: 4,
            parameter: "Potensi Pengumpulan UCO",
            parameter_evaluation: 0
        }
    ];

    const surveyorRecommendationData = [{
            status: "Layak",
            value: "1"
        },
        {
            status: "Layak Bersyarat",
            value: "2"
        },
        {
            status: "Tidak Layak",
            value: "3"
        },

    ];


    function generateRowId(index) {
        return 'row_' + index;
    }

    function renderTableSurveyorRecommendation(data) {
        let html = '';

        $.each(surveyorRecommendationData, function(index, item) {
            const rowId = generateRowId(index);
            const sesuaiId = 'sesuai_' + index;
            const tidakId = 'tidak_' + index;
            const fileInputId = 'file_' + index;
            const radioId = `H_p1_col1`
            const val = index === 1 ? 1 : 0
            const status = item.status

            html += '<tr id="' + rowId + '">';
            html += '    <td class="radio-group">';
            html += '<input type="radio" name="' + radioId + '" value="' + item.value + '" id="' + status + '">';
            html += '<label for="' + status + '">' + status + '</label>';
            html += '    </td>';
            html += '</tr>';
        });

        $('#H_Checklist').html(html);

        // Add file change event listeners
        // attachFileChangeListeners();
    }

    function renderTableSurveyorAnalysis(data) {
        let html = '';
        let d = data


        surveyorAnalysisData[0]['parameter_evaluation'] = d.C_Total_Score
        surveyorAnalysisData[1]['parameter_evaluation'] = d.D_Total_Score
        surveyorAnalysisData[2]['parameter_evaluation'] = d.E_Total_Score
        surveyorAnalysisData[3]['parameter_evaluation'] = d.F_Total_Score


        $.each(surveyorAnalysisData, function(index, item) {
            const rowId = generateRowId(index);
            const sesuaiId = 'sesuai_' + index;
            const tidakId = 'tidak_' + index;
            const fileInputId = 'file_' + index;


            html += '<tr id="' + rowId + '">';
            html += '    <td>' + item.no + '</td>';
            html += '    <td>' + item.parameter + '</td>';
            html += '    <td>' + item.parameter_evaluation + '</td>';
            html += '</tr>';
        });

        $('#G_Checklist').html(html);

        // Add file change event listeners
        // attachFileChangeListeners();
    }

    function renderTableUCOEstimation(data) {
        let html = '';

        $.each(ucoPotentialData, function(index, item) {
            const idx = index + 1
            const rowId = generateRowId(index);
            const sourceValId = `F_p${idx}_col1`
            const UCOEstimationId = `F_p${idx}_col2`
            const totalEstimationId = `F_p${idx}_col3`
            const isRow5 = (idx == 5); // Row 5 will be the total row

            html += '<tr id="' + rowId + '">';
            html += '      <td>' + item.no + '';
            html += '      <td>' + item.source + '';

            // Column 1 (Source Value)
            if (isRow5) {
                html += '      <td><input type="number" id="' + sourceValId + '" name="' + sourceValId + '" class="source-value total-source" data-row="' + idx + '" readonly style="background-color:#e9ecef; font-weight:bold;" />';
            } else {
                html += '      <td><input type="number" id="' + sourceValId + '" name="' + sourceValId + '" class="source-value" data-row="' + idx + '" min="0" step="0.01" />';
            }

            // Column 2 (UCO Estimation)
            if (isRow5) {
                html += '      <td><input type="number" id="' + UCOEstimationId + '" name="' + UCOEstimationId + '" class="uco-estimation total-uco" data-row="' + idx + '" readonly style="background-color:#e9ecef; font-weight:bold;" />';
            } else {
                html += '      <td><input type="number" id="' + UCOEstimationId + '" name="' + UCOEstimationId + '" class="uco-estimation" data-row="' + idx + '" min="0" step="0.01" />';
            }

            // Column 3 (Total Estimation)
            if (isRow5) {
                html += '      <td><input type="number" id="' + totalEstimationId + '" name="' + totalEstimationId + '" class="total-estimation total-sum" data-row="' + idx + '" readonly style="background-color:#e9ecef; font-weight:bold;" />';
            } else {
                html += '      <td><input type="number" id="' + totalEstimationId + '" name="' + totalEstimationId + '" class="total-estimation" data-row="' + idx + '" readonly style="background-color:#f8f9fa;" />';
            }
            html += '     </tr>';
        });

        $('#F_Checklist').html(html);

        // Initialize calculations
        initializeUCOCalculations();
    }

    function initializeUCOCalculations() {
        // Function to calculate individual row total (col3 = col1 + col2)
        function calculateRowTotal(row) {
            const sourceVal = parseFloat($(`#F_p${row}_col1`).val()) || 0;
            const ucoVal = parseFloat($(`#F_p${row}_col2`).val()) || 0;
            const total = sourceVal * ucoVal;
            $(`#F_p${row}_col3`).val(total.toFixed(2));
            console.log(`Row ${row}: ${sourceVal} + ${ucoVal} = ${total.toFixed(2)}`);
            return {
                sourceVal,
                ucoVal,
                total
            };
        }

        // Function to calculate sum of column 1 from rows 1-4 and put in row 5 col1
        function calculateRow5Col1() {
            let sumSource = 0;

            // Sum all Source values from rows 1-4
            for (let i = 1; i <= 4; i++) {
                const sourceVal = parseFloat($(`#F_p${i}_col1`).val()) || 0;
                sumSource += sourceVal;
                console.log(`Row ${i} col1: ${sourceVal}, Running sum: ${sumSource}`);
            }

            // Set row 5 col1 to the sum
            $('#F_p5_col1').val(sumSource.toFixed(2));
            console.log(`Row 5 col1 (Total Source) set to: ${sumSource.toFixed(2)}`);

            return sumSource;
        }

        // Function to calculate sum of column 2 from rows 1-4 and put in row 5 col2
        function calculateRow5Col2() {
            let sumUCO = 0;

            // Sum all UCO values from rows 1-4
            for (let i = 1; i <= 4; i++) {
                const ucoVal = parseFloat($(`#F_p${i}_col2`).val()) || 0;
                sumUCO += ucoVal;
                console.log(`Row ${i} col2: ${ucoVal}, Running sum: ${sumUCO}`);
            }

            // Set row 5 col2 to the sum
            $('#F_p5_col2').val(sumUCO.toFixed(2));
            console.log(`Row 5 col2 (Total UCO) set to: ${sumUCO.toFixed(2)}`);

            return sumUCO;
        }

        // Function to calculate sum of column 3 from rows 1-4 and put in row 5 col3
        function calculateRow5Col3() {
            let sumTotal = 0;

            // Sum all Total Estimation values from rows 1-4
            for (let i = 1; i <= 4; i++) {
                const totalVal = parseFloat($(`#F_p${i}_col3`).val()) || 0;
                sumTotal += totalVal;
                console.log(`Row ${i} col3: ${totalVal}, Running sum: ${sumTotal}`);
            }

            // Set row 5 col3 to the sum
            $('#F_p5_col3').val(sumTotal.toFixed(2));
            console.log(`Row 5 col3 (Total Estimation Sum) set to: ${sumTotal.toFixed(2)}`);

            return sumTotal;
        }

        // Function to recalculate all rows
        function recalculateAll() {
            console.log('Recalculating all...');

            // Calculate row totals for rows 1-4 (non-total rows)
            for (let i = 1; i <= 4; i++) {
                calculateRowTotal(i);
            }

            // Calculate row 5 col1 (sum of rows 1-4 col1)
            calculateRow5Col1();

            // Calculate row 5 col2 (sum of rows 1-4 col2)
            calculateRow5Col2();

            // Calculate row 5 col3 (sum of rows 1-4 col3)
            calculateRow5Col3();
        }

        // Event handlers
        function handleInputChange() {
            const row = $(this).data('row');
            console.log(`Change detected on row ${row}, element: ${$(this).attr('id')}, value: ${$(this).val()}`);

            if (row == 5) {
                // If row 5 changes, row 5 col3 is already a sum from rows 1-4 col3
                // No action needed as row 5 col3 is calculated from rows 1-4
                console.log('Row 5 is a total row, values are calculated automatically');
            } else {
                // For rows 1-4, update the current row's total (col3 = col1 + col2)
                calculateRowTotal(row);

                // Then update all row 5 totals
                calculateRow5Col1(); // Update total source (sum of rows 1-4 col1)
                calculateRow5Col2(); // Update total UCO (sum of rows 1-4 col2)
                calculateRow5Col3(); // Update total estimation (sum of rows 1-4 col3)
            }
        }

        // Attach event listeners
        $(document).off('input', '.source-value, .uco-estimation');
        $(document).on('input', '.source-value, .uco-estimation', handleInputChange);

        // Initial calculation
        recalculateAll();

        console.log('Initialization complete');
    }

    function renderTableEnvirontmentAssessment() {
        let html = '';

        $.each(environtmentAssessmentData, function(index, item) {
            const rowId = generateRowId(index);
            const sesuaiId = 'sesuai_' + index;
            const tidakId = 'tidak_' + index;
            const fileInputId = 'file_' + index;
            const name = `E_p${index+1}`

            var selectedValue = item.rating_value || "";

            html += '<tr id="' + rowId + '">';
            html += '     <td>' + item.no + '</td>';
            html += '     <td>' + item.parameter + '</td>';
            html += '     <td>';
            html += '         <select name="' + name + '" class="rating-select" data-rowid="' + rowId + '">';
            html += '             <option value="">-- Pilih --</option>';
            html += '             <option value="1"' + (selectedValue == "1" ? ' selected' : '') + '>Kurang</option>';
            html += '             <option value="2"' + (selectedValue == "2" ? ' selected' : '') + '>Sedang</option>';
            html += '             <option value="3"' + (selectedValue == "3" ? ' selected' : '') + '>Tinggi</option>';
            html += '         </select>';
            html += '     </td>';
            html += '     <td>' + item.parameter_evaluation + '</td>';
            html += '     <td>' + item.explanation + '</td>';
            html += '</tr>';
        });

        $('#E_Checklist').html(html);

        // Add file change event listeners
        // attachFileChangeListeners();
    }

    function renderTableLocationAssessment() {
        let html = '';

        $.each(locationAssessmentData, function(index, item) {
            const rowId = generateRowId(index);
            const radioId = `D_c${index+1}`
            const noteId = `D_c${index+1}_Notes`

            html += '<tr id="' + rowId + '">';
            html += '    <td>' + item.no + '</td>';
            html += '    <td>' + item.criteria + '</td>';
            html += '    <td>' + item.parameter + '</td>';
            html += '    <td>' + item.min_standard + '</td>';
            html += '    <td class="radio-group">';
            html += '        <input type="radio" name="' + radioId + '" value="1" id="' + radioId + '">';
            html += '    </td>';
            html += '    <td class="radio-group">';
            html += '        <input type="radio" name="' + radioId + '" value="0" id="' + radioId + '">';
            html += '    </td>';
            html += '    <td>';
            html += '    <textarea id="' + noteId + '" name="' + noteId + '" rows="5" cols="33"></textarea>'
            html += '    </td>';
            html += '</tr>';
        });

        $('#D_Checklist').html(html);

        // Add file change event listeners
        // attachFileChangeListeners();
    }

    function renderTableLocationDocumentation() {
        let html = '';

        $.each(photoData, function(index, item) {
            const rowId = generateRowId(index);
            const sesuaiId = 'sesuai_' + index;
            const tidakId = 'tidak_' + index;
            const radioId = `C_c${index+1}`
            const fileInputId = `${radioId}_File`;

            html += '<tr id="' + rowId + '">';
            html += '    <td>' + item.no + '</td>';
            html += '    <td>' + item.jenisFoto + '</td>';
            html += '    <td>' + item.keterangan + '</td>';
            html += '    <td class="radio-group">';
            html += '        <input type="radio" name="' + radioId + '" value="1" id="' + radioId + '">';
            html += '    </td>';
            html += '    <td class="radio-group">';
            html += '        <input type="radio" name="' + radioId + '" value="0" id="' + radioId + '">';
            html += '    </td>';
            html += '    <td>';
            html += '        <input type="file" name="' + fileInputId + '" id="' + fileInputId + '" style="display: none;">';
            html += '        <button class="upload-btn" type="button" onclick="$(\'#' + fileInputId + '\').click();">Upload</button>';
            html += '        <span class="file-name" id="fileName_' + index + '">No file chosen</span>';
            html += '    </td>';
            html += '</tr>';
        });

        $('#C_Checklist').html(html);

        // Add file change event listeners
        // attachFileChangeListeners();
    }

    function showAnalysisResult(data) {
        $('#AnalysisResult').html('<h6>H. Hasil Analisis</h6><h6>' + data.Analysis_Result + '</h6>');
    }

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
        renderTableLocationDocumentation()
        renderTableLocationAssessment()
        renderTableEnvirontmentAssessment()
        renderTableUCOEstimation()

        renderTableSurveyorRecommendation()

        var urlParams = new URLSearchParams(window.location.search);
        var editId = urlParams.get('id');
        var parentId = urlParams.get('parent_id');

        if (editId) {
            $('#ID').val(editId);
            getSurveyData(editId);
        } else if (parentId) {
            $('#Parent_Id').val(parentId);
            $('#Suggestion_Type').val('REPLY');
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
        if (!id) id = new URLSearchParams(window.location.search).get("id");
        if (!id) return;

        $.ajax({
            url: 'partner-survey-data?format=json&id=' + id,
            type: 'GET',
            success: function(res) {
                var d = res.data[0];
                if (!d) return;

                $('#Survey_At').val(d.Surveyed_At);
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

                $('#Surveyor_Name').val(d.Surveyor_Name);
                $('#Location_Owner_Name').val(d.Location_Owner_Name);
                $('#Location_Type').val(d.Location_Type);
                $('#Address').val(d.Address);
                $('#Latitude').val(d.Latitude);
                $('#Longitude').val(d.Longitude);
                $('#Area').val(d.Area);

                for (var i = 1; i <= 4; i++) {
                    var val = d['C_c' + i];
                    $('input[name="C_c' + i + '"][value="' + val + '"]').prop('checked', true);
                }

                for (var j = 1; j <= 9; j++) {
                    var val = d['D_c' + j];
                    $('input[name="D_c' + j + '"][value="' + val + '"]').prop('checked', true);
                    $('#D_c' + j + '_Notes').val(d['D_c' + j + '_Notes'] || '');
                }

                for (var k = 1; k <= 4; k++) {
                    $('select[name="E_p' + k + '"]').val(d['E_p' + k]);
                }

                for (var f = 1; f <= 4; f++) {
                    $('#F_p' + f + '_col1').val(d['F_p' + f + '_col1']);
                    $('#F_p' + f + '_col2').val(d['F_p' + f + '_col2']);
                }
                initializeUCOCalculations();
            }
        });
    }


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

            // C_c1_File..C_c4_File are named inputs, so they are already
            // part of FormData(this).


            $('#submitBtn').prop('disabled', true).text('Submitting...');

            var submitUrl = $('#ID').val() ? 'partner-survey-update' : 'partner-survey-submit';

            $.ajax({
                url: submitUrl,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function(response) {
                    alert("Berhasil membuat data survey!");
                    let data = response.data
                    let analysisRes = "Unknown"



                    //$('#surveyForm')[0].reset();
                    renderTableSurveyorAnalysis(data)
                    renderTableSurveyorRecommendation(data)
                    showAnalysisResult(data)
                    $('#AnalysisResult').show()
                    $('#SurveyorAnalysis').show()
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