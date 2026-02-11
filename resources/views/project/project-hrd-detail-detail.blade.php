<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">FULFILLMENTS</div>
		<div id="RecruitmentLists" style="width:auto;height:220px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="RecruitmentLists.toExcel('./export/generate.php');"  />
		</div>
		<div id="divlistRecruitment"></div>
<script>

function RecruitmentListsKlik() {

RecruitmentListsID = RecruitmentLists.getSelectedId();

if (RecruitmentListsID !=''){
	LoadM('divlistRecruitment','project-hrd-detail-detail2?ID=' + RecruitmentListsID);
}
return true;
}
RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
RecruitmentLists.setImagePath("./dhtmlx/imgs/");
RecruitmentLists.setHeader("No.,Task ID,Name,Gender,Birth Date,Position,Last Education,Reference,Reference Desc,Status,Aging Remain(days)");
RecruitmentLists.setInitWidths("70,150,100,100,100,100,100,120,100,100,100");
RecruitmentLists.setColAlign("center,left,center,center,center,center,center,center,center,center");
RecruitmentLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
RecruitmentLists.setColSorting("int,str,str,str,str,str,str,str");
RecruitmentLists.enableKeyboardSupport(true);
PRecruitmentLists = new dataProcessor("project-hrd-detail-detail-submit");
PRecruitmentLists.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PRecruitmentLists.setTransactionMode("POST", true);
PRecruitmentLists.setUpdateMode("Off");
PRecruitmentLists.init(RecruitmentLists);
RecruitmentLists.attachEvent("onRowDblClicked", RecruitmentListsKlik);
RecruitmentLists.setSkin("dhx_skyblue");
RecruitmentLists.init();
refreshdata();

function refreshdata(){
	RecruitmentLists.clearAndLoad('xml-project-hrd-detail-detail?ID=<?php echo $_GET['ID']?>');

}
</script>
