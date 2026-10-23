<?php
use Illuminate\Support\Facades\DB;

$category = request('Category');

// Menambahkan handling pengecekan kata kunci 'vehicle' atau 'kendaraan' pada onchange
$select = "<select id='Asset_Sub_Category' class='form-control' onchange=\"loadassettype(); 
    var val = this.value.toLowerCase();
    var text = this.options[this.selectedIndex].text.toLowerCase();
    
    if ((val.includes('vehicle') || text.includes('vehicle')) && typeof toggleVehicleFieldsNew === 'function') {
        toggleVehicleFieldsNew();
    }
\">";

$option = "";
if (!empty($category)) {
    $get = DB::table('master_product_sub_group')->where("Product_Group", $category)->orderBy('Sub_Group')->get();
    foreach ($get as $key => $value) {
        $option .= "<option value='{$value->Sub_Group}'>{$value->Sub_Group} - {$value->Initial}</option>";
    }
}
$select .= $option . "</select>";

echo $select;