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
$qy = DB::table("branch_coa")->where("Listed_OnSearch", "=", 1)->orderBy("Acc_No", 'asc')->get();
$dx = "";
foreach ($qy as $ax) {
	$dx .= "{value:'$ax->Acc_No',text:'$ax->Description - $ax->Acc_No},";
}
$dx = substr($dx, 0, -1);

echo "
$dx
]
}";
