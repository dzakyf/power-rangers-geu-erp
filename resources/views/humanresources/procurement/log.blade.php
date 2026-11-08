<?php
error_reporting(0);
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP   = Auth::user()->Location ?? '';
$Saya  = Auth::user()->username ?? '';
$ID    = $_GET['ID'] ?? $_GET['PR_No'] ?? '';

$header = "No,Action Date,Action By,Action Status,PR No,Category,Sub Category,Supplies Type,Note,Reference";
$widths = "50,130,120,240,140,110,110,120,240,140";
$aligns = "center,center,center,left,center,center,center,center,left,left";
$sort   = "int,str,str,str,str,str,str,str,str,str";
$types  = "ro,ro,ro,ro,ro,ro,ro,ro,ro,ro";
?>

<div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <span class="badge bg-secondary p-2 me-2 rounded-circle">
                <i class="fa fa-history text-white"></i>
            </span>
            <div>
                <h5 class="card-title mb-0 font-weight-bold text-dark">Riwayat Approval &amp; Milestone PR</h5>
                <small class="text-muted">Nomor Dokumen PR: <strong class="text-primary"><?php echo htmlspecialchars($ID); ?></strong></small>
            </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="$('#PurchaseManagementDiv').html('')">
            <i class="fa fa-times me-1"></i> Tutup
        </button>
    </div>

    <div class="card-body p-3">
        <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center border-0 rounded-3">
            <i class="fa fa-info-circle me-2 fa-lg"></i>
            <span class="small">Tabel berikut menampilkan urutan log tindakan, tanggal aksi, status persetujuan, dan catatan dari pemohon maupun reviewer.</span>
        </div>

        <div class="border rounded-3 overflow-hidden bg-white shadow-xs">
            <div id="LogGrid" style="width:100%; height:460px; background-color:white; overflow:hidden;"></div>
        </div>
    </div>
</div>

<script>
    var LogGrid = new dhtmlXGridObject('LogGrid');
    LogGrid.setImagePath('./dhtmlx/imgs/');
    LogGrid.setHeader('<?php echo $header; ?>');
    LogGrid.setInitWidths('<?php echo $widths; ?>');
    LogGrid.setColAlign('<?php echo $aligns; ?>');
    LogGrid.setColSorting('<?php echo $sort; ?>');
    LogGrid.setColTypes('<?php echo $types; ?>');
    LogGrid.enableMultiline(true);
    LogGrid.setSkin('dhx_skyblue');
    LogGrid.enableRowsHover(true, 'grid_hover');
    LogGrid.init();
    LogGrid.clearAndLoad('xml-hrd-procurement-log?ID=<?php echo urlencode($ID); ?>');
    $.unblockUI();
</script>
