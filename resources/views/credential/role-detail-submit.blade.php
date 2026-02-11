<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Custom_Function;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$ars = substr($_POST['isian'],0,-1);

DB::update(DB::raw("UPDATE users SET Sub_Menu='$ars' WHERE id=$_POST[id]"));

echo "success";
