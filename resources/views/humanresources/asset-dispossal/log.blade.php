<?php
error_reporting(0);
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;
$ID  = $_GET['Disposal_No'] ?? $_GET['ID'] ?? '';

// Log dari asset_disposal — diurutkan terbaru di atas
$logs = DB::table('asset_disposal_log')
    ->where('Disposal_No', $ID)
    ->orderBy('ID', 'DESC')
    ->get();
?>

<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">
    ASSET DISPOSAL LOG — PEMUSNAHAN ASET
</div>

<div style="margin:6px 0;font-size:12px;color:#555;">
    Disposal No : <strong><?php echo htmlspecialchars($ID); ?></strong>
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
  background-color: #c0392b;
  color: #fff;
  padding: 6px 8px;
  border: 1px solid #c0392b;
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
  background-color: #fdf2f2;
}
.log-table tbody tr:nth-child(odd) {
  background-color: #fff;
}
.log-table tbody tr:hover {
  background-color: #fae5e5;
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
    <div class="log-empty">No disposal log entries found for this Disposal No.</div>
<?php else: ?>
<table class="log-table">
  <thead>
    <tr>
      <th>No</th>
      <th>Action By</th>
      <th>Action Date</th>
      <th>Action Status</th>
      <th>Disposal Date</th>
      <th>Status</th>
      <th>User Name</th>
      <th>User Position</th>
      <th>Asset Name</th>
      <th>Asset Serial No</th>
      <th>Asset Condition</th>
      <th>Asset Location</th>
      <th>Disposal Reason</th>
      <th>Disposal Actual Date</th>
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
      <td><?php echo htmlspecialchars($log->Disposal_Date); ?></td>
      <td><?php echo htmlspecialchars($log->Status); ?></td>
      <td><?php echo htmlspecialchars($log->User_Name); ?></td>
      <td><?php echo htmlspecialchars($log->User_Position); ?></td>
      <td><?php echo htmlspecialchars($log->Asset_Name); ?></td>
      <td><?php echo htmlspecialchars($log->Asset_Serial_No); ?></td>
      <td><?php echo htmlspecialchars($log->Asset_Condition); ?></td>
      <td><?php echo htmlspecialchars($log->Asset_Location); ?></td>
      <td><?php echo htmlspecialchars($log->Disposal_Reason); ?></td>
      <td><?php echo htmlspecialchars($log->Disposal_Actual_Date); ?></td>
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
