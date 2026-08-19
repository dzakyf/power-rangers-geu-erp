<div id="AHPRODUCTLIST" style="width:700px; height:300px;position:absolute;"></div>
<div style="width:400px;height:auto;position:relative;float:left;left:700px;z-index:0;vertical-align:middle;">
<img align="absmiddle" id="PRPict" width="300" height="302"/></div>
<div class="badan" style="top:320px;left:10px;position:absolute">
	<input type="button" name="CancelPRList" id="CancelPRList" value="Batal" onclick="ClosePRNew();" />
    <input type="button" name="SubmitPRList" id="SubmitPRList" value="Submit" onclick="AddPRList();" />
</div>
<script>


function ClosePRNew() {
	parent.WinPR.window("WPR").close();
}

function ShowPict(){
	Foto=AHProductList.cells(AHProductList.getSelectedId(),9).getValue(); 
	$('#PRPict').attr("src",Foto);
}
function AddPRList() {

    let id = AssetHandoverDetail.getSelectedRowId();
    let idProduct = AHProductList.getSelectedRowId();
    
    Product_Code=AHProductList.cells(idProduct,1).getValue(); 
    Product_Desc=AHProductList.cells(idProduct,2).getValue(); 
    Product_Category=AHProductList.cells(idProduct,4).getValue(); 
    Product_Sub_Category=AHProductList.cells(idProduct,5).getValue(); 
    Product_Sub_Sub_Category=AHProductList.cells(idProduct,6).getValue(); 
    UnitName=AHProductList.cells(idProduct,8).getValue(); 
    Stock=AHProductList.cells(idProduct,7).getValue();
    Foto=AHProductList.cells(idProduct,9).getValue(); 
    Prices=AHProductList.cells(idProduct,10).getValue(); 
    Product_Brand=AHProductList.cells(idProduct,11).getValue(); 
    Specification=AHProductList.cells(idProduct,3).getValue();

    
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
	AHProductList.attachEvent("onRowSelect",ShowPict);
	AHProductList.enableSmartRendering(true, 50);
	AHProductList.setSkin("dhx_skyblue");
	AHProductList.init();
	AHProductList.clearAndLoad("hrd-asset-handover-product-data");
	

</script>