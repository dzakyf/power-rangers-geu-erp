<?php
// 1. Panggil Header (Session check, CSS, & Koneksi Database)
include 'includes/header.php';
require_once 'db.php'; 

$success_message = '';
$error_message = '';

// 2. PROSES INSERT DATA MULTI-ITEM UNTUK OPS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pr'])) {
    $kategori           = trim($_POST['kategori']);
    $status_order       = trim($_POST['status_order']);
    $tanggal_pengajuan  = trim($_POST['tanggal_pengajuan']);
    $tanggal_diinginkan = trim($_POST['tanggal_diinginkan']);
    $catatan            = trim($_POST['catatan']);
    $departemen         = $_SESSION['department'] ?? 'Operation / Logistics';
    $pic                = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Admin';
    $user_id            = $_SESSION['user_id'] ?? NULL;
    $tipe_pengadaan     = 'Ops';

    $item_keterangan    = $_POST['item_keterangan'] ?? [];
    $item_spesifikasi   = $_POST['item_spesifikasi'] ?? [];
    $item_qty           = $_POST['item_qty'] ?? [];
    $item_satuan        = $_POST['item_satuan'] ?? [];
    $item_harga         = $_POST['item_harga'] ?? [];

    if (empty($item_keterangan)) {
        $error_message = "Minimal harus ada 1 item material/barang operasional yang diinputkan!";
    } else {
        try {
            $pdo->beginTransaction();

            $bulanRomawi = array("", "I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
            $bln = $bulanRomawi[(int)date('m')];
            $thn = date('Y');

            $stmt_count = $pdo->query("SELECT COUNT(*) FROM purchase_requisitions WHERE tipe_pengadaan = 'Ops'");
            $next_no = $stmt_count->fetchColumn() + 1;
            $nomor_pr = sprintf("%03d", $next_no) . "/PR-OPS/" . $bln . "/" . $thn;

            $lampiran_name = NULL;
            if (isset($_FILES['lampiran_file']) && $_FILES['lampiran_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['lampiran_file']['tmp_name'];
                $fileName = $_FILES['lampiran_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExtensions = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx');
                if (in_array($fileExtension, $allowedExtensions)) {
                    $uploadFileDir = 'uploads/pr/';
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', $fileName);
                    $dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $lampiran_name = $dest_path;
                    }
                }
            }

            $sql_header = "INSERT INTO purchase_requisitions 
                           (nomor_pr, kategori, tipe_pengadaan, departemen, tanggal_pengajuan, tanggal_diinginkan, status_order, user_id, pic, catatan, lampiran_file, status_approval) 
                           VALUES 
                           (:nomor_pr, :kategori, :tipe_pengadaan, :departemen, :tanggal_pengajuan, :tanggal_diinginkan, :status_order, :user_id, :pic, :catatan, :lampiran_file, 'Pending')";
            
            $stmt_h = $pdo->prepare($sql_header);
            $stmt_h->execute([
                ':nomor_pr'           => $nomor_pr,
                ':kategori'           => $kategori,
                ':tipe_pengadaan'      => $tipe_pengadaan,
                ':departemen'          => $departemen,
                ':tanggal_pengajuan'  => $tanggal_pengajuan,
                ':tanggal_diinginkan' => $tanggal_diinginkan,
                ':status_order'        => $status_order,
                ':user_id'             => $user_id,
                ':pic'                 => $pic,
                ':catatan'             => $catatan,
                ':lampiran_file'       => $lampiran_name
            ]);

            $pr_id = $pdo->lastInsertId();

            $sql_item = "INSERT INTO purchase_requisition_items 
                         (pr_id, keterangan, spesifikasi, qty, satuan, harga_estimasi, total_harga) 
                         VALUES 
                         (:pr_id, :keterangan, :spesifikasi, :qty, :satuan, :harga_estimasi, :total_harga)";
            $stmt_i = $pdo->prepare($sql_item);

            for ($i = 0; $i < count($item_keterangan); $i++) {
                $ket  = trim($item_keterangan[$i]);
                if (!empty($ket)) {
                    $spec  = trim($item_spesifikasi[$i]);
                    $qty   = floatval($item_qty[$i]);
                    $sat   = trim($item_satuan[$i]);
                    $hrg   = floatval($item_harga[$i]);
                    $total = $qty * $hrg;

                    $stmt_i->execute([
                        ':pr_id'          => $pr_id,
                        ':keterangan'     => $ket,
                        ':spesifikasi'    => $spec,
                        ':qty'            => $qty,
                        ':satuan'         => $sat,
                        ':harga_estimasi' => $hrg,
                        ':total_harga'    => $total
                    ]);
                }
            }

            $pdo->commit();
            $success_message = "PR Operasional berhasil diajukan dengan Nomor: " . $nomor_pr;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}

// 3. FILTER & SEARCH PARAMETERS
$search     = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

// Query dasar dengan filter pencarian dan tanggal
$query = "SELECT pr.*, 
          (SELECT SUM(total_harga) FROM purchase_requisition_items WHERE pr_id = pr.id) as grand_total 
          FROM purchase_requisitions pr 
          WHERE pr.tipe_pengadaan = 'Ops'";

$params = [];

if (!empty($search)) {
    $query .= " AND (pr.nomor_pr LIKE :search OR pr.kategori LIKE :search OR pr.catatan LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND pr.tanggal_pengajuan BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date']   = $end_date;
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

    <!-- CSS Tambahan untuk Floating Label & Card View -->
    <style>
        .form-label-group {
            position: relative;
            margin-bottom: 15px;
        }
        .form-label-group input, 
        .form-label-group select, 
        .form-label-group textarea {
            height: 48px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 16px 15px 4px 15px;
            font-size: 14px;
            background-color: #fff;
        }
        .form-label-group textarea {
            height: auto;
            padding-top: 20px;
        }
        .form-label-group label {
            position: absolute;
            top: 0;
            left: 0;
            display: block;
            width: 100%;
            margin-bottom: 0;
            line-height: 1.5;
            color: #6c757d;
            border: 1px solid transparent;
            border-radius: .25rem;
            transition: all .1s ease-in-out;
            padding: 12px 15px;
            pointer-events: none;
            font-size: 13px;
        }
        .form-label-group input:focus ~ label,
        .form-label-group input:not(:placeholder-shown) ~ label,
        .form-label-group select:focus ~ label,
        .form-label-group select:not([value=""]) ~ label,
        .form-label-group textarea:focus ~ label,
        .form-label-group textarea:not(:placeholder-shown) ~ label {
            padding-top: 4px;
            padding-bottom: 2px;
            font-size: 10px;
            color: #007bff;
            font-weight: 600;
        }
        /* Styling Card View */
        .pr-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: transform 0.2s;
            margin-bottom: 12px;
            background: #fff;
        }
        .pr-card:hover {
            transform: translateY(-2px);
        }
    </style>

    <!-- Panggil Topbar -->
    <?php include 'includes/topbar.php'; ?>

    <!-- Panggil Sidebar Drawer -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- CONTAINER / KONTEN UTAMA PROCUREMENT OPS -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-truck text-primary mr-2"></i>Procurement - Ops</h5>
                <small class="text-muted">Pengadaan suku cadang, armada, & material lapangan.</small>
            </div>
            <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambahPROps">
                <i class="fas fa-plus mr-1"></i> Buat PR Baru
            </button>
        </div>

        <!-- FORM FILTER & PENCARIAN -->
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
            <div class="card-body p-3">
                <form action="procurement-ops.php" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-secondary mb-1">Cari No. PR / Kategori</label>
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
                            <button type="submit" class="btn btn-primary btn-sm btn-block font-weight-bold mr-1"><i class="fas fa-filter mr-1"></i> Filter</button>
                            <a href="procurement-ops.php" class="btn btn-secondary btn-sm" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- CARD VIEW DAFTAR PURCHASE REQUISITION OPS -->
        <div class="row">
            <?php if (!empty($list_pr)): ?>
                <?php foreach ($list_pr as $row): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card pr-card p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-primary" style="font-size: 14px;"><?= htmlspecialchars($row['nomor_pr']); ?></span>
                                <?php 
                                    $status = $row['status_approval'];
                                    if ($status === 'Approved') {
                                        echo '<span class="badge badge-success px-2 py-1" style="font-size: 11px;">Approved</span>';
                                    } elseif ($status === 'Pending') {
                                        echo '<span class="badge badge-warning text-dark px-2 py-1" style="font-size: 11px;">Pending</span>';
                                    } else {
                                        echo '<span class="badge badge-secondary px-2 py-1" style="font-size: 11px;">' . $status . '</span>';
                                    }
                                ?>
                            </div>
                            <div class="mb-2 text-secondary" style="font-size: 12px;">
                                <div><i class="fas fa-tag mr-1 text-muted"></i> <strong>Kategori:</strong> <?= htmlspecialchars($row['kategori']); ?></div>
                                <div><i class="far fa-calendar-alt mr-1 text-muted"></i> <strong>Tgl Diinginkan:</strong> <?= date('d/m/Y', strtotime($row['tanggal_diinginkan'] ?? $row['tanggal_pengajuan'])); ?></div>
                                <div><i class="fas fa-money-bill-wave mr-1 text-muted"></i> <strong>Est. Total:</strong> Rp <?= number_format($row['grand_total'] ?? 0, 0, ',', '.'); ?></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <small class="text-muted" style="font-size: 11px;"><i class="far fa-user mr-1"></i> <?= htmlspecialchars($row['pic']); ?></small>
                                <div>
                                    <!-- <button class="btn btn-sm btn-info text-white py-1 px-2" title="Detail" style="font-size: 11px;"><i class="fas fa-eye mr-1"></i> Detail</button> -->

                                        <a href="detail-pr-ops?token=<?= urlencode(encrypt_id($row['id'])); ?>" class="btn btn-sm btn-info text-white py-1 px-2">
                                            <i class="fas fa-eye mr-1"></i> Detail
                                        </a>

                                    <?php if (!empty($row['lampiran_file'])): ?>
                                        <a href="<?= htmlspecialchars($row['lampiran_file']); ?>" target="_blank" class="btn btn-sm btn-secondary py-1 px-2" title="Lampiran" style="font-size: 11px;"><i class="fas fa-paperclip"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card shadow-sm border-0 text-center py-5">
                        <p class="text-muted mb-0">Tidak ada data PR Operasional yang ditemukan.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL FORM TAMBAH PR OPS -->
    <div class="modal fade" id="modalTambahPROps" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title font-weight-bold"><i class="fas fa-file-alt mr-2"></i>Form Purchase Requisition - Ops</h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light" style="max-height: 80vh; overflow-y: auto;">
                    <form action="procurement-ops.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="submit_pr" value="1">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <select class="form-control" name="kategori" required>
                                        <option value="" disabled selected></option>
                                        <option value="Kategori - 1 (< 1 Jt)">Kategori - 1 (&lt; 1 Jt)</option>
                                        <option value="Kategori - 2 (Operational Regular)">Kategori - 2 (Operational Regular)</option>
                                        <option value="Kategori - 3 (Heavy Equipment / Project)">Kategori - 3 (Heavy Equipment / Project)</option>
                                    </select>
                                    <label>Kategori Anggaran</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <select class="form-control" name="status_order" required>
                                        <option value="Normal" selected>Normal</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                    <label>Status Order</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <input type="date" class="form-control" name="tanggal_pengajuan" value="<?= date('Y-m-d'); ?>" required placeholder=" ">
                                    <label>Tanggal Pengajuan</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <input type="date" class="form-control" name="tanggal_diinginkan" value="<?= date('Y-m-d', strtotime('+7 days')); ?>" required placeholder=" ">
                                    <label>Tanggal Diinginkan</label>
                                </div>
                            </div>
                        </div>

                        <!-- CONTAINER DAFTAR ITEM MATERIAL (DINAMIS) -->
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold text-dark mb-0" style="font-size: 13px;"><i class="fas fa-list mr-1"></i> Daftar Material / Suku Cadang</label>
                            <button type="button" class="btn btn-primary btn-sm px-2 py-1" id="addRowOps" style="font-size: 11px;">
                                <i class="fas fa-plus mr-1"></i> Tambah Item
                            </button>
                        </div>

                        <div id="itemContainerOps">
                            <div class="card p-2 mb-2 item-row-ops shadow-sm border">
                                <div class="form-row align-items-center">
                                    <div class="col-md-4 mb-2">
                                        <input type="text" class="form-control form-control-sm" name="item_keterangan[]" placeholder="Nama Barang / Material" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <input type="text" class="form-control form-control-sm" name="item_spesifikasi[]" placeholder="Spesifikasi / Part Number">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <input type="number" step="any" class="form-control form-control-sm" name="item_qty[]" placeholder="Qty" required>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <input type="text" class="form-control form-control-sm" name="item_satuan[]" placeholder="Satuan (Pcs/Pack)" required>
                                    </div>
                                    <div class="col-md-1 mb-2 text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-ops" style="display:none;"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="number" step="any" class="form-control form-control-sm" name="item_harga[]" placeholder="Estimasi Harga Satuan (Rp)" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>

                        <div class="form-label-group">
                            <textarea class="form-control" name="catatan" rows="2" placeholder=" "></textarea>
                            <label>Catatan / Keterangan Lapangan (Opsional)</label>
                        </div>

                        <div class="form-group mb-2">
                            <label class="font-weight-bold small text-secondary mb-1">Lampiran (Foto Part / Drawing / RAB)</label>
                            <input type="file" name="lampiran_file" class="form-control-file form-control-file-sm">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-sm font-weight-bold mt-4">
                            <i class="fas fa-save mr-1"></i> Simpan & Ajukan PR Ops
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Panggil Bottom Bar -->
    <?php include 'includes/bottombar.php'; ?>

    <!-- jQuery, Bootstrap JS, & SweetAlert2 JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JavaScript Pendukung -->
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
        if(closeBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);

        document.getElementById('addRowOps').addEventListener('click', function() {
            const container = document.getElementById('itemContainerOps');
            const newRow = container.querySelector('.item-row-ops').cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => input.value = '');
            newRow.querySelector('.remove-row-ops').style.display = 'inline-block';
            container.appendChild(newRow);
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-row-ops')) {
                const row = e.target.closest('.item-row-ops');
                const totalRows = document.querySelectorAll('.item-row-ops').length;
                if (totalRows > 1) {
                    row.remove();
                } else {
                    Swal.fire('Info', 'Minimal harus ada 1 item material!', 'info');
                }
            }
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

        <?php if (!empty($success_message)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= addslashes($success_message); ?>',
                confirmButtonColor: '#007bff'
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