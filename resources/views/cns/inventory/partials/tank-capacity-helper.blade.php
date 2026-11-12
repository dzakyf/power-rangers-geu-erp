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

        window.cnsRenderTankCapacityPreview = function($container, params) {
            if (!$container || !$container.length) return null;

            var res = window.cnsEvaluateTankCapacity(params);
            if (!res) {
                $container.hide().empty();
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

            return res;
        };
    }
</script>
