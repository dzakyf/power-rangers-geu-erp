	<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$ars = "";
$ax = "";
$isi = "";
foreach($_POST as $key=>$value)
{
		
		if ($key !='IssueListsDetaildel' && $key !='SubmitCustDetail' && $key !='DelCustDetail'){
			$ars .= "$key = '$value',";
			$ax .="$key,";
			$isi.="'$value',";
		}
}
$Saya = Auth::User()->username;
$LSP = Auth::user()->Location;

$ars = substr($ars,0,-1);
$ax = substr($ax,0,-1);
$isi = substr($isi,0,-1);

DB::insert(DB::raw("INSERT INTO project_issues($ax,Created_Date,Created_By,Location) VALUES($isi,NOW(),'$Saya','$LSP')"));
DB::insert(DB::raw("INSERT INTO project_issues_log($ax,Created_Date,Created_By,Location,Action_By,Action_Date,Action_Note) VALUES($isi,NOW(),'$Saya','$LSP','$Saya',NOW(),'ADD NEW ISSUES')"));


echo "success";

?>