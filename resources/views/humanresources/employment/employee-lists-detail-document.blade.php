@include('layout.plugins')
@include('layout.adminor.tab-head')

<div class="container-fluid py-3">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-folder-open me-2"></i>Employee Documents</h6>
            <div class="d-flex gap-2">
                <button type="button" id="ekselDocumentListsDetailDet" name="ekselDocumentListsDetailDet" class="btn btn-sm btn-light text-dark" onclick="DocumentListsDetail.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel-o text-success me-1"></i> Export Excel
                </button>
                <button type="button" id="DocumentListsDetailadd" name="DocumentListsDetailadd" class="btn btn-sm btn-light text-primary" onclick="addNew()">
                    <i class="fa fa-plus me-1"></i> Add New Document
                </button>
                <button type="button" id="DocumentListsDetaildel" name="DocumentListsDetaildel" class="btn btn-sm btn-light text-danger" onclick="DelDocuments()">
                    <i class="fa fa-trash me-1"></i> Delete Document
                </button>
                <button type="button" class="btn btn-sm btn-light text-primary fw-bold" onclick="PDocumentListsDetail.sendData();alert('Data saved.');">
                    <i class="fa fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div class="alert alert-info py-2 px-3 small mb-2 d-flex align-items-center">
                <i class="fa fa-info-circle me-2"></i> Klik dua kali (double click) baris dokumen untuk mendownload/melihat lampiran.
            </div>
            <div id="DocumentListsDetail" style="width:100%;height:420px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
    <div id="DivDocumentListsDetail"></div>
</div>

<script>
function DokumenDownload() {
    var DokumenDownloadFile = DocumentListsDetail.cells(DocumentListsDetail.getSelectedId(),7).getValue();
    console.log(DokumenDownloadFile);
    window.open('doc_recruitment/'+ DokumenDownloadFile);
    return true;
}

function addNew() {
    LoadM('DivDocumentListsDetail','employee-lists-detail-document?ID=<?php echo $_GET['ID']?>');
}

DocumentListsDetail = new dhtmlXGridObject('DocumentListsDetail');
DocumentListsDetail.setImagePath("./dhtmlx/imgs/");
DocumentListsDetail.setHeader("No,Category,Document No,Document Name,Reference,Created_Date,Created_By,PathFile");
DocumentListsDetail.setInitWidths("60,120,200,160,250,120,120,0");
DocumentListsDetail.setColAlign("center,center,left,left,left,center,left,center");
DocumentListsDetail.setColSorting("int,str,str,str,str,str,str,str");
DocumentListsDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
DocumentListsDetail.enableSmartRendering(true, 150);
PDocumentListsDetail = new dataProcessor("project-documents-detail-update");
PDocumentListsDetail.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    }
});
	
PDocumentListsDetail.setTransactionMode("POST", true);
PDocumentListsDetail.setUpdateMode("Off");
PDocumentListsDetail.init(DocumentListsDetail);
DocumentListsDetail.attachEvent("onRowDblClicked", DokumenDownload);
DocumentListsDetail.setSkin("dhx_skyblue");
DocumentListsDetail.init();
refreshdataDocumentdetail();

function refreshdataDocumentdetail(){
    DocumentListsDetail.clearAndLoad('xml-employee-lists-detail-document?ID=<?php echo $_GET['ID']?>');
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
    });
}
</script>
