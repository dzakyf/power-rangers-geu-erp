<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

// Ambil Token dari URL dan Dekripsi menjadi ID
$token = isset($_GET['token']) ? $_GET['token'] : '';
$pr_id = decrypt_id($token);

if ($pr_id === false || intval($pr_id) <= 0) {
    // Jika token tidak valid atau dimanipulasi orang
    header("Location: procurement-ops.php");
    exit;
}

$success_message = '';
$error_message = '';

// 2. PROSES UPDATE CATATAN / STATUS (HOLD / CANCEL / REVISI)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_pr'])) {
    $catatan         = trim($_POST['catatan']);
    $status_approval = trim($_POST['status_approval']);

    try {
        $stmt_up = $pdo->prepare("UPDATE purchase_requisitions SET catatan = :catatan, status_approval = :status_approval WHERE id = :id AND tipe_pengadaan = 'Ops'");
        $stmt_up->execute([
            ':catatan'         => $catatan,
            ':status_approval' => $status_approval,
            ':id'              => $pr_id
        ]);
        $success_message = "Data PR berhasil diperbarui!";
    } catch (PDOException $e) {
        $error_message = "Gagal memperbarui data: " . $e->getMessage();
    }
}

// 3. AMBIL DATA HEADER PR
try {
    $stmt_header = $pdo->prepare("SELECT * FROM purchase_requisitions WHERE id = :id AND tipe_pengadaan = 'Ops' LIMIT 1");
    $stmt_header->execute([':id' => $pr_id]);
    $pr = $stmt_header->fetch(PDO::FETCH_ASSOC);

    if (!$pr) {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Data Purchase Requisition tidak ditemukan! <a href='procurement-ops'>Kembali</a></div></div>";
        include 'includes/bottombar.php';
        exit;
    }

    // AMBIL DATA DETAIL ITEM PR (MULTI-ITEM)
    $stmt_items = $pdo->prepare("SELECT * FROM purchase_requisition_items WHERE pr_id = :pr_id");
    $stmt_items->execute([':pr_id' => $pr_id]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Terjadi kesalahan database: " . $e->getMessage();
}
?>

    <!-- CSS Khusus Detail -->
    <style>
        .detail-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            background: #fff;
            margin-bottom: 20px;
        }
        .table-items th {
            font-size: 12px;
            background-color: #f8f9fa;
            color: #495057;
        }
        .table-items td {
            font-size: 13px;
            vertical-align: middle;
        }
    </style>

    <!-- Panggil Topbar & Sidebar -->
    <?php include 'includes/topbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <!-- CONTAINER UTAMA DETAIL PR OPS -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-file-invoice text-primary mr-2"></i>Detail PR : <?= htmlspecialchars($pr['nomor_pr']); ?></h5>
                <small class="text-muted">Rincian lengkap pengadaan operasional dan status dokumen.</small>
            </div>
            <a href="procurement-ops" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <!-- INFORMASI HEADER PR -->
        <div class="card detail-card p-3">
            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-info-circle mr-1"></i> Informasi Umum</h6>
            <div class="row" style="font-size: 13px;">
                <div class="col-md-6 mb-2">
                    <span class="text-muted d-block">Nomor PR:</span>
                    <strong><?= htmlspecialchars($pr['nomor_pr']); ?></strong>
                </div>
                <div class="col-md-6 mb-2">
                    <span class="text-muted d-block">Status Approval:</span>
                    <?php 
                        $status = $pr['status_approval'];
                        if ($status === 'Approved') {
                            echo '<span class="badge badge-success px-2 py-1">Approved</span>';
                        } elseif ($status === 'Pending') {
                            echo '<span class="badge badge-warning text-dark px-2 py-1">Pending Approval</span>';
                        } elseif ($status === 'Hold') {
                            echo '<span class="badge badge-secondary px-2 py-1">Hold</span>';
                        } else {
                            echo '<span class="badge badge-danger px-2 py-1">Canceled</span>';
                        }
                    ?>
                </div>
                <div class="col-md-6 mb-2">
                    <span class="text-muted d-block">Kategori Anggaran:</span>
                    <strong><?= htmlspecialchars($pr['kategori']); ?></strong>
                </div>
                <div class="col-md-6 mb-2">
                    <span class="text-muted d-block">Status Order & Departemen:</span>
                    <strong><?= htmlspecialchars($pr['status_order']); ?> / <?= htmlspecialchars($pr['departemen']); ?></strong>
                </div>
                <div class="col-md-6 mb-2">
                    <span class="text-muted d-block">Tanggal Pengajuan:</span>
                    <strong><?= date('d/m/Y', strtotime($pr['tanggal_pengajuan'])); ?></strong>
                </div>
                <div class="col-md-6 mb-2">
                    <span class="text-muted d-block">Tanggal Diinginkan (Delivery):</span>
                    <strong><?= date('d/m/Y', strtotime($pr['tanggal_diinginkan'] ?? $pr['tanggal_pengajuan'])); ?></strong>
                </div>
                <div class="col-md-6 mb-2">
                    <span class="text-muted d-block">Pembuat (PIC):</span>
                    <strong><?= htmlspecialchars($pr['pic']); ?></strong>
                </div>
                <div class="col-md-6 mb-2">
                    <span class="text-muted d-block">Lampiran Berkas:</span>
                    <?php if (!empty($pr['lampiran_file'])): ?>
                        <a href="<?= htmlspecialchars($pr['lampiran_file']); ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 mt-1"><i class="fas fa-paperclip mr-1"></i> Lihat Lampiran / RAB</a>
                    <?php else: ?>
                        <span class="text-muted font-italic">Tidak ada lampiran</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TABEL RINCIAN MULTI-ITEM -->
        <div class="card detail-card p-3">
            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-list mr-1"></i> Rincian Material / Suku Cadang</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-items mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="30%">Nama Barang / Keterangan</th>
                            <th width="25%">Spesifikasi / Part Number</th>
                            <th class="text-center" width="10%">Qty</th>
                            <th class="text-center" width="10%">Satuan</th>
                            <th class="text-right" width="20%">Est. Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        if (!empty($items)): 
                            $no = 1;
                            foreach ($items as $item):
                                $subtotal = $item['total_harga'];
                                $grand_total += $subtotal;
                        ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="font-weight-bold"><?= htmlspecialchars($item['keterangan']); ?></td>
                                <td><?= htmlspecialchars($item['spesifikasi'] ?? '-'); ?></td>
                                <td class="text-center"><?= $item['qty']; ?></td>
                                <td class="text-center"><?= htmlspecialchars($item['satuan']); ?></td>
                                <td class="text-right">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Tidak ada item rincian material.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="5" class="text-right">Grand Total Estimasi Biaya:</td>
                            <td class="text-right text-primary">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- FORM EDIT CATATAN & STATUS (AKSI HOLD / CANCEL) -->
        <div class="card detail-card p-3">
            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-edit mr-1"></i> Aksi & Catatan Dokumen</h6>
            <form action="detail-pr-ops.php?token=<?= htmlspecialchars($token); ?>" method="POST">
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-secondary">Catatan / Keterangan Lapangan</label>
                    <textarea class="form-control form-control-sm" name="catatan" rows="3"><?= htmlspecialchars($pr['catatan'] ?? ''); ?></textarea>
                </div>
                <div class="form-group mb-3">
                    <label class="small font-weight-bold text-secondary">Ubah Status Dokumen</label>
                    <select class="form-control form-control-sm" name="status_approval">
                        <option value="Pending" <?= ($pr['status_approval'] === 'Pending') ? 'selected' : ''; ?>>Pending (Menunggu Approval)</option>
                        <option value="Hold" <?= ($pr['status_approval'] === 'Hold') ? 'selected' : ''; ?>>Hold (Ditahan Sementara)</option>
                        <option value="Cancel" <?= ($pr['status_approval'] === 'Cancel') ? 'selected' : ''; ?>>Cancel (Dibatalkan)</option>
                    </select>
                </div>
                <button type="submit" name="update_pr" class="btn btn-primary btn-sm font-weight-bold btn-block">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan PR
                </button>
            </form>
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
                confirmButtonColor: '#007bff'
            }).then(() => {
                // Opsional: Refresh halaman setelah alert ditutup agar data header ter-update
                window.location.href = 'detail-pr-ops.php?token=<?= urlencode($token); ?>';
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