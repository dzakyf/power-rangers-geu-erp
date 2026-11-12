@php
    $batchId = request()->input('ID');
@endphp
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-success">
                <i class="fe fe-check-circle me-1"></i> Rincian Karyawan pada Batch Payroll Approved:
                <span class="badge bg-success text-white ms-1">{{ $batchId }}</span>
            </h6>
            <small class="text-muted">Klik ganda (double-click) pada baris karyawan untuk mencetak atau mengirim Slip Gaji Digital.</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="ekselPODListDet" class="btn btn-sm btn-warning rounded-pill px-3 text-dark" onclick="PayrollDetail.toExcel('{{ asset('export/generate.php') }}');">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="$('#divlistProjectOrgx').empty();" title="Tutup">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-3">
        <div id="PayrollDetail" style="width:100%; height:320px; background-color:white; overflow:hidden; border: 1px solid #e2e8f0; border-radius: 8px;"></div>
        <div id="GroupPayroll_ListsDiv" class="mt-3"></div>
    </div>
</div>

<script>
    function PayrollDetailKlik() {
        var selId = PayrollDetail.getSelectedId();
        if (!selId) return true;
        var nik = PayrollDetail.cells(selId, 2).getValue();
        if (nik != '') {
            window.open('{{ url('print-salary') }}?Task_ID={{ urlencode($batchId) }}&NIK=' + encodeURIComponent(nik), '_blank');
        }
        return true;
    }

    PayrollDetail = new dhtmlXGridObject('PayrollDetail');
    PayrollDetail.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    PayrollDetail.setHeader("No,Name,NIK,Gender,Group Category,Salary Group,Position,Basic Salary,Allowance,Overtime,KJK,WHT Allowance,Deduction,PPH,THP");
    PayrollDetail.attachHeader("#rspan,#text_filter,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter");
    PayrollDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
    PayrollDetail.setInitWidths("65,170,105,80,110,130,130,115,115,110,100,115,115,110,125");
    PayrollDetail.setColAlign("center,left,center,center,center,center,left,right,right,right,right,right,right,right,right");
    PayrollDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron");
    PayrollDetail.setColSorting("int,str,str,str,str,str,str,int,int,int,int,int,int,int,int");
    PayrollDetail.setNumberFormat("0,000", 7, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 8, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 9, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 10, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 11, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 12, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 13, ".", ",");
    PayrollDetail.setNumberFormat("0,000", 14, ".", ",");
    PayrollDetail.attachEvent("onRowDblClicked", PayrollDetailKlik);
    PayrollDetail.setSkin("dhx_skyblue");
    PayrollDetail.setEditable(false);
    PayrollDetail.enableMultiline(true);
    PayrollDetail.init();
    PayrollDetail.clearAndLoad('{{ url('xml-payroll-approved-detail') }}?Task_ID={{ urlencode($batchId) }}');
</script>
