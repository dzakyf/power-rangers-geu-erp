@include('layout.plugins')
@include('layout.adminor.tab-head')

<?php
$empDocTaskId = (string) ($_GET['ID'] ?? '');
?>

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
var empDocTaskId = {!! json_encode($empDocTaskId) !!};

function DokumenDownload() {
    var selectedId = DocumentListsDetail.getSelectedId();
    if (!selectedId) return false;
    var DokumenDownloadFile = DocumentListsDetail.cells(selectedId, 7).getValue();
    if (DokumenDownloadFile) {
        window.open('{{ asset("doc_recruitment") }}/' + DokumenDownloadFile, '_blank');
    }
    return true;
}

function addNew() {
    LoadM('DivDocumentListsDetail', '{{ url("employee-lists-detail-document-form") }}?ID=' + encodeURIComponent(empDocTaskId));
}

DocumentListsDetail = new dhtmlXGridObject('DocumentListsDetail');
DocumentListsDetail.setImagePath("./dhtmlx/imgs/");
DocumentListsDetail.setHeader("No,Category,Document No,Document Name,Reference,Created_Date,Created_By,PathFile");
DocumentListsDetail.setInitWidths("60,120,200,160,250,120,120,0");
DocumentListsDetail.setColAlign("center,center,left,left,left,center,left,center");
DocumentListsDetail.setColSorting("int,str,str,str,str,str,str,str");
DocumentListsDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
DocumentListsDetail.enableSmartRendering(true, 150);
DocumentListsDetail.attachEvent("onRowDblClicked", DokumenDownload);
DocumentListsDetail.setSkin("dhx_skyblue");
DocumentListsDetail.init();
refreshdataDocumentdetail();

function refreshdataDocumentdetail() {
    DocumentListsDetail.clearAndLoad('{{ url("xml-employee-lists-detail-document") }}?ID=' + encodeURIComponent(empDocTaskId));
}

function DelDocuments() {
    var selectedId = DocumentListsDetail.getSelectedId();
    if (!selectedId) {
        alert('Pilih dokumen yang ingin dihapus terlebih dahulu.');
        return;
    }
    var category = DocumentListsDetail.cells(selectedId, 1).getValue();
    dhtmlx.confirm('Hapus dokumen ' + category + ' ini?', 'Documents', function(r) {
        if (r === true) {
            $.ajax({
                url: '{{ url("employee-lists-detail-document-delete") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    Task_ID: empDocTaskId,
                    category: category
                },
                success: function(res) {
                    alert((res && res.message) ? res.message : 'Dokumen berhasil dihapus.');
                    refreshdataDocumentdetail();
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal menghapus dokumen.';
                    alert(msg);
                }
            });
        }
    });
}
</script>
