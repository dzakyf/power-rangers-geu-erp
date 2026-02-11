<script>
			Akademic = new dhtmlXGridObject('Akademic');
			Akademic.setImagePath("./dhtmlx/imgs/");
			Akademic.setHeader("No.,Education Level,College,Education Majors,Location,Certified Year,GPA");
			Akademic.setInitWidths("60,150,220,200,100,100,100");
			Akademic.setColAlign("center,center,center,left,center,center,center");
			Akademic.setColSorting("int,str,str,str,str,str,str,str");
			Akademic.setColTypes("ro,txt,ed,ed,ed,ed,ed");
			Akademic.enableEditEvents(true,false,true);
			PAkademic = new dataProcessor("./HR/Personel/Recruitment_Akademic_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			PAkademic.setTransactionMode("POST",false);
			PAkademic.init(Akademic);
			Akademic.setEditable(true);
			Akademic.setSkin("dhx_skyblue");
			Akademic.init();
			Akademic.loadXML("employee-lists-detail-academic?Task_ID=<?php echo $datahasil->Task_ID;?>");

			Language = new dhtmlXGridObject('Language');
			Language.setImagePath("./dhtmlx/imgs/");
			Language.setHeader("No.,Language,Oral,Writing,Additional Information");
			Language.setInitWidths("60,220,100,100,auto");
			Language.setColAlign("center,center,center,center,left");
			Language.setColSorting("int,str,str,str,str");
			Language.setColTypes("ro,ed,coro,coro,txt");
			Language.enableEditEvents(true,false,true);
			PLanguage = new dataProcessor("./HR/Personel/Recruitment_Language_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			PLanguage.setTransactionMode("POST",false);
			PLanguage.init(Language);
			Language.setEditable(true);
			Language.setSkin("dhx_skyblue");
			Language.init();
			Language.loadXML("employee-lists-detail-language?Task_ID=<?php echo $datahasil->Task_ID;?>");
			Language.getCombo(2).put ('GOOD','GOOD');
			Language.getCombo(2).put ('ENOUGH','GOOD');
			Language.getCombo(2).put ('LESS','LESS');
			Language.getCombo(3).put ('GOOD','GOOD');
			Language.getCombo(3).put ('ENOUGH','ENOUGH');
			Language.getCombo(3).put ('LESS','LESS');

			CTPoints = new dhtmlXGridObject('CTPoints');
			CTPoints.setImagePath("./dhtmlx/imgs/");
			CTPoints.setHeader("No.,Name,Description,Location,Contact No,Email,Phone Number");
			CTPoints.setInitWidths("60,120,220,100,100,100,auto");
			CTPoints.setColAlign("center,center,center,left,center,center,center");
			CTPoints.setColSorting("int,str,str,str,str,str,str,str");
			CTPoints.setColTypes("ro,ed,txt,ed,ed,ed,ed");
			CTPoints.enableEditEvents(true,false,true);
			PCTPoints = new dataProcessor("./HR/Personel/Recruitment_Contact_Points_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			PCTPoints.setTransactionMode("POST",false);
			PCTPoints.init(CTPoints);
			CTPoints.setEditable(true);
			CTPoints.setSkin("dhx_skyblue");
			CTPoints.init();
			CTPoints.loadXML("employee-lists-detail-contact-points?ID=<?php echo $datahasil->Task_ID;?>");

			Driving_Licenses = new dhtmlXGridObject('Driving_Licenses');
			Driving_Licenses.setImagePath("./dhtmlx/imgs/");
			Driving_Licenses.setHeader("No.,Description,,Note");
			Driving_Licenses.setInitWidths("60,320,100,300");
			Driving_Licenses.setColAlign("center,left,center,left");
			Driving_Licenses.setColSorting("int,str,str,str");
			Driving_Licenses.enableEditEvents(true,false,true);
			Driving_Licenses.setColTypes("ro,ro,ch,txt");
			Driving_Licenses.setEditable(true);
			Driving_Licenses.enableEditEvents(true,false,true);
			PDriving_Licenses = new dataProcessor("./HR/Personel/Recruitment_Driving_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			PDriving_Licenses.setTransactionMode("POST",false);
			PDriving_Licenses.init(Driving_Licenses);
			Driving_Licenses.setSkin("dhx_skyblue");
			Driving_Licenses.init();
			Driving_Licenses.loadXML("employee-lists-detail-driving?ID=<?php echo $datahasil->Task_ID;?>");

			Health = new dhtmlXGridObject('Health');
			Health.setImagePath("./dhtmlx/imgs/");
			Health.setHeader("No.,Description,Disease,Year,Days");
			Health.setInitWidths("60,320,200,100,100");
			Health.setColAlign("center,left,center,center,left");
			Health.setColSorting("int,str,str,str,str");
			Health.setColTypes("ro,ro,ed,ed,ed");
			Health.enableEditEvents(true,false,true);
			PHealth = new dataProcessor("./HR/Personel/Recruitment_Health_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			PHealth.setTransactionMode("POST",false);
			PHealth.init(Health);
			Health.setEditable(true);
			Health.setSkin("dhx_skyblue");
			Health.init();
			Health.loadXML("employee-lists-detail-health?ID=<?php echo $datahasil->Task_ID;?>");
			
			Organization = new dhtmlXGridObject('Organization');
			Organization.setImagePath("./dhtmlx/imgs/");
			Organization.setHeader("No.,Organization,Position,Location,Year,Note");
			Organization.setInitWidths("60,220,200,200,100,200");
			Organization.setColAlign("center,center,center,center,center,left");
			Organization.setColSorting("int,str,str,str,str,str");
			Organization.setColTypes("ro,ed,ed,ed,ed,txt");
			Organization.enableEditEvents(true,false,true);
			POrganization = new dataProcessor("./HR/Personel/Recruitment_Organization_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			POrganization.setTransactionMode("POST",false);
			POrganization.init(Organization);
			Organization.setEditable(true);
			Organization.setSkin("dhx_skyblue");
			Organization.init();
			Organization.loadXML("employee-lists-detail-organization?ID=<?php echo $datahasil->Task_ID;?>");

			Experiences = new dhtmlXGridObject('Experiences');
			Experiences.setImagePath("./dhtmlx/imgs/");
			Experiences.setHeader("No.,Start Date,End Date,Position,Company Name,Last Salary,Reason of Resign");
			Experiences.setInitWidths("60,100,100,100,100,100,auto");
			Experiences.setColAlign("center,center,center,left,center,center,center");
			Experiences.setColSorting("int,str,str,str,str,str,str,str");
			Experiences.enableEditEvents(true,false,true);
			PExperiences = new dataProcessor("./HR/Personel/Recruitment_Experiences_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			PExperiences.setTransactionMode("POST",false);
			PExperiences.init(Experiences);
			Experiences.setDateFormat("%Y-%m-%d"); 
			Experiences.setColTypes("ro,dhxCalendar,dhxCalendar,ed,ed,ed,ed");
			Experiences.setEditable(true);
			Experiences.setSkin("dhx_skyblue");
			Experiences.init();
			Experiences.loadXML("employee-lists-detail-experience?ID=<?php echo $datahasil->Task_ID;?>");

			
			
			MyFamily = new dhtmlXGridObject('MyFamily');
			MyFamily.setImagePath("./dhtmlx/imgs/");
			MyFamily.setHeader("No.,Family Status,Name,Gender,Birth Date,Last Education,Occupation");
			MyFamily.setInitWidths("60,120,220,100,100,100,auto");
			MyFamily.setColAlign("center,center,center,left,center,center,center");
			MyFamily.setColSorting("int,str,str,str,str,str,str,str");
			MyFamily.setColTypes("dyn,coro,txt,coro,dhxCalendar,coro,ed");
			MyFamily.setDateFormat("%Y-%m-%d"); 
			PMyFamily = new dataProcessor("./HR/Personel/Recruitment_Family_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			PMyFamily.setTransactionMode("POST",false);
			PMyFamily.init(MyFamily);
			MyFamily.setEditable(true);
			MyFamily.setSkin("dhx_skyblue");
			MyFamily.enableEditEvents(true,false,true);
			MyFamily.init();
			MyFamily.loadXML("employee-lists-detail-family?ID=<?php echo $datahasil->Task_ID;?>");
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
			MyFamily.getCombo(5).put('S1','S2');
			MyFamily.getCombo(5).put('S2','S2');
		

			OtherSkill = new dhtmlXGridObject('OtherSkill');
			OtherSkill.setImagePath("./dhtmlx/imgs/");
			OtherSkill.setHeader("No.,Category,Subject/Area,Year,Competences");
			OtherSkill.setInitWidths("60,200,220,100,auto");
			OtherSkill.setColAlign("center,center,center,center,left");
			OtherSkill.setColSorting("int,str,str,str,str");
			OtherSkill.setColTypes("ro,ed,ed,ed,txt");
			OtherSkill.enableEditEvents(true,false,true);
			POtherSkill = new dataProcessor("./HR/Personel/Recruitment_Other_Skill_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			POtherSkill.setTransactionMode("POST",false);
			POtherSkill.init(OtherSkill);
			OtherSkill.setEditable(true);
			OtherSkill.setSkin("dhx_skyblue");
			OtherSkill.init();
			OtherSkill.loadXML("employee-lists-detail-other-skill?ID=<?php echo $datahasil->Task_ID;?>");

			Awards = new dhtmlXGridObject('Awards');
			Awards.setImagePath("./dhtmlx/imgs/");
			Awards.setHeader("No.,Month and Year of Winning,Place,Detail,Title");
			Awards.setInitWidths("60,200,220,200,auto");
			Awards.setColAlign("center,center,center,center,left");
			Awards.setColSorting("int,str,str,str,str");
			Awards.setColTypes("ro,ed,ed,ed,txt");
			Awards.enableEditEvents(true,false,true);

			PAwards = new dataProcessor("./HR/Personel/Recruitment_Awards_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			PAwards.setTransactionMode("POST",false);
			PAwards.init(Awards);
			Awards.setEditable(true);
			Awards.setSkin("dhx_skyblue");
			Awards.init();
			Awards.loadXML("employee-lists-detail-award?ID=<?php echo $datahasil->Task_ID;?>");


			Informal = new dhtmlXGridObject('Informal');
			Informal.setImagePath("./dhtmlx/imgs/");
			Informal.setHeader("No.,Training Company,Location,Year,Note");
			Informal.setInitWidths("60,120,220,100,auto");
			Informal.setColAlign("center,center,center,center,left");
			Informal.setColSorting("int,str,str,str,str");
			Informal.setColTypes("ro,ed,ed,ed,txt");
			Informal.enableEditEvents(true,false,true);

			PInformal = new dataProcessor("./HR/Personel/Recruitment_Informal_Submit.php?Task_ID=<?php echo $datahasil->Task_ID;?>");
			PInformal.setTransactionMode("POST",false);
			PInformal.init(Informal);
			Informal.setEditable(true);
			Informal.setSkin("dhx_skyblue");
			Informal.init();
			Informal.loadXML("employee-lists-detail-informal?ID=<?php echo $datahasil->Task_ID;?>");

var winUploadDetail;
var WUploadDetail;
function UploadRecruit(Namadoc,iddOC) {
	IDTask = "<?php echo $_GET['ID'] ?>";
    winUploadDetail = new dhtmlXWindows();
    winUploadDetail.attachViewportTo("divatas");
    winUploadDetail.enableAutoViewport(true);
    winUploadDetail.setImagePath("./dhtmlx/imgs/");
    WUploadDetail = winUploadDetail.createWindow("WUploadDetail", 0, 0, 450, 360);
    WUploadDetail.setText("Upload " + Namadoc);

    WUploadDetail.attachURL('./HR/Personel/Recruitment_Upload.php?namadok=' + iddOC, true);
	WUploadDetail.button("minmax1").hide();
    WUploadDetail.button("park").hide();
	WUploadDetail.centerOnScreen();
	WUploadDetail.allowMove();	
	WUploadDetail.progressOn();

}
function cekFoto(pathf){
	if (pathf!='') {
		window.open ("./Personel/Documents/"+pathf,"mywindow");
	}
}
function UploadStart(varUpload){
	nomorDoc = $("#" + varUpload + "_Desc").val();
	if (nomorDoc == ''){
		jAlert('Please fill Reference First!','Upload ' + varUpload);
		return false;
	}else{
		UploadRecruit(nomorDoc,varUpload);
	}
	
}

var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Worked_Pandu_Start","Worked_Pandu_End"]);
myCalendar.setDateFormat("%Y-%m-%d");
</script>
<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="CTPoints" style="width:auto;height:130px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
   	    <tr height="30">
        <td class="badan" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Emergency Contact (Family Relation)</td>
	</tr>
    <tr height="30">
        <td class="badan" width="150">Emergency Contact No</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id = "Emergency_Contact_No"  size="30" /></td>
        <td class="badan" width="100">Emergency Contact Name</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id="Emergency_Contact_Name" size="30" tabindex='6'/>
        </td>
    </tr>
    <tr height="30">
        <td class="badan" width="150">Relationship</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id = "Emergency_Relation"  size="30" /></td>
        <td class="badan" width="100">Address</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id="Emergency_Address" size="30" tabindex='6'/>
        </td>
    </tr>
 </table>

