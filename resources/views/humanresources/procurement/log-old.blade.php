<?php
error_reporting(0);
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP  = Auth::user()->Location;
$Saya = Auth::user()->username;
$ID   = $_GET['ID'] ?? $_GET['PR_No'] ?? '';

// Ambil log milestone dari purchase_request_log (urutan: terbaru di atas)
$logs = DB::table('purchase_request_log')
    ->where('PR_No', $ID)
    ->orderBy('ID', 'DESC')
    ->get();
?>

<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">
    PURCHASE REQUEST LOG — MILESTONE APPROVAL
</div>

<div style="margin:6px 0;font-size:12px;color:#555;">
    PR No : <strong><?php echo htmlspecialchars($ID); ?></strong>
</div>

<style>
.pr-timeline {
  position: relative;
  margin-top: 14px;
  padding-bottom: 14px;
}
.pr-timeline__line {
  position: absolute;
  right: 0;
  top: 8px;
  bottom: 0;
  width: 3px;
  background: #cfd8dc;
}
.pr-timeline__card {
  position: relative;
  margin-bottom: 18px;
  padding: 12px 16px 12px 16px;
  border-radius: 8px;
  background-color: #ffffff;
  border: 1px solid #e0e0e0;
  box-shadow: 0 2px 6px rgba(0,0,0,0.06);
  min-height: 72px;
}
.pr-timeline__card:first-child {
  margin-top: 0;
}
.pr-timeline__card .pr-timeline__node {
  position: absolute;
  right: -8px;
  top: 16px;
  width: 15px;
  height: 15px;
  border-radius: 50%;
  border: 3px solid #ffffff;
  background: #90a4ae;
  box-shadow: 0 0 0 1px #b0bec5;
}
.pr-timeline__card--first .pr-timeline__node {
  background: #f5a623;
  border-color: #f5a623;
  box-shadow: 0 0 0 2px rgba(245,166,35,0.22);
}
.pr-timeline__date {
  font-size: 12px;
  font-weight: bold;
  color: #444;
  margin-bottom: 5px;
}
.pr-timeline__card--first {
  background: #f5a623;
  border-color: #e09210;
  color: #222;
}
.pr-timeline__card--first .pr-timeline__date {
  color: #3a2a00;
}
.pr-timeline__title {
  font-size: 15px;
  font-weight: bold;
  margin-bottom: 7px;
}
.pr-timeline__meta {
  font-size: 12px;
  line-height: 1.65;
}
.pr-timeline__meta > div {
  margin-bottom: 2px;
}
.pr-timeline__meta strong {
  color: #555;
}
.pr-timeline--empty {
  color: #888;
  font-style: italic;
  margin-top: 20px;
  padding: 8px 12px;
  background: #fafafa;
  border-radius: 6px;
  border: 1px dashed #d0d0d0;
}
</style>

<div class="pr-timeline">
  <div class="pr-timeline__line"></div>

  <?php if ($logs->isEmpty()): ?>
    <div class="pr-timeline--empty">No log entries found for this PR.</div>
  <?php else: ?>
    <?php $i = 0; foreach ($logs as $log): ?>
      <?php
        $is_first = ($i === 0);
        $card_class = 'pr-timeline__card' . ($is_first ? ' pr-timeline__card--first' : '');
      ?>
      <div class="<?php echo $card_class; ?>">
        <div class="pr-timeline__node"></div>
        <div class="pr-timeline__date"><?php echo htmlspecialchars($log->Action_Date); ?></div>
        <div class="pr-timeline__title"><?php echo htmlspecialchars($log->Action_Status); ?></div>
        <div class="pr-timeline__meta">
          <?php if (!empty($log->PR_No)): ?>
            <div><strong>PO:</strong> <?php echo htmlspecialchars($log->PR_No); ?></div>
          <?php endif; ?>
          <?php if (!empty($log->Action_By)): ?>
            <div><strong>By:</strong> <?php echo htmlspecialchars($log->Action_By); ?></div>
          <?php endif; ?>
          <?php if (!empty($log->Category)): ?>
            <div><strong>Category:</strong> <?php echo htmlspecialchars($log->Category); ?></div>
          <?php endif; ?>
          <?php if (!empty($log->Sub_Category)): ?>
            <div><strong>Sub-Category:</strong> <?php echo htmlspecialchars($log->Sub_Category); ?></div>
          <?php endif; ?>
          <?php if (!empty($log->Supplies_Type)): ?>
            <div><strong>Supplies Type:</strong> <?php echo htmlspecialchars($log->Supplies_Type); ?></div>
          <?php endif; ?>
          <?php if (!empty($log->Note)): ?>
            <div><strong>Note:</strong> <?php echo htmlspecialchars($log->Note); ?></div>
          <?php endif; ?>
          <?php if (!empty($log->Reference)): ?>
            <div><strong>Reference:</strong> <?php echo htmlspecialchars($log->Reference); ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php $i++; endforeach; ?>
  <?php endif; ?>
</div>
