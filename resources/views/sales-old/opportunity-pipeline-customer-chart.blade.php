<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	
$mQuery = DB::table("project_mgt")->where("Source_Reference","!=","")->get();

foreach($mQuery as $fproject){
	
	$xx = DB::table("opportunity")->where("Task_ID",$fproject->Source_Reference)->first();

	$proj[$fproject->Task_ID] = $xx->Account_No;
	
}
$mqac = DB::table("project_master_activity")->orderBy("Activity_Grade","ASC")->get();

foreach($mqac as $aktif){
	
	$jenis[$aktif->Activity_Type] = $aktif->Activity_Group;
	
	
}
	
$mQuery1 = DB::table("project_activity")->where("Activity_Date","LIKE","'$_GET[Tgl1]%'")->get();
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
<script>
<?php echo $kategori."\n"; ?>
<?php echo $serie."\n";?>
redrawchart(kategori,data);
</script>
	