 @include('layout.plugins')



<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    
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
			<div id="MyFamily" style="width:auto;height:330px; background-color:white;overflow:hidden"></div>
			 <input type="button" id="MyFamily_Add" value="Add" onclick="MyFamily.addRow(MyFamily.uid()[MyFamily.getRowsNum()+1],0)" size="20"/>    
					 <input type="button" id="MyFamily_Delete" value="Delete" onclick="MyFamily.deleteSelectedRows()" size="20"/> 
        </td>
	</tr>
	<tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Emergency Contact</td>
	</tr>
    <tr>
        <td class="badan" colspan="6">
			<div id="CTPoints" style="width:auto;height:230px; background-color:white;overflow:hidden"></div>
			 <input type="button" id="CTPoints_Add" value="Add" onclick="CTPoints.addRow(CTPoints.uid()[CTPoints.getRowsNum()+1],0)" size="20"/>    
					 <input type="button" id="CTPoints_Delete" value="Delete" onclick="CTPoints.deleteSelectedRows()" size="20"/> 
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
<div style="padding:10px">
    <input type="button" class="btn btn-primary" value="Save" onclick="saveFamilyData()">
</div>

<script>
function saveFamilyData() {
    PMyFamily.sendData();
    PCTPoints.sendData();
    alert('Data saved.');
}

MyFamily = new dhtmlXGridObject('MyFamily');
			MyFamily.setImagePath("./dhtmlx/imgs/");
			MyFamily.setHeader("No.,Family Status,Name,Gender,Birth Date,Last Education,Occupation");
			MyFamily.setInitWidths("60,120,220,100,100,100,auto");
			MyFamily.setColAlign("center,center,center,left,center,center,center");
			MyFamily.setColSorting("int,str,str,str,str,str,str,str");
			MyFamily.setColTypes("dyn,coro,txt,coro,dhxCalendar,coro,ed");
			MyFamily.setDateFormat("%Y-%m-%d"); 
			PMyFamily = new dataProcessor("recruitment-lists-detail-family-update");
			PMyFamily.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
			PMyFamily.init(MyFamily);
			MyFamily.setEditable(true);
			MyFamily.setSkin("dhx_skyblue");
			MyFamily.enableEditEvents(true,false,true);
			MyFamily.init();
			MyFamily.loadXML("xml-recruitment-lists-detail-family?ID=<?php echo $_GET['ID'];?>");
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
		
			CTPoints = new dhtmlXGridObject('CTPoints');
			CTPoints.setImagePath("./dhtmlx/imgs/");
			CTPoints.setHeader("No.,Name,Description,Location,Contact No,Email,Phone Number");
			CTPoints.setInitWidths("60,120,220,100,100,100,100,100");
			CTPoints.setColAlign("center,center,center,left,center,center,center,center");
			CTPoints.setColSorting("int,str,str,str,str,str,str,str");
			CTPoints.setColTypes("ro,ed,ed,ed,ed,ed,ed,ed");
			CTPoints.setEditable(true);
			PCTPoints = new dataProcessor("recruitment-lists-detail-contact-points-update");
			PCTPoints.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
			PCTPoints.setTransactionMode("POST",false);
			PCTPoints.init(CTPoints);
			CTPoints.setSkin("dhx_skyblue");
			CTPoints.init();
			CTPoints.loadXML("xml-recruitment-lists-detail-contact-points?ID=<?php echo $_GET['ID'];?>");

</script>