<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

$success_message = '';
$error_message = '';

// 2. PROSES UPDATE STATUS APPROVAL & KOMENTAR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_approval'])) {
    $pr_id           = intval($_POST['pr_id']);
    $status_approval = trim($_POST['status_approval']);
    $komentar        = trim($_POST['komentar_approval']);
    $approver_name   = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Manager';

    try {
        $pdo->beginTransaction();

        // Update status approval pada tabel utama PR
        $stmt_up = $pdo->prepare("UPDATE purchase_requisitions SET status_approval = :status_approval WHERE id = :id AND tipe_pengadaan = 'Ops'");
        $stmt_up->execute([
            ':status_approval' => $status_approval,
            ':id'              => $pr_id
        ]);

        // Catat log approval ke catatan
        $log_catatan = "Approval oleh " . $approver_name . " [" . $status_approval . "]: " . $komentar;
        
        $stmt_log = $pdo->prepare("UPDATE purchase_requisitions SET catatan = CONCAT(COALESCE(catatan, ''), '\n- ', :log) WHERE id = :id");
        $stmt_log->execute([
            ':log' => $log_catatan,
            ':id'  => $pr_id
        ]);

        $pdo->commit();
        $success_message = "Status approval berhasil diperbarui menjadi " . $status_approval . "!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Gagal memperbarui approval: " . $e->getMessage();
    }
}

// 3. QUERY FETCH DATA PR OPS UNTUK APPROVAL
$search     = $_GET['search'] ?? '';
$status_flt = $_GET['status'] ?? '';

$query = "SELECT pr.*, 
          (SELECT SUM(total_harga) FROM purchase_requisition_items WHERE pr_id = pr.id) as grand_total 
          FROM purchase_requisitions pr 
          WHERE pr.tipe_pengadaan = 'Ops'";

$params = [];

if (!empty($search)) {
    $query .= " AND (pr.nomor_pr LIKE :search OR pr.kategori LIKE :search OR pr.pic LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($status_flt)) {
    $query .= " AND pr.status_approval = :status";
    $params[':status'] = $status_flt;
}

$query .= " ORDER BY pr.id DESC";

try {
    $stmt_list = $pdo->prepare($query);
    $stmt_list->execute($params);
    $list_pr = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $list_pr = [];
}
?>

    <!-- CSS Khusus Approval -->
    <style>
        .approval-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: transform 0.2s;
            margin-bottom: 12px;
            background: #fff;
        }
        .approval-card:hover {
            transform: translateY(-2px);
        }
        .item-list-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 12px;
            margin-top: 8px;
            margin-bottom: 8px;
            border-left: 3px solid #007bff;
        }
    </style>

    <!-- Panggil Topbar & Sidebar -->
    <?php include 'includes/topbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <!-- CONTAINER UTAMA APPROVAL PR OPS -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-check-circle text-success mr-2"></i>Approval PR - Ops</h5>
                <small class="text-muted">Persetujuan bertingkat dokumen pengadaan operasional.</small>
            </div>
        </div>

        <!-- FORM FILTER & PENCARIAN -->
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
            <div class="card-body p-3">
                <form action="approval-ops" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-secondary mb-1">Cari No. PR / Pemohon</label>
                            <input type="text" class="form-control form-control-sm" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Ketik nomor PR atau pemohon...">
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-secondary mb-1">Status Approval</label>
                            <select class="form-control form-control-sm" name="status">
                                <option value="">-- Semua Status --</option>
                                <option value="Pending" <?= ($status_flt === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?= ($status_flt === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                <option value="Hold" <?= ($status_flt === 'Hold') ? 'selected' : ''; ?>>Hold</option>
                                <option value="Cancel" <?= ($status_flt === 'Cancel') ? 'selected' : ''; ?>>Cancel</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex">
                            <button type="submit" class="btn btn-success btn-sm btn-block font-weight-bold mr-1"><i class="fas fa-filter mr-1"></i> Filter</button>
                            <a href="approval-ops" class="btn btn-secondary btn-sm" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- CARD VIEW DAFTAR APPROVAL PR OPS DENGAN RINCIAN ITEM -->
        <div class="row">
            <?php if (!empty($list_pr)): ?>
                <?php foreach ($list_pr as $row): ?>
                    <?php 
                        // Ambil rincian item untuk setiap PR
                        $stmt_items = $pdo->prepare("SELECT * FROM purchase_requisition_items WHERE pr_id = :pr_id");
                        $stmt_items->execute([':pr_id' => $row['id']]);
                        $pr_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card approval-card p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-primary" style="font-size: 14px;"><?= htmlspecialchars($row['nomor_pr']); ?></span>
                                <?php 
                                    $status = $row['status_approval'];
                                    if ($status === 'Approved') {
                                        echo '<span class="badge badge-success px-2 py-1" style="font-size: 11px;">Approved</span>';
                                    } elseif ($status === 'Pending') {
                                        echo '<span class="badge badge-warning text-dark px-2 py-1" style="font-size: 11px;">Pending</span>';
                                    } elseif ($status === 'Hold') {
                                        echo '<span class="badge badge-secondary px-2 py-1" style="font-size: 11px;">Hold</span>';
                                    } else {
                                        echo '<span class="badge badge-danger px-2 py-1" style="font-size: 11px;">Cancel</span>';
                                    }
                                ?>
                            </div>
                            
                            <div class="mb-2 text-secondary" style="font-size: 12px;">
                                <div><i class="fas fa-tag mr-1 text-muted"></i> <strong>Kategori:</strong> <?= htmlspecialchars($row['kategori']); ?></div>
                                <div><i class="far fa-calendar-alt mr-1 text-muted"></i> <strong>Tgl Pengajuan:</strong> <?= date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></div>
                            </div>

                            <!-- KOTAK RINCIAN ITEM / MATERIAL -->
                            <div class="item-list-box">
                                <div class="font-weight-bold text-dark mb-1"><i class="fas fa-list-ul mr-1 text-primary"></i> Rincian Barang:</div>
                                <ul class="pl-3 mb-0 text-secondary">
                                    <?php if (!empty($pr_items)): ?>
                                        <?php foreach ($pr_items as $it): ?>
                                            <li>
                                                <strong><?= htmlspecialchars($it['keterangan']); ?></strong> 
                                                (<?= $it['qty']; ?> <?= htmlspecialchars($it['satuan']); ?>) 
                                                - <span class="text-dark">Rp <?= number_format($it['total_harga'], 0, ',', '.'); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="text-italic">Tidak ada item tercatat.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 12px;">
                                <span class="font-weight-bold text-dark"><i class="fas fa-money-bill-wave mr-1 text-success"></i> Est. Grand Total:</span>
                                <span class="font-weight-bold text-primary" style="font-size: 13px;">Rp <?= number_format($row['grand_total'] ?? 0, 0, ',', '.'); ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <small class="text-muted" style="font-size: 11px;"><i class="far fa-user mr-1"></i> Pemohon: <?= htmlspecialchars($row['pic']); ?></small>
                                <div>
                                    <a href="detail-pr-ops?token=<?= urlencode(encrypt_id($row['id'])); ?>" class="btn btn-sm btn-info text-white py-1 px-2" title="Detail" style="font-size: 11px;"><i class="fas fa-eye mr-1"></i> Detail</a>
                                    <button class="btn btn-sm btn-success py-1 px-2 text-white font-weight-bold" data-toggle="modal" data-target="#modalApproval<?= $row['id']; ?>" style="font-size: 11px;">
                                        <i class="fas fa-check-double mr-1"></i> Review
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL POPUP APPROVAL UNTUK SETIAP PR -->
                    <div class="modal fade" id="modalApproval<?= $row['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-success text-white">
                                    <h6 class="modal-title font-weight-bold"><i class="fas fa-clipboard-check mr-2"></i>Form Approval PR : <?= htmlspecialchars($row['nomor_pr']); ?></h6>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body bg-light">
                                    <form action="approval-ops" method="POST">
                                        <input type="hidden" name="submit_approval" value="1">
                                        <input type="hidden" name="pr_id" value="<?= $row['id']; ?>">

                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-secondary">Keputusan Approval</label>
                                            <select class="form-control form-control-sm" name="status_approval" required>
                                                <option value="Approved" <?= ($row['status_approval'] === 'Approved') ? 'selected' : ''; ?>>Approved (Setujui)</option>
                                                <option value="Hold" <?= ($row['status_approval'] === 'Hold') ? 'selected' : ''; ?>>Hold (Tahan Sementara)</option>
                                                <option value="Cancel" <?= ($row['status_approval'] === 'Cancel') ? 'selected' : ''; ?>>Cancel (Tolak / Batalkan)</option>
                                                <option value="Pending" <?= ($row['status_approval'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="small font-weight-bold text-secondary">Komentar / Catatan Approval</label>
                                            <textarea class="form-control form-control-sm" name="komentar_approval" rows="3" placeholder="Tuliskan catatan atau alasan persetujuan..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-success btn-block btn-sm font-weight-bold">
                                            <i class="fas fa-paper-plane mr-1"></i> Kirim Keputusan Approval
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
                        <p class="text-muted mb-0">Tidak ada data dokumen PR Operasional untuk approval.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Panggil Bottom Bar -->
    <?php include 'includes/bottombar.php'; ?>

    <!-- jQuery & Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const toggleBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');
        const sidebar = document.getElementById('sidebarDrawer');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        if(toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);

        // SweetAlert Notifikasi Respon
        <?php if (!empty($success_message)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= addslashes($success_message); ?>',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = 'approval-ops';
            });
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '<?= addslashes($error_message); ?>',
                confirmButtonColor: '#d33'
            });
        <?php endif; ?>
    </script>
</body>
</html>