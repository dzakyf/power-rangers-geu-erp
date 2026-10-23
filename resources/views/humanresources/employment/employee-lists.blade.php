<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;

$GridView = 'employee-lists';
$AllCols = employee_grid_columns();
$DefaultCols = employee_grid_default_columns();
$ActiveCols = $DefaultCols;
if (Schema::hasTable('user_grid_prefs')) {
	$SavedJson = DB::table('user_grid_prefs')->where('username', $Saya)->where('view', $GridView)->value('columns_json');
	$SavedCols = $SavedJson ? json_decode($SavedJson, true) : null;
	if (is_array($SavedCols) && count($SavedCols)) {
		$ValidCols = array_values(array_intersect($SavedCols, array_keys($AllCols)));
		if (count($ValidCols)) $ActiveCols = $ValidCols;
	}
}
$Meta = [];
foreach ($AllCols as $ck => $cm) {
	$Meta[$ck] = ['label' => $cm[0], 'width' => $cm[1], 'align' => $cm[2], 'filter' => $cm[3], 'sort' => $cm[4], 'type' => 'ro'];
}

?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Employment</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Internal Employees</span>
            </li>
        </ol>
    </div>
	<div class="row">
		@if(employee_grid_can_manage_columns())
        <div class="col-12">
            <div style="width:100%;border:1px solid #ccc;background:#fff;margin-bottom:10px;padding:10px;">
                <a href="javascript:void(0)" onclick="toggleEmpColPanel()" style="font-weight:bold"><span id="EmpColToggleIcon">&#9656;</span> Pengaturan Kolom (<span id="EmpColCount">{{ count($ActiveCols) }}</span> aktif)</a>
                <input type="text" id="EmpColSearch" placeholder="Cari kolom..." onkeyup="filterEmpCols()" onclick="expandEmpColPanel()" style="margin-left:10px;padding:2px 6px;width:200px;" />
                <span id="EmpColFound" style="margin-left:5px;color:#888;">{{ count($AllCols) }} kolom</span>
                <span id="EmpColMsg" style="margin-left:10px;color:green;"></span>
                <div id="EmpColPanel" style="margin-top:10px;display:none;">
                    @foreach($AllCols as $ck => $cm)
                    <div class="empColItem" style="float:left;width:220px;padding:2px 5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $cm[0] }}">
                        <label style="font-weight:normal;margin:0;"><input type="checkbox" class="empColCheck" value="{{ $ck }}" {{ in_array($ck, $ActiveCols) ? 'checked' : '' }}> {{ $cm[0] }}</label>
                    </div>
                    @endforeach
                    <div style="clear:both;"></div>
                    <div style="margin-top:10px;">
                        <input type="button" value="Simpan Pilihan" class="btn btn-success" onclick="saveEmployeeCols(false)" />
                        <input type="button" value="Reset Default" class="btn btn-warning" onclick="saveEmployeeCols(true)" />
                    </div>
                </div>
            </div>
        </div>
		@endif
		<div class="col-12 mb-2">
			<button class="btn btn-primary" onclick="toggleDivPivotTable();">Show/Hide Pivot Table</button>
		</div>
        <div class="col-12 mb-2" id="divPivotTable" style="display:block;">
            <div style="width:100%;border:1px solid #ccc;background:#fff;padding:10px;">
                <b>Tabel Pivot</b> <span style="color:#888;">(geser field baris / kolom / nilai sesuai kebutuhan)</span>
                <div id="EmployeePivotUI" style="margin-top:10px;overflow:auto;"></div>
            </div>
        </div>
        <div class="col-12">
            <div id="EmployeeLists" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
            <div><input type="button" id="ekselAttendantListDet" name="ekselAttendantListDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="EmployeeLists.toExcel('./export/generate.php');"  />
        </div>

			</div>
	</div>
				<div id="divlistProjectOrg"></div>

<script>

var EmpColMeta = {!! json_encode($Meta) !!};
var EmpActiveCols = {!! json_encode(array_values($ActiveCols)) !!};
var EmpDefaultCols = {!! json_encode(array_values($DefaultCols)) !!};
var EmployeeLists = null;

function toggleDivPivotTable() {
	$('#divPivotTable').toggle();
}

function expandEmpColPanel(){
	$("#EmpColPanel").show();
	$("#EmpColToggleIcon").html("&#9662;");
}

function toggleEmpColPanel(){
	$("#EmpColPanel").toggle();
	$("#EmpColToggleIcon").html($("#EmpColPanel").is(":visible") ? "&#9662;" : "&#9656;");
}

function filterEmpCols(){
	var q = $("#EmpColSearch").val().toLowerCase();
	if (q !== "") expandEmpColPanel();
	var n = 0;
	$(".empColItem").each(function(){
		var hit = $(this).text().toLowerCase().indexOf(q) !== -1;
		$(this).toggle(hit);
		if (hit) n++;
	});
	$("#EmpColFound").text(q === "" ? (EmpColTotal + " kolom") : (n + " cocok")); 
}

function updateEmpColCount(){
	$("#EmpColCount").text($(".empColCheck:checked").length);
}

function EmployeeListsKlik() {
	var rid = EmployeeLists.getSelectedId();
	var nma = EmployeeLists.getUserData(rid, "nama");
	if (rid != '' && rid != null){
		LoadM('divlistProjectOrg','employee-lists-detail?ID=' + rid + "&nama=" + encodeURI(nma));
	}
	return true;
}

function buildEmployeeGrid(keys){
	document.getElementById('EmployeeLists').innerHTML = '';
	EmployeeLists = new dhtmlXGridObject('EmployeeLists');
	EmployeeLists.setImagePath("./dhtmlx/imgs/");
	var header = ['No.'], fh = ['#rspan'], w = ['50'], al = ['center'], ty = ['ro'], so = ['int'];
	for (var i = 0; i < keys.length; i++){
		var m = EmpColMeta[keys[i]];
		if (!m) continue;
		header.push(m.label);
		fh.push(m.filter);
		w.push(m.width);
		al.push(m.align);
		ty.push(m.type);
		so.push(m.sort);
	}
	EmployeeLists.setHeader(header.join(','));
	EmployeeLists.attachHeader(fh.join(','));
	EmployeeLists.setInitWidths(w.join(','));
	EmployeeLists.setColAlign(al.join(','));
	EmployeeLists.setColTypes(ty.join(','));
	EmployeeLists.setColSorting(so.join(','));
	EmployeeLists.attachEvent("onRowDblClicked", EmployeeListsKlik);
	EmployeeLists.setSkin("dhx_skyblue");
	EmployeeLists.init();
	EmployeeLists.clearAndLoad('xml-employee-lists?cols=' + encodeURIComponent(keys.join(',')));
}

function saveEmployeeCols(reset){
	var keys = reset ? EmpDefaultCols.slice() : $(".empColCheck:checked").map(function(){ return this.value; }).get();
	if (!keys.length){
		alert('Pilih minimal 1 kolom.');
		return;
	}
	$("#EmpColMsg").text('Menyimpan...');
	$.post('employee-lists-columns-save', {_token: $("input[name=_token]").val(), cols: keys.join(',')}, function(res){
		if (res && res.status === 'ok'){
			$(".empColCheck").each(function(){ this.checked = (keys.indexOf(this.value) !== -1); });
			updateEmpColCount();
			buildEmployeeGrid(keys);
			loadEmployeePivotUI(keys);
			$("#EmpColMsg").text('Pilihan kolom tersimpan.');
		} else {
			$("#EmpColMsg").text('');
			alert('Gagal menyimpan: ' + ((res && res.message) ? res.message : 'unknown error'));
		}
	}, 'json').fail(function(){
		$("#EmpColMsg").text('');
		alert('Gagal menyimpan pilihan kolom.');
	});
}

var EmpColTotal = {!! count($AllCols) !!};
$(document).on("change", ".empColCheck", updateEmpColCount);
updateEmpColCount();
buildEmployeeGrid(EmpActiveCols);

</script>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js"></script>
<script>
function pivotAutoFields(data){
	if (!data || !data.length) return {rows: [], cols: []};
	var attrs = Object.keys(data[0]);
	var cat = [];
	for (var i = 0; i < attrs.length; i++){
		if (typeof data[0][attrs[i]] === "number") continue;
		var seen = {}, distinct = 0;
		for (var r = 0; r < data.length; r++){ var v = data[r][attrs[i]]; if (!seen.hasOwnProperty(v)){ seen[v] = 1; distinct++; } }
		if (distinct > 1 && distinct < data.length) cat.push(attrs[i]);
	}
	var pool = cat.length ? cat : attrs.slice();
	return {rows: pool.length ? [pool[0]] : [], cols: pool.length > 1 ? [pool[1]] : []};
}

function loadEmployeePivotUI(activeKeys){
	var pcols = (activeKeys && activeKeys.length) ? activeKeys.join(",") : "";
	// if (typeof $.pivotUI === "undefined"){
	// 	$("#EmployeePivotUI").html('<div style="padding:10px;color:red;">Pustaka pivot gagal dimuat (periksa koneksi CDN).</div>');
	// 	return;
	// }
	$("#EmployeePivotUI").html('<div style="padding:10px;">Memuat data...</div>');
	$.getJSON('xml-employee-pivot?cols=' + encodeURIComponent(pcols), function(data){
		$("#EmployeePivotUI").pivotUI(data, {
			rows: pivotAutoFields(data).rows,
			cols: pivotAutoFields(data).cols,
			rendererName: "Table",
			aggregatorName: "Count",
			renderers: $.pivotUtilities.renderers
		});
	}).fail(function(){
		$("#EmployeePivotUI").html('<div style="padding:10px;color:red;">Gagal memuat data pivot.</div>');
	});
}
loadEmployeePivotUI(EmpActiveCols);
</script>
@endpush
