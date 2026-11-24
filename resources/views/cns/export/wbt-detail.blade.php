<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

function getRomawi($bln)
{
	switch ($bln) {
		case 1:
			return "I";
			break;
		case 2:
			return "II";
			break;
		case 3:
			return "III";
			break;
		case 4:
			return "IV";
			break;
		case 5:
			return "V";
			break;
		case 6:
			return "VI";
			break;
		case 7:
			return "VII";
			break;
		case 8:
			return "VIII";
			break;
		case 9:
			return "IX";
			break;
		case 10:
			return "X";
			break;
		case 11:
			return "XI";
			break;
		case 12:
			return "XII";
			break;
	}
}

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>
<div class="mt-3">
	<div class="cns-section-header">WEIGHT BRIDGE TICKET</div>
	<div class="card cns-card">
		<div class="card-body">
			<div id="ContentPages">
				<form action="cns-export-weight-bridge-ticket-detail-submit" enctype="multipart/form-data" class="FormInput_Form_Content" id="formWBT">
					@csrf
					<div class="row">
						<div class="col-md-3 col-12">
							<div class="form-group">
								<input type="hidden" name="WBT_No" id="WBT_No" value="{{ $wbt->WBT_No ?? '' }}">
								<label for="Packing_List_No">Packing List No</label>
                                <select name="Packing_List_No" id="Packing_List_No" class="form-control shadow-sm" required>
                                    <option value="">-- Pilih Packing List No --</option>
                                    @foreach ($pls as $pl)
                                        <option value="{{ $pl->Packing_List_No }}" {{ trim((string) $wbt->Packing_List_No) === trim((string) $pl->Packing_List_No) ? 'selected' : '' }}>{{ $pl->Packing_List_No }}</option>
                                    @endforeach
                                </select>
							</div>
						</div>
						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="Voyage">Vessel / Voyage</label>
								<input type="text" name="Voyage" id="Voyage" value="{{ $wbt->Voyage ?? '' }}" class="form-control shadow-sm">
							</div>
						</div>
						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="Departure_Date">Date of Departure</label>
								<input type="text" name="Departure_Date" id="Departure_Date" value="{{ $wbt->Departure_Date ?? '' }}" class="form-control bg-white shadow-sm" readonly>
							</div>
						</div>
						<div class="col-md-3 col-12">
							<div class="form-group">
								<label for="BL_No">B/L No.</label>
								<input type="text" name="BL_No" id="BL_No" value="{{ $wbt->BL_No ?? '' }}" class="form-control shadow-sm">
							</div>
						</div>
                        <div class="col-12">
                            <div class="row" id="containerList">
								@foreach ($wbt->Details as $item)
                                <div class="col-md-3 col-12">
                                    <div class="form-group">
                                        <label for="Nopol{{$item->VSD_ID}}">Nopol</label>
                                        <input type="text" id="Nopol{{$item->VSD_ID}}" class="form-control bg-white shadow-sm" value="{{ $item->Police_No ?: '-' }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-9 col-12">
                                    <div class="form-group">
                                        <label for="Container{{$item->VSD_ID}}">Container {{ $item->Container }} ({{ $item->Seal }})</label>
                                        <div class="input-group mb-3">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="Container{{$item->VSD_ID}}" name="Container{{$item->VSD_ID}}" {{ empty($item->Path_Loc) ? 'required' : '' }}>
                                                <label class="custom-file-label shadow-sm" for="Container{{$item->VSD_ID}}">{{ !empty($item->Path_Loc) ? basename($item->Path_Loc) : 'Choose file...' }}</label>
                                            </div>
                                            @if (!empty($item->Path_Loc))
                                            <div class="input-group-append mt-2 wbt-existing-preview-btn">
                                                <button type="button" class="btn btn-info btn-sm" onclick='window.cnsOpenDocPreview(@json(asset($item->Path_Loc)), @json("WBT - " . $item->Container))'>
                                                    <i class="fa fa-eye"></i> Preview Dokumen
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
								@endforeach
                            </div>
                        </div>
                        <div class="col-12">
			                {{-- <div id="ShipmentInstructionNew" style="width:auto;height:320px; background-color:white;overflow:hidden"></div> --}}
                        </div>
					</div>
					<div id="divtom">
						<input type="submit" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit WBT" class="btn btn-success mt-2 mr-2" />
					</div>
				</form>
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>
    
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Stuffing", "PEB_Date", "SI_Date", "ETD", "ETA"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	// ShipmentInstructionNew = new dhtmlXGridObject('ShipmentInstructionNew');
	// ShipmentInstructionNew.setImagePath("./dhtmlx/imgs/");
	// ShipmentInstructionNew.setHeader("No, Dari Jam, Hingga Jam, Nopol, Kontainer (Seal), Tare, Netto (Kg), Netto (MT), Gross (Kg), Gross (MT), Qty (Kgs) Note");
	// ShipmentInstructionNew.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// // ShipmentInstructionNew.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	// ShipmentInstructionNew.setInitWidths("60,150,100,200,150,150,100,100,100,100,100,100,auto");
	// ShipmentInstructionNew.setColAlign("center,left,left,left,left,left,right,right,right,right,right,right,left");
	// ShipmentInstructionNew.setColSorting("int,str,str,str,str,str,int,int,int,int,int,int,str");
	// ShipmentInstructionNew.setColTypes("ro,ed,ed,ed,ed,ed,edn,edn,edn,edn,edn,edn,ed");
	// ShipmentInstructionNew.enableSmartRendering(true, 150);
	// ShipmentInstructionNew.setNumberFormat("0,000", 6, ".", ",");
	// ShipmentInstructionNew.setNumberFormat("0,000", 7, ".", ",");
	// ShipmentInstructionNew.setNumberFormat("0,000", 8, ".", ",");
	// ShipmentInstructionNew.setNumberFormat("0,000", 9, ".", ",");
	// ShipmentInstructionNew.setNumberFormat("0,000", 10, ".", ",");
	// ShipmentInstructionNew.setNumberFormat("0,000", 11, ".", ",");
	// // ShipmentInstructionNew.attachEvent("onRowDblClicked", ShipmentInstructionNewKlik);
	// ShipmentInstructionNew.setSkin("dhx_skyblue");
	// ShipmentInstructionNew.init();

	// function refreshdata() {
	// 	ShipmentInstructionNew.clearAndLoad('xml-cns-export-shipment-instruction-new?Order_No=' + $('#Order_No').find('option:selected').val());

	// }

    $('#Packing_List_No').on('change', function() {
        // refreshdata();  
        $.blockUI();
        $.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
            url: "cns-export-weight-bridge-ticket-new-get-order",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                Packing_List_No: $('#Packing_List_No').find('option:selected').val()
            },
            success: function(response) {
                $('#containerList').html('');
				if (!response || Array.isArray(response) || !response.orderDetails) {
					$('#Voyage').val('');
					$('#BL_No').val('');
					$.unblockUI();
					return;
				}
				$('#Voyage').val(response.Voyage || '');
				$('#BL_No').val(response.BL_No || '');
				if (response.orderDetails.length > 0) {
					response.orderDetails.forEach(function(container, index) {
						$('#containerList').append(`
							<div class="col-md-3 col-12">
								<div class="form-group">
									<label for="Nopol${container.ID}">Nopol</label>
									<input type="text" id="Nopol${container.ID}" class="form-control bg-white shadow-sm" value="${container.Police_No ? container.Police_No : '-'}" readonly>
								</div>
							</div>
							<div class="col-md-9 col-12">
								<div class="form-group">
									<label for="Container${container.ID}">Container ${index + 1} ${container.Container} (${container.Seal})</label>
									<div class="custom-file mb-3">
										<input type="file" class="custom-file-input" id="Container${container.ID}" name="Container${container.ID}" required>
										<label class="custom-file-label shadow-sm" for="Container${container.ID}">Choose file...</label>
									</div>
								</div>
							</div>
						`);
					});
				} else {
					$('#containerList').html('<div class="col-12"><div class="alert alert-warning mb-0">Data kontainer stuffing tidak ditemukan untuk Packing List ini.</div></div>');
				}
                $.unblockUI();
            },
            error:function() {
                swal('Error', 'Error fetching order details. Please try again.', 'error');
                $.unblockUI();
            }
        });
    });
	

	function isValidImage(file) {
		const allowedExtensions = ['jpg', 'jpeg', 'png'];
		const fileName = file.name.toLowerCase();
		const extension = fileName.split('.').pop();

		return allowedExtensions.includes(extension);
	}

	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Stuffing", "PEB_Date", "ETD", "ETA", "SI_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	$('#formWBT').on('change', '.custom-file-input', function() {
		var $existingBtn = $(this).closest('.input-group').find('.wbt-existing-preview-btn');
		if (this.files && this.files.length > 0) {
			$existingBtn.hide();
		} else {
			$existingBtn.show();
		}
	});

	$('#formWBT').submit(function(e) {
		e.preventDefault(); // avoid to execute the actual submit of the form.

		var form = $(this);
		var actionUrl = form.attr('action');
		var formData = new FormData(this);

		let classID = $('.custom-file-input');
		let ids = [];
		for (let i = 0; i < classID.length; i++) {
			const ele = classID[i];
			ids.push(ele.id)
		}
		
		ids.forEach(ele => {
			formData.append(ele, $('#' + ele)[0].files[0]);
			if ($('#' + ele)[0].files[0] && !isValidImage($('#' + ele)[0].files[0])) {
				swal('Perhatian', 'File Kop Surat harus jpg, jpeg, atau png', 'warning');
			}
		});

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: actionUrl,
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			cache: false,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					swal('Berhasil!', 'Data Weight Bridge Ticket Submitted!', 'success');
					setTimeout(() => {
						refreshdata();
						$('#divlistOpportunity').html('')
					}, 200);
				} else {
					swal('Error', 'Weight Bridge Ticket Failed to Submit!', 'error');
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				swal('Error', 'Error Internal! Please Try again!', 'error');

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