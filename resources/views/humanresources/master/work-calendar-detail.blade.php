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
	// sebelum editor muncul
    if(stage == 0 && (cInd == 3 || cInd == 4)){

        let total = getTotalMinutes(rId);

        let weeklyHour = Number(
            WorkCalendar.cells(
                WorkCalendar.getSelectedRowId(),3
            ).getValue()
        );

        if(total >= weeklyHour * 60){
            alert("Total weekly hours sudah terpenuhi.");
            return false; // editor tidak dibuka
        }
    }
	
	if(stage == 2){

        if(cInd == 3 || cInd == 4){

            if(!/^\d{2}:\d{2}$/.test(nValue)){
                alert("Format waktu harus HH:MM");
                return false;
            }

            if(cInd == 4){

                let start = WorkCalendarDetail.cells(rId,3).getValue().split(":");
                let end = nValue.split(":");

                let startMin = Number(start[0])*60 + Number(start[1]);
                let endMin = Number(end[0])*60 + Number(end[1]);

                if(endMin < startMin){
                    alert("End Time must be greater than Start Time.");
                    return false;
                }
            }
        }

        // Delay until grid stores the new value
        setTimeout(function(){

            let totalHours = getTotalWeeklyHours();

            // Weekly Hours from master grid
            let masterId = WorkCalendar.getSelectedRowId();
            let weeklyHours = parseFloat(
                WorkCalendar.cells(masterId, 3).getValue()
            );

            if(totalHours > weeklyHours){
                alert("Total weekly hours (" + totalHours +
                      ") cannot exceed " + weeklyHours + " hours.");

                // Restore old value
                WorkCalendarDetail.cells(rId, cInd).setValue(oValue);
            }

        }, 0);
    }

    return true;
});
function getTotalMinutes(exceptRow){
    let total = 0;

    WorkCalendarDetail.forEachRow(function(rid){

        if(rid == exceptRow) return;

        if(!WorkCalendarDetail.cells(rid,2).isChecked())
            return;

        let start = WorkCalendarDetail.cells(rid,3).getValue();
        let end   = WorkCalendarDetail.cells(rid,4).getValue();
        let brk   = parseInt(WorkCalendarDetail.cells(rid,5).getValue()) || 0;

        if(start && end){

            let s = start.split(":");
            let e = end.split(":");

            let sm = parseInt(s[0])*60 + parseInt(s[1]);
            let em = parseInt(e[0])*60 + parseInt(e[1]);

            total += (em - sm - brk);
        }

    });

    return total;
}
function getTotalWeeklyHours() {
    let totalMinutes = 0;

    WorkCalendarDetail.forEachRow(function(rid) {

        // Is Working Day column
        if (WorkCalendarDetail.cells(rid, 2).isChecked()) {

            let start = WorkCalendarDetail.cells(rid, 3).getValue();
            let end = WorkCalendarDetail.cells(rid, 4).getValue();
            let breakMin = parseInt(WorkCalendarDetail.cells(rid, 5).getValue()) || 0;

            if (start && end) {

                let s = start.split(":");
                let e = end.split(":");

                let startMin = parseInt(s[0]) * 60 + parseInt(s[1]);
                let endMin = parseInt(e[0]) * 60 + parseInt(e[1]);

                totalMinutes += (endMin - startMin - breakMin);
            }
        }

    });

    return totalMinutes / 60;
}
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
