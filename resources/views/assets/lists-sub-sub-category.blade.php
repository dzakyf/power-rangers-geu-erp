<?php
    $select = "<select id='Asset_Type' class='form-control'>";
    $category = $_GET['Category'];
    $subCat = $_GET['Sub_Category'];

    $get = DB::table('master_product_sub_sub_group')
        ->where('Sub_Group', $subCat)
        ->where("Product_Group", $category)
        ->orderBy('Sub_Sub_Group', "ASC")
        ->get();
    $option = "";
    foreach ($get as $key => $value) {
        $option .= "<option value='{$value->Sub_Sub_Group}'>{$value->Sub_Sub_Group} - {$value->Initial}</option>";
    }
    $select .= $option . "</select>";

    echo $select;