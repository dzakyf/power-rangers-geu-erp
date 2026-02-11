<?php
error_reporting(0);
ini_set('memory_limit', '-1');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$arr = [];

if (strlen(trim(app('request')->input('chars'))) > 2) {

	$result = DB::table('opportunity_detail')->where('PO_No','LIKE', '%'.app('request')->input('chars').'%')->orWhere('Subject','LIKE', '%'.app('request')->input('chars').'%')->orWhere('Description','LIKE', '%'.app('request')->input('chars').'%')->get();


	if(count($result) > 0){
		foreach($result as $hasil){


			$Description=  $hasil->PO_No."<br>Subject : ".$hasil->Subject."<br>Description : ".$hasil->Description."<br>Date : ".$hasil->PO_Date."<br>Total : ".$hasil->Total;
			$arr[]=array("id" => $hasil->PO_No, "data" => $hasil->Contract_No, "thumbnail" => $hasil->Reference_No, "description" => $Description);
		}

		
	}
}
echo json_encode($arr);

?>