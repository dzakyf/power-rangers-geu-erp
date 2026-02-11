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

$result = DB::select("SELECT column_name FROM information_schema.columns where table_name = 'opportunity'");
$jumlah = count($result);
if ($jumlah > 0) {
	
	
    foreach ($result as $row) {
		$key = $row->column_name;
			if ($key != 'Task_ID' && $key != 'Created_Date' && $key != 'Created_By' && $key != 'Location' && $key != 'Company_Name'){
				
			if ($key =='Activity_Type') {
				switch($_POST[$key]) {
					case "EMAIL": $grade = 1;break;
					case "PHONE CALL": $grade = 1;break;
					case "MEETING": $grade = 1;break;
					case "SUBMIT PROPOSAL": $grade = 2;break;
					case "NEGOTIATE": $grade = 2;break;
					case "AGREED SHIPPING": $grade = 3;break;
					case "TRIAL SHIPPING": $grade = 3;break;
					case "NEW CONTRACT": $grade = 4;break;
					case "CLOSE CONTRACT": $grade = 6;break;
					case "FAILED": $grade = 6;break;
					default: $grade = 1; break;				
				}
			}
			
				$ars 	.="\"$key\",";
				
				$isi	.="'$_POST[$key]',";
				
				
				$MX .="$key= $_POST[$key],";
			
			}					
			

    }

}

$ars = substr($ars,0,-1);
$isi = substr($isi,0,-1);

$kueri = "INSERT INTO online_document (Task_ID,Subject,EmailTo,Data_Text,Location,Created_By) 
			VALUES ('$Task_ID','$EmailSubject','$EmailTo','$dataeditor','$LSP','$Saya')";
DB::insert(DB::raw($kueri));

echo "success";
?>