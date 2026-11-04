<style>
    /* Scope isolation untuk checkbox & elemen dhtmlXGrid agar tidak terpengaruh CSS global Adminor/Bootstrap */
    #WorkCalendarDetail img,
    .gridbox_dhx_skyblue img {
        width: auto !important;
        height: auto !important;
        max-width: none !important;
        border-radius: 0 !important;
        vertical-align: middle !important;
        display: inline !important;
        box-shadow: none !important;
    }

    #WorkCalendarDetail table.obj td {
        vertical-align: middle !important;
    }
</style>

<div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
    <div
        class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark text-white p-2">
                <i class="fa fa-calendar-week fs-14"></i>
            </span>
            <div>
                <h6 class="mb-0 fw-bold text-dark">Detail Jam Kerja Harian (Office Calendar)</h6>
                <small class="text-muted">Atur status hari kerja, jam mulai, jam selesai, dan waktu istirahat per
                    hari.</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-primary" id="btnSaveCalendarDetail"
                onclick="simpanCalendarDetail();" title="Simpan seluruh perubahan ke server">
                <i class="fa fa-floppy-disk me-1"></i> Simpan Perubahan
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reloadgroupdetail();"
                title="Muat ulang grid detail">
                <i class="fa fa-rotate me-1"></i> Refresh
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="$('#WorkCalendarDiv').html('');">
                <i class="fa fa-xmark me-1"></i> Tutup
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div id="WorkCalendarDetail" style="width:100%;height:320px;background-color:white;overflow:hidden"></div>
    </div>
    <div
        class="card-footer bg-light py-2 px-3 border-top d-flex flex-wrap align-items-center justify-content-between text-muted fs-12">
        <div>
            <i class="fa fa-circle-info text-info me-1"></i> Klik ganda pada sel untuk mengedit. Format waktu harus
            <strong>HH:MM</strong> (24 Jam).
        </div>
        <div id="detail-total-info" class="fw-semibold text-primary">
            Total Terjadwal: Memuat...
        </div>
    </div>
</div>

<script>
    var WorkCalendarDetail, PWorkCalendarDetail;

    WorkCalendarDetail = new dhtmlXGridObject('WorkCalendarDetail');
    WorkCalendarDetail.setImagePath("./dhtmlx/imgs/");
    WorkCalendarDetail.setHeader("No, Day of Week, Is Working Day?, Start (24H), End (24H), Break Minutes");
    WorkCalendarDetail.setInitWidths("70,180,140,160,160,160");
    WorkCalendarDetail.setColAlign("center,left,center,center,center,center");
    WorkCalendarDetail.setColTypes("ro,ro,ch,ed,ed,ed");
    WorkCalendarDetail.setColSorting("int,str,str,str,str,int");
    WorkCalendarDetail.setEditable(true);
    WorkCalendarDetail.setSkin("dhx_skyblue");

    PWorkCalendarDetail = new dataProcessor("master-work-calendar-detail-update");
    PWorkCalendarDetail.setTransactionMode({
        mode: "POST",
        total: true,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
    });
    PWorkCalendarDetail.setUpdateMode("Off");
    PWorkCalendarDetail.init(WorkCalendarDetail);
    WorkCalendarDetail.init();

    // Feedback saat pengiriman data selesai
    PWorkCalendarDetail.attachEvent("onFullSync", function() {
        let btn = $('#btnSaveCalendarDetail');
        btn.prop('disabled', false).html('<i class="fa fa-floppy-disk me-1"></i> Simpan Perubahan');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Tersimpan',
                text: 'Detail jadwal jam kerja harian berhasil disimpan.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert('Detail jadwal jam kerja harian berhasil disimpan.');
        }
        reloadgroupdetail();
    });

    function simpanCalendarDetail() {
        let totalHours = getTotalWeeklyHours();
        let masterId = WorkCalendar.getSelectedRowId();
        let weeklyHours = parseFloat(
            WorkCalendar.cells(masterId, 3).getValue()
        );

        if (totalHours > weeklyHours) {
            let msg = "Total alokasi jam kerja mingguan (" + totalHours.toFixed(1) + " jam) melebihi batas ketentuan " +
                weeklyHours + " jam. Silakan sesuaikan kembali sebelum menyimpan.";
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Jam Gagal',
                    text: msg
                });
            } else {
                alert(msg);
            }
            return false;
        }

        let btn = $('#btnSaveCalendarDetail');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

        // Pastikan seluruh baris tertandai updated agar dikirim oleh sendData()
        let count = WorkCalendarDetail.getRowsNum();
        for (let i = 0; i < count; i++) {
            let rowId = WorkCalendarDetail.getRowId(i);
            PWorkCalendarDetail.setUpdated(rowId, true);
        }

        PWorkCalendarDetail.sendData();
    }

    WorkCalendarDetail.attachEvent("onEditCell", function(stage, rId, cInd, nValue, oValue) {
        // Sebelum editor dibuka
        if (stage == 0 && (cInd == 3 || cInd == 4)) {
            let total = getTotalMinutes(rId);
            let masterId = WorkCalendar.getSelectedRowId();
            let weeklyHour = Number(
                WorkCalendar.cells(masterId, 3).getValue()
            );

            if (total >= weeklyHour * 60) {
                alert("Total weekly hours sudah terpenuhi (" + weeklyHour + " Jam).");
                return false;
            }
        }

        // Saat nilai baru diinput (selesai edit)
        if (stage == 2) {
            if (cInd == 3 || cInd == 4) {
                if (!/^\d{2}:\d{2}$/.test(nValue)) {
                    alert("Format waktu harus HH:MM (contoh 08:00, 17:00)");
                    return false;
                }

                if (cInd == 4) {
                    let start = WorkCalendarDetail.cells(rId, 3).getValue().split(":");
                    let end = nValue.split(":");

                    let startMin = Number(start[0]) * 60 + Number(start[1]);
                    let endMin = Number(end[0]) * 60 + Number(end[1]);

                    if (endMin < startMin) {
                        alert("Jam selesai (End Time) harus lebih besar daripada jam mulai (Start Time).");
                        return false;
                    }
                }
            }

            setTimeout(function() {
                let totalHours = getTotalWeeklyHours();
                let masterId = WorkCalendar.getSelectedRowId();
                let weeklyHours = parseFloat(
                    WorkCalendar.cells(masterId, 3).getValue()
                );

                updateDetailSummaryDisplay(totalHours, weeklyHours);

                if (totalHours > weeklyHours) {
                    alert("Total alokasi jam kerja mingguan (" + totalHours + " jam) melebihi batas " +
                        weeklyHours + " jam.");
                    WorkCalendarDetail.cells(rId, cInd).setValue(oValue);
                    totalHours = getTotalWeeklyHours();
                    updateDetailSummaryDisplay(totalHours, weeklyHours);
                }
            }, 0);
        }

        return true;
    });

    WorkCalendarDetail.attachEvent("onCheck", function(rId, cInd, state) {
        setTimeout(function() {
            let totalHours = getTotalWeeklyHours();
            let masterId = WorkCalendar.getSelectedRowId();
            let weeklyHours = parseFloat(
                WorkCalendar.cells(masterId, 3).getValue()
            );
            updateDetailSummaryDisplay(totalHours, weeklyHours);
        }, 0);
    });

    function updateDetailSummaryDisplay(totalHours, weeklyHours) {
        $('#detail-total-info').html('Total Jam Terjadwal: <strong>' + totalHours.toFixed(1) + '</strong> / ' +
            weeklyHours + ' Jam');
    }

    function getTotalMinutes(exceptRow) {
        let total = 0;
        WorkCalendarDetail.forEachRow(function(rid) {
            if (rid == exceptRow) return;
            if (!WorkCalendarDetail.cells(rid, 2).isChecked()) return;

            let start = WorkCalendarDetail.cells(rid, 3).getValue();
            let end = WorkCalendarDetail.cells(rid, 4).getValue();
            let brk = parseInt(WorkCalendarDetail.cells(rid, 5).getValue()) || 0;

            if (start && end) {
                let s = start.split(":");
                let e = end.split(":");
                let sm = parseInt(s[0]) * 60 + parseInt(s[1]);
                let em = parseInt(e[0]) * 60 + parseInt(e[1]);
                total += (em - sm - brk);
            }
        });
        return total;
    }

    function getTotalWeeklyHours() {
        let totalMinutes = 0;
        WorkCalendarDetail.forEachRow(function(rid) {
            if (WorkCalendarDetail.cells(rid, 2).isChecked()) {
                let start = WorkCalendarDetail.cells(rid, 3).getValue();
                let end = WorkCalendarDetail.cells(rid, 4).getValue();
                let breakMin = parseInt(WorkCalendarDetail.cells(rid, 5).getValue()) || 0;

                if (start && end) {
                    let s = start.split(":");
                    let e = end.split(":");
                    let startMin = parseInt(s[0]) * 60 + parseInt(s[1]);
                    let endMin = parseInt(e[0]) * 60 + parseInt(e[1]);
                    totalMinutes += (endMin - startMin - breakMin);
                }
            }
        });
        return totalMinutes / 60;
    }

    function reloadgroupdetail() {
        WorkCalendarDetail.clearAndLoad("xml-hrd-master-work-calendar-detail?task={{ $_GET['task'] ?? '' }}",
            function() {
                let totalHours = getTotalWeeklyHours();
                let masterId = WorkCalendar.getSelectedRowId();
                let weeklyHours = parseFloat(
                    WorkCalendar.cells(masterId, 3).getValue()
                );
                updateDetailSummaryDisplay(totalHours, weeklyHours);
            });
    }

    // Initial load
    reloadgroupdetail();
</script>
