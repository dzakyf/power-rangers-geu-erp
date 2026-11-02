@include('layout.plugins')
@include('layout.adminor.tab-head')

<div class="container-fluid py-3">
    <!-- Formal Education -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-graduation-cap me-2"></i>Formal Education</h6>
        </div>
        <div class="card-body p-2">
            <div id="Akademic" style="width:100%;height:150px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Informal Education -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-certificate me-2"></i>Informal Education / Trainings</h6>
        </div>
        <div class="card-body p-2">
            <div id="Informal" style="width:100%;height:150px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Organization -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-users me-2"></i>Organization Experiences</h6>
        </div>
        <div class="card-body p-2">
            <div id="Organization" style="width:100%;height:150px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Save Action -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-3 d-flex justify-content-end">
            <button type="button" class="btn btn-primary px-4" onclick="saveEducationData()">
                <i class="fa fa-save me-1"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<script>
function saveEducationData() {
    if (typeof PAkademic !== 'undefined') PAkademic.sendData();
    if (typeof POrganization !== 'undefined') POrganization.sendData();
    if (typeof PInformal !== 'undefined') PInformal.sendData();
    alert('Data saved.');
}

Akademic = new dhtmlXGridObject('Akademic');
Akademic.setImagePath("./dhtmlx/imgs/");
Akademic.setHeader("No.,Education Level,College,Education Majors,Location,Certified Year,GPA");
Akademic.setInitWidths("60,150,220,200,100,100,100");
Akademic.setColAlign("center,center,center,left,center,center,center");
Akademic.setColSorting("int,str,str,str,str,str,str,str");
Akademic.setColTypes("ro,txt,ed,ed,ed,ed,ed");
Akademic.enableEditEvents(true,false,true);
PAkademic = new dataProcessor("recruitment-lists-detail-academic-update?Task_ID=<?php echo $_GET['ID'];?>");
PAkademic.setTransactionMode("POST",false);
PAkademic.init(Akademic);
Akademic.setEditable(true);
Akademic.setSkin("dhx_skyblue");
Akademic.init();
Akademic.loadXML("xml-recruitment-lists-detail-academic?Task_ID=<?php echo $_GET['ID'];?>");
					
Organization = new dhtmlXGridObject('Organization');
Organization.setImagePath("./dhtmlx/imgs/");
Organization.setHeader("No.,Organization,Position,Location,Year,Note");
Organization.setInitWidths("60,220,200,200,100,200");
Organization.setColAlign("center,center,center,center,center,left");
Organization.setColSorting("int,str,str,str,str,str");
Organization.setColTypes("ro,ed,ed,ed,ed,txt");
Organization.enableEditEvents(true,false,true);
POrganization = new dataProcessor("recruitment-lists-detail-organization-update?Task_ID=<?php echo $_GET['ID'];?>");
POrganization.setTransactionMode("POST",false);
POrganization.init(Organization);
Organization.setEditable(true);
Organization.setSkin("dhx_skyblue");
Organization.init();
Organization.loadXML("xml-recruitment-lists-detail-organization?ID=<?php echo $_GET['ID'];?>");
			
Informal = new dhtmlXGridObject('Informal');
Informal.setImagePath("./dhtmlx/imgs/");
Informal.setHeader("No.,Training Company,Location,Year,Note");
Informal.setInitWidths("60,150,220,100,auto");
Informal.setColAlign("center,center,center,center,left");
Informal.setColSorting("int,str,str,str,str");
Informal.setColTypes("ro,ed,ed,ed,txt");
Informal.enableEditEvents(true,false,true);
PInformal = new dataProcessor("recruitment-lists-detail-informal-update?Task_ID=<?php echo $_GET['ID'];?>");
PInformal.setTransactionMode("POST",false);
PInformal.init(Informal);
Informal.setEditable(true);
Informal.setSkin("dhx_skyblue");
Informal.init();
Informal.loadXML("xml-recruitment-lists-detail-informal?ID=<?php echo $_GET['ID'];?>");
</script>
