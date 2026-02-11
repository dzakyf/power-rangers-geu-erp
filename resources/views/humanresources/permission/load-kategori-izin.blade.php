<?php
error_reporting(0);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	


$arr=array();
$result = DB::select(DB::raw("SELECT * from kategori_izin WHERE izin_jenis_id=$_GET[ID]"));
$mx = "<select id=\"kat_izin_id\">";
if(count($result)>0){
    foreach($result as $data){
		
		$mx .="<option value='$data->kat_izin_id'>$data->kat_izin_nama</option>";
    }
}
$mx .="</select>";
echo $mx;
?>
