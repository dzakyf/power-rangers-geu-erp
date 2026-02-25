<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Partner</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Approval</span>
		</li>
	</ol>
</div>


<div class="mt-5 mb-5 p-4 bg-white shadow-sm">
	<div class="bs-stepper" id="customStepper">
		<div class="bs-stepper-header" role="tablist">
			<div class="step" data-target="#step1">
				<button type="button" class="step-trigger" role="tab">
					<span class="bs-stepper-circle">1</span>
					<span class="bs-stepper-label">Cek Dokumen</span>
				</button>
			</div>
			<div class="line"></div>
			<div class="step" data-target="#step2">
				<button type="button" class="step-trigger" role="tab">
					<span class="bs-stepper-circle">2</span>
					<span class="bs-stepper-label">Penandatanganan MOU</span>
				</button>
			</div>

			<div class="line"></div>

			<div class="step" data-target="#step3">
				<button type="button" class="step-trigger" role="tab">
					<span class="bs-stepper-circle">3</span>
					<span class="bs-stepper-label">Penerbitan Invoice</span>
				</button>
			</div>
		</div>

		<div class="bs-stepper-content">
			<div id="step1" class="content" role="tabpanel">

				@include('approval.stepper.detail')

			</div>

			<div id="step2" class="content" role="tabpanel">
				@include('approval.stepper.mou')
			</div>

			<div id="step3" class="content" role="tabpanel">
				@include('approval.stepper.invoice-dp')
			</div>
		</div>
	</div>
</div>

<!-- Current Step Display -->
<!-- <div class="mt-2" id="stepDisplay"></div> -->
</div>

<div id="divlistOpportunity"></div>
<div></div>
<div id="divlistProjectList"></div>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
</script>

<script>
	$(document).ready(function() {

		$('#divlistOpportunity').show()

		var stepperElement = $('.bs-stepper')[0];
		window.stepper = new Stepper(stepperElement, {
			linear: true,
			animation: true
		});

		function updateStepDisplay() {
			var currentIndex = window.stepper._currentIndex + 1; // 1-based for display
			$('#stepDisplay').html(`
				<div class="alert alert-info">
					Current Step: ${currentIndex} of 3
				</div>
			`);
		}

		// Initial display
		updateStepDisplay();

		// Event listener for step changes
		stepperElement.addEventListener('shown.bs-stepper', function(event) {
			console.log('Moved to step:', event.detail.indexStep);
			updateStepDisplay();
		});



		$('.btn-next').on('click', function() {
			window.stepper.next();
		});

		$('.btn-prev').on('click', function() {
			window.stepper.previous();
		});

		$('#goToStep2').on('click', function() {
			window.stepper.to(2); // Goes to step 2 (1-based index)
		});

		$('#resetStepper').on('click', function() {
			window.stepper.reset();
			showNotification('Stepper reset to step 1');
		});

		$('#destroyStepper').on('click', function() {
			window.stepper.destroy();
			$('#stepDisplay').html(`
				<div class="alert alert-danger">
					Stepper destroyed - methods disabled
				</div>
			`);
			$(this).prop('disabled', true);
		});

		$('#getCurrentStep').on('click', function() {
			var currentIndex = window.stepper._currentIndex + 1;
			alert('Current step: ' + currentIndex);
		});

		// Helper function for notifications
		function showNotification(message) {
			var notification = $(`
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					${message}
					<button type="button" class="close" data-dismiss="alert">
						<span>&times;</span>
					</button>
				</div>
			`);

			$('#stepDisplay').after(notification);
			setTimeout(function() {
				notification.alert('close');
			}, 2000);
		}
	});

	function submitApproval(type) {
		let fd = new FormData()
		let id = window.location.pathname.split('/')
		// return
		fd.append('id', id[6]);
		fd.append('type', type)
		fd.append('_token', '{{ csrf_token() }}')

		$.ajax({
			url: "submit",
			type: "POST",
			processData: false,
			contentType: false,
			data: fd,
			success: function(response, status, xhr) {
				alert("Berhasil melakukan aksi");
				window.location.reload()
			},
			error: function(err) {
				alert("Telah terjadi kesalahan.");
			}
		})
	}

	$('.downloadMOU').on('click', function() {
		generateMOU()
	})

	$('.uploadMOU').on('click', function() {
		console.log("uploading mou")
	})

	function generateInvoice() {

		$.ajax({
			url: `/geu/hcga/public/partner-prints-invoice`,
			type: "GET",
			success: function(response, status) {
				console.log(response)
				window.open('https://dev.hexamatika.com/geu/hcga/public/partner-prints-invoice', '_blank')
			},
			error: function(err) {
				console.log(err)
			}

		})
	}

	function generateMOU() {
		const pathSegments = window.location.pathname.split('/');
		const id = pathSegments[pathSegments.length - 1];

		$.ajax({
			url: `generate-mou?id=${id}`,
			type: "GET",
			xhrFields: {
				responseType: 'blob'
			},
			success: function(response, status, xhr) {
				var blob = new Blob([response]);
				var link = document.createElement('a');
				link.href = window.URL.createObjectURL(blob);
				link.download = 'mou.docx';
				link.click();

				window.URL.revokeObjectURL(link.href);
			},
			error: function(err) {
				alert("Telah terjadi kesalahan.");
			}
		})
	}
</script>
@endsection