<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Training</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Approval</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">AUDIT</div>
	<div class="card">
		<div class="card-body">
            <div>
                Training Name <select name="training_no" id="training_no" onchange="refreshdata()">
                    @foreach ($trainings as $item)
                        <option value="{{ $item->training_no }}">{{ 'Target : ' . ucfirst($item->target) . ' || Nama Pelatihan: ' . $item->training_name }}</option>
                    @endforeach
                </select>
                {{-- <button class="btn btn-success" onclick="refreshdata()">Refresh</button> --}}
            </div>
			<div id="TrainingApproval" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselTrainingApprovalDet" name="ekselTrainingApprovalDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="TrainingApproval.toExcel('./export/generate.php');" />
				<input type="button" id="detailTrainingApproval" name="detailTrainingApproval" value="Buat Pelatihan" class="btn btn-info mt-2 mr-2" onclick="TrainingApprovalKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>


    $(document).on('change', '.material',function() {
        let value = $(this).val();
        let counter = $(this).data('counter');
		console.log(counter)
        $.ajax({
            url:"partner-training-get-speaker",
            type:"POST",
            data:{_token:"{{csrf_token()}}",value},
            success:function(res)
            {
                $('#speaker' + counter).find('option').not(':first').remove();
                for (let i = 0; i < res.data.length; i++) {
                    const ele = res.data[i];
                    $('#speaker'+counter).append($('<option />', {
                        value: ele.username,
                        text: ele.Name,
                    }));
                }
            }
        });
    })

    $(document).on('click', ".minus-material", function() {
        let getCount = $(this).data('counter');
        console.log(getCount,counter);
        if (counter-1 == getCount) {
            $('#row' + getCount).remove();
            counter--;
        }else {
            alert("Harus hapus dari yang paling bawah");
        }
    })

	function TrainingApprovalKlik() {
        
		let TrainingApprovalID = TrainingApproval.getChangedRows().split(',').filter(item => item !== '');
        let counter = 0;
        let ids = [];
        for (let i = 0; i < TrainingApprovalID.length; i++) {
            const ele = TrainingApprovalID[i];
            const check = parseInt(TrainingApproval.cells(ele, 1).getValue());
            if (check) {
                counter++;
            }
        }
		if (TrainingApprovalID.length > 0) {
			LoadM('divlistOpportunity', 'partner-training-approval-detail?Task=' + TrainingApprovalID + "&training_no=" + $('#training_no').val());
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	TrainingApproval = new dhtmlXGridObject('TrainingApproval');
	TrainingApproval.setImagePath("./dhtmlx/imgs/");
	TrainingApproval.setHeader("No,Approval,Training Name, Participant Name, Participant Position, Participant Department, Participant Email,Request Date, Request Fee, Keterangan Tambahan, Status, Waktu pembuatan");
	TrainingApproval.attachHeader("#rspan,#master_checkbox,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// TrainingApproval.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TrainingApproval.setInitWidths("60,80,100,100,100,100,100,150,200,auto,100,100");
	TrainingApproval.setColAlign("center,center,left,left,left,left,left,left,right,left,left");
	TrainingApproval.setColSorting("int,int,str,str,str,str,str,str,int,str,str,str");
	TrainingApproval.setColTypes("ron,ch,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro");
	TrainingApproval.enableSmartRendering(true, 150);
	TrainingApproval.setNumberFormat("0,000", 7, ".", ",");
	// TrainingApproval.attachEvent("onRowDblClicked", TrainingApprovalKlik);
	TrainingApproval.setSkin("dhx_skyblue");
	TrainingApproval.init();
	refreshdata();

	function refreshdata() {
		TrainingApproval.clearAndLoad('xml-partner-training-approval?training=' + $('#training_no').val());
	}
</script>
@endsection