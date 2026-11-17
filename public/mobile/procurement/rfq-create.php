<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

$success_message = '';
$error_message = '';
$current_username = $_SESSION['username'] ?? $_SESSION['name'] ?? 'ADMIN';

// 2. PROSES SIMPAN RFQ BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rfq'])) {
    $pr_no       = trim($_POST['pr_no']);
    $supplier_id = trim($_POST['supplier_id']);
    $rfq_date    = trim($_POST['tanggal_pengajuan'] ?? $_POST['rfq_date'] ?? date('Y-m-d'));

    try {
        $pdo->beginTransaction();

        // Cari Nama Supplier berdasarkan Supplier_ID yang dipilih
        $supplier_name_val = $supplier_id;
        $stmt_sname = $pdo->prepare("SELECT Company_Name FROM supplier WHERE Supplier_ID = :sid LIMIT 1");
        $stmt_sname->execute([':sid' => $supplier_id]);
        $sname_res = $stmt_sname->fetchColumn();
        if ($sname_res) {
            $supplier_name_val = $sname_res;
        }

        // Buat Nomor RFQ otomatis
        $bulanRomawi = array("", "I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
        $bln = $bulanRomawi[(int) date('m', strtotime($rfq_date))];
        $thn = date('Y', strtotime($rfq_date));

        $stmt_count = $pdo->query("SELECT COUNT(*) FROM rfq");
        $next_no = $stmt_count->fetchColumn() + 1;
        $target_rfq_no = "RFQ/" . $bln . "/" . $thn . "/" . sprintf("%03d", $next_no);

        // A. Insert ke tabel rfq (Support Multi Vendor untuk 1 PR yang sama)
        $stmt_insert = $pdo->prepare("INSERT INTO rfq (RFQ_No, PR_No, Supplier_ID, RFQ_Date, Status, Created_By) VALUES (:rfq_no, :pr_no, :supplier_id, :rfq_date, 'SENT', :created_by)");
        $stmt_insert->execute([
            ':rfq_no'     => $target_rfq_no,
            ':pr_no'      => $pr_no,
            ':supplier_id'=> $supplier_id,
            ':rfq_date'   => $rfq_date,
            ':created_by' => $current_username
        ]);

        $pdo->commit();
        $success_message = "Dokumen RFQ multi-vendor berhasil dibuat dengan Nomor: " . $target_rfq_no;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Gagal membuat RFQ: " . $e->getMessage();
    }
}

// 3. Ambil Daftar PR yang Approved (Bisa dipilih berulang kali untuk Multi Vendor)
try {
    $stmt_pr = $pdo->query("SELECT * FROM purchase_request WHERE Status IN ('Approved', 'APPROVAL_UPPER', 'APPROVED') ORDER BY ID DESC");
    $list_pr_approved = $stmt_pr->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $list_pr_approved = [];
}

// 4. Ambil Daftar Supplier / Vendor
try {
    $stmt_supp = $pdo->query("SELECT * FROM supplier ORDER BY Company_Name ASC");
    $list_suppliers = $stmt_supp->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $list_suppliers = [];
}

// 5. Filter Tanggal & Pencarian untuk Daftar RFQ
$search     = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

$query_rfq = "SELECT r.*, s.Company_Name FROM rfq r LEFT JOIN supplier s ON r.Supplier_ID = s.Supplier_ID WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query_rfq .= " AND (r.RFQ_No LIKE :search OR r.PR_No LIKE :search OR s.Company_Name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($start_date) && !empty($end_date)) {
    $query_rfq .= " AND r.RFQ_Date BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date']   = $end_date;
}

$query_rfq .= " ORDER BY r.ID DESC";

try {
    $stmt_rfq_list = $pdo->prepare($query_rfq);
    $stmt_rfq_list->execute($params);
    $list_rfq = $stmt_rfq_list->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $list_rfq = [];
}
?>

<!-- Tambahkan CSS Select2 & Styling Card Mobile -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
        padding-top: 5px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .rfq-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transition: transform 0.2s;
        margin-bottom: 12px;
        background: #fff;
    }
    .rfq-card:hover {
        transform: translateY(-2px);
    }
    .item-list-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 12px;
        margin-top: 8px;
        margin-bottom: 8px;
        border-left: 3px solid #28a745;
    }
</style>

<!-- Panggil Topbar & Sidebar -->
<?php include 'includes/topbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-file-invoice-dollar text-success mr-2"></i>RFQ (Request for Quotation)</h5>
            <small class="text-muted">Kirimkan permintaan penawaran harga ke banyak vendor (Multi Vendor) berdasarkan PR.</small>
        </div>
        <button class="btn btn-sm btn-success shadow-sm" data-toggle="modal" data-target="#modalCreateRFQ">
            <i class="fas fa-plus mr-1"></i> Buat RFQ Baru
        </button>
    </div>

    <!-- FILTER PENCARIAN & RENTANG TANGGAL -->
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
        <div class="card-body p-3">
            <form action="rfq-create" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Cari No. RFQ / PR / Vendor</label>
                        <input type="text" class="form-control form-control-sm" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Ketik kata kunci...">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Dari Tanggal</label>
                        <input type="date" class="form-control form-control-sm" name="start_date" value="<?= htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small font-weight-bold text-secondary mb-1">Sampai Tanggal</label>
                        <input type="date" class="form-control form-control-sm" name="end_date" value="<?= htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-2 d-flex">
                        <button type="submit" class="btn btn-success btn-sm btn-block font-weight-bold mr-1"><i class="fas fa-filter mr-1"></i> Filter</button>
                        <a href="rfq-create" class="btn btn-secondary btn-sm" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- CARD VIEW DAFTAR RFQ -->
    <div class="row">
        <?php if (!empty($list_rfq)): ?>
            <?php foreach ($list_rfq as $rfq): ?>
                <?php 
                    $stmt_items = $pdo->prepare("SELECT * FROM purchase_request_dtl WHERE PR_No = :pr_no");
                    $stmt_items->execute([':pr_no' => $rfq['PR_No']]);
                    $pr_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                    $status = $rfq['Status'];
                    $badge_bg = (strcasecmp($status, 'RESPONDED') === 0) ? 'badge-success' : 'badge-info';
                ?>
                <div class="col-md-6 mb-3">
                    <div class="card rfq-card p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-success" style="font-size: 14px;"><?= htmlspecialchars($rfq['RFQ_No']); ?></span>
                            <span class="badge <?= $badge_bg; ?> px-2 py-1" style="font-size: 11px;"><?= htmlspecialchars($status); ?></span>
                        </div>
                        <div class="mb-2 text-secondary" style="font-size: 12px;">
                            <div><i class="fas fa-file-alt mr-1 text-muted"></i> <strong>No. PR:</strong> <?= htmlspecialchars($rfq['PR_No']); ?></div>
                            <div><i class="fas fa-store mr-1 text-muted"></i> <strong>Vendor:</strong> <?= htmlspecialchars($rfq['Company_Name'] ?? $rfq['Supplier_ID']); ?></div>
                            <div><i class="far fa-calendar-alt mr-1 text-muted"></i> <strong>Tanggal RFQ:</strong> <?= date('d/m/Y', strtotime($rfq['RFQ_Date'])); ?></div>
                        </div>

                        <!-- Kotak Rincian Item Barang -->
                        <div class="item-list-box">
                            <div class="font-weight-bold text-dark mb-1"><i class="fas fa-list-ul mr-1 text-success"></i> Rincian Barang:</div>
                            <ul class="pl-3 mb-0 text-secondary">
                                <?php if (!empty($pr_items)): ?>
                                    <?php foreach ($pr_items as $it): ?>
                                        <li>
                                            <strong><?= htmlspecialchars($it['Product_Desc']); ?></strong> 
                                            (<?= $it['Qty']; ?> <?= htmlspecialchars($it['Unit']); ?>) 
                                            - <span class="text-dark">Rp <?= number_format($it['Prices'], 0, ',', '.'); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="text-italic">Tidak ada item tercatat.</li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <small class="text-muted" style="font-size: 11px;"><i class="far fa-user mr-1"></i> By: <?= htmlspecialchars($rfq['Created_By'] ?? 'ADMIN'); ?></small>
                            
                            <?php if (strcasecmp($status, 'RESPONDED') !== 0): ?>
                                <a href="qtn-create?rfq=<?= urlencode($rfq['RFQ_No']); ?>" class="btn btn-sm btn-primary py-1 px-2 font-weight-bold shadow-sm" style="font-size: 11px;">
                                    <i class="fas fa-arrow-right mr-1"></i> Input Penawaran (QTN)
                                </a>
                            <?php else: ?>
                                <span class="text-success font-weight-bold" style="font-size: 11px;"><i class="fas fa-check-circle mr-1"></i> Sudah Direspon</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card shadow-sm border-0 text-center py-5">
                    <p class="text-muted mb-0">Tidak ada data RFQ yang ditemukan pada rentang tanggal tersebut.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL BUAT RFQ BARU -->
<div class="modal fade" id="modalCreateRFQ" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title font-weight-bold"><i class="fas fa-paper-plane mr-2"></i>Form Buat RFQ ke Vendor</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light">
                <form action="rfq-create" method="POST">
                    <input type="hidden" name="submit_rfq" value="1">

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Pilih Dokumen PR (Bisa Dipilih Berulang untuk Multi Vendor)</label>
                        <select class="form-control form-control-sm select2-search" name="pr_no" required style="width: 100%;">
                            <option value="" disabled selected>-- Pilih PR --</option>
                            <?php if (!empty($list_pr_approved)): ?>
                                <?php foreach ($list_pr_approved as $pr): ?>
                                    <option value="<?= htmlspecialchars($pr['PR_No']); ?>">
                                        <?= htmlspecialchars($pr['PR_No']); ?> (Dept: <?= htmlspecialchars($pr['Department']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Belum ada PR yang disetujui</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Pilih Vendor / Supplier</label>
                        <select class="form-control form-control-sm select2-search" name="supplier_id" required style="width: 100%;">
                            <option value="" disabled selected>-- Pilih Vendor --</option>
                            <?php foreach ($list_suppliers as $supp): ?>
                                <option value="<?= htmlspecialchars($supp['Supplier_ID']); ?>">
                                    <?= htmlspecialchars($supp['Company_Name']); ?> (ID: <?= htmlspecialchars($supp['Supplier_ID']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small font-weight-bold text-secondary">Tanggal RFQ</label>
                        <input type="date" class="form-control form-control-sm" name="rfq_date" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-success btn-block btn-sm font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan & Kirim RFQ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/bottombar.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

    $('#modalCreateRFQ').on('shown.bs.modal', function () {
        $('.select2-search').select2({
            dropdownParent: $('#modalCreateRFQ'),
            width: '100%'
        });
    });

    <?php if (!empty($success_message)): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= addslashes($success_message); ?>', confirmButtonColor: '#28a745' }).then(() => { window.location.href = 'rfq-create'; });
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= addslashes($error_message); ?>', confirmButtonColor: '#d33' });
    <?php endif; ?>
</script>
</body>
</html>