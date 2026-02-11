<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DOCUMENT LISTS</div>
<div id="DocumentListsDetail" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
<div>
	<input type="button" id="ekselDocumentListsDetailDet" name="ekselDocumentListsDetailDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="DocumentListsDetail.toExcel('./export/generate.php');"  />
	<input type="button" id="DocumentListsDetaildel" name="DocumentListsDetaildel" value="Delete Document" onclick="DelDocuments()"  class="btn btn-danger mt-2 mr-2" />
	<input type="button" id="DocumentListsDetailadd" name="DocumentListsDetailadd" value="Add New Document" onclick="addNew()" class="btn btn-success mt-2 mr-2"  />
</div>
<div id="DivDocumentListsDetail"></div>
<script>

function DokumenDownload() {

	DokumenDownloadFile = DocumentListsDetail.cells(DocumentListsDetail.getSelectedId(),7).getValue();
	window.open('./project_file/'+ DokumenDownloadFile);
	
	return true;
}
function DocumentListsDetailKlik() {

	DocumentListsDetailID = DocumentListsDetail.getSelectedId();

	if (DocumentListsDetailID !=''){
		LoadM('DivDocumentListsDetail','project-documents-detail-edit?ID=' + DocumentListsDetailID);
	}
	return true;
}
function addNew() {

LoadM('DivDocumentListsDetail','project-documents-detail-add?Task_ID=<?php echo $_GET['Task_ID']?>');
}

DocumentListsDetail = new dhtmlXGridObject('DocumentListsDetail');
DocumentListsDetail.setImagePath("./dhtmlx/imgs/");
DocumentListsDetail.setHeader("No,Category,Document No,Document Date,Reference,Created_Date,Created_By,PathFile");
DocumentListsDetail.setInitWidths("60,100,200,100,300,100,100,0,100,100,200,100,100,100,100,100,100,100,100,100,100,100,100");
DocumentListsDetail.setColAlign("center,center,center,center,left,center,left,center,left,right,left,center,center,center,center,center");
DocumentListsDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
DocumentListsDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
DocumentListsDetail.enableSmartRendering(true, 150);
PDocumentListsDetail = new dataProcessor("project-documents-detail-update");
PDocumentListsDetail.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PDocumentListsDetail.setTransactionMode("POST", true);
PDocumentListsDetail.setUpdateMode("Off");
PDocumentListsDetail.init(DocumentListsDetail);
DocumentListsDetail.attachEvent("onRowDblClicked", DokumenDownload);
DocumentListsDetail.attachEvent("onRowSelect", DocumentListsDetailKlik);
DocumentListsDetail.setSkin("dhx_skyblue");
DocumentListsDetail.init();
refreshdataDocumentdetail();

function refreshdataDocumentdetail(){
	DocumentListsDetail.clearAndLoad('xml-project-documents-detail?Task_ID=<?php echo $_GET['Task_ID']?>');

}

function DelDocuments(){
	dhtmlx.confirm('Cancel this Documents?', 'Documents', function(r) {
			
			if (r==true){
				DocumentListsDetail.deleteSelectedRows();
				PDocumentListsDetail.sendData();
					alert('Data Successfully Deleted!');
					setTimeout(function(){
						$("#DivDocumentListsDetail").html('');
						refreshdataDocumentdetail();
					},1000);
			}
	})
}
</script>
