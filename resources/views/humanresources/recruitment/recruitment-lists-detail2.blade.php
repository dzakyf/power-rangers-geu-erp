<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$priv =  Auth::user()->Priv_ID;
	$datahasil = DB::table("recruitment")->where("ID",$_GET['ID'])->first();
	$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'recruitment'");
	$jumlah = count($result);
	if ($jumlah > 0) {
		echo "<script>";
		
		foreach ($result as $row) {
			
			$kolom = $row->column_name;
			
				
				if ($datahasil->$kolom == '0000-00-00')
				{
					echo "$('#$kolom').val('');\n";
				
				}else{
					if($datahasil->$kolom != 'ID'){
					$dataForm = $datahasil->$kolom;
					
					echo "$('#$kolom').val('$dataForm');\n";
					}
				}
			

		}
	   echo "</script>";

	}
	
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL RECRUITMENT</div>

<div id="BankTransferDiv">
    <div class="FormInput_Form_Content">

    		
<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    <tr><td align="center">
		<table cellpadding="0" cellspacing="0" width="100%" border="0" style="border-left: outset #cdcdcd;border-top: outset #cdcdcd; border-bottom: inset #cdcdcd; border-right: inset #cdcdcd;">
    <tr height="30">
        <td class="badan" width="150">Task ID</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
            <?php echo $datahasil->Task_ID;?>
        </td>
        <td class="badan" width="100">Name</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width="250">
            <?php echo $datahasil->Name;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Gender</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Gender;?>
        </td>
        <td class="badan">Birth Date &amp; Place </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Birth_Date." / ".$datahasil->Birth_Place;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Citizen ID Card </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->KTP;?>
        </td>
        <td class="badan">Citizenship</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->kewarganegaraan;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Marital Status </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Marital_Status;?>
        </td>
        <td class="badan">Last Education </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Last_Education;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Religion </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Religion;?>
        </td>
        <td class="badan">Date Created </td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Created_Date;?>
        </td>
    </tr>
    <tr height="30">
        <td class="badan">Address</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Address.", ".$datahasil->City;?>
        </td>
         <td class="badan">Phone Contact</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Phone ." / ".$datahasil->Handphone;?>
        </td>
	</tr>
    <tr height="30">
        <td class="badan">Education From</td>
        <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Last_Education_From;?>
        </td>
         <td class="badan">Position Fulfillment</td>
         <td class="badan">:</td>
        <td class="badan_kiri">
            <?php echo $datahasil->Position;?>
        </td>
	</tr>
</table>
</td>
</tr>
</table>
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
			Akademic.loadXML("xml-recruitment-lists-detail-academic?Task_ID=<?php echo $datahasil->Task_ID;?>");

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
			Language.loadXML("xml-recruitment-lists-detail-language?Task_ID=<?php echo $datahasil->Task_ID;?>");
			Language.getCombo(2).put ('GOOD','GOOD');
			Language.getCombo(2).put ('ENOUGH','GOOD');
			Language.getCombo(2).put ('LESS','LESS');
			Language.getCombo(3).put ('GOOD','GOOD');
			Language.getCombo(3).put ('ENOUGH','ENOUGH');
			Language.getCombo(3).put ('LESS','LESS');

			CTPoints = new dhtmlXGridObject('CTPoints');
			CTPoints.setImagePath("./dhtmlx/imgs/");
			CTPoints.setHeader("No.,Name,Employee Department,Position,Contact No,Email,Phone Number");
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
			CTPoints.loadXML("xml-recruitment-lists-detail-contact-points?ID=<?php echo $datahasil->Task_ID;?>");

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
			Driving_Licenses.loadXML("xml-recruitment-lists-detail-driving?ID=<?php echo $datahasil->Task_ID;?>");

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
			Health.loadXML("xml-recruitment-lists-detail-health?ID=<?php echo $datahasil->Task_ID;?>");
			
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
			Organization.loadXML("xml-recruitment-lists-detail-academic?ID=<?php echo $datahasil->Task_ID;?>");

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
			Experiences.loadXML("./lib/xml/HR/Personel/Experiences.php?ID=<?php echo $datahasil->Task_ID;?>");

			
			
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
			MyFamily.loadXML("./lib/xml/HR/Personel/Family.php?ID=<?php echo $datahasil->Task_ID;?>");
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
			OtherSkill.loadXML("./lib/xml/HR/Personel/Other_Skill.php?ID=<?php echo $datahasil->Task_ID;?>");

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
			Awards.loadXML("./lib/xml/HR/Personel/Awards.php?ID=<?php echo $datahasil->Task_ID;?>");


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
			Informal.loadXML("./lib/xml/HR/Personel/Informal.php?ID=<?php echo $datahasil->Task_ID;?>");

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
		window.open ("./HR/Personel/Documents/"+pathf,"mywindow");
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
function tutupRec(){
		RecruitmentMGT.clearAndLoad("./lib/xml/HR/Personel/Recruitment.php");
			setTimeout(function(){
				
				$('#DivPersonelDetail').html('');

			},2000);
	
}
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Worked_Pandu_Start","Worked_Pandu_End"]);
myCalendar.setDateFormat("%Y-%m-%d");
</script>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DETAIL DATA AND RELATIONS</div>

<div id="ContentPages">
<div>
<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    <tr><td align="center">
		<table cellpadding="0" cellspacing="0" width="100%" border="0" style="border-left: outset #cdcdcd;border-top: outset #cdcdcd; border-bottom: inset #cdcdcd; border-right: inset #cdcdcd;">
    <tr height="30">
        <td class="badan" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Contact Points for Possible Requests of
Letters of Recommendation</td>
	</tr>

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
    
    <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Family</td>
	</tr>
    <tr height="30">
    <td class="badan" width="100">Family Card No</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250' colspan="4">
            <input type="text" id="Family_Card_No" size="40" tabindex='6'/>
        </td>
    </tr>
    <tr height="30">
        <td class="badan" colspan="6">
			<div id="MyFamily" style="width:auto;height:230px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
    <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Formal Educational Level</td>
	</tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Akademic" style="width:auto;height:130px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
    <tr height="30">
        <td class="badan"  colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Informal Education</td>
	</tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Informal" style="width:auto;height:130px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
    <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Languages</td>
	</tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Language" style="width:auto;height:130px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
    <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">WORK EXPERIENCES</td>
	</tr>
     <tr height="30">
        <td class="badan" width="150">Work Experience Year</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id = "Location"  size="30" /></td>
        <td class="badan" width="100">Last Experience Knowledge</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id="Last_Experience" size="30" tabindex='6'/>
        </td>
    </tr>
     <tr height="30">
        <td class="badan" width="150">Have you ever worked at KSPS?</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <select id = "Worked_Pandu"><option>No</option><option>Yes</option></select></td>
        <td class="badan" width="100">If Yes, Please describe</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
           Date <input type="text" id="Worked_Pandu_Start" size="6"/> to <input type="text" id="Worked_Pandu_End" size="6"/>
            Position <input type="text" id="Worked_Pandu_Position" size="10"/>
        </td>
    </tr>
    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Experiences" style="width:auto;height:150px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
    <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Research and Development Skills</td>
	</tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="OtherSkill" style="width:auto;height:130px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>

     <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Organization Experiences</td>
	</tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Organization" style="width:auto;height:130px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>

     <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Medical History</td>
	</tr>
     <tr height="30">
        <td class="badan" width="150">Height</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id = "Height"  size="5" /> cm</td>
        <td class="badan" width="100">Weight</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id="Weight" size="5"/> kg
        </td>
    </tr>
     <tr height="30">
        <td class="badan" width="150">Using Glasses/Contact Lens</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <select id = "Glasses"><option>No</option><option>Yes</option></select></td>
        <td class="badan" width="100">Smoker</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <select id = "Smoker"><option>No</option><option>Yes</option></select></td>
    </tr>
     <tr height="30">
        <td class="badan" width="150">Blood Type</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <select id = "Blood">
            <option>A</option>
            <option>AB</option>
            <option>B</option>
            <option>O</option>
            </select> Rhesus <select id = "Blood_Rhesus">
            <option>Positive (+)</option>
            <option>Negative (-)</option>
            </select> </td>
        <td class="badan" width="100">Have Diseases for longtime Care?</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <select id = "Longtime"><option>No</option><option>Yes</option></select>
         if Yes,Disease:<input type="text" id="Longtime_Desc" size="20"/>    
         </td>
    </tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Health" style="width:auto;height:130px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
 
     <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Driving Licenses</td>
	</tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Driving_Licenses" style="width:auto;height:150px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>

     <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Winning Awards / Grants / Prizes / Scholarships</td>
	</tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Awards" style="width:auto;height:100px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
 
</table>
</td>
</tr>

</table>
</div>

<div class="JudulContent"  style="color:#699;background-image:url(hdr_back.gif);">ADVANCED INFORMATION</div>
    <div class="FormInput_Form_Content" >
   		<div class="FormInput_left" style="height:80px;">
        		
                Describe Yourself
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Self"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                Why you left last job?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Left_Job"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                What do you think of your previous boss?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Previous_Boss"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                Describe what you know about this company
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Know"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                What you did to improve your knowledge last?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Improve"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                What are you looking for in a job?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Looking"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                Why you interest to join us?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Interest"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                How long you will work to us?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Long"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                Describe your target for long time
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Target"></textarea>        	

         
         </div>
   		<div class="FormInput_left">
        		
                Salary Expectation
        
        </div>
		<div class="FormInput_right">
        
				Rp. <input type="text" size="30" id="Salary_Expectation"/>        	

         
         </div>
   		<div class="FormInput_left">
        		
                Facility Requirement Expectation
        
        </div>
		<div class="FormInput_right">
        
				<input type="text" size="50" id="Facility_Expectation"/>        	

         
         </div>
    		<div class="FormInput_left">
        		
                Environment of Working Area
        
        </div>
		<div class="FormInput_right">
        
				<select id="Environment"><option>OFFICE</option><option>FIELD</option><option>FACTORY</option><option>WORKSHOP</option></select>
                       	

         
         </div>
  		<div class="FormInput_left" style="height:80px;">
        		
                What you will do at free time?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Free_Time"></textarea>        	

         
         </div>
  		<div class="FormInput_left" style="height:80px;">
        		
                What strong point of you?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Strong"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                Please Describe your weakness
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Describe_Weak"></textarea>        	

         
         </div>
   		<div class="FormInput_left">
        		
                Do You Agree to work overtime when we need?
        
        </div>
		<div class="FormInput_right">
        
				<select id="Overtime"><option>YES</option><option>NO</option> </select>     	

         
         </div>
   		<div class="FormInput_left">
        		
                Do You Agree to work at holiday?
        
        </div>
		<div class="FormInput_right">
        
				<select id="Holiday"><option>YES</option><option>NO</option>  </select>    	

         
         </div>
   		<div class="FormInput_left">
        		
                Do You Agree to work shifted as we need?
        
        </div>
		<div class="FormInput_right">
        
				<select id="Shift_Work"><option>YES</option><option>NO</option> </select>     	

         
         </div>
   		<div class="FormInput_left">
        		
                Do You Agree to work Out of Town?
        
        </div>
		<div class="FormInput_right">
        
				<select id="Out_Town"><option>YES</option><option>NO</option> </select>     	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                Can you operate a computer? can explain what you can?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Computer"></textarea>        	

         
         </div>
   		<div class="FormInput_left" style="height:80px;">
        		
                Any Questions to Us?
        
        </div>
		<div class="FormInput_right" style="height:80px;">
        
				<textarea cols="100" rows="4" id="Questions_Us"></textarea>        	

         
         </div>

</div>
</div>
    <div id="divtom" align="center">
        <input type="button" id="SubmitCustDetail" NAME="SubmitCustDetail" value="Update" />                
    </div>
<script>
$("#SubmitCustDetail").click(function(){

	isian = "";
	
	$("#ContentPages input,textarea").each(function() {
		var id = $(this).attr("id");
		var val = $(this).val();
		
			if (isian == ''){
				isian = id + "=" + val;
			}else{
				isian = isian + "&" + id + "=" + val;
			}
	});
	
	$("#ContentPages select").each(function() {
		var id = $(this).attr("id");
		var val = $(this).val();
		
			if (isian == ''){
				isian = id + "=" + val;
			}else{
				isian = isian + "&" + id + "=" + val;
			}
	});


})
var myCalendar;
    myCalendar = new dhtmlXCalendarObject(["PO_Date"]);
	myCalendar.setDateFormat("%Y-%m-%d");


</script>
<div id="DivProcess"></div>

