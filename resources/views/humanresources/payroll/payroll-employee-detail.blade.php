			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif); font-weight: bold; padding: 8px 12px; margin-bottom: 8px;">
				<i class="fa fa-money me-1"></i> KOMPONEN PENGGAJIAN KARYAWAN: <?php echo htmlspecialchars($_GET['ID']) ?>
			</div>
			<div id="GroupPayroll_Lists" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
			<div class="d-flex flex-wrap gap-2 mt-2">
				<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning" onclick="GroupPayroll_Lists.toExcel('{{ asset('export/generate.php') }}');" />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data" class="btn btn-info text-white" />
				<input type="button" id="HapusdataPayrollListsGroup" NAME="HapusdataPayrollListsGroup" value="Hapus Baris" class="btn btn-danger text-white" />
				<input type="button" id="UpdateGroupPayroll_Lists" NAME="UpdateGroupPayroll_Lists" value="Simpan Komponen" class="btn btn-primary" />
				<input type="button" id="AddDataPayrollLists" NAME="AddDataPayrollLists" value="Tambah Komponen" class="btn btn-secondary" />
				<button type="button" id="btnPreviewSlip" class="btn btn-success text-white" onclick="previewSlipDetail('<?php echo htmlspecialchars($_GET['ID']) ?>')">
					<i class="fa fa-calculator me-1"></i> Preview Rincian Slip & Presensi
				</button>
			</div>
		</div>

		<!-- Container Preview Slip & Metadata Perhitungan -->
		<div id="SlipPreviewContainer" class="mt-3" style="display: none;"></div>

		<div id="GroupPayroll_ListsDiv"></div>
	</div>

<script>
GroupPayroll_Lists = new dhtmlXGridObject('GroupPayroll_Lists');
GroupPayroll_Lists.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
GroupPayroll_Lists.setHeader("No,ID,Name,Fix Allowance,PPH,Category,Amounts,Note,Created By,Created Date");
GroupPayroll_Lists.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,,");
GroupPayroll_Lists.setInitWidths("60,100,220,100,100,110,120,250,110,110");
GroupPayroll_Lists.setColAlign("center,left,left,center,center,center,right,left,center,center");
GroupPayroll_Lists.setColTypes("ro,ro,ro,coro,coro,coro,edn,ro,ro,ro");
GroupPayroll_Lists.setColSorting("int,str,str,str,str,str,str,str,str,str");
GroupPayroll_Lists.setEditable(true);
GroupPayroll_Lists.setNumberFormat("0,000", 6, ".", ",");
GroupPayroll_Lists.setSkin("dhx_skyblue");

PGroupPayroll_Lists = new dataProcessor("{{ url('payroll-employee-detail-update') }}");
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

function Reload_GroupPayroll_Lists(){
    GroupPayroll_Lists.clearAndLoad("{{ url('xml-payroll-employee-detail') }}?ID=<?php echo $_GET['ID']?>");	
}

$("#UpdateGroupPayroll_Lists").click(function(){
    PGroupPayroll_Lists.sendData();
    alert("Data komponen berhasil disimpan!");
    setTimeout(function(){
        Reload_GroupPayroll_Lists();
    }, 1500);
});

$("#HapusdataPayrollListsGroup").click(function(){
    if (confirm("Hapus komponen terpilih?")) {
        GroupPayroll_Lists.deleteSelectedRows();
        PGroupPayroll_Lists.sendData();
        Reload_GroupPayroll_Lists();
    }
});

$("#AddDataPayrollLists").click(function(){
    LoadM('GroupPayroll_ListsDiv', '{{ url('payroll-group-lists-add') }}?ID=<?php echo $_GET['ID']?>');
});

$("#RefreshBM").click(function(){
    Reload_GroupPayroll_Lists();		
});

function previewSlipDetail(nik) {
    $("#SlipPreviewContainer").show().html('<div class="alert alert-info py-3 text-center"><i class="fa fa-spinner fa-spin me-2"></i> Mengkalkulasi data slip, presensi (Alpha, Terlambat), potongan & PPh 21 TER...</div>');

    $.get('{{ url('api/payroll/employee-salary-detail') }}?nik=' + nik, function(res) {
        if (res.success && res.data) {
            var d = res.data;
            var m = d.metadata || {};
            var att = m.attendance_metrics || {};
            var ded = m.deduction_breakdown || {};
            var tax = m.tax_details || {};

            var html = '<div class="card border-0 shadow rounded-4 mb-3" style="background: #fdfdfd;">' +
                '<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3 rounded-top-4">' +
                    '<h6 class="mb-0 fw-bold"><i class="fa fa-file-text me-1"></i> Rincian Slip & Perhitungan Gaji: ' + d.name + ' (' + d.nik + ')</h6>' +
                    '<button type="button" class="btn btn-sm btn-light py-0 px-2 rounded-pill" onclick="$(\'#SlipPreviewContainer\').slideUp();">Tutup</button>' +
                '</div>' +
                '<div class="card-body p-3">' +
                    '<div class="row g-3">' +
                        // Kolom 1: Profil & Presensi Cutoff
                        '<div class="col-md-4">' +
                            '<div class="p-3 bg-light rounded-3 border h-100">' +
                                '<h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="fa fa-calendar-check-o me-1"></i> Presensi (Siklus Cutoff)</h6>' +
                                '<small class="text-muted d-block mb-2">Rentang: <strong>' + (att.cutoff_label || '-') + '</strong></small>' +
                                '<ul class="list-unstyled mb-0 small">' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Hari Kerja Terjadwal:</span><span class="fw-bold">' + (att.scheduled_days || 0) + ' hari</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Hadir Normal:</span><span class="text-success fw-bold">' + (att.present_days || 0) + ' hari</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Cuti / Izin Resmi (Paid):</span><span class="badge bg-primary text-white">' + (att.paid_leave_days || 0) + ' hari</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Tukar Shift / Koreksi:</span><span class="badge bg-secondary text-white">' + (att.change_shift_days || 0) + ' shift / ' + (att.corrections_count || 0) + ' koreksi</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span class="text-danger">Alpha / Unpaid / Incomplete:</span><span class="badge bg-danger text-white">' + (att.absent_days || 0) + ' hari</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span class="text-warning text-dark">Keterlambatan:</span><span class="badge bg-warning text-dark">' + (att.late_count || 0) + 'x (' + (att.late_minutes || 0) + ' mnt)</span></li>' +
                                    '<li class="d-flex justify-content-between py-1"><span>PTKP / Kategori TER:</span><span class="badge bg-info text-white">' + (d.metadata.ptkp_status || '-') + ' (TER ' + (tax.ter_category || '-') + ')</span></li>' +
                                '</ul>' +
                            '</div>' +
                        '</div>' +
                        // Kolom 2: Pendapatan & Tunjangan
                        '<div class="col-md-4">' +
                            '<div class="p-3 bg-light rounded-3 border h-100">' +
                                '<h6 class="fw-bold text-success border-bottom pb-2 mb-2"><i class="fa fa-plus-circle me-1"></i> Komponen Penerimaan</h6>' +
                                '<ul class="list-unstyled mb-0 small">' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Gaji Pokok:</span><span class="fw-semibold">Rp ' + Number(d.pokok).toLocaleString('id-ID') + '</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Total Tunjangan:</span><span class="fw-semibold">Rp ' + Number(d.tunjangan).toLocaleString('id-ID') + '</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Upah Lembur:</span><span class="fw-semibold">Rp ' + Number(d.overtime).toLocaleString('id-ID') + '</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Insentif KJK:</span><span class="fw-semibold">Rp ' + Number(d.kjk || 0).toLocaleString('id-ID') + '</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Tunjangan PPh (Gross-Up):</span><span class="fw-semibold text-info">Rp ' + Number(d.tunjangan_pph).toLocaleString('id-ID') + '</span></li>' +
                                    '<li class="d-flex justify-content-between py-2 fw-bold text-dark border-top mt-2"><span>Total Pendapatan:</span><span>Rp ' + Number(Number(d.pokok) + Number(d.tunjangan) + Number(d.overtime) + Number(d.kjk || 0) + Number(d.tunjangan_pph)).toLocaleString('id-ID') + '</span></li>' +
                                '</ul>' +
                            '</div>' +
                        '</div>' +
                        // Kolom 3: Potongan (Deductions) & Pajak TER
                        '<div class="col-md-4">' +
                            '<div class="p-3 bg-light rounded-3 border h-100">' +
                                '<h6 class="fw-bold text-danger border-bottom pb-2 mb-2"><i class="fa fa-minus-circle me-1"></i> Rincian Potongan</h6>' +
                                '<ul class="list-unstyled mb-0 small">' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Unpaid Leave (' + (att.absent_days || 0) + ' hr):</span><span class="text-danger">Rp ' + Number(d.unpaid_leave_amount).toLocaleString('id-ID') + '</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>Denda Telat (' + (att.late_count || 0) + 'x):</span><span class="text-danger">Rp ' + Number(d.late_penalty_amount).toLocaleString('id-ID') + '</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>BPJS TK (2%):</span><span class="text-danger">Rp ' + Number(d.bpjs_tk_amount).toLocaleString('id-ID') + '</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>BPJS Kes (1% / Cap 120k):</span><span class="text-danger">Rp ' + Number(d.bpjs_kes_amount).toLocaleString('id-ID') + '</span></li>' +
                                    '<li class="d-flex justify-content-between py-1 border-bottom"><span>PPh 21 (' + (tax.rate_percent || 0) + '%):</span><span class="text-danger fw-bold">Rp ' + Number(d.pph).toLocaleString('id-ID') + '</span></li>' +
                                    '<li class="d-flex justify-content-between py-2 fw-bold text-danger border-top mt-2"><span>Total Potongan:</span><span>Rp ' + Number(Number(d.deduction) + Number(d.pph)).toLocaleString('id-ID') + '</span></li>' +
                                '</ul>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="alert alert-primary d-flex justify-content-between align-items-center mt-3 mb-0 py-2 px-3 rounded-3">' +
                        '<span class="fw-bold fs-6">TAKE HOME PAY (THP BERSIH):</span>' +
                        '<span class="fw-bold fs-5 text-primary">Rp ' + Number(d.thp).toLocaleString('id-ID') + '</span>' +
                    '</div>' +
                '</div>' +
            '</div>';

            $("#SlipPreviewContainer").html(html);
        } else {
            $("#SlipPreviewContainer").html('<div class="alert alert-warning py-2 text-center">' + (res.message || 'Gagal memuat slip gaji.') + '</div>');
        }
    }).fail(function() {
        $("#SlipPreviewContainer").html('<div class="alert alert-danger py-2 text-center">Terjadi kesalahan koneksi ke server.</div>');
    });
}
</script>
