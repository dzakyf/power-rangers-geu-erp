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
$qy = DB::table('jns_izin')->get();
$dx = "";
foreach ($qy as $ax){
	
		$dx .= "{value:'$ax->izin_jenis_id',text:'$ax->izin_jenis_name'},";

	
}
$dx = substr($dx,0,-1);

echo "
$dx
]
}"; 

?>
