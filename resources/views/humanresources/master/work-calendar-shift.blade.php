<div class="mt-3">
	<div class="card">
		<div class="card-header">
			<h4 class="card-title m-2">Master Work Calendar</h4>
		</div>
		<div class="card-body">
			<!-- Stats Card -->
			<div class="row no-gutters">
				<div id="WorkCalendarShift" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			</div>
		</div>
	</div>

</div>
<script>

WorkCalendarShift = new dhtmlXGridObject('WorkCalendarShift');
WorkCalendarShift.setImagePath("./dhtmlx/imgs/");
WorkCalendarShift.setHeader("No, Code, Name, Start (24H), End (24H), Break Minutes, Total Working, Cross Days");
WorkCalendarShift.setInitWidths("80,150,50,150,150,150,150,150");
WorkCalendarShift.setColAlign("center,center,center,center,center,center,center,center");
WorkCalendarShift.setColTypes("ro,ed,ed,ed,ed,ed,ed,ed,ch");
WorkCalendarShift.setColSorting("int,str,str,str,str,str,str,str");
WorkCalendarShift.setEditable(true);
WorkCalendarShift.setSkin("dhx_skyblue");
PWorkCalendarShift = new dataProcessor("master-work-calendar-shift-update");
	PWorkCalendarShift.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PWorkCalendarShift.setUpdateMode("Off");
PWorkCalendarShift.init(WorkCalendarShift);
WorkCalendarShift.init();
WorkCalendarShift.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
	if (stage == 2) {
		if (cInd == 3 || cInd == 4) {
			if (!/^\d{2}:\d{2}$/.test(nValue)) {
				alert("Format waktu harus HH:MM");
				return false; // Cancel the edit
			}else if(cInd == 4){
				let split1 = WorkCalendarShift.cells(rId, 3).getValue().split(':');
				let split2 = WorkCalendarShift.cells(rId, cInd).getValue().split(':');
				let nilai1 = (Number(split1[0]) * 60) + Number(split1[1]);
				let nilai2 = (Number(split2[0]) * 60) + Number(split2[1]);
				// if (nilai2 < nilai1) {
				// 	alert("Format Berakhir tidak boleh le i");
				// 	return false; // Cancel the edit
				// }
			}
		}
	}
	return true; // Allow the edit
});
reloadgroupdetail();
function reloadgroupdetail(){
	WorkCalendarShift.clearAndLoad("xml-hrd-master-work-calendar-shift?task={{$_GET['task']}}");	

}

$('#submitBtn').click(function() {
	$.ajax({
		url: 'hrd-master-work-calendar-new-submit',
		type: 'POST',
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},
		data: {
			Name: $('#Name').val(),
			Employee_Type: $('#Employee_Type').val(),
			Weekly_Hours: $('#Weekly_Hours').val(),
		},
		success: function(data) {
			console.log(data);
			if (data.status === 'success') {
				alert('Data berhasil disimpan');
				setTimeout(function() {
					reloadgroup();
					$('#WorkCalendarDiv').html('');
				}, 500);

			} else {
				alert('Terjadi kesalahan saat menyimpan data');
			}
		}
	});
});


</script>
