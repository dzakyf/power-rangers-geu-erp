<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CASH IN DETAIL</div>

<div id="GridCash" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
		


<script>
	
		 
		GridCash = new dhtmlXGridObject('GridCash');
		GridCash.setImagePath("./dhtmlx/imgs/");
		GridCash.setHeader("No.,Account No,Date,Reference,Remark,Value,Currency Val,Department");
		GridCash.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total","",""]);
		GridCash.setInitWidths("70,350,100,150,200,100,100,100");
		GridCash.setColAlign("center,left,center,left,left,right,right,center");
		GridCash.setColTypes("ro,ro,ro,ed,ed,edn,ron,combo");
		GridCash.setColSorting("int,str,str,str,str,str,str");
		GridCash.setDateFormat("%Y-%m-%d"); 
		GridCash.setEditable(false);
		GridCash.setNumberFormat("0,000",5,".",",");
		GridCash.setNumberFormat("0,000",6,".",",");
		GridCash.enableKeyboardSupport(true);
		GridCash.setSkin("dhx_skyblue");
		GridCash.init();
				
		
		refreshdatadetail();
				
		function refreshdatadetail(){
					GridCash.clearAndLoad("xml-finance-report-archive-cash-in-detail?ID=<?php echo $_GET['ID'];?>");

		}


		</script>
