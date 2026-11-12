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
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CREATE INBOUND MOVEMENT</div>
    <div class="card w-100">
        <div class="card-body">
            <form id="UCOStorageForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="MC" id="create_MC" />
                <input type="hidden" name="IMP" id="create_IMP" />

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="movement_kode">Nomor Movement <span style="color:red;">*</span></label>
                            <select name="movement_kode" id="movement_kode" class="form-control" style="width:100%;" required>
                                <option value="">-- Pilih Nomor Movement --</option>
                            </select>
                            <small id="movementDetailLoading" class="text-muted" style="display:none;">Memuat detail movement...</small>
                        </div>
                    </div>
                </div>

                <div id="movementFieldsWrapper" style="display:none;">
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
                                <input type="text" inputmode="decimal" name="Quantity" id="Quantity" class="form-control cns-decimal" required />
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
                                <select name="Asal_Warehouse" id="Asal_Warehouse" class="form-control">
                                    <option value="">-- Pilih Warehouse --</option>
                                </select>
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
                                <input type="text" inputmode="decimal" name="Quantity_PTU" id="Quantity_PTU" class="form-control cns-decimal" />
                            </div>
                        </div>
                    </div>
                    <div id="create_tank_capacity_info" class="mb-3" style="display:none;"></div>
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
                                                    <input type="text" inputmode="decimal" name="top_{{ $param['key'] }}" id="create_top_{{ $param['key'] }}"
                                                           class="form-control text-right cns-decimal" oninput="onCreateQcInput('{{ $param['key'] }}')" />
                                                </td>
                                                <td>
                                                    <input type="text" inputmode="decimal" name="middle_{{ $param['key'] }}" id="create_middle_{{ $param['key'] }}"
                                                           class="form-control text-right cns-decimal" oninput="onCreateQcInput('{{ $param['key'] }}')" />
                                                </td>
                                                <td>
                                                    <input type="text" inputmode="decimal" name="bottom_{{ $param['key'] }}" id="create_bottom_{{ $param['key'] }}"
                                                           class="form-control text-right cns-decimal" oninput="onCreateQcInput('{{ $param['key'] }}')" />
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
                </div>

                <button type="button" id="btnSubmitUCO" class="btn btn-primary" onclick="submitUCOStorage()" disabled>Submit</button>
                <button type="button" class="btn btn-secondary ml-2" onclick="closeCreateUCO()">Cancel</button>
            </form>
        </div>
    </div>
</div>

@include('cns.inventory.partials.tank-capacity-helper')
<script>
    var createInventoryQcParamKeys = [
        'FFA_As_Palmitic', 'FFA_As_Oleic', 'MIU', 'Sulphur',
        'IV', 'Total_Chlorides', 'Phosphorus', 'Total_Metals'
    ];

    if (typeof window.populateCnsWarehouseSelect !== 'function') {
        window.populateCnsWarehouseSelect = function($sel, selectedVal) {
            if (!$sel || !$sel.length) return;
            if (!window._cnsWarehousesReq) {
                window._cnsWarehousesReq = $.get('cns-master-warehouses').fail(function() {
                    window._cnsWarehousesReq = null;
                });
            }
            window._cnsWarehousesReq.done(function(res) {
                $sel.empty().append($('<option>', { value: '', text: '-- Pilih Warehouse --' }));
                var found = false;
                $.each((res && res.data) || [], function(_, w) {
                    var name = (w && w.name) ? String(w.name) : '';
                    if (!name) return;
                    var isSelected = (selectedVal && selectedVal === name);
                    if (isSelected) found = true;
                    $sel.append($('<option>', { value: name, text: name, selected: isSelected }));
                });
                if (selectedVal && !found) {
                    $sel.append($('<option>', { value: selectedVal, text: selectedVal, selected: true }));
                }
                if (typeof $.fn.select2 === 'function') {
                    if ($sel.hasClass('select2-hidden-accessible')) {
                        $sel.select2('destroy');
                    }
                    $sel.select2({
                        placeholder: '-- Pilih / Ketik Warehouse --',
                        allowClear: true,
                        tags: true,
                        width: '100%'
                    });
                }
                $sel.val(selectedVal || '').trigger('change');
            });
        };
    }

    if (typeof window.calcInventoryQcAverage !== 'function') {
        window.calcInventoryQcAverage = function(paramKey, prefix) {
            prefix = prefix || 'create';
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
                avgTarget.val(cnsFormatNumber(sum / vals.length, 2));
            } else {
                avgTarget.val('');
            }
        };
    }

    function onCreateQcInput(paramKey) {
        if (paramKey === 'MIU') {
            $('#create_MC').val('');
            $('#create_IMP').val('');
        }
        window.calcInventoryQcAverage(paramKey, 'create');
    }

    function getCreateTankCapacityParams() {
        return {
            tankName: $('#Nama_Tangki').val(),
            type: $('#Type').val() || 'inbound',
            qty: cnsParseNumber($('#Quantity').val(), 0)
        };
    }

    function updateCreateTankCapacityPreview() {
        return window.cnsRenderTankCapacityPreview($('#create_tank_capacity_info'), getCreateTankCapacityParams());
    }

    function loadCreateTankDropdown(selectedTank) {
        return window.cnsLoadTankBalanceDropdown($('#Nama_Tangki'), selectedTank, {
            onLoaded: updateCreateTankCapacityPreview
        });
    }

    $(document).ready(function() {
        loadCreateTankDropdown();
        $('#Nama_Tangki, #Type').on('change', updateCreateTankCapacityPreview);
        $('#Quantity').on('input change', updateCreateTankCapacityPreview);

        $('#movement_kode').on('change', function() {
            var kode = ($(this).val() || '').trim();
            if (!kode) {
                resetMovementFields();
                return;
            }
            var movId = $(this).find('option:selected').data('id');
            if (!movId) {
                resetMovementFields();
                return;
            }

            $('#movementDetailLoading').show();
            $('#btnSubmitUCO').prop('disabled', true);

            $.get('cns-inventory-tracking-movement-detail', { id: movId })
                .done(function(res) {
                    if (!res || !res.success || !res.data) {
                        resetMovementFields();
                        alert((res && res.message) ? res.message : 'Gagal memuat detail movement dari APIPI');
                        return;
                    }
                    prefillCreateFromMovement(res.data);
                })
                .fail(function(xhr) {
                    resetMovementFields();
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghubungi APIPI detail movement';
                    alert(msg);
                })
                .always(function() {
                    $('#movementDetailLoading').hide();
                });
        });
    });

    function initMovementSelect2() {
        var $sel = $('#movement_kode');
        if (typeof $.fn.select2 === 'function') {
            if ($sel.hasClass('select2-hidden-accessible')) {
                $sel.select2('destroy');
            }
            $sel.select2({
                placeholder: '-- Pilih Nomor Movement --',
                allowClear: true,
                width: '100%'
            });
        }
    }

    function resetCreateQcFields() {
        $('#create_MC').val('');
        $('#create_IMP').val('');
        createInventoryQcParamKeys.forEach(function(k) {
            $('#create_top_' + k).val('');
            $('#create_middle_' + k).val('');
            $('#create_bottom_' + k).val('');
            $('#create_avg_' + k).val('');
        });
    }

    function resetMovementFields() {
        $('#movementFieldsWrapper').hide();
        $('#btnSubmitUCO').prop('disabled', true);
        $('#Type').val('');
        $('#Quantity').val('');
        $('#Transaction_Date').val('');
        $('#Nama_Tangki').val('');
        $('#create_tank_capacity_info').hide().empty();
        $('#Quantity_PTU').val('');
        $('#Reference_Number').val('');
        $('#Attachment').val('');
        $('#QC_Attachment').val('');
        $('#Notes').val('');
        if ($('#Asal_Warehouse').hasClass('select2-hidden-accessible')) {
            $('#Asal_Warehouse').val('').trigger('change');
        } else {
            $('#Asal_Warehouse').val('');
        }
        resetCreateQcFields();
    }

    function prefillCreateFromMovement(d) {
        $('#Type').val(d.Type || 'inbound');
        $('#Quantity').val(d.Quantity !== null && d.Quantity !== undefined && d.Quantity !== '' ? cnsFormatNumber(d.Quantity, 2) : '');
        $('#Transaction_Date').val(d.Transaction_Date || '');
        $('#Reference_Number').val(d.Reference_Number || d.kode || '');
        $('#Quantity_PTU').val('');
        $('#Attachment').val('');
        $('#QC_Attachment').val('');
        $('#Notes').val('');

        window.populateCnsWarehouseSelect($('#Asal_Warehouse'), d.Asal_Warehouse || '');
        loadCreateTankDropdown(d.Nama_Tangki || '');

        resetCreateQcFields();
        if (d.FFA !== null && d.FFA !== undefined && d.FFA !== '') {
            $('#create_top_FFA_As_Palmitic').val(cnsFormatNumber(d.FFA, 2));
            window.calcInventoryQcAverage('FFA_As_Palmitic', 'create');
        }
        if (d.MIU !== null && d.MIU !== undefined && d.MIU !== '') {
            $('#create_top_MIU').val(cnsFormatNumber(d.MIU, 2));
            window.calcInventoryQcAverage('MIU', 'create');
        }
        $('#create_MC').val(d.MC !== null && d.MC !== undefined ? d.MC : '');
        $('#create_IMP').val(d.IMP !== null && d.IMP !== undefined ? d.IMP : '');

        $('#movementFieldsWrapper').show();
        $('#btnSubmitUCO').prop('disabled', false);
        updateCreateTankCapacityPreview();
    }

    window.loadInboundMovementOptions = function() {
        var $sel = $('#movement_kode');
        resetMovementFields();
        $sel.empty().append($('<option>', { value: '', text: 'Memuat data movement...' }));
        initMovementSelect2();

        $.get('cns-inventory-tracking-movements')
            .done(function(res) {
                $sel.empty().append($('<option>', { value: '', text: '-- Pilih Nomor Movement --' }));
                if (!res || !res.success) {
                    initMovementSelect2();
                    alert((res && res.message) ? res.message : 'Gagal memuat data movement dari APIPI');
                    return;
                }
                $.each(res.data || [], function(_, m) {
                    if (!m || !m.kode) return;
                    var qtyNum = Number(m.total_qty || 0);
                    var qtyFmt = cnsFormatNumber(qtyNum, 2);
                    var meta = [m.tgl || '-', m.dari_gudang || '-', qtyFmt + ' Kg'].join(' | ');
                    var label = m.kode + ' (' + meta + ')';
                    $sel.append($('<option>', {
                        value: m.kode,
                        text: label,
                        'data-id': m.id || ''
                    }));
                });
                initMovementSelect2();
            })
            .fail(function(xhr) {
                $sel.empty().append($('<option>', { value: '', text: '-- Pilih Nomor Movement --' }));
                initMovementSelect2();
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghubungi APIPI movements';
                alert(msg);
            });
    };

    function closeCreateUCO() {
        $('#FormQualityControl').hide();
        $('#UCOStorageForm')[0].reset();
        $('#movement_kode').val('').trigger('change');
        resetMovementFields();
    }

    function submitUCOStorage() {
        var form = document.getElementById('UCOStorageForm');

        if (!$('#movement_kode').val()) {
            alert('Nomor Movement wajib dipilih');
            return;
        }
        if (!form.Type.value) {
            alert('Type wajib diisi');
            return;
        }
        if (form.Quantity.value === '') {
            alert('Quantity wajib diisi');
            return;
        }
        if (!form.Transaction_Date.value) {
            alert('Transaction Date wajib diisi');
            return;
        }

        var capacityCheck = window.cnsEvaluateTankCapacity(getCreateTankCapacityParams());
        if (capacityCheck && capacityCheck.isWarning && !confirm(capacityCheck.confirmMessage)) {
            return;
        }

        var formData = new FormData(form);
        $(form).find('input.cns-decimal').each(function() {
            var name = $(this).attr('name');
            if (name && $.trim($(this).val()) !== '') {
                formData.set(name, cnsParseNumber($(this).val(), 0));
            }
        });
        var $btn = $('#btnSubmitUCO');
        $btn.prop('disabled', true);

        $.ajax({
            url: 'cns-inventory-tracking-submit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res && res.success) {
                    alert('Data berhasil disimpan');
                    closeCreateUCO();
                    applyFilters();
                    if (typeof loadTankMonitoringCards === 'function') {
                        loadTankMonitoringCards();
                    }
                } else {
                    alert('Gagal menyimpan data: ' + ((res && res.message) ? res.message : 'Unknown error'));
                }
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menyimpan data';
                alert(msg);
            },
            complete: function() {
                if ($('#movement_kode').val()) {
                    $btn.prop('disabled', false);
                }
            }
        });
    }
</script>
