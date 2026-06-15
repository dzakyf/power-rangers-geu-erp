<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();

?>
<div class="card mt-3">
	<div class="card-body" id="ContentPages">
		<form id="masterAuditForm">
			<div class="row">
				<div class="col-md-4">
					<div class="form-group">
						<label for="category">Category</label>
						<input type="text" name="category" id="category" class="form-control">
					</div>
				</div>
				<div class="col-md-8">
					<div class="form-group">
						<label for="category_descriptoin">Category Descriptoin</label>
						<input type="text" name="category_description" id="category_description" class="form-control">
					</div>
				</div>
				<div class="col-12" id="divParameterList">
					<div class="row">
						<div class="col-5">
							<div class="form-group">
								<label for="parameter1">Parameter</label>
								<input type="text" name="parameter[]" id="parameter1" data-id="1" class="form-control">
							</div>
						</div>
						<div class="col-5">
							<div class="form-group">
								<label for="score1">Score</label>
								<input type="text" name="score[]" id="score1" data-id="1" class="form-control">
							</div>
						</div>
						<div class="col-1">
							<div class="form-group">
								<label for="">&nbsp;</label>
								<button class="btn btn-primary w-100" id="plusParameter" type="button"><i class="lni lni-plus"></i></button>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12">
					<button class="btn btn-success w-100" id="SubmitCustDetail" type="button">Submit</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	count = 2;

	// Prevent comma in parameter and score fields
	$(document).on('keydown', 'input[name="parameter[]"], input[name="score[]"]', function(e) {
		if (e.key === ',' || e.keyCode === 188) {
			e.preventDefault();
			return false;
		}
	});
	$(document).on('input', 'input[name="parameter[]"], input[name="score[]"]', function() {
		if (this.value.indexOf(',') !== -1) {
			this.value = this.value.replace(/,/g, '');
		}
	});
	$('#plusParameter').on('click', function() {
		$('#divParameterList').append(`
				<div class="row" id="divParameter${count}">
					<div class="col-5">
						<div class="form-group">
							<label for="parameter${count}">Parameter ${count}</label>
							<input type="text" name="parameter[]" id="parameter${count}" data-id="${count}" class="form-control">
						</div>
					</div>
					<div class="col-5">
						<div class="form-group">
							<label for="score${count}">Score ${count}</label>
							<input type="text" name="score[]" id="score${count}" data-id="${count}" class="form-control">
						</div>
					</div>
					<div class="col-1">
						<div class="form-group">
							<label for="">&nbsp;</label>
							<button class="btn btn-danger w-100" id="minusParameter" data-id="${count}" type="button"><i class="lni lni-minus"></i></button>
						</div>
					</div>
				</div>
		`);
		count++;
	})

	$('#SubmitCustDetail').click(function() {

		for (let i = 1; i < count; i++) {
			let param = $('#parameter' + i).val();
			let score = $('#score' + i).val();

			if (param == '' || score == '') {
				dhtmlx.alert(`Parameter ${i} and Score ${i} must be filled!`);
				if (param == '') {
					$('#parameter' + i).focus();
				} else {
					$('#score' + i).focus();
				}
				return;
			}
		}


		var formData = new FormData(document.getElementById('masterAuditForm'));

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-master-audit-category-new-submit",
			type: "POST",
			data: formData,
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
</script>