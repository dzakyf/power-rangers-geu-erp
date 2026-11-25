@include('layout.plugins')
@include('layout.adminor.tab-head')

<div class="container-fluid py-3">
    <!-- Formal Education -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-graduation-cap me-2"></i>Formal Education</h6>
            <div class="d-flex gap-2">
                <button type="button" id="Akademic_Add" class="btn btn-sm btn-light text-primary" onclick="Akademic.addRow(Akademic.uid(),[Akademic.getRowsNum()+1],0)">
                    <i class="fa fa-plus me-1"></i> Add Education
                </button>
                <button type="button" id="Akademic_Delete" class="btn btn-sm btn-light text-danger" onclick="Akademic.deleteSelectedRows()">
                    <i class="fa fa-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="Akademic" style="width:100%;height:180px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Informal Education -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-certificate me-2"></i>Informal Education / Trainings</h6>
            <div class="d-flex gap-2">
                <button type="button" id="Informal_Add" class="btn btn-sm btn-light text-primary" onclick="Informal.addRow(Informal.uid(),[Informal.getRowsNum()+1],0)">
                    <i class="fa fa-plus me-1"></i> Add Training
                </button>
                <button type="button" id="Informal_Delete" class="btn btn-sm btn-light text-danger" onclick="Informal.deleteSelectedRows()">
                    <i class="fa fa-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="Informal" style="width:100%;height:180px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Organization -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-users me-2"></i>Organization Experiences</h6>
            <div class="d-flex gap-2">
                <button type="button" id="Organization_Add" class="btn btn-sm btn-light text-primary" onclick="Organization.addRow(Organization.uid(),[Organization.getRowsNum()+1],0)">
                    <i class="fa fa-plus me-1"></i> Add Organization
                </button>
                <button type="button" id="Organization_Delete" class="btn btn-sm btn-light text-danger" onclick="Organization.deleteSelectedRows()">
                    <i class="fa fa-trash me-1"></i> Delete Selected
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="Organization" style="width:100%;height:180px; background-color:white;overflow:hidden"></div>
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
var empEduTaskId = {!! json_encode((string) ($_GET['ID'] ?? '')) !!};

function saveEducationData() {
    if (typeof PAkademic !== 'undefined') PAkademic.sendData();
    if (typeof POrganization !== 'undefined') POrganization.sendData();
    if (typeof PInformal !== 'undefined') PInformal.sendData();
    alert('Education and organization data saved.');
}

Akademic = new dhtmlXGridObject('Akademic');
Akademic.setImagePath("./dhtmlx/imgs/");
Akademic.setHeader("No.,Education Level,College,Education Majors,Location,Certified Year,GPA");
Akademic.setInitWidths("60,150,220,200,120,110,auto");
Akademic.setColAlign("center,left,left,left,left,center,center");
Akademic.setColSorting("int,str,str,str,str,str,str");
Akademic.setColTypes("ro,txt,ed,ed,ed,ed,ed");
Akademic.enableEditEvents(true,false,true);
PAkademic = new dataProcessor("{{ url('employee-lists-detail-academic-update') }}?Task_ID=" + encodeURIComponent(empEduTaskId));
PAkademic.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID": empEduTaskId}
});
PAkademic.init(Akademic);
Akademic.setEditable(true);
Akademic.setSkin("dhx_skyblue");
Akademic.init();
Akademic.loadXML("{{ url('xml-employee-lists-detail-academic') }}?Task_ID=" + encodeURIComponent(empEduTaskId));

Organization = new dhtmlXGridObject('Organization');
Organization.setImagePath("./dhtmlx/imgs/");
Organization.setHeader("No.,Organization,Position,Location,Year,Note");
Organization.setInitWidths("60,220,200,150,110,auto");
Organization.setColAlign("center,left,left,left,center,left");
Organization.setColSorting("int,str,str,str,str,str");
Organization.setColTypes("ro,ed,ed,ed,ed,txt");
Organization.enableEditEvents(true,false,true);
POrganization = new dataProcessor("{{ url('employee-lists-detail-organization-update') }}?Task_ID=" + encodeURIComponent(empEduTaskId));
POrganization.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID": empEduTaskId}
});
POrganization.init(Organization);
Organization.setEditable(true);
Organization.setSkin("dhx_skyblue");
Organization.init();
Organization.loadXML("{{ url('xml-employee-lists-detail-organization') }}?ID=" + encodeURIComponent(empEduTaskId));

Informal = new dhtmlXGridObject('Informal');
Informal.setImagePath("./dhtmlx/imgs/");
Informal.setHeader("No.,Training Company,Location,Year,Note");
Informal.setInitWidths("60,200,200,110,auto");
Informal.setColAlign("center,left,left,center,left");
Informal.setColSorting("int,str,str,str,str");
Informal.setColTypes("ro,ed,ed,ed,txt");
Informal.enableEditEvents(true,false,true);
PInformal = new dataProcessor("{{ url('employee-lists-detail-informal-update') }}?Task_ID=" + encodeURIComponent(empEduTaskId));
PInformal.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID": empEduTaskId}
});
PInformal.init(Informal);
Informal.setEditable(true);
Informal.setSkin("dhx_skyblue");
Informal.init();
Informal.loadXML("{{ url('xml-employee-lists-detail-informal') }}?ID=" + encodeURIComponent(empEduTaskId));
</script>
