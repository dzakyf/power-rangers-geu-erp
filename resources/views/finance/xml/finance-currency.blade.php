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
$qy = DB::table('currency')->get();
$dx = "";
foreach ($qy as $ax){
	
		$dx .= "{value:'$ax->Currency_Symbol',text:'$ax->Currency_Symbol - $ax->Currency_Name'},";

	
}
$dx = substr($dx,0,-1);

echo "
$dx
]
}"; 

?>
