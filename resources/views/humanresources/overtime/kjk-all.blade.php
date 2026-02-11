@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">KJK</a>
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
			Periode&nbsp;&nbsp; <input type="text" id="Tgl1" name="Tgl1"  size="10" readonly="readonly" value="<?php echo date("Y-m")?>"/>
			
			<input type="button" id="Process" name="Process"  class="btn btn-info mt-2 mr-2" value="Process" onclick="Reload_KJKAll();"  />
	</div>
		 <div id="KJKAll" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="KJK.toExcel('./export/generate.php');"  />
			<input type="button" id="refreshdata" NAME="refreshdata" value="Refresh Data" class="btn btn-info mt-2 mr-2"/>
		</div>

		<div id="KJKAllDiv"></div>


	
	</div>
<script>


var myCalendar;
myCalendar = new dhtmlXCalendarObject(["Tgl1"]);
myCalendar.setDateFormat("%Y-%m");

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



KJK = new dhtmlXGridObject('KJKAll');
KJK.setImagePath("./dhtmlx/imgs/");
KJK.setHeader("No,Task ID,NIK,Name,Allowance,Rate,KJK Value,Total,KJK Period,Note,Salary Period,Salary Group");
KJK.attachHeader("#rspan,#text_filter,#numeric_filter,#combo_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter");
KJK.setInitWidths("80,100,120,250,100,100,100,100,100,300,100,100");
KJK.setColAlign("center,center,center,left,left,center,left,center,center,center");
KJK.setColTypes("ro,ro,ro,ro,ron,ron,edn,ron,ro,ed,ro,ro");
KJK.setColSorting("int,str,str,str,str,str,str,str,str");
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
	KJK.clearAndLoad("xml-kjk-all?NIK=" + $("#NIK").val() + "&Tgl1=" + $("#Tgl1").val());
}

$("#refreshdata").click(function(){
		Reload_KJKAll();
});

$("#RefreshBM").click(function(){
	Reload_KJKAll();		
});


</script>

@endsection
