<?php

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


header('Content-Type: application/json');
	
echo "{
options: ["; 
$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;
$qy = DB::table('bank_acc')->where("Status",'ACTIVE')->get();
$dx = "";
foreach ($qy as $ax){
	
		$dx .= "{value:'$ax->Acc_No',text:'$ax->Acc_No - $ax->Bank_Name'},";

	
}
$dx = substr($dx,0,-1);

echo "
$dx
]
}"; 

?>
