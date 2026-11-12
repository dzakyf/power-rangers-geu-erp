@php
    $empNik = $nik ?? request()->input('ID');
    $empName = isset($employee) && $employee ? $employee->Name : $empNik;
    $empPos = isset($employee) && $employee ? $employee->Position : '-';
@endphp

<div class="card border-0 shadow-sm rounded-4 mb-4" id="generatorDetailCard">
    <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-primary">
                <i class="fe fe-FileText me-1"></i> Rincian Komponen Gaji &amp; Potongan Karyawan:
                <span class="text-dark">{{ $empName }}</span>
                <span class="badge bg-primary text-white ms-1">{{ $empNik }}</span>
                @if($empPos && $empPos !== '-')
                    <span class="badge bg-light text-secondary border ms-1">{{ $empPos }}</span>
                @endif
            </h6>
            <small class="text-muted">Daftar komponen aktif yang menjadi dasar perhitungan batch penggajian karyawan ini.</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 text-white" onclick="previewGeneratorSlipDetail('{{ $empNik }}')">
                <i class="fa fa-calculator me-1"></i> Simulasi Slip &amp; Presensi Cutoff
            </button>
            <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 text-dark" onclick="GroupPayroll_Lists.toExcel('{{ asset('export/generate.php') }}');">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2" onclick="Reload_GroupPayroll_Lists()" title="Refresh">
                <i class="fa fa-sync"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2" onclick="$('#divlistProjectOrg').empty();" title="Tutup Rincian">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-3">
        <div id="GroupPayroll_Lists" style="width:100%; height:220px; background-color:white; overflow:hidden; border: 1px solid #e9ecef; border-radius: 8px;"></div>

        <!-- Container Preview Slip & Metadata Perhitungan -->
        <div id="GeneratorSlipPreviewContainer" class="mt-3" style="display: none;"></div>
    </div>
</div>

<script>
    GroupPayroll_Lists = new dhtmlXGridObject('GroupPayroll_Lists');
    GroupPayroll_Lists.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    GroupPayroll_Lists.setHeader("No,ID,Nama Komponen,Fix Allowance,PPH,Kategori,Nominal (Rp),Catatan,Created By,Created Date");
    GroupPayroll_Lists.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,,,");
    GroupPayroll_Lists.setInitWidths("60,100,230,100,90,110,130,260,110,120");
    GroupPayroll_Lists.setColAlign("center,left,left,center,center,center,right,left,center,center");
    GroupPayroll_Lists.setColTypes("ro,ro,ro,ro,ro,ro,ron,ro,ro,ro");
    GroupPayroll_Lists.setColSorting("int,str,str,str,str,str,int,str,str,str");
    GroupPayroll_Lists.setEditable(false);
    GroupPayroll_Lists.setNumberFormat("0,000", 6, ".", ",");
    GroupPayroll_Lists.setSkin("dhx_skyblue");
    GroupPayroll_Lists.init();
    GroupPayroll_Lists.enableMultiline(true);

    Reload_GroupPayroll_Lists();

    function Reload_GroupPayroll_Lists() {
        GroupPayroll_Lists.clearAndLoad("{{ url('xml-payroll-employee-detail') }}?ID={{ urlencode($empNik) }}");
    }

    function previewGeneratorSlipDetail(nik) {
        var periodVal = $('#Salary_Period').val() || '{{ date('Y-m') }}';
        $("#GeneratorSlipPreviewContainer").show().html(
            '<div class="alert alert-info py-3 text-center mb-0 rounded-3">' +
            '<i class="fa fa-spinner fa-spin me-2"></i> Mengkalkulasi simulasi slip gaji, presensi (Alpha &amp; Terlambat), BPJS, dan PPh 21 TER periode <strong>' + periodVal + '</strong>...' +
            '</div>'
        );

        $.get("{{ url('api/payroll/employee-salary-detail') }}", { nik: nik, period: periodVal }, function(res) {
            if (res.success && res.data) {
                var d = res.data;
                var m = d.metadata || {};
                var att = m.attendance_metrics || {};
                var tax = m.tax_details || {};

                var html = '<div class="card border shadow-sm rounded-3 mb-0" style="background: #fcfcfd;">' +
                    '<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3">' +
                        '<h6 class="mb-0 fw-bold text-white"><i class="fa fa-file-text-o me-1"></i> Simulasi Perhitungan Slip Gaji: ' + d.name + ' (' + d.nik + ') — Periode ' + d.periode + '</h6>' +
                        '<button type="button" class="btn btn-sm btn-light py-0 px-2 rounded-pill" onclick="$(\'#GeneratorSlipPreviewContainer\').slideUp();">Tutup</button>' +
                    '</div>' +
                    '<div class="card-body p-3">' +
                        '<div class="row g-3">' +
                            '<div class="col-md-4">' +
                                '<div class="p-3 bg-white rounded-3 border h-100">' +
                                    '<h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="fa fa-calendar-check-o me-1 text-primary"></i> Rekap Presensi Cutoff</h6>' +
                                    '<small class="text-muted d-block mb-2">Siklus: <strong>' + (att.cutoff_label || '-') + '</strong></small>' +
                                    '<ul class="list-unstyled mb-0 small">' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Hari Kerja Terjadwal:</span><span class="fw-bold">' + (att.scheduled_days || 0) + ' hari</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Hadir Normal:</span><span class="text-success fw-bold">' + (att.present_days || 0) + ' hari</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Cuti / Izin Resmi (Paid):</span><span class="badge bg-primary text-white">' + (att.paid_leave_days || 0) + ' hari</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Tukar Shift / Koreksi:</span><span class="badge bg-secondary text-white">' + (att.change_shift_days || 0) + ' shift / ' + (att.corrections_count || 0) + ' koreksi</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span class="text-danger">Alpha / Unpaid / Incomplete:</span><span class="badge bg-danger text-white">' + (att.absent_days || 0) + ' hari</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span class="text-warning text-dark">Keterlambatan:</span><span class="badge bg-warning text-dark">' + (att.late_count || 0) + 'x (' + (att.late_minutes || 0) + ' mnt)</span></li>' +
                                        '<li class="d-flex justify-content-between py-1"><span>PTKP / Kategori TER:</span><span class="badge bg-info text-white">' + (m.ptkp_status || '-') + ' (TER ' + (tax.ter_category || '-') + ')</span></li>' +
                                    '</ul>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-4">' +
                                '<div class="p-3 bg-white rounded-3 border h-100">' +
                                    '<h6 class="fw-bold text-success border-bottom pb-2 mb-2"><i class="fa fa-plus-circle me-1"></i> Komponen Penerimaan</h6>' +
                                    '<ul class="list-unstyled mb-0 small">' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Gaji Pokok:</span><span class="fw-semibold">Rp ' + Number(d.pokok || 0).toLocaleString('id-ID') + '</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Total Tunjangan:</span><span class="fw-semibold">Rp ' + Number(d.tunjangan || 0).toLocaleString('id-ID') + '</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Upah Lembur:</span><span class="fw-semibold">Rp ' + Number(d.overtime || 0).toLocaleString('id-ID') + '</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Insentif KJK:</span><span class="fw-semibold">Rp ' + Number(d.kjk || 0).toLocaleString('id-ID') + '</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Tunjangan PPh (Gross-Up):</span><span class="fw-semibold text-info">Rp ' + Number(d.tunjangan_pph || 0).toLocaleString('id-ID') + '</span></li>' +
                                        '<li class="d-flex justify-content-between py-2 fw-bold text-dark border-top mt-2"><span>Total Bruto:</span><span>Rp ' + Number(Number(d.pokok || 0) + Number(d.tunjangan || 0) + Number(d.overtime || 0) + Number(d.kjk || 0) + Number(d.tunjangan_pph || 0)).toLocaleString('id-ID') + '</span></li>' +
                                    '</ul>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-4">' +
                                '<div class="p-3 bg-white rounded-3 border h-100">' +
                                    '<h6 class="fw-bold text-danger border-bottom pb-2 mb-2"><i class="fa fa-minus-circle me-1"></i> Rincian Potongan</h6>' +
                                    '<ul class="list-unstyled mb-0 small">' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Unpaid Leave (' + (att.absent_days || 0) + ' hr):</span><span class="text-danger">Rp ' + Number(d.unpaid_leave_amount || 0).toLocaleString('id-ID') + '</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>Denda Telat (' + (att.late_count || 0) + 'x):</span><span class="text-danger">Rp ' + Number(d.late_penalty_amount || 0).toLocaleString('id-ID') + '</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>BPJS TK (2%):</span><span class="text-danger">Rp ' + Number(d.bpjs_tk_amount || 0).toLocaleString('id-ID') + '</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>BPJS Kes (1%):</span><span class="text-danger">Rp ' + Number(d.bpjs_kes_amount || 0).toLocaleString('id-ID') + '</span></li>' +
                                        '<li class="d-flex justify-content-between py-1 border-bottom"><span>PPh 21 (' + (tax.rate_percent || 0) + '%):</span><span class="text-danger fw-bold">Rp ' + Number(d.pph || 0).toLocaleString('id-ID') + '</span></li>' +
                                        '<li class="d-flex justify-content-between py-2 fw-bold text-danger border-top mt-2"><span>Total Potongan:</span><span>Rp ' + Number(Number(d.deduction || 0) + Number(d.pph || 0)).toLocaleString('id-ID') + '</span></li>' +
                                    '</ul>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="alert alert-primary d-flex justify-content-between align-items-center mt-3 mb-0 py-2 px-3 rounded-3">' +
                            '<span class="fw-bold">TAKE HOME PAY (THP BERSIH):</span>' +
                            '<span class="fw-bold fs-5 text-primary">Rp ' + Number(d.thp || 0).toLocaleString('id-ID') + '</span>' +
                        '</div>' +
                    '</div>' +
                '</div>';

                $("#GeneratorSlipPreviewContainer").html(html);
            } else {
                $("#GeneratorSlipPreviewContainer").html('<div class="alert alert-warning py-2 text-center mb-0">' + (res.message || 'Gagal memuat simulasi slip gaji.') + '</div>');
            }
        }).fail(function() {
            $("#GeneratorSlipPreviewContainer").html('<div class="alert alert-danger py-2 text-center mb-0">Terjadi kesalahan saat menghubungi server.</div>');
        });
    }
</script>
