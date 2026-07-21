<?php

use Illuminate\Support\Facades\DB;

$result = DB::select("SELECT TABLE_NAME FROM information_schema.columns where TABLE_SCHEMA='erp_ksps' GROUP BY TABLE_NAME");

foreach ($result as $key => $value) {
    $aliasNameTable = ucwords(str_replace('_', ' ',$value->TABLE_NAME));
    $aliasNameColumn = ucwords(str_replace('_', ' ',$value->COLUMN_NAME));
    $cek = DB::table('metadata_tables')->where("table_name", $value->TABLE_NAME)->first();
    if (!$cek) {
        DB::table('metadata_tables')->insertGetId([
            'schema_id' => 1,
            'table_name' => $value->TABLE_NAME,
            'display_name' => $aliasNameTable,
            'table_type' => 'TABLE',
            'has_primary_key' => 1,
            'is_transactional' => 1,
            'is_active' => 1,
        ]);
    }else {
        $cek = $cek->ID;
    }
    
    $results = DB::select("SELECT COLUMN_NAME FROM information_schema.columns where TABLE_SCHEMA='erp_ksps' TABLE_NAME = '$value->TABLE_NAME'");
    foreach ($results as $kunci => $values) {
        DB::table('metadata_columns')->insert([
            'table_id' => $cek,
            'column_name' => $value->COLUMN_NAME,
            'display_name' => $aliasNameColumn,
            'ordinal_position' => $key,
            'data_type' => $value->DATA_TYPE,
            ''
        ]);
    }

    dd($aliasName);
}