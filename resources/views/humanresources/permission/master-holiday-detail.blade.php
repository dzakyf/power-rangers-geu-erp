<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	$priv =  Auth::user()->Priv_ID;
	$fetch = DB::table("supplier")->where("ID",$_GET['ID'])->first();
	$sobjf = DB::select(DB::raw("SHOW FIELDS FROM supplier"));

	echo "<script>";

		foreach($sobjf as $objf){
		$idobj = $objf->Field;
		$isinya = $fetch->$idobj;
			
				echo "$('#$idobj').val('$isinya'); \n";
		}
	
	echo "</script>";

?>
<style>
html, body{
			margin:0;
			padding:0;
			height:100%;
			overflow:hidden;
		}

		.dhx_cal_event div.dhx_footer,
		.dhx_cal_event.past_event div.dhx_footer,
		.dhx_cal_event.event_english div.dhx_footer,
		.dhx_cal_event.event_math div.dhx_footer,
		.dhx_cal_event.event_science div.dhx_footer{
			background-color: transparent !important;
		}
		.dhx_cal_event .dhx_body{
			-webkit-transition: opacity 0.1s;
			transition: opacity 0.1s;
			opacity: 0.7;
		}
		.dhx_cal_event .dhx_title{
			line-height: 12px;
		}
		.dhx_cal_event_line:hover,
		.dhx_cal_event:hover .dhx_body,
		.dhx_cal_event.selected .dhx_body,
		.dhx_cal_event.dhx_cal_select_menu .dhx_body{
			opacity: 1;
		}

		.dhx_cal_event.event_math div,
		.dhx_cal_event_line.event_math{
			background-color: #FF5722 !important;
			border-color: #732d16 !important;
		}

		.dhx_cal_event.dhx_cal_editor.event_math{
			background-color: #FF5722 !important;
		}

		.dhx_cal_event_clear.event_math{
			color:#FF5722 !important;
		}

		.dhx_cal_event.event_science div,
		.dhx_cal_event_line.event_science{
			background-color: #0FC4A7 !important;
			border-color: #698490 !important;
		}

		.dhx_cal_event.dhx_cal_editor.event_science{
			background-color: #0FC4A7 !important;
		}

		.dhx_cal_event_clear.event_science{
			color:#0FC4A7 !important;
		}

		.dhx_cal_event.event_english div,
		.dhx_cal_event_line.event_english{
			background-color: #684f8c !important;
			border-color: #9575CD !important;
		}

		.dhx_cal_event.dhx_cal_editor.event_english{
			background-color: #684f8c !important;
		}

		.dhx_cal_event_clear.event_english{
			color:#B82594 !important;
		}
</style>
<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">HOLIDAY SETTING</div>
<div id="JadwalLibur" class="dhx_cal_container" style='width:100%; height:100%;'>
		<div class="dhx_cal_navline" style="padding-bottom:50px;margin-bottom:50px">
			<div class="dhx_cal_prev_button">&nbsp;</div>
			<div class="dhx_cal_next_button">&nbsp;</div>
			<div class="dhx_cal_today_button"></div>
			<div class="dhx_cal_date"></div>
		</div>
		<br><Br>
		<div class="dhx_cal_header"></div>
			<Br><br>
		<div class="dhx_cal_data">
		</div>		
	</div>
	

<script>

function init() {

		scheduler.config.multi_day = false;
		scheduler.config.xml_date="%Y-%m-%d";
		scheduler.config.dblclick_create = true;
		scheduler.config.details_on_create = true;
		var subject = [
			
			{ key: '1', label: 'Hari Libur' },
			{ key: '2', label: 'Cuti Bersama' }
			
		];
		scheduler.templates.event_class=function(start, end, event){
			var css = "";

			if(event.subject) // if event has subject property then special class should be assigned
				css += "event_"+event.subject;

			if(event.id == scheduler.getState().select_id){
				css += " selected";
			}
			return css; // default return
		}
		scheduler.config.lightbox.sections=[
				{name:"description", height:43, map_to:"text", type:"textarea" , focus:true},
				{name:"time", height:72, type:"select", options: subject, map_to:"details" }
				
			];


		scheduler.init('JadwalLibur',new Date('<?php echo date('Y'); ?>','<?php $bln = date('m'); echo $bln-1; ?>','<?php echo date('d'); ?>'),"month");
		scheduler.load("xml-master-holiday-detail");
	
        var dscheduler = new dataProcessor("master-holiday-detail-submit");
		dscheduler.setTransactionMode("POST", true);
        dscheduler.init(scheduler);	
}
init();

</script>
