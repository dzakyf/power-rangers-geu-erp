@php
// Generate unique ID for this component instance
$instanceId = uniqid('canvas_');
$canvasId = "signature_canvas_{$instanceId}";
$formId = "drawing_form_{$instanceId}";
$finalizeFormId = "finalize_form_{$instanceId}";

$opportunityId = $opportunityId ?? request('id', "null");
$saveUrl = $saveUrl ?? "/geu/hcga/public/partner-onboarding-preview-pks?id=" . $opportunityId;
$saveDocumentUrl = $saveDocumentUrl ?? "/geu/hcga/public/partner-onboarding-submit-pks?id=" . $opportunityId;
@endphp

<div class="signature-canvas-wrapper" data-instance-id="{{ $instanceId }}">
    <canvas
        id="{{ $canvasId }}"
        width="800"
        height="500"
        style="border:2px solid black; cursor:crosshair;"></canvas>

    <div style="margin-top: 10px;">
        <button onclick="window.canvasInstances['{{ $instanceId }}'].clear()">Clear</button>
        <button onclick="window.canvasInstances['{{ $instanceId }}'].save()"
            style="background: #4CAF50; color: white;">
            Submit & Preview
        </button>
        <button onclick="window.canvasInstances['{{ $instanceId }}'].finalize()"
            style="background: #2196F3; color: white;">
            Finalize Document
        </button>
    </div>

    {{-- Hidden forms for this instance --}}
    <form id="{{ $formId }}" method="POST" action="{{ $saveUrl }}" style="display: none;">
        @csrf
        <input type="hidden" name="signature" id="signature_data_{{ $instanceId }}">
        <input type="hidden" name="opportunity_id" value="{{ $opportunityId }}">
    </form>

    <form id="{{ $finalizeFormId }}" method="POST" action="{{ $saveDocumentUrl }}" style="display: none;">
        @csrf
        <input type="hidden" name="signature" id="finalize_signature_{{ $instanceId }}">
        <input type="hidden" name="opportunity_id" value="{{ $opportunityId }}">
    </form>

    <div id="status_{{ $instanceId }}" style="margin-top: 10px; display: none;"></div>
</div>

<script>
    window.canvasInstances = window.canvasInstances || {};

    (function() {
        const instanceId = '{{ $instanceId }}';
        const canvasId = '{{ $canvasId }}';
        const formId = '{{ $formId }}';
        const finalizeFormId = '{{ $finalizeFormId }}';
        const signatureDataId = 'signature_data_{{ $instanceId }}';
        const finalizeSignatureId = 'finalize_signature_{{ $instanceId }}';

        // Get canvas element
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Drawing state (instance-specific)
        let drawing = false;
        let currentColor = 'black';

        // Set initial drawing style
        ctx.lineWidth = 5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = currentColor;

        // Drawing functions
        function startDrawing(e) {
            drawing = true;
            ctx.beginPath();
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;
            ctx.moveTo(x, y);
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX || e.touches[0].clientX) - rect.left;
            const y = (e.clientY || e.touches[0].clientY) - rect.top;
            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        function stopDrawing() {
            drawing = false;
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            showStatus('Canvas cleared', 'info');
        }

        function changeColor(color) {
            currentColor = color;
            ctx.strokeStyle = color;
        }

        function saveDrawing() {
            const imageData = canvas.toDataURL('image/png');
            $(`#${signatureDataId}`).val(imageData);
            $(`#${formId}`).submit();
        }

        function finalizeDocument() {
            const imageData = canvas.toDataURL('image/png');
            $(`#${finalizeSignatureId}`).val(imageData);
            console.log('Finalizing for instance:', instanceId);
            $(`#${finalizeFormId}`).submit();
        }

        function showStatus(message, type) {
            const statusDiv = document.getElementById(`status_${instanceId}`);
            if (!statusDiv) return;
            statusDiv.textContent = message;
            statusDiv.style.color = type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue';
            statusDiv.style.display = 'block';
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 3000);
        }

        // Add event listeners
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        window.canvasInstances[instanceId] = {
            clear: clearCanvas,
            save: saveDrawing,
            finalize: finalizeDocument,
            changeColor: changeColor,
            canvas: canvas,
            ctx: ctx
        };

        // Handle form submissions for this instance
        $(`#${formId}`).on('submit', function(e) {
            e.preventDefault();
            let fd = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response && response.success) {
                        showStatus('Dokumen berhasil dibuat. Mengunduh...', 'success');
                        if (response.download_url) {
                            var dl = document.createElement('iframe');
                            dl.style.display = 'none';
                            dl.src = response.download_url;
                            document.body.appendChild(dl);
                        }
                        // Refresh the stepper so the MoU/PKS progress updates
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        showStatus('Error: ' + (response ? response.message : 'Unknown error'), 'error');
                    }
                },
                error: function(xhr) {
                    showStatus('Error generating document', 'error');
                }
            });
        });

        $(`#${finalizeFormId}`).on('submit', function(e) {
            e.preventDefault();
            let fd = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response && response.success) {
                        location.reload();
                    } else {
                        showStatus('Error: ' + (response ? response.message : 'Unknown error'), 'error');
                    }
                },
                error: function(xhr) {
                    showStatus('Error finalizing document', 'error');
                }
            });
        });
    })();
</script>