<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


	echo "<script>
		var jans = parseFloat(BudgetManagement.cells('$_GET[ID]',4).getValue());
		var pebs = parseFloat(BudgetManagement.cells('$_GET[ID]',5).getValue());
		var mars = parseFloat(BudgetManagement.cells('$_GET[ID]',6).getValue());
		var aprs = parseFloat(BudgetManagement.cells('$_GET[ID]',7).getValue());
		var mays = parseFloat(BudgetManagement.cells('$_GET[ID]',8).getValue());
		var juns = parseFloat(BudgetManagement.cells('$_GET[ID]',9).getValue());
		var juls = parseFloat(BudgetManagement.cells('$_GET[ID]',10).getValue());
		var agts = parseFloat(BudgetManagement.cells('$_GET[ID]',11).getValue());
		var seps = parseFloat(BudgetManagement.cells('$_GET[ID]',12).getValue());
		var octs = parseFloat(BudgetManagement.cells('$_GET[ID]',13).getValue());
		var novs = parseFloat(BudgetManagement.cells('$_GET[ID]',14).getValue());
		var dess = parseFloat(BudgetManagement.cells('$_GET[ID]',15).getValue());
		
		var jansx = parseFloat($('#div_jan').text().replaceAll(',',''));
		var pebsx =  parseFloat($('#div_feb').text().replaceAll(',',''));
		var marsx =  parseFloat($('#div_mar').text().replaceAll(',',''));
		var aprsx =  parseFloat($('#div_apr').text().replaceAll(',',''));
		var maysx =  parseFloat($('#div_may').text().replaceAll(',',''));
		var junsx = parseFloat($('#div_jun').text().replaceAll(',',''));
		var julsx = parseFloat($('#div_jul').text().replaceAll(',',''));
		var agtsx = parseFloat($('#div_aug').text().replaceAll(',',''));
		var sepsx = parseFloat($('#div_sep').text().replaceAll(',',''));
		var octsx = parseFloat($('#div_oct').text().replaceAll(',',''));
		var novsx = parseFloat($('#div_nov').text().replaceAll(',',''));
		var dessx = parseFloat($('#div_dec').text().replaceAll(',',''));

		if (jansx != jans ){
			alert( jansx + 'January is not match!' + jans);$.unblockUI();
		}else
		if (pebsx != pebs){
			alert('February is not match!');$.unblockUI();
		}else if (marsx != mars){
			alert('March is not match!');$.unblockUI();
		}else if (aprsx != aprs){
			alert('April is not match!');$.unblockUI();
		}else if (maysx != mays){
			alert('May is not match!');$.unblockUI();
		}else if (junsx != juns){
			alert('June is not match!');$.unblockUI();
		}else if (julsx != juls){
			alert('July is not match!');$.unblockUI();
		}else if (agtsx != agts){
			alert('August is not match!');$.unblockUI();
		}else if (sepsx != seps){
			alert('September is not match!');$.unblockUI();
		}else if (octsx != octs){
			alert('October is not match!');$.unblockUI();
		}else if (novsx != novs){
			alert('November is not match!');$.unblockUI();
		}else if (dessx != dess){
			alert('December is not match!');$.unblockUI();
		}else{
			
			LoadM('loaddivbatch','budget-finalize-submit?ID=$_GET[ID]');
		}
		</script>
		";
		$nomors++;


?>
