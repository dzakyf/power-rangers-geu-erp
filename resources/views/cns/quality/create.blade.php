@php
    $contracts = $contracts ?? collect();
    $tanks = $tanks ?? collect();
    $bookings = $bookings ?? collect();
    $category = $category ?? 'EXTERNAL';
@endphp
<div id="FormQualityControl" class="card cns-card" style="display:none;">
    <div class="card-header">
        <h5 class="card-title">Form Quality Control</h5>
        <button type="button" class="close" onclick="cancelCreateQC()" aria-label="Close">&times;</button>
    </div>
    <div class="card-body">
            <form id="QualityControlForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="create_Kode_QC" class="font-weight-bold">Kode QC</label>
                            <input type="text" name="Kode_QC" id="create_Kode_QC" class="text-black form-control" placeholder="[Otomatis dibuat sistem]" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="create_Created_At" class="font-weight-bold">Tanggal Pemeriksaan <span style="color:red;">*</span></label>
                            <input type="date" name="Created_At" id="create_Created_At" class="text-black form-control" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="create_QC_Type" class="font-weight-bold">Jenis Quality Control <span style="color:red;">*</span></label>
                            <select name="QC_Type" id="create_QC_Type" class="text-black form-control" required onchange="onQcTypeChange(this.value)">
                                <option value="INBOUND">Inbound (Barang Masuk)</option>
                                <option value="OUTBOUND">Outbound (Barang Keluar)</option>
                                <option value="DAILY_QC">Daily QC</option>
                                <option value="SURVEYOR">Surveyor (COA Surveyor)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="create_QC_Category" class="font-weight-bold">Pelaksana Lab <span style="color:red;">*</span></label>
                            <select name="QC_Category" id="create_QC_Category" class="text-black form-control" required>
                                <option value="INTERNAL" {{ $category === 'INTERNAL' ? 'selected' : '' }}>Lab Internal</option>
                                <option value="EXTERNAL" {{ $category === 'EXTERNAL' ? 'selected' : '' }}>Lab External</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="create_Attachment" id="create_Attachment_Label" class="font-weight-bold">Upload Dokumen / Bukti Pemeriksaan Lab</label>
                            <input type="file" name="Attachment" id="create_Attachment" class="text-black form-control" accept=".pdf,.jpg,.jpeg,.png" />
                            <small class="form-text text-muted">Dokumen dapat berupa PDF atau Foto.</small>
                        </div>
                    </div>
                    <div class="col-md-3 outbound-contract-group" style="display:none;">
                        <div class="form-group">
                            <label for="create_Contract_ID" class="font-weight-bold">Nomor Kontrak</label>
                            <select name="Contract_ID" id="create_Contract_ID" class="text-black form-control">
                                <option value="">-- Pilih Kontrak --</option>
                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->ID }}">{{ $contract->Contract_No }}{{ !empty($contract->Buyer_Name) ? ' - ' . $contract->Buyer_Name : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 outbound-contract-group" style="display:none;">
                        <div class="form-group">
                            <label for="create_Booking_ID" class="font-weight-bold">Nomor Booking Confirmation</label>
                            <select name="Booking_ID" id="create_Booking_ID" class="text-black form-control">
                                <option value="">-- Pilih Booking Confirmation --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 daily-tank-group" style="display:none;">
                        <div class="form-group">
                            <label for="create_Tank_ID" class="font-weight-bold">Nama Tangki <span style="color:red;">*</span></label>
                            <select name="Tank_ID" id="create_Tank_ID" class="text-black form-control">
                                <option value="">-- Pilih Tangki --</option>
                                @foreach($tanks as $tank)
                                    <option value="{{ $tank->id }}">{{ $tank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 surveyor-bc-group" style="display:none;">
                        <div class="form-group">
                            <label for="create_Surveyor_Booking_ID" class="font-weight-bold">Nomor Booking Confirmation <span style="color:red;">*</span></label>
                            <select name="Booking_ID" id="create_Surveyor_Booking_ID" class="text-black form-control" disabled>
                                <option value="">-- Pilih Booking Confirmation --</option>
                                @foreach($bookings as $booking)
                                    <option value="{{ $booking->ID }}" data-contract-id="{{ $booking->Contract_ID }}">
                                        {{ $booking->Booking_No }}{{ !empty($booking->Label) ? ' (' . $booking->Label . ')' : '' }} — {{ $booking->Contract_No }}{{ !empty($booking->Buyer_Name) ? ' - ' . $booking->Buyer_Name : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 surveyor-bc-group" style="display:none;">
                        <div class="form-group">
                            <label for="create_QC_Method" class="font-weight-bold">Metode QC</label>
                            <input type="text" name="QC_Method" id="create_QC_Method" class="text-black form-control" placeholder="Contoh: AOCS / ISO / ASTM" disabled />
                        </div>
                    </div>
                </div>

                <div class="mt-3 mb-2">
                    <h5 class="font-weight-bold text-dark mb-1">Hasil Pemeriksaan Lab</h5>
                    <p class="text-muted small mb-2">Masukkan nilai parameter sesuai dengan hasil pemeriksaan lab baik internal maupun external.</p>
                </div>

                @php
                    $qcParameters = [
                        ['key' => 'FFA_As_Palmitic', 'label' => '1. FFA (As Palmitic)', 'unit' => '%', 'required' => true],
                        ['key' => 'FFA_As_Oleic',    'label' => '2. FFA (As Oleic)',    'unit' => '%', 'required' => true],
                        ['key' => 'Moisture',        'label' => '3. Moisture',          'unit' => '%', 'required' => true],
                        ['key' => 'Impurities',      'label' => '4. Impurities',        'unit' => '%', 'required' => true],
                        ['key' => 'Unsaponifiables', 'label' => '5. Unsaponifiables',   'unit' => '%', 'required' => false],
                        ['key' => 'Sulphur',         'label' => '6. Total Sulfur',      'unit' => 'ppm', 'required' => false],
                        ['key' => 'IV',              'label' => '7. Iodine Value (IV)', 'unit' => 'g I2/100g', 'required' => false],
                        ['key' => 'Total_Chlorides', 'label' => '8. Chlorides',         'unit' => 'ppm', 'required' => false],
                        ['key' => 'Phosphorus',      'label' => '9. Phosphorus',        'unit' => 'ppm', 'required' => false],
                        ['key' => 'Total_Metals',    'label' => '10. Total Metals',     'unit' => 'ppm', 'required' => false],
                    ];
                @endphp

                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle" style="background-color:#fff;">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:36%;text-align:left;vertical-align:middle;">Parameter QC</th>
                                <th class="qc-level-col" style="width:16%;vertical-align:middle;">TOP</th>
                                <th class="qc-level-col" style="width:16%;vertical-align:middle;">MIDDLE</th>
                                <th class="qc-level-col" style="width:16%;vertical-align:middle;">BOTTOM</th>
                                <th style="width:16%;vertical-align:middle;background-color:#e8f4f8;">AVERAGE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($qcParameters as $param)
                            <tr>
                                <td class="text-left font-weight-bold" style="vertical-align:middle;">
                                    {{ $param['label'] }} <span class="badge badge-light text-secondary border ml-1">{{ $param['unit'] }}</span>
                                    @if($param['required'])
                                        <span style="color:red;">*</span>
                                    @endif
                                </td>
                                <td class="qc-level-col">
                                    <input type="text" inputmode="decimal" name="top_{{ $param['key'] }}" id="create_top_{{ $param['key'] }}"
                                           class="text-black form-control text-right qc-input cns-decimal" oninput="calcQcAverage('{{ $param['key'] }}', 'create')" />
                                </td>
                                <td class="qc-level-col">
                                    <input type="text" inputmode="decimal" name="middle_{{ $param['key'] }}" id="create_middle_{{ $param['key'] }}"
                                           class="text-black form-control text-right qc-input cns-decimal" oninput="calcQcAverage('{{ $param['key'] }}', 'create')" />
                                </td>
                                <td class="qc-level-col">
                                    <input type="text" inputmode="decimal" name="bottom_{{ $param['key'] }}" id="create_bottom_{{ $param['key'] }}"
                                           class="text-black form-control text-right qc-input cns-decimal" oninput="calcQcAverage('{{ $param['key'] }}', 'create')" />
                                </td>
                                <td>
                                    <input type="text" inputmode="decimal" name="avg_{{ $param['key'] }}" id="create_avg_{{ $param['key'] }}"
                                           class="text-black form-control text-right font-weight-bold qc-avg-input"
                                           readonly style="background-color:#f0f8ff;" placeholder="-" />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-primary px-4 mr-2" onclick="submitQC()">Submit</button>
                    <button type="button" class="btn btn-secondary px-4" onclick="cancelCreateQC()">Cancel</button>
                </div>
            </form>
        </div>
</div>

<script>
    var qcParamKeys = [
        'FFA_As_Palmitic', 'FFA_As_Oleic', 'Moisture', 'Impurities', 'Unsaponifiables', 'Sulphur',
        'IV', 'Total_Chlorides', 'Phosphorus', 'Total_Metals'
    ];

    function calcQcAverage(paramKey, prefix) {
        prefix = prefix || 'create';
        if ($('#' + prefix + '_QC_Type').val() === 'SURVEYOR') {
            return;
        }
        var topVal = cnsParseNumber($('#' + prefix + '_top_' + paramKey).val(), NaN);
        var midVal = cnsParseNumber($('#' + prefix + '_middle_' + paramKey).val(), NaN);
        var botVal = cnsParseNumber($('#' + prefix + '_bottom_' + paramKey).val(), NaN);

        var vals = [];
        if (!isNaN(topVal)) vals.push(topVal);
        if (!isNaN(midVal)) vals.push(midVal);
        if (!isNaN(botVal)) vals.push(botVal);

        var avgTarget = $('#' + prefix + '_avg_' + paramKey);
        if (vals.length > 0) {
            var sum = vals.reduce(function(a, b) { return a + b; }, 0);
            var avg = sum / vals.length;
            avgTarget.val(cnsFormatNumber(avg, 2));
        } else {
            avgTarget.val('-');
        }
    }

    function recalculateAllAverages(prefix) {
        qcParamKeys.forEach(function(key) {
            calcQcAverage(key, prefix);
        });
    }

    function setCreateSurveyorMode(isSurveyor) {
        var $avgInputs = $('#FormQualityControl .qc-avg-input');
        if (isSurveyor) {
            $('#FormQualityControl .qc-level-col').hide();
            $('#create_QC_Category').val('EXTERNAL');
            $('#create_QC_Category option[value="INTERNAL"]').prop('disabled', true);
            $('#create_Attachment_Label').html('Upload File COA Surveyor <span style="color:red;">*</span>');
            $('#create_Attachment').prop('required', true);
            $avgInputs.prop('readonly', false).addClass('cns-decimal').css('background-color', '#fff');
            $avgInputs.each(function() {
                if ($(this).val() === '-') $(this).val('');
            });
        } else {
            $('#FormQualityControl .qc-level-col').show();
            $('#create_QC_Category option[value="INTERNAL"]').prop('disabled', false);
            $('#create_Attachment_Label').text('Upload Dokumen / Bukti Pemeriksaan Lab');
            $('#create_Attachment').prop('required', false);
            $avgInputs.prop('readonly', true).removeClass('cns-decimal').css('background-color', '#f0f8ff');
            recalculateAllAverages('create');
        }
    }

    function onQcTypeChange(val) {
        if (val === 'OUTBOUND') {
            $('.outbound-contract-group').show();
            $('.daily-tank-group, .surveyor-bc-group').hide();
            $('#create_Booking_ID').prop('disabled', false);
            $('#create_Surveyor_Booking_ID').prop('disabled', true).val('');
            $('#create_QC_Method').prop('disabled', true).val('');
            $('#create_Tank_ID').val('');
            setCreateSurveyorMode(false);
        } else if (val === 'DAILY_QC') {
            $('.daily-tank-group').show();
            $('.outbound-contract-group, .surveyor-bc-group').hide();
            $('#create_Booking_ID').prop('disabled', false).empty().append('<option value="">-- Pilih Booking Confirmation --</option>');
            $('#create_Surveyor_Booking_ID').prop('disabled', true).val('');
            $('#create_QC_Method').prop('disabled', true).val('');
            $('#create_Contract_ID').val('');
            setCreateSurveyorMode(false);
        } else if (val === 'SURVEYOR') {
            $('.surveyor-bc-group').show();
            $('.outbound-contract-group, .daily-tank-group').hide();
            $('#create_Booking_ID').prop('disabled', true).empty().append('<option value="">-- Pilih Booking Confirmation --</option>');
            $('#create_Surveyor_Booking_ID').prop('disabled', false);
            $('#create_QC_Method').prop('disabled', false);
            $('#create_Contract_ID').val('');
            $('#create_Tank_ID').val('');
            setCreateSurveyorMode(true);
        } else {
            $('.outbound-contract-group, .daily-tank-group, .surveyor-bc-group').hide();
            $('#create_Booking_ID').prop('disabled', false).empty().append('<option value="">-- Pilih Booking Confirmation --</option>');
            $('#create_Surveyor_Booking_ID').prop('disabled', true).val('');
            $('#create_QC_Method').prop('disabled', true).val('');
            $('#create_Contract_ID').val('');
            $('#create_Tank_ID').val('');
            setCreateSurveyorMode(false);
        }
    }

    function cancelCreateQC() {
        $('#FormQualityControl').hide();
        $('#QualityControlForm')[0].reset();
        $('#create_Booking_ID').empty().append('<option value="">-- Pilih Booking Confirmation --</option>');
        $('#create_Surveyor_Booking_ID').val('');
        $('#create_Tank_ID').val('');
        qcParamKeys.forEach(function(key) {
            $('#create_avg_' + key).val('-');
        });
        onQcTypeChange('INBOUND');
    }

    function loadQualityBookings(contractId, targetSelector, selectedId) {
        var select = $(targetSelector);
        select.empty().append('<option value="">-- Pilih Booking Confirmation --</option>');

        if (!contractId) {
            return;
        }

        $.get('cns-quality-bookings?contract_id=' + encodeURIComponent(contractId), function(data) {
            if (Array.isArray(data)) {
                data.forEach(function(booking) {
                    var text = (booking.label ? booking.label + ' - ' : '') + (booking.booking_no || '');
                    var option = $('<option></option>').val(booking.id).text(text);
                    if (selectedId && String(selectedId) === String(booking.id)) {
                        option.prop('selected', true);
                    }
                    select.append(option);
                });
                if (selectedId) {
                    select.val(String(selectedId));
                }
            }
        });
    }

    $(document).ready(function() {
        $('#create_Contract_ID').on('change', function() {
            var contractId = $(this).val();
            loadQualityBookings(contractId, '#create_Booking_ID', null);
        });
    });

    function submitQC() {
        var form = document.getElementById('QualityControlForm');
        var createdAt = form.Created_At.value;
        var qcType = form.QC_Type.value;
        var qcCategory = form.QC_Category.value;

        if (!createdAt) {
            swal('Perhatian', 'Tanggal Pemeriksaan wajib diisi', 'warning');
            return;
        }

        if (qcType === 'DAILY_QC' && !form.Tank_ID.value) {
            swal('Perhatian', 'Nama Tangki wajib dipilih untuk Daily QC', 'warning');
            return;
        }

        var mandatoryParams = ['FFA_As_Palmitic', 'FFA_As_Oleic', 'Moisture', 'Impurities'];

        if (qcType === 'SURVEYOR') {
            var surveyorBookingId = $('#create_Surveyor_Booking_ID').val();
            if (!surveyorBookingId) {
                swal('Perhatian', 'Nomor Booking Confirmation wajib dipilih untuk QC Surveyor', 'warning');
                return;
            }
            var attachmentInput = document.getElementById('create_Attachment');
            if (!attachmentInput || !attachmentInput.files || attachmentInput.files.length === 0) {
                swal('Perhatian', 'File COA Surveyor wajib diupload', 'warning');
                return;
            }
            for (var k = 0; k < mandatoryParams.length; k++) {
                var sKey = mandatoryParams[k];
                var sVal = $('#create_avg_' + sKey).val();
                if (sVal === '' || sVal === null || isNaN(cnsParseNumber(sVal, NaN))) {
                    swal('Perhatian', 'Parameter ' + sKey.replace(/_/g, ' ') + ' pada kolom AVERAGE wajib diisi.', 'warning');
                    return;
                }
            }
        } else {
            // Validate mandatory parameters for all 3 levels (Top, Middle, Bottom)
            var levels = ['top', 'middle', 'bottom'];

            if (qcType === 'OUTBOUND') {
                mandatoryParams = qcParamKeys;
            }

            for (var i = 0; i < levels.length; i++) {
                var lvl = levels[i];
                for (var j = 0; j < mandatoryParams.length; j++) {
                    var pKey = mandatoryParams[j];
                    var val = $('#create_' + lvl + '_' + pKey).val();
                    if (val === '' || val === null || isNaN(cnsParseNumber(val, NaN))) {
                        swal('Perhatian', 'Parameter ' + pKey.replace(/_/g, ' ') + ' pada level ' + lvl.toUpperCase() + ' wajib diisi.', 'warning');
                        return;
                    }
                }
            }
        }

        var formData = new FormData(form);
        if (qcType === 'SURVEYOR') {
            formData.set('Booking_ID', $('#create_Surveyor_Booking_ID').val());
            formData.set('QC_Category', 'EXTERNAL');
        }
        $(form).find('input.cns-decimal').each(function() {
            var name = $(this).attr('name');
            if (name && $.trim($(this).val()) !== '') {
                formData.set(name, cnsParseNumber($(this).val(), 0));
            }
        });

        $.ajax({
            url: 'cns-quality-submit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    swal('Berhasil!', 'Data Quality Control berhasil disimpan', 'success').then(function() {
                        cancelCreateQC();
                        loadQualityGrid();
                    });
                } else {
                    swal('Error', res.message || 'Gagal menyimpan data Quality Control', 'error');
                }
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan data Quality Control';
                swal('Error', msg, 'error');
            }
        });
    }
</script>
