<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

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
                <div class="col-md-6 mb-3">
                    <label for="signature1_name">Signature 1 Name</label>
                    <input type="text" class="form-control" id="signature1_name" name="signature1_name" value="{{ $sign->signature1_name ?? '' }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="signature1_position">Signature 1 Position</label>
                    <input type="text" class="form-control" id="signature1_position" name="signature1_position" value="{{ $sign->signature1_position ?? '' }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="signature2_name">Signature 2 Name</label>
                    <input type="text" class="form-control" id="signature2_name" name="signature2_name" value="{{ $sign->signature2_name ?? '' }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="signature2_position">Signature 2 Position</label>
                    <input type="text" class="form-control" id="signature2_position" name="signature2_position" value="{{ $sign->signature2_position ?? '' }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="signature3_name">Signature 3 Name</label>
                    <input type="text" class="form-control" id="signature3_name" name="signature3_name" value="{{ $sign->signature3_name ?? '' }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="signature3_position">Signature 3 Position</label>
                    <input type="text" class="form-control" id="signature3_position" name="signature3_position" value="{{ $sign->signature3_position ?? '' }}">
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
	$('#SubmitCustDetail').click(function() {

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
</script>
@endsection