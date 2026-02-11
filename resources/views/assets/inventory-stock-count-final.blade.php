<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
$LSP = Auth::user()->Location;
$NIK =  Auth::user()->Emp_ID;
$saya =  Auth::user()->username;


DB::update(DB::raw("UPDATE stock_count set Status = 'CLOSED'  where Task_ID='$_GET[Task_ID]'"));
		
		

echo "<script>
	alert('Stock Count Success Created!');
	refreshdata();	

	$.unblockUI();
	</script>";

	
	
?>
