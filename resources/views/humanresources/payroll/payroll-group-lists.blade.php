<div class="card border-0 shadow-sm rounded-4 mt-3">
	<div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
		<div>
			<h6 class="fw-bold mb-0 text-primary">
				<i class="fe fe-list me-1"></i> Daftar Komponen (Benefit &amp; Costs) Group: <span class="badge bg-primary text-white"><?php echo htmlspecialchars($_GET['ID'])?></span>
			</h6>
			<small class="text-muted">Komponen master yang otomatis diterapkan pada seluruh karyawan dalam kelompok ini.</small>
		</div>
		<div class="d-flex align-items-center gap-2">
			<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="$('#Payroll_GroupDiv').html('');">
				<i class="fe fe-x me-1"></i> Tutup
			</button>
		</div>
	</div>
	<div class="card-body p-3">
		<div id="GroupPayroll_Lists" style="width:100%;height:320px; background-color:white;overflow:hidden"></div>
		<div class="d-flex flex-wrap gap-2 mt-3">
			<button type="button" id="AddDataPayrollLists" class="btn btn-primary rounded-pill px-3 shadow-sm">
				<i class="fe fe-plus me-1"></i> Tambah Komponen
			</button>
			<button type="button" id="UpdateGroupPayroll_Lists" class="btn btn-success rounded-pill px-3 text-white shadow-sm">
				<i class="fe fe-save me-1"></i> Simpan Perubahan
			</button>
			<button type="button" id="HapusdataPayrollListsGroup" class="btn btn-outline-danger rounded-pill px-3">
				<i class="fe fe-trash-2 me-1"></i> Hapus Komponen
			</button>
			<button type="button" id="Refreshgrouplists" class="btn btn-outline-secondary rounded-pill px-3">
				<i class="fe fe-rotate-cw me-1"></i> Refresh
			</button>
			<button type="button" id="ekselPODListDet" class="btn btn-outline-warning rounded-pill px-3 text-dark" onclick="GroupPayroll_Lists.toExcel('{{ asset('export/generate.php') }}');">
				<i class="fe fe-file-text me-1"></i> Export Excel
			</button>
		</div>
	</div>
</div>

<div id="GroupPayroll_ListsDiv"></div>

<script>
GroupPayroll_Lists = new dhtmlXGridObject('GroupPayroll_Lists');
GroupPayroll_Lists.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
GroupPayroll_Lists.setHeader("No,Urutan,Name,Fix Allowance,PPH,Category,Amounts,Note,Created By,Created Date");
GroupPayroll_Lists.attachHeader("#rspan,#text_filter,#text_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#text_filter,#rspan,#rspan");
GroupPayroll_Lists.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,,");
GroupPayroll_Lists.setInitWidths("60,80,220,110,100,120,120,200,110,120");
GroupPayroll_Lists.setColAlign("center,center,left,center,center,center,right,left,center,center");
GroupPayroll_Lists.setColTypes("ro,ed,ro,coro,coro,coro,edn,ro,ro,ro");
GroupPayroll_Lists.setColSorting("int,int,str,str,str,str,int,str,str,str");
GroupPayroll_Lists.setNumberFormat("0,000", 6, ".", ",");
GroupPayroll_Lists.setEditable(true);
GroupPayroll_Lists.setSkin("dhx_skyblue");

PGroupPayroll_Lists = new dataProcessor("{{ url('payroll-group-lists-update') }}");
PGroupPayroll_Lists.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"ID": "<?php echo $_GET['ID'] ?>"}
});
PGroupPayroll_Lists.setUpdateMode("Off");
PGroupPayroll_Lists.init(GroupPayroll_Lists);
GroupPayroll_Lists.init();
GroupPayroll_Lists.enableMultiline(true);

Reload_GroupPayroll_Lists();
GroupPayroll_Lists.getCombo(3).put('1', 'YES');
GroupPayroll_Lists.getCombo(3).put('0', 'NO');
GroupPayroll_Lists.getCombo(4).put('1', 'YES');
GroupPayroll_Lists.getCombo(4).put('0', 'NO');
GroupPayroll_Lists.getCombo(5).put('BASIC', 'BASIC');
GroupPayroll_Lists.getCombo(5).put('ALLOWANCE', 'ALLOWANCE');
GroupPayroll_Lists.getCombo(5).put('DEDUCTION', 'DEDUCTION');

function Reload_GroupPayroll_Lists() {
    GroupPayroll_Lists.clearAndLoad("{{ url('xml-payroll-group-lists') }}?ID=<?php echo $_GET['ID']?>");
}

$("#UpdateGroupPayroll_Lists").click(function() {
    PGroupPayroll_Lists.sendData();
    alert("Perubahan komponen berhasil disimpan!");
    setTimeout(function() {
        Reload_GroupPayroll_Lists();
    }, 1500);
});

$("#HapusdataPayrollListsGroup").click(function() {
    if (confirm("Hapus komponen yang dipilih dari group ini?")) {
        GroupPayroll_Lists.deleteSelectedRows();
        PGroupPayroll_Lists.sendData();
        alert("Komponen berhasil dihapus!");
        Reload_GroupPayroll_Lists();
    }
});

$("#AddDataPayrollLists").click(function() {
    LoadM('GroupPayroll_ListsDiv', '{{ url('payroll-group-lists-add') }}?ID=<?php echo $_GET['ID']?>');
});

$("#Refreshgrouplists").click(function() {
    Reload_GroupPayroll_Lists();
});
</script>
