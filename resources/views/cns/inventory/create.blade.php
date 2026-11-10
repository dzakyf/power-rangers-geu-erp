@php
    $qcParameters = [
        ['key' => 'FFA_As_Palmitic', 'label' => '1. FFA (As Palmitic)', 'unit' => '%', 'required' => false],
        ['key' => 'FFA_As_Oleic',    'label' => '2. FFA (As Oleic)',    'unit' => '%', 'required' => false],
        ['key' => 'MIU',             'label' => '3. MIU (Moisture, Impurities & Unsaponifiables)', 'unit' => '%', 'required' => false],
        ['key' => 'Sulphur',         'label' => '4. Total Sulfur',      'unit' => 'ppm', 'required' => false],
        ['key' => 'IV',              'label' => '5. Iodine Value (IV)', 'unit' => 'g I2/100g', 'required' => false],
        ['key' => 'Total_Chlorides', 'label' => '6. Chlorides',         'unit' => 'ppm', 'required' => false],
        ['key' => 'Phosphorus',      'label' => '7. Phosphorus',        'unit' => 'ppm', 'required' => false],
        ['key' => 'Total_Metals',    'label' => '8. Total Metals',      'unit' => 'ppm', 'required' => false],
    ];
@endphp
<div id="FormQualityControl" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CREATE UCO STORAGE PELINDO</div>
    <div class="card w-100">
        <div class="card-body">
            <form id="UCOStorageForm" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Type">Type <span style="color:red;">*</span></label>
                            <select name="Type" id="Type" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <option value="inbound">Inbound</option>
                                <option value="outbound">Outbound</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Quantity">Quantity (Kg) <span style="color:red;">*</span></label>
                            <input type="number" step="0.01" name="Quantity" id="Quantity" class="form-control" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Transaction_Date">Transaction Date <span style="color:red;">*</span></label>
                            <input type="date" name="Transaction_Date" id="Transaction_Date" class="form-control" required />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Asal_Warehouse">Asal Warehouse</label>
                            <input type="text" name="Asal_Warehouse" id="Asal_Warehouse" class="form-control" placeholder="Contoh: Site Cisoka, PT. SIMP" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Nama_Tangki">Nama Tangki</label>
                            <select name="Nama_Tangki" id="Nama_Tangki" class="form-control">
                                <option value="">-- Pilih Tangki --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Quantity_PTU">Qty Pelindo / SJ</label>
                            <input type="number" step="0.01" name="Quantity_PTU" id="Quantity_PTU" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Reference_Number">Reference Number</label>
                            <input type="text" name="Reference_Number" id="Reference_Number" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Attachment">Attachment Nota Timbang</label>
                            <input type="file" name="Attachment" id="Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="QC_Attachment">Attachment QC</label>
                            <input type="file" name="QC_Attachment" id="QC_Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="Notes">Notes</label>
                            <textarea name="Notes" id="Notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="font-weight-bold">Quality Control</label>
                            <p class="text-muted small mb-2">Parameter hasil uji laboratorium (TOP, MIDDLE, BOTTOM, dan AVERAGE).</p>

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
                                                <input type="number" step="0.01" name="top_{{ $param['key'] }}" id="create_top_{{ $param['key'] }}"
                                                       class="form-control text-right" oninput="calcInventoryQcAverage('{{ $param['key'] }}', 'create')" />
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="middle_{{ $param['key'] }}" id="create_middle_{{ $param['key'] }}"
                                                       class="form-control text-right" oninput="calcInventoryQcAverage('{{ $param['key'] }}', 'create')" />
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="bottom_{{ $param['key'] }}" id="create_bottom_{{ $param['key'] }}"
                                                       class="form-control text-right" oninput="calcInventoryQcAverage('{{ $param['key'] }}', 'create')" />
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
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-primary" onclick="submitUCOStorage()">Submit</button>
                <button type="button" class="btn btn-secondary ml-2" onclick="$('#FormQualityControl').hide(); $('#UCOStorageForm')[0].reset();">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    var inventoryQcParamKeys = [
        'FFA_As_Palmitic', 'FFA_As_Oleic', 'MIU', 'Sulphur',
        'IV', 'Total_Chlorides', 'Phosphorus', 'Total_Metals'
    ];

    $(document).ready(function() {
        loadCreateTankDropdown();
    });

    function loadCreateTankDropdown() {
        $.get('cns-master-tank-options', function(res) {
            var $sel = $('#Nama_Tangki');
            $sel.empty().append($('<option>', { value: '', text: '-- Pilih Tangki --' }));
            if (res.success && res.tanks && res.tanks.length) {
                $.each(res.tanks, function(_, t) {
                    var label = t.name + (t.capacity > 0 ? ' (' + Number(t.capacity).toLocaleString('id-ID') + ' KG)' : '');
                    $sel.append($('<option>', { value: t.name, text: label }));
                });
            }
        });
    }

    function calcInventoryQcAverage(paramKey, prefix) {
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
            avgTarget.val('');
        }
    }

    function submitUCOStorage() {
        var form = document.getElementById('UCOStorageForm');

        if (!form.Type.value) {
            alert('Type wajib diisi');
            return;
        }
        if (!form.Quantity.value) {
            alert('Quantity wajib diisi');
            return;
        }
        if (!form.Transaction_Date.value) {
            alert('Transaction Date wajib diisi');
            return;
        }

        var formData = new FormData(form);

        $.ajax({
            url: 'cns-inventory-tracking-submit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil disimpan');
                    form.reset();
                    inventoryQcParamKeys.forEach(function(k) {
                        $('#create_avg_' + k).val('');
                    });
                    $('#FormQualityControl').hide();
                    applyFilters();
                    if (typeof loadTankMonitoringCards === 'function') {
                        loadTankMonitoringCards();
                    }
                } else {
                    alert('Gagal menyimpan data: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Gagal menyimpan data');
            }
        });
    }
</script>