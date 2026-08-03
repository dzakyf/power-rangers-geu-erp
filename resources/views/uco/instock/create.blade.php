@csrf
<div class="card mb-3" id="createInstockCard" style="display:none;">
    <div class="card-header m-3">
        <h5>Create Instock Supply</h5>
    </div>
    <div class="card-body p-3">
        <form id="instockCreateForm">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" class="form-control" name="received_date" id="create_received_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Source</label>
                        <select class="form-control" name="source" id="create_source">
                            <option value="MITRA" selected>MITRA</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Surat Jalan <span style="color:red;">*</span></label>
                <select id="createInstockDO" class="form-control" multiple="multiple" style="width:100%;"></select>
            </div>

            <button type="button" class="btn btn-info" id="btnPreview">Preview</button>
            <hr>

            <div id="previewSection" style="display:none;">
                <h6 class="mb-2">Preview — Instock Records to be Created</h6>
                <div style="max-height:250px;overflow-y:auto;">
                    <table class="table table-sm table-bordered" id="previewTable">
                        <thead><tr><th>Warehouse</th><th>Surat Jalan</th><th>Qty Plan (L)</th><th>Actual Qty</th><th>Avg Price</th><th>Total</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>

                <hr>
                <h6 class="mb-2">Photos & Notes</h6>
                <div class="text-center mb-3">
                    <div class="border rounded p-2 bg-light">
                        <video id="webcam" width="100%" autoplay playsinline style="max-height:200px;"></video>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-primary" id="btnCapture">Capture</button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Captured Photos</label>
                    <div id="photoGallery" class="row"></div>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea class="form-control" name="notes" id="create_notes" rows="3"></textarea>
                </div>

                <button type="button" class="btn btn-primary" id="btnSubmitBatch">Submit All</button>
            </div>
        </form>
    </div>
</div>

<script>
    var capturedPhotos = [];
    var webcamStream = null;
    var previewData = null;

    function startWebcam() {
        var video = document.getElementById('webcam');
        if (!video) return;
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) { webcamStream = stream; video.srcObject = stream; })
                .catch(function(err) { console.log('Webcam error:', err); });
        }
    }

    function stopWebcam() {
        if (webcamStream) { webcamStream.getTracks().forEach(function(t) { t.stop(); }); webcamStream = null; }
    }

    function renderGallery() {
        var container = document.getElementById('photoGallery');
        container.innerHTML = '';
        capturedPhotos.forEach(function(photo, index) {
            var col = document.createElement('div');
            col.className = 'col-4 col-md-3 mb-2';
            col.innerHTML = '<div class="position-relative" style="border:1px solid #ddd;border-radius:4px;overflow:hidden;">' +
                '<img src="' + photo + '" style="width:100%;height:120px;object-fit:cover;" />' +
                '<button type="button" class="btn btn-sm btn-danger position-absolute" style="top:2px;right:2px;padding:0 6px;" onclick="capturedPhotos.splice(' + index + ',1); renderGallery();">&times;</button>' +
                '</div>';
            container.appendChild(col);
        });
    }

    function initContainerSelect($sel, containers) {
        $sel.empty();
        (containers || []).forEach(function(c) {
            var $opt = $('<option>')
                .val(c.id)
                .text(c.name + ' — ' + c.current_fill + ' / ' + c.volume + ' L')
                .attr('data-volume', c.volume)
                .attr('data-fill', c.current_fill)
                .attr('data-remaining', c.remaining_capacity);
            $sel.append($opt);
        });
        $sel.select2({
            placeholder: '-- Pilih / Ketik container --',
            allowClear: true,
            width: '100%',
            tags: true,
            createTag: function(params) {
                var term = $.trim(params.term);
                if (term === '') return null;
                return { id: term, text: term, isNew: true };
            }
        });
    }

    function addContainerItem(i, preset) {
        var g = previewData[i];
        if (!g) return;
        var items = $('#previewTable .cont-items[data-group="' + i + '"]');
        var $tr = $('<tr class="cont-item"></tr>');
        $tr.append('<td><select class="form-control form-control-sm cont-select" data-group="' + i + '"></select></td>');
        $tr.append('<td><input type="number" step="0.01" class="form-control form-control-sm cont-volume" style="display:none;" placeholder="Volume (L)"></td>');
        $tr.append('<td><input type="number" step="0.01" class="form-control form-control-sm cont-qty" value="' + (preset ? preset.qty : '') + '"></td>');
        $tr.append('<td><input type="number" step="0.01" class="form-control form-control-sm cont-price" value="' + (g.avg_price || 0) + '"></td>');
        $tr.append('<td><button type="button" class="btn btn-sm btn-danger btn-remove-cont">&times;</button></td>');
        items.append($tr);

        var $sel = $tr.find('.cont-select');
        initContainerSelect($sel, g.containers || []);
        if (preset && preset.container) {
            $sel.val(String(preset.container.id)).trigger('change');
        }
    }

    function renderContainerBlock(tbody, g, i) {
        tbody.append(
            '<tr class="cont-row" data-group="' + i + '">' +
                '<td colspan="6">' +
                    '<table class="table table-sm table-bordered mb-1">' +
                        '<thead><tr><th style="min-width:220px;">Kontainer</th><th style="width:120px;">Volume Baru (L)</th><th style="width:120px;">Qty (L)</th><th style="width:130px;">Harga</th><th style="width:36px;"></th></tr></thead>' +
                        '<tbody class="cont-items" data-group="' + i + '"></tbody>' +
                        '<tfoot><tr><td colspan="5">' +
                            '<button type="button" class="btn btn-sm btn-outline-primary btn-add-cont" data-group="' + i + '">+ Tambah Kontainer</button>' +
                        '</td></tr></tfoot>' +
                    '</table>' +
                '</td>' +
            '</tr>'
        );

        var containers = g.containers || [];
        if (!containers.length) {
            addContainerItem(i, null);
            return;
        }
        var qty = parseFloat(g.received_qty) || 0;
        var first = containers[0];
        var presetQty = Math.min(qty, parseFloat(first.remaining_capacity));
        addContainerItem(i, { container: first, qty: presetQty });
    }

    function validateContainerAllocation() {
        var errors = [];
        previewData.forEach(function(g, i) {
            var items = $('#previewTable .cont-items[data-group="' + i + '"] .cont-item');
            if (!items.length) {
                errors.push('Alokasi container wajib diisi untuk ' + g.warehouse_display);
                return;
            }
            var total = 0;
            items.each(function() {
                var $row = $(this);
                var val = String($row.find('.cont-select').val() || '');
                var qty = parseFloat($row.find('.cont-qty').val()) || 0;
                if (!val) {
                    errors.push('Pilih atau ketik container untuk ' + g.warehouse_display);
                    return;
                }
                if (qty <= 0) {
                    errors.push('Qty container harus diisi untuk ' + g.warehouse_display);
                    return;
                }
                var isNew = !/^\d+$/.test(val);
                var volume, remaining, label;
                if (isNew) {
                    volume = parseFloat($row.find('.cont-volume').val()) || 0;
                    if (volume <= 0) {
                        errors.push('Volume container baru "' + val + '" wajib diisi');
                        return;
                    }
                    remaining = volume;
                    label = val;
                } else {
                    var $opt = $row.find('.cont-select option[value="' + val + '"]');
                    volume = parseFloat($opt.data('volume'));
                    remaining = parseFloat($opt.data('remaining'));
                    label = $opt.text();
                }
                if (qty > volume) {
                    errors.push('Qty melebihi volume container "' + label + '" (' + volume + ' L)');
                    return;
                }
                if (qty > remaining) {
                    errors.push('Kapasitas container "' + label + '" tidak cukup (sisa ' + remaining + ' L)');
                    return;
                }
                total += qty;
            });
            if (Math.abs(total - (parseFloat(g.received_qty) || 0)) > 0.001) {
                errors.push('Total alokasi container (' + total + ' L) tidak sama dengan qty diterima (' + g.received_qty + ' L) untuk ' + g.warehouse_display);
            }
        });
        return errors;
    }

    $(document).ready(function() {
        $('#createInstockDO').select2({
            placeholder: '-- Pilih Surat Jalan --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: 'partner-delivery-order-data',
                dataType: 'json',
                delay: 250,
                data: function(p) {
                    return { format: 'json', status: 'DONE', q: p.term || '' };
                },
                processResults: function(res) {
                    return { results: (res.data || []).map(function(r) {
                        return { id: r.ID, text: r.DO_Code || 'DO #' + r.ID };
                    })};
                },
                cache: true
            }
        });

        $('#btnPreview').on('click', function() {
            var doIds = $('#createInstockDO').val();
            if (!doIds || !doIds.length) {
                alert('Pilih minimal satu Surat Jalan');
                return;
            }
            $.ajax({
                url: 'partner-uco-instock-preview',
                type: 'POST',
                data: {
                    do_ids: doIds,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (!res.success || !res.data.length) {
                        alert('Tidak ada data PO yang ditemukan');
                        return;
                    }
                    previewData = res.data;
                    var tbody = $('#previewTable tbody');
                    tbody.empty();
                    res.data.forEach(function(g, i) {
                        tbody.append('<tr class="group-row" data-group="' + i + '" style="cursor:pointer;">' +
                            '<td>' + (g.warehouse_display || g.warehouse) + '</td>' +
                            '<td>' + (g.do_codes || []).join(', ') + '</td>' +
                            '<td>' + g.qty_plan + '</td>' +
                            '<td>' + g.qty_actual + '</td>' +
                            '<td>' + new Intl.NumberFormat('id-ID').format(g.avg_price) + '</td>' +
                            '<td>' + new Intl.NumberFormat('id-ID').format(g.total) + '</td>' +
                            '</tr>');
                        if (g.po_details && g.po_details.length) {
                            g.po_details.forEach(function(po) {
                                tbody.append('<tr class="po-detail-row" data-group="' + i + '" style="display:none;">' +
                                    '<td colspan="2" style="padding-left:30px;font-size:0.9em;"><strong>' + po.po_code + '</strong> — ' + po.poo_name + '</td>' +
                                    '<td>' + po.qty_plan + '</td>' +
                                    '<td>' + (po.qty_actual || '-') + '</td>' +
                                    '<td>' + po.percentage + '%</td>' +
                                    '<td><input type="number" step="0.01" class="form-control form-control-sm rec-input" data-po-id="' + po.po_id + '" value="' + po.recommendation + '" style="width:100px;"></td>' +
                                    '</tr>');
                            });
                        }
                        renderContainerBlock(tbody, g, i);
                    });
                    $('#previewSection').show();
                    setTimeout(startWebcam, 300);
                },
                error: function() { alert('Gagal memuat preview'); }
            });
        });

        $(document).on('click', '.group-row', function() {
            var g = $(this).data('group');
            $('.po-detail-row[data-group="' + g + '"]').toggle();
        });

        $(document).on('click', '.btn-add-cont', function() {
            addContainerItem($(this).data('group'), null);
        });

        $(document).on('click', '.btn-remove-cont', function() {
            $(this).closest('.cont-item').remove();
        });

        $(document).on('change', '.cont-select', function() {
            var val = String($(this).val() || '');
            var isNew = val !== '' && !/^\d+$/.test(val);
            var $volume = $(this).closest('tr').find('.cont-volume');
            $volume.toggle(isNew);
            if (!isNew) $volume.val('');
        });

        $('#btnSubmitBatch').on('click', function() {
            if (!previewData || !previewData.length) {
                alert('Lakukan preview terlebih dahulu');
                return;
            }

            var errors = validateContainerAllocation();
            if (errors.length) {
                alert(errors.join('\n'));
                return;
            }

            var groups = previewData.map(function(g, i) {
                var containers = [];
                $('#previewTable .cont-items[data-group="' + i + '"] .cont-item').each(function() {
                    var $row = $(this);
                    var val = String($row.find('.cont-select').val() || '');
                    var isNew = !/^\d+$/.test(val);
                    containers.push({
                        container_id: isNew ? '' : val,
                        container_name: isNew ? val : '',
                        container_volume: isNew ? $row.find('.cont-volume').val() : '',
                        qty: $row.find('.cont-qty').val(),
                        price: $row.find('.cont-price').val()
                    });
                });
                return {
                    warehouse: g.warehouse,
                    warehouse_display: g.warehouse_display,
                    received_qty: g.received_qty,
                    avg_price: g.avg_price,
                    total: g.total,
                    containers: containers
                };
            });

            var recommendations = {};
            $('.rec-input').each(function() {
                recommendations[$(this).data('po-id')] = $(this).val();
            });

            var fd = new FormData();
            fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
            fd.append('received_date', $('#create_received_date').val());
            fd.append('source', $('#create_source').val());
            fd.append('notes', $('#create_notes').val());
            fd.append('groups', JSON.stringify(groups));
            fd.append('recommendations', JSON.stringify(recommendations));

            capturedPhotos.forEach(function(photo) {
                var b64 = photo.split(',')[1] || photo;
                fd.append('webcam_photos[]', b64);
            });

            $.ajax({
                url: 'partner-uco-instock-submit-batch',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        alert(res.message);
                        resetCreateForm();
                        if (typeof loadInstockSuppliesData === 'function') loadInstockSuppliesData();
                    } else {
                        alert('Gagal: ' + (res.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                }
            });
        });

        function resetCreateForm() {
            $('#instockCreateForm')[0].reset();
            $('#create_received_date').val(new Date().toISOString().slice(0, 10));
            $('#create_source').val('MITRA');
            $('#createInstockDO').val(null).trigger('change');
            $('#previewTable tbody').empty();
            $('#previewSection').hide();
            previewData = null;
            capturedPhotos = [];
            renderGallery();
        }

        $(document).on('click', '#btnCapture', function() {
            var video = document.getElementById('webcam');
            var canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            capturedPhotos.push(canvas.toDataURL('image/png'));
            renderGallery();
        });
    });
</script>
