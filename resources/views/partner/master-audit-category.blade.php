<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$pic = DB::table('users')->where('Location', $LSP)->where('Priv_ID', 'SALES')->get();

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Master</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">SOP Compliance</span>
		</li>
	</ol>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div id="MasterAuditCategory" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
                <div>
                    <input type="button" id="ekselMasterAuditCategoryDet" name="ekselMasterAuditCategoryDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="MasterAuditCategory.toExcel('./export/generate.php');" />
                    <input type="button" id="newMasterAuditCategory" name="new" value="New Category" class="btn btn-primary mt-2 mr-2" onclick="MasterAuditCategoryNew()" />
                    <input type="button" id="detailMasterAuditCategory" name="detailMasterAuditCategory" value="Detail Category" class="btn btn-info mt-2 mr-2" onclick="MasterAuditCategoryKlik()" />
            
                    <input type="file" id="csvFileInput" accept=".csv" style="display: none;">
                </div>
            </div>
        </div>
    </div>
</div>
<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script>
	let count = 2;
	$(document).on('click', '#minusParameter', function() {
		let data = $(this).data('id')
		console.log(data)
		if (data != count - 1) {
			dhtmlx.alert('Only the last parameter can be deleted!');
			return
		}

		let cekParamter = $('input[id="parameter' + data + '"]').val();
		let cekScore = $('input[id="score' + data + '"]').val();

		if (cekParamter != '' || cekScore != '') {
			dhtmlx.confirm('Parameter or Score is not empty, Are you sure want to delete this parameter?', 'Parameter', function(r) {
				if (r == true) {
					$('#divParameter' + data).remove();

					count--;
				}
			})
			return
		}
		
		$('#divParameter' + data).remove();

		count--;
	})
	function MasterAuditCategoryNew() {
		LoadM('divlistOpportunity', 'partner-master-audit-category-new');
		return true;
	}
	function MasterAuditCategoryKlik() {

		MasterAuditCategoryID = MasterAuditCategory.getSelectedId();
		$("#divlistOpportunity").show();
		if (MasterAuditCategoryID != '') {
			$("#divlistProjectList").hide();
			LoadM('divlistOpportunity', 'partner-master-audit-category-detail?ID=' + MasterAuditCategoryID);
		}
		return true;
	}
	MasterAuditCategory = new dhtmlXGridObject('MasterAuditCategory');
	MasterAuditCategory.setImagePath("./dhtmlx/imgs/");
	MasterAuditCategory.setHeader("No, Category, Description, Parameter List, Parameter Score, Status");
	MasterAuditCategory.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// MasterAuditCategory.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	MasterAuditCategory.setInitWidths("60,100,200,150,100,100");
	MasterAuditCategory.setColAlign("center,left,left,left,center,center");
	MasterAuditCategory.setColSorting("int,str,str,str,str,str");
	MasterAuditCategory.setColTypes("ron,ro,ro,ro,ro,ro");
	MasterAuditCategory.enableSmartRendering(true, 150);
    MasterAuditCategory.setDateFormat("%Y-%m-%d");
	MasterAuditCategory.attachEvent('onRowDblClicked', function(rId, cInd) {
		MasterAuditCategoryKlik();
	});
	MasterAuditCategory.setSkin("dhx_skyblue");
	MasterAuditCategory.init();
	refreshdata();

	function refreshdata() {
		MasterAuditCategory.clearAndLoad('xml-partner-master-audit-category');
	}

	function deleteRow() {
		dhtmlx.confirm('Delete this Row?', 'Opportunity', function(r) {

			if (r == true) {
				MasterAuditCategory.deleteSelectedRows();
				PMasterAuditCategory.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}

    function addRow()
    {
        let uid = MasterAuditCategory.uid();
        let num = MasterAuditCategory.getRowsNum() + 1;
        MasterAuditCategory.addRow(uid, [num,"{{ date('Y-m-d') }}",'0','','']);
        MasterAuditCategory.selectRow(uid);
    }

	function savePrice() {
		PMasterAuditCategory.sendData();
		refreshdata();
	}

</script>
@endsection