 @include('layout.plugins')



<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    
    <tr height="30">
        <td class="badan"  colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Formal Education</td>
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
        <td class="badan"  colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Organization</td>
	</tr>

    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Organization" style="width:auto;height:130px; background-color:white;overflow:hidden"></div>
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
			Informal.setInitWidths("60,120,220,100,auto");
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