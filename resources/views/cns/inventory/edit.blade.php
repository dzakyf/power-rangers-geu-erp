@php
    $qcParameters = [
        ['key' => 'FFA_As_Palmitic', 'label' => '1. FFA (As Palmitic)', 'unit' => '%', 'required' => false],
        ['key' => 'FFA_As_Oleic',    'label' => '2. FFA (As Oleic)',    'unit' => '%', 'required' => false],
        ['key' => 'Moisture',        'label' => '3. Moisture',          'unit' => '%', 'required' => false],
        ['key' => 'Impurities',      'label' => '4. Impurities',        'unit' => '%', 'required' => false],
        ['key' => 'Unsaponifiables', 'label' => '5. Unsaponifiables',   'unit' => '%', 'required' => false],
        ['key' => 'Sulphur',         'label' => '6. Total Sulfur',      'unit' => 'ppm', 'required' => false],
        ['key' => 'IV',              'label' => '7. Iodine Value (IV)', 'unit' => 'g I2/100g', 'required' => false],
        ['key' => 'Total_Chlorides', 'label' => '8. Chlorides',         'unit' => 'ppm', 'required' => false],
        ['key' => 'Phosphorus',      'label' => '9. Phosphorus',        'unit' => 'ppm', 'required' => false],
        ['key' => 'Total_Metals',    'label' => '10. Total Metals',     'unit' => 'ppm', 'required' => false],
    ];
@endphp
<div id="FormEditStock" class="row m-1" style="display:none;">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">EDIT UCO TRACKING</div>
    <div class="card w-100">
        <div class="card-body">
            <form id="StockEditForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="edit_id" />
                <div id="edit_approval_banner" class="mb-3" style="display:none;"></div>
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
                            <input type="text" inputmode="decimal" name="Quantity" id="edit_Quantity" class="form-control cns-decimal" required />
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
                            <select name="Asal_Warehouse" id="edit_Asal_Warehouse" class="form-control">
                                <option value="">-- Pilih Warehouse --</option>
                            </select>
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
                            <input type="text" inputmode="decimal" name="Quantity_PTU" id="edit_Quantity_PTU" class="form-control cns-decimal" />
                        </div>
                    </div>
                </div>
                <div id="edit_inbound_adjustment_alert" class="mb-3" style="display:none;"></div>
                <div id="edit_tank_capacity_info" class="mb-3" style="display:none;"></div>
                <div id="edit_tank_overflow_container" class="mb-3" style="display:none;"></div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_fleet_name">Fleet</label>
                            <input type="text" id="edit_fleet_name" class="form-control" readonly />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_fleet_plat">No. Polisi (Fleet Plat)</label>
                            <input type="text" id="edit_fleet_plat" class="form-control" readonly />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="edit_driver_name">Driver</label>
                            <input type="text" id="edit_driver_name" class="form-control" readonly />
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="edit_Reference_Number">Reference Number</label>
                            <input type="text" name="Reference_Number" id="edit_Reference_Number" class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="edit_Attachment">Attachment Nota Timbang</label>
                            <input type="file" name="Attachment" id="edit_Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                            <div id="current_notim_link"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="edit_BA_Attachment">Attachment BA <span style="color:red;">*</span></label>
                            <input type="file" name="BA_Attachment" id="edit_BA_Attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required />
                            <div id="current_ba_link"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
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
                    <div class="col-lg-4 mb-3">
                        @include('cns.inventory.partials.movement-qc-table', ['prefix' => 'edit'])
                    </div>
                    <div class="col-lg-8 mb-3">
                        <div class="form-group">
                            <label class="font-weight-bold">QC Inbound</label>
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
                                                <input type="text" inputmode="decimal" name="top_{{ $param['key'] }}" id="edit_top_{{ $param['key'] }}"
                                                       class="form-control text-right cns-decimal" oninput="calcInventoryQcAverage('{{ $param['key'] }}', 'edit')" />
                                            </td>
                                            <td>
                                                <input type="text" inputmode="decimal" name="middle_{{ $param['key'] }}" id="edit_middle_{{ $param['key'] }}"
                                                       class="form-control text-right cns-decimal" oninput="calcInventoryQcAverage('{{ $param['key'] }}', 'edit')" />
                                            </td>
                                            <td>
                                                <input type="text" inputmode="decimal" name="bottom_{{ $param['key'] }}" id="edit_bottom_{{ $param['key'] }}"
                                                       class="form-control text-right cns-decimal" oninput="calcInventoryQcAverage('{{ $param['key'] }}', 'edit')" />
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

                <button type="button" id="btnUpdateEditStock" class="btn btn-primary" onclick="submitEditStock()">Update</button>
                <button type="button" class="btn btn-secondary ml-2" onclick="closeEditStock()">Cancel</button>
            </form>
        </div>
    </div>
</div>

@include('cns.inventory.partials.tank-capacity-helper')
<script>
    var editOriginalTankState = { tank: '', type: 'inbound', qty: 0 };
    var inventoryQcParamKeys = [
        'FFA_As_Palmitic', 'FFA_As_Oleic', 'Moisture', 'Impurities', 'Unsaponifiables', 'Sulphur',
        'IV', 'Total_Chlorides', 'Phosphorus', 'Total_Metals'
    ];

    function calcInventoryQcAverage(paramKey, prefix) {
        prefix = prefix || 'edit';
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
            avgTarget.val('');
        }
    }

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
            renderEditApprovalBanner(row);
            document.getElementById('edit_Type').value = row.Type ?? 'inbound';
            document.getElementById('edit_Quantity').value = (row.Quantity !== null && row.Quantity !== undefined && row.Quantity !== '') ? cnsFormatNumber(row.Quantity, 2) : '';
            editOriginalTankState = {
                tank: row.Nama_Tangki ?? '',
                type: row.Type ?? 'inbound',
                qty: cnsParseNumber(row.Quantity, 0)
            };
            document.getElementById('edit_Transaction_Date').value = row.Transaction_Date ? row.Transaction_Date.substring(0, 10) : '';
            if (typeof window.populateCnsWarehouseSelect === 'function') {
                window.populateCnsWarehouseSelect($('#edit_Asal_Warehouse'), row.Asal_Warehouse ?? '');
            } else {
                document.getElementById('edit_Asal_Warehouse').value = row.Asal_Warehouse ?? '';
            }
            populateEditTankDropdown(row.Nama_Tangki);
            document.getElementById('edit_Quantity_PTU').value = (row.Quantity_PTU !== null && row.Quantity_PTU !== undefined && row.Quantity_PTU !== '') ? cnsFormatNumber(row.Quantity_PTU, 2) : '';
            document.getElementById('edit_fleet_name').value = row.fleet_name ?? '';
            document.getElementById('edit_fleet_plat').value = row.fleet_plat ?? '';
            document.getElementById('edit_driver_name').value = row.driver_name ?? '';
            document.getElementById('edit_Reference_Number').value = row.Reference_Number ?? '';
            document.getElementById('edit_Notes').value = row.Notes ?? '';

            if (row.Reference_Number) {
                $.get('cns-inbound-adjustment-active/' + encodeURIComponent(row.Reference_Number), function(adjRes) {
                    if (adjRes && adjRes.success && adjRes.has_adjustment && adjRes.adjustment) {
                        var adj = adjRes.adjustment;
                        $('#edit_inbound_adjustment_alert').html(
                            '<div class="alert alert-success py-2 px-3 mb-0">' +
                                '<i class="fa fa-check-circle mr-1"></i> <strong>Kuantiti disesuaikan dari Inbound Adjustment (' + adj.adjustment_code + ')</strong><br>' +
                                'Kuantiti Asli Movement: <strong>' + cnsFormatNumber(adj.original_qty, 2) + ' KG</strong> &rarr; ' +
                                'Kuantiti Disesuaikan: <strong>' + cnsFormatNumber(adj.adjusted_qty, 2) + ' KG</strong> ' +
                                '(Selisih Variansi: <strong>' + cnsFormatNumber(adj.selisih_qty, 2) + ' KG</strong> pada tangki <strong>' + adj.tank_name + '</strong>).' +
                            '</div>'
                        ).show();
                    } else {
                        $('#edit_inbound_adjustment_alert').hide().empty();
                    }
                });
            } else {
                $('#edit_inbound_adjustment_alert').hide().empty();
            }

            var notimHtml = row.Notim_Path ? `<a href="filevendor/${row.Notim_Path}" target="_blank" class="small text-danger d-block mt-1"><i class="fa fa-file-pdf"></i> Lihat Nota Timbang saat ini</a>` : '';
            $('#current_notim_link').html(notimHtml);

            $('#edit_BA_Attachment').val('');
            var baHtml = row.BA_Path ? `<a href="filevendor/${row.BA_Path}" target="_blank" class="small text-success d-block mt-1"><i class="fa fa-file-pdf"></i> Lihat Attachment BA saat ini</a>` : '';
            $('#current_ba_link').html(baHtml);

            var qcHtml = row.QC_Path ? `<a href="filevendor/${row.QC_Path}" target="_blank" class="small text-primary d-block mt-1"><i class="fa fa-file-pdf"></i> Lihat QC Attachment saat ini</a>` : '';
            $('#current_qc_link').html(qcHtml);

            $('#edit_mov_ffa').val(row.Movement_FFA !== null && row.Movement_FFA !== undefined && row.Movement_FFA !== '' ? cnsFormatNumber(row.Movement_FFA, 2) : '');
            $('#edit_mov_mc').val(row.Movement_MC !== null && row.Movement_MC !== undefined && row.Movement_MC !== '' ? cnsFormatNumber(row.Movement_MC, 2) : '');
            $('#edit_mov_imp').val(row.Movement_IMP !== null && row.Movement_IMP !== undefined && row.Movement_IMP !== '' ? cnsFormatNumber(row.Movement_IMP, 2) : '');

            var qc = row.qc || {};
            ['top', 'middle', 'bottom'].forEach(function(type) {
                var item = qc[type] || {};
                inventoryQcParamKeys.forEach(function(key) {
                    var el = document.getElementById('edit_' + type + '_' + key);
                    if (el) {
                        var val = item[key] ?? '';
                        if (key === 'Moisture' && (val === '' || val === null) && item.MC !== undefined) {
                            val = item.MC;
                        }
                        if (key === 'Impurities' && (val === '' || val === null) && item.IMP !== undefined) {
                            val = item.IMP;
                        }
                        el.value = (val !== '' && val !== null && val !== undefined) ? cnsFormatNumber(val, 2) : '';
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

    function renderEditApprovalBanner(row) {
        var status = row.Approval_Status ? String(row.Approval_Status).toUpperCase() : 'APPROVED';
        var $banner = $('#edit_approval_banner');
        var $btn = $('#btnUpdateEditStock');

        if (status === 'APPROVED') {
            var info = row.Approved_By ? (' oleh ' + $('<div>').text(row.Approved_By).html()) : '';
            $banner.html('<div class="alert alert-success py-2 mb-0"><strong>Status: APPROVED</strong>' + info + ' — Data sudah terkunci dan tidak dapat diubah.</div>').show();
            $btn.prop('disabled', true);
        } else if (status === 'REJECTED') {
            var reason = row.Rejection_Reason ? $('<div>').text(row.Rejection_Reason).html() : '-';
            $banner.html('<div class="alert alert-danger py-2 mb-0"><strong>Status: REJECTED</strong> — Alasan: ' + reason + '. Simpan perubahan untuk mengajukan ulang (PENDING).</div>').show();
            $btn.prop('disabled', false);
        } else {
            $banner.html('<div class="alert alert-warning py-2 mb-0"><strong>Status: PENDING</strong> — Menunggu approval.</div>').show();
            $btn.prop('disabled', false);
        }
    }

    function submitEditStock() {
        var form = document.getElementById('StockEditForm');

        if (!form.Transaction_Date.value) {
            alert('Transaction Date wajib diisi');
            return;
        }
        if (!form.BA_Attachment || !form.BA_Attachment.files || form.BA_Attachment.files.length === 0) {
            alert('Attachment BA wajib diupload');
            return;
        }

        var overflowResult = window.cnsValidateAndCollectOverflow(
            $('#edit_tank_overflow_container'),
            getEditTankCapacityParams()
        );
        if (!overflowResult.ok) {
            alert(overflowResult.message);
            return;
        }

        var capacityCheck = window.cnsEvaluateTankCapacity(getEditTankCapacityParams());
        if (capacityCheck && capacityCheck.isNegativeStock && !confirm(capacityCheck.confirmMessage)) {
            return;
        }

        var formData = new FormData(form);
        if (overflowResult.allocations && overflowResult.allocations.length > 0) {
            formData.set('overflow_allocations', JSON.stringify(overflowResult.allocations));
        }
        $(form).find('input.cns-decimal').each(function() {
            var name = $(this).attr('name');
            if (name && $.trim($(this).val()) !== '') {
                formData.set(name, cnsParseNumber($(this).val(), 0));
            }
        });

        $.ajax({
            url: 'cns-inventory-tracking-submit-edit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil diupdate');
                    closeEditStock();
                    applyFilters();
                    if (typeof loadTankMonitoringCards === 'function') {
                        loadTankMonitoringCards();
                    }
                } else {
                    alert('Gagal mengupdate data: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal mengupdate data';
                alert(msg);
            }
        });
    }

    function getEditTankCapacityParams() {
        return {
            tankName: $('#edit_Nama_Tangki').val(),
            type: $('#edit_Type').val() || 'inbound',
            qty: cnsParseNumber($('#edit_Quantity').val(), 0),
            originalTank: editOriginalTankState.tank,
            originalType: editOriginalTankState.type,
            originalQty: editOriginalTankState.qty
        };
    }

    function updateEditTankCapacityPreview() {
        return window.cnsRenderTankCapacityPreview(
            $('#edit_tank_capacity_info'),
            getEditTankCapacityParams(),
            $('#edit_tank_overflow_container')
        );
    }

    function closeEditStock() {
        $('#FormEditStock').hide();
        $('#StockEditForm')[0].reset();
        $('#edit_Asal_Warehouse').val('').trigger('change');
        $('#edit_inbound_adjustment_alert').hide().empty();
        $('#edit_tank_capacity_info').hide().empty();
        $('#edit_tank_overflow_container').hide().empty();
        editOriginalTankState = { tank: '', type: 'inbound', qty: 0 };
    }

    function populateEditTankDropdown(selectedVal) {
        return window.cnsLoadTankBalanceDropdown($('#edit_Nama_Tangki'), selectedVal, {
            allowLegacy: true,
            onLoaded: updateEditTankCapacityPreview
        });
    }

    $(document).ready(function() {
        $('#edit_Nama_Tangki, #edit_Type').on('change', updateEditTankCapacityPreview);
        $('#edit_Quantity').on('input change', updateEditTankCapacityPreview);
    });
</script>