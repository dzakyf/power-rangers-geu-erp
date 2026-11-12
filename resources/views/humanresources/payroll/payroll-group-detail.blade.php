<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$cek=DB::table("payrol_main_group_lists")->where("Main_ID",$_GET['ID'])->orderBy("Urutan","ASC")->get();
	$dx = "";
	$align = "";
	$ro = "";
	$width = "";
	$sort = "";
	$nox = 10;
	$dx2 ="";
	$numbring= "";
	foreach($cek as $data){
		
		$dx .=",$data->Name";
		$dx2 .=",#numeric_filter";
		$ro .=",edn";
		$sort .=",int";
		$width .=",100";
		$align .=",right";
		$numbring .="Employee_Lists.setNumberFormat(\"0,000\",$nox,\".\",\",\");\n";
		$nox++;
	}
	
	
	?>
<div class="card border-0 shadow-sm rounded-4 mt-3">
	<div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
		<div>
			<h6 class="fw-bold mb-0 text-primary">
				<i class="fe fe-users me-1"></i> Daftar Pegawai dalam Group: <span class="badge bg-primary text-white"><?php echo htmlspecialchars($_GET['ID'])?></span>
			</h6>
			<small class="text-muted">Kelola pegawai yang tergabung ke kelompok payroll ini serta nominal gaji spesifik tiap komponen.</small>
		</div>
		<div class="d-flex align-items-center gap-2">
			<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="$('#Payroll_GroupDiv').html('');">
				<i class="fe fe-x me-1"></i> Tutup
			</button>
		</div>
	</div>
	<div class="card-body p-3">
		<div id="Employee_Lists" style="width:100%;height:350px; background-color:white;overflow:hidden"></div>
		<div class="d-flex flex-wrap gap-2 mt-3">
			<button type="button" id="deletsaa" class="btn btn-primary rounded-pill px-3 shadow-sm" onclick="TambahGroupPayroll_Lists()">
				<i class="fe fe-user-plus me-1"></i> Tambah Pegawai ke Group
			</button>
			<button type="button" id="idgrupss" class="btn btn-success rounded-pill px-3 text-white shadow-sm" onclick="UpdateGroupPayroll_Lists()">
				<i class="fe fe-save me-1"></i> Simpan Nilai Komponen
			</button>
			<button type="button" id="delets" class="btn btn-outline-danger rounded-pill px-3" onclick="DelGroupPayroll_Lists()">
				<i class="fe fe-user-minus me-1"></i> Keluarkan Pegawai
			</button>
			<button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="refreshdatagroupdetail()">
				<i class="fe fe-rotate-cw me-1"></i> Refresh
			</button>
			<button type="button" id="ekselEmployee_ListsDet" class="btn btn-outline-warning rounded-pill px-3 text-dark" onclick="Employee_Lists.toExcel('{{ asset('export/generate.php') }}');">
				<i class="fe fe-file-text me-1"></i> Export Excel
			</button>
		</div>
	</div>
</div>

<div id="Div_Payroll_Group"></div>
<script>
function TambahGroupPayroll_Lists() {
    LoadM('Div_Payroll_Group', '{{ url('payroll-group-detail-add') }}?ID=<?php echo $_GET['ID']?>');
}

function UpdateGroupPayroll_Lists() {
    PEmployee_Lists.sendData();
    alert('Nilai komponen gaji pegawai berhasil disimpan!');
    setTimeout(function() {
        refreshdatagroupdetail();
    }, 1000);
}

Employee_Lists = new dhtmlXGridObject('Employee_Lists');
Employee_Lists.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
Employee_Lists.setHeader("No,NIK,Nama Karyawan,Gender,Jabatan,Work Area,Payment Method,No Rekening,Nama Bank,Gross Up<?php echo $dx?>");
Employee_Lists.attachHeader("#rspan,#text_filter,#text_filter,#combo_filter,#text_filter,#text_filter,#combo_filter,#text_filter,#text_filter,#combo_filter<?php echo $dx2?>");

Employee_Lists.setInitWidths("60,110,180,80,180,120,120,120,100,90<?php echo $width?>");
Employee_Lists.setColAlign("center,left,left,center,left,left,center,left,center,center<?php echo $align?>");
Employee_Lists.setColSorting("int,str,str,str,str,str,str,str,str,str<?php echo $sort?>");
Employee_Lists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,coro<?php echo $ro?>");
Employee_Lists.setEditable(true);
Employee_Lists.enableEditEvents(true, false, true);

PEmployee_Lists = new dataProcessor("{{ url('payroll-group-detail-update') }}");
PEmployee_Lists.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"ID": "<?php echo $_GET['ID'] ?>"}
});
<?php echo $numbring?>	
PEmployee_Lists.setTransactionMode("POST", true);
PEmployee_Lists.setUpdateMode("Off");
PEmployee_Lists.init(Employee_Lists);
Employee_Lists.setSkin("dhx_skyblue");
Employee_Lists.init();
Employee_Lists.getCombo(9).put("1", "YES");
Employee_Lists.getCombo(9).put("0", "NO");
refreshdatagroupdetail();

function refreshdatagroupdetail() {
    if (typeof $.blockUI === 'function') $.blockUI({ message: '<div class="p-2"><h6>Memuat data pegawai...</h6></div>' });
    Employee_Lists.clearAndLoad("{{ url('xml-payroll-group-detail') }}?ID=<?php echo $_GET['ID']?>", function() {
        if (typeof $.unblockUI === 'function') $.unblockUI();
    });
}

function DelGroupPayroll_Lists() {
    var selId = Employee_Lists.getSelectedId();
    if (!selId) {
        alert("Pilih baris pegawai yang ingin dikeluarkan dari group terlebih dahulu.");
        return;
    }
    if (confirm("Keluarkan pegawai ini dari Salary Group <?php echo htmlspecialchars($_GET['ID'])?>?")) {
        Employee_Lists.deleteSelectedRows();
        PEmployee_Lists.sendData();
        alert('Pegawai berhasil dikeluarkan dari group.');
        setTimeout(function() {
            refreshdatagroupdetail();
        }, 1000);
    }
}
</script>
