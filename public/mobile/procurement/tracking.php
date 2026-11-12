<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

// Ambil parameter pencarian jika ada
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM purchase_requisitions WHERE tipe_pengadaan = 'Non-Ops'";
$params = [];

if (!empty($search)) {
    $query .= " AND (nomor_pr LIKE :search OR kategori LIKE :search OR supplier_name LIKE :search OR delivery_site LIKE :search OR pic LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$query .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $list_tracking = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $list_tracking = [];
}
?>

    <!-- Panggil Topbar & Sidebar -->
    <?php include 'includes/topbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <!-- KONTEN UTAMA TRACKING PR -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-shipping-fast text-info mr-2"></i>Tracking & Workflow PR</h5>
                <small class="text-muted">Pantau status pengerjaan, alur approval, dan riwayat dokumen pengadaan.</small>
            </div>
            <a href="index.php" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- FORM PENCARIAN TRACKING -->
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
            <div class="card-body p-3">
                <form action="tracking.php" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-10 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-secondary mb-1">Cari Berdasarkan No. PR / Vendor / PIC / Site</label>
                            <input type="text" class="form-control form-control-sm" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Ketik kata kunci pencarian...">
                        </div>
                        <div class="col-md-2 d-flex">
                            <button type="submit" class="btn btn-info btn-sm btn-block font-weight-bold mr-1 text-white"><i class="fas fa-search mr-1"></i> Cari</button>
                            <a href="tracking.php" class="btn btn-secondary btn-sm" title="Reset"><i class="fas fa-sync-alt"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- DAFTAR KARTU TRACKING -->
        <div class="row">
            <?php if (!empty($list_tracking)): ?>
                <?php foreach ($list_tracking as $row): ?>
                    <?php 
                        // Ambil item barang terkait PR ini
                        $stmt_items = $pdo->prepare("SELECT * FROM purchase_requisition_items WHERE pr_id = :pr_id");
                        $stmt_items->execute([':pr_id' => $row['id']]);
                        $pr_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                        // Tentukan warna badge status approval
                        $status = $row['status_approval'];
                        $badge_class = 'badge-warning text-dark';
                        if ($status === 'Approved') $badge_class = 'badge-success';
                        elseif ($status === 'Cancel' || $status === 'Rejected') $badge_class = 'badge-danger';
                        elseif ($status === 'Hold') $badge_class = 'badge-secondary';
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm border-0 p-3 h-100" style="border-radius: 12px; background: #fff;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-info" style="font-size: 14px;"><i class="fas fa-file-alt mr-1"></i> <?= htmlspecialchars($row['nomor_pr']); ?></span>
                                <span class="badge <?= $badge_class; ?> px-2 py-1" style="font-size: 11px;"><?= htmlspecialchars($status); ?></span>
                            </div>

                            <div class="mb-2 text-secondary" style="font-size: 12px;">
                                <div><i class="fas fa-store mr-1 text-muted"></i> <strong>Vendor:</strong> <?= htmlspecialchars($row['supplier_name'] ?? '-'); ?></div>
                                <div><i class="fas fa-map-marker-alt mr-1 text-muted"></i> <strong>Delivery Site:</strong> <?= htmlspecialchars($row['delivery_site'] ?? '-'); ?></div>
                                <div><i class="far fa-calendar-alt mr-1 text-muted"></i> <strong>Tgl Pengajuan:</strong> <?= date('d/m/Y', strtotime($row['tanggal_pengajuan'])); ?></div>
                            </div>

                            <!-- WORKFLOW TIMELINE / STATUS PENGERJAAN -->
                            <div class="bg-light p-2 rounded mb-2 border" style="font-size: 12px;">
                                <div class="font-weight-bold text-dark mb-1"><i class="fas fa-tasks mr-1 text-info"></i> Workflow & Status Pengerjaan:</div>
                                <div class="d-flex justify-content-between text-muted border-bottom pb-1 mb-1">
                                    <span>1. Pembuatan PR</span>
                                    <span class="text-success font-weight-bold">Selesai</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted border-bottom pb-1 mb-1">
                                    <span>2. Review & Lampiran (Foto/RAB)</span>
                                    <span class="text-success font-weight-bold">Terlampir</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted border-bottom pb-1 mb-1">
                                    <span>3. Approval Manager / Direksi</span>
                                    <span class="font-weight-bold <?= $status === 'Approved' ? 'text-success' : ($status === 'Pending' ? 'text-warning' : 'text-danger'); ?>">
                                        <?= htmlspecialchars($status); ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between text-muted">
                                    <span>4. Confirm PO / BAST / GR</span>
                                    <span class="text-muted font-weight-bold">Menunggu PO</span>
                                </div>
                            </div>

                            <!-- RINGKASAN BIAYA -->
                            <div class="d-flex justify-content-between align-items-center mb-2 px-1" style="font-size: 12px;">
                                <span class="font-weight-bold text-dark">Grand Total:</span>
                                <span class="font-weight-bold text-success" style="font-size: 13px;">Rp <?= number_format($row['grand_total'] ?? 0, 0, ',', '.'); ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto" style="font-size: 11px;">
                                <span class="text-muted"><i class="far fa-user mr-1"></i> PIC: <?= htmlspecialchars($row['pic']); ?></span>
                                <div>
                                    <a href="detail-pr-non-ops?token=<?= urlencode(encrypt_id($row['id'])); ?>" class="btn btn-sm btn-info text-white py-1 px-2" style="font-size: 11px;"><i class="fas fa-eye mr-1"></i> Detail / Track</a>
                                    <?php if (!empty($row['lampiran_file'])): ?>
                                        <a href="<?= htmlspecialchars($row['lampiran_file']); ?>" target="_blank" class="btn btn-sm btn-secondary py-1 px-2" style="font-size: 11px;" title="Lihat Lampiran"><i class="fas fa-paperclip"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card shadow-sm border-0 text-center py-5">
                        <p class="text-muted mb-0">Tidak ada data tracking PR yang ditemukan.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Panggil Bottom Bar -->
    <?php include 'includes/bottombar.php'; ?>

    <!-- jQuery, Bootstrap JS, & SweetAlert2 JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JavaScript Global -->
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

        const btnLogout = document.getElementById('btnLogout');
        if(btnLogout) {
            btnLogout.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi Keluar',
                    text: 'Apakah anda mau keluar dari menu ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Tidak',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'logout.php';
                    }
                });
            });
        }
    </script>
</body>
</html>