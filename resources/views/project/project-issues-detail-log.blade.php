<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ISSUE LOGS</div>
<div id="IssueListsDetailLog" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
<div id="DivIssueListsDetailLog"></div>
<script>

IssueListsDetailLog = new dhtmlXGridObject('IssueListsDetailLog');
IssueListsDetailLog.setImagePath("./dhtmlx/imgs/");
IssueListsDetailLog.setHeader("No,Action Date,Action By,Action Note,Date,Resolution Date,Priority,Issue Name,Category,Description,Status,Risk Information,Loss Revenue,Solving Method,Assignment");
IssueListsDetailLog.setInitWidths("60,100,100,100,200,100,100,100,200,100,200,100,100,200,100,100,100,100,100,100,100,100,100,100,100,100");
IssueListsDetailLog.setColAlign("center,center,center,left,center,center,center,left,center,left,center,left,right,left,center,center,center,center,center");
IssueListsDetailLog.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
IssueListsDetailLog.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
IssueListsDetailLog.setSkin("dhx_skyblue");
IssueListsDetailLog.setNumberFormat("0,000",12,".",",");
IssueListsDetailLog.init();
IssueListsDetailLog.clearAndLoad('xml-project-issues-detail-log?ID=<?php echo $_GET['ID']?>');
</script>
