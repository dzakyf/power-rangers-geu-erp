 @include('layout.plugins')
<?php
error_reporting(0);
?>


<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    <tr height="30">
        <td class="badan" colspan="6">
			<div id="EMployeeHistory" style="width:auto;height:370px; background-color:white;overflow:hidden"></div>
			<input type="button" id="EMployeeHistoryExel" name="EMployeeHistoryExel" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="EMployeeHistory.toExcel('./export/generate.php');"  /> 
        </td>
	</tr>
   
 
</table>

<script>

			EMployeeHistory = new dhtmlXGridObject('EMployeeHistory');
			EMployeeHistory.setImagePath("./dhtmlx/imgs/");
			EMployeeHistory.setHeader("No.,Task ID,NIK,Date,Activity,Reference,Note,Start Date,End Date,Remark,User Trx,Date Trx");
			EMployeeHistory.setInitWidths("60,100,100,100,100,200,200,100,100,100,100,100,100,100,100,100,100");
			EMployeeHistory.setColAlign("center,center,center,left,center,center,center,center,center,center,center,center,center,center,center,center,center");
			EMployeeHistory.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
			EMployeeHistory.setColTypes("ro,ro,ed,coro,ro,ro,ed,ch,ed,ed,ed,ed,ed,ed,ed,ed,ed");
			EMployeeHistory.setDateFormat("%Y-%m-%d"); 
			EMployeeHistory.setSkin("dhx_skyblue");
			EMployeeHistory.enableEditEvents(true,false,true);
			EMployeeHistory.init();
			EMployeeHistory.loadXML("xml-recruitment-lists-detail-history?ID=<?php echo $_GET['ID'];?>");
			
</script>