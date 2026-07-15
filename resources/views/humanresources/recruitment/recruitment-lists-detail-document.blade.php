 @include('layout.plugins')



<div id="DocumentListsDetail" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
<div>
	<input type="button" id="ekselDocumentListsDetailDet" name="ekselDocumentListsDetailDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="DocumentListsDetail.toExcel('./export/generate.php');"  />
	
</div>
<div id="DivDocumentListsDetail"></div>
<script>

function DokumenDownload() {

	DokumenDownloadFile = DocumentListsDetail.cells(DocumentListsDetail.getSelectedId(),2).getValue();
	window.open('./doc_recruitment/'+ DokumenDownloadFile);
	
	return true;
}

function addNew() {

LoadM('DivDocumentListsDetail','employee-lists-detail-document?ID=<?php echo $_GET['ID']?>');
}

DocumentListsDetail = new dhtmlXGridObject('DocumentListsDetail');
DocumentListsDetail.setImagePath("./dhtmlx/imgs/");
DocumentListsDetail.setHeader("No,Category,Document No,Document Name,Reference,Created_Date,Created_By,PathFile");
DocumentListsDetail.setInitWidths("60,100,200,100,300,100,100,0,100,100,200,100,100,100,100,100,100,100,100,100,100,100,100");
DocumentListsDetail.setColAlign("center,center,center,center,left,center,left,center,left,right,left,center,center,center,center,center");
DocumentListsDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
DocumentListsDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
DocumentListsDetail.enableSmartRendering(true, 150);
PDocumentListsDetail = new dataProcessor("recruitment-lists-documents-detail-update");
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
DocumentListsDetail.setSkin("dhx_skyblue");
DocumentListsDetail.init();
refreshdataDocumentdetail();

function refreshdataDocumentdetail(){
	DocumentListsDetail.clearAndLoad('xml-recruitment-lists-detail-document?ID=<?php echo $_GET['ID']?>');

}

function DelDocuments(){
	dhtmlx.confirm('Delete this Documents?', 'Documents', function(r) {
			
			if (r==true){
				DocumentListsDetail.deleteSelectedRows();
				PDocumentListsDetail.sendData();
					alert('Data Successfully Deleted!');
					setTimeout(function(){
						refreshdataDocumentdetail();
					},1000);
			}
	})
}
</script>
