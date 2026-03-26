<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CREATE TRAINING</div>
	<div class="card">
		<div class="card-body" id="ContentPages">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" style="font-size:13pt" for="training_name">Nama pelatihan</label>
                        <input type="text" name="training_name" id="training_name" class="form-control" value="{{ $training->training_name }}">
                        <input type="hidden" name="training_no" id="training_no" value="{{ $training->training_no }}">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" style="font-size:13pt" for="training_coverage">Cakupan pelatihan</label>
                        <input type="text" name="training_coverage" id="training_coverage" class="form-control" value="{{ $training->training_coverage }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label style="font-size:13pt" for="category">Category</label>
                        <select name="category" id="category" class="form-control">
                            <option value="mandatory" {{ 'mandatory' == $training->category ? 'selected' : '' }}>Mandatory</option>
                            <option value="optional" {{ 'optional' == $training->category ? 'selected' : '' }}>Optional</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label style="font-size:13pt" for="target">Target</label>
                        <select name="target" id="target" class="form-control">
                            <option value="staff" {{ 'staff' == $training->target ? 'selected' : '' }}>Staff</option>
                            <option value="supervisor" {{ 'supervisor' == $training->target ? 'selected' : '' }}>Supervisor</option>
                            <option value="mitra" {{ 'mitra' == $training->target ? 'selected' : '' }}>Mitra</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label style="font-size:13pt" for="method">Metode</label>
                        <select name="method" id="method" class="form-control">
                            <option value="online" {{ $training->method == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ $training->method == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="address">Alamat (link/alamat tempat)</label>
                        <textarea name="address" id="address" rows="3" class="form-control">{{ $training->address }}</textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt">Tanggal</label>
                        <div class="row">
                            <div class="col-1">
                                <label style="font-size:13pt" for="training_date_from">Dari</label>
                            </div>
                            <div class="col-5">
                                <input type="text" id="training_date_from" name="training_date_from" value="{{ $training->training_date_from }}" class="form-control bg-white" readonly>
                            </div>
                            <div class="col-1">
                                <label style="font-size:13pt" for="training_date_to">Ke</label>
                            </div>
                            <div class="col-5">
                                <input type="text" id="training_date_to" name="training_date_to" value="{{ $training->training_date_to }}" class="form-control bg-white" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt">Waktu</label>
                        <div class="row">
                            <div class="col-1">
                                <label style="font-size:13pt" for="training_time_from">Dari</label>
                            </div>
                            <div class="col-5">
                                <input type="time" id="training_time_from" name="training_time_from" value="{{ $training->training_time_from }}" class="form-control">
                            </div>
                            <div class="col-1">
                                <label style="font-size:13pt" for="training_time_to">Ke</label>
                            </div>
                            <div class="col-5">
                                <input type="time" id="training_time_to" name="training_time_to" value="{{ $training->training_time_to }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <div class="form-group">
                        <label style="font-size:13pt" for="training_fee">Biaya</label>
                        <input type="text" id="training_fee" name="training_fee" class="form-control" value="{{ $training->training_fee }}">
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <div>
	                    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MATERI</div>
			            <div id="TrainingListMaterial" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <div>
	                    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PESERTA</div>
			            <div id="TrainingListParticipant" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
                    </div>
                </div>
            </div>
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["training_date_from", "training_date_to"]);
	myCalendar.setDateFormat("%Y-%m-%d");
    
	TrainingListMaterial = new dhtmlXGridObject('TrainingListMaterial');
	TrainingListMaterial.setImagePath("./dhtmlx/imgs/");
	TrainingListMaterial.setHeader("No, Materi Pelatihan, Pemateri");
	TrainingListMaterial.attachHeader("#rspan,#text_filter,#text_filter");
	// TrainingListMaterial.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TrainingListMaterial.setInitWidths("60,100,100");
	TrainingListMaterial.setColAlign("center,left,left");
	TrainingListMaterial.setColSorting("int,str,str");
	TrainingListMaterial.setColTypes("ron,ro,ro");
	TrainingListMaterial.enableSmartRendering(true, 150);
	// TrainingListMaterial.setNumberFormat("0,000", 7, ".", ",");
	// TrainingListMaterial.attachEvent("onRowDblClicked", TrainingListMaterialKlik);
	TrainingListMaterial.setSkin("dhx_skyblue");
	TrainingListMaterial.init();
	refreshdataMaterial();

	function refreshdataMaterial() {
		TrainingListMaterial.clearAndLoad('xml-partner-training-list-detail-material?training={{$training->id}}');
	}

	TrainingListParticipant = new dhtmlXGridObject('TrainingListParticipant');
	TrainingListParticipant.setImagePath("./dhtmlx/imgs/");
	TrainingListParticipant.setHeader("No, Tanggal Pelatihan,Nama Partisipan, Posisi, Department, Email, Kehadiran");
	TrainingListParticipant.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// TrainingListParticipant.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TrainingListParticipant.setInitWidths("60,100,100,100,100,100,100");
	TrainingListParticipant.setColAlign("center,center,left,left,left,left,left");
	TrainingListParticipant.setColSorting("int,str,str,str,str,str,str");
	TrainingListParticipant.setColTypes("ron,ro,ro,ro,ro,ro,ro");
	TrainingListParticipant.enableSmartRendering(true, 150);
	// TrainingListParticipant.setNumberFormat("0,000", 7, ".", ",");
	// TrainingListParticipant.attachEvent("onRowDblClicked", TrainingListParticipantKlik);
	TrainingListParticipant.setSkin("dhx_skyblue");
	TrainingListParticipant.init();
	refreshdataParticipant();

	function refreshdataParticipant() {
		TrainingListParticipant.clearAndLoad('xml-partner-training-list-detail-participant?training={{$training->id}}');
	}

	$('#SubmitCustDetail').click(function() {
        let cekTraining = $('#training_no').val();
        if (cekTraining == '') {
            alert("Pelatihan wajib dipilih");
            return;
        }

		let TrainingApprovalID = TrainingApproval.getChangedRows().split(',').filter(item => item !== '');
        let count = 0;
        let ids = [];
        for (let i = 0; i < TrainingApprovalID.length; i++) {
            const ele = TrainingApprovalID[i];
            const check = parseInt(TrainingApproval.cells(ele, 1).getValue());
            if (check) {
                count++;
                ids.push(ele);
            }
        }

		gettingVars = getAllValues();
        gettingVars['training_ids'] = ids;
        gettingVars['counter'] = counter - 1;

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "partner-training-approval-detail-submit?_token={{ csrf_token() }}",
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