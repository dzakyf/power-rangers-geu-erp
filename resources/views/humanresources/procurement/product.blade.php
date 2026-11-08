<div class="card border-0 shadow-none">
    <div class="card-body p-2">
        <div class="row g-2">
            <div class="col-md-8">
                <div class="border rounded-3 overflow-hidden bg-white shadow-xs">
                    <div id="PRPRODUCTLIST" style="width:100%; height:320px; background-color:white; overflow:hidden;"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border rounded-3 h-100 bg-light p-2 d-flex flex-column align-items-center justify-content-center text-center">
                    <span class="text-muted small mb-2"><i class="fa fa-picture-o me-1"></i> Preview Gambar Produk</span>
                    <div class="border rounded bg-white p-1 d-flex align-items-center justify-content-center" style="width:200px; height:200px;">
                        <img id="PRPict" class="img-fluid rounded" style="max-height:190px; object-fit:contain;" alt="Preview Produk" src="" onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23f8f9fa%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23aaa%22%20dy%3D%22.3em%22%20text-anchor%3D%22middle%22%3ENo%20Image%3C%2Ftext%3E%3C%2Fsvg%3E';" />
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
            <button type="button" name="CancelPRList" id="CancelPRList" class="btn btn-sm btn-outline-secondary" onclick="ClosePRNew();">
                <i class="fa fa-times me-1"></i> Batal
            </button>
            <button type="button" name="SubmitPRList" id="SubmitPRList" class="btn btn-sm btn-primary" onclick="AddPRList();">
                <i class="fa fa-check me-1"></i> Pilih &amp; Masukkan ke Form
            </button>
        </div>
    </div>
</div>

<script>
function ClosePRNew() {
    if (parent.WinPR && parent.WinPR.window("WPR")) {
        parent.WinPR.window("WPR").close();
    } else if (window.AppModal) {
        window.AppModal.close();
    }
}

function ShowPict2(){
    var Foto = PRProductList.cells(PRProductList.getSelectedId(),4).getValue(); 
    $('#PRPict').attr("src",Foto);
}

function ShowPict(){
    var Foto = PRProductList.cells(PRProductList.getSelectedId(),7).getValue(); 
    $('#PRPict').attr("src",Foto);
}

function AddPRList() {
    var PRCats = $("#Category").val();
    var id = PRDetail.getSelectedRowId();
    var idProduct = PRProductList.getSelectedRowId();
    
    if(!idProduct) {
        alert('Silakan pilih salah satu produk terlebih dahulu');
        return;
    }

    var Product_Code = PRProductList.cells(idProduct,1).getValue(); 
    var Product_Desc = PRProductList.cells(idProduct,2).getValue(); 
    var UnitName     = PRProductList.cells(idProduct,5).getValue(); 
    var Stock        = PRProductList.cells(idProduct,4).getValue(); 
    var Specification= PRProductList.cells(idProduct,3).getValue(); 
    var Prices       = PRProductList.cells(idProduct,8).getValue(); 
    
    PRDetail.cells(id, 1).setValue('');
    PRDetail.cells(id, 2).setValue(Product_Code);
    PRDetail.cells(id, 3).setValue(Product_Desc);
    PRDetail.cells(id, 4).setValue('1');
    PRDetail.cells(id, 5).setValue(Specification);
    PRDetail.cells(id, 6).setValue(UnitName);
    PRDetail.cells(id, 7).setValue('');
    PRDetail.cells(id, 8).setValue(PRCats);
    PRDetail.cells(id, 9).setValue('');
    PRDetail.cells(id, 10).setValue(Prices);
    ClosePRNew();
}

var PRProductList = new dhtmlXGridObject('PRPRODUCTLIST');
PRProductList.setImagePath("./dhtmlx/imgs/");
PRProductList.setHeader("No.,Product Code,Description,Specification,Current Stock,Unit,Foto,Dim,Price");
PRProductList.attachHeader("&nbsp;,#text_filter,#text_filter,&nbsp;,&nbsp;,&nbsp;,&nbsp;");
PRProductList.setInitWidths("35,100,200,160,80,60,0,80,90");
PRProductList.setColAlign("center,center,left,center,right,left,center,center,right");
PRProductList.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro");
PRProductList.setColSorting("int,str,str,str,str,str,str");
PRProductList.attachEvent("onRowSelect", ShowPict);
PRProductList.enableSmartRendering(true, 50);
PRProductList.setSkin("dhx_skyblue");
PRProductList.init();

var PRPRoductCategory = $("#Category").val();
PRProductList.clearAndLoad("xml-hrd-procurement-product?Category=" + encodeURIComponent(PRPRoductCategory));
</script>
