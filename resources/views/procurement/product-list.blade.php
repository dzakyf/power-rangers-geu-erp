<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
		$QProd = DB::select(DB::raw("SELECT Supplies_Type,Supplies_Desc FROM supplies_type"));

	
?>
<div style="height:30px;padding-left:20px;display:none;">
Supplies Type 
    <select id="Inventory_Type" onchange="ChangeSupplies(this.value);">
        <option value="">Select</option>
			<?php 
			foreach($QProd as $supplies){
					echo "<option value='$supplies->Supplies_Type'>$supplies->Supplies_Type - $supplies->Supplies_Desc</option>";
				
			}
			?>
    </select>
</div>
<div id="PRPRODUCTLIST" style="width:700px; height:300px;position:absolute;"></div>
<div style="width:400px;height:auto;position:relative;float:left;left:700px;z-index:0;vertical-align:middle;">
<img align="absmiddle" id="PRPict" width="300" height="302"/></div>
<div class="badan" style="top:320px;left:10px;position:absolute">
	<input type="button" name="CancelPRList" id="CancelPRList" value="Batal" onclick="ClosePRNew();" />
    <input type="button" name="SubmitPRList" id="SubmitPRList" value="Submit" onclick="AddPRList();" />
</div>
<script>

function ChangeSupplies(SuppliesType){
	PRCats = $("#Category").val();
	PRPRoductCategory = $("#Sub_Category").val();
	PRPRoductCategory2 = $("#Sub_Category2").val();
	PRProductList.clearAndLoad("./lib/xml/Asset/Purchase/PR_Product_List.php?Category=" + PRCats + "&Sub_Category=" + PRPRoductCategory  + "&Purchase_Type=" + PRPRoductCategory2);	
	
}
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
PRPRoductCategory = $("#Sub_Category").val();
PRPRoductCategory2 = $("#Sub_Category2").val();

  for (var i=0; i<PRProductList.getRowsNum(); i++) {
	 
		 Product_Code=PRProductList.cells2(i,2).getValue(); 
		 Product_Desc=PRProductList.cells2(i,3).getValue(); 
		 BinLoc=PRProductList.cells2(i,4).getValue(); 
		 Stock=PRProductList.cells2(i,5).getValue(); 
		 Specification=PRProductList.cells2(i,6).getValue(); 
		 Foto=PRProductList.cells2(i,7).getValue(); 
		 UnitName=PRProductList.cells2(i,8).getValue(); 
		 Prices=PRProductList.cells2(i,9).getValue(); 
		 CheckPR=PRProductList.cells2(i,1).getValue(); 

		 if (CheckPR == 1) {
			PRNewDetail.addRow(PRNewDetail.uid(),[PRNewDetail.getRowsNum()+1,$("#Sub_Category2").val(),Product_Code,Product_Desc,1,Specification,UnitName,'',PRCats,PRPRoductCategory,Prices],PRNewDetail.getRowsNum());
		 }
  }
  ClosePRNew();
}
PRPRoductCategory = $("#Sub_Category").val();
	PRProductList = new dhtmlXGridObject('PRPRODUCTLIST');
	PRProductList.setImagePath(PathDHTMLX + "imgs/");
	PRProductList.setHeader("No.,Chk,Product Code,Description,Bin Location,Current Stock,Specification,Foto,Unit,Price");
	PRProductList.attachHeader("&nbsp;,&nbsp;,#text_filter,#text_filter,&nbsp;,&nbsp;,&nbsp;,&nbsp;");
	PRProductList.setInitWidths("30,50,100,200,100,100,200,0,100,0");
	PRProductList.setColAlign("center,center,center,left,center,right,left,center,center,center");
	PRProductList.setColTypes("dyn,ch,ro,ro,ro,ro,ro,ro,ro,ro");
	PRProductList.setColSorting("int,str,str,str,str,str,str,str");
	PRProductList.attachEvent("onRowSelect",ShowPict);
	PRProductList.enableSmartRendering(true, 50);
	PRProductList.setSkin("dhx_skyblue");
	PRProductList.init();
	PRCats = $("#Category").val();
	PRPRoductCategory = $("#Sub_Category").val();
	PRPRoductCategory2 = $("#Sub_Category2").val();
	PRProductList.loadXML("./lib/xml/Asset/Purchase/PR_Product_List.php?Category=" + PRCats + "&Sub_Category=" + PRPRoductCategory  + "&Purchase_Type=" + PRPRoductCategory2);	


</script>
