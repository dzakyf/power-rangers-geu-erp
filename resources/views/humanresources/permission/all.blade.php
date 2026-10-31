@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Permission</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">ALL Data</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	<div>
			Employee <input type="text" id="Name" name="Name" size="40"/> <input type="hidden" id="NIK" name="NIK" size="30"/> &nbsp; &nbsp;
			Periode&nbsp;&nbsp; <input type="text" id="Tgl1" name="Tgl1"  size="10" readonly="readonly" value="<?php echo date("Y-m-d")?>"/>
			&nbsp;&nbsp;To &nbsp;&nbsp;<input type="text" id="Tgl2" name="Tgl2"  size="10" readonly="readonly" value="<?php echo date("Y-m-d")?>"/>
			<input type="button" id="Process" name="Process"  class="btn btn-info mt-2 mr-2" value="Process" onclick="Reload_Permission();"  />
	</div>
		 <div id="Permission" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="Permission.toExcel('./export/generate.php');"  />
			<input type="button" id="refreshdata" NAME="refreshdata" value="Refresh Data" class="btn btn-info mt-2 mr-2"/>
		</div>

		<div id="PermissionDiv"></div>


	
	</div>
<script>


var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
myCalendar.setDateFormat("%Y-%m-%d");

$("#Name").coolautosuggest({
		url:"search-employee?chars=",
		showThumbnail:false,
		showDescription:true,
		idField:$("#NIK"),
		width:500,
		minChars:1,
		onSelected:function(result){
		  if(result!=null){
			$("#NIK").val(result.id); 
			$("#Name").val(result.data); 
		  }
		}
	});


function PermissionKlik() {

PermissionID = Permission.getSelectedId();

if (PermissionID !=''){
	
	LoadM('PermissionDet','permission-all-detail?ID=' + PermissionID);
	
}
return true;
}

Permission = new dhtmlXGridObject('Permission');
Permission.setImagePath("./dhtmlx/imgs/");
Permission.setHeader("No,Permission ID,NIK,Name,Employee Category,Permission Category,Sub Category,Location,Note,Date");
Permission.attachHeader("#master_checkbox,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter");
Permission.setInitWidths("70,220,100,200,100,100,200,150,220,100,100,100,100,100,100");
Permission.setColAlign("center,left,center,center,center,center,center,center,center,center");
Permission.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
Permission.setColSorting("int,str,str,str,str,str,str,str");
Permission.attachEvent("onRowDblClicked", PermissionKlik);
Permission.setSkin("dhx_skyblue");
Permission.setEditable(false);
Permission.enableMultiline(true);
Permission.init();

Reload_Permission();

function Reload_Permission(){
	Permission.clearAndLoad("xml-permission-all?NIK=" + $("#NIK").val() + "&Tgl1=" + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());
}

$("#refreshdata").click(function(){
		Reload_Permission();
});

$("#RefreshBM").click(function(){
	Reload_Permission();		
});


</script>

@endsection
