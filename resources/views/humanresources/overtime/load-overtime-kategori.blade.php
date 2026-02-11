<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

?>

 
  <select id="kat_overtime_id">
                
              <?php
			  $mx = DB::table("overtime_kategori")->where("overtime_jenis_id", $_GET['ID'])->orderBy("kat_overtime_nama","ASC")->get();
			  foreach ($mx as $fx){
				  echo "<option value='$fx->kat_overtime_id'>$fx->kat_overtime_nama</option>";
				  
			  }
			  ?>
 </select>
	
