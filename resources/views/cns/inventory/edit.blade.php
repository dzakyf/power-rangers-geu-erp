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
<div id="FormEditStock" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EDIT UCO TRACKING</div>
    <div class="card w-100">
        <div class="card-body">
            <form id="StockEditForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="edit_id" />
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Type">Type <span style="color:red;">*</span></label>
                            <select name="Type" id="edit_Type" disabled class="form-control" required>
                                <option value="inbound">Inbound</option>
                                <option value="outbound">Outbound</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Quantity">Quantity (Kg) <span style="color:red;">*</span></label>
                            <input type="number" step="0.01" name="Quantity" id="edit_Quantity" class="form-control" required />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Transaction_Date">Transaction Date <span style="color:red;">*</span></label>
                            <input type="date" name="Transaction_Date" id="edit_Transaction_Date" class="form-control" required />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Asal_Warehouse">Asal Warehouse</label>
                            <input type="text" name="Asal_Warehouse" id="edit_Asal_Warehouse" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Nama_Tangki">Nama Tangki</label>
                            <select name="Nama_Tangki" id="edit_Nama_Tangki" class="form-control">
                                <option value="">-- Pilih Tangki --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Quantity_PTU">Qty Pelindo / SJ</label>
                            <input type="number" step="0.01" name="Quantity_PTU" id="edit_Quantity_PTU" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Reference_Number">Reference Number</label>
                            <input type="text" name="Reference_Number" id="edit_Reference_Number" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_Attachment">Attachment Nota Timbang</label>
                            <input type="file" name="Attachment" id="edit_Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                            <div id="current_notim_link"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_QC_Attachment">QC Attachment</label>
                            <input type="file" name="QC_Attachment" id="edit_QC_Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                            <div id="current_qc_link"></div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="edit_Notes">Notes</label>
                            <textarea name="Notes" id="edit_Notes" class="form-control" rows="2"></textarea>
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
                                                <input type="number" step="0.01" name="top_{{ $param['key'] }}" id="edit_top_{{ $param['key'] }}"
                                                       class="form-control text-right" oninput="calcInventoryQcAverage('{{ $param['key'] }}', 'edit')" />
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="middle_{{ $param['key'] }}" id="edit_middle_{{ $param['key'] }}"
                                                       class="form-control text-right" oninput="calcInventoryQcAverage('{{ $param['key'] }}', 'edit')" />
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="bottom_{{ $param['key'] }}" id="edit_bottom_{{ $param['key'] }}"
                                                       class="form-control text-right" oninput="calcInventoryQcAverage('{{ $param['key'] }}', 'edit')" />
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
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-primary" onclick="submitEditStock()">Update</button>
                <button type="button" class="btn btn-secondary ml-2" onclick="$('#FormEditStock').hide(); $('#StockEditForm')[0].reset();">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    var inventoryQcParamKeys = [
        'FFA_As_Palmitic', 'FFA_As_Oleic', 'MIU', 'Sulphur',
        'IV', 'Total_Chlorides', 'Phosphorus', 'Total_Metals'
    ];

    function calcInventoryQcAverage(paramKey, prefix) {
        prefix = prefix || 'edit';
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

    function prefillEditQC(rowId) {
        $.get('cns-inventory-tracking-data-json', {
            ID: rowId
        }, function(res) {
            var row = res && res[0] ? res[0] : null;
            if (!row) {
                alert('Data tidak ditemukan');
                return;
            }

            document.getElementById('edit_id').value = rowId;
            document.getElementById('edit_Type').value = row.Type ?? 'inbound';
            document.getElementById('edit_Quantity').value = row.Quantity ?? '';
            document.getElementById('edit_Transaction_Date').value = row.Transaction_Date ? row.Transaction_Date.substring(0, 10) : '';
            document.getElementById('edit_Asal_Warehouse').value = row.Asal_Warehouse ?? '';
            populateEditTankDropdown(row.Nama_Tangki);
            document.getElementById('edit_Quantity_PTU').value = row.Quantity_PTU ?? '';
            document.getElementById('edit_Reference_Number').value = row.Reference_Number ?? '';
            document.getElementById('edit_Notes').value = row.Notes ?? '';

            var notimHtml = row.Notim_Path ? `<a href="filevendor/${row.Notim_Path}" target="_blank" class="small text-danger d-block mt-1"><i class="fa fa-file-pdf"></i> Lihat Nota Timbang saat ini</a>` : '';
            $('#current_notim_link').html(notimHtml);

            var qcHtml = row.QC_Path ? `<a href="filevendor/${row.QC_Path}" target="_blank" class="small text-primary d-block mt-1"><i class="fa fa-file-pdf"></i> Lihat QC Attachment saat ini</a>` : '';
            $('#current_qc_link').html(qcHtml);

            var qc = row.qc || {};
            ['top', 'middle', 'bottom'].forEach(function(type) {
                var item = qc[type] || {};
                inventoryQcParamKeys.forEach(function(key) {
                    var el = document.getElementById('edit_' + type + '_' + key);
                    if (el) {
                        var val = item[key] ?? '';
                        // Legacy fallback for MIU if only MC/IMP exists
                        if (key === 'MIU' && (val === '' || val === null) && (item.MC !== undefined || item.IMP !== undefined)) {
                            var mc = parseFloat(item.MC) || 0;
                            var imp = parseFloat(item.IMP) || 0;
                            val = (mc + imp > 0) ? (mc + imp).toFixed(2) : '';
                        }
                        el.value = val;
                    }
                });
            });

            inventoryQcParamKeys.forEach(function(key) {
                calcInventoryQcAverage(key, 'edit');
            });

            $('#FormEditStock').show();
        }).fail(function() {
            alert('Gagal memuat data');
        });
    }

    function submitEditStock() {
        var form = document.getElementById('StockEditForm');

        if (!form.Transaction_Date.value) {
            alert('Transaction Date wajib diisi');
            return;
        }

        var formData = new FormData(form);

        $.ajax({
            url: 'cns-inventory-tracking-submit-edit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil diupdate');
                    form.reset();
                    $('#FormEditStock').hide();
                    applyFilters();
                    if (typeof loadTankMonitoringCards === 'function') {
                        loadTankMonitoringCards();
                    }
                } else {
                    alert('Gagal mengupdate data: ' + (res.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Gagal mengupdate data');
            }
        });
    }

    function populateEditTankDropdown(selectedVal) {
        $.get('cns-master-tank-options', function(res) {
            var $sel = $('#edit_Nama_Tangki');
            $sel.empty().append($('<option>', { value: '', text: '-- Pilih Tangki --' }));
            var found = false;
            if (res.success && res.tanks && res.tanks.length) {
                $.each(res.tanks, function(_, t) {
                    var label = t.name + (t.capacity > 0 ? ' (' + Number(t.capacity).toLocaleString('id-ID') + ' KG)' : '');
                    var isSelected = (selectedVal && selectedVal === t.name);
                    if (isSelected) found = true;
                    $sel.append($('<option>', { value: t.name, text: label, selected: isSelected }));
                });
            }
            if (selectedVal && !found) {
                $sel.append($('<option>', { value: selectedVal, text: selectedVal + ' (Legacy)', selected: true }));
            }
        });
    }
</script>