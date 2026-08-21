<?php
error_reporting(0);
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;
$ID  = $_GET['Handover_No'] ?? $_GET['ID'] ?? '';

// Log dari asset_handover — diurutkan terbaru di atas
$logs = DB::table('asset_handover_log')
    ->where('Handover_No', $ID)
    ->orderBy('ID', 'DESC')
    ->get();
?>

<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">
    ASSET HANDOVER LOG — SERAH TERIMA ASET
</div>

<div style="margin:6px 0;font-size:12px;color:#555;">
    Handover No : <strong><?php echo htmlspecialchars($ID); ?></strong>
</div>

<style>
.log-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
  margin-top: 10px;
  background-color: #fff;
}
.log-table th {
  background-color: #8e44ad;
  color: #fff;
  padding: 6px 8px;
  border: 1px solid #8e44ad;
  text-align: left;
  font-weight: bold;
  white-space: nowrap;
}
.log-table td {
  padding: 5px 8px;
  border: 1px solid #ccc;
  vertical-align: top;
}
.log-table tbody tr:nth-child(even) {
  background-color: #f5eef8;
}
.log-table tbody tr:nth-child(odd) {
  background-color: #fff;
}
.log-table tbody tr:hover {
  background-color: #ecdfe5;
}
.log-table-wrap {
  max-width: 100%;
  overflow-x: auto;
}
.log-empty {
  color: #888;
  font-style: italic;
  margin-top: 18px;
  padding: 8px 12px;
  background: #fafafa;
  border-radius: 6px;
  border: 1px dashed #d0d0d0;
  font-size: 12px;
}
</style>

<div class="log-table-wrap">
<?php if ($logs->isEmpty()): ?>
    <div class="log-empty">No handover log entries found for this Handover No.</div>
<?php else: ?>
<table class="log-table">
  <thead>
    <tr>
      <th>No</th>
      <th>Action By</th>
      <th>Action Date</th>
      <th>Action Status</th>
      <th>Handover Date</th>
      <th>Status</th>
      <th>User Name</th>
      <th>User NIK</th>
      <th>PIC Name</th>
      <th>PIC Department</th>
      <th>PIC Phone</th>
      <th>Reference</th>
      <th>Remark</th>
      <th>Created By</th>
      <th>Created Date</th>
      <th>Updated By</th>
      <th>Updated Date</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; foreach ($logs as $log): ?>
    <tr>
      <td><?php echo $no++; ?></td>
      <td><?php echo htmlspecialchars($log->Action_By); ?></td>
      <td><?php echo htmlspecialchars($log->Action_Date); ?></td>
      <td><?php echo htmlspecialchars($log->Action_Status); ?></td>
      <td><?php echo htmlspecialchars($log->Handover_Date); ?></td>
      <td><?php echo htmlspecialchars($log->Status); ?></td>
      <td><?php echo htmlspecialchars($log->User_Name); ?></td>
      <td><?php echo htmlspecialchars($log->User_NIK); ?></td>
      <td><?php echo htmlspecialchars($log->PIC_Name); ?></td>
      <td><?php echo htmlspecialchars($log->PIC_Department); ?></td>
      <td><?php echo htmlspecialchars($log->PIC_Phone); ?></td>
      <td><?php echo htmlspecialchars($log->Reference); ?></td>
      <td><?php echo htmlspecialchars($log->Remark); ?></td>
      <td><?php echo htmlspecialchars($log->Created_By); ?></td>
      <td><?php echo htmlspecialchars($log->Created_Date); ?></td>
      <td><?php echo htmlspecialchars($log->Updated_By); ?></td>
      <td><?php echo htmlspecialchars($log->Updated_Date); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</div>
