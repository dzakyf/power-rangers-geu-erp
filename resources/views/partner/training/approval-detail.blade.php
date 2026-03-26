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
                        <input type="text" name="training_name" id="training_name" class="form-control" value="{{ $master->training_name }}">
                        <input type="hidden" name="training_no" id="training_no" value="{{ $master->training_no }}">
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" style="font-size:13pt" for="training_coverage">Cakupan pelatihan</label>
                        <input type="text" name="training_coverage" id="training_coverage" class="form-control" value="{{ $master->training_coverage }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label style="font-size:13pt" for="category">Category</label>
                        <select name="category" id="category" class="form-control">
                            <option value="mandatory" {{ 'mandatory' == $master->category ? 'selected' : '' }}>Mandatory</option>
                            <option value="optional" {{ 'optional' == $master->category ? 'selected' : '' }}>Optional</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label style="font-size:13pt" for="target">Target</label>
                        <select name="target" id="target" class="form-control">
                            <option value="staff" {{ 'staff' == $master->target ? 'selected' : '' }}>Staff</option>
                            <option value="supervisor" {{ 'supervisor' == $master->target ? 'selected' : '' }}>Supervisor</option>
                            <option value="mitra" {{ 'mitra' == $master->target ? 'selected' : '' }}>Mitra</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label style="font-size:13pt" for="method">Metode</label>
                        <select name="method" id="method" class="form-control">
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="address">Alamat (link/alamat tempat)</label>
                        <textarea name="address" id="address" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div id="divMaterial">
                        <div class="row">
                            <div class="col-5">
                                <div class="form-group">
                                    <label for="material1">Materi 1</label>
                                    <select name="material1" id="material1" data-counter="1" class="material form-control">
                                        <option value="">-- Pilih materi --</option>
                                        @foreach ($masterMaterial as $item)
                                        <option value="{{ $item->material_no }}">{{ $item->material_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-group">
                                    <label for="speaker1">Pemateri 1</label>
                                    <select name="speaker1" id="speaker1" data-counter="1" class="speaker form-control">
                                        <option value="">-- Pilih pembicara --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-1 my-auto">
                                <div class="form-group">
                                    <label for="">&nbsp;</label>
                                    <button class="btn btn-primary w-100" id="plusMaterial"><i class="lni lni-plus"></i></button>
                                </div>
                            </div>
                        </div>
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
                                <input type="text" id="training_date_from" name="training_date_from" value="{{ date("Y-m-d") }}" class="form-control bg-white" readonly>
                            </div>
                            <div class="col-1">
                                <label style="font-size:13pt" for="training_date_to">Ke</label>
                            </div>
                            <div class="col-5">
                                <input type="text" id="training_date_to" name="training_date_to" value="{{ date("Y-m-d") }}" class="form-control bg-white" readonly>
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
                                <input type="time" id="training_time_from" name="training_time_from" value="" class="form-control">
                            </div>
                            <div class="col-1">
                                <label style="font-size:13pt" for="training_time_to">Ke</label>
                            </div>
                            <div class="col-5">
                                <input type="time" id="training_time_to" name="training_time_to" value="" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label style="font-size:13pt" for="training_fee">Biaya</label>
                        <input type="text" id="training_fee" name="training_fee" class="form-control" value="">
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
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["training_date_from", "training_date_to"]);
	myCalendar.setDateFormat("%Y-%m-%d");
    
	// TrainingApprovalMaterial = new dhtmlXGridObject('TrainingApprovalMaterial');
	// TrainingApprovalMaterial.setImagePath("./dhtmlx/imgs/");
	// TrainingApprovalMaterial.setHeader("No,checklist, Materi Pelatihan, Pemateri");
	// TrainingApprovalMaterial.attachHeader("#rspan,#master_checkbox,#text_filter,#text_filter");
	// // TrainingApprovalMaterial.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	// TrainingApprovalMaterial.setInitWidths("60,80,100,100");
	// TrainingApprovalMaterial.setColAlign("center,center,left,left");
	// TrainingApprovalMaterial.setColSorting("int,int,str,str");
	// TrainingApprovalMaterial.setColTypes("ron,ch,ro,ro");
	// TrainingApprovalMaterial.enableSmartRendering(true, 150);
	// // TrainingApprovalMaterial.setNumberFormat("0,000", 7, ".", ",");
	// // TrainingApprovalMaterial.attachEvent("onRowDblClicked", TrainingApprovalMaterialKlik);
	// TrainingApprovalMaterial.setSkin("dhx_skyblue");
	// TrainingApprovalMaterial.init();
	// refreshdataMaterial();

	// function refreshdataMaterial() {
	// 	TrainingApprovalMaterial.clearAndLoad('xml-partner-training-approval-material');
	// }
    var counter = 2;
    
    function materials()
    {
    }

    $('#plusMaterial').on('click', function() {
        let material = JSON.parse(`{!! json_encode($masterMaterial) !!}`);
        let str = "";
        for (let i = 0; i < material.length; i++) {
            const ele = material[i];
            str += `<option value="${ele.material_no}">${ele.material_name}</option>`;
        }

        $('#divMaterial').append(`
                        <div class="row" id="row${counter}">
                            <div class="col-5">
                                <div class="form-group">
                                    <label for="material${counter}">Materi ${counter}</label>
                                    <select name="material${counter}" id="material${counter}" data-counter="${counter}" class="material form-control">
                                        <option value="">-- Pilih materi --</option>
                                        ` +
                                        str
                                        + `
                                    </select>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-group">
                                    <label for="speaker${counter}">Pemateri ${counter}</label>
                                    <select name="speaker${counter}" id="speaker${counter}" data-counter="${counter}" class="speaker form-control">
                                        <option value="">-- Pilih pembicara --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-1 my-auto">
                                <div class="form-group">
                                    <label for="">&nbsp;</label>
                                    <button class="btn btn-danger w-100 minus-material" data-counter="${counter}"><i class="lni lni-minus"></i></button>
                                </div>
                            </div>
                        </div>
        `);
        counter++;
    });

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