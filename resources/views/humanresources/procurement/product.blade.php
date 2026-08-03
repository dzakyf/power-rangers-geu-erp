<div id="PRPRODUCTLIST" style="width:700px; height:300px;position:absolute;"></div>
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


function ShowPict2(){
	Foto=PRProductList.cells(PRProductList.getSelectedId(),4).getValue(); 
	$('#PRPict').attr("src",Foto);
}
function ShowPict(){
	Foto=PRProductList.cells(PRProductList.getSelectedId(),7).getValue(); 
	$('#PRPict').attr("src",Foto);
}
function AddPRList() {
    PRCats = $("#Category").val();

    let id = PRDetail.getSelectedRowId();
    let idProduct = PRProductList.getSelectedRowId();
    
    Product_Code=PRProductList.cells(idProduct,1).getValue(); 
    Product_Desc=PRProductList.cells(idProduct,2).getValue(); 
    UnitName=PRProductList.cells(idProduct,5).getValue(); 
    Stock=PRProductList.cells(idProduct,6).getValue(); 
    Specification=PRProductList.cells(idProduct,5).getValue(); 
    Foto=PRProductList.cells(idProduct,6).getValue(); 
    Prices=PRProductList.cells(idProduct,8).getValue(); 
    Specification=PRProductList.cells(idProduct,3).getValue();

    
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
	PRProductList = new dhtmlXGridObject('PRPRODUCTLIST');
	PRProductList.setImagePath("./dhtmlx/imgs/");
	PRProductList.setHeader("No.,Product Code,Description,Specification,Current Stock,Unit,Foto,Dim,Price");
	PRProductList.attachHeader("&nbsp;,#text_filter,#text_filter,&nbsp;,&nbsp;,&nbsp;,&nbsp;");
	PRProductList.setInitWidths("30,100,200,200,100,50,0,100,100");
	PRProductList.setColAlign("center,center,left,center,right,left,center,center,center");
	PRProductList.setColTypes("dyn,ro,ro,ro,ro,ro,ro,ro,ro");
	PRProductList.setColSorting("int,str,str,str,str,str,str");
	PRProductList.attachEvent("onRowSelect",ShowPict);
	PRProductList.enableSmartRendering(true, 50);
	PRProductList.setSkin("dhx_skyblue");
	PRProductList.init();
	
	Supplies_Type = $("#Supplies_Type").val();
	PRPRoductCategory = $("#Category").val();
	subCategory = $("#Sub_Category").val();
	PRProductList.clearAndLoad("xml-hrd-procurement-product?Category=" + PRPRoductCategory);
	

</script>