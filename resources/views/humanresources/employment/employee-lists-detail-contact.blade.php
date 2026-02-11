 @include('layout.plugins')


<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    <tr>
        <td class="badan" colspan="6">
			<div id="CTPoints" style="width:auto;height:230px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
   	    <tr height="30">
        <td class="badan" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Relation Contact with KSPS</td>
	</tr>
    <tr height="30">
        <td class="badan" width="150">Contact No</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
            <input type="text" id = "Emergency_Contact_No"  size="30" /></td>
        <td class="badan" width="100">Contact Name</td>
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
 
 <script>
			
			CTPoints = new dhtmlXGridObject('CTPoints');
			CTPoints.setImagePath("./dhtmlx/imgs/");
			CTPoints.setHeader("No.,Name,Description,Location,Contact No,Email,Phone Number");
			CTPoints.setInitWidths("60,120,220,100,100,100,100,100");
			CTPoints.setColAlign("center,center,center,left,center,center,center,center");
			CTPoints.setColSorting("int,str,str,str,str,str,str,str");
			CTPoints.setColTypes("ro,ed,ed,ed,ed,ed,ed,ed");
			CTPoints.setSkin("dhx_skyblue");
			CTPoints.init();
			//CTPoints.loadXML("employee-lists-detail-contact-points?ID=<?php echo $_GET['ID'];?>");

			

</script>

