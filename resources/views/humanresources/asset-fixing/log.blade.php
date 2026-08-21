<?php
error_reporting(0);
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

$LSP = Auth::user()->Location;
$Saya = Auth::user()->username;
$ID  = $_GET['ID'] ?? $_GET['No_Trx'] ?? '';

// Log dari asset_fixing — diurutkan terbaru di atas
$logs = DB::table('asset_fixing_log')
    ->where('ID', $ID)
    ->orderBy('ID', 'DESC')
    ->get();
?>

<div class="JudulContent" style="color:#699;background-image:url(./hdr_back.gif);">
    ASSET FIXING LOG — PERBAIKAN ASET
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
  background-color: #27ae60;
  color: #fff;
  padding: 6px 8px;
  border: 1px solid #27ae60;
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
  background-color: #eefaf1;
}
.log-table tbody tr:nth-child(odd) {
  background-color: #fff;
}
.log-table tbody tr:hover {
  background-color: #dff3e6;
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
    <div class="log-empty">No asset fixing log entries found for this Request No.</div>
<?php else: ?>
<table class="log-table">
  <thead>
    <tr>
      <th>No</th>
      <th>Action By</th>
      <th>Action Date</th>
      <th>Action Status</th>
      <th>Tanggal Pengajuan</th>
      <th>Status</th>
      <th>Jenis Perbaikan</th>
      <th>Nama Pengaju</th>
      <th>Jabatan</th>
      <th>Nama Aset</th>
      <th>Nomor Seri</th>
      <th>Deskripsi Aset</th>
      <th>Kondisi Aset</th>
      <th>Lokasi Saat Ini</th>
      <th>Alasan Perkiraan</th>
      <th>Estimasi Biaya</th>
      <th>Vendor</th>
      <th>Approval User</th>
      <th>Approval Datetime</th>
      <th>Department</th>
      <th>User NIP</th>
      <th>User Trx</th>
      <th>Approval Note</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; foreach ($logs as $log): ?>
    <tr>
      <td><?php echo $no++; ?></td>
      <td><?php echo htmlspecialchars($log->Action_By); ?></td>
      <td><?php echo htmlspecialchars($log->Action_Date); ?></td>
      <td><?php echo htmlspecialchars($log->Action_Status); ?></td>
      <td><?php echo htmlspecialchars($log->Tanggal_Pengajuan); ?></td>
      <td><?php echo htmlspecialchars($log->Status); ?></td>
      <td><?php echo htmlspecialchars($log->Jenis_Perbaikan); ?></td>
      <td><?php echo htmlspecialchars($log->Nama_Pengaju); ?></td>
      <td><?php echo htmlspecialchars($log->Jabatan); ?></td>
      <td><?php echo htmlspecialchars($log->Nama_Aset); ?></td>
      <td><?php echo htmlspecialchars($log->Nomor_Seri); ?></td>
      <td><?php echo htmlspecialchars($log->Deskripsi_Aset); ?></td>
      <td><?php echo htmlspecialchars($log->Kondisi_Aset); ?></td>
      <td><?php echo htmlspecialchars($log->Lokasi_Saat_Ini); ?></td>
      <td><?php echo htmlspecialchars($log->Alasan_Perkiraan); ?></td>
      <td style="text-align:right;"><?php echo number_format($log->Estimasi_Biaya ?? 0, 0, ',', '.'); ?></td>
      <td><?php echo htmlspecialchars($log->Vendor_Yang_Digunakan); ?></td>
      <td><?php echo htmlspecialchars($log->Approval_User); ?></td>
      <td><?php echo htmlspecialchars($log->Approval_Datetime); ?></td>
      <td><?php echo htmlspecialchars($log->Department); ?></td>
      <td><?php echo htmlspecialchars($log->User_NIP); ?></td>
      <td><?php echo htmlspecialchars($log->User_Trx); ?></td>
      <td><?php echo htmlspecialchars($log->Approval_Note); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
</div>
