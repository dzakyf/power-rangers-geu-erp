@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div class="mb-2 mb-md-0">
            <h4 class="content-title text-white fw-bold mb-1"
                style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fe fe-list me-2"></i>Master Komponen Gaji (Benefits &amp; Costs)
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
                        Master Benefits &amp; Costs
                    </li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ url('payroll-tax') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-percent me-1"></i> Master Tax &amp; TER
            </a>
            <a href="{{ url('payroll-employee') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-users me-1"></i> Setup Gaji Pegawai
            </a>
            <a href="{{ url('payroll-generator') }}" class="btn btn-sm rounded-pill shadow-sm"
                style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
                <i class="fe fe-play me-1"></i> Generator Payroll
            </a>
        </div>
    </div>
    <!-- End Main Content Header -->

    <!-- Stats Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-3 pb-2">
            <h5 class="fw-bold mb-0 text-primary">Master Benefit & Deduction Component</h5>
            <small class="text-muted">Kelola jenis komponen penghasilan, tunjangan, potongan, serta metode perhitungannya.</small>
        </div>
        <div class="card-body p-3">
            <div id="Payroll_Lists" style="width:100%; height:500px; background-color:white; overflow:hidden;"></div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" id="AddBM" class="btn btn-primary rounded-pill px-3">
                    <i class="fa fa-plus me-1"></i> Tambah Komponen
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
                <button type="button" id="ekselPODListDet" class="btn btn-warning rounded-pill px-3 text-dark" onclick="Payroll_Lists.toExcel('{{ asset('export/generate.php') }}');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
            </div>
        </div>
    </div>

    <div id="Payroll_ListsDiv"></div>

<script>
Payroll_Lists = new dhtmlXGridObject('Payroll_Lists');
Payroll_Lists.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
Payroll_Lists.setHeader("No,No Urut,Name,Fix Allowance,WHT/PPH,Category,Calculation Method,Cost Value,Max Value,Amount Limitation,Formula Method,TaxPayer,Created By,Created Date,Last Modified By,Last Modified Date,Last Seen By, Last Seen Date,AP Salary Group");
Payroll_Lists.attachHeader("#rspan,#text_filter,#numeric_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#rspan,#text_filter,#rspan,#text_filter");
Payroll_Lists.setInitWidths("80,100,250,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
Payroll_Lists.setColAlign("center,center,left,center,left,center,left,center,center,center,center,center,center,center,center,center");
Payroll_Lists.setColTypes("ro,ed,ed,coro,coro,coro,coro,edn,edn,edn,ed,coro,ro,ro,ro,ro,ro,ro,combo");
Payroll_Lists.setColSorting("int,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
Payroll_Lists.setEditable(true);
Payroll_Lists.setNumberFormat("0,000.00", 7, ".", ",");
Payroll_Lists.setNumberFormat("0,000.00", 8, ".", ",");
Payroll_Lists.setNumberFormat("0,000", 9, ".", ",");
Payroll_Lists.setSkin("dhx_skyblue");

PPayroll_Lists = new dataProcessor("{{ url('payroll-lists-update') }}");
PPayroll_Lists.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    }
});
PPayroll_Lists.setUpdateMode("Off");
PPayroll_Lists.init(Payroll_Lists);
Payroll_Lists.init();

combocurr = Payroll_Lists.getColumnCombo(18);
combocurr.enableFilteringMode(true);
combocurr.load("{{ url('xml-payroll-ap-salary') }}");

Payroll_Lists.getCombo(3).put('1', 'YES');
Payroll_Lists.getCombo(3).put('0', 'NO');
Payroll_Lists.getCombo(4).put('1', 'YES');
Payroll_Lists.getCombo(4).put('0', 'NO');
Payroll_Lists.getCombo(5).put('BASIC', 'BASIC');
Payroll_Lists.getCombo(5).put('ALLOWANCE', 'ALLOWANCE');
Payroll_Lists.getCombo(5).put('DEDUCTION', 'DEDUCTION');
Payroll_Lists.getCombo(6).put('VALUES', 'VALUES');
Payroll_Lists.getCombo(6).put('PERCENT', 'PERCENT');
Payroll_Lists.getCombo(11).put('EMPLOYEE', 'EMPLOYEE');
Payroll_Lists.getCombo(11).put('COMPANY', 'COMPANY');

Reload_Payroll_Lists();

function Reload_Payroll_Lists() {
    Payroll_Lists.clearAndLoad("{{ url('xml-payroll-lists') }}");	
}

$("#SaveBM").click(function(){
    PPayroll_Lists.sendData();
    alert("Data berhasil disimpan!");
    setTimeout(function(){
        Reload_Payroll_Lists();
    }, 2000);
});

$("#DeleteBM").click(function(){
    if (confirm("Yakin ingin menghapus baris yang dipilih?")) {
        Payroll_Lists.deleteSelectedRows();
        PPayroll_Lists.sendData();
        alert("Data berhasil dihapus!");
        Reload_Payroll_Lists();
    }
});

$("#AddBM").click(function(){
    var idbi = Payroll_Lists.uid();
    Payroll_Lists.addRow(idbi, [Payroll_Lists.getRowsNum() + 1, '', '', '', '', '', 'VALUES', '0', '0', '0'], 0);
    Payroll_Lists.selectRow(0);
});

$("#RefreshBM").click(function(){
    Reload_Payroll_Lists();		
});
</script>
@endsection
