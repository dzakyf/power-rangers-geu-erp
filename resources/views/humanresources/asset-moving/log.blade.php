<?php
error_reporting(0);
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;
$ID  = $_GET['No_Trx'] ?? $_GET['ID'] ?? '';

// Log dari moving_asset — diurutkan terbaru di atas
$logs = DB::table('moving_asset')
    ->where('No_Trx', $ID)
    ->orderBy('ID', 'DESC')
    ->get();
?>

<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">
    ASSET MOVING LOG — PEMINDAHAN ASET
</div>

<div style="margin:6px 0;font-size:12px;color:#555;">
    Request No : <strong><?php echo htmlspecialchars($ID); ?></strong>
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
  background-color: #2980b9;
  color: #fff;
  padding: 6px 8px;
  border: 1px solid #2980b9;
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
  background-color: #eaf2f8;
}
.log-table tbody tr:nth-child(odd) {
  background-color: #fff;
}
.log-table tbody tr:hover {
  background-color: #dde5ef;
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
    <div class="log-empty">No asset moving log entries found for this Request No.</div>
<?php else: ?>
<table class="log-table">
  <thead>
    <tr>
      <th>No</th>
      <th>Action By</th>
      <th>Action Date</th>
      <th>Action Status</th>
      <th>DateTrx</th>
      <th>UserTrx</th>
      <th>User NIP</th>
      <th>Department</th>
      <th>Asset No</th>
      <th>Serial No</th>
      <th>Product Desc</th>
      <th>Asset Description</th>
      <th>Last Status</th>
      <th>Current Location</th>
      <th>New Location</th>
      <th>Moving Reason</th>
      <th>Moving Purpose</th>
      <th>Moving Date</th>
      <th>Recipient Name</th>
      <th>Recipient NIK</th>
      <th>Recipient Dept</th>
      <th>Recipient Position</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; foreach ($logs as $log): ?>
    <tr>
      <td><?php echo $no++; ?></td>
      <td><?php echo htmlspecialchars($log->Action_By); ?></td>
      <td><?php echo htmlspecialchars($log->Action_Date); ?></td>
      <td><?php echo htmlspecialchars($log->Action_Status); ?></td>
      <td><?php echo htmlspecialchars($log->DateTrx); ?></td>
      <td><?php echo htmlspecialchars($log->UserTrx); ?></td>
      <td><?php echo htmlspecialchars($log->User_NIP); ?></td>
      <td><?php echo htmlspecialchars($log->Department); ?></td>
      <td><?php echo htmlspecialchars($log->Asset_No); ?></td>
      <td><?php echo htmlspecialchars($log->SN); ?></td>
      <td><?php echo htmlspecialchars($log->Product_Description); ?></td>
      <td><?php echo htmlspecialchars($log->Asset_Description); ?></td>
      <td><?php echo htmlspecialchars($log->Last_Stat); ?></td>
      <td><?php echo htmlspecialchars($log->Current_Location); ?></td>
      <td><?php echo htmlspecialchars($log->New_Location); ?></td>
      <td><?php echo htmlspecialchars($log->Moving_Reason); ?></td>
      <td><?php echo htmlspecialchars($log->Moving_Purpose); ?></td>
      <td><?php echo htmlspecialchars($log->Moving_Date); ?></td>
      <td><?php echo htmlspecialchars($log->Receipent_Name); ?></td>
      <td><?php echo htmlspecialchars($log->Receipent_NIK); ?></td>
      <td><?php echo htmlspecialchars($log->Receipent_Department); ?></td>
      <td><?php echo htmlspecialchars($log->Receipent_Position); ?></td>
      <td><?php echo htmlspecialchars($log->Status); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</div>
