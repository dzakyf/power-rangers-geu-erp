<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$nama =  Auth::user()->Name;


?>
@extends('layout.mainlayout-hrd')

@section('content-wrapper')
<!-- Main Content Header -->
<div class="main-content-header">

</div>
<!-- End Main Content Header -->

@endsection