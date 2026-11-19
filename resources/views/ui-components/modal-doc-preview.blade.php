{{-- Global Document Preview Modal for CNS module --}}
<div class="modal fade" id="globalCnsDocPreviewModal" tabindex="-1" aria-labelledby="globalCnsDocPreviewTitle" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 90vw;">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title font-weight-bold d-flex align-items-center mb-0" id="globalCnsDocPreviewTitleWrap">
                    <i class="fa fa-file-text-o mr-2"></i>
                    <span id="globalCnsDocPreviewTitle">Preview Dokumen</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" onclick="window.cnsCloseDocPreview();"></button>
            </div>
            <div class="modal-body p-0" id="globalCnsDocPreviewBody" style="min-height: 70vh; max-height: 80vh; overflow: auto; background-color: #f8f9fa;">
                {{-- Dynamic content loaded via JS --}}
            </div>
            <div class="modal-footer py-2 bg-light">
                <a href="#" id="globalCnsDocPreviewDownloadBtn" class="btn btn-sm btn-primary" download target="_blank">
                    <i class="fa fa-download mr-1"></i> Unduh Dokumen
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="window.cnsCloseDocPreview();">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    window._cnsActiveBlobUrl = null;

    window.cnsCloseDocPreview = function () {
        var modalEl = document.getElementById('globalCnsDocPreviewModal');
        if (!modalEl) return;
        if (window.bootstrap && window.bootstrap.Modal) {
            var inst = window.bootstrap.Modal.getInstance(modalEl);
            if (inst) inst.hide();
        } else if (window.$) {
            $('#globalCnsDocPreviewModal').modal('hide');
        }
    };

    window.cnsOpenDocPreview = function (target, customTitle) {
        var url = null;
        var fileName = customTitle || '';
        var isPdf = false;
        var isImage = false;
        var isDoc = false;

        if (window._cnsActiveBlobUrl) {
            URL.revokeObjectURL(window._cnsActiveBlobUrl);
            window._cnsActiveBlobUrl = null;
        }

        if (target instanceof File || target instanceof Blob) {
            fileName = target.name || customTitle || 'Dokumen';
            window._cnsActiveBlobUrl = URL.createObjectURL(target);
            url = window._cnsActiveBlobUrl;

            var type = (target.type || '').toLowerCase();
            var ext = fileName.split('.').pop().toLowerCase();
            isPdf = type === 'application/pdf' || ext === 'pdf';
            isImage = type.indexOf('image/') === 0 || ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].indexOf(ext) !== -1;
            isDoc = ['doc', 'docx'].indexOf(ext) !== -1;
        } else if (typeof target === 'string') {
            url = target;
            var cleanUrl = target.split('?')[0].split('#')[0];
            var ext = cleanUrl.split('.').pop().toLowerCase();
            if (!fileName) {
                fileName = cleanUrl.split('/').pop() || 'Dokumen';
            }

            isPdf = ext === 'pdf' || target.indexOf('-file') !== -1 || target.indexOf('bast') !== -1 || target.indexOf('-document') !== -1 || target.indexOf('download') !== -1;
            isImage = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'].indexOf(ext) !== -1;
            isDoc = ['doc', 'docx'].indexOf(ext) !== -1;
        }

        if (!url) return;

        $('#globalCnsDocPreviewTitle').text(fileName);

        var $body = $('#globalCnsDocPreviewBody');
        $body.empty();

        var $dlBtn = $('#globalCnsDocPreviewDownloadBtn');
        $dlBtn.attr('href', url);
        if (fileName) {
            $dlBtn.attr('download', fileName);
        }

        if (isImage) {
            var $imgWrapper = $('<div>')
                .addClass('d-flex justify-content-center align-items-center p-3')
                .css({ 'min-height': '70vh', 'background-color': '#212529' });
            var $img = $('<img>')
                .attr('src', url)
                .attr('alt', fileName)
                .css({ 'max-width': '100%', 'max-height': '75vh', 'object-fit': 'contain', 'border-radius': '4px', 'box-shadow': '0 4px 12px rgba(0,0,0,0.3)' });
            $imgWrapper.append($img);
            $body.append($imgWrapper);
        } else if (isDoc) {
            $body.html(
                '<div class="p-5 text-center text-muted">' +
                '<i class="fa fa-file-word-o fa-4x mb-3 text-primary d-block"></i>' +
                '<h5 class="text-dark font-weight-bold">Preview format DOC/DOCX tidak didukung langsung oleh browser</h5>' +
                '<p class="mb-3">Browser tidak dapat menampilkan berkas Word (.doc, .docx) secara langsung tanpa konverter pihak ketiga.</p>' +
                '<p>Silakan klik tombol <strong>Unduh Dokumen</strong> di bawah untuk membuka berkas di perangkat Anda.</p>' +
                '</div>'
            );
        } else {
            var $iframe = $('<iframe>', {
                src: url,
                style: 'width: 100%; height: 75vh; border: none; display: block;'
            });
            $body.append($iframe);
        }

        var modalEl = document.getElementById('globalCnsDocPreviewModal');
        if (window.bootstrap && window.bootstrap.Modal) {
            var modalInst = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInst.show();
        } else if (window.$) {
            $('#globalCnsDocPreviewModal').modal('show');
        }
    };

    $(document).on('hidden.bs.modal', '#globalCnsDocPreviewModal', function () {
        $('#globalCnsDocPreviewBody').empty();
        if (window._cnsActiveBlobUrl) {
            URL.revokeObjectURL(window._cnsActiveBlobUrl);
            window._cnsActiveBlobUrl = null;
        }
    });

    // Otomatis pasang tombol preview pada setiap input file saat berkas dipilih
    $(document).on('change', 'input[type="file"]', function () {
        var input = this;
        var $input = $(input);
        var files = input.files;

        var $parent = $input.closest('.form-group, .custom-file, td, div');
        var $btn = $parent.find('.cns-auto-preview-btn');

        if (files && files.length > 0) {
            if ($btn.length === 0) {
                $btn = $(
                    '<button type="button" class="btn btn-outline-info btn-sm cns-auto-preview-btn mt-1">' +
                    '<i class="fa fa-eye mr-1"></i> Preview Dokumen' +
                    '</button>'
                );
                if ($input.parent().hasClass('custom-file')) {
                    $input.parent().after($btn);
                } else {
                    $input.after($btn);
                }
            }
            $btn.show().data('target-input', input);
        } else {
            $btn.hide();
        }
    });

    $(document).on('click', '.cns-auto-preview-btn', function (e) {
        e.preventDefault();
        var input = $(this).data('target-input');
        if (!input || !input.files || !input.files[0]) return;
        window.cnsOpenDocPreview(input.files[0]);
    });

    // Buka link dokumen tersimpan langsung di dalam modal preview
    $(document).on('click', 'a', function (e) {
        var $a = $(this);
        var href = $a.attr('href');
        if (!href || href === '#' || href.indexOf('javascript:') === 0) return;
        if ($a.hasClass('no-cns-preview') || $a.data('no-preview') || $a.attr('id') === 'globalCnsDocPreviewDownloadBtn') return;

        var extMatch = href.match(/\.(pdf|jpe?g|png|docx?|webp)(\?.*)?$/i);
        var isDownloadRoute = href.match(/(-file|-download|\.download|-document|-bast|\/bast|bast|filevendor|filescontract|attachments?)/i);
        var isPrintOrNav = href.match(/(\.print|-print|master-company|order-detail|users-cns|role-cns)/i);

        if ((extMatch || isDownloadRoute) && !isPrintOrNav) {
            e.preventDefault();
            var title = $a.text().trim() || 'Dokumen';
            title = title.replace(/^(\+)?\s*(Lihat|Download|Unduh)\s*/i, '').trim();
            window.cnsOpenDocPreview(href, title);
        }
    });
})(jQuery);
</script>
