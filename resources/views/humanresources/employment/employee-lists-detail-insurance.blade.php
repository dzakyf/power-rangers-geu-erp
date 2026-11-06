@include('layout.plugins')
@include('layout.adminor.tab-head')
<?php
error_reporting(0);
?>

<div class="container-fluid py-3">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-shield me-2"></i>Employee Insurances</h6>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-light text-primary" onclick="tambahdatainsuransiS()">
                    <i class="fa fa-plus me-1"></i> Add Row
                </button>
                <button type="button" class="btn btn-sm btn-light text-danger" onclick="InsuranceGrid.deleteSelectedRows()">
                    <i class="fa fa-trash me-1"></i> Delete Selected
                </button>
                <button type="button" class="btn btn-sm btn-light text-primary fw-bold" onclick="PInsuranceGrid.sendData();alert('Insurance data saved.');">
                    <i class="fa fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="InsuranceGrid" style="width:100%;height:450px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<script>
function tambahdatainsuransiS(){
    InsuranceGrid.addRow(InsuranceGrid.uid(),[InsuranceGrid.getRowsNum()+1,""],0);
}

InsuranceGrid = new dhtmlXGridObject('InsuranceGrid');
InsuranceGrid.setImagePath("./dhtmlx/imgs/");
InsuranceGrid.setHeader("No.,Employee Name,On Behalf,On Behalf Status,Insurances,Card No,Polis No,In Salary,Polis Type,Polis Benefit,Polis Reimbursement,Polis Status,Date Start,End Date,Date Submission,Employee Collateral,Salary Component");
InsuranceGrid.setInitWidths("60,140,150,130,120,120,120,80,110,120,130,100,100,100,120,130,130");
InsuranceGrid.setColAlign("center,left,left,center,left,center,center,center,center,left,center,center,center,center,center,left,left");
InsuranceGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
InsuranceGrid.setColTypes("ro,ro,ed,coro,ro,ro,ed,ch,ed,ed,ed,ed,ed,ed,ed,ed,ed");
InsuranceGrid.setDateFormat("%Y-%m-%d"); 
PInsuranceGrid = new dataProcessor("employee-lists-detail-insurance-update");
PInsuranceGrid.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}
});
PInsuranceGrid.init(InsuranceGrid);
InsuranceGrid.setEditable(true);
InsuranceGrid.setSkin("dhx_skyblue");
InsuranceGrid.enableEditEvents(true,false,true);
InsuranceGrid.init();
InsuranceGrid.loadXML("xml-employee-lists-detail-insurance?ID=<?php echo $_GET['ID'];?>");
InsuranceGrid.getCombo(3).put("OWNER","WIFE");
InsuranceGrid.getCombo(3).put("ISTRI","ISTRI");
InsuranceGrid.getCombo(3).put("ANAK KE 1","ANAK KE 1");
InsuranceGrid.getCombo(3).put("ANAK KE 2","ANAK KE 2");
InsuranceGrid.getCombo(3).put("ANAK KE 3","ANAK KE 3");
InsuranceGrid.getCombo(3).put("ANAK KE 4","ANAK KE 4");
</script>
