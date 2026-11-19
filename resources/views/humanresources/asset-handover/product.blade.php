<div class="p-2">
    <div class="row g-2 align-items-start">
        <div class="col-md-8">
            <div id="AHPRODUCTLIST" style="width:100%;height:310px;background-color:white;"></div>
        </div>
        <div class="col-md-4 text-center">
            <div class="border rounded p-2 bg-light">
                <img id="PRPict" class="img-fluid rounded" style="max-height:290px;object-fit:contain;" alt="Preview Produk" />
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-2">
        <button type="button" name="CancelPRList" id="CancelPRList" class="btn btn-secondary btn-sm" onclick="ClosePRNew();">
            Batal
        </button>
        <button type="button" name="SubmitPRList" id="SubmitPRList" class="btn btn-primary btn-sm" onclick="AddPRList();">
            Submit
        </button>
    </div>
</div>
<script>
function ClosePRNew() {
    parent.WinPR.window("WPR").close();
}

function ShowPict() {
    var Foto = AHProductList.cells(AHProductList.getSelectedId(), 9).getValue();
    $('#PRPict').attr("src", Foto);
}

function AddPRList() {
    let id = AssetHandoverDetail.getSelectedRowId();
    let idProduct = AHProductList.getSelectedRowId();

    var Product_Code = AHProductList.cells(idProduct, 1).getValue();
    var Product_Desc = AHProductList.cells(idProduct, 2).getValue();
    var Product_Category = AHProductList.cells(idProduct, 4).getValue();
    var Product_Sub_Category = AHProductList.cells(idProduct, 5).getValue();
    var Product_Sub_Sub_Category = AHProductList.cells(idProduct, 6).getValue();
    var UnitName = AHProductList.cells(idProduct, 8).getValue();
    var Prices = AHProductList.cells(idProduct, 10).getValue();
    var Product_Brand = AHProductList.cells(idProduct, 11).getValue();
    var Specification = AHProductList.cells(idProduct, 3).getValue();

    AssetHandoverDetail.cells(id, 1).setValue(Product_Sub_Sub_Category);
    AssetHandoverDetail.cells(id, 2).setValue(Product_Code);
    AssetHandoverDetail.cells(id, 3).setValue(Product_Desc);
    AssetHandoverDetail.cells(id, 4).setValue('1');
    AssetHandoverDetail.cells(id, 5).setValue(Product_Brand);
    AssetHandoverDetail.cells(id, 6).setValue('');
    AssetHandoverDetail.cells(id, 7).setValue(Specification);
    AssetHandoverDetail.cells(id, 8).setValue('');
    AssetHandoverDetail.cells(id, 9).setValue(UnitName);
    AssetHandoverDetail.cells(id, 10).setValue('');
    AssetHandoverDetail.cells(id, 11).setValue(Product_Category);
    AssetHandoverDetail.cells(id, 12).setValue(Product_Sub_Category);
    AssetHandoverDetail.cells(id, 13).setValue(Prices);
    ClosePRNew();
}

AHProductList = new dhtmlXGridObject('AHPRODUCTLIST');
AHProductList.setImagePath("./dhtmlx/imgs/");
AHProductList.setHeader("No.,Product Code,Description,Specification,Category, Sub Category, Sub Sub Category,Current Stock,Unit,Foto,Dim,Price,Product Brand");
AHProductList.attachHeader("&nbsp;,#text_filter,#text_filter,&nbsp;,&nbsp;,&nbsp;,&nbsp;,#text_filter");
AHProductList.setInitWidths("30,100,200,200,100,100,100,100,50,100,100,100,100");
AHProductList.setColAlign("center,center,left,center,center,center,center,right,left,center,center,center,center");
AHProductList.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
AHProductList.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
AHProductList.attachEvent("onRowSelect", ShowPict);
AHProductList.enableSmartRendering(true, 50);
AHProductList.setSkin("dhx_skyblue");
AHProductList.init();
AHProductList.clearAndLoad("hrd-asset-handover-product-data");
</script>
