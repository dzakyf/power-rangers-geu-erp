<script>
    if (typeof window.cnsTankCapacityHelperLoaded === 'undefined') {
        window.cnsTankCapacityHelperLoaded = true;
        window._cnsTankBalanceMap = window._cnsTankBalanceMap || {};

        window.cnsLoadTankBalanceDropdown = function($sel, selectedTank, options) {
            options = options || {};
            return $.get('cns-master-tank-balances', function(res) {
                $sel.empty().append($('<option>', { value: '', text: '-- Pilih Tangki --' }));
                var matchedVal = '';
                var nextMap = {};

                if (res && res.success && res.data && res.data.length) {
                    $.each(res.data, function(_, t) {
                        if (!t || !t.name) return;
                        nextMap[String(t.name).toLowerCase()] = t;

                        var cap = Number(t.capacity) || 0;
                        var label = t.name + (cap > 0 ? ' (' + cnsFormatNumber(cap, 2) + ' KG)' : '');
                        var isSelected = (selectedTank && String(t.name).toLowerCase() === String(selectedTank).toLowerCase());
                        if (isSelected) {
                            matchedVal = t.name;
                        }
                        $sel.append($('<option>', { value: t.name, text: label, selected: isSelected }));
                    });
                }

                window._cnsTankBalanceMap = nextMap;

                if (selectedTank && !matchedVal && options.allowLegacy) {
                    $sel.append($('<option>', { value: selectedTank, text: selectedTank + ' (Legacy)', selected: true }));
                } else if (matchedVal) {
                    $sel.val(matchedVal);
                }

                if (typeof options.onLoaded === 'function') {
                    options.onLoaded(matchedVal);
                }
            });
        };

        window.cnsEvaluateTankCapacity = function(params) {
            var tankName = $.trim(params && params.tankName ? params.tankName : '');
            if (!tankName) return null;

            var tank = window._cnsTankBalanceMap[tankName.toLowerCase()];
            if (!tank) return null;

            var capacity = Number(tank.capacity) || 0;
            var currentStock = Number(tank.current_stock) || 0;
            var qty = Math.max(0, Number(params.qty) || 0);
            var txType = String(params.type || 'inbound').toLowerCase() === 'outbound' ? 'outbound' : 'inbound';

            var originalTank = $.trim(params.originalTank || '');
            var isSameOriginalTank = originalTank !== '' && originalTank.toLowerCase() === tank.name.toLowerCase();
            var baseStock = currentStock;

            if (isSameOriginalTank) {
                var origType = String(params.originalType || 'inbound').toLowerCase() === 'outbound' ? 'outbound' : 'inbound';
                var origQty = Number(params.originalQty) || 0;
                var origDelta = (origType === 'outbound') ? -origQty : origQty;
                baseStock = currentStock - origDelta;
            }

            var delta = (txType === 'outbound') ? -qty : qty;
            var projectedStock = baseStock + delta;
            var remainingBefore = capacity > 0 ? (capacity - baseStock) : null;
            var overQty = (txType === 'inbound' && capacity > 0) ? (projectedStock - capacity) : 0;
            var isOverCapacity = (txType === 'inbound' && capacity > 0 && Math.round(overQty * 100) > 0);
            var isNegativeStock = (txType === 'outbound' && Math.round(projectedStock * 100) < 0);
            var isWarning = isOverCapacity || isNegativeStock;
            var projectedPct = capacity > 0 ? ((projectedStock / capacity) * 100).toFixed(1) : null;

            var confirmMessage = '';
            if (isOverCapacity) {
                confirmMessage = 'Peringatan: Qty inbound (' + cnsFormatNumber(qty, 2) + ' KG) melebihi sisa kapasitas tangki ' +
                    tank.name + ' (' + cnsFormatNumber(Math.max(0, remainingBefore), 2) + ' KG). Estimasi stok terupdate menjadi ' +
                    cnsFormatNumber(projectedStock, 2) + ' KG dari kapasitas ' + cnsFormatNumber(capacity, 2) +
                    ' KG (lebih ' + cnsFormatNumber(overQty, 2) + ' KG).\n\nTetap lanjutkan simpan data?';
            } else if (isNegativeStock) {
                confirmMessage = 'Peringatan: Stok tangki ' + tank.name + ' akan menjadi minus (' +
                    cnsFormatNumber(projectedStock, 2) + ' KG) setelah outbound ' + cnsFormatNumber(qty, 2) +
                    ' KG.\n\nTetap lanjutkan simpan data?';
            }

            return {
                tank: tank,
                txType: txType,
                qty: qty,
                capacity: capacity,
                currentStock: currentStock,
                baseStock: baseStock,
                isSameOriginalTank: isSameOriginalTank,
                projectedStock: projectedStock,
                remainingBefore: remainingBefore,
                overQty: overQty,
                isOverCapacity: isOverCapacity,
                isNegativeStock: isNegativeStock,
                isWarning: isWarning,
                projectedPct: projectedPct,
                confirmMessage: confirmMessage
            };
        };

        window.cnsRenderTankCapacityPreview = function($container, params, $overflowContainer) {
            if (!$container || !$container.length) return null;

            var res = window.cnsEvaluateTankCapacity(params);
            if (!res) {
                $container.hide().empty();
                if ($overflowContainer && $overflowContainer.length) {
                    $overflowContainer.hide().empty();
                }
                return null;
            }

            var safeName = $('<div>').text(res.tank.name).html();
            var alertClass = res.isWarning
                ? 'alert-danger'
                : (res.txType === 'inbound' && res.capacity > 0 && res.qty > 0 ? 'alert-success' : 'alert-info');

            var titleHtml = '';
            if (res.isOverCapacity) {
                titleHtml = '<i class="fa fa-exclamation-triangle mr-1"></i><strong>Peringatan Kapasitas Tangki!</strong> Qty movement melebihi kapasitas tangki <strong>' +
                    safeName + '</strong> sebesar <strong>' + cnsFormatNumber(res.overQty, 2) + ' KG</strong>.';
            } else if (res.isNegativeStock) {
                titleHtml = '<i class="fa fa-exclamation-triangle mr-1"></i><strong>Peringatan Stok Tangki!</strong> Estimasi stok tangki <strong>' +
                    safeName + '</strong> akan minus (<strong>' + cnsFormatNumber(res.projectedStock, 2) + ' KG</strong>).';
            } else if (res.txType === 'inbound' && res.capacity > 0) {
                titleHtml = '<i class="fa fa-check-circle mr-1"></i><strong>Tangki ' + safeName + ' Mencukupi:</strong> Qty movement masih muat untuk inbound.';
            } else {
                titleHtml = '<i class="fa fa-info-circle mr-1"></i><strong>Info Stok Tangki ' + safeName + ':</strong>';
            }

            var metrics = [];
            if (res.isSameOriginalTank) {
                metrics.push('Stok Saat Ini (termasuk transaksi ini): <strong>' + cnsFormatNumber(res.currentStock, 2) + ' KG</strong>');
                metrics.push('Stok Sebelum Transaksi Ini: <strong>' + cnsFormatNumber(res.baseStock, 2) + ' KG</strong>');
            } else {
                metrics.push('Stok Saat Ini: <strong>' + cnsFormatNumber(res.currentStock, 2) + ' KG</strong>');
            }

            if (res.capacity > 0) {
                metrics.push('Kapasitas: <strong>' + cnsFormatNumber(res.capacity, 2) + ' KG</strong>');
                metrics.push('Sisa Muat: <strong>' + cnsFormatNumber(Math.max(0, res.remainingBefore), 2) + ' KG</strong>');
                metrics.push('Qty Tangki Terupdate: <strong>' + cnsFormatNumber(res.projectedStock, 2) + ' KG</strong> (' + res.projectedPct + '%)');
            } else {
                metrics.push('Qty Tangki Terupdate: <strong>' + cnsFormatNumber(res.projectedStock, 2) + ' KG</strong>');
            }

            $container
                .html(
                    '<div class="alert ' + alertClass + ' mb-0 py-2 px-3" role="alert" style="font-size:12px;">' +
                        '<div>' + titleHtml + '</div>' +
                        '<div class="mt-1">' + metrics.join(' <span class="mx-1">|</span> ') + '</div>' +
                    '</div>'
                )
                .show();

            if ($overflowContainer && $overflowContainer.length) {
                window.cnsRenderTankOverflowAllocator($overflowContainer, res);
            }

            return res;
        };

        window.cnsGetAvailableOverflowTanks = function(primaryTankName) {
            var list = [];
            var primaryLower = String(primaryTankName || '').toLowerCase();
            $.each(window._cnsTankBalanceMap || {}, function(_, t) {
                if (!t || !t.name) return;
                if (String(t.name).toLowerCase() === primaryLower) return;
                var cap = Number(t.capacity) || 0;
                var stock = Number(t.current_stock) || 0;
                var rem = cap > 0 ? Math.max(0, cap - stock) : 0;
                list.push({ name: t.name, capacity: cap, current_stock: stock, remaining: rem });
            });
            list.sort(function(a, b) { return a.name.localeCompare(b.name); });
            return list;
        };

        window.cnsUpdateOverflowSummary = function($container) {
            var requiredOver = Number($container.data('requiredOver')) || 0;
            var allocated = 0;
            var primaryTank = String($container.data('primaryTank') || '');
            var tanks = window.cnsGetAvailableOverflowTanks(primaryTank);
            var tankByName = {};
            $.each(tanks, function(_, t) { tankByName[t.name.toLowerCase()] = t; });

            $container.find('.cns-overflow-row').each(function() {
                var $row = $(this);
                var selTank = $.trim($row.find('.cns-overflow-tank').val() || '');
                var qty = Math.max(0, Number($row.find('.cns-overflow-qty').val()) || 0);
                allocated += qty;

                var $info = $row.find('.cns-overflow-row-info');
                if (selTank && tankByName[selTank.toLowerCase()]) {
                    var t = tankByName[selTank.toLowerCase()];
                    if (t.capacity > 0 && Math.round((qty - t.remaining) * 100) > 0) {
                        $info.removeClass('text-muted text-success').addClass('text-danger')
                            .text('Melebihi sisa kapasitas tangki (' + cnsFormatNumber(t.remaining, 2) + ' KG).');
                    } else {
                        $info.removeClass('text-danger text-success').addClass('text-muted')
                            .text('Sisa muat tangki: ' + (t.capacity > 0 ? cnsFormatNumber(t.remaining, 2) + ' KG' : 'Tanpa batas'));
                    }
                } else {
                    $info.removeClass('text-danger text-success').addClass('text-muted').text('');
                }
            });

            var diff = Math.round((requiredOver - allocated) * 100) / 100;
            var $summary = $container.find('.cns-overflow-summary');
            if (Math.abs(diff) <= 0.01) {
                $summary.removeClass('text-danger').addClass('text-success')
                    .html('Teralokasi: <strong>' + cnsFormatNumber(allocated, 2) + ' KG</strong> / <strong>' + cnsFormatNumber(requiredOver, 2) + ' KG</strong> (Lengkap)');
            } else if (diff > 0) {
                $summary.removeClass('text-success').addClass('text-danger')
                    .html('Teralokasi: <strong>' + cnsFormatNumber(allocated, 2) + ' KG</strong> / <strong>' + cnsFormatNumber(requiredOver, 2) + ' KG</strong> (Kurang ' + cnsFormatNumber(diff, 2) + ' KG)');
            } else {
                $summary.removeClass('text-success').addClass('text-danger')
                    .html('Teralokasi: <strong>' + cnsFormatNumber(allocated, 2) + ' KG</strong> / <strong>' + cnsFormatNumber(requiredOver, 2) + ' KG</strong> (Lebih ' + cnsFormatNumber(Math.abs(diff), 2) + ' KG)');
            }
        };

        window.cnsAppendOverflowRow = function($container, prefillQty) {
            var primaryTank = String($container.data('primaryTank') || '');
            var tanks = window.cnsGetAvailableOverflowTanks(primaryTank);
            var $select = $('<select>', { class: 'form-control form-control-sm cns-overflow-tank' });
            $select.append($('<option>', { value: '', text: '-- Pilih Tangki Tujuan --' }));
            $.each(tanks, function(_, t) {
                var remText = t.capacity > 0 ? 'Sisa: ' + cnsFormatNumber(t.remaining, 2) + ' KG' : 'No limit';
                $select.append($('<option>', { value: t.name, text: t.name + ' (' + remText + ')' }));
            });

            var qtyVal = (typeof prefillQty === 'number' && prefillQty > 0) ? prefillQty.toFixed(2) : '';
            var $row = $(
                '<div class="cns-overflow-row border rounded p-2 mb-2 bg-white">' +
                    '<div class="form-row align-items-center">' +
                        '<div class="col-md-6 mb-1 mb-md-0 cns-overflow-tank-col"></div>' +
                        '<div class="col-md-4 mb-1 mb-md-0">' +
                            '<input type="number" step="0.01" min="0.01" class="form-control form-control-sm cns-overflow-qty" placeholder="Qty sisa (KG)" value="' + qtyVal + '">' +
                        '</div>' +
                        '<div class="col-md-2 text-right">' +
                            '<button type="button" class="btn btn-sm btn-outline-danger cns-overflow-remove" title="Hapus baris">&times;</button>' +
                        '</div>' +
                    '</div>' +
                    '<small class="form-text text-muted cns-overflow-row-info mb-0"></small>' +
                '</div>'
            );
            $row.find('.cns-overflow-tank-col').append($select);
            $container.find('.cns-overflow-rows').append($row);

            $select.on('change', function() {
                var selName = $.trim($(this).val() || '');
                var $qtyInput = $row.find('.cns-overflow-qty');
                if (selName && (Number($qtyInput.val()) || 0) <= 0) {
                    var requiredOver = Number($container.data('requiredOver')) || 0;
                    var usedOther = 0;
                    $container.find('.cns-overflow-row').not($row).each(function() {
                        usedOther += Math.max(0, Number($(this).find('.cns-overflow-qty').val()) || 0);
                    });
                    var unallocated = Math.max(0, requiredOver - usedOther);
                    var found = $.grep(tanks, function(t) { return t.name.toLowerCase() === selName.toLowerCase(); })[0];
                    if (found && unallocated > 0) {
                        var autoQty = found.capacity > 0 ? Math.min(unallocated, found.remaining) : unallocated;
                        if (autoQty > 0) $qtyInput.val(autoQty.toFixed(2));
                    }
                }
                window.cnsUpdateOverflowSummary($container);
            });
            $row.find('.cns-overflow-qty').on('input change', function() {
                window.cnsUpdateOverflowSummary($container);
            });
            $row.find('.cns-overflow-remove').on('click', function() {
                if ($container.find('.cns-overflow-row').length > 1) {
                    $row.remove();
                    window.cnsUpdateOverflowSummary($container);
                }
            });

            window.cnsUpdateOverflowSummary($container);
        };

        window.cnsRenderTankOverflowAllocator = function($container, res) {
            if (!$container || !$container.length) return;
            if (!res || !res.isOverCapacity) {
                $container.hide().empty().removeData('primaryTank').removeData('requiredOver');
                return;
            }

            var primaryMuat = Math.min(res.qty, Math.max(0, res.remainingBefore));
            var safePrimary = $('<div>').text(res.tank.name).html();
            var prevPrimary = String($container.data('primaryTank') || '');
            var hasExisting = $container.find('.cns-overflow-rows').length > 0 && prevPrimary.toLowerCase() === res.tank.name.toLowerCase();

            $container.data('primaryTank', res.tank.name);
            $container.data('requiredOver', res.overQty);

            if (!hasExisting) {
                $container.html(
                    '<div class="card border-danger mb-0">' +
                        '<div class="card-header bg-danger text-white py-2 px-3" style="font-size:12px;">' +
                            '<strong><i class="fa fa-random mr-1"></i>Alokasi Sisa Kuantiti ke Tangki Lain (Wajib)</strong>' +
                        '</div>' +
                        '<div class="card-body py-2 px-3" style="font-size:12px;">' +
                            '<p class="mb-2 cns-overflow-desc"></p>' +
                            '<div class="cns-overflow-rows"></div>' +
                            '<div class="d-flex flex-wrap justify-content-between align-items-center mt-2">' +
                                '<div class="cns-overflow-summary"></div>' +
                                '<button type="button" class="btn btn-sm btn-outline-primary cns-overflow-add">+ Tambah Tangki Lain</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
                $container.find('.cns-overflow-add').on('click', function() {
                    var req = Number($container.data('requiredOver')) || 0;
                    var alloc = 0;
                    $container.find('.cns-overflow-qty').each(function() {
                        alloc += Math.max(0, Number($(this).val()) || 0);
                    });
                    window.cnsAppendOverflowRow($container, Math.max(0, req - alloc));
                });
                window.cnsAppendOverflowRow($container, res.overQty);
            }

            $container.find('.cns-overflow-desc').html(
                'Tangki <strong>' + safePrimary + '</strong> hanya muat <strong>' + cnsFormatNumber(primaryMuat, 2) + ' KG</strong>. ' +
                'Sisa <strong>' + cnsFormatNumber(res.overQty, 2) + ' KG</strong> dapat dialokasikan ke tangki lain ' +
                '(otomatis tercatat <strong>APPROVED</strong> dengan remark <code>sisa kuantiti {kode_moving}</code>) atau lakukan penyesuaian di menu <strong>Inbound Adjustment</strong>.'
            );
            window.cnsUpdateOverflowSummary($container);
            $container.show();
        };

        window.cnsValidateAndCollectOverflow = function($container, params) {
            var res = window.cnsEvaluateTankCapacity(params);
            if (!res || !res.isOverCapacity) {
                return { ok: true, allocations: [] };
            }

            if (!$container || !$container.length || $container.find('.cns-overflow-row').length === 0) {
                return {
                    ok: false,
                    message: 'Qty inbound melebihi sisa kapasitas tangki ' + res.tank.name + '. Silakan lakukan penyesuaian di menu Inbound Adjustment terlebih dahulu atau alokasikan sisa kuantiti (' + cnsFormatNumber(res.overQty, 2) + ' KG) ke tangki lain.'
                };
            }

            var tanks = window.cnsGetAvailableOverflowTanks(res.tank.name);
            var tankByName = {};
            $.each(tanks, function(_, t) { tankByName[t.name.toLowerCase()] = t; });

            var allocations = [];
            var seen = {};
            var sum = 0;
            var errMsg = '';

            $container.find('.cns-overflow-row').each(function(idx) {
                var tankName = $.trim($(this).find('.cns-overflow-tank').val() || '');
                var qty = Math.round((Number($(this).find('.cns-overflow-qty').val()) || 0) * 100) / 100;

                if (!tankName || qty <= 0) {
                    errMsg = 'Tangki tujuan dan qty sisa pada baris #' + (idx + 1) + ' wajib diisi lebih dari 0.';
                    return false;
                }
                var key = tankName.toLowerCase();
                if (seen[key]) {
                    errMsg = 'Tangki ' + tankName + ' tidak boleh dipilih ganda.';
                    return false;
                }
                seen[key] = true;

                var t = tankByName[key];
                if (t && t.capacity > 0 && Math.round((qty - t.remaining) * 100) > 0) {
                    errMsg = 'Qty alokasi untuk tangki ' + t.name + ' (' + cnsFormatNumber(qty, 2) + ' KG) melebihi sisa kapasitas (' + cnsFormatNumber(t.remaining, 2) + ' KG).';
                    return false;
                }

                sum = Math.round((sum + qty) * 100) / 100;
                allocations.push({ tank_name: t ? t.name : tankName, qty: qty });
            });

            if (errMsg) return { ok: false, message: errMsg };
            if (Math.abs(sum - res.overQty) > 0.01) {
                return {
                    ok: false,
                    message: 'Total alokasi sisa kuantiti (' + cnsFormatNumber(sum, 2) + ' KG) harus sama dengan sisa kuantiti (' + cnsFormatNumber(res.overQty, 2) + ' KG).'
                };
            }

            return { ok: true, allocations: allocations };
        };
    }
</script>
