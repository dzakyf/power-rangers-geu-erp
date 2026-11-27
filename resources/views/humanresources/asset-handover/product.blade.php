<div class="p-2">
    <div class="row g-2 align-items-start">
        <div class="col-md-8">
            <div id="AHPRODUCTLIST" style="width:100%;height:310px;background-color:white;"></div>
        </div>
        <div class="col-md-4 text-center">
            <div class="border rounded p-2 bg-light">
                <img id="PRPict" class="img-fluid rounded" style="max-height:290px;object-fit:contain;" alt="Preview Produk" onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%22200%22%20height%3D%22200%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23f8f9fa%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20fill%3D%22%23aaa%22%20dy%3D%22.3em%22%20text-anchor%3D%22middle%22%3ENo%20Image%3C%2Ftext%3E%3C%2Fsvg%3E';" />
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
    if (parent.WinPR && parent.WinPR.window("WPR")) {
        parent.WinPR.window("WPR").close();
    }
}

function ShowPict() {
    var selId = AHProductList.getSelectedId();
    if (!selId) return;
    var Foto = AHProductList.cells(selId, 9).getValue();
    $('#PRPict').attr("src", Foto || '');
}

function AddPRList() {
    let id = AssetHandoverDetail.getSelectedRowId();
    if (!id) {
        alert('Baris target pada tabel detail belum dipilih.');
        return;
    }
    if (String(id).indexOf(',') !== -1) {
        id = String(id).split(',')[0];
    }

    let idProduct = AHProductList.getSelectedRowId();
    if (!idProduct) {
        alert('Pilih item produk/aset terlebih dahulu.');
        return;
    }

    var Product_Code = AHProductList.cells(idProduct, 1).getValue();
    var Product_Desc = AHProductList.cells(idProduct, 2).getValue();
    var Specification = AHProductList.cells(idProduct, 3).getValue();
    var Product_Category = AHProductList.cells(idProduct, 4).getValue();
    var Product_Sub_Category = AHProductList.cells(idProduct, 5).getValue();
    var Product_Sub_Sub_Category = AHProductList.cells(idProduct, 6).getValue();
    var UnitName = AHProductList.cells(idProduct, 8).getValue();
    var Prices = AHProductList.cells(idProduct, 11).getValue();
    var Product_Brand = AHProductList.cells(idProduct, 12).getValue();
    var Asset_No = String(AHProductList.cells(idProduct, 13).getValue() || '').trim();

    if (Asset_No !== '' && AssetHandoverDetail.getColumnsNum() > 17) {
        var duplicateFound = false;
        AssetHandoverDetail.forEachRow(function(rId) {
            if (String(rId) !== String(id)) {
                var existingAssetNo = String(AssetHandoverDetail.cells(rId, 17).getValue() || '').trim();
                if (existingAssetNo === Asset_No) {
                    duplicateFound = true;
                }
            }
        });
        if (duplicateFound) {
            alert('Asset No ' + Asset_No + ' sudah dipilih pada baris lain.');
            return;
        }
    }

    var qtyNum = 1;
    var priceNum = Number(String(Prices || '0').replace(/,/g, ''));
    if (isNaN(priceNum)) priceNum = 0;

    AssetHandoverDetail.cells(id, 1).setValue(Product_Sub_Sub_Category);
    AssetHandoverDetail.cells(id, 2).setValue(Product_Code);
    AssetHandoverDetail.cells(id, 3).setValue(Product_Desc);
    AssetHandoverDetail.cells(id, 4).setValue(qtyNum);
    AssetHandoverDetail.cells(id, 5).setValue(Product_Brand);
    AssetHandoverDetail.cells(id, 6).setValue('');
    AssetHandoverDetail.cells(id, 7).setValue(Specification);
    AssetHandoverDetail.cells(id, 8).setValue('Baik');
    AssetHandoverDetail.cells(id, 9).setValue(UnitName);
    AssetHandoverDetail.cells(id, 10).setValue('');
    AssetHandoverDetail.cells(id, 11).setValue(Product_Category);
    AssetHandoverDetail.cells(id, 12).setValue(Product_Sub_Category);
    AssetHandoverDetail.cells(id, 13).setValue(priceNum);
    AssetHandoverDetail.cells(id, 14).setValue(qtyNum * priceNum);
    if (AssetHandoverDetail.getColumnsNum() > 17) {
        AssetHandoverDetail.cells(id, 17).setValue(Asset_No);
    }

    if (typeof PAssetHandoverDetail !== 'undefined' && PAssetHandoverDetail && typeof PAssetHandoverDetail.setUpdated === 'function') {
        if (!PAssetHandoverDetail.getState(id)) {
            PAssetHandoverDetail.setUpdated(id, true, 'updated');
        }
    }

    window.__ahPendingNewRowId = null;
    ClosePRNew();
}

AHProductList = new dhtmlXGridObject('AHPRODUCTLIST');
AHProductList.setImagePath("./dhtmlx/imgs/");
AHProductList.setHeader("No.,Product Code,Description,Specification,Category,Sub Category,Sub Sub Category,Current Stock,Unit,Foto,Dim,Price,Product Brand,Asset No,SN");
AHProductList.attachHeader("&nbsp;,#text_filter,#text_filter,&nbsp;,&nbsp;,&nbsp;,&nbsp;,#text_filter,&nbsp;,&nbsp;,&nbsp;,&nbsp;,#text_filter,#text_filter,#text_filter");
AHProductList.setInitWidths("35,100,180,150,95,95,100,75,55,70,70,80,100,120,110");
AHProductList.setColAlign("center,center,left,left,center,center,center,right,left,center,center,right,center,center,center");
AHProductList.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
AHProductList.setColSorting("int,str,str,str,str,str,str,int,str,str,str,int,str,str,str");
AHProductList.setColumnHidden(9, true);
AHProductList.setColumnHidden(10, true);
AHProductList.attachEvent("onRowSelect", ShowPict);
AHProductList.attachEvent("onRowDblClicked", function(rId) {
    AHProductList.selectRowById(rId);
    AddPRList();
});
AHProductList.enableSmartRendering(true, 50);
AHProductList.setSkin("dhx_skyblue");
AHProductList.init();
AHProductList.clearAndLoad("hrd-asset-handover-product-data");
</script>
