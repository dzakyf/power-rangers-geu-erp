@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Surat Jalan</span></li>
    </ol>
</div>

<div class="container-fluid">
    <form id="UpdateReceiptForm">
        @csrf
        <input type="hidden" id="PO_Id" name="PO_Id">
        <input type="hidden" id="capturedPhotos" name="capturedPhotos">

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-3 d-inline-block">Update Surat Jalan</h5>
                        <button type="button" class="btn btn-warning float-right m-3 px-3 py-2" onclick="goingBack()"><i class="fas fa-arrow-left"></i> Back</button>
                    </div>
                    <div class="card-body">
                        @if (!empty($locked))
                        <div class="alert alert-warning">
                            <strong>Surat Jalan sudah DONE — read-only.</strong>
                            Data hanya dapat dilihat (untuk tracing) dan tidak dapat diubah.
                        </div>
                        @endif
                        {{-- Section 1: Take Photo --}}
                        <h6 class="font-weight-bold">1. Take Photo</h6>
                        <div class="form-group">
                            <label>GPS Location</label>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="Lat" name="Lat" placeholder="Latitude" value="{{ $po->Lat ?? '' }}" readonly>
                                <input type="text" class="form-control" id="Lng" name="Lng" placeholder="Longitude" value="{{ $po->Lng ?? '' }}" readonly>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="btnGetGPS">Get GPS</button>
                                </div>
                            </div>
                            <small id="gpsStatus" class="text-muted"></small>
                        </div>
                        <div class="form-group">
                            <label>Camera</label>
                            <video id="webcam" width="100%" autoplay style="max-height:300px;border:1px solid #ddd;border-radius:4px;background:#000;"></video>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary" id="btnCapture">
                                    <i class="fa fa-camera"></i> Ambil Foto
                                </button>
                            </div>
                            <canvas id="webcamCanvas" style="display:none;"></canvas>
                        </div>
                        <div class="form-group">
                            <label>Preview Foto</label>
                            <div id="photoGallery" class="row"></div>
                        </div>

                        <hr>

                        {{-- Section 2: Signature --}}
                        <h6 class="font-weight-bold">2. Signature</h6>
                        <div class="form-group">
                            @include('ui-components.signature-canvas-field', ['name' => 'Signature', 'label' => 'Tanda Tangan'])
                        </div>

                        <hr>

                        {{-- Section 3: Confirmation --}}
                        <h6 class="font-weight-bold">3. Konfirmasi</h6>
                        <div class="form-group">
                            <label>Total Berat Aktual</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="Actual_Qty" name="Actual_Qty" min="0" step="any" value="{{ $po->Actual_Qty ?? '' }}" {{ !empty($locked) ? 'disabled' : '' }}>
                                <div class="input-group-append">
                                    <select class="form-control" id="BeratUnit" name="BeratUnit">
                                        <option value="L" selected>L (Liter)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status Surat Jalan</label>
                            <select class="form-control" id="DO_Status" name="DO_Status" {{ !empty($locked) ? 'disabled' : '' }}>
                                <option value="">-- Pilih Status --</option>
                                <option value="PROGRESS" {{ ($do->DO_Status ?? 'PROGRESS') == 'PROGRESS' ? 'selected' : '' }}>PROGRESS</option>
                                <option value="PICKUP" {{ ($do->DO_Status ?? '') == 'PICKUP' ? 'selected' : '' }}>PICKUP</option>
                                <option value="DONE" {{ ($do->DO_Status ?? '') == 'DONE' ? 'selected' : '' }}>DONE</option>
                                <option value="CANCELLED" {{ ($do->DO_Status ?? '') == 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                            </select>
                        </div>

                        <hr>

                        {{-- Slide to Confirm --}}
                        <div class="slide-confirm-wrapper">
                            <div class="slide-confirm-track" id="slideConfirmTrack">
                                <div class="slide-confirm-progress" id="slideConfirmProgress"></div>
                                <span class="slide-confirm-text" id="slideConfirmText">Geser untuk konfirmasi</span>
                                <div class="slide-confirm-thumb" id="slideConfirmThumb">
                                    <i class="fa fa-arrow-right"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .slide-confirm-wrapper {
        margin-top: 16px;
    }

    .slide-confirm-track {
        position: relative;
        background: #e9ecef;
        border-radius: 30px;
        height: 50px;
        overflow: hidden;
        cursor: pointer;
        user-select: none;
        -webkit-user-select: none;
    }

    .slide-confirm-text {
        position: absolute;
        width: 100%;
        line-height: 50px;
        text-align: center;
        color: #6c757d;
        font-size: 14px;
        pointer-events: none;
        z-index: 1;
    }

    .slide-confirm-thumb {
        position: absolute;
        left: 0;
        top: 0;
        width: 50px;
        height: 50px;
        background: #007bff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        cursor: grab;
        z-index: 3;
        transition: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .slide-confirm-thumb:active {
        cursor: grabbing;
    }

    .slide-confirm-progress {
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        background: #28a745;
        border-radius: 30px;
        width: 0;
        z-index: 0;
        transition: none;
    }

    .slide-confirm-track.confirmed .slide-confirm-thumb {
        background: #28a745;
        left: calc(100% - 50px) !important;
    }

    .slide-confirm-track.confirmed .slide-confirm-progress {
        width: 100% !important;
    }

    .slide-confirm-track.confirmed .slide-confirm-text {
        color: #fff;
    }
</style>

<script>
    function goingBack() {
        window.location.href = 'partner-delivery-order';
    }
    var capturedPhotos = [];
    var isLocked = {{ !empty($locked) ? 'true' : 'false' }};
    var webcamStream = null;
    var isDragging = false;
    var dragStartX = 0;
    var thumbStartLeft = 0;

    function startWebcam() {
        var video = document.getElementById('webcam');
        if (!video) return;
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({
                    video: true
                })
                .then(function(stream) {
                    webcamStream = stream;
                    video.srcObject = stream;
                })
                .catch(function(err) {
                    console.log('Webcam error:', err);
                });
        }
    }

    function stopWebcam() {
        if (webcamStream) {
            webcamStream.getTracks().forEach(function(track) {
                track.stop();
            });
            webcamStream = null;
        }
    }

    function renderGallery() {
        var container = document.getElementById('photoGallery');
        container.innerHTML = '';
        capturedPhotos.forEach(function(photo, index) {
            var col = document.createElement('div');
            col.className = 'col-4 col-md-3 mb-2';
            col.innerHTML = '<div class="position-relative" style="border:1px solid #ddd;border-radius:4px;overflow:hidden;">' +
                '<img src="' + photo + '" style="width:100%;height:120px;object-fit:cover;" />' +
                '<button type="button" class="btn btn-sm btn-danger position-absolute" style="top:2px;right:2px;padding:0 6px;" onclick="deletePhoto(' + index + ')">&times;</button>' +
                '</div>';
            container.appendChild(col);
        });
        document.getElementById('capturedPhotos').value = capturedPhotos.join(',');
    }

    function deletePhoto(index) {
        capturedPhotos.splice(index, 1);
        renderGallery();
    }

    function dataURLToBlob(dataUrl) {
        var parts = dataUrl.split(',');
        var mime = parts[0].match(/:(.*?);/)[1];
        var bytes = atob(parts[1]);
        var ab = new ArrayBuffer(bytes.length);
        var ia = new Uint8Array(ab);
        for (var i = 0; i < bytes.length; i++) ia[i] = bytes.charCodeAt(i);
        return new Blob([ab], { type: mime });
    }

    function submitForm() {
        if (isLocked) { return; }
        var form = document.getElementById('UpdateReceiptForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            resetSlide();
            return;
        }

        var payload = new FormData(form);
        capturedPhotos.forEach(function(dataUrl) {
            var blob = dataURLToBlob(dataUrl);
            payload.append('weighing_photos[]', blob, 'weighing_' + Date.now() + '.png');
        });

        $.ajax({
            url: 'partner-delivery-order-update-submit',
            type: 'POST',
            data: payload,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.status === 'success') {
                    $('#slideConfirmText').text('Terkonfirmasi!');
                    setTimeout(function() {
                        alert('Data berhasil disimpan!');
                        form.reset();
                        capturedPhotos = [];
                        renderGallery();
                        $('#Lat').val('');
                        $('#Lng').val('');
                        $('#gpsStatus').text('');
                        resetSlide();
                        $('.sig-wrap').each(function() {
                            var sid = $(this).data('sig-id');
                            if (sid) {
                                var c = document.getElementById('sig_canvas_' + sid);
                                if (c) {
                                    var ctx = c.getContext('2d');
                                    ctx.clearRect(0, 0, c.width, c.height);
                                }
                                $('#sig_input_' + sid).val('');
                            }
                        });
                    }, 500);
                } else {
                    alert('Gagal: ' + (res.message || 'Unknown error'));
                    resetSlide();
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                resetSlide();
            }
        });
    }

    function resetSlide() {
        var track = document.getElementById('slideConfirmTrack');
        track.classList.remove('confirmed');
        $('#slideConfirmThumb').css('left', 0);
        $('#slideConfirmProgress').css('width', 0);
        $('#slideConfirmText').text('Geser untuk konfirmasi');
        isDragging = false;
    }

    $(document).ready(function() {
        if (isLocked) {
            $('#UpdateReceiptForm').find('input, select, textarea, button').prop('disabled', true);
            $('#slideConfirmTrack').css('pointer-events', 'none');
            $('#slideConfirmText').text('Read-only');
            return;
        }
        startWebcam();

        var urlParams = new URLSearchParams(window.location.search);
        var poId = urlParams.get('po_id');
        if (poId) {
            $('#PO_Id').val(poId);
        }

        $('#btnGetGPS').on('click', function() {
            if (!navigator.geolocation) {
                $('#gpsStatus').text('Geolocation not supported by this browser.').css('color', 'red');
                return;
            }
            $('#gpsStatus').text('Getting location...').css('color', 'blue');
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    $('#Lat').val(position.coords.latitude);
                    $('#Lng').val(position.coords.longitude);
                    $('#gpsStatus').text('Location acquired.').css('color', 'green');
                },
                function(error) {
                    $('#gpsStatus').text('Error: ' + error.message).css('color', 'red');
                }, {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        });

        $('#btnCapture').on('click', function() {
            var video = document.getElementById('webcam');
            var canvas = document.getElementById('webcamCanvas');
            var ctx = canvas.getContext('2d');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            var dataUrl = canvas.toDataURL('image/png');
            capturedPhotos.push(dataUrl);
            renderGallery();
        });

        // Slide-to-confirm drag handlers
        $('#slideConfirmThumb').on('mousedown touchstart', function(e) {
            if ($('#slideConfirmTrack').hasClass('confirmed')) return;
            isDragging = true;
            dragStartX = e.pageX || e.originalEvent.touches[0].pageX;
            thumbStartLeft = parseInt($(this).css('left')) || 0;
            e.preventDefault();
        });

        $(document).on('mousemove touchmove', function(e) {
            if (!isDragging) return;
            var currentX = e.pageX || e.originalEvent.touches[0].pageX;
            var trackWidth = $('#slideConfirmTrack').width();
            var thumbWidth = 50;
            var diff = currentX - dragStartX;
            var newLeft = Math.max(0, Math.min(trackWidth - thumbWidth, thumbStartLeft + diff));
            $('#slideConfirmThumb').css('left', newLeft);
            $('#slideConfirmProgress').css('width', newLeft + thumbWidth / 2);

            if (newLeft >= trackWidth - thumbWidth) {
                isDragging = false;
                $('#slideConfirmTrack').addClass('confirmed');
                $('#slideConfirmText').text('Memproses...');
                submitForm();
            }
        });

        $(document).on('mouseup touchend', function(e) {
            if (isDragging) {
                isDragging = false;
                var trackWidth = $('#slideConfirmTrack').width();
                var thumbWidth = 50;
                var currentLeft = parseInt($('#slideConfirmThumb').css('left')) || 0;
                if (currentLeft < trackWidth - thumbWidth) {
                    resetSlide();
                }
            }
        });
    });
</script>
@endsection