<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>
<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">VESSEL SCHEDULE</div>
	<div class="card">
		<div class="card-body">
			<div id="ContentPages">
				<div class="FormInput_Form_Content">
					<div class="row">
						<div class="col-12">
							<div class="form-group">
								<label for="Order_No">Order No</label>
								<input type="text" name="Order_No" id="Order_No" value="{{ $vs->Order_No }}" class="form-control" required readonly />
							</div>
						</div>
                        <div class="col-12" id="divContainerSeal">
			                <div id="StuffingDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
							<div class="mt-3">
								<button class="btn btn-primary mr-3" onclick="addRowStuffingDetail()">Add Container</button>
								<button class="btn btn-danger mr-3" onclick="deleteRowStuffingDetail()">Delete Container</button>
							</div>
                        </div>
					</div>
				</div>
			</div>
			<div id="divtom">
				<input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Submit Container" class="btn btn-success mt-2 mr-2" />
			</div>
		</div>
	</div>
	<div id="DivProcess"></div>

</div>

<script>

	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Stuffing", "Checking_BC","Closing_CY", "ETD", "ATD", "ETA", "ATA"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	$('#SubmitCustDetail').click(function() {

		PStuffingDetail.sendData();
		// gettingVars = getAllValues();

		// $.ajax({
		// 	headers: {
		// 		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		// 	},
		// 	url: "cns-vessel-schedule-new-submit?_token={{ csrf_token() }}",
		// 	type: "POST",
		// 	data: gettingVars,
		// 	success: function(data, textStatus, jqXHR) {
		// 		var dataPos = data.indexOf("success");
		// 		if (dataPos != '-1') {
		// 			var cariAWB = data.split(":");
		// 			alert('Data Announcement Submitted!');

		// 			// location.href = "partner-announcement";
        //             refreshdata();
        //             $('#divlistOpportunity').html('')
		// 		} else {
		// 			alert('Announcement Failed to Submit!');
		// 		}
		// 	},
		// 	error: function(jqXHR, textStatus, errorThrown) {
		// 		alert("Error Internal! Please Try again!");

		// 	}
		// });
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

    
	StuffingDetail = new dhtmlXGridObject('StuffingDetail');
	StuffingDetail.setImagePath("./dhtmlx/imgs/");
	StuffingDetail.setHeader("No, Jam Awal (Jam:Menit), Jam Akhir (Jam:Menit), Tanggal, Nopol, Kontainer, Seal, Tare, Netto (Kg), Netto (MT), Gross, Gross (MT), Note");
	StuffingDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// StuffingDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	StuffingDetail.setInitWidths("60,100,100,100,200,150,150,100,100,100,100,100,auto");
	StuffingDetail.setColAlign("center,center,center,center,center,center,center,right,right,right,right,right,right,left");
	StuffingDetail.setDateFormat("%Y-%m-%d");
	StuffingDetail.setColSorting("int,str,str,str,str,str,str,int,int,int,int,int,int,str");
	StuffingDetail.setColTypes("ro,ed,ed,dhxCalendar,ed,ed,ed,edn,edn,edn,edn,edn,edn,ed");
	StuffingDetail.enableSmartRendering(true, 150);
	StuffingDetail.setNumberFormat("0,000", 7, ".", ",");
	StuffingDetail.setNumberFormat("0,000", 8, ".", ",");
	StuffingDetail.setNumberFormat("0,000.00", 9, ".", ",");
	StuffingDetail.setNumberFormat("0,000", 10, ".", ",");
	StuffingDetail.setNumberFormat("0,000.00", 11, ".", ",");
	PStuffingDetail = new dataProcessor("cns-stuffing-detail-update?task={{ $vs->Order_No }}");
	PStuffingDetail.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PStuffingDetail.setTransactionMode("POST", true);
	PStuffingDetail.setUpdateMode("Off");
	PStuffingDetail.init(StuffingDetail);
    PStuffingDetail.attachEvent("onBeforeDataSending", function (id,state, data) {
        // Modify or add payload data here
        // console.log(data)
        return true; // Return true to continue processing
    });
    PStuffingDetail.attachEvent("onAfterUpdateFinish", function (id,state, data) {
        alert("Data Successfully Submitted!");
        setTimeout(function() {
            refreshdata();
        }, 500);
    });
	StuffingDetail.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
		if (stage == 2) {
			if (cInd == 1 || cInd == 2) {
				if (!/^\d{2}:\d{2}$/.test(nValue)) {
					alert("Format waktu harus HH:MM");
					return false; // Cancel the edit
				}
			}
			if (cInd == 7 || cInd == 8) {
				let tare = StuffingDetail.cells(rId, 7).getValue();
				let netto = StuffingDetail.cells(rId, 8).getValue();
				if ((tare != 0 && netto != 0) && tare != "" && netto != "") {
					let nettoMT = parseFloat(netto) / 1000;
					let gross = parseFloat(tare) + parseFloat(netto);
					StuffingDetail.cells(rId, 9).setValue(nettoMT.toFixed(2));
					StuffingDetail.cells(rId, 10).setValue(gross.toFixed(2));
					StuffingDetail.cells(rId, 11).setValue((gross / 1000).toFixed(2));
				}
			}
		}
		return true; // Allow the edit
	});
	StuffingDetail.setSkin("dhx_skyblue");
	StuffingDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		StuffingDetail.clearAndLoad('xml-cns-stuffing-detail?task={{ $vs->Task_ID }}');
	}

	function addRowStuffingDetail()
	{
		let newId = StuffingDetail.uid();
		let rowCount = StuffingDetail.getRowsNum();
		if (rowCount == {{$vs->Qty1}}) {
			alert("Jumlah container sudah sesuai dengan Qty!");
			return;
		}
		StuffingDetail.addRow(newId, [rowCount + 1, "", "", "{{ $vs->ETD }}", "", "", "", "", "", "", "", "", "", ""]);
		StuffingDetail.selectRowById(newId);
	}

	function deleteRowStuffingDetail() {
		let selectedId = StuffingDetail.getSelectedId();
		if (selectedId) {
			StuffingDetail.deleteRow(selectedId);
		} else {
			alert("Pilih baris yang ingin dihapus!");
		}
	}
</script>