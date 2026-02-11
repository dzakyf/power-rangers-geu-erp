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
$qy = DB::table("recruitment_master_template")->where("Status", "=", 'ACTIVE')->orderBy("Template_ID", 'asc')->get();
$dx = "";
foreach ($qy as $ax) {
	$dx .= "{value:'$ax->Template_ID',text:'$ax->Template_ID'},";
}
$dx = substr($dx, 0, -1);

echo "
$dx
]
}";
