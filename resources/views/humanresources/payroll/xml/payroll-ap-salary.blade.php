<?php

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;



header('Content-Type: application/json');
	
echo "{
options: ["; 
$qy = DB::table('supplier')->select("AP_Salary_Group")->where("AP_Salary_Group","!=","")->groupBy("AP_Salary_Group")->get();
$dx = "";
foreach ($qy as $ax){
	
		$dx .= "{value:'$ax->AP_Salary_Group',text:'$ax->AP_Salary_Group'},";

	
}
$dx = substr($dx,0,-1);

echo "
$dx
]
}"; 


?>
