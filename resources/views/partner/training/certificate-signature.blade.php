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
						<input type="hidden" name="signature" id="signature_data1" value="{{ $instanceId1 }}">

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
						<input type="hidden" name="signature" id="signature_data2" value="{{ $instanceId2 }}">
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
						<input type="hidden" name="signature" id="signature_data3" value="{{ $instanceId3 }}">
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

    window.canvasInstances = window.canvasInstances || {};

    (function() {
        const instanceId1 = '{{ $instanceId1 }}';
        const canvasId1 = '{{ $canvasId1 }}';
        const formId1 = '{{ $formId1 }}';
        const finalizeFormId1 = '{{ $finalizeFormId1 }}';
        const signatureDataId1 = 'signature_data_{{ $instanceId1 }}';
        const finalizeSignatureId1 = 'finalize_signature_{{ $instanceId1 }}';

        const instanceId2 = '{{ $instanceId2 }}';
        const canvasId2 = '{{ $canvasId2 }}';
        const formId2 = '{{ $formId2 }}';
        const finalizeFormId2 = '{{ $finalizeFormId2 }}';
        const signatureDataId2 = 'signature_data_{{ $instanceId2 }}';
        const finalizeSignatureId2 = 'finalize_signature_{{ $instanceId2 }}';

        const instanceId3 = '{{ $instanceId3 }}';
        const canvasId3 = '{{ $canvasId3 }}';
        const formId3 = '{{ $formId3 }}';
        const finalizeFormId3 = '{{ $finalizeFormId3 }}';
        const signatureDataId3 = 'signature_data_{{ $instanceId3 }}';
        const finalizeSignatureId3 = 'finalize_signature_{{ $instanceId3 }}';

        // Get canvas element
        const canvas1 = document.getElementById(canvasId1);
        const canvas2 = document.getElementById(canvasId2);
        const canvas3 = document.getElementById(canvasId3);
        if (canvas1) {

			const ctx1 = canvas1.getContext('2d');
	
			// Drawing state (instance-specific)
			let drawing = false;
			let currentColor = 'black';
	
			// Set initial drawing style
			ctx1.lineWidth = 5;
			ctx1.lineCap = 'round';
			ctx1.lineJoin = 'round';
			ctx1.strokeStyle = currentColor;
	
			// Drawing functions
			function startDrawing1(e) {
				drawing = true;
				ctx1.beginPath();
				const rect = canvas1.getBoundingClientRect();
				const x = (e.clientX || e.touches[0].clientX) - rect.left;
				const y = (e.clientY || e.touches[0].clientY) - rect.top;
				ctx1.moveTo(x, y);
			}
	
			function draw1(e) {
				if (!drawing) return;
				e.preventDefault();
				const rect = canvas1.getBoundingClientRect();
				const x = (e.clientX || e.touches[0].clientX) - rect.left;
				const y = (e.clientY || e.touches[0].clientY) - rect.top;
				ctx1.lineTo(x, y);
				ctx1.stroke();
				ctx1.beginPath();
				ctx1.moveTo(x, y);
			}
	
			function stopDrawing1() {
				drawing = false;
			}
	
			function clearCanvas1() {
				ctx1.clearRect(0, 0, canvas1.width, canvas1.height);
				showStatus('Canvas cleared', 'info');
			}
	
			function changeColor1(color) {
				currentColor = color;
				ctx1.strokeStyle = color;
			}
	
			function saveDrawing1() {
				const imageData1 = canvas1.toDataURL('image/png');
				$(`#${signatureDataId1}`).val(imageData1);
				$(`#${formId1}`).submit();
			}
	
			function finalizeDocument1() {
				const imageData = canvas1.toDataURL('image/png');
				$(`#${finalizeSignatureId1}`).val(imageData);
				console.log('Finalizing for instance:', instanceId1);
				$(`#${finalizeFormId1}`).submit();
			}
	
			function showStatus1(message, type) {
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
			canvas1.addEventListener('mousedown', startDrawing1);
			canvas1.addEventListener('mousemove', draw1);
			canvas1.addEventListener('mouseup', stopDrawing1);
			canvas1.addEventListener('mouseleave', stopDrawing1);
			canvas1.addEventListener('touchstart', startDrawing1);
			canvas1.addEventListener('touchmove', draw1);
			canvas1.addEventListener('touchend', stopDrawing1);
	
			window.canvasInstances[instanceId1] = {
				clear: clearCanvas1,
				save: saveDrawing1,
				finalize: finalizeDocument1,
				changeColor: changeColor1,
				canvas: canvas1,
				ctx: ctx1
			};
		}
		if (canvas2) {
			// Similar setup for canvas2 with its own context and functions
			const ctx2 = canvas2.getContext('2d');
	
			// Drawing state (instance-specific)
			let drawing = false;
			let currentColor = 'black';
	
			// Set initial drawing style
			ctx2.lineWidth = 5;
			ctx2.lineCap = 'round';
			ctx2.lineJoin = 'round';
			ctx2.strokeStyle = currentColor;
	
			// Drawing functions
			function startDrawing2(e) {
				drawing = true;
				ctx2.beginPath();
				const rect = canvas2.getBoundingClientRect();
				const x = (e.clientX || e.touches[0].clientX) - rect.left;
				const y = (e.clientY || e.touches[0].clientY) - rect.top;
				ctx2.moveTo(x, y);
			}
	
			function draw2(e) {
				if (!drawing) return;
				e.preventDefault();
				const rect = canvas2.getBoundingClientRect();
				const x = (e.clientX || e.touches[0].clientX) - rect.left;
				const y = (e.clientY || e.touches[0].clientY) - rect.top;
				ctx2.lineTo(x, y);
				ctx2.stroke();
				ctx2.beginPath();
				ctx2.moveTo(x, y);
			}
	
			function stopDrawing2() {
				drawing = false;
			}
	
			function clearCanvas2() {
				ctx2.clearRect(0, 0, canvas2.width, canvas2.height);
				showStatus('Canvas cleared', 'info');
			}
	
			function changeColor2(color) {
				currentColor = color;
				ctx2.strokeStyle = color;
			}
	
			function saveDrawing2() {
				const imageData2 = canvas2.toDataURL('image/png');
				$(`#${signatureDataId2}`).val(imageData2);
				$(`#${formId2}`).submit();
			}
	
			function finalizeDocument2() {
				const imageData = canvas2.toDataURL('image/png');
				$(`#${finalizeSignatureId2}`).val(imageData);
				console.log('Finalizing for instance:', instanceId2);
				$(`#${finalizeFormId2}`).submit();
			}
	
			function showStatus2(message, type) {
				const statusDiv = document.getElementById(`status_${instanceId2}`);
				if (!statusDiv) return;
				statusDiv.textContent = message;
				statusDiv.style.color = type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue';
				statusDiv.style.display = 'block';
				setTimeout(() => {
					statusDiv.style.display = 'none';
				}, 3000);
			}
	
			// Add event listeners
			canvas2.addEventListener('mousedown', startDrawing2);
			canvas2.addEventListener('mousemove', draw2);
			canvas2.addEventListener('mouseup', stopDrawing2);
			canvas2.addEventListener('mouseleave', stopDrawing2);
			canvas2.addEventListener('touchstart', startDrawing2);
			canvas2.addEventListener('touchmove', draw2);
			canvas2.addEventListener('touchend', stopDrawing2);
	
			window.canvasInstances[instanceId2] = {
				clear: clearCanvas2,
				save: saveDrawing2,
				finalize: finalizeDocument2,
				changeColor: changeColor2,
				canvas: canvas2,
				ctx: ctx2
			};
		}
		if (canvas3) {
			// Similar setup for canvas3 with its own context and functions
			const ctx3 = canvas3.getContext('2d');
	
			// Drawing state (instance-specific)
			let drawing = false;
			let currentColor = 'black';
	
			// Set initial drawing style
			ctx3.lineWidth = 5;
			ctx3.lineCap = 'round';
			ctx3.lineJoin = 'round';
			ctx3.strokeStyle = currentColor;
	
			// Drawing functions
			function startDrawing3(e) {
				drawing = true;
				ctx3.beginPath();
				const rect = canvas3.getBoundingClientRect();
				const x = (e.clientX || e.touches[0].clientX) - rect.left;
				const y = (e.clientY || e.touches[0].clientY) - rect.top;
				ctx3.moveTo(x, y);
			}
	
			function draw3(e) {
				if (!drawing) return;
				e.preventDefault();
				const rect = canvas3.getBoundingClientRect();
				const x = (e.clientX || e.touches[0].clientX) - rect.left;
				const y = (e.clientY || e.touches[0].clientY) - rect.top;
				ctx3.lineTo(x, y);
				ctx3.stroke();
				ctx3.beginPath();
				ctx3.moveTo(x, y);
			}
	
			function stopDrawing3() {
				drawing = false;
			}
	
			function clearCanvas3() {
				ctx3.clearRect(0, 0, canvas3.width, canvas3.height);
				showStatus('Canvas cleared', 'info');
			}
	
			function changeColor3(color) {
				currentColor = color;
				ctx3.strokeStyle = color;
			}
	
			function saveDrawing3() {
				const imageData3 = canvas3.toDataURL('image/png');
				$(`#${signatureDataId3}`).val(imageData3);
				$(`#${formId3}`).submit();
			}
	
			function finalizeDocument3() {
				const imageData = canvas3.toDataURL('image/png');
				$(`#${finalizeSignatureId3}`).val(imageData);
				console.log('Finalizing for instance:', instanceId3);
				$(`#${finalizeFormId3}`).submit();
			}
	
			function showStatus3(message, type) {
				const statusDiv = document.getElementById(`status_${instanceId3}`);
				if (!statusDiv) return;
				statusDiv.textContent = message;
				statusDiv.style.color = type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue';
				statusDiv.style.display = 'block';
				setTimeout(() => {
					statusDiv.style.display = 'none';
				}, 3000);
			}
	
			// Add event listeners
			canvas3.addEventListener('mousedown', startDrawing3);
			canvas3.addEventListener('mousemove', draw3);
			canvas3.addEventListener('mouseup', stopDrawing3);
			canvas3.addEventListener('mouseleave', stopDrawing3);
			canvas3.addEventListener('touchstart', startDrawing3);
			canvas3.addEventListener('touchmove', draw3);
			canvas3.addEventListener('touchend', stopDrawing3);
	
			window.canvasInstances[instanceId3] = {
				clear: clearCanvas3,
				save: saveDrawing3,
				finalize: finalizeDocument3,
				changeColor: changeColor3,
				canvas: canvas3,
				ctx: ctx3
			};
		}


		$('#SubmitCustDetail').click(function() {
			const imageData1 = canvas1.toDataURL('image/png');
			$(`#${signatureDataId1}`).val(imageData1);

			const imageData2 = canvas2.toDataURL('image/png');
			$(`#${signatureDataId2}`).val(imageData2);

			const imageData3 = canvas3.toDataURL('image/png');
			$(`#${signatureDataId3}`).val(imageData3);

			gettingVars = getAllValues();

			let fd = new FormData();
			fd.append('data', JSON.stringify(gettingVars));

			fd.append('signature1_upload', document.getElementById('signature1_upload').files[0]);
			fd.append('signature2_upload', document.getElementById('signature2_upload').files[0]);
			fd.append('signature3_upload', document.getElementById('signature3_upload').files[0]);

			$.ajax({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				url: "partner-training-certificate-signature-submit?_token={{ csrf_token() }}",
				type: "POST",
				data: fd,
				processData: false,
				contentType: false,
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