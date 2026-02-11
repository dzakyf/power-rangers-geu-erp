
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">BANK OUT DETAIL</div>

<div id="GridBankOUTd" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>


	<script>
		

		GridBankOUT = new dhtmlXGridObject('GridBankOUTd');
		GridBankOUT.setImagePath("./dhtmlx/imgs/");
		GridBankOUT.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val,Department");
		GridBankOUT.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', "#stat_total", "", ""]);
		GridBankOUT.setInitWidths("70,350,100,150,200,100,100,100");
		GridBankOUT.setColAlign("center,left,center,left,left,right,right,center");
		GridBankOUT.setColTypes("ro,combo,ro,ed,ed,edn,ron,combo");
		GridBankOUT.setColSorting("int,str,str,str,str,str,str,str");
		GridBankOUT.setDateFormat("%Y-%m-%d");
		GridBankOUT.setEditable(false);
		GridBankOUT.setNumberFormat("0,000", 5, ".", ",");
		GridBankOUT.setNumberFormat("0,000", 6, ".", ",");
		GridBankOUT.setSkin("dhx_skyblue");
		GridBankOUT.init();
		refreshdatadetail();
		
		function refreshdatadetail() {
			GridBankOUT.clearAndLoad("xml-finance-report-archive-bank-out-detail?ID=<?php echo $_GET['ID']; ?>");

		}
		
	</script>