{{-- Dynamic modal component — Adminor / Bootstrap 5 variant --}}
<div class="modal fade" id="appModal" tabindex="-1" aria-labelledby="appModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" id="appModalDialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="appModalBody"></div>
            <div class="modal-footer" id="appModalFooter"></div>
        </div>
    </div>
</div>

@push('modals-script')
<script>
window.AppModal = (function ($) {
    function instance() {
        return bootstrap.Modal.getOrCreateInstance(document.getElementById('appModal'));
    }

    function open(options) {
        var opts = $.extend({ title: '', body: '', size: null, actions: [] }, options);

        $('#appModalLabel').html(opts.title);
        $('#appModalBody').html(opts.body);

        var $dialog = $('#appModalDialog');
        $dialog.removeClass('modal-sm modal-lg modal-xl');
        if (opts.size) {
            $dialog.addClass('modal-' + opts.size);
        }

        var $footer = $('#appModalFooter');
        $footer.find('.modal-dynamic-btn').remove();

        $.each(opts.actions, function (i, action) {
            var $btn = $('<button>')
                .addClass('btn modal-dynamic-btn')
                .addClass(action.class || 'btn-primary')
                .html(action.label || 'OK');

            if (action.id) { $btn.attr('id', action.id); }
            if (action.attrs && typeof action.attrs === 'object') {
                $.each(action.attrs, function (attr, val) { $btn.attr(attr, val); });
            }
            if (typeof action.onClick === 'function') {
                $btn.on('click', function () {
                    var result = action.onClick.call(this, close);
                    if (result !== false) { close(); }
                });
            }
            $footer.append($btn);
        });

        instance().show();
    }

    function close() {
        instance().hide();
    }

    return { open: open, close: close };
}(jQuery));
</script>
@endpush
