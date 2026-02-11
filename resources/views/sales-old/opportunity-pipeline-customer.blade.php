<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

if ($_GET['Tgl1'] != ''){
	$tgx = $_GET['Tgl1'];
}else{
	$tgx = date('Y');
}
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	function load_bac_kota(){
		$query = DB::table("branch_lists")->get();
			foreach ($query as $fa){
					$optx .="<option value='$fa->IATA_Code'>$fa->IATA_Code - $fa->City_Name</option>";
			}
		$optx .= "<option value=''>ALL</option>";
		return $optx;
	}
	

$mQuery = DB::table("project_mgt")->where("Source_Reference","!=","")->get();

foreach($mQuery as $fproject){
	
	$xx = DB::table("opportunity")->where("Task_ID",$fproject->Source_Reference)->first();

	$proj[$fproject->Task_ID] = $xx->Account_No;
	
}
$mqac = DB::table("project_master_activity")->orderBy("Activity_Grade","ASC")->get();

foreach($mqac as $aktif){
	
	$jenis[$aktif->Activity_Type] = $aktif->Activity_Group;
	
	
}
	
$mQuery1 = DB::table("project_activity")->where("Activity_Date","LIKE","$tgx%")->get();
foreach($mQuery1 as $fpo){
	$nama = $proj[$fpo->Task_ID];
	$target[$nama][$jenis[$fpo->Activity_Type]] += $fpo->Estimated_Rev;
	
}
$serie =" var data = [";
$mser = DB::table("customerz")->get();
$kategori = " var kategori = [";
$nocust = 1;
foreach($mser as $users){
	$kategori .= "'$users->Company_Name',";
	$user[$users->Customer_No] = $users->Company_Name;
	$probing[$nocust] = $target[$users->Customer_No]['PROBING']+0;
	$new[$nocust] = $target[$users->Customer_No]['NEW']+0;
	$sph[$nocust] = $target[$users->Customer_No]['SPH']+0;
	$won[$nocust] = $target[$users->Customer_No]['WON']+0;
	$addendum[$nocust] = $target[$users->Customer_No]['ADDENDUM']+0;
	$closing[$nocust] = $target[$users->Customer_No]['CLOSING']+0;
	$failed[$nocust] = $target[$users->Customer_No]['FAILED']+0;
	$nocust++;
	
}
$serie .="{
                name: 'PROBING',
                data: [";
					for($m=1;$m<=$nocust;$m++){ $xprobing .= $probing[$m].","; } $xprobing = substr($xprobing,0,-2); $serie .= $xprobing; 
				$serie .= "]
            },
			{
                name: 'NEW',
                data: [";
					for($m=1;$m<=$nocust;$m++){ $xnew .= $new[$m].","; } $xnew = substr($xnew,0,-2); $serie .= $xnew;
				$serie .= "]
            },
			{
                name: 'SPH',
                 data: [";
					for($m=1;$m<=$nocust;$m++){ $xsph .= $sph[$m].","; } $xsph = substr($xsph,0,-2); $serie .= $xsph;
				$serie .= "]
            },
			{
                name: 'WON',
                 data: [";
					for($m=1;$m<=$nocust;$m++){ $xwon .= $won[$m].","; } $xwon = substr($xwon,0,-2); $serie .= $xwon;
				$serie .= "]
            },
			{
                name: 'ADDENDUM',
                data: [";
					for($m=1;$m<=$nocust;$m++){ $xaddendum .= $addendum[$m].","; } $xaddendum = substr($xaddendum,0,-2); $serie .= $xaddendum;
				$serie .= "]
            },
			{
                name: 'CLOSING',
                 data: [";
					for($m=1;$m<=$nocust;$m++){ $xclosing .= $closing[$m].","; } $xclosing = substr($xclosing,0,-2); $serie .= $xclosing;
				$serie .= "]
            },
			{
                name: 'FAILED',
                 data: [";
					for($m=1;$m<=$nocust;$m++){ $xfailed .= $failed[$m].","; } $xfailed = substr($xfailed,0,-2); $serie .= $xfailed;
				$serie .= "]
            }";
	
$serie .="];";
$kategori = substr($kategori,0,-1);

$kategori .="];";		

	
		
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
<!-- Main Content Header -->
   <div class="main-content-header">
        <h1>Sales</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Opportunity</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Pipeline Summaries By Customers</span>
            </li>
        </ol>
    </div> 
   
    <!-- End Main Content Header -->

	<!-- Stats Card -->
<div class="row">
	<div class="form-group">
		Year <input type="text" id="Tgl1"  size="5" value="<?php echo $tgx ?>" maxlength="4" class="form-control-sm" />
		<input type="button" id="SubmitCheck" value="Process" class="btn btn-success" style="vertical-align:top" />
	</div>

	
	<div id="ReportPipeline" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div>
	 <input type="button" id="ExportExl" name="ExportExl" value="Export Table" onclick="ReportPipeline.toExcel('./export/generate.php');" class="btn btn-success mt-2 mr-2"  />
	
	</div>
	
	
</div>
<div class="row" style="text-align:center">

	 <div id="ReportPipelineChart" style="width:1000px;height:400px; overflow:hidden"></div>
		<div id="chrtdiv"></div>
</div>	
<script>
	var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y");


	$("#SubmitCheck").click(function(){
		location.href='sales-opportunity-pipeline-customer?Tgl1=' + $("#Tgl1").val();			
	});

	function itungx() {
		reloadchart();

		$.unblockUI(); 
		return true;
	}
			
			ReportPipeline = new dhtmlXGridObject('ReportPipeline');
			ReportPipeline.setImagePath("dhtmlx/imgs/");
			ReportPipeline.setHeader("No,Customer Name,Probing,New,SPH,Won,Addendum,Closing,Failed");
			ReportPipeline.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
			ReportPipeline.attachFooter("Total,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
			ReportPipeline.setInitWidths("60,300,100,100,100,100,100,100,100");
			ReportPipeline.setColSorting("int,str,int,int,int,int,int,int,int");

			ReportPipeline.setColAlign("center,left,right,right,right,right,right,right,right,right,right");
			ReportPipeline.setColTypes("ro,ro,ron,ron,ron,ron,ron,ron,ron,ron,ron");
			ReportPipeline.setNumberFormat("0,000",2,".",",");
			ReportPipeline.setNumberFormat("0,000",3,".",",");
			ReportPipeline.setNumberFormat("0,000",4,".",",");
			ReportPipeline.setNumberFormat("0,000",5,".",",");
			ReportPipeline.setNumberFormat("0,000",6,".",",");
			ReportPipeline.setNumberFormat("0,000",7,".",",");
			ReportPipeline.setNumberFormat("0,000",8,".",",");
			ReportPipeline.setMathRound(2);
			ReportPipeline.setEditable(false);
			ReportPipeline.enableMultiline(true);
			ReportPipeline.enableSmartRendering(true);
			ReportPipeline.setSkin("dhx_skyblue");
			ReportPipeline.init();


setTimeout(function(){
		$.blockUI(); 
		refereshdata();
		itungx();
},1000);

function refereshdata(){
	ReportPipeline.clearAndLoad('xml-sales-opportunity-pipeline-customer?Tgl1=' + $("#Tgl1").val());
}
function reloadchart(){
LoadM('chrtdiv','opportunity-pipeline-customer-chart?Tgl1=' + $("#Tgl1").val());	
  
}
<?php echo $kategori."\n"; ?>
<?php echo $serie."\n";?>


$('#ReportPipelineChart').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: 'Pipeline By Customers'
            },
            xAxis: {
				 categories: kategori               
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total Revenues (Rp)'
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold',
                        color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                    }
                }
            },
            legend: {
                align: 'right',
                x: 0,
                verticalAlign: 'bottom',
                y: 20,
                floating: true,
                backgroundColor:  '#EEEEF8',
                borderColor: '#CCC',
                borderWidth: 1,
                shadow: true
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.x +'</b><br/>'+
                        this.series.name +': '+ this.y +'<br/>'+
                        'Total: '+ this.point.stackTotal;
                }
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true,
                        color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                        style: {
                            textShadow: '0 0 3px black, 0 0 3px black'
                        }
                    }
                }
            },
            series: data
		});
	
	chart = $('#ReportPipelineChart').highcharts();

function redrawchart(kats,data){

	chart.xAxis[0].setCategories(kats);
	chart.series[0].setData(data , true);;
    setTimeout(function(){
		chart.redraw();
	},2000);
}

	</script>
	
	
@endsection
