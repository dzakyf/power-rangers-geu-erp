<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$Priv = Auth::user()->Priv_ID;
$Name = Auth::user()->Name;
$email = Auth::user()->email;
$Department = Auth::user()->Department;
$Position = Auth::user()->Position;

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Dashboard</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Training</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">APPLICATION</div>
	<div class="card">
		<div class="card-body" id="ContentPages">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:13pt" for="participant_name">Participant</label>
                        <input type="text" name="participant_name" id="participant_name" class="form-control" value="{{ $Name }}">
                        <input type="hidden" name="participant" id="participant" value="{{ $Saya }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:13pt" for="participant_position">Position</label>
                        <input type="text" name="participant_position" id="participant_position" class="form-control" value="{{ $Position }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:13pt" for="participant_department">Department</label>
                        <input type="text" name="participant_department" id="participant_department" class="form-control" value="{{ $Department }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label style="font-size:13pt" for="participant_email">Email</label>
                        <input type="text" name="participant_email" id="participant_email" class="form-control" value="{{ $email }}">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="training_no">Nama pelatihan yang dibutuhkan</label>
                        <select name="training_no" id="training_no" class="form-control">
                            <option value="">-- Nama Pelatihan --</option>
                            @foreach ($trainings as $item)
                                <option value="{{ $item->training_no }}">{{ $item->training_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="training_why">Mengapa perlu mengikuti pelatihan ini?</label>
                        <textarea name="training_why" id="training_why" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="training_how">Bagaimana training ini akan mendukung tugas atau tanggung jawab bawahan...</label>
                        <textarea name="training_how" id="training_how" rows="5" class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt">Tanggal yang Diinginkan untuk Pelatihan... (agar tidak mengganggu operasional)</label>
                        <div class="row">
                            <div class="col-1">
                                <label style="font-size:13pt" for="requested_date_from">Dari</label>
                            </div>
                            <div class="col-5">
                                <input type="text" id="requested_date_from" name="requested_date_from" value="{{ date("Y-m-d") }}" class="form-control bg-white" readonly>
                            </div>
                            <div class="col-1">
                                <label style="font-size:13pt" for="requested_date_to">Ke</label>
                            </div>
                            <div class="col-5">
                                <input type="text" id="requested_date_to" name="requested_date_to" value="{{ date("Y-m-d") }}" class="form-control bg-white" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt">Waktu yang Diinginkan untuk Pelatihan... (agar tidak mengganggu operasional)</label>
                        <div class="row">
                            <div class="col-1">
                                <label style="font-size:13pt" for="requested_time_from">Dari</label>
                            </div>
                            <div class="col-5">
                                <input type="time" id="requested_time_from" name="requested_time_from" value="" class="form-control">
                            </div>
                            <div class="col-1">
                                <label style="font-size:13pt" for="requested_time_to">Ke</label>
                            </div>
                            <div class="col-5">
                                <input type="time" id="requested_time_to" name="requested_time_to" value="" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="note">Keterangan tambahan bila ada</label>
                        <textarea name="note" id="note" rows="5" class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="requested_fee">Biaya</label>
                        <input type="text" id="requested_fee" name="requested_fee" class="form-control" value="">
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
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["requested_date_from", "requested_date_to"]);
	myCalendar.setDateFormat("%Y-%m-%d");
    
	$("#participant_name").coolautosuggest({
		url:"partner-training-get-participant?chars=",
		showThumbnail:false,
		showDescription:true,
		idField:$("#participant"),
		width:700,
		minChars:1,
		onSelected:function(result){
		  if(result!=null){
			$("#participant").val(result.id);
			$("#participant_name").val(result.name);
			$("#participant_department").val(result.department);
			$("#participant_position").val(result.position);
			$("#participant_email").val(result.email);
		  }
		}
	});
	$('#SubmitCustDetail').click(function() {
        let cekTraining = $('#training_no').val();
        if (cekTraining == '') {
            alert("Pelatihan wajib dipilih");
            return;
        }

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-training-application-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.status;
				if (dataPos == 'success') {
					// var cariAWB = data.split(":");
					alert('Training Application Submitted!');
                    refreshdata();
                    // $('#divlistOpportunity').html('');
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
		var inputValues = [];
		$('#ContentPages input,#ContentPages select,#ContentPages textarea').each(function() {
			var type = $(this).attr("type");
			if ((type == "checkbox" || type == "radio") && this.checked) {
				inputValues.push($(this).attr("name") + "=" + $(this).val());
			} else if (type != "button" && type != "submit" && type != "radio" && type != "checkbox") {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			} else if ($(this).is("textarea")) {
				inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
		})
		return inputValues.join('&');
	}
</script>
@endsection