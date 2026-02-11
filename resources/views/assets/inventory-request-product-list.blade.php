<div id="PRPRODUCTLIST" style="width:700px; height:300px;position:absolute;"></div>
<div style="width:400px;height:auto;position:relative;float:left;left:700px;z-index:0;vertical-align:middle;">
<img align="absmiddle" id="PRPict" width="300" height="302"/></div>
<div class="badan" style="top:320px;left:10px;position:absolute">
	<input type="button" name="CancelPRList" id="CancelPRList" value="Batal" onclick="ClosePRNew();" />
    <input type="button" name="SubmitPRList" id="SubmitPRList" value="Submit" onclick="AddPRList();" />
</div>
<script>


function ClosePRNew() {
	parent.winPR.window("WPR").close();
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
PRPRoductCategory = $("#Sub_Category").val();
PRPRoductCategory2 = $("#Supplies_Type").val();

  for (var i=0; i<PRProductList.getRowsNum(); i++) {
	 
		 Product_Code=PRProductList.cells2(i,2).getValue(); 
		 Product_Desc=PRProductList.cells2(i,3).getValue(); 
		 UnitName=PRProductList.cells2(i,6).getValue(); 
		 Stock=PRProductList.cells2(i,5).getValue(); 
		 Specification=PRProductList.cells2(i,6).getValue(); 
		 Foto=PRProductList.cells2(i,7).getValue(); 
		 Prices=PRProductList.cells2(i,9).getValue(); 
		 CheckPR=PRProductList.cells2(i,1).getValue(); 
		 Specification=PRProductList.cells2(i,4).getValue(); 
		var IDNYA = PRProductList.getRowId(i);
		 if (CheckPR == 1) {
			PRNewDetail.addRow(IDNYA,[PRNewDetail.getRowsNum()+1,$("#Sub_Inventory").val(),Product_Code,Product_Desc,1,Specification,UnitName,'',PRCats,PRPRoductCategory,Prices],PRNewDetail.getRowsNum());
		 }
  }
  ClosePRNew();
}
	PRProductList = new dhtmlXGridObject('PRPRODUCTLIST');
	PRProductList.setImagePath("./dhtmlx/imgs/");
	PRProductList.setHeader("No.,Chk,Product Code,Description,Specification,Current Stock,Unit,Foto,Dim,Price");
	PRProductList.attachHeader("&nbsp;,&nbsp;,#text_filter,#text_filter,&nbsp;,&nbsp;,&nbsp;,&nbsp;");
	PRProductList.setInitWidths("30,50,100,200,200,100,50,0,100,100");
	PRProductList.setColAlign("center,center,center,left,center,right,left,center,center,center");
	PRProductList.setColTypes("dyn,ch,ro,ro,ro,ro,ro,ro,ro,ro");
	PRProductList.setColSorting("int,str,str,str,str,str,str,str");
	PRProductList.attachEvent("onRowSelect",ShowPict);
	PRProductList.enableSmartRendering(true, 50);
	PRProductList.setSkin("dhx_skyblue");
	PRProductList.init();
	
	Supplies_Type = $("#Supplies_Type").val();
	PRPRoductCategory = $("#Category").val();
	subCategory = $("#Sub_Category").val();
	PRProductList.clearAndLoad("xml-inventory-request-product-lists?Supplies_Type=" + $("#Sub_Inventory").val());	
	

</script>