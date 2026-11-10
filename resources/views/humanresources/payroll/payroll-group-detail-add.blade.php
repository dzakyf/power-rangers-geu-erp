<div class="card border-0 shadow-sm rounded-4 mt-3">
    <div
        class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-success">
                <i class="fe fe-user-plus me-1"></i> Masukkan Pegawai ke Salary Group: <span
                    class="badge bg-success text-white"><?php echo htmlspecialchars($_GET['ID']); ?></span>
            </h6>
            <small class="text-muted">Centang nama-nama pegawai yang belum memiliki Salary Group untuk dimasukkan ke
                group ini.</small>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill"
            onclick="$('#Div_Payroll_Group').html('');">
            <i class="fe fe-x me-1"></i> Batal
        </button>
    </div>
    <div class="card-body p-3">
        <div id="Employee_add" style="width:100%;height:320px; background-color:white;overflow:hidden"></div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" id="deletsaa" class="btn btn-success rounded-pill px-4 text-white shadow-sm"
                onclick="SubmitGrid()">
                <i class="fe fe-check me-1"></i> Simpan ke Group <?php echo htmlspecialchars($_GET['ID']); ?>
            </button>
            <button type="button" id="ekselEmployee_addDet" class="btn btn-outline-warning rounded-pill px-3 text-dark"
                onclick="Employee_add.toExcel('{{ asset('export/generate.php') }}');">
                <i class="fe fe-file-text me-1"></i> Export Excel
            </button>
        </div>
    </div>
</div>

<script>
    Employee_add = new dhtmlXGridObject('Employee_add');
    Employee_add.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    Employee_add.setHeader("Pilih,NIK,Nama Karyawan,Gender,Pendidikan,Jabatan,Work Area,Status");
    Employee_add.attachHeader(
        "#master_checkbox,#text_filter,#text_filter,#combo_filter,#combo_filter,#text_filter,#combo_filter,#combo_filter"
        );
    Employee_add.setInitWidths("60,120,200,80,140,200,120,100");
    Employee_add.setColAlign("center,left,left,center,left,left,left,center");
    Employee_add.setColSorting("int,str,str,str,str,str,str,str");
    Employee_add.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro");
    Employee_add.enableSmartRendering(true, 150);

    PEmployee_add = new dataProcessor("{{ url('payroll-group-detail-add-update') }}?ID=<?php echo $_GET['ID']; ?>");
    PEmployee_add.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });

    PEmployee_add.setTransactionMode("POST", true);
    PEmployee_add.setUpdateMode("Off");
    PEmployee_add.init(Employee_add);
    Employee_add.setSkin("dhx_skyblue");
    Employee_add.init();
    refreshdatas();

    function refreshdatas() {
        Employee_add.clearAndLoad("{{ url('xml-payroll-group-detail-add') }}?ID=<?php echo $_GET['ID']; ?>");
    }

    function SubmitGrid() {
        PEmployee_add.sendData();
        alert('Pegawai berhasil dimasukkan ke group!');
        setTimeout(function() {
            $("#Div_Payroll_Group").html('');
            refreshdatagroupdetail();
        }, 1500);
    }
</script>


</script>
