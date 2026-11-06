@include('layout.plugins')
@include('layout.adminor.tab-head')

<div class="container-fluid py-3">
    <!-- Family Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-users me-2"></i>Family Members</h6>
            <div class="d-flex gap-2">
                <button type="button" id="MyFamily_Add" class="btn btn-sm btn-light text-primary" onclick="MyFamily.addRow(MyFamily.uid(),[MyFamily.getRowsNum()+1],0)">
                    <i class="fa fa-plus me-1"></i> Add Row
                </button>
                <button type="button" id="MyFamily_Delete" class="btn btn-sm btn-light text-danger" onclick="MyFamily.deleteSelectedRows()">
                    <i class="fa fa-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted" for="Family_Card_No">Family Card No (No. KK)</label>
                    <input type="text" id="Family_Card_No" class="form-control form-control-sm" placeholder="Nomor Kartu Keluarga" tabindex="6">
                </div>
            </div>
            <div id="MyFamily" style="width:100%;height:280px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Emergency Contact Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-phone me-2"></i>Emergency Contact Points</h6>
            <div class="d-flex gap-2">
                <button type="button" id="CTPoints_Add" class="btn btn-sm btn-light text-primary" onclick="CTPoints.addRow(CTPoints.uid(),[CTPoints.getRowsNum()+1],0)">
                    <i class="fa fa-plus me-1"></i> Add Contact
                </button>
                <button type="button" id="CTPoints_Delete" class="btn btn-sm btn-light text-danger" onclick="CTPoints.deleteSelectedRows()">
                    <i class="fa fa-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div id="CTPoints" style="width:100%;height:200px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Relation Contact with KSPS -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-handshake-o me-2"></i>Relation Contact with KSPS</h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Emergency_Contact_No">Contact Phone No</label>
                    <input type="text" id="Emergency_Contact_No" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Emergency_Contact_Name">Contact Name</label>
                    <input type="text" id="Emergency_Contact_Name" class="form-control form-control-sm" tabindex="6">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Emergency_Relation">Relationship</label>
                    <input type="text" id="Emergency_Relation" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted" for="Emergency_Address">Address</label>
                    <input type="text" id="Emergency_Address" class="form-control form-control-sm" tabindex="6">
                </div>
            </div>
        </div>
    </div>

    <!-- Save Action -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-3 d-flex justify-content-end">
            <button type="button" class="btn btn-primary px-4" onclick="saveFamilyData()">
                <i class="fa fa-save me-1"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<script>
function saveFamilyData() {
    if (typeof PMyFamily !== 'undefined') PMyFamily.sendData();
    if (typeof PCTPoints !== 'undefined') PCTPoints.sendData();
    alert('Family and contact data saved.');
}

MyFamily = new dhtmlXGridObject('MyFamily');
MyFamily.setImagePath("./dhtmlx/imgs/");
MyFamily.setHeader("No.,Family Status,Name,Gender,Birth Date,Last Education,Occupation");
MyFamily.setInitWidths("60,140,220,100,120,150,auto");
MyFamily.setColAlign("center,center,left,center,center,center,left");
MyFamily.setColSorting("int,str,str,str,str,str,str");
MyFamily.setColTypes("dyn,coro,txt,coro,dhxCalendar,coro,ed");
MyFamily.setDateFormat("%Y-%m-%d"); 
PMyFamily = new dataProcessor("employee-lists-detail-family-update");
PMyFamily.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}
});
PMyFamily.init(MyFamily);
MyFamily.setEditable(true);
MyFamily.setSkin("dhx_skyblue");
MyFamily.enableEditEvents(true,false,true);
MyFamily.init();
MyFamily.loadXML("xml-employee-lists-detail-family?ID=<?php echo $_GET['ID'];?>");
MyFamily.getCombo(1).put('DAUGHTER','DAUGHTER');
MyFamily.getCombo(1).put('SON','SON');
MyFamily.getCombo(1).put('WIFE','WIFE');
MyFamily.getCombo(1).put('HUSBAND','HUSBAND');
MyFamily.getCombo(1).put('ADOPTED CHILDREN','ADOPTED CHILDREN');
MyFamily.getCombo(3).put('MALE','MALE');
MyFamily.getCombo(3).put('FEMALE','FEMALE');
MyFamily.getCombo(5).put('SCHOOL YET','SCHOOL YET');
MyFamily.getCombo(5).put('PRE SCHOOL','PRE SCHOOL');
MyFamily.getCombo(5).put('ELEMENTARY SCHOOL','ELEMENTARY SCHOOL');
MyFamily.getCombo(5).put('JUNIOR HIGH SCHOOL','JUNIOR HIGH SCHOOL');
MyFamily.getCombo(5).put('HIGH SCHOOL','HIGH SCHOOL');
MyFamily.getCombo(5).put('D1','D1');
MyFamily.getCombo(5).put('D2','D2');
MyFamily.getCombo(5).put('D3','D3');
MyFamily.getCombo(5).put('S1','S1');
MyFamily.getCombo(5).put('S2','S2');

CTPoints = new dhtmlXGridObject('CTPoints');
CTPoints.setImagePath("./dhtmlx/imgs/");
CTPoints.setHeader("No.,Name,Description,Location,Contact No,Email,Phone Number");
CTPoints.setInitWidths("60,150,200,120,120,150,auto");
CTPoints.setColAlign("center,left,left,left,center,left,center");
CTPoints.setColSorting("int,str,str,str,str,str,str");
CTPoints.setColTypes("ro,ed,ed,ed,ed,ed,ed");
CTPoints.setEditable(true);
PCTPoints = new dataProcessor("employee-lists-detail-contact-points-update");
PCTPoints.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}
});
PCTPoints.setTransactionMode("POST",false);
PCTPoints.init(CTPoints);
CTPoints.setSkin("dhx_skyblue");
CTPoints.init();
CTPoints.loadXML("xml-employee-lists-detail-contact-points?ID=<?php echo $_GET['ID'];?>");
</script>
