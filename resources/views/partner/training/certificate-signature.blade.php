<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$instanceId1 = uniqid('canvas_');
$canvasId1 = "signature_canvas_{$instanceId1}";
$formId1 = "drawing_form_{$instanceId1}";
$finalizeFormId1 = "finalize_form_{$instanceId1}";

$instanceId2 = uniqid('canvas_');
$canvasId2 = "signature_canvas_{$instanceId2}";
$formId2 = "drawing_form_{$instanceId2}";
$finalizeFormId2 = "finalize_form_{$instanceId2}";

$instanceId3 = uniqid('canvas_');
$canvasId3 = "signature_canvas_{$instanceId3}";
$formId3 = "drawing_form_{$instanceId3}";
$finalizeFormId3 = "finalize_form_{$instanceId3}";
?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Training</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">List Training</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LIST TRAINING BASED ON TARGET</div>
	<div class="card">
		<div class="card-body" id="ContentPages">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="signature1_name">Signature 1 Name</label>
                    <input type="text" class="form-control" id="signature1_name" name="signature1_name" value="{{ $sign->signature1_name ?? '' }}">
                </div>
                <div class="col-md-4 mb-3">
					<div class="row">
						<div class="col-8">
							<label for="signature1_position">Signature 1 Position</label>
							<input type="text" class="form-control" id="signature1_position" name="signature1_position" value="{{ $sign->signature1_position ?? '' }}">
						</div>
						<div class="col-4">
							  <div class="custom-control custom-radio">
								<input type="radio" class="custom-control-input" id="signature1_type1" name="signature1_type" value="upload" checked>
								<label class="custom-control-label" for="signature1_type1">Upload Tanda tangan</label>
							</div>
							<div class="custom-control custom-radio mb-3">
								<input type="radio" class="custom-control-input" id="signature1_type2" name="signature1_type" value="draw">
								<label class="custom-control-label" for="signature1_type2">Tanda Tangan Langsung</label>
								<div class="invalid-feedback">More example invalid feedback text</div>
							</div>
						</div>
					</div>
                </div>
                <div class="col-md-10 mb-3" id="div_signature1_draw" style="display: none;">
                    <label for="signature1_draw">Signature 1 Draw</label>
					<div class="signature-canvas-wrapper" data-instance-id="{{ $instanceId1 }}">
						<canvas
							id="{{ $canvasId1 }}"
							width="800"
							height="500"
							style="border:2px solid black; cursor:crosshair;"></canvas>

						<div style="margin-top: 10px;">
							<button onclick="window.canvasInstances['{{ $instanceId1 }}'].clear()">Clear</button>
						</div>
						
						{{-- Hidden forms for this instance --}}
						<input type="hidden" name="signature" id="signature_data_{{ $instanceId1 }}">

						<div id="status_{{ $instanceId1 }}" style="margin-top: 10px; display: none;"></div>
					</div>
                </div>
                <div class="col-md-4 mb-3" id="div_signature1_upload" style="display: block;">
                    <label for="signature1_upload">Signature 1 Upload</label>
					<div>
						<input type="file" class="custom-file-input" id="signature1_upload" name="signature1_upload" value="{{ $sign->signature1_upload ?? '' }}">
						<label class="custom-file-label" style="top: auto;margin: 0 12px" for="signature1_upload">Choose file...</label>
						<div class="invalid-feedback">Invalid File</div>
					</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="signature2_name">Signature 2 Name</label>
                    <input type="text" class="form-control" id="signature2_name" name="signature2_name" value="{{ $sign->signature2_name ?? '' }}">
                </div>
                <div class="col-md-4 mb-3">
					<div class="row">
						<div class="col-8">
							<label for="signature2_position">Signature 2 Position</label>
							<input type="text" class="form-control" id="signature2_position" name="signature2_position" value="{{ $sign->signature2_position ?? '' }}">
						</div>
						<div class="col-4">
							  <div class="custom-control custom-radio">
								<input type="radio" class="custom-control-input" id="signature2_type1" name="signature2_type" value="upload" checked>
								<label class="custom-control-label" for="signature2_type1">Upload Tanda tangan</label>
							</div>
							<div class="custom-control custom-radio mb-3">
								<input type="radio" class="custom-control-input" id="signature2_type2" name="signature2_type" value="draw">
								<label class="custom-control-label" for="signature2_type2">Tanda Tangan Langsung</label>
								<div class="invalid-feedback">More example invalid feedback text</div>
							</div>
						</div>
					</div>
                </div>
                <div class="col-md-10 mb-3" id="div_signature2_draw" style="display: none;">
                    <label for="signature2_draw">Signature 2 Draw</label>
					<div class="signature-canvas-wrapper" data-instance-id="{{ $instanceId2 }}">
						<canvas
							id="{{ $canvasId2 }}"
							width="800"
							height="500"
							style="border:2px solid black; cursor:crosshair;"></canvas>

						<div style="margin-top: 10px;">
							<button onclick="window.canvasInstances['{{ $instanceId2 }}'].clear()">Clear</button>
						</div>
						
						{{-- Hidden forms for this instance --}}
						<input type="hidden" name="signature" id="signature_data_{{ $instanceId2 }}">
						<div id="status_{{ $instanceId2 }}" style="margin-top: 10px; display: none;"></div>
					</div>
                </div>
                <div class="col-md-4 mb-3" id="div_signature2_upload" style="display: block;">
                    <label for="signature2_upload">Signature 2 Upload</label>
					<div>
						<input type="file" class="custom-file-input" id="signature2_upload" name="signature2_upload" value="{{ $sign->signature2_upload ?? '' }}">
						<label class="custom-file-label" style="top: auto;margin: 0 12px" for="signature2_upload">Choose file...</label>
						<div class="invalid-feedback">Invalid File</div>
					</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="signature3_name">Signature 3 Name</label>
                    <input type="text" class="form-control" id="signature3_name" name="signature3_name" value="{{ $sign->signature3_name ?? '' }}">
                </div>
                <div class="col-md-4 mb-3">
					<div class="row">
						<div class="col-8">
							<label for="signature3_position">Signature 3 Position</label>
							<input type="text" class="form-control" id="signature3_position" name="signature3_position" value="{{ $sign->signature3_position ?? '' }}">
						</div>
						<div class="col-4">
							  <div class="custom-control custom-radio">
								<input type="radio" class="custom-control-input" id="signature3_type1" name="signature3_type" value="upload" checked>
								<label class="custom-control-label" for="signature3_type1">Upload Tanda tangan</label>
							</div>
							<div class="custom-control custom-radio mb-3">
								<input type="radio" class="custom-control-input" id="signature3_type2" name="signature3_type" value="draw">
								<label class="custom-control-label" for="signature3_type2">Tanda Tangan Langsung</label>
								<div class="invalid-feedback">More example invalid feedback text</div>
							</div>
						</div>
					</div>
                </div>
                <div class="col-md-10 mb-3" id="div_signature3_draw" style="display: none;">
                    <label for="signature3_draw">Signature 3 Draw</label>
					<div class="signature-canvas-wrapper" data-instance-id="{{ $instanceId3 }}">
						<canvas
							id="{{ $canvasId3 }}"
							width="800"
							height="500"
							style="border:2px solid black; cursor:crosshair;"></canvas>

						<div style="margin-top: 10px;">
							<button onclick="window.canvasInstances['{{ $instanceId3 }}'].clear()">Clear</button>
						</div>
						
						{{-- Hidden forms for this instance --}}
						<input type="hidden" name="signature" id="signature_data_{{ $instanceId3 }}">
						<div id="status_{{ $instanceId3 }}" style="margin-top: 10px; display: none;"></div>
					</div>
                </div>
                <div class="col-md-4 mb-3" id="div_signature3_upload" style="display: block;">
                    <label for="signature3_upload">Signature 3 Upload</label>
					<div>
						<input type="file" class="custom-file-input" id="signature3_upload" name="signature3_upload" value="{{ $sign->signature3_upload ?? '' }}">
						<label class="custom-file-label" style="top: auto;margin: 0 12px" for="signature3_upload">Choose file...</label>
						<div class="invalid-feedback">Invalid File</div>
					</div>
                </div>
                <div class="col-12">
                    <button class="btn btn-success w-100" id="SubmitCustDetail">Submit</button>
                </div>
            </div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	$('input[name="signature1_type"]').change(function() {
		if ($(this).val() == 'upload') {
			$('#div_signature1_draw').hide();
			$('#div_signature1_upload').show('swing');
		} else {
			$('#div_signature1_draw').show('swing');
			$('#div_signature1_upload').hide();
		}
	})
	$('input[name="signature3_type"]').change(function() {
		if ($(this).val() == 'upload') {
			$('#div_signature3_draw').hide();
			$('#div_signature3_upload').show('swing');
		} else {
			$('#div_signature3_draw').show('swing');
			$('#div_signature3_upload').hide();
		}
	})
	$('input[name="signature2_type"]').change(function() {
		if ($(this).val() == 'upload') {
			$('#div_signature2_draw').hide();
			$('#div_signature2_upload').show('swing');
		} else {
			$('#div_signature2_draw').show('swing');
			$('#div_signature2_upload').hide();
		}
	})

	$('#SubmitCustDetail').click(function() {
		const imageData = canvas.toDataURL('image/png');
		$(`#${signatureDataId}`).val(imageData);

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-training-certificate-signature-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.status;
				if (dataPos == 'success') {
					// var cariAWB = data.split(":");
					alert('Training Application Submitted!');
                    refreshdata();
                    $('#divlistOpportunity').html('');
                    window.reload();
				} else {
					alert('Application Failed to Submit!');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				alert("Error Internal! Please Try again!");

			}
		});
	})

	function getAllValues() {
		var inputValues = {};
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues[$(this).attr("name")] = $(this).val();
			} else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
				inputValues[$(this).attr("id")] = $(this).val();
			} else if ($(this).is("textarea")) {
				inputValues[$(this).attr("id")] = $(this).val();
			}
		})
		return inputValues;
	}
    window.canvasInstances = window.canvasInstances || {};

    (function() {
        const instanceId1 = '{{ $instanceId1 }}';
        const canvasId1 = '{{ $canvasId1 }}';
        const formId1 = '{{ $formId1 }}';
        const finalizeFormId1 = '{{ $finalizeFormId1 }}';
        const signatureDataId = 'signature_data_{{ $instanceId1 }}';
        const finalizeSignatureId = 'finalize_signature_{{ $instanceId1 }}';

        // Get canvas element
        const canvas = document.getElementById(canvasId1);
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
            $(`#${formId1}`).submit();
        }

        function finalizeDocument() {
            const imageData = canvas.toDataURL('image/png');
            $(`#${finalizeSignatureId}`).val(imageData);
            console.log('Finalizing for instance:', instanceId1);
            $(`#${finalizeFormId1}`).submit();
        }

        function showStatus(message, type) {
            const statusDiv = document.getElementById(`status_${instanceId1}`);
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

        window.canvasInstances[instanceId1] = {
            clear: clearCanvas,
            save: saveDrawing,
            finalize: finalizeDocument,
            changeColor: changeColor,
            canvas: canvas,
            ctx: ctx
        };

        // Handle form submissions for this instance
        $(`#${formId1}`).on('submit', function(e) {
            e.preventDefault();
            let fd = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(response) {
                    showStatus('Document generated successfully!', 'success');
                    // $().show()
                },
                error: function(xhr) {
                    showStatus('Error generating document', 'error');
                }
            });
        });

        $(`#${finalizeFormId1}`).on('submit', function(e) {
            e.preventDefault();
            let fd = new FormData(this);

            $.ajax({
                url: $(this).attr('action'),
                type: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response)
                    showStatus('Document finalized!', 'success');
                },
                error: function(xhr) {
                    showStatus('Error finalizing document', 'error');
                }
            });
        });
    })();
</script>
@endsection