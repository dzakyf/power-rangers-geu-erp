<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

// Parameter Filter & Pencarian (Default menampilkan data mulai hari ini)
$search        = $_GET['search'] ?? '';
$supplies_type = $_GET['supplies_type'] ?? '';
$status_flt    = $_GET['status'] ?? '';
$start_date    = $_GET['start_date'] ?? date('Y-m-d');
$end_date      = $_GET['end_date'] ?? date('Y-m-d');

$query = "SELECT * FROM purchase_request WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (PR_No LIKE :search OR Department LIKE :search OR Supplier_Name LIKE :search OR Location LIKE :search OR PIC LIKE :search OR PO_No LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($supplies_type)) {
    $query .= " AND Supplies_Type = :stype";
    $params[':stype'] = $supplies_type;
}

if (!empty($status_flt)) {
    $query .= " AND Status = :status";
    $params[':status'] = $status_flt;
}

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND PR_Date BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date']   = $end_date;
}

$query .= " ORDER BY ID DESC";

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
            <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-shipping-fast text-info mr-2"></i>Tracking & Monitoring Procurement</h5>
            <small class="text-muted">Pantau progres menyeluruh dari pengajuan PR, Approval, RFQ Multi-Vendor, QTN, hingga PO.</small>
        </div>
        <a href="index.php" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Dashboard
        </a>
    </div>

    <!-- FORM PENCARIAN & FILTER -->
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
        <div class="card-body p-3">
            <form action="tracking.php" method="GET" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Cari No. PR / PO / Vendor / PIC</label>
                        <input type="text" class="form-control form-control-sm" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Ketik kata kunci...">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Tipe</label>
                        <select class="form-control form-control-sm" name="supplies_type">
                            <option value="">-- Semua --</option>
                            <option value="Ops" <?= ($supplies_type === 'Ops') ? 'selected' : ''; ?>>Ops</option>
                            <option value="Non-Ops" <?= ($supplies_type === 'Non-Ops') ? 'selected' : ''; ?>>Non-Ops</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Status PR</label>
                        <select class="form-control form-control-sm" name="status">
                            <option value="">-- Semua --</option>
                            <option value="Pending" <?= ($status_flt === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="APPROVAL_UPPER" <?= ($status_flt === 'APPROVAL_UPPER') ? 'selected' : ''; ?>>APPROVAL_UPPER</option>
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
                        <button type="submit" id="btnFilter" class="btn btn-info btn-sm btn-block font-weight-bold mr-1 text-white" title="Cari"><i class="fas fa-search"></i></button>
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
                    $pr_no = $row['PR_No'];

                    // 1. Ambil item barang dari purchase_request_dtl
                    $stmt_items = $pdo->prepare("SELECT * FROM purchase_request_dtl WHERE PR_No = :pr_no");
                    $stmt_items->execute([':pr_no' => $pr_no]);
                    $pr_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                    // 2. Cek progres RFQ (Multi Vendor)
                    $stmt_rfq = $pdo->prepare("SELECT COUNT(*) as total_rfq, SUM(CASE WHEN Status = 'RESPONDED' THEN 1 ELSE 0 END) as responded_rfq FROM rfq WHERE PR_No = :pr_no");
                    $stmt_rfq->execute([':pr_no' => $pr_no]);
                    $rfq_stat = $stmt_rfq->fetch(PDO::FETCH_ASSOC);
                    $total_rfq = (int)($rfq_stat['total_rfq'] ?? 0);
                    $responded_rfq = (int)($rfq_stat['responded_rfq'] ?? 0);

                    // 3. Cek progres QTN (Jumlah Penawaran Masuk)
                    $stmt_qtn = $pdo->prepare("SELECT COUNT(DISTINCT QTN_No) FROM quotation WHERE PR_No = :pr_no");
                    $stmt_qtn->execute([':pr_no' => $pr_no]);
                    $total_qtn = (int)$stmt_qtn->fetchColumn();

                    // 4. Cek data Purchase Order (PO) jika sudah terbit
                    $stmt_po = $pdo->prepare("SELECT po.*, s.Company_Name FROM purchase_order po LEFT JOIN supplier s ON po.Supplier = s.Supplier_ID WHERE po.PR_No = :pr_no LIMIT 1");
                    $stmt_po->execute([':pr_no' => $pr_no]);
                    $po_data = $stmt_po->fetch(PDO::FETCH_ASSOC);

                    // Tentukan warna badge status PR
                    $status = $row['Status'];
                    $badge_class = 'badge-secondary';
                    if (strcasecmp($status, 'Approved') === 0 || strcasecmp($status, 'APPROVED') === 0) {
                        $badge_class = 'badge-success';
                    } elseif (strcasecmp($status, 'APPROVAL_UPPER') === 0) {
                        $badge_class = 'badge-info';
                    } elseif (strcasecmp($status, 'Hold') === 0 || strcasecmp($status, 'Pending') === 0) {
                        $badge_class = 'badge-warning text-dark';
                    } elseif (strcasecmp($status, 'Cancel') === 0) {
                        $badge_class = 'badge-danger';
                    }

                    $is_approved = in_array(strtoupper($status), ['APPROVED', 'APPROVAL_UPPER']);
                ?>
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0 p-3 h-100" style="border-radius: 12px; background: #fff;">
                        <!-- Header Kartu -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="font-weight-bold text-info" style="font-size: 14px;"><i class="fas fa-file-alt mr-1"></i> <?= htmlspecialchars($pr_no); ?></span>
                                <span class="badge badge-dark ml-1" style="font-size: 10px;"><?= htmlspecialchars($row['Supplies_Type'] ?? '-'); ?></span>
                            </div>
                            <span class="badge <?= $badge_class; ?> px-2 py-1" style="font-size: 11px;"><?= htmlspecialchars($status); ?></span>
                        </div>

                        <!-- Info Umum PR -->
                        <div class="mb-2 text-secondary" style="font-size: 12px;">
                            <div><i class="fas fa-building mr-1 text-muted"></i> <strong>Dept / Lokasi:</strong> <?= htmlspecialchars($row['Department'] ?? '-'); ?> (<?= htmlspecialchars($row['Location'] ?? '-'); ?>)</div>
                            <div><i class="fas fa-store mr-1 text-muted"></i> <strong>Vendor Terpilih:</strong> <?= htmlspecialchars($po_data['Company_Name'] ?? $row['Supplier_Name'] ?? 'Belum ditentukan'); ?></div>
                            <div><i class="far fa-calendar-alt mr-1 text-muted"></i> <strong>Tgl PR:</strong> <?= date('d/m/Y', strtotime($row['PR_Date'])); ?></div>
                        </div>

                        <!-- Rincian Item Barang -->
                        <div class="bg-light p-2 rounded mb-2" style="font-size: 11.5px; border-left: 3px solid #17a2b8;">
                            <div class="font-weight-bold text-dark mb-1">Rincian Item (<?= count($pr_items); ?> item):</div>
                            <ul class="pl-3 mb-0 text-secondary">
                                <?php if (!empty($pr_items)): ?>
                                    <?php foreach ($pr_items as $it): ?>
                                        <li><?= htmlspecialchars($it['Product_Desc']); ?> (<?= $it['Qty']; ?> <?= htmlspecialchars($it['Unit']); ?>)</li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="font-italic">Tidak ada rincian item.</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <!-- WORKFLOW TIMELINE 5 TAHAP -->
                        <div class="bg-light p-2 rounded mb-2 border" style="font-size: 12px;">
                            <div class="font-weight-bold text-dark mb-1"><i class="fas fa-tasks mr-1 text-info"></i> Progres Alur Pengadaan:</div>
                            
                            <!-- Tahap 1 -->
                            <div class="d-flex justify-content-between text-muted border-bottom pb-1 mb-1">
                                <span>1. Pengajuan PR</span>
                                <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Selesai</span>
                            </div>

                            <!-- Tahap 2 -->
                            <div class="d-flex justify-content-between text-muted border-bottom pb-1 mb-1">
                                <span>2. Approval Center</span>
                                <span class="font-weight-bold <?= $is_approved ? 'text-success' : (strcasecmp($status, 'Cancel') === 0 ? 'text-danger' : 'text-warning'); ?>">
                                    <?= htmlspecialchars($status); ?>
                                </span>
                            </div>

                            <!-- Tahap 3 -->
                            <div class="d-flex justify-content-between text-muted border-bottom pb-1 mb-1">
                                <span>3. RFQ (Multi Vendor)</span>
                                <?php if ($total_rfq > 0): ?>
                                    <span class="text-primary font-weight-bold"><?= $total_rfq; ?> RFQ Terkirim (<?= $responded_rfq; ?> Respon)</span>
                                <?php elseif ($is_approved): ?>
                                    <a href="rfq-create.php" class="text-info font-weight-bold">Buat RFQ &rarr;</a>
                                <?php else: ?>
                                    <span class="text-muted">Menunggu Approval</span>
                                <?php endif; ?>
                            </div>

                            <!-- Tahap 4 -->
                            <div class="d-flex justify-content-between text-muted border-bottom pb-1 mb-1">
                                <span>4. QTN & Matriks Harga</span>
                                <?php if ($total_qtn > 0): ?>
                                    <span class="text-info font-weight-bold"><?= $total_qtn; ?> Penawaran Masuk</span>
                                <?php elseif ($total_rfq > 0): ?>
                                    <a href="qtn-create.php" class="text-primary font-weight-bold">Input QTN &rarr;</a>
                                <?php else: ?>
                                    <span class="text-muted">Menunggu RFQ</span>
                                <?php endif; ?>
                            </div>

                            <!-- Tahap 5 -->
                            <div class="d-flex justify-content-between text-muted">
                                <span>5. Purchase Order (PO)</span>
                                <?php if ($po_data): ?>
                                    <span class="text-success font-weight-bold"><i class="fas fa-file-contract mr-1"></i><?= htmlspecialchars($po_data['PO_No']); ?></span>
                                <?php elseif ($total_qtn > 0): ?>
                                    <a href="qtn-create.php" class="text-success font-weight-bold">Pilih Pemenang &rarr;</a>
                                <?php else: ?>
                                    <span class="text-muted">Belum Terbit</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- RINGKASAN BIAYA -->
                        <div class="d-flex justify-content-between align-items-center mb-2 px-1" style="font-size: 12px;">
                            <span class="text-muted">Est. Budget PR: <strong>Rp <?= number_format($row['Estimated_Budget'] ?? 0, 0, ',', '.'); ?></strong></span>
                            <?php if ($po_data): ?>
                                <span class="font-weight-bold text-success">Final PO: Rp <?= number_format($po_data['Total'] ?? 0, 0, ',', '.'); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- FOOTER KARTU -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto" style="font-size: 11px;">
                            <span class="text-muted"><i class="far fa-user mr-1"></i> PIC: <?= htmlspecialchars($row['PIC'] ?? '-'); ?></span>
                            <div>
                                <?php if ($po_data): ?>
                                    <a href="po-create" class="btn btn-sm btn-success py-1 px-2 font-weight-bold" style="font-size: 11px;">
                                        <i class="fas fa-check-double mr-1"></i> Lihat PO
                                    </a>
                                <?php elseif ($total_qtn > 0): ?>
                                    <a href="qtn-create" class="btn btn-sm btn-info text-white py-1 px-2 font-weight-bold" style="font-size: 11px;">
                                        <i class="fas fa-list-ol mr-1"></i> Matriks QTN
                                    </a>
                                <?php elseif ($is_approved): ?>
                                    <a href="rfq-create" class="btn btn-sm btn-primary py-1 px-2 font-weight-bold" style="font-size: 11px;">
                                        <i class="fas fa-paper-plane mr-1"></i> Kelola RFQ
                                    </a>
                                <?php else: ?>
                                    <a href="approval-center" class="btn btn-sm btn-warning text-dark py-1 px-2 font-weight-bold" style="font-size: 11px;">
                                        <i class="fas fa-tasks mr-1"></i> Ke Approval
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card shadow-sm border-0 text-center py-5">
                    <p class="text-muted mb-0">Tidak ada data tracking dokumen pada periode tanggal yang dipilih.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Panggil Bottom Bar -->
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

    // Loading spinner saat tombol cari diklik
    document.getElementById('filterForm').addEventListener('submit', function() {
        const btnFilter = document.getElementById('btnFilter');
        btnFilter.disabled = true;
        btnFilter.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    });

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