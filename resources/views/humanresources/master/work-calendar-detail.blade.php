<div class="mt-3">
	<div class="card">
		<div class="card-header">
			<h4 class="card-title m-2">Master Work Calendar</h4>
		</div>
		<div class="card-body">
			<!-- Stats Card -->
			<div class="row no-gutters">
				<div id="WorkCalendarDetail" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			</div>
		</div>
	</div>

</div>
<script>

WorkCalendarDetail = new dhtmlXGridObject('WorkCalendarDetail');
WorkCalendarDetail.setImagePath("./dhtmlx/imgs/");
WorkCalendarDetail.setHeader("No, Day of Week, Is Working Day?, Start (24H), End (24H), Break Minutes");
WorkCalendarDetail.setInitWidths("80,150,50,150,150,150");
WorkCalendarDetail.setColAlign("center,center,center,center,center,center");
WorkCalendarDetail.setColTypes("ro,ed,ch,ed,ed,ed,ed");
WorkCalendarDetail.setColSorting("int,str,str,str,str,str");
WorkCalendarDetail.setEditable(true);
WorkCalendarDetail.setSkin("dhx_skyblue");
PWorkCalendarDetail = new dataProcessor("master-work-calendar-detail-update");
	PWorkCalendarDetail.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PWorkCalendarDetail.setUpdateMode("Off");
PWorkCalendarDetail.init(WorkCalendarDetail);
WorkCalendarDetail.init();
WorkCalendarDetail.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
	if (stage == 2) {
		if (cInd == 3 || cInd == 4) {
			if (!/^\d{2}:\d{2}$/.test(nValue)) {
				alert("Format waktu harus HH:MM");
				return false; // Cancel the edit
			}else if(cInd == 4){
				let split1 = WorkCalendarDetail.cells(rId, 3).getValue().split(':');
				let split2 = WorkCalendarDetail.cells(rId, cInd).getValue().split(':');
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
	WorkCalendarDetail.clearAndLoad("xml-hrd-master-work-calendar-detail?task={{$_GET['task']}}");	

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
