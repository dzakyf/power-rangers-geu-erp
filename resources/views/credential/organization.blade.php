<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Manage</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Organization Struktur</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Lists</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="CredentialList" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
        <input type="button" id="Add" NAME="Add" value="New" class="btn btn-primary mt-2 mr-2" />
        <input type="button" id="SaveGl" NAME="SaveGl" value="Save Data" class="btn btn-success mt-2 mr-2" />
        <input type="button" id="Canceled" NAME="Canceled" value="Delete" onclick="delsJurnal()" class="btn btn-danger  mt-2 mr-2" />
			<input type="button" id="ekselCredentialListDet" name="ekselCredentialListDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="CredentialList.toExcel('./export/generate.php');"  />
		</div>

	</div>	
	
<script>


CredentialList = new dhtmlXGridObject('CredentialList');
CredentialList.setImagePath("./dhtmlx/imgs/");
CredentialList.setHeader("No,NIK,Nama,1,2,3,4,5,6,7,Department,Dept Code,Email,Alert,Divisi");
CredentialList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#combo_filter");
CredentialList.setInitWidths("60,120,100,60,60,60,60,60,60,60,100,100,120,120,120,120");
CredentialList.setColAlign("center,left,left,left,left,left,left,left,left,left,left,center,left,left,left");
CredentialList.setColSorting("int,str,str,int,int,int,int,int,int,int,str,str,str,str");
CredentialList.setColTypes("ro,ed,ed,edn,edn,edn,edn,edn,edn,edn,combo,ed,ed,ed,combo");
//CredentialList.setColValidators("null,NotEmpty,ValidAplhaNumeric,ValidBoolean,ValidBoolean,ValidBoolean,ValidBoolean,ValidBoolean,ValidBoolean,ValidBoolean,NotEmpty,ValidEmail,ValidBoolean,null"); 
 
PCTRX = new dataProcessor("credential-organization-update");
PCTRX.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },

});

//PCTRX.setVerificator(1,dhtmlxValidation.NotEmpty);

PCTRX.setUpdateMode("Off");
PCTRX.init(CredentialList);
CredentialList.setSkin("dhx_skyblue");
CredentialList.init();
combocurr = CredentialList.getColumnCombo(10);
combocurr.enableFilteringMode(true);
combocurr.load("xml-finance-department");

combocurr1 = CredentialList.getColumnCombo(14);
combocurr1.enableFilteringMode(true);
combocurr1.load("xml-finance-department");
refreshdata();

function refreshdata(){
	CredentialList.clearAndLoad('xml-credential-organization');

}
$('#Add').click(function() {
    var idbi = CredentialList.uid();
    CredentialList.addRow(idbi, [CredentialList.getRowsNum() + 1, '','', '0', '0','0','1','0','0','0','','', '0', '0'], 0);
    CredentialList.selectRow(0);
});

$("#SaveGl").click(function() {
			PCTRX.sendData();
			CredentialList.refresh();
			alert("Item was Saved!");

		});

function delsJurnal() {
    dhtmlx.confirm('Remove this Item?', 'Organization', function(r) {

        if (r == true) {
            CredentialList.deleteSelectedRows();
            PCTRX.sendData();
            alert('Data Successfully Deleted!');
            setTimeout(function() {
                    refreshdata();
            }, 1000);
        }
    })
}

</script>
@endsection