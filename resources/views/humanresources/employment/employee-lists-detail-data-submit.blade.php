<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
$ars = "";
$ax = "";
$isi = "";

foreach($_POST as $key=>$value)
{	
	
    	
			if ($key != 'buttonSimpan' && $key != 'ID' && $key != 'undefined'){
				
			
				$ars 	.="$key,";
				
				$isi	.="'$_POST[$key]',";
				
				
				$MX .="$key = '$_POST[$key]',";
			
			}					
			

    

}

$MX = substr($MX,0,-1);
$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

	
 
$kueri = "UPDATE employment set $MX WHERE ID=$_POST[ID]";
DB::update(DB::raw($kueri));

// Sync the employee's branch office to users.Location (matched by users.Emp_ID = employment.NIK)
if (isset($_POST['Location'])) {
	$empRow = DB::table('employment')->where('ID', $_POST['ID'])->first();
	if ($empRow) {
		DB::table('users')->where('Emp_ID', $empRow->NIK)->update(['Location' => $_POST['Location']]);
	}
}

echo "success";
?>