<?php
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$arr = [];

if (strlen(trim(app('request')->input('chars'))) > 2) {

	$result = DB::table('employment')->where('Name','LIKE', '%'.app('request')->input('chars').'%')->get();


	if(count($result) > 0){
		foreach($result as $hasil){


			$Description=  $hasil->Name."<br>Department : ".$hasil->Department."<br>Jenis Kelamin : ".$hasil->Gender."<br>NIK : ".$hasil->NIK."<br>Position : ".$hasil->Position;
			$arr[]=array("id" => $hasil->NIK, "data" => $hasil->Name, "thumbnail" => $hasil->Department, "description" => $Description);
		}

		
	}
}
echo json_encode($arr);

?>