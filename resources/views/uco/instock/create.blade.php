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
                            <option value="HUB GEU">HUB GEU</option>
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

        $('#btnSubmitBatch').on('click', function() {
            if (!previewData || !previewData.length) {
                alert('Lakukan preview terlebih dahulu');
                return;
            }

            var groups = previewData.map(function(g, i) {
                return {
                    warehouse: g.warehouse,
                    do_codes: g.do_codes,
                    received_qty: g.received_qty,
                    avg_price: g.avg_price,
                    total: g.total
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
