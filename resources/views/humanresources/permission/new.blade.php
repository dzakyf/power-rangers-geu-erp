@extends('layout.mainlayout')

@section('content-wrapper')
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Permission and Leave</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">New Request</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->
<?php

use Illuminate\Http\Request;
use Illuminate\Http\Fungsi;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

$Saya = Auth::user()->username;
$LSP = Auth::user()->Location;

$qx = DB::table("jns_izin")->get();
$optizin = "<select id='izin_jenis_id' onchange='loadkategori();'>";
foreach ($qx as $fm){
	
	$optizin .="<option value='$fm->izin_jenis_id'>$fm->izin_jenis_name</option>";
}
$optizin .="</select>";

$fm = DB::table("izin")->orderBy("izin_tgl","DESC")->first();
$skrg = date('ymdhis');
$Task_ID =  "IZIN/$LSP-$skrg/".strtoupper(substr(md5($Saya.rand(999,9999)),-5));

$thnini = date("Y");
$prevyear = date("Y",strtotime("-1 year"));
$nextyear = date("Y",strtotime("+1 year"));
$tgllibur = "";

$hrilibur = DB::select(DB::raw("SELECT * FROM libur WHERE libur_tgl like '$thnini%' or libur_tgl like '$prevyear%' or libur_tgl LIKE '$nextyear%'"));
foreach($hrilibur as  $dx){
	$tgllibur .= "$dx->libur_tgl,";
}
$listlibur = substr($tgllibur,0,-1);

?>	
	<!-- Stats Card -->
	<div class="row">
		
		<div id="divBLade">
			<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0" class="table table-hover text-vertical-middle">
				<input type="hidden" id="Task_ID" size="30" value="<?php echo $Task_ID ?>" readonly="readonly"/>
				<input type="hidden" id="izin_tgl_pengajuan" size="10" value="<?php echo date('Y-m-d')?>" readonly="readonly" disabled="disabled"/>	
				<input type="hidden" id="izin_status" size="10" value="3" readonly="readonly" disabled="disabled"/>	
				
				
			 <tr height="30">
				<td class="badan" width="200">Permission Date</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
				<input type="text" id="izin_tgl" size="10" value="<?php echo date('Y-m-d')?>" readonly="readonly"/> s.d <input type="text" id="izin_tgl2" size="10" tabindex="1" value="<?php echo date('Y-m-d')?>" readonly="readonly"/>			</td>
				<td class="badan" width="100">Permission Type</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
				<?php echo $optizin ?>
					</td>     
			</tr>
			<tr height="30">
				<td class="badan" width="200">Employee NIK</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
					<input type="text" id="NIK" size="20">
					
				</td>
				<td class="badan" width="100">Category</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
				
					<input type="text" id="Employee_Category" size="20" disabled="disabled"/>
				</td>
			</tr>
			<tr height="30">
				<td class="badan" width="200">Employee Name</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
					<input type="text" id="Employee_Name" size="25" disabled="disabled" >
					
				</td>
				<td class="badan" width="200">Leave Remain</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
				
					<input type="text" id="Sisa_Cuti" size="5" disabled="disabled"/>
				</td>
			</tr>
			<tr height="30">
				<td class="badan" width="200">Permission Category</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
				 <div id = "DivKategori">
				<select id="kat_izin_id">
				   		
				 </select>
					
					</td>
				<td class="badan" width="200">Permission Note</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
					<input type="hidden" id="File_Path" >

					<input type="text" id="izin_catatan"  tabindex="2" size="40"  tabindex='6'/>
				</td>
			</tr>
			<tr height="30">
				<td class="badan" width="200">Permission Location</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'>
				<input type="text" id="izin_ket_lain" tabindex="4" size="30"/>	
				   </td>
				<td class="badan" width="200">Time Info</td>
				<td class="badan" width="30">:</td>
				<td class="badan_kiri" width='250'><input type="text" id="Informasi_Waktu" tabindex="4" size="10"/>ex. 15:30<Br>
				(only for  early go home/late incoming)
				</td>
			</tr>
			
		 </table>
		</div>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">UPLOAD DOCUMENTS</div>
		<div id="vault" class="with-header" style='height:200px;margin:0px;width:100%;'></div>
			<input type="button" id="Processdata" value="Process Data!"/>


	
	</div>
<div id="dividload"></div>
	
<script>
var myCalendar;
myCalendar = new dhtmlXCalendarObject(["izin_tgl","izin_tgl2"]);
myCalendar.setDateFormat("%Y-%m-%d");
myCalendar.setHolidays("<?php echo $listlibur?>");

setTimeout(function(){
	
	loadkategori();

},1000);
function loadkategori(){
	pxid = $("#izin_jenis_id").val();

	if (pxid !=''){
		
		LoadM('DivKategori','load-kategori-izin?ID=' + pxid);
		
	}
	
}

var vault = new dhx.Vault("vault", {
                uploader: {
                    target: "upload-permission.php"
                },
				    filesLimit: 1

            });
			
vault.events.on("uploadComplete", function(obj) {
	$("#File_Path").val(obj[0].name);	
});
vault.events.on("uploadBegin", function(obj) {
	$("#File_Path").val("");
	
});	
vault.events.on("beforeAdd", function(fileItem) {
	if (vault.data.getLength() >= 1) {
		alert('Only can upload 1 file per checkpoint. create in a excel file if you want upload multiple!');
		return false;
	}
});
$("#NIK").coolautosuggest({
		url:"search-employee?chars=",
		showThumbnail:false,
		showDescription:true,
		idField:$("#NIK"),
		width:500,
		minChars:1,
		onSelected:function(result){
		  if(result!=null){
			  var xx = result.thumbnail;
			  var exp = xx.split("|");
			$("#NIK").val(result.id); 
			$("#Employee_Category").val(exp[0]); 
			$("#Sisa_Cuti").val(exp[1]); 
			$("#Employee_Name").val(result.data); 
		  }
		}
	});


function getAllValues() {
    var inputValues = [];
    $('#divBLade input,select,textarea').each(function() {
        var type = $(this).attr("type");
        if ((type == "checkbox" || type == "radio") && this.checked) {
            inputValues.push($(this).attr("id") + "=" + $(this).val());
        }else{
			if (type != "button" && type != "submit") {
				
            inputValues.push($(this).attr("id") + "=" + $(this).val());
			}
        }
    })
	
    return inputValues.join('&');
} 	

$("#Processdata").click(function() {
	dapat = getAllValues();
	$.ajax({
		 headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
		 },
    
		url : "permission-new-submit?_token={{ csrf_token() }}",
		type: "POST",
		data : dapat,
		success: function(data, textStatus, jqXHR)
		{
			if (data == 'success'){
				
				setTimeout(function(){
					alert('New Permission with Task '+ $("#Task_ID").val() + ' Submitted!');
					location.href='permission-new';
				},1000);			

			}else{
				alert(data);
			}
		},
		error: function (jqXHR, textStatus, errorThrown)
		{
		 alert( "error" );
	
		}
	});
				
});


</script>

@endsection
