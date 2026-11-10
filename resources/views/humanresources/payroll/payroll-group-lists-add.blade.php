<div class="card border-0 shadow-sm rounded-4 mt-3">
	<div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
		<div>
			<h6 class="fw-bold mb-0 text-success">
				<i class="fe fe-plus-circle me-1"></i> Tambah Komponen ke Group: <span class="badge bg-success text-white"><?php echo htmlspecialchars($_GET['ID'])?></span>
			</h6>
			<small class="text-muted">Centang komponen master yang ingin dimasukkan ke group ini lalu tekan tombol tambah.</small>
		</div>
		<button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="$('#GroupPayroll_ListsDiv').html('');">
			<i class="fe fe-x me-1"></i> Batal
		</button>
	</div>
	<div class="card-body p-3">
		<div id="ListMasterPayroll" style="width:100%;height:240px; background-color:white;overflow:hidden"></div>
		<div class="d-flex flex-wrap gap-2 mt-3">
			<button type="button" id="AddDataPayrollListsGroup" class="btn btn-success rounded-pill px-4 text-white shadow-sm">
				<i class="fe fe-check me-1"></i> Tambahkan ke Group <?php echo htmlspecialchars($_GET['ID'])?>
			</button>
		</div>
	</div>
</div>

<script>
ListMasterPayroll = new dhtmlXGridObject('ListMasterPayroll');
ListMasterPayroll.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
ListMasterPayroll.setHeader("Pilih,ID,Nama Komponen,Fix Allowance,PPH,Kategori,Created By,Created Date");
ListMasterPayroll.attachHeader("#master_checkbox,#text_filter,#text_filter,#combo_filter,#combo_filter,#combo_filter,#rspan,#rspan");
ListMasterPayroll.setInitWidths("60,110,240,110,100,120,110,120");
ListMasterPayroll.setColAlign("center,left,left,center,center,center,center,center");
ListMasterPayroll.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro");
ListMasterPayroll.setColSorting("int,str,str,str,str,str,str,str");
ListMasterPayroll.setEditable(true);
ListMasterPayroll.setSkin("dhx_skyblue");

PListMasterPayroll = new dataProcessor("{{ url('payroll-group-lists-add-update') }}");
PListMasterPayroll.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },
    payload: {"ID": "<?php echo $_GET['ID'] ?>"}
});
PListMasterPayroll.setUpdateMode("Off");
PListMasterPayroll.init(ListMasterPayroll);
ListMasterPayroll.init();
Reload_ListMasterPayroll();

function Reload_ListMasterPayroll() {
    ListMasterPayroll.clearAndLoad("{{ url('xml-payroll-group-lists-add') }}?ID=<?php echo $_GET['ID']?>");
}

$("#AddDataPayrollListsGroup").click(function() {
    PListMasterPayroll.sendData();
    alert("Komponen berhasil ditambahkan ke group!");
    setTimeout(function() {
        $("#GroupPayroll_ListsDiv").html('');
        Reload_GroupPayroll_Lists();
    }, 1500);
});
</script>




</script>
