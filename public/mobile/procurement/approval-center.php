<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

$success_message = '';
$error_message = '';

$current_username = $_SESSION['username'] ?? $_SESSION['name'] ?? 'MANAGER_APPROVAL';

// 2. PROSES UPDATE STATUS APPROVAL DARI MENU BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_approval'])) {
    $pr_id           = intval($_POST['pr_id']);
    $status_approval = trim($_POST['status_approval']);
    $komentar        = trim($_POST['komentar_approval']);

    try {
        $pdo->beginTransaction();

        // Ambil data header PR
        $stmt_pr = $pdo->prepare("SELECT * FROM purchase_request WHERE ID = :id");
        $stmt_pr->execute([':id' => $pr_id]);
        $pr_data = $stmt_pr->fetch(PDO::FETCH_ASSOC);

        if ($pr_data) {
            $pr_no = $pr_data['PR_No'];

            // Update status di tabel utama purchase_request
            $stmt_up = $pdo->prepare("UPDATE purchase_request SET Status = :status WHERE ID = :id");
            $stmt_up->execute([
                ':status' => $status_approval,
                ':id'     => $pr_id
            ]);

            // Catat log ke tabel purchase_request_log
            $stmt_log = $pdo->prepare("INSERT INTO purchase_request_log 
                (PR_No, Location, Category, Sub_Category, PIC, PR_Date, Department, Status, Note, Action_By, Action_Date, Action_Status) 
                VALUES (:pr_no, :location, :category, :sub_category, :pic, :pr_date, :department, :status, :note, :action_by, NOW(), :action_status)");
            $stmt_log->execute([
                ':pr_no'         => $pr_no,
                ':location'      => $pr_data['Location'],
                ':category'      => $pr_data['Category'],
                ':sub_category'  => $pr_data['Sub_Category'],
                ':pic'           => $pr_data['PIC'],
                ':pr_date'       => $pr_data['PR_Date'],
                ':department'    => $pr_data['Department'],
                ':status'        => $status_approval,
                ':note'          => $komentar ?: 'Approval decision updated to ' . $status_approval,
                ':action_by'     => $current_username,
                ':action_status' => $status_approval
            ]);

            $pdo->commit();
            $success_message = "Status PR " . $pr_no . " berhasil diperbarui menjadi " . $status_approval . "!";
        } else {
            throw new Exception("Dokumen PR tidak ditemukan.");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Gagal memperbarui approval: " . $e->getMessage();
    }
}

// 3. AMBIL DATA PR DENGAN FILTER PERIODE TANGGAL (Default hari ini jika kosong)
$search        = $_GET['search'] ?? '';
$status_flt    = $_GET['status'] ?? '';
$supplies_type = $_GET['supplies_type'] ?? '';
$start_date    = $_GET['start_date'] ?? date('Y-m-d');
$end_date      = $_GET['end_date'] ?? date('Y-m-d');

$query = "SELECT * FROM purchase_request WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (PR_No LIKE :search OR Category LIKE :search OR Department LIKE :search OR Supplier_Name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($status_flt)) {
    $query .= " AND Status = :status";
    $params[':status'] = $status_flt;
}

if (!empty($supplies_type)) {
    $query .= " AND Supplies_Type = :stype";
    $params[':stype'] = $supplies_type;
}

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND PR_Date BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date']   = $end_date;
}

$query .= " ORDER BY ID DESC";

try {
    $stmt_list = $pdo->prepare($query);
    $stmt_list->execute($params);
    $list_pr = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $list_pr = [];
}
?>

<!-- Panggil Topbar & Sidebar -->
<?php include 'includes/topbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-tasks text-success mr-2"></i>Approval Center (Pusat Persetujuan PR)</h5>
            <small class="text-muted">Kelola dan setujui seluruh dokumen Purchase Requisition dari satu tempat.</small>
        </div>
    </div>

    <!-- FILTER PENCARIAN & RENTANG TANGGAL -->
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
        <div class="card-body p-3">
            <form action="approval-center" method="GET" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Cari No. PR / Vendor / Dept</label>
                        <input type="text" class="form-control form-control-sm" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Ketik kata kunci...">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Tipe Pengadaan</label>
                        <select class="form-control form-control-sm" name="supplies_type">
                            <option value="">-- Semua Tipe --</option>
                            <option value="Ops" <?= ($supplies_type === 'Ops') ? 'selected' : ''; ?>>Ops</option>
                            <option value="Non-Ops" <?= ($supplies_type === 'Non-Ops') ? 'selected' : ''; ?>>Non-Ops</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Status</label>
                        <select class="form-control form-control-sm" name="status">
                            <option value="">-- Semua Status --</option>
                            <option value="APPROVAL_UPPER" <?= ($status_flt === 'APPROVAL_UPPER') ? 'selected' : ''; ?>>APPROVAL_UPPER</option>
                            <option value="Pending" <?= ($status_flt === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?= ($status_flt === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="Hold" <?= ($status_flt === 'Hold') ? 'selected' : ''; ?>>Hold</option>
                            <option value="Cancel" <?= ($status_flt === 'Cancel') ? 'selected' : ''; ?>>Cancel</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Dari Tanggal</label>
                        <input type="date" class="form-control form-control-sm" name="start_date" value="<?= htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Sampai Tanggal</label>
                        <input type="date" class="form-control form-control-sm" name="end_date" value="<?= htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-1 d-flex mt-2 mt-md-0">
                        <button type="submit" id="btnFilter" class="btn btn-success btn-sm btn-block font-weight-bold mr-1" title="Filter"><i class="fas fa-filter"></i></button>
                        <a href="approval-center" class="btn btn-secondary btn-sm" title="Reset"><i class="fas fa-sync-alt"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DAFTAR KARTU PR -->
    <div class="row">
        <?php if (!empty($list_pr)): ?>
            <?php foreach ($list_pr as $row): ?>
                <?php 
                    // Ambil detail item dari purchase_request_dtl
                    $stmt_items = $pdo->prepare("SELECT * FROM purchase_request_dtl WHERE PR_No = :pr_no");
                    $stmt_items->execute([':pr_no' => $row['PR_No']]);
                    $pr_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                    // Tentukan warna badge status secara dinamis agar tidak sama semua
                    $status_val = $row['Status'];
                    $badge_class = 'badge-secondary';
                    if (strcasecmp($status_val, 'Approved') === 0 || strcasecmp($status_val, 'APPROVED') === 0) {
                        $badge_class = 'badge-success';
                    } elseif (strcasecmp($status_val, 'APPROVAL_UPPER') === 0) {
                        $badge_class = 'badge-info';
                    } elseif (strcasecmp($status_val, 'Hold') === 0) {
                        $badge_class = 'badge-warning text-dark';
                    } elseif (strcasecmp($status_val, 'Cancel') === 0) {
                        $badge_class = 'badge-danger';
                    }
                ?>
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0 p-3" style="border-radius: 10px;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="font-weight-bold text-success"><?= htmlspecialchars($row['PR_No']); ?></span>
                                <span class="badge badge-dark ml-2" style="font-size: 10px;"><?= htmlspecialchars($row['Supplies_Type']); ?></span>
                            </div>
                            <span class="badge <?= $badge_class; ?> px-2 py-1" style="font-size: 11px;"><?= htmlspecialchars($status_val); ?></span>
                        </div>
                        
                        <div class="mb-2 text-secondary" style="font-size: 12px;">
                            <div><strong>Dept:</strong> <?= htmlspecialchars($row['Department'] ?? '-'); ?></div>
                            <div><strong>Vendor:</strong> <?= htmlspecialchars($row['Supplier_Name'] ?? '-'); ?></div>
                            <div><strong>Tgl:</strong> <?= date('d/m/Y', strtotime($row['PR_Date'])); ?></div>
                        </div>

                        <!-- Kotak Rincian Barang -->
                        <div class="bg-light p-2 rounded mb-2" style="font-size: 12px; border-left: 3px solid #28a745;">
                            <div class="font-weight-bold text-dark mb-1">Rincian Barang:</div>
                            <ul class="pl-3 mb-0 text-secondary">
                                <?php if (!empty($pr_items)): ?>
                                    <?php foreach ($pr_items as $it): ?>
                                        <li><?= htmlspecialchars($it['Product_Desc']); ?> (<?= $it['Qty']; ?> <?= htmlspecialchars($it['Unit']); ?>) - Rp <?= number_format($it['Prices'], 0, ',', '.'); ?></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li>Tidak ada item.</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <small class="text-muted font-weight-bold">Total: Rp <?= number_format($row['Estimated_Budget'] ?? 0, 0, ',', '.'); ?></small>
                            
                            <!-- Tombol Review disembunyikan jika status sudah Approved atau Cancel -->
                            <?php if (strcasecmp($status_val, 'Approved') !== 0 && strcasecmp($status_val, 'APPROVED') !== 0 && strcasecmp($status_val, 'Cancel') !== 0): ?>
                                <button class="btn btn-sm btn-success px-3 py-1 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalApp<?= $row['ID']; ?>" style="font-size: 11px;">
                                    <i class="fas fa-check-circle mr-1"></i> Review & Approve
                                </button>
                            <?php else: ?>
                                <span class="text-muted font-italic" style="font-size: 11px;"><i class="fas fa-lock mr-1"></i> Selesai / Dikunci</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- MODAL APPROVAL PER ITEM -->
                <div class="modal fade" id="modalApp<?= $row['ID']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-success text-white">
                                <h6 class="modal-title font-weight-bold">Approval PR: <?= htmlspecialchars($row['PR_No']); ?></h6>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body bg-light">
                                <form action="approval-center" method="POST">
                                    <input type="hidden" name="submit_approval" value="1">
                                    <input type="hidden" name="pr_id" value="<?= $row['ID']; ?>">

                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-secondary">Pilih Keputusan</label>
                                        <select class="form-control form-control-sm" name="status_approval" required>
                                            <option value="Approved">Approved (Setujui)</option>
                                            <!-- <option value="APPROVAL_UPPER">APPROVAL_UPPER</option> -->
                                            <option value="Hold">Hold (Tahan)</option>
                                            <option value="Cancel">Cancel (Tolak)</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-secondary">Catatan / Komentar</label>
                                        <textarea class="form-control form-control-sm" name="komentar_approval" rows="3" placeholder="Tuliskan catatan approval..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-block btn-sm font-weight-bold">
                                        Simpan Keputusan
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
                    <p class="text-muted mb-0">Tidak ada data dokumen PR yang memerlukan approval pada periode ini.</p>
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
    // Skrip Pengontrol Sidebar Mobile
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

    // Efek Loading Spinner saat Filter Ditekan
    document.getElementById('filterForm').addEventListener('submit', function() {
        const btnFilter = document.getElementById('btnFilter');
        btnFilter.disabled = true;
        btnFilter.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    });

    // Notifikasi SweetAlert2 untuk Sukses & Gagal
    <?php if (!empty($success_message)): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= addslashes($success_message); ?>',
            confirmButtonColor: '#28a745'
        }).then(() => {
            window.location.href = 'approval-center';
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