<div class="card border-0 shadow-sm rounded-4 mt-3">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="bg-success text-white p-2 rounded-3 me-2">
                <i class="fa fa-list-check"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0">Rincian Kehadiran Harian (Task: {{ $_GET['ID'] ?? '' }})</h6>
                <small class="text-muted">Karyawan dapat memperbarui jam kehadiran atau shift kerja per tanggal</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" id="deletsaa" name="deletsaaa" onclick="SubmitGrid()" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa fa-floppy-disk me-1"></i> Simpan Perubahan
            </button>
            <button type="button" id="ekselAttendantListDet" name="ekselAttendantListDet" onclick="AttendantList.toExcel('./export/generate.php');" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa fa-file-excel me-1"></i> Export Excel
            </button>
        </div>
    </div>
    <div class="card-body p-3">
        <div id="AttendantList" style="width:100%;height:380px;background-color:white;border-radius:8px;overflow:hidden;border:1px solid #e9ecef;"></div>
    </div>
</div>

<script>
AttendantList = new dhtmlXGridObject('AttendantList');
AttendantList.setImagePath("./dhtmlx/imgs/");
AttendantList.setHeader("No,Tanggal,Hari,Kategori,Jam Masuk,Jam Pulang,Shift 1,Shift 2,Shift 3,Tercatat");
AttendantList.setInitWidths("60,110,100,280,110,110,90,90,90,*");
AttendantList.setColAlign("center,center,center,left,center,center,center,center,center,center");
AttendantList.setColSorting("int,str,str,str,str,str,str,str,str,str");
AttendantList.setColTypes("ro,ro,ro,ro,ed,ed,ra_str,ra_str,ra_str,ro");
AttendantList.enableSmartRendering(true, 150);
AttendantList.setSkin("dhx_skyblue");

PAttendantList = new dataProcessor("attendant-entry-lists-update?ID=<?php echo $_GET['ID'] ?? ''?>");
PAttendantList.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    }
});
PAttendantList.setUpdateMode("Off");
PAttendantList.init(AttendantList);

AttendantList.init();
AttendantList.clearAndLoad("xml-attendant-entry-lists?ID=<?php echo $_GET['ID'] ?? ''?>");

function SubmitGrid(){
    $.blockUI({ message: '<h6><i class="fa fa-spinner fa-spin me-2"></i>Menyimpan Data Kehadiran...</h6>' });
    PAttendantList.sendData();
    setTimeout(function(){
        $.unblockUI();
        alert("Data kehadiran berhasil disimpan.");
        AttendantList.clearAndLoad("xml-attendant-entry-lists?ID=<?php echo $_GET['ID'] ?? ''?>");
    }, 1500);
}
</script>
