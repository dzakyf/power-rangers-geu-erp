<?php
	//error_reporting(E_ALL ^ E_NOTICE);
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Support\Facades\DB;
?>
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PELUNASAN UANG MUKA</div>

<div id="grid_kbterima" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>

<div id="divtom">
<input type="button" id="PrintT" NAME="PrintT" value="Print Tanda Terima" class="btn btn-success mt-2 mr-2" />
</div>


<script>
	Gridbank = new dhtmlXGridObject('grid_kbterima');
	Gridbank.setImagePath("./dhtmlx/imgs/");
	Gridbank.setHeader("No.,Nomor Akun,Tanggal,Referensi,Catatan,Nilai,Kurs Val,Departemen,Akun Lawan,Reference,Nilai,Sisa");
	Gridbank.attachFooter(["Total", '#cspan', '#cspan', '#cspan', '#cspan', "#stat_total", "", "", "", "", "#stat_total", ""]);
	Gridbank.setInitWidths("70,150,100,150,200,100,100,100,100,100,100,100");
	Gridbank.setColAlign("center,left,center,left,left,right,right,center,center,center,center,center");
	Gridbank.setColTypes("ro,ro,dhxCalendar,ed,ed,edn,ron,combo,ro,ro,ron,ron");
	Gridbank.setColSorting("int,str,str,str,str,str,str,str,str,int,int");
	Gridbank.setDateFormat("%Y-%m-%d");
	Gridbank.setEditable(false);
	Gridbank.setNumberFormat("0,000", 5, ".", ",");
	Gridbank.setNumberFormat("0,000", 6, ".", ",");
	Gridbank.enableKeyboardSupport(true);
	Gridbank.setSkin("dhx_skyblue");
	Gridbank.init();

	refreshdatadetail();

	function refreshdatadetail() {
		Gridbank.clearAndLoad("xml-finance-report-archive-bank-advance-detail?ID=<?php echo $_GET['ID']; ?>");

		
	}

	
	$('#PrintT').click(function() {

		<?php 
			$data =  DB::table("bank_advance_dtl")->where("Task_ID",$_GET['ID'])->orderBy("ID", "desc")->first();

			if($data->Bank_Val > 0) {
				$url = "finance-print-pum-tanda-terima";
			} else {
				$url = "finance-print-pum-tanda-keluar";
			}
		?>

		window.open("<?php echo $url; ?>?id=<?php echo $_GET['ID']; ?>&type=bank_advanced");

	});

</script>