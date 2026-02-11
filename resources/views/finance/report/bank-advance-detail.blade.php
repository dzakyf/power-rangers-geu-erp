<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">Outstanding PTJB</div>

<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>




<script>
	Gridbank = new dhtmlXGridObject('grid_kbterima');
	Gridbank.setImagePath("./dhtmlx/imgs/");
	Gridbank.setHeader("No.,Task ID,Nama,Tgl Pengajuan,Nilai,Deskripsi");
	Gridbank.attachFooter(["Total", '#cspan', '#cspan', '#cspan', "#stat_total"]);
	Gridbank.setInitWidths("70,150,100,150,200,100,100,100");
	Gridbank.setColAlign("center,left,left,left,right,left");
	Gridbank.setColTypes("ro,ed,ro,dhxCalendar,ron,ro");
	Gridbank.setColSorting("int,str,str,str,str,str,str,str,str,int,int");
	Gridbank.setDateFormat("%Y-%m-%d");
	Gridbank.setEditable(true);
	Gridbank.setNumberFormat("0,000", 4, ".", ",");
	Gridbank.enableKeyboardSupport(true);
	Gridbank.setSkin("dhx_skyblue");

	Gridbank.init();

	refreshdatadetail();

	function refreshdatadetail() {
		Gridbank.clearAndLoad("xml-finance-report-bank-advance-detail?nama=<?php echo $_GET['nama']; ?>&Department=<?php echo $_GET['Department']; ?>");

	}
</script>