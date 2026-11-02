@include('layout.plugins')
@include('layout.adminor.tab-head')
<?php
error_reporting(0);
?>

<div class="container-fluid py-3">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-history me-2"></i>Recruitment & Employee History</h6>
            <button type="button" id="EMployeeHistoryExel" name="EMployeeHistoryExel" class="btn btn-sm btn-light text-dark" onclick="EMployeeHistory.toExcel('./export/generate.php');">
                <i class="fa fa-file-excel-o text-success me-1"></i> Export Excel
            </button>
        </div>
        <div class="card-body p-2">
            <div id="EMployeeHistory" style="width:100%;height:450px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<script>
	EMployeeHistory = new dhtmlXGridObject('EMployeeHistory');
	EMployeeHistory.setImagePath("./dhtmlx/imgs/");
	EMployeeHistory.setHeader("No.,Task ID,NIK,Date,Activity,Reference,Note,Start Date,End Date,Remark,User Trx,Date Trx");
	EMployeeHistory.setInitWidths("60,110,110,100,120,180,180,100,100,120,110,120");
	EMployeeHistory.setColAlign("center,center,center,center,left,left,left,center,center,left,center,center");
	EMployeeHistory.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
	EMployeeHistory.setColTypes("ro,ro,ed,coro,ro,ro,ed,ch,ed,ed,ed,ed");
	EMployeeHistory.setDateFormat("%Y-%m-%d"); 
	EMployeeHistory.setSkin("dhx_skyblue");
	EMployeeHistory.enableEditEvents(true,false,true);
	EMployeeHistory.init();
	EMployeeHistory.loadXML("xml-recruitment-lists-detail-history?ID=<?php echo $_GET['ID'];?>");
</script>
