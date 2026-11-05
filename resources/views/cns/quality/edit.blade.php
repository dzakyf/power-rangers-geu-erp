@php
    $contracts = $contracts ?? collect();
    $category = $category ?? 'EXTERNAL';
@endphp
<div id="FormEditQualityControl" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EDIT QUALITY CONTROL</div>
    <div class="card w-100 shadow-sm">
        <div class="card-body">
            <form id="QualityControlEditForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="edit_Kode_QC" class="font-weight-bold">Kode QC</label>
                            <input type="text" name="Kode_QC" id="edit_Kode_QC" class="form-control" readonly />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="edit_Created_At" class="font-weight-bold">Tanggal Pemeriksaan <span style="color:red;">*</span></label>
                            <input type="date" name="Created_At" id="edit_Created_At" class="form-control" required readonly />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="edit_QC_Type" class="font-weight-bold">Jenis Quality Control</label>
                            <input type="text" name="QC_Type" id="edit_QC_Type" class="form-control" readonly />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="edit_QC_Category" class="font-weight-bold">Pelaksana Lab</label>
                            <input type="text" name="QC_Category" id="edit_QC_Category" class="form-control" readonly />
                        </div>
                    </div>
                </div>

                <div class="row mb-3 edit-outbound-contract-group" style="display:none;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_Contract_ID" class="font-weight-bold">Nomor Kontrak</label>
                            <select name="Contract_ID" id="edit_Contract_ID" class="form-control">
                                <option value="">-- Pilih Kontrak --</option>
                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->ID }}">{{ $contract->Contract_No }}{{ !empty($contract->Buyer_Name) ? ' - ' . $contract->Buyer_Name : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="edit_Booking_ID" class="font-weight-bold">Nomor Booking Confirmation</label>
                            <select name="Booking_ID" id="edit_Booking_ID" class="form-control">
                                <option value="">-- Pilih Booking Confirmation --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-3 mb-2">
                    <h5 class="font-weight-bold text-dark mb-1">Hasil Pemeriksaan Lab</h5>
                    <p class="text-muted small mb-2">Edit nilai parameter hasil pemeriksaan lab.</p>
                </div>

                @php
                    $qcParameters = [
                        ['key' => 'FFA_As_Palmitic', 'label' => '1. FFA (As Palmitic)', 'unit' => '%'],
                        ['key' => 'FFA_As_Oleic',    'label' => '2. FFA (As Oleic)',    'unit' => '%'],
                        ['key' => 'MIU',             'label' => '3. MIU (Moisture, Impurities & Unsaponifiables)', 'unit' => '%'],
                        ['key' => 'Sulphur',         'label' => '4. Total Sulfur',      'unit' => 'ppm'],
                        ['key' => 'IV',              'label' => '5. Iodine Value (IV)', 'unit' => 'g I2/100g'],
                        ['key' => 'Total_Chlorides', 'label' => '6. Chlorides',         'unit' => 'ppm'],
                        ['key' => 'Phosphorus',      'label' => '7. Phosphorus',        'unit' => 'ppm'],
                        ['key' => 'Total_Metals',    'label' => '8. Total Metals',      'unit' => 'ppm'],
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
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="top_{{ $param['key'] }}" id="edit_top_{{ $param['key'] }}"
                                           class="form-control text-right" oninput="calcQcAverage('{{ $param['key'] }}', 'edit')" />
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="middle_{{ $param['key'] }}" id="edit_middle_{{ $param['key'] }}"
                                           class="form-control text-right" oninput="calcQcAverage('{{ $param['key'] }}', 'edit')" />
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="bottom_{{ $param['key'] }}" id="edit_bottom_{{ $param['key'] }}"
                                           class="form-control text-right" oninput="calcQcAverage('{{ $param['key'] }}', 'edit')" />
                                </td>
                                <td>
                                    <input type="text" id="edit_avg_{{ $param['key'] }}" class="form-control text-right font-weight-bold"
                                           readonly style="background-color:#f0f8ff;" placeholder="-" />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-primary px-4 mr-2" onclick="submitEditQC()">Update</button>
                    <button type="button" class="btn btn-secondary px-4" onclick="cancelEditQC()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#edit_Contract_ID').on('change', function() {
            var contractId = $(this).val();
            if (typeof loadQualityBookings === 'function') {
                loadQualityBookings(contractId, '#edit_Booking_ID', null);
            }
        });
    });

    function cleanCell(val) {
        if (!val || val === '-' || val === 'null' || val === 'undefined') return '';
        return String(val).replace('%', '').trim();
    }

    function cancelEditQC() {
        $('#FormEditQualityControl').hide();
        $('#QualityControlEditForm')[0].reset();
        $('#edit_Booking_ID').empty().append('<option value="">-- Pilih Booking Confirmation --</option>');
        qcParamKeys.forEach(function(key) {
            $('#edit_avg_' + key).val('-');
        });
    }

    function prefillEditQC(rowId) {
        var grid = ProjectionGrid;

        var kodeQc = grid.cells(rowId, 1).getValue() || '';
        document.getElementById('edit_Kode_QC').value = (kodeQc === '-' ? '' : kodeQc);

        var tanggal = grid.cells(rowId, 2).getValue();
        var date = new Date(tanggal);
        var isoDate = date.getFullYear() + '-' +
            String(date.getMonth() + 1).padStart(2, '0') + '-' +
            String(date.getDate()).padStart(2, '0');

        document.getElementById('edit_Created_At').value = isoDate;

        var qcType = grid.cells(rowId, 3).getValue() || 'INBOUND';
        document.getElementById('edit_QC_Type').value = qcType;

        var qcCat = grid.cells(rowId, 4).getValue() || 'EXTERNAL';
        document.getElementById('edit_QC_Category').value = qcCat;

        if (qcType === 'OUTBOUND') {
            $('.edit-outbound-contract-group').show();
        } else {
            $('.edit-outbound-contract-group').hide();
        }

        var contractId = '';
        var bookingId = '';
        try {
            contractId = grid.cells(rowId, 38) ? grid.cells(rowId, 38).getValue() : '';
            bookingId  = grid.cells(rowId, 39) ? grid.cells(rowId, 39).getValue() : '';
        } catch (e) {}

        $('#edit_Contract_ID').val(contractId);
        if (contractId && typeof loadQualityBookings === 'function') {
            loadQualityBookings(contractId, '#edit_Booking_ID', bookingId);
        } else {
            $('#edit_Booking_ID').empty().append('<option value="">-- Pilih Booking Confirmation --</option>');
        }

        // TOP indices: 5..12, MIDDLE: 13..20, BOTTOM: 21..28
        // Keys: FFA_As_Palmitic(5), FFA_As_Oleic(6), MIU(7), Sulphur(8), IV(9), Total_Chlorides(10), Phosphorus(11), Total_Metals(12)
        qcParamKeys.forEach(function(key, idx) {
            var topVal = cleanCell(grid.cells(rowId, 5 + idx).getValue());
            var midVal = cleanCell(grid.cells(rowId, 13 + idx).getValue());
            var botVal = cleanCell(grid.cells(rowId, 21 + idx).getValue());

            $('#edit_top_' + key).val(topVal);
            $('#edit_middle_' + key).val(midVal);
            $('#edit_bottom_' + key).val(botVal);

            calcQcAverage(key, 'edit');
        });

        $('#FormEditQualityControl').show();
    }

    function submitEditQC() {
        var form = document.getElementById('QualityControlEditForm');
        var createdAt = form.Created_At.value;
        var qcType = form.QC_Type.value || 'INBOUND';
        var qcCategory = form.QC_Category.value || 'EXTERNAL';
        var kodeQc = form.Kode_QC ? form.Kode_QC.value : '';

        if (!createdAt) {
            alert('Tanggal Pemeriksaan wajib diisi');
            return;
        }

        var formData = new FormData(form);

        $.ajax({
            url: 'cns-quality-submit-edit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    alert('Data Quality Control berhasil diperbarui');
                    cancelEditQC();
                    loadQualityGrid();
                } else {
                    alert(res.message || 'Gagal mengupdate data Quality Control');
                }
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengupdate data Quality Control';
                alert(msg);
            }
        });
    }
</script>
