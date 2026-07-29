<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$getRecruit = DB::table('requisition')
	->select('Task_ID', 'Subject', 'Placement', 'Job_Position', 'Department', 'Priority', 'Allocation', 'Qty', 'Status', 'Requisition_Type', 'PO_No', 'PIC_Production', 'Work_Area', 'Work_Schedule', 'Start_Date', 'End_Date', 'Created_By')
	->where('Location', $LSP)
	->get();

?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Dashboard PTK</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Recruitment</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Dashboard PTK</span>
		</li>
	</ol>
</div>

<div class="card">
	<div class="card-body">
		<h4>Dashboard PTK</h4>
		<div class="row no-gutters">
			<div class="col-12">
				<div class="w-100" id="pivottrial"></div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/tips_data.min.js"></script>
<script>
	var derivers = $.pivotUtilities.derivers;
	let json = `<?= json_encode($getRecruit) ?>`;
	$('#pivottrial').pivotUI(JSON.parse(json));
</script>
@endsection
