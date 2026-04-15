var Signature = (function ($) {

    var instances = {};

    // -------------------------
    // Init a single instance
    // -------------------------
    function init(id) {
        var canvas = document.getElementById('sig_canvas_' + id);
        if (!canvas) return;

        // Wipe old listeners cleanly by replacing the node
        var fresh = canvas.cloneNode(true);
        canvas.parentNode.replaceChild(fresh, canvas);
        canvas = fresh;

        var ctx     = canvas.getContext('2d');
        var drawing = false;

        // Match internal resolution to CSS display size
        canvas.width = canvas.offsetWidth;

        ctx.lineWidth   = 2;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';
        ctx.strokeStyle = '#000';

        function pos(e) {
            var r   = canvas.getBoundingClientRect();
            var src = e.touches ? e.touches[0] : e;
            return {
                x: src.clientX - r.left,
                y: src.clientY - r.top
            };
        }

        function start(e) {
            drawing = true;
            var p = pos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        }

        function move(e) {
            if (!drawing) return;
            e.preventDefault();
            var p = pos(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        }

        function stop() {
            if (!drawing) return;
            drawing = false;
            sync(id, canvas);
        }

        canvas.addEventListener('mousedown',  start);
        canvas.addEventListener('mousemove',  move);
        canvas.addEventListener('mouseup',    stop);
        canvas.addEventListener('mouseleave', stop);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove',  move,  { passive: false });
        canvas.addEventListener('touchend',   stop);

        instances[id] = { canvas: canvas, ctx: ctx };
    }

    // -------------------------
    // Sync canvas to hidden input
    // -------------------------
    function sync(id, canvas) {
        $('#sig_input_' + id).val(canvas.toDataURL('image/png'));
    }

    // -------------------------
    // Public: clear
    // -------------------------
    function clear(id) {
        var inst = instances[id];
        if (!inst) return;
        inst.ctx.clearRect(0, 0, inst.canvas.width, inst.canvas.height);
        $('#sig_input_' + id).val('');
        $('#sig_error_' + id).text('');
    }

    // -------------------------
    // Public: validate
    // -------------------------
    function validate(id, message) {
        var val = $('#sig_input_' + id).val();
        if (!val) {
            $('#sig_error_' + id).text(message || 'Signature is required.');
            return false;
        }
        $('#sig_error_' + id).text('');
        return true;
    }

    // -------------------------
    // Public: get base64 value
    // -------------------------
    function getValue(id) {
        return $('#sig_input_' + id).val();
    }

    // -------------------------
    // Auto-init all on the page
    // -------------------------
    function initAll() {
        $('[data-sig-id]').each(function () {
            init($(this).data('sig-id'));
        });
    }

    return {
        init:     init,
        initAll:  initAll,
        clear:    clear,
        validate: validate,
        getValue: getValue
    };

}(jQuery));


// Auto-init on DOM ready
$(function () {
    Signature.initAll();
});