 @include('layout.plugins')


<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
      <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Health History</td>
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
        <td class="badan" width="150">Have Congenital Diseases for longtime Care?</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
              <select id = "Longtime"><option>No</option><option>Yes</option></select>
		</td>
        <td class="badan" width="100">if Yes,Disease:</td>
        <td class="badan" width="30">:</td>
        <td class="badan_kiri" width='250'>
         <input type="text" id="Longtime_Desc" size="20"/>    
         </td>
    </tr>
 </table>
 
 <script>
			
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
			PDriving_Licenses = new dataProcessor("recruitment-lists-detail-driving-update");
			PDriving_Licenses.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
			
			PDriving_Licenses.init(Driving_Licenses);
			Driving_Licenses.setSkin("dhx_skyblue");
			Driving_Licenses.init();
			Driving_Licenses.loadXML("xml-recruitment-lists-detail-driving?ID=<?php echo $_GET['ID'];?>");

			Health = new dhtmlXGridObject('Health');
			Health.setImagePath("./dhtmlx/imgs/");
			Health.setHeader("No.,Description,Disease,Year,Days");
			Health.setInitWidths("60,320,200,100,100");
			Health.setColAlign("center,left,center,center,left");
			Health.setColSorting("int,str,str,str,str");
			Health.setColTypes("ro,ro,ed,ed,ed");
			Health.enableEditEvents(true,false,true);
			PHealth = new dataProcessor("recruitment-lists-detail-health-update");
			PHealth.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
			PHealth.setTransactionMode("POST",false);
			PHealth.init(Health);
			Health.setEditable(true);
			Health.setSkin("dhx_skyblue");
			Health.init();
			Health.loadXML("xml-recruitment-lists-detail-health?ID=<?php echo $_GET['ID'];?>");
			
			

</script>

