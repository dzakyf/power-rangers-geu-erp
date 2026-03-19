@php
// Set defaults if parameters not provided
$canvasId = $canvasId ?? 'canvas-' . uniqid();
$width = $width ?? 800;
$height = $height ?? 500;
$borderColor = $borderColor ?? 'black';
$showControls = $showControls ?? true;
$saveUrl = $saveUrl ?? '404';
@endphp

<div class="drawing-canvas-container">
    <canvas
        id="{{ $canvasId }}"
        width="{{ $width }}"
        height="{{ $height }}"
        style="border:2px solid black; cursor:crosshair;"></canvas>

    <div style="margin-top: 10px;">
        <button onclick="clearCanvas()" style="padding: 5px 15px; margin-right: 10px;">Clear</button>


        <label style="margin-left: 20px;">
            Brush Size:
            <input type="range" id="brushSize" min="1" max="20" value="5">
        </label>

        {{-- Save button --}}
        <button onclick="saveDrawing()" style="background: #4CAF50; color: white; padding: 5px 15px; margin-left: 20px;">
            Submit & Preview
        </button>

        <button id="saveDocument" style="display: none; background: #2196F3; color: white; padding: 5px 15px; margin-left: 10px;">
            Submit Document
        </button>
    </div>

    {{-- Optional: Hidden form for submitting --}}
    <form id="drawingForm" method="POST" action="{{ $saveUrl }}" style="display: none;">
        @csrf
        <input type="hidden" name="signature" id="imageData">
    </form>

    {{-- Save status message --}}
    <div id="savestatus" style="margin-top: 10px; padding: 10px; display: none;"></div>
    <div id="previewDoc" style="margin-top: 10px; padding: 10px; display: none;"></div>

</div>
<!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script> -->

<script>
    $(document).ready(function() {

        // Get the canvas element
        const canvas = document.getElementById('{{ $canvasId }}');
        const ctx = canvas.getContext('2d');

        // Drawing state
        let drawing = false;
        let currentColor = 'black';

        // Set initial drawing style
        ctx.lineWidth = 5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = currentColor;

        // Mouse event handlers
        function startDrawing(e) {
            drawing = true;
            ctx.beginPath();
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ctx.moveTo(x, y);
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();

            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        function stopDrawing() {
            drawing = false;
        }

        // Clear canvas function
        window.clearCanvas = function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            showStatus('Canvas cleared', 'info');
        }

        // Brush size changer
        document.getElementById('brushSize').addEventListener('input', function(e) {
            ctx.lineWidth = e.target.value;
        });

        window.saveDrawing = function() {
            const imageData = canvas.toDataURL('image/png'); // You can also use 'image/jpeg'

            $('#imageData').val(imageData)
            $('#drawingForm').submit()


        }


        $('#drawingForm').on('submit', function(e) {

            e.preventDefault()

            let fd = new FormData(this)
            // let fd = new FormData()
            // fd.append('file', imageData)


            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    // alert("Berhasil menyimpan");
                    console.log("Berhasil generate dokumen")
                    var blob = new Blob([response]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'mou.docx';
                    link.click();

                    window.URL.revokeObjectURL(link.href);
                    $('#saveDocument').show();




                    //$('#surveyForm')[0].reset();
                },
                error: function(xhr, status, error) {
                    console.log('Error details:', xhr.responseText);

                },
                complete: function() {
                    // $('#submitBtn').prop('disabled', false).text('Submit');
                }
            });
        })

        // fetch('/save-drawing', {
        //         method: 'POST',
        //         headers: {
        //             'Content-Type': 'application/json',
        //             'X-CSRF-TOKEN': '{{ csrf_token() }}'
        //         },
        //         body: JSON.stringify({
        //             image: imageData,
        //             filename: 'drawing-' + Date.now() + '.png'
        //         })
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         if (data.success) {
        //             showStatus('Drawing saved successfully!', 'success');
        //         } else {
        //             showStatus('Error saving drawing', 'error');
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Error:', error);
        //         showStatus('Error saving drawing', 'error');
        //     });
        // }

        // Method 2: Save using traditional form
        function saveWithForm(imageData) {
            document.getElementById('imageData').value = imageData;
            document.getElementById('drawingForm').submit();
        }

        // Helper function to show status messages
        function showStatus(message, type) {
            const statusDiv = document.getElementById('saveStatus');
            statusDiv.style.display = 'block';
            statusDiv.textContent = message;

            // Style based on type
            if (type === 'success') {
                statusDiv.style.color = 'green';
            } else if (type === 'error') {
                statusDiv.style.color = 'red';
            } else {
                statusDiv.style.color = 'blue';
            }

            // Hide after 3 seconds
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 3000);
        }

        // Add mouse event listeners
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        // Touch events for mobile
        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            startDrawing(e.touches[0]);
        });

        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            draw(e.touches[0]);
        });

        canvas.addEventListener('touchend', stopDrawing);

    })
</script>