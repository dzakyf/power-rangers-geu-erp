@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Master</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Holiday</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row no-gutters">
		 <div id="Holiday" style="width:auto;height:500px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="Holiday.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="Settings" NAME="Settings" value="Settings" onclick="settinglibur()" class="btn btn-info mt-2 mr-2"/>
		</div>
	</div>
	<div id="HolidayDiv"></div>
<script>





Holiday = new dhtmlXGridObject('Holiday');
Holiday.setImagePath("./dhtmlx/imgs/");
Holiday.setHeader("No,Year,Day,Date,Holiday,Description");
Holiday.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter");
Holiday.setInitWidths("80,100,100,100,100,300");
Holiday.setColAlign("center,right,center,center,left");
Holiday.setColTypes("ro,ro,ro,ro,ro,ro");
Holiday.setColSorting("int,str,str,str,str,str");
Holiday.setEditable(true);
Holiday.enableMultiline(true);
Holiday.enableSmartRendering(true, 100);
Holiday.setSkin("dhx_skyblue");
PHoliday = new dataProcessor("hrd-master-holiday-submit");
	PHoliday.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
PHoliday.setUpdateMode("Off");
	
PHoliday.init(Holiday);
Holiday.init();

function settinglibur() {
	LoadM('HolidayDiv','master-holiday-detail');
}
		
setTimeout(function(){
	reloadmasterHoliday();
},1000);

function reloadmasterHoliday(){
	Holiday.clearAndLoad("xml-hrd-master-holiday");	

}
$("#SaveBM").click(function(){
		PHoliday.sendData();
		alert("Holiday was Saved!");
		setTimeout(function(){
			reloadmasterHoliday();
		},1000);
		
});
$("#DeleteBM").click(function(){
	dhtmlx.confirm('Delete Data?', 'Holiday', function(r) {
			
		if (r==true){
							
				Holiday.deleteSelectedRows();
				PHoliday.sendData();
				alert("Data were deleted!");
				reloadmasterHoliday();
		}
	});
		
});
$("#AddBM").click(function(){
	var idbi =Holiday.uid();
		Holiday.addRow(idbi,[Holiday.getRowsNum()+1,''],0);
		Holiday.selectRow(0);
		
});


$("#RefreshBM").click(function(){
	reloadmasterHoliday();		
});



</script>

@endsection
