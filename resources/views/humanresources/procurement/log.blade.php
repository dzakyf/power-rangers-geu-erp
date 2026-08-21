<?php
error_reporting(0);
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP   = Auth::user()->Location;
$Saya  = Auth::user()->username;
$ID    = $_GET['ID'] ?? $_GET['PR_No'] ?? '';

// ------------------------------------------------------------------
// Kolom yang akan ditampilkan di grid (dari purchase_request_log)
// Urutan dan header disesuaikan untuk tampilan milestone
// ------------------------------------------------------------------
$header = "No,Action Date,Action By,Action Status,PR No,Category,Sub Category,Supplies Type,Note,Reference";
$widths = "50,120,120,300,120,100,100,120,250,150";
$aligns = "center,center,center,left,center,center,center,center,left,left";
$sort   = "int,str,str,str,str,str,str,str,str,str";
$types  = "ro,ro,ro,ro,ro,ro,ro,ro,ed,ed";

$nomor = 1;
?>
<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">
    PURCHASE REQUEST LOG — MILESTONE APPROVAL
</div>

<div style="margin:6px 0;font-size:12px;color:#555;">
    PR No : <strong><?php echo htmlspecialchars($ID); ?></strong>
</div>

<div id="LogGrid" style="width:auto;height:480px;background-color:white;overflow:hidden;"></div>

<script>
    LogGrid = new dhtmlXGridObject('LogGrid');
    LogGrid.setImagePath('./dhtmlx/imgs/');
    LogGrid.setHeader('<?php echo $header; ?>');
    LogGrid.setInitWidths('<?php echo $widths; ?>');
    LogGrid.setColAlign('<?php echo $aligns; ?>');
    LogGrid.setColSorting('<?php echo $sort; ?>');
    LogGrid.setColTypes('<?php echo $types; ?>');
    LogGrid.enableMultiline(true);
    LogGrid.setSkin('dhx_skyblue');
    LogGrid.init();
    LogGrid.clearAndLoad('xml-hrd-procurement-log?ID=<?php echo urlencode($ID); ?>');
</script>
