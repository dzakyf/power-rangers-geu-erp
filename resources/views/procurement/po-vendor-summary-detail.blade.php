<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">DETAIL SUPPLIER</div>

		<div id="VendorSummaryDetail" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>


<script>
function VendorSummaryDetailDoubleClick() {

	VendorSummaryDetailMID = VendorSummaryDetail.getSelectedId();
	LoadM('VendorSummaryDetailDiv','po-payment-monitoring-detail?ID=' + VendorSummaryDetailMID);

}
VendorSummaryDetail = new dhtmlXGridObject('VendorSummaryDetail');
VendorSummaryDetail.setImagePath("./dhtmlx/imgs/");
VendorSummaryDetail.setHeader("No.,System No,PR No,PO No,Supplier,PO Date,Category,Sub Category,Purchase Type,Request By, Department, Status, Created By, Product");
VendorSummaryDetail.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
VendorSummaryDetail.setInitWidths("50,0,165,165,210,0,0,0,120,110,0,0,100,450");
VendorSummaryDetail.setColAlign("left,left,center,center,left,center,center,center,center,center,left,left");
VendorSummaryDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
VendorSummaryDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
VendorSummaryDetail.setSkin("dhx_skyblue");
VendorSummaryDetail.init();
VendorSummaryDetail.loadXML("xml-po-vendor-summary-detail?ID=<?php echo $_GET['ID']?>");

</script>
