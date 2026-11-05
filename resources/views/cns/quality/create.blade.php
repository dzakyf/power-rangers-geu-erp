@php
    $contracts = $contracts ?? collect();
    $category = $category ?? 'EXTERNAL';
@endphp
<div id="FormQualityControl" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">FORM QUALITY CONTROL</div>
    <div class="card w-100 shadow-sm">
        <div class="card-body">
            <form id="QualityControlForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="create_Kode_QC" class="font-weight-bold">Kode QC</label>
                            <input type="text" name="Kode_QC" id="create_Kode_QC" class="form-control" placeholder="[Otomatis dibuat sistem]" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="create_Created_At" class="font-weight-bold">Tanggal Pemeriksaan <span style="color:red;">*</span></label>
                            <input type="date" name="Created_At" id="create_Created_At" class="form-control" required />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="create_QC_Type" class="font-weight-bold">Jenis Quality Control <span style="color:red;">*</span></label>
                            <select name="QC_Type" id="create_QC_Type" class="form-control" required onchange="onQcTypeChange(this.value)">
                                <option value="INBOUND">Inbound (Barang Masuk)</option>
                                <option value="OUTBOUND">Outbound (Barang Keluar)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="create_QC_Category" class="font-weight-bold">Pelaksana Lab <span style="color:red;">*</span></label>
                            <select name="QC_Category" id="create_QC_Category" class="form-control" required>
                                <option value="INTERNAL" {{ $category === 'INTERNAL' ? 'selected' : '' }}>Lab Internal</option>
                                <option value="EXTERNAL" {{ $category === 'EXTERNAL' ? 'selected' : '' }}>Lab External</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="create_Attachment" class="font-weight-bold">Upload Dokumen / Bukti Pemeriksaan Lab</label>
                            <input type="file" name="Attachment" id="create_Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                            <small class="form-text text-muted">Dokumen dapat berupa PDF atau Foto.</small>
                        </div>
                    </div>
                    <div class="col-md-3 outbound-contract-group" style="display:none;">
                        <div class="form-group">
                            <label for="create_Contract_ID" class="font-weight-bold">Nomor Kontrak</label>
                            <select name="Contract_ID" id="create_Contract_ID" class="form-control">
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
                            <select name="Booking_ID" id="create_Booking_ID" class="form-control">
                                <option value="">-- Pilih Booking Confirmation --</option>
                            </select>
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
                        ['key' => 'MIU',             'label' => '3. MIU (Moisture, Impurities & Unsaponifiables)', 'unit' => '%', 'required' => true],
                        ['key' => 'Sulphur',         'label' => '4. Total Sulfur',      'unit' => 'ppm', 'required' => false],
                        ['key' => 'IV',              'label' => '5. Iodine Value (IV)', 'unit' => 'g I2/100g', 'required' => false],
                        ['key' => 'Total_Chlorides', 'label' => '6. Chlorides',         'unit' => 'ppm', 'required' => false],
                        ['key' => 'Phosphorus',      'label' => '7. Phosphorus',        'unit' => 'ppm', 'required' => false],
                        ['key' => 'Total_Metals',    'label' => '8. Total Metals',      'unit' => 'ppm', 'required' => false],
                    ];
                @endphp

                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle" style="background-color:#fff;">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:36%;text-align:left;vertical-align:middle;">Parameter QC</th>
                                <th style="width:16%;vertical-align:middle;">TOP</th>
                                <th style="width:16%;vertical-align:middle;">MIDDLE</th>
                                <th style="width:16%;vertical-align:middle;">BOTTOM</th>
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
                                <td>
                                    <input type="number" step="0.01" name="top_{{ $param['key'] }}" id="create_top_{{ $param['key'] }}"
                                           class="form-control text-right qc-input" oninput="calcQcAverage('{{ $param['key'] }}', 'create')" />
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="middle_{{ $param['key'] }}" id="create_middle_{{ $param['key'] }}"
                                           class="form-control text-right qc-input" oninput="calcQcAverage('{{ $param['key'] }}', 'create')" />
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="bottom_{{ $param['key'] }}" id="create_bottom_{{ $param['key'] }}"
                                           class="form-control text-right qc-input" oninput="calcQcAverage('{{ $param['key'] }}', 'create')" />
                                </td>
                                <td>
                                    <input type="text" id="create_avg_{{ $param['key'] }}" class="form-control text-right font-weight-bold"
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
</div>

<script>
    var qcParamKeys = [
        'FFA_As_Palmitic', 'FFA_As_Oleic', 'MIU', 'Sulphur',
        'IV', 'Total_Chlorides', 'Phosphorus', 'Total_Metals'
    ];

    function calcQcAverage(paramKey, prefix) {
        prefix = prefix || 'create';
        var topVal = parseFloat($('#' + prefix + '_top_' + paramKey).val());
        var midVal = parseFloat($('#' + prefix + '_middle_' + paramKey).val());
        var botVal = parseFloat($('#' + prefix + '_bottom_' + paramKey).val());

        var vals = [];
        if (!isNaN(topVal)) vals.push(topVal);
        if (!isNaN(midVal)) vals.push(midVal);
        if (!isNaN(botVal)) vals.push(botVal);

        var avgTarget = $('#' + prefix + '_avg_' + paramKey);
        if (vals.length > 0) {
            var sum = vals.reduce(function(a, b) { return a + b; }, 0);
            var avg = sum / vals.length;
            avgTarget.val(avg.toFixed(2));
        } else {
            avgTarget.val('-');
        }
    }

    function recalculateAllAverages(prefix) {
        qcParamKeys.forEach(function(key) {
            calcQcAverage(key, prefix);
        });
    }

    function onQcTypeChange(val) {
        if (val === 'OUTBOUND') {
            $('.outbound-contract-group').show();
        } else {
            $('.outbound-contract-group').hide();
            $('#create_Contract_ID').val('');
            $('#create_Booking_ID').empty().append('<option value="">-- Pilih Booking Confirmation --</option>');
        }
    }

    function cancelCreateQC() {
        $('#FormQualityControl').hide();
        $('#QualityControlForm')[0].reset();
        $('#create_Booking_ID').empty().append('<option value="">-- Pilih Booking Confirmation --</option>');
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
            alert('Tanggal Pemeriksaan wajib diisi');
            return;
        }

        // Validate mandatory parameters for all 3 levels (Top, Middle, Bottom)
        var levels = ['top', 'middle', 'bottom'];
        var mandatoryParams = ['FFA_As_Palmitic', 'FFA_As_Oleic', 'MIU'];

        if (qcType === 'OUTBOUND') {
            mandatoryParams = qcParamKeys;
        }

        for (var i = 0; i < levels.length; i++) {
            var lvl = levels[i];
            for (var j = 0; j < mandatoryParams.length; j++) {
                var pKey = mandatoryParams[j];
                var val = $('#create_' + lvl + '_' + pKey).val();
                if (val === '' || val === null || isNaN(parseFloat(val))) {
                    alert('Parameter ' + pKey.replace(/_/g, ' ') + ' pada level ' + lvl.toUpperCase() + ' wajib diisi.');
                    return;
                }
            }
        }

        var formData = new FormData(form);

        $.ajax({
            url: 'cns-quality-submit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    alert('Data Quality Control berhasil disimpan');
                    cancelCreateQC();
                    loadQualityGrid();
                } else {
                    alert(res.message || 'Gagal menyimpan data Quality Control');
                }
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan data Quality Control';
                alert(msg);
            }
        });
    }
</script>
