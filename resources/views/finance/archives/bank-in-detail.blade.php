
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">BANK IN DETAIL</div>

	<div id="GridBankINd" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	
		


<script>
	

		 
		GridBankIN = new dhtmlXGridObject('GridBankINd');
		GridBankIN.setImagePath("./dhtmlx/imgs/");
		GridBankIN.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val,Department");
		GridBankIN.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total","",""]);
		GridBankIN.setInitWidths("70,350,100,150,200,100,100,100");
		GridBankIN.setColAlign("center,left,center,left,left,right,right,center");
		GridBankIN.setColTypes("ro,combo,ro,ed,ed,edn,ron,combo");
		GridBankIN.setColSorting("int,str,str,str,str,str,str,str");
		GridBankIN.setDateFormat("%Y-%m-%d"); 
		GridBankIN.setEditable(false);
		GridBankIN.setNumberFormat("0,000.00",5,".",",");
		GridBankIN.setNumberFormat("0,000.00",6,".",",");
		GridBankIN.setSkin("dhx_skyblue");
		
		GridBankIN.init();
		
		
		refreshdatadetail();
				
		function refreshdatadetail(){
					GridBankIN.clearAndLoad("xml-finance-report-archive-bank-in-detail?ID=<?php echo $_GET['ID'];?>");

		}
		
		</script>
