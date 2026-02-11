<?php
error_reporting(0);
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
if ($_GET['mask']){
	$qy = DB::table('branch_coa')->where("Description","LIKE","'%$_GET[mask]%'")->orderBy("Acc_No","ASC")->get();
}else{
	$qy = DB::table('branch_coa')->orderBy("Acc_No","ASC")->get();
}
$dx = "";
foreach ($qy as $ax){
	
		$dx .= "{value:'$ax->Acc_No',text:'$ax->Acc_No - $ax->Description'},";

	
}
$dx = substr($dx,0,-1);

echo "
$dx
]
}"; 

?>
