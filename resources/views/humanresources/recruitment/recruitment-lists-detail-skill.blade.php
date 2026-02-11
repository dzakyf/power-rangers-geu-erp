 @include('layout.plugins')


<div id="CPExperiences">
<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
<tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Work Experiences</td>
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
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Winning Awards / Grants / Prizes / Scholarships</td>
	</tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Awards" style="width:auto;height:100px; background-color:white;overflow:hidden"></div>
			 
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
</table>
</div>
<script>
			
			Experiences = new dhtmlXGridObject('Experiences');
			Experiences.setImagePath("./dhtmlx/imgs/");
			Experiences.setHeader("No.,Start Date,End Date,Position,Company Name,Last Salary,Reason of Resign");
			Experiences.setInitWidths("60,100,100,100,100,100,auto");
			Experiences.setColAlign("center,center,center,left,center,center,center");
			Experiences.setColSorting("int,str,str,str,str,str,str,str");
			Experiences.enableEditEvents(true,false,true);
			PExperiences = new dataProcessor("recruitment-lists-detail-experience-update");
			PExperiences.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
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
			OtherSkill.setInitWidths("60,200,220,100,auto");
			OtherSkill.setColAlign("center,center,center,center,left");
			OtherSkill.setColSorting("int,str,str,str,str");
			OtherSkill.setColTypes("ro,ed,ed,ed,txt");
			OtherSkill.enableEditEvents(true,false,true);
			POtherSkill = new dataProcessor("recruitment-lists-detail-other-skill-update");
			POtherSkill.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
			POtherSkill.setTransactionMode("POST",false);
			POtherSkill.init(OtherSkill);
			OtherSkill.setEditable(true);
			OtherSkill.setSkin("dhx_skyblue");
			OtherSkill.init();
			OtherSkill.loadXML("xml-recruitment-lists-detail-other-skill?ID=<?php echo $_GET['ID'];?>");
		
			Awards = new dhtmlXGridObject('Awards');
			Awards.setImagePath("./dhtmlx/imgs/");
			Awards.setHeader("No.,Month and Year of Winning,Place,Detail,Title");
			Awards.setInitWidths("60,200,220,200,auto");
			Awards.setColAlign("center,center,center,center,left");
			Awards.setColSorting("int,str,str,str,str");
			Awards.setColTypes("ro,ed,ed,ed,txt");
			Awards.enableEditEvents(true,false,true);

			PAwards = new dataProcessor("recruitment-lists-detail-award-update");
			PAwards.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
			PAwards.setTransactionMode("POST",false);
			PAwards.init(Awards);
			Awards.setEditable(true);
			Awards.setSkin("dhx_skyblue");
			Awards.init();
			Awards.loadXML("xml-recruitment-lists-detail-award?ID=<?php echo $_GET['ID'];?>");

		Language = new dhtmlXGridObject('Language');
			Language.setImagePath("./dhtmlx/imgs/");
			Language.setHeader("No.,Language,Oral,Writing,Additional Information");
			Language.setInitWidths("60,220,100,100,auto");
			Language.setColAlign("center,center,center,center,left");
			Language.setColSorting("int,str,str,str,str");
			Language.setColTypes("ro,ed,coro,coro,txt");
			Language.enableEditEvents(true,false,true);
			PLanguage = new dataProcessor("recruitment-lists-detail-language-update");
			PLanguage.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
			
			
			PLanguage.init(Language);
			Language.setEditable(true);
			Language.setSkin("dhx_skyblue");
			Language.init();
			Language.loadXML("xml-recruitment-lists-detail-language?Task_ID=<?php echo $_GET['ID'];?>");
			Language.getCombo(2).put ('GOOD','GOOD');
			Language.getCombo(2).put ('ENOUGH','GOOD');
			Language.getCombo(2).put ('LESS','LESS');
			Language.getCombo(3).put ('GOOD','GOOD');
			Language.getCombo(3).put ('ENOUGH','ENOUGH');
			Language.getCombo(3).put ('LESS','LESS');
</script>