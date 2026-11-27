@php
    $panelId = $panelId ?? ('bastPanel_' . preg_replace('/[^A-Za-z0-9]/', '', (string) $handoverNo) . '_' . strtoupper((string) $docType));
    $docTypeUpper = strtoupper((string) $docType);
    $docLabel = $docTypeUpper === 'RETURN' ? 'BAST Pengembalian (Return)' : 'BAST Serah Terima (Handover)';
    $docsList = $docs ?? [];
    $allowUpload = $allowUpload ?? true;
    $bastPrintUrl = $bastPrintUrl ?? url('hrd-asset-handover-bast-print?Handover_No=' . urlencode((string) $handoverNo) . '&doc_type=' . $docTypeUpper);
@endphp

<div class="card border shadow-sm mt-3" id="{{ $panelId }}" data-handover-no="{{ $handoverNo }}" data-doc-type="{{ $docTypeUpper }}">
    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <i class="fa fa-file-pdf text-danger"></i>
            <span class="fw-bold small text-dark">{{ $docLabel }} (Sistem mPDF)</span>
            <span class="badge {{ count($docsList) > 0 ? 'bg-info text-white' : 'bg-secondary text-white' }}" id="{{ $panelId }}_countBadge">
                {{ count($docsList) > 0 ? (count($docsList) . ' Lampiran Fisik') : '0 Lampiran Opsional' }}
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ $bastPrintUrl }}" target="_blank" class="btn btn-danger btn-sm py-0 px-2">
                <i class="fa fa-print me-1"></i> Cetak / Preview BAST PDF
            </a>
            <button type="button" class="btn btn-outline-secondary btn-xs btn-sm py-0 px-2" onclick="window.BastPanelRefresh('{{ $panelId }}');">
                <i class="fa fa-sync-alt"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-3">
        <div class="alert alert-light border py-2 px-3 small text-muted mb-3">
            <i class="fa fa-info-circle text-primary me-1"></i>
            Dokumen <strong>{{ $docLabel }}</strong> dihasilkan otomatis oleh sistem (mPDF + QR Verifikasi). Unggah lampiran di bawah bersifat <strong>opsional</strong> untuk foto kondisi fisik atau dokumen pendukung tambahan.
        </div>

        @if($allowUpload)
            <div class="border rounded p-2 bg-light mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold mb-1">Lampiran Foto / Dokumen Pendukung (Opsional, maks 10 MB/file)</label>
                        <input type="file" id="{{ $panelId }}_files" class="form-control form-control-sm" multiple accept=".pdf,.jpg,.jpeg,.png,.webp" />
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold mb-1">Catatan Lampiran (Opsional)</label>
                        <input type="text" id="{{ $panelId }}_notes" class="form-control form-control-sm" placeholder="Mis. Foto kondisi fisik unit / kelengkapan..." maxlength="500" />
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-primary btn-sm" id="{{ $panelId }}_uploadBtn" onclick="window.BastPanelUpload('{{ $panelId }}');">
                            <i class="fa fa-upload me-1"></i> Upload
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle mb-0" style="font-size: 0.82rem;">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;" class="text-center">No</th>
                        <th>Nama Dokumen</th>
                        <th style="width:75px;" class="text-center">Tipe</th>
                        <th style="width:90px;" class="text-end">Ukuran</th>
                        <th>Diunggah Oleh &amp; Waktu</th>
                        <th>Catatan</th>
                        <th style="width:165px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="{{ $panelId }}_tbody">
                    @forelse($docsList as $idx => $doc)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="fw-semibold text-break">{{ $doc['original_name'] }}</td>
                            <td class="text-center"><span class="badge bg-info text-white">{{ strtoupper($doc['extension']) }}</span></td>
                            <td class="text-end text-nowrap">{{ $doc['file_size_fmt'] }}</td>
                            <td class="small">{{ $doc['uploaded_by'] }}<br><span class="text-muted">{{ $doc['uploaded_at'] }}</span></td>
                            <td class="small">{{ $doc['notes'] ?: '-' }}</td>
                            <td class="text-center text-nowrap">
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm py-0 px-2 bast-preview-btn"
                                        data-preview-url="{{ $doc['preview_url'] }}"
                                        data-preview-name="{{ $doc['original_name'] }}"
                                        data-preview-ext="{{ $doc['extension'] }}"
                                        title="Preview">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <a href="{{ $doc['download_url'] }}" class="btn btn-outline-success btn-sm py-0 px-2" title="Download">
                                    <i class="fa fa-download"></i>
                                </a>
                                @if(!empty($doc['can_delete']))
                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" onclick="window.BastPanelDelete('{{ $panelId }}', {{ (int) $doc['id'] }});" title="Hapus">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                @else
                                    <span class="badge bg-secondary text-white ms-1" title="Terkunci pasca-approval"><i class="fa fa-lock"></i></span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada dokumen {{ $docLabel }} yang diunggah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function() {
        if (window.__bastPanelInitialized) return;
        window.__bastPanelInitialized = true;

        function escapeHtml(str) {
            return String(str || '').replace(/[&<>"']/g, function(m) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m];
            });
        }

        window.BastPanelCount = function(panelId) {
            var $panel = $('#' + panelId);
            return Number($panel.data('count') !== undefined ? $panel.data('count') : $panel.find('tbody tr').filter(function() {
                return $(this).find('td').length > 1;
            }).length);
        };

        window.BastPanelRenderRows = function(panelId, docs) {
            var $panel = $('#' + panelId);
            var docType = $panel.attr('data-doc-type');
            var label = docType === 'RETURN' ? 'BAST Pengembalian (Return)' : 'BAST Serah Terima (Handover)';
            $panel.data('count', docs.length);

            var $badge = $('#' + panelId + '_countBadge');
            if (docs.length > 0) {
                $badge.removeClass('bg-secondary text-white').addClass('bg-info text-white').text(docs.length + ' Lampiran Fisik');
            } else {
                $badge.removeClass('bg-info text-white').addClass('bg-secondary text-white').text('0 Lampiran Opsional');
            }

            var html = '';
            if (!docs.length) {
                html = '<tr><td colspan="7" class="text-center text-muted py-3">Belum ada dokumen ' + escapeHtml(label) + ' yang diunggah.</td></tr>';
            } else {
                docs.forEach(function(doc, idx) {
                    var delBtn = doc.can_delete
                        ? '<button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" onclick="window.BastPanelDelete(\'' + panelId + '\', ' + doc.id + ');" title="Hapus"><i class="fa fa-trash"></i></button>'
                        : '<span class="badge bg-secondary text-white ms-1" title="Terkunci pasca-approval"><i class="fa fa-lock"></i></span>';

                    html += '<tr>'
                        + '<td class="text-center">' + (idx + 1) + '</td>'
                        + '<td class="fw-semibold text-break">' + escapeHtml(doc.original_name) + '</td>'
                        + '<td class="text-center"><span class="badge bg-info text-white">' + escapeHtml((doc.extension || '').toUpperCase()) + '</span></td>'
                        + '<td class="text-end text-nowrap">' + escapeHtml(doc.file_size_fmt) + '</td>'
                        + '<td class="small">' + escapeHtml(doc.uploaded_by) + '<br><span class="text-muted">' + escapeHtml(doc.uploaded_at) + '</span></td>'
                        + '<td class="small">' + escapeHtml(doc.notes || '-') + '</td>'
                        + '<td class="text-center text-nowrap">'
                        + '<button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 me-1 bast-preview-btn" data-preview-url="' + escapeHtml(doc.preview_url) + '" data-preview-name="' + escapeHtml(doc.original_name) + '" data-preview-ext="' + escapeHtml(doc.extension) + '" title="Preview"><i class="fa fa-eye"></i></button>'
                        + '<a href="' + escapeHtml(doc.download_url) + '" class="btn btn-outline-success btn-sm py-0 px-2 me-1" title="Download"><i class="fa fa-download"></i></a>'
                        + delBtn
                        + '</td></tr>';
                });
            }
            $('#' + panelId + '_tbody').html(html);

            if (typeof window.refreshData === 'function') {
                try { window.refreshData(); } catch (e) {}
            }
        };

        window.BastPanelRefresh = function(panelId) {
            var $panel = $('#' + panelId);
            var handoverNo = $panel.attr('data-handover-no');
            var docType = $panel.attr('data-doc-type');
            $.get('hrd-asset-handover-bast-list', { Handover_No: handoverNo, doc_type: docType }, function(res) {
                if (res && res.status === 'success') {
                    window.BastPanelRenderRows(panelId, res.data || []);
                }
            });
        };

        window.BastPanelUpload = function(panelId) {
            var $panel = $('#' + panelId);
            var handoverNo = $panel.attr('data-handover-no');
            var docType = $panel.attr('data-doc-type');
            var fileInput = document.getElementById(panelId + '_files');
            var notes = $('#' + panelId + '_notes').val() || '';

            if (!fileInput || !fileInput.files || !fileInput.files.length) {
                alert('Pilih minimal satu file BAST terlebih dahulu.');
                return;
            }

            var fd = new FormData();
            fd.append('Handover_No', handoverNo);
            fd.append('doc_type', docType);
            fd.append('notes', notes);
            for (var i = 0; i < fileInput.files.length; i++) {
                fd.append('files[]', fileInput.files[i]);
            }

            var $btn = $('#' + panelId + '_uploadBtn');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Uploading...');

            $.ajax({
                url: 'hrd-asset-handover-bast-upload',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}" },
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="fa fa-upload me-1"></i> Upload');
                    if (res && res.status === 'success') {
                        fileInput.value = '';
                        $('#' + panelId + '_notes').val('');
                        window.BastPanelRenderRows(panelId, res.docs || []);
                        alert(res.message || 'Dokumen BAST berhasil diunggah.');
                    } else {
                        alert((res && res.message) ? res.message : 'Gagal mengunggah dokumen BAST.');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<i class="fa fa-upload me-1"></i> Upload');
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat mengunggah dokumen BAST.';
                    alert(msg);
                }
            });
        };

        window.BastPanelDelete = function(panelId, docId) {
            if (!confirm('Yakin ingin menghapus dokumen BAST ini?')) return;
            $.ajax({
                url: 'hrd-asset-handover-bast-delete',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}" },
                data: { id: docId },
                success: function(res) {
                    if (res && res.status === 'success') {
                        window.BastPanelRefresh(panelId);
                    } else {
                        alert((res && res.message) ? res.message : 'Gagal menghapus dokumen BAST.');
                    }
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghapus dokumen BAST.';
                    alert(msg);
                }
            });
        };

        $(document).off('click.bastPreview').on('click.bastPreview', '.bast-preview-btn', function() {
            var $btn = $(this);
            window.BastPanelPreview(
                $btn.attr('data-preview-url') || '',
                $btn.attr('data-preview-name') || '',
                $btn.attr('data-preview-ext') || ''
            );
        });

        window.BastPanelPreview = function(url, name, ext) {
            var e = String(ext || '').toLowerCase();
            if (window.AppModal && typeof window.AppModal.open === 'function') {
                var bodyHtml = '';
                if (e === 'pdf') {
                    bodyHtml = '<iframe src="' + escapeHtml(url) + '" style="width:100%;height:72vh;border:0;"></iframe>';
                } else {
                    bodyHtml = '<div class="text-center"><img src="' + escapeHtml(url) + '" alt="' + escapeHtml(name) + '" class="img-fluid rounded shadow-sm" style="max-height:72vh;" /></div>';
                }
                window.AppModal.open({
                    title: 'Preview BAST: ' + escapeHtml(name),
                    body: bodyHtml,
                    size: 'xl'
                });
            } else {
                window.open(url, '_blank');
            }
        };
    })();
</script>
