@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-file-text me-2"></i>Master Template Penggajian
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ url('payroll-tax') }}" style="color: rgba(255, 255, 255, 0.8) !important;">
                            Payroll
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page"
                        style="color: #ffffff !important; font-weight: 600;">
                        Master Template
                    </li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ url('payroll-tax') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-percent me-1"></i> Master Tax &amp; TER
            </a>
            <a href="{{ url('payroll-group') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-layers me-1"></i> Master Group
            </a>
            <a href="{{ url('payroll-employee') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-users me-1"></i> Setup Gaji Pegawai
            </a>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Stats Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-2">
            <h5 class="fw-bold mb-0 text-primary">Master Template Penggajian</h5>
            <small class="text-muted">Kelola struktur template perhitungan gaji standar karyawan dan komponen turunannya.</small>
        </div>
        <div class="card-body p-3">
            <div id="Payroll_Group" style="width:100%; height:320px; background-color:white; overflow:hidden;"></div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" id="AddBM" class="btn btn-primary rounded-pill px-3">
                    <i class="fa fa-plus me-1"></i> Tambah Template
                </button>
                <button type="button" id="SaveBM" class="btn btn-success rounded-pill px-4 text-white">
                    <i class="fa fa-save me-1"></i> Simpan Perubahan
                </button>
                <button type="button" id="DeleteBM" class="btn btn-danger rounded-pill px-3 text-white">
                    <i class="fa fa-trash me-1"></i> Hapus Baris
                </button>
                <button type="button" id="RefreshBM" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fa fa-sync me-1"></i> Refresh Data
                </button>
                <button type="button" id="SETLIST" class="btn btn-info rounded-pill px-3 text-white">
                    <i class="fa fa-sliders me-1"></i> Components
                </button>
                <button type="button" id="ekselPODListDet" class="btn btn-outline-success rounded-pill px-3" onclick="Payroll_Group.toExcel('{{ asset('export/generate.php') }}');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
            </div>
        </div>
    </div>

    <div id="Payroll_GroupDiv"></div>

<script>
Payroll_Group = new dhtmlXGridObject('Payroll_Group');
Payroll_Group.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
Payroll_Group.setHeader("No,ID,Group Name,Category,Use For,Reference,Created By,Created Date");
Payroll_Group.setInitWidths("80,100,250,120,120,140,120,140");
Payroll_Group.setColAlign("center,left,left,center,left,center,left,center");
Payroll_Group.setColTypes("ro,ro,ed,coro,coro,ed,ro,ro");
Payroll_Group.setColSorting("int,str,str,str,str,str,str,str");
Payroll_Group.setEditable(true);
Payroll_Group.setSkin("dhx_skyblue");

PPayroll_Group = new dataProcessor("{{ url('payroll-template-master-update') }}");
PPayroll_Group.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    }
});
PPayroll_Group.setUpdateMode("Off");
PPayroll_Group.init(Payroll_Group);
Payroll_Group.init();

Reload_Payroll_Group();

Payroll_Group.getCombo(3).put('SALARY', 'SALARY');
Payroll_Group.getCombo(3).put('BONUS', 'BONUS');
Payroll_Group.getCombo(4).put('ALL', 'ALL');
Payroll_Group.getCombo(4).put('INTERNAL', 'INTERNAL');
Payroll_Group.getCombo(4).put('OUTSOURCE', 'OUTSOURCE');

function Reload_Payroll_Group() {
    Payroll_Group.clearAndLoad("{{ url('xml-payroll-template-master') }}");	
}

$("#SaveBM").click(function(){
    PPayroll_Group.sendData();
    alert("Data berhasil disimpan!");
    setTimeout(function(){
        Reload_Payroll_Group();
    }, 2000);
});

$("#DeleteBM").click(function(){
    if (confirm("Yakin ingin menghapus baris yang dipilih?")) {
        Payroll_Group.deleteSelectedRows();
        PPayroll_Group.sendData();
        alert("Data berhasil dihapus!");
        Reload_Payroll_Group();
    }
});

$("#AddBM").click(function(){
    var idbi = Payroll_Group.uid();
    Payroll_Group.addRow(idbi, [Payroll_Group.getRowsNum() + 1, ''], 0);
    Payroll_Group.selectRow(0);
});

$("#RefreshBM").click(function(){
    Reload_Payroll_Group();		
});

$("#SETLIST").click(function(){
    var selId = Payroll_Group.getSelectedId();
    if (!selId) {
        alert("Pilih baris template terlebih dahulu!");
        return;
    }
    var salesListsId = Payroll_Group.cells(selId, 1).getValue();
    LoadM('Payroll_GroupDiv', '{{ url('payroll-template-master-lists') }}?ID=' + salesListsId);
});
</script>
@endsection
