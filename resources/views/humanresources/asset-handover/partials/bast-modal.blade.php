<div class="p-1">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
        <div>
            <div class="fw-bold text-primary">{{ $ah->Handover_No }}</div>
            <small class="text-muted">
                Status: <span class="badge bg-info text-white">{{ $ah->Status ?? 'CREATED' }}</span>
                &bull; PIC: {{ $ah->PIC_Name ?: '-' }} ({{ $ah->PIC_Department ?: '-' }})
            </small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ url('hrd-asset-handover-bast-print?Handover_No=' . urlencode($ah->Handover_No) . '&doc_type=' . $docType) }}" target="_blank" class="btn btn-danger btn-sm">
                <i class="fa fa-print me-1"></i> Cetak BAST {{ $docType === 'RETURN' ? 'Return' : 'Handover' }} (mPDF)
            </a>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn {{ $docType === 'HANDOVER' ? 'btn-primary' : 'btn-outline-primary' }}"
                        data-handover-no="{{ $ah->Handover_No }}"
                        data-doc-type="HANDOVER"
                        onclick="window.switchBastModalTab(this);">
                    Handover
                </button>
                <button type="button" class="btn {{ $docType === 'RETURN' ? 'btn-warning text-dark fw-semibold' : 'btn-outline-warning text-dark' }}"
                        data-handover-no="{{ $ah->Handover_No }}"
                        data-doc-type="RETURN"
                        onclick="window.switchBastModalTab(this);">
                    Return
                </button>
            </div>
        </div>
    </div>

    @include('humanresources.asset-handover.partials.bast-panel', [
        'handoverNo' => $ah->Handover_No,
        'docType'    => $docType,
        'docs'       => $bastDocs,
        'panelId'    => $panelId,
        'allowUpload'=> true,
    ])
</div>
<script>
    window.switchBastModalTab = function(btn) {
        var handoverNo = btn.getAttribute('data-handover-no') || '';
        var docType = btn.getAttribute('data-doc-type') || 'HANDOVER';
        var url = 'hrd-asset-handover-bast-modal?Handover_No=' + encodeURIComponent(handoverNo) + '&doc_type=' + encodeURIComponent(docType);
        if (window.AppModal && typeof window.AppModal.open === 'function') {
            window.AppModal.open({
                title: 'BAST & Lampiran: ' + handoverNo,
                body: '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="small text-muted mt-2">Memuat dokumen BAST...</div></div>',
                size: 'xl'
            });
            $.get(url, function(html) {
                $('#appModalBody').html(html);
            }).fail(function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : ('Gagal memuat dokumen BAST (' + xhr.status + ').');
                $('#appModalBody').html('<div class="alert alert-danger mb-0">' + msg + '</div>');
            });
        } else {
            LoadM('AssetHandoverDiv', url);
        }
    };
</script>
