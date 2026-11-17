<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

$success_message = '';
$error_message = '';
$current_username = $_SESSION['username'] ?? $_SESSION['name'] ?? 'ADMIN';

// 2. PROSES SIMPAN KONFIRMASI BAST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_bast'])) {
    $po_no        = trim($_POST['po_no']);
    $surat_jalan  = trim($_POST['surat_jalan']);
    $tgl_terima   = trim($_POST['tgl_terima']);
    $kondisi      = trim($_POST['kondisi_barang']);
    $catatan_bast = trim($_POST['catatan_bast']);

    try {
        $pdo->beginTransaction();

        // Update status di tabel purchase_order menjadi COMPLETED / BAST RECEIVED
        $stmt_up = $pdo->prepare("UPDATE purchase_order SET Status = 'COMPLETED' WHERE PO_No = :po_no");
        $stmt_up->execute([':po_no' => $po_no]);

        // Simpan data konfirmasi BAST (Anda bisa membuat tabel baru misal: bast_receiving atau mencatatnya di log)
        // Contoh update atau insert log penerimaan
        $stmt_log = $pdo->prepare("INSERT INTO purchase_request_log 
            (PR_No, Status, Note, Action_By, Action_Date, Action_Status) 
            SELECT PR_No, 'COMPLETED', :note, :action_by, NOW(), 'BAST_CONFIRMED' 
            FROM purchase_order WHERE PO_No = :po_no LIMIT 1");
        $stmt_log->execute([
            ':note'      => "Barang diterima dengan kondisi: $kondisi. No. Surat Jalan: $surat_jalan. Catatan: $catatan_bast",
            ':action_by' => $current_username,
            ':po_no'     => $po_no
        ]);

        $pdo->commit();
        $success_message = "Konfirmasi BAST untuk PO " . $po_no . " berhasil disimpan!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Gagal menyimpan BAST: " . $e->getMessage();
    }
}

// 3. Filter & Pencarian Daftar PO
$search     = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

$query = "SELECT po.*, s.Company_Name FROM purchase_order po LEFT JOIN supplier s ON po.Supplier = s.Supplier_ID WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (po.PO_No LIKE :search OR po.PR_No LIKE :search OR s.Company_Name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND po.PO_Date BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date']   = $end_date;
}

$query .= " ORDER BY po.ID DESC";

try {
    $stmt_po = $pdo->prepare($query);
    $stmt_po->execute($params);
    $list_po_confirm = $stmt_po->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $list_po_confirm = [];
}
?>

<!-- Panggil Topbar & Sidebar -->
<?php include 'includes/topbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-file-contract text-success mr-2"></i>Confirm PO & BAST (Penerimaan Barang)</h5>
            <small class="text-muted">Konfirmasi kedatangan fisik barang dari Purchase Order (PO) dan terbitkan Berita Acara Serah Terima (BAST).</small>
        </div>
    </div>

    <!-- FILTER PENCARIAN -->
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
        <div class="card-body p-3">
            <form action="confirm-po" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Cari No. PO / PR / Vendor</label>
                        <input type="text" class="form-control form-control-sm" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Ketik kata kunci...">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Dari Tanggal PO</label>
                        <input type="date" class="form-control form-control-sm" name="start_date" value="<?= htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Sampai Tanggal PO</label>
                        <input type="date" class="form-control form-control-sm" name="end_date" value="<?= htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-2 d-flex">
                        <button type="submit" class="btn btn-success btn-sm btn-block font-weight-bold mr-1"><i class="fas fa-filter mr-1"></i> Filter</button>
                        <a href="confirm-po" class="btn btn-secondary btn-sm" title="Reset"><i class="fas fa-sync-alt"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DAFTAR KARTU PO -->
    <div class="row">
        <?php if (!empty($list_po_confirm)): ?>
            <?php foreach ($list_po_confirm as $po): ?>
                <?php 
                    // Ambil rincian item PO dari purchase_order_dtl
                    $stmt_dtl = $pdo->prepare("SELECT * FROM purchase_order_dtl WHERE PO_No = :pno");
                    $stmt_dtl->execute([':pno' => $po['PO_No']]);
                    $po_items = $stmt_dtl->fetchAll(PDO::FETCH_ASSOC);

                    $is_completed = (strcasecmp($po['Status'], 'COMPLETED') === 0);
                ?>
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0 p-3 h-100" style="border-radius: 12px; background: #fff;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-success" style="font-size: 14px;"><?= htmlspecialchars($po['PO_No']); ?></span>
                            <span class="badge <?= $is_completed ? 'badge-success' : 'badge-warning text-dark'; ?> px-2 py-1" style="font-size: 11px;">
                                <?= htmlspecialchars($po['Status']); ?>
                            </span>
                        </div>

                        <div class="mb-2 text-secondary" style="font-size: 12px;">
                            <div><i class="fas fa-file-alt mr-1 text-muted"></i> <strong>No. PR:</strong> <?= htmlspecialchars($po['PR_No']); ?></div>
                            <div><i class="fas fa-store mr-1 text-muted"></i> <strong>Vendor:</strong> <?= htmlspecialchars($po['Company_Name'] ?? $po['Supplier']); ?></div>
                            <div><i class="far fa-calendar-alt mr-1 text-muted"></i> <strong>Tgl PO:</strong> <?= date('d/m/Y', strtotime($po['PO_Date'])); ?></div>
                        </div>

                        <!-- Kotak Rincian Item -->
                        <div class="bg-light p-2 rounded mb-2" style="font-size: 12px; border-left: 3px solid #28a745;">
                            <div class="font-weight-bold text-dark mb-1">Rincian Barang Dipesan:</div>
                            <ul class="pl-3 mb-0 text-secondary">
                                <?php if (!empty($po_items)): ?>
                                    <?php foreach ($po_items as $it): ?>
                                        <li><?= htmlspecialchars($it['Product_Desc']); ?> (<?= $it['Qty']; ?> <?= htmlspecialchars($it['Unit']); ?>)</li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li>Tidak ada rincian item.</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                            <span class="font-weight-bold text-dark" style="font-size: 12px;">Total: Rp <?= number_format($po['Total'] ?? 0, 0, ',', '.'); ?></span>
                            
                            <?php if (!$is_completed): ?>
                                <button class="btn btn-sm btn-success px-3 py-1 font-weight-bold" data-toggle="modal" data-target="#modalBast<?= $po['ID']; ?>" style="font-size: 11px;">
                                    <i class="fas fa-check-circle mr-1"></i> Konfirmasi BAST
                                </button>
                            <?php else: ?>
                                <span class="text-success font-weight-bold" style="font-size: 11px;"><i class="fas fa-check-double mr-1"></i> BAST Selesai</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- MODAL KONFIRMASI BAST PER PO -->
                <div class="modal fade" id="modalBast<?= $po['ID']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-success text-white">
                                <h6 class="modal-title font-weight-bold"><i class="fas fa-truck mr-2"></i>Konfirmasi Penerimaan PO: <?= htmlspecialchars($po['PO_No']); ?></h6>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body bg-light">
                                <form action="confirm-po" method="POST">
                                    <input type="hidden" name="submit_bast" value="1">
                                    <input type="hidden" name="po_no" value="<?= htmlspecialchars($po['PO_No']); ?>">

                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-secondary">Nomor Surat Jalan (Vendor)</label>
                                        <input type="text" class="form-control form-control-sm" name="surat_jalan" placeholder="Contoh: SJ/2026/11/008" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-secondary">Tanggal Barang Diterima</label>
                                        <input type="date" class="form-control form-control-sm" name="tgl_terima" value="<?= date('Y-m-d'); ?>" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-secondary">Kondisi Fisik Barang</label>
                                        <select class="form-control form-control-sm" name="kondisi_barang" required>
                                            <option value="Baik & Lengkap">Baik, Lengkap, Sesuai Spesifikasi</option>
                                            <option value="Ada Catatan / Kurang">Ada Catatan / Qty Kurang / Sebagian Rusak</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-secondary">Catatan BAST / Keterangan</label>
                                        <textarea class="form-control form-control-sm" name="catatan_bast" rows="3" placeholder="Tuliskan catatan penerimaan atau kendala jika ada..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-block btn-sm font-weight-bold">
                                        <i class="fas fa-save mr-1"></i> Simpan BAST & Selesaikan PO
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card shadow-sm border-0 text-center py-5">
                    <p class="text-muted mb-0">Tidak ada data Purchase Order (PO) yang ditemukan pada periode ini.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/bottombar.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');
    const sidebar = document.getElementById('sidebarDrawer');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        if (sidebar) sidebar.classList.toggle('active');
        if (overlay) overlay.classList.toggle('active');
    }

    if(toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
    if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    if(overlay) overlay.addEventListener('click', toggleSidebar);

    <?php if (!empty($success_message)): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= addslashes($success_message); ?>', confirmButtonColor: '#28a745' }).then(() => { window.location.href = 'confirm-po'; });
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= addslashes($error_message); ?>', confirmButtonColor: '#d33' });
    <?php endif; ?>
</script>
</body>
</html>