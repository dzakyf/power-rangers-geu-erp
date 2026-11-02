@include('layout.plugins')
@include('layout.adminor.tab-head')

<div class="container-fluid py-3" id="CPExperiences">
    <!-- Work Experiences -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-briefcase me-2"></i>Work Experiences</h6>
        </div>
        <div class="card-body p-2">
            <div id="Experiences" style="width:100%;height:160px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- R&D Skills -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-cogs me-2"></i>Research & Development Skills / Competencies</h6>
        </div>
        <div class="card-body p-2">
            <div id="OtherSkill" style="width:100%;height:140px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Awards & Scholarships -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-trophy me-2"></i>Awards, Grants, Prizes & Scholarships</h6>
        </div>
        <div class="card-body p-2">
            <div id="Awards" style="width:100%;height:120px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Languages -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-language me-2"></i>Language Proficiencies</h6>
        </div>
        <div class="card-body p-2">
            <div id="Language" style="width:100%;height:140px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Save Action -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-3 d-flex justify-content-end">
            <button type="button" class="btn btn-primary px-4" onclick="saveSkillData()">
                <i class="fa fa-save me-1"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<script>
function saveSkillData() {
    if (typeof PExperiences !== 'undefined') PExperiences.sendData();
    if (typeof POtherSkill !== 'undefined') POtherSkill.sendData();
    if (typeof PAwards !== 'undefined') PAwards.sendData();
    if (typeof PLanguage !== 'undefined') PLanguage.sendData();
    alert('Data saved.');
}

Experiences = new dhtmlXGridObject('Experiences');
Experiences.setImagePath("./dhtmlx/imgs/");
Experiences.setHeader("No.,Start Date,End Date,Position,Company Name,Last Salary,Reason of Resign");
Experiences.setInitWidths("60,110,110,180,200,120,auto");
Experiences.setColAlign("center,center,center,left,left,right,left");
Experiences.setColSorting("int,str,str,str,str,str,str");
Experiences.enableEditEvents(true,false,true);
PExperiences = new dataProcessor("recruitment-lists-detail-experience-update");
PExperiences.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}
});
PExperiences.setTransactionMode("POST",false);
PExperiences.init(Experiences);
Experiences.setDateFormat("%Y-%m-%d"); 
Experiences.setColTypes("ro,dhxCalendar,dhxCalendar,ed,ed,ed,ed");
Experiences.setEditable(true);
Experiences.setSkin("dhx_skyblue");
Experiences.init();
Experiences.loadXML("xml-recruitment-lists-detail-experience?ID=<?php echo $_GET['ID'];?>");

OtherSkill = new dhtmlXGridObject('OtherSkill');
OtherSkill.setImagePath("./dhtmlx/imgs/");
OtherSkill.setHeader("No.,Category,Subject/Area,Year,Competences");
OtherSkill.setInitWidths("60,180,220,100,auto");
OtherSkill.setColAlign("center,left,left,center,left");
OtherSkill.setColSorting("int,str,str,str,str");
OtherSkill.setColTypes("ro,ed,ed,ed,txt");
OtherSkill.enableEditEvents(true,false,true);
POtherSkill = new dataProcessor("recruitment-lists-detail-other-skill-update");
POtherSkill.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}
});
POtherSkill.setTransactionMode("POST",false);
POtherSkill.init(OtherSkill);
OtherSkill.setEditable(true);
OtherSkill.setSkin("dhx_skyblue");
OtherSkill.init();
OtherSkill.loadXML("xml-recruitment-lists-detail-other-skill?ID=<?php echo $_GET['ID'];?>");

Awards = new dhtmlXGridObject('Awards');
Awards.setImagePath("./dhtmlx/imgs/");
Awards.setHeader("No.,Month and Year of Winning,Place,Detail,Title");
Awards.setInitWidths("60,180,200,220,auto");
Awards.setColAlign("center,center,left,left,left");
Awards.setColSorting("int,str,str,str,str");
Awards.setColTypes("ro,ed,ed,ed,txt");
Awards.enableEditEvents(true,false,true);
PAwards = new dataProcessor("recruitment-lists-detail-award-update");
PAwards.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}
});
PAwards.setTransactionMode("POST",false);
PAwards.init(Awards);
Awards.setEditable(true);
Awards.setSkin("dhx_skyblue");
Awards.init();
Awards.loadXML("xml-recruitment-lists-detail-award?ID=<?php echo $_GET['ID'];?>");

Language = new dhtmlXGridObject('Language');
Language.setImagePath("./dhtmlx/imgs/");
Language.setHeader("No.,Language,Oral,Writing,Additional Information");
Language.setInitWidths("60,180,120,120,auto");
Language.setColAlign("center,left,center,center,left");
Language.setColSorting("int,str,str,str,str");
Language.setColTypes("ro,ed,coro,coro,txt");
Language.enableEditEvents(true,false,true);
PLanguage = new dataProcessor("recruitment-lists-detail-language-update");
PLanguage.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}
});
PLanguage.init(Language);
Language.setEditable(true);
Language.setSkin("dhx_skyblue");
Language.init();
Language.loadXML("xml-recruitment-lists-detail-language?Task_ID=<?php echo $_GET['ID'];?>");
Language.getCombo(2).put('GOOD','GOOD');
Language.getCombo(2).put('ENOUGH','ENOUGH');
Language.getCombo(2).put('LESS','LESS');
Language.getCombo(3).put('GOOD','GOOD');
Language.getCombo(3).put('ENOUGH','ENOUGH');
Language.getCombo(3).put('LESS','LESS');
</script>
