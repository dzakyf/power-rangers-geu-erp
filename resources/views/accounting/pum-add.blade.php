<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ADD NEW SALES PERSON</div>
		<div id="SalesLists_Detail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselSalesLists_DetailDet" name="ekselSalesLists_DetailDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="SalesLists_Detail.toExcel('./export/generate.php');"  />
			<input type="button" id="deletsaa" name="deletsaaa" value="Update" onclick="SubmitGrid()"class="btn btn-success mt-2 mr-2"  />
		</div>

	</div>	
<script>
SalesLists_Detail = new dhtmlXGridObject('SalesLists_Detail');
SalesLists_Detail.setImagePath("./dhtmlx/imgs/");
SalesLists_Detail.setHeader("Selected,NIK,Name,Gender,Last Education,Position,Work Area,Employee Status");
SalesLists_Detail.attachHeader("#master_checkbox,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
SalesLists_Detail.setInitWidths("60,150,100,100,200,120,120,100");
SalesLists_Detail.setColAlign("center,left,left,left,left,left,left,center");
SalesLists_Detail.setColSorting("int,str,str,str,str,str,str,str");
SalesLists_Detail.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro");
SalesLists_Detail.enableSmartRendering(true, 150);
PSalesLists_Detail = new dataProcessor("pum-add-update");
PSalesLists_Detail.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PSalesLists_Detail.setTransactionMode("POST", true);
PSalesLists_Detail.setUpdateMode("Off");
PSalesLists_Detail.init(SalesLists_Detail);
SalesLists_Detail.setSkin("dhx_skyblue");
SalesLists_Detail.init();
refreshdatas();

function refreshdatas(){
	SalesLists_Detail.clearAndLoad('xml-pum-add');

}



function SubmitGrid(){
	PSalesLists_Detail.sendData();
	alert('Data PUM Updated!');
	SalesLists_Detail.clearAll();
	$("#divlistSales").html('');
	refreshdata();
}


</script>
