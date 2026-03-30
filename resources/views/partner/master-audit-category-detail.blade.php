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
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label for="category">Category</label>
					<input type="text" name="category" id="category" class="form-control" value="{{ $data->category }}">
				</div>
			</div>
			<div class="col-md-8">
				<div class="form-group">
					<label for="category_descriptoin">Category Descriptoin</label>
					<input type="text" name="category_descriptoin" id="category_descriptoin" class="form-control" value="{{ $data->category_description }}">
				</div>
			</div>
			<div class="col-12" id="divParameterList">
				<div class="row">
					<div class="col-5">
						<div class="form-group">
							<label for="parameter1">Parameter</label>
							<input type="text" name="parameter[]" id="parameter1" data-id="1" class="form-control" value="{{ $parameter[0] }}">
						</div>
					</div>
					<div class="col-5">
						<div class="form-group">
							<label for="score1">Score</label>
							<input type="text" name="score[]" id="score1" data-id="1" class="form-control" value="{{ $score[0] }}">
						</div>
					</div>
					<div class="col-1">
						<div class="form-group">
							<label for="">&nbsp;</label>
							<button class="btn btn-primary w-100" id="plusParameter"><i class="lni lni-plus"></i></button>
						</div>
					</div>
				</div>
				@for ($i = 1; $i < $count; $i++)
				<div class="row" id="divParameter{{ $i + 2 }}">
					<div class="col-5">
						<div class="form-group">
							<label for="parameter{{ $i + 2 }}">Parameter {{ $i + 2 }}</label>
							<input type="text" name="parameter[]" id="parameter{{ $i + 2 }}" data-id="{{ $i + 2 }}" class="form-control" value="{{ $parameter[$i] }}">
						</div>
					</div>
					<div class="col-5">
						<div class="form-group">
							<label for="score{{ $i + 2 }}">Score {{ $i + 2 }}</label>
							<input type="text" name="score[]" id="score{{ $i + 2 }}" data-id="{{ $i + 2 }}" class="form-control" value="{{ $score[$i] }}">
						</div>
					</div>
					<div class="col-1">
						<div class="form-group">
							<label for="">&nbsp;</label>
							<button class="btn btn-danger w-100" id="minusParameter" data-id="{{ $i + 2 }}"><i class="lni lni-minus"></i></button>
						</div>
					</div>
				</div>
				@endfor
			</div>
			<div class="col-12">
				<button class="btn btn-success w-100" id="SubmitCustDetail">Submit</button>
			</div>
		</div>
	</div>
</div>
<script>
	count = 2;
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
							<button class="btn btn-danger w-100" id="minusParameter" data-id="${count}"><i class="lni lni-minus"></i></button>
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
		
		gettingVars = getAllValues();
        gettingVars['count'] = count - 1;

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-master-audit-category-detail-submit?_token={{ csrf_token() }}",
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
</script>