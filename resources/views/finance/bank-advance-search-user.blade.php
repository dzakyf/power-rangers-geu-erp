<?php
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$arr = [];

if (strlen(trim(app('request')->input('chars'))) > 2) {

	$result = DB::table('pum_karyawan')->where('Name','LIKE', '%'.app('request')->input('chars').'%')->get();


	if(count($result) > 0){
		foreach($result as $hasil){


			$Description=  $hasil->Name."<br>".$hasil->Department."<br>".$hasil->Inv_No."<br>PUM Ref : ".$hasil->Inv_No;
			$arr[]=array("id" => $hasil->ID, "data" => $hasil->Name, "thumbnail" => $hasil->PIC, "description" => $Description);
		}

		
	}
}
echo json_encode($arr);

?>