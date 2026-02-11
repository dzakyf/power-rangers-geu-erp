			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ADD LIST OF BENEFIT AND COSTS FOR GROUP <?php echo $_GET['ID']?></div>
			<div id="ListMasterPayroll" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
			<input type="button" id="AddDataPayrollListsGroup" NAME="AddDataPayrollListsGroup" value="Add to Group <?php echo $_GET['ID']?>" class="btn btn-info mt-2 mr-2"/>
		</div>



	
	</div>
<script>

ListMasterPayroll = new dhtmlXGridObject('ListMasterPayroll');
ListMasterPayroll.setImagePath("./dhtmlx/imgs/");
ListMasterPayroll.setHeader("Select,ID,Name,Fix Allowance,PPH,Category,Created By,Created Date");
ListMasterPayroll.attachHeader("#master_checkbox,#text_filter,#text_filter,#combo_filter,#combo_filter,#combo_filter,#rspan,#rspan");
ListMasterPayroll.setInitWidths("80,100,250,100,100,100,100,100,100");
ListMasterPayroll.setColAlign("center,left,left,center,left,center,left,center,center,center");
ListMasterPayroll.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro");
ListMasterPayroll.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
ListMasterPayroll.setEditable(true);
ListMasterPayroll.setSkin("dhx_skyblue");
PListMasterPayroll = new dataProcessor("payroll-template-master-lists-add-update");
	PListMasterPayroll.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	 	    payload: {"ID":"<?php echo $_GET['ID'] ?>"}
 
    })
PListMasterPayroll.setUpdateMode("Off");
PListMasterPayroll.init(ListMasterPayroll);
ListMasterPayroll.init();
Reload_ListMasterPayroll();

function Reload_ListMasterPayroll(){
	ListMasterPayroll.clearAndLoad("xml-payroll-template-master-lists-add?ID=<?php echo $_GET['ID']?>");	

}

$("#AddDataPayrollListsGroup").click(function(){
		PListMasterPayroll.sendData();
		alert("Data was updated!");
		setTimeout(function(){
			$("#Payroll_ListsDiv").html('');
				Reload_GroupPayroll_Lists();
		},2000);
});




</script>
