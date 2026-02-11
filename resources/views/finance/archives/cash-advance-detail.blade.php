
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PELUNASAN UANG MUKA</div>

<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>




	<script>
		

		GridCash = new dhtmlXGridObject('grid_kbterima');
		GridCash.setImagePath("./dhtmlx/imgs/");
		GridCash.setHeader("No.,Nomor Akun,Tanggal,Referensi,Catatan,Nilai,Kurs Val,Departemen,Akun Lawan,Reference,Nilai,Sisa");
		GridCash.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', "#stat_total", "", "", "", "", "#stat_total", ""]);
		GridCash.setInitWidths("70,150,100,150,200,100,100,100,100,100,100,100");
		GridCash.setColAlign("center,left,center,left,left,right,right,center,center,center,center");
		GridCash.setColTypes("ro,combo,dhxCalendar,ed,ed,edn,ron,combo,ro,ro,ron,ron");
		GridCash.setColSorting("int,str,str,str,str,str,str,str,str,int,int");
		GridCash.setDateFormat("%Y-%m-%d");
		GridCash.setEditable(false);
		GridCash.setNumberFormat("0,000", 5, ".", ",");
		GridCash.setNumberFormat("0,000", 6, ".", ",");
		GridCash.setNumberFormat("0,000", 10, ".", ",");
		GridCash.setNumberFormat("0,000", 11, ".", ",");
		GridCash.enableKeyboardSupport(true);
		GridCash.setSkin("dhx_skyblue");
		
		GridCash.init();
	
		refreshdatadetail();

		function refreshdatadetail() {
			GridCash.clearAndLoad("xml-finance-report-archive-cash-advance-detail?ID=<?php echo $_GET['ID']; ?>");

		}
		
	</script>