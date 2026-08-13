<?php

    $select = "<select id='Asset_Sub_Category' onchange='loadassettype()'>";
    $category = $_GET['Category'];

    $get = DB::table('master_product_sub_group')->where("Product_Group", $category)->get();
    $option = "";
    foreach ($get as $key => $value) {
        $option .= "<option value='{$value->Sub_Group}'>{$value->Sub_Group} - {$value->Initial}</option>";
    }
    $select .= $option . "</select>";

    echo $select;