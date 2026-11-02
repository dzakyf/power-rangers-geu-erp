@include('cns.vs.partials.vessel-schedule-form', [
    'vs' => $vs,
    'contracts' => $contracts,
    'vendors' => $vendors,
    'bookings' => $bookings,
    'q88' => $q88,
    'isEdit' => true,
])

@include('cns.vs.partials.vessel-schedule-form-script', [
    'submitUrl' => 'cns-vessel-schedule-detail-submit',
    'isEdit' => true,
    'vs' => $vs,
])

<div class="mt-3">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">VESSEL SCHEDULE LOG</div>
	<div class="card">
		<div class="card-body">
			<div id="StuffingDetailLog" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		</div>
	</div>
</div>

<script>

	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["Stuffing", "Checking_BC", "Checking_CY", "ETD", "ATD", "ETA", "ATA"]);
	myCalendar.setDateFormat("%Y-%m-%d");

	$('#SubmitCustDetail').click(function() {

		gettingVars = getAllValues();

		$.ajax({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			url: "cns-vessel-schedule-detail-submit?_token={{ csrf_token() }}",
			type: "POST",
			data: gettingVars,
			success: function(data, textStatus, jqXHR) {
				var dataPos = data.indexOf("success");
				if (dataPos != '-1') {
					var cariAWB = data.split(":");
					alert('Data Announcement Submitted!');

					// location.href = "partner-announcement";
                    refreshdata();
                    $('#divlistOpportunity').html('')
				} else {
					alert('Announcement Failed to Submit!');
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

	StuffingDetailLog = new dhtmlXGridObject('StuffingDetailLog');
	StuffingDetailLog.setImagePath("./dhtmlx/imgs/");
	StuffingDetailLog.setHeader("No, Action By, Action Date, Action Status, Kontrak, Shipping Method, Qty, Vessel/Truck, PoL, PoD, Stuffing Date, Closing CY, ETD, ETA");
	StuffingDetailLog.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// StuffingDetailLog.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	StuffingDetailLog.setInitWidths("60,120,140,140,150,120,100,180,150,150,130,120,110,110");
	StuffingDetailLog.setColAlign("center,left,center,left,left,left,left,left,left,left,center,center,center,center");
	StuffingDetailLog.setDateFormat("%d %F %Y");
	StuffingDetailLog.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	StuffingDetailLog.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	StuffingDetailLog.enableSmartRendering(true, 150);
	StuffingDetailLog.setSkin("dhx_skyblue");
	StuffingDetailLog.init();
	refreshdataDataLog();

	function refreshdataDataLog() {
		StuffingDetailLog.clearAndLoad('xml-cns-vessel-schedule-detail-log?task={{$_GET["task"]}}');

	}
</script>