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

<div class="row">
	<div id="OpportunityLists" class="m-3" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>

	<div id="ApprovalDataLists" class="m-3" style="width:auto;height:320px; background-color:white;overflow:hidden">
	</div>

	<div id="ApprovalDataLogLists" class="m-3" style="width:auto;height:320px; background-color:white;overflow:hidden">
	</div>
	<div>
		<input type="button" id="ekselOpportunityListsDet" name="ekselOpportunityListsDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OpportunityLists.toExcel('./export/generate.php');" />
		<input type="button" id="showProgress" name="showProgress" value="Show Progress" class="btn btn-info mt-2 mr-2" onclick="redirectToProgress()" />
		<div class="btn-group" role="group">
		</div>
	</div>

	<div class="row m-1">
		<div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
		<div class="row col-md-12" style="width:auto;height:40px">
			<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
				<div class="btn-group" role="group">
					<button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						Actio
					</button>
					<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
						<a class="dropdown-item" href="#" onclick="approveClick('APPROVED')">Approve</a>
						<a class="dropdown-item" href="#" onclick="approveClick('REJECTED')">Reject</a>
					</div>
				</div>
			</div>
		</div>
		<div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mt-2"></div>
	</div>

</div>


<div id="divlistApprovalData"></div>
<div id="divlistApprovalDataLog"></div>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
</script>

<script>
	let mitraId = null
	$(document).ready(function() {
		// Initialize stepper with custom options
		var stepperElement = $('.bs-stepper')[0];
		window.stepper = new Stepper(stepperElement, {
			linear: true, // Force sequential steps
			animation: true // Enable fade animation
		});

		// Update current step display
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

		// =============================================
		// CUSTOM BUTTON CONTROLS
		// =============================================

		// 1. Next button handler
		$('.btn-next').on('click', function() {
			window.stepper.next();
		});

		// 2. Previous button handler
		$('.btn-prev').on('click', function() {
			window.stepper.previous();
		});

		// 3. Go to specific step (using to() method)
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

	function approveClick(action) {

		let formData = new FormData();

		formData.append('id', mitraId);
		formData.append('_token', '{{ csrf_token() }}')
		formData.append('action', action)
		formData.append('type', action)

		$.ajax({
			url: " partner-approval-progress/submit",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			cache: false,
			success: function(response) {
				alert("Berhasil approve");

				// $('#broadcastForm')[0].reset();

			},
			error: function(xhr, status, error) {
				// console.log('Error details:', xhr.responseText);
				alert("Telah terjadi kesalahan. Silakan coba lagi.");
			},
			complete: function() {
				// $('#submitBtn').prop('disabled', false).text('Submit');
			}
		});

	}

	function redirectToProgress() {
		window.location.href = `partner-approval-progress/${OpportunityLists.getSelectedId()}`;
	}

	function submitApproval(type) {
		let fd = new FormData()
		fd.append('id', OpportunityLists.getSelectedId());
		fd.append('type', type)
		fd.append('_token', '{{ csrf_token() }}')

		$.ajax({
			url: "approval/submit",
			type: "POST",
			processData: false,
			contentType: false,
			data: fd,
			success: function(response, status, xhr) {
				alert("Berhasil melakukan aksi");
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

	function ApprovalDataListsClick() {
		initApprovalDataLog()
	}


	function OpportunityListsKlik() {
		OpportunityListsID = OpportunityLists.getSelectedId();
		initApprovalData()
		// $("#divlistApprovalData").show();
		// if (OpportunityListsID != '') {
		// 	$("#divlistProjectList").hide();
		// 	LoadM('divlistOpportunity', 'partner-approval-detail?ID=' + OpportunityListsID);
		// }
		// return true;
	}

	OpportunityLists = new dhtmlXGridObject('OpportunityLists');
	OpportunityLists.setImagePath("./dhtmlx/imgs/");
	OpportunityLists.setHeader("No,Daftar Sebagai, Nama, Alamat, Jumlah Mitra/Bisnis Gudang, Motivasi Bergabung, Pertanyaan 1, Pertanyaan 2, Pertanyaan 3, Pertanyaan 4, Pertanyaan 5, Pertanyaan 6, Paket");
	OpportunityLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// OpportunityLists.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	OpportunityLists.setInitWidths("60,100,150,300,100,120,120,100,100,100,100,100,100");
	OpportunityLists.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
	OpportunityLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	OpportunityLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	OpportunityLists.enableSmartRendering(true, 150);
	// OpportunityLists.setNumberFormat("0,000", 7, ".", ",");
	OpportunityLists.attachEvent("onRowDblClicked", OpportunityListsKlik);
	OpportunityLists.setSkin("dhx_skyblue");
	OpportunityLists.init();
	refreshdata();

	function initApprovalData() {
		ApprovalDataLists = new dhtmlXGridObject('ApprovalDataLists');
		ApprovalDataLists.setImagePath("./dhtmlx/imgs/");
		ApprovalDataLists.setHeader("No, Requested By, Tipe Approval, Status,  Note, Diapprove oleh");
		ApprovalDataLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		// OpportunityLists.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
		ApprovalDataLists.setInitWidths("60,100,150,300,100,120,120,100,100,100,100,100,100");
		ApprovalDataLists.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
		ApprovalDataLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
		ApprovalDataLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		ApprovalDataLists.enableSmartRendering(true, 150);
		// OpportunityLists.setNumberFormat("0,000", 7, ".", ",");
		ApprovalDataLists.attachEvent("onRowDblClicked", ApprovalDataListsClick);
		ApprovalDataLists.setSkin("dhx_skyblue");
		ApprovalDataLists.init();
		ApprovalDataLists.clearAndLoad('partner-onboarding-approval-data?type=ONBOARDING');

		$('#ApprovalDataLists').append('<button>approve</button>')
	}

	function initApprovalDataLog() {
		ApprovalDataLogLists = new dhtmlXGridObject('ApprovalDataLogLists');
		ApprovalDataLogLists.setImagePath("./dhtmlx/imgs/");
		ApprovalDataLogLists.setHeader("No,Daftar Sebagai, Nama, Alamat, Jumlah Mitra/Bisnis Gudang, Motivasi Bergabung, Pertanyaan 1, Pertanyaan 2, Pertanyaan 3, Pertanyaan 4, Pertanyaan 5, Pertanyaan 6, Paket");
		ApprovalDataLogLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		// OpportunityLists.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
		ApprovalDataLogLists.setInitWidths("60,100,150,300,100,120,120,100,100,100,100,100,100");
		ApprovalDataLogLists.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
		ApprovalDataLogLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
		ApprovalDataLogLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		ApprovalDataLogLists.enableSmartRendering(true, 150);
		// OpportunityLists.setNumberFormat("0,000", 7, ".", ",");
		ApprovalDataLogLists.attachEvent("onRowDblClicked", OpportunityListsKlik);
		ApprovalDataLogLists.setSkin("dhx_skyblue");
		ApprovalDataLogLists.init();
		ApprovalDataLogLists.clearAndLoad('partner-onboarding-approval-data-log');
	}

	function refreshdata() {
		OpportunityLists.clearAndLoad('xml-partner-business-lists');

	}

	function delsOpportunity() {
		dhtmlx.confirm('Cancel this Opportunity?', 'Opportunity', function(r) {

			if (r == true) {
				OpportunityLists.deleteSelectedRows();
				POpportunityLists.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}


	function detailBP() {
		OpportunityListsKlik();
		// dhtmlx.confirm('Submit this Opportunity FU?', 'Opportunity', function(r) {

		// 	if (r == true) {
		// 		POpportunityLists.sendData();
		// 		alert('Data Successfully Submitted!');
		// 		setTimeout(function() {
		// 			refreshdata();
		// 		}, 1000);
		// 	}
		// })
	}


	function UpdateGrid_Opportunity() {
		var out = 0;
		for (var i = 0; i < OpportunityLists.getRowsNum(); i++) {
			rowIdMov = OpportunityLists.getRowId(i);
			OpportunityListsProc.setUpdated(rowIdMov, true);
		}
		return out;
	}

	function SubmitGrid_Opportunity() {
		OpportunityListsProc.sendData();
		alert('Data Successfully Updated!');
		OpportunityLists.clearAll();
		refreshdata();
	}

	function EditOpportunity() {
		taskid = OpportunityLists.cells(OpportunityLists.getSelectedId(), 1).getValue();
		LoadM('divlistOpportunity', 'sales-opportunity-lists-detail?ID=' + taskid);
	}

	function LihatDetailOpportunityLists() {
		taskid = OpportunityLists.cells(OpportunityLists.getSelectedId(), 1).getValue();
		LoadM('divlistOpportunity', 'sales-opportunity-activity?taskid=' + taskid);
	}

	function listDoc() {

		DocumentListsDetailID = OpportunityLists.cells(OpportunityLists.getSelectedId(), 12).getValue();

		$("#divlistProjectList").show();

		if (DocumentListsDetailID != '') {
			$("#divlistOpportunity").hide();
			LoadM('divlistProjectList', 'project-documents-detail?Task_ID=' + DocumentListsDetailID);
		}
		return true;
	}
</script>
@endsection