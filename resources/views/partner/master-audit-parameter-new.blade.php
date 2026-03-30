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
					<select name="category" id="category" class="form-control">
						<option value="">-- Pilih Category --</option>
						@foreach ($category as $item)
						<option value="{{ $item->category }}">{{ $item->category }} - {{ $item->category_description }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="col-md-8">
				<div class="form-group">
					<label for="parameter">Parameter</label>
					<input type="text" name="parameter" id="parameter" class="form-control">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label for="parameter_minor">Minor Case</label>
					<input type="text" name="parameter_minor" id="parameter_minor" class="form-control">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label for="parameter_major">Major Case</label>
					<input type="text" name="parameter_major" id="parameter_major" class="form-control">
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label for="parameter_critical">Critical Case</label>
					<input type="text" name="parameter_critical" id="parameter_critical" class="form-control">
				</div>
			</div>
			<div class="col-12">
				<button class="btn btn-success w-100" id="SubmitCustDetail">Submit</button>
			</div>
		</div>
	</div>
</div>
<script>
	$('#SubmitCustDetail').click(function() {
		
		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-master-audit-parameter-new-submit?_token={{ csrf_token() }}",
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