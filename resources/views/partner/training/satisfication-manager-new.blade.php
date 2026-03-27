<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>
<style>
    .radio-lg {
        transform: scale(1.5);
    }
</style>
<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">BUAT PENILAIAN KEPUASAN</div>
	<div class="card">
		<div class="card-body" id="ContentPages">
            <div class="row">
                <div class="col-12">
                    <input type="hidden" name="training_no" id="training_no" value="{{ $training->training_no }}">
                    <input type="hidden" name="training_id" id="training_id" value="{{ $training->id }}">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th rowspan="2">No</th>
                                    <th rowspan="2">Pertanyaan</th>
                                    <th colspan="5">Penilaian</th>
                                    <th rowspan="2">Keterangan</th>
                                </tr>
                                <tr>
                                    <th style="text-align: center;vertical-align:center">1</th>
                                    <th style="text-align: center;vertical-align:center">2</th>
                                    <th style="text-align: center;vertical-align:center">3</th>
                                    <th style="text-align: center;vertical-align:center">4</th>
                                    <th style="text-align: center;vertical-align:center">5</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($masterSatisfication as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td style="font-size:13pt">{{ $item->question }}</td>
                                        <td style="text-align: center;vertical-align:middle;">
                                            <input type="radio" class="radio-lg" name="question{{$item->id}}" id="question{{$item->id}}1" data-counter="{{$item->id}}" value="1">
                                        </td>
                                        <td style="text-align: center;vertical-align:middle;">
                                            <input type="radio" class="radio-lg" name="question{{$item->id}}" id="question{{$item->id}}2" data-counter="{{$item->id}}" value="2">
                                        </td>
                                        <td style="text-align: center;vertical-align:middle;">
                                            <input type="radio" class="radio-lg" name="question{{$item->id}}" id="question{{$item->id}}3" data-counter="{{$item->id}}" value="3">
                                        </td>
                                        <td style="text-align: center;vertical-align:middle;">
                                            <input type="radio" class="radio-lg" name="question{{$item->id}}" id="question{{$item->id}}4" data-counter="{{$item->id}}" value="4">
                                        </td>
                                        <td style="text-align: center;vertical-align:middle;">
                                            <input type="radio" class="radio-lg" name="question{{$item->id}}" id="question{{$item->id}}5" data-counter="{{$item->id}}" value="5">
                                        </td>
                                        <td>
                                            <input type="text" name="question{{$item->id}}_note" id="question{{$item->id}}_note" class="form-control">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-success w-100" id="SubmitCustDetail">Submit</button>
                </div>
            </div>
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-training-satisfication-manager-new-submit?_token={{ csrf_token() }}",
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