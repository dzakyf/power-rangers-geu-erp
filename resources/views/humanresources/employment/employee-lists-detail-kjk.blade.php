 @include('layout.plugins')

<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$datahasil = DB::table("employment")->where("NIK",$_GET['ID'])->first();
	
$nik = $datahasil->NIK;
?>

<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    
    <tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Overtime</td>
	</tr>
    
    <tr height="30">
        <td class="badan" colspan="6">
			<div id="Overtime" style="width:auto;height:330px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
	<tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">KJK</td>
	</tr>
    <tr>
        <td class="badan" colspan="6">
			<div id="KJKAll" style="width:auto;height:330px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>
   	<tr height="30">
        <td class="badan" colspan="6" align="center" style="color:#F90;background-image:url(hdr_back.gif);" colspan="6">Leave and Permission</td>
	</tr>
    <tr>
        <td class="badan" colspan="6">
			<div id="PermissionList" style="width:auto;height:330px; background-color:white;overflow:hidden"></div>
        </td>
	</tr>  
 
</table>

<script>
			
			
Overtime = new dhtmlXGridObject('Overtime');
Overtime.setImagePath("./dhtmlx/imgs/");
Overtime.setHeader("No,Task ID,NIK,Name,Start Date,End Date,Early IN,Early OUT,After IN,After OUT,Holiday,Total OT,Calculated OT,Rate,Salary Period,Amount Received,Reference,Note,Created Date,Created By");
Overtime.attachHeader("#rspan,#text_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter,#rspan,#rspan,#rspan");
Overtime.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,,,#stat_total,,,,");

Overtime.setInitWidths("80,100,120,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,150,300,120,100");
Overtime.setColAlign("center,center,center,left,left,center,left,center,center,center,center,center,center,center,center,center,center");
Overtime.setColTypes("ro,ro,ro,ro,dhxCalendar,dhxCalendar,time,time,time,time,ch,ro,ro,edn,ro,ron,ro,ro,ed,ed,ro,ro");
Overtime.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
Overtime.setEditable(false);
Overtime.setDateFormat("%Y-%m-%d"); 
Overtime.setNumberFormat("0,000",13,".",",");
Overtime.setNumberFormat("0,000",15,".",",");
Overtime.setSkin("dhx_skyblue");
Overtime.init();
Reload_Overtime();

function Reload_Overtime(){
	Overtime.clearAndLoad("xml-employee-lists-detail-overtime?NIK=<?php echo $nik?>");
}
		


KJK = new dhtmlXGridObject('KJKAll');
KJK.setImagePath("./dhtmlx/imgs/");
KJK.setHeader("No,Task ID,NIK,Name,Allowance,Rate,KJK Value,Total,KJK Period,Note,Salary Period,Created Date,Created By");
KJK.attachHeader("#rspan,#text_filter,#numeric_filter,#combo_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter,#text_filter");
KJK.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,,,,,");

KJK.setInitWidths("80,100,120,250,100,100,100,100,100,300,100,120,100");
KJK.setColAlign("center,center,center,left,left,center,left,center,center,center,center");
KJK.setColTypes("ro,ro,ro,ro,ron,ron,edn,ron,ro,ed,ro,ro,ro");
KJK.setColSorting("int,str,str,str,str,str,str,str,str,str");
KJK.setEditable(false);
KJK.setDateFormat("%Y-%m"); 
KJK.setNumberFormat("0,000",4,".",",");
KJK.setNumberFormat("0,000.00",5,".",",");
KJK.setNumberFormat("0,000.00",6,".",",");
KJK.setNumberFormat("0,000.00",7,".",",");
KJK.setSkin("dhx_skyblue");
KJK.init();
Reload_KJKAll();


function Reload_KJKAll(){
	KJK.clearAndLoad("xml-employee-lists-detail-kjk?NIK=<?php echo $nik?>");
}
	
PermissionList = new dhtmlXGridObject('PermissionList');
PermissionList.setImagePath("./dhtmlx/imgs/");
PermissionList.setHeader("Approve,Permission ID,NIK,Name,Employee Category,Permission Category,Sub Category,Location,Note,Date");
PermissionList.attachHeader("#master_checkbox,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter");
PermissionList.setInitWidths("70,220,100,200,100,100,200,150,220,100,100,100,100,100,100");
PermissionList.setColAlign("center,left,center,center,center,center,center,center,center,center");
PermissionList.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro,ro");
PermissionList.setColSorting("int,str,str,str,str,str,str,str");
PermissionList.setEditable(false);
PermissionList.enableMultiline(true);
PermissionList.init();
refreshdata();

function refreshdata(){
	PermissionList.clearAndLoad('xml-permission-approval?NIK=<?php echo $nik?>');

}
</script>