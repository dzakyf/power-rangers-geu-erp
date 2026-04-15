{{-- Dynamic Modal Component --}}
<div class="modal fade" id="appModal" tabindex="-1" role="dialog"
    aria-labelledby="appModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" id="appModalDialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="appModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="appModalBody">
                {{-- Dynamic content injected here --}}
            </div>

            <div class="modal-footer" id="appModalFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
                {{-- Dynamic action buttons injected here --}}
            </div>

        </div>
    </div>
</div>

@push('modals-script')
<script>
    window.AppModal = (function($) {

        /**
         * Open the modal with dynamic content and optional action buttons.
         *
         * @param {object} options
         * @param {string}   options.title        - Modal title
         * @param {string}   options.body         - HTML or plain text for the body
         * @param {string}   [options.size]       - 'sm' | 'lg' | 'xl' (optional)
         * @param {Array}    [options.actions]    - Array of button config objects
         *   Each action: { label, class, id, attrs, onClick }
         *     - label   {string}   Button text
         *     - class   {string}   Bootstrap btn class e.g. 'btn-primary'
         *     - id      {string}   Optional button id
         *     - attrs   {object}   Optional extra HTML attributes { 'data-foo': 'bar' }
         *     - onClick {Function} Click handler (modal auto-closes after call unless
         *                          handler returns false explicitly)
         */
        function open(options) {
            var opts = $.extend({
                title: '',
                body: '',
                size: null,
                actions: []
            }, options);

            // Title
            $('#appModalLabel').html(opts.title);

            // Body
            $('#appModalBody').html(opts.body);

            // Modal dialog size
            var $dialog = $('#appModalDialog');
            $dialog.removeClass('modal-sm modal-lg modal-xl');
            if (opts.size) {
                $dialog.addClass('modal-' + opts.size);
            }

            // Rebuild action buttons (keep the static Close button)
            var $footer = $('#appModalFooter');
            $footer.find('.modal-dynamic-btn').remove();

            $.each(opts.actions, function(i, action) {
                var $btn = $('<button>')
                    .addClass('btn modal-dynamic-btn')
                    .addClass(action.class || 'btn-primary')
                    .html(action.label || 'OK');

                if (action.id) {
                    $btn.attr('id', action.id);
                }

                // Extra arbitrary attributes
                if (action.attrs && typeof action.attrs === 'object') {
                    $.each(action.attrs, function(attr, val) {
                        $btn.attr(attr, val);
                    });
                }

                // Click handler
                if (typeof action.onClick === 'function') {
                    $btn.on('click', function() {
                        var result = action.onClick.call(this, close);
                        if (result !== false) {
                            close();
                        }
                    });
                }

                $footer.append($btn);
            });

            $('#appModal').modal('show');
        }

        function close() {
            $('#appModal').modal('hide');
        }

        return {
            open: open,
            close: close
        };

    }(jQuery));
</script>
@endpush