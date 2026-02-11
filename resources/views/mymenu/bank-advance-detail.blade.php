<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PERTANGGUNG JAWABAN UANG MUKA</div>

<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
		


<script>

Gridbank = new dhtmlXGridObject('grid_kbterima');
Gridbank.setImagePath("./dhtmlx/imgs/");
Gridbank.setHeader("No.,Nomor Akun,Tanggal,Referensi,Catatan,Nilai,Kurs Val,Departemen,Akun Lawan,Reference,Nilai,Sisa");
Gridbank.attachFooter(["Total",'#cspan','#cspan','#cspan','#cspan',"#stat_total","","","","","#stat_total",""]);
Gridbank.setInitWidths("70,150,100,150,200,100,100,100,100,100,100,100");
Gridbank.setColAlign("center,left,center,left,left,right,right,center,center,center,center");
Gridbank.setColTypes("ro,ro,dhxCalendar,ed,ed,edn,ron,combo,ro,ro,ron,ron");
Gridbank.setColSorting("int,str,str,str,str,str,str,str,str,int,int");
Gridbank.setDateFormat("%Y-%m-%d"); 
Gridbank.setEditable(false);
Gridbank.setNumberFormat("0,000",5,".",",");
Gridbank.setNumberFormat("0,000",6,".",",");
Gridbank.enableKeyboardSupport(true);
Gridbank.setSkin("dhx_skyblue");

Gridbank.init();

refreshdatadetail();

function refreshdatadetail(){
			Gridbank.clearAndLoad("xml-finance-bank-advance-detail?ID=<?php echo $_GET['ID'];?>");

}



</script>
