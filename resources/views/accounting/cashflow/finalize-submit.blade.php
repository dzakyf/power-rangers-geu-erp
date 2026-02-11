<?php
error_reporting(0);

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

DB::update(DB::raw("UPDATE Proyeksi set Status='FINALIZE' WHERE ID=$_GET[ID]"));
echo"<script>$.unblockUI();</script>";
echo "<script>alert('Proyeksi was Finalize!');location.href='finance-proyeksi-cashflow-management';</script>";

?>
