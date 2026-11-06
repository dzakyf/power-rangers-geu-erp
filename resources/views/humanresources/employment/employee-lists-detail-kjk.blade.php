@include('layout.plugins')
@include('layout.adminor.tab-head')

<?php
error_reporting(0);
use Illuminate\Support\Facades\DB;

$datahasil = DB::table("employment")->where("ID", $_GET['ID'] ?? '')->first();
$nik = $datahasil ? $datahasil->NIK : '';
?>

<div class="container-fluid py-3">
    <!-- Overtime Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-clock-o me-2"></i>Overtime Records</h6>
            <div>
                <button type="button" class="btn btn-sm btn-light text-primary" onclick="Reload_Overtime()">
                    <i class="fa fa-refresh me-1"></i> Reload
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="Overtime" style="width:100%;height:300px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- KJK Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-calculator me-2"></i>KJK (Kompensasi Jam Kerja)</h6>
            <div>
                <button type="button" class="btn btn-sm btn-light text-primary" onclick="Reload_KJKAll()">
                    <i class="fa fa-refresh me-1"></i> Reload
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="KJKAll" style="width:100%;height:300px; background-color:white;overflow:hidden"></div>
        </div>
    </div>

    <!-- Leave and Permission Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold"><i class="fa fa-calendar-check-o me-2"></i>Leave and Permission History</h6>
            <div>
                <button type="button" class="btn btn-sm btn-light text-primary" onclick="refreshdata()">
                    <i class="fa fa-refresh me-1"></i> Reload
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div id="PermissionList" style="width:100%;height:300px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<script>
Overtime = new dhtmlXGridObject('Overtime');
Overtime.setImagePath("./dhtmlx/imgs/");
Overtime.setHeader("No,Task ID,NIK,Name,Start Date,End Date,Early IN,Early OUT,After IN,After OUT,Holiday,Total OT,Calculated OT,Rate,Salary Period,Amount Received,Reference,Note,Created Date,Created By");
Overtime.attachHeader("#rspan,#text_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter,#rspan,#rspan,#rspan");
Overtime.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,,,#stat_total,,,,");

Overtime.setInitWidths("60,100,120,200,100,100,90,90,90,90,80,90,100,100,100,120,130,200,120,100");
Overtime.setColAlign("center,center,center,left,center,center,center,center,center,center,center,center,center,right,center,right,left,left,center,left");
Overtime.setColTypes("ro,ro,ro,ro,dhxCalendar,dhxCalendar,ro,ro,ro,ro,ch,ro,ro,edn,ro,ron,ro,ro,ro,ro");
Overtime.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
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

KJK.setInitWidths("60,100,120,200,100,100,110,120,100,220,100,120,100");
KJK.setColAlign("center,center,center,left,right,right,right,right,center,left,center,center,left");
KJK.setColTypes("ro,ro,ro,ro,ron,ron,edn,ron,ro,ed,ro,ro,ro");
KJK.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
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
PermissionList.setInitWidths("70,180,110,180,120,140,150,130,220,110");
PermissionList.setColAlign("center,left,center,left,center,center,center,left,left,center");
PermissionList.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro,ro");
PermissionList.setColSorting("str,str,str,str,str,str,str,str,str,str");
PermissionList.setEditable(false);
PermissionList.enableMultiline(true);
PermissionList.setSkin("dhx_skyblue");
PermissionList.init();
refreshdata();

function refreshdata(){
    PermissionList.clearAndLoad('xml-permission-approval?NIK=<?php echo $nik?>');
}
</script>
