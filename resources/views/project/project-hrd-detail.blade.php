<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REQUISITION</div>


<div id="GridDetailPO" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="DetailRequisition" NAME="DetailRequisition" value="Detail Requisition" onclick="GridDetailPOKlik()" class="btn btn-warning mt-2 mr-2"/>
		<input type="button" id="AddGridDetailPO" NAME="AddGridDetailPO" value="Add Requisition" class="btn btn-success mt-2 mr-2"/>
		<input type="button" id="AddGridDetailPO2" NAME="AddGridDetailPO2" value="Update Requisition" class="btn btn-info mt-2 mr-2"/>
	</div>
<div id="divListProjectHRD"></div>
<script>
function GridDetailPOKlik() {

GridDetailPOID = GridDetailPO.cells(GridDetailPO.getSelectedId(),1).getValue();

	if (GridDetailPOID !=''){
		LoadM('divListProjectHRD','project-hrd-detail-detail?ID=' + ProjectHRDID);
	}
return true;
}
		GridDetailPO = new dhtmlXGridObject('GridDetailPO');
		GridDetailPO.setImagePath("./dhtmlx/imgs/");
		GridDetailPO.setHeader("ID,Department,Position,Qty Person,Detail Requirement,Detail Position,Total Amount(IDR),Site,Salary Offering,Benefit Information,User Interview,Email User Interview,HP User Interview");
		GridDetailPO.attachFooter("Total,'#cspan','#cspan','#cspan',#cspan,#cspan,#stat_total,,#stat_total,,,,");
		GridDetailPO.setInitWidths("100,120,150,100,250,250,200,100,120,250,100,120,120");
		GridDetailPO.setColAlign("center,left,center,left,left,left,right,left,right,right,center,left,center,center,center");
		GridDetailPO.setColTypes("ro,ed,ed,ed,txt,txt,edn,ed,edn,txt,ed,ed,ed");
		GridDetailPO.setColSorting("int,str,str,str,str,str,int,str,int,str,str,str,str");
		GridDetailPO.setDateFormat("%Y-%m-%d"); 
		GridDetailPO.setEditable(true);
		GridDetailPO.setNumberFormat("0,000",6,".",",");
		GridDetailPO.setNumberFormat("0,000",8,".",",");
		GridDetailPO.enableEditEvents(true,false,true);

		GridDetailPO.enableKeyboardSupport(true);
		GridDetailPO.setSkin("dhx_skyblue");
		PGridDetailPO = new dataProcessor("project-hrd-detail-update");
		PGridDetailPO.setTransactionMode({
		 mode: "POST",
		  total:true,
		  headers:{
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		  }	,
		  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

		})
		
		PGridDetailPO.setUpdateMode("Off");
		PGridDetailPO.init(GridDetailPO);
		GridDetailPO.init();
		refreshgridx();
		
function refreshgridx(){
		GridDetailPO.clearAndLoad("xml-project-hrd-detail?Task_ID=<?php echo $_GET['ID'] ?>");
	
}


$('#AddGridDetailPO2').click(function(){
			PGridDetailPO.sendData();
			alert("Data Requisition Updated!");
			refreshgridx();
});


$('#AddGridDetailPO').click(function(){
			if(GridDetailPO.getRowsNum()==0){
				nums_reqrow=1;
				nomorrowsreq=0;
			}else{
				nums_reqrow=GridDetailPO.getRowsNum()+1;
				nomorrowsreq=nums_reqrow-1;
			}
			
			GridDetailPO.addRow(GridDetailPO.uid(), [nums_reqrow],GridDetailPO.getRowsNum());
			GridDetailPO.selectRow(nomorrowsreq);
});

			
function getAllValues() {
    var inputValues = [];
    $('#CPOPR input,select,textarea').each(function() {
        var type = $(this).attr("type");
        if ((type == "checkbox" || type == "radio") && this.checked) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }else{
			if (type != "button" && type != "submit") {
				
            inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
        }
    })
	
    return inputValues.join('&');
} 				


function UpdatePO(){
	dapat = getAllValues();
	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "opportunity-detail-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				UpdatePO2();			

			}else{
				
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "error" );
	
		}
	});
	
}
 	
</script>
