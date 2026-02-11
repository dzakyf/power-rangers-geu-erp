<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$cek=DB::table("payrol_main_group_lists")->where("Main_ID",$_GET['ID'])->orderBy("Urutan","ASC")->get();
	$dx = "";
	$align = "";
	$ro = "";
	$width = "";
	$sort = "";
	$nox = 10;
	$dx2 ="";
	$numbring= "";
	foreach($cek as $data){
		
		$dx .=",$data->Name";
		$dx2 .=",#numeric_filter";
		$ro .=",edn";
		$sort .=",int";
		$width .=",100";
		$align .=",right";
		$numbring .="Employee_Lists.setNumberFormat(\"0,000\",$nox,\".\",\",\");\n";
		$nox++;
	}
	
	
	?>
			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LIST OF EMPLOYEES IN GROUP <?php echo $_GET['ID']?></div>

		<div id="Employee_Lists" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselEmployee_ListsDet" name="ekselEmployee_ListsDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="Employee_Lists.toExcel('./export/generate.php');"  />
			<input type="button" id="delets" name="delets" value="Remove Employee" onclick="DelGroupPayroll_Lists()"  class="btn btn-danger mt-2 mr-2" />
			<input type="button" id="deletsaa" name="deletsaaa" value="Add New" onclick="TambahGroupPayroll_Lists()" class="btn btn-success mt-2 mr-2"  />
			<input type="button" id="idgrupss" name="idgrupss" value="Update Data" onclick="UpdateGroupPayroll_Lists()" class="btn btn-success mt-2 mr-2"  />

		</div>
		<div id="Div_Payroll_Group"></div>
<script>
function TambahGroupPayroll_Lists() {

LoadM('Div_Payroll_Group','payroll-group-detail-add?ID=<?php echo $_GET['ID']?>');
}
function UpdateGroupPayroll_Lists() {

	PEmployee_Lists.sendData();
		alert('Data Successfully Updated!');
		setTimeout(function(){
			refreshdatagroupdetail();
		},1000);
}

Employee_Lists = new dhtmlXGridObject('Employee_Lists');
Employee_Lists.setImagePath("./dhtmlx/imgs/");
Employee_Lists.setHeader("No,NIK,Name,Gender,Position,Work Area,Payment Method,Payment Reference,Payment Reference2,Gross Up<?php echo $dx?>");
Employee_Lists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter<?php echo $dx2?>");

Employee_Lists.setInitWidths("60,150,100,100,200,120,120,100,100,100<?php echo $width?>");
Employee_Lists.setColAlign("center,left,left,left,left,left,left,center,center,center<?php echo $align?>");
Employee_Lists.setColSorting("int,str,str,str,str,str,str,str,str,str<?php echo $sort?>");
Employee_Lists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,coro<?php echo $ro?>");
Employee_Lists.setEditable(true);
Employee_Lists.enableEditEvents(true,false,true);

PEmployee_Lists = new dataProcessor("payroll-group-detail-update");
PEmployee_Lists.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	    payload: {"ID":"<?php echo $_GET['ID'] ?>"}

    })
<?php echo $numbring?>	
PEmployee_Lists.setTransactionMode("POST", true);
PEmployee_Lists.setUpdateMode("Off");
PEmployee_Lists.init(Employee_Lists);
Employee_Lists.setSkin("dhx_skyblue");
Employee_Lists.init();
Employee_Lists.getCombo(9).put("1","YES");
Employee_Lists.getCombo(9).put("0","NO");
refreshdatagroupdetail();

function refreshdatagroupdetail(){
	$.blockUI();
	Employee_Lists.clearAndLoad('xml-payroll-group-detail?ID=<?php echo $_GET['ID']?>',bukagelap);

}
function bukagelap(){
	$.unblockUI();
}
function DelGroupPayroll_Lists(){
	dhtmlx.confirm('Remove this Employee?', 'Employee Salary Group', function(r) {
			
			if (r==true){
				Employee_Lists.deleteSelectedRows();
				PEmployee_Lists.sendData();
					alert('Data Successfully Deleted!');
					setTimeout(function(){
						refreshdatagroupdetail();
					},1000);
			}
	})
}

</script>
