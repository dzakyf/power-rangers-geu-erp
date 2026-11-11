<?php
// 1. Panggil Header (Session check, CSS, & Koneksi Database)
include 'includes/header.php';
require_once 'db.php'; 

$success_message = '';
$error_message = '';

// 2. PROSES REJECT / CANCEL PR (Hanya untuk status Pending)
if (isset($_GET['action']) && $_GET['action'] === 'reject' && isset($_GET['id'])) {
    $pr_id = intval($_GET['id']);
    try {
        $stmt_check = $pdo->prepare("SELECT status_approval FROM purchase_requisitions WHERE id = :id AND tipe_pengadaan = 'Non-Ops'");
        $stmt_check->execute([':id' => $pr_id]);
        $curr_status = $stmt_check->fetchColumn();

        if ($curr_status === 'Pending') {
            $stmt_rej = $pdo->prepare("UPDATE purchase_requisitions SET status_approval = 'Cancel' WHERE id = :id");
            $stmt_rej->execute([':id' => $pr_id]);
            $success_message = "Dokumen PR berhasil di-reject/cancel.";
        } else {
            $error_message = "Hanya dokumen dengan status Pending yang dapat di-reject!";
        }
    } catch (Exception $e) {
        $error_message = "Gagal melakukan reject: " . $e->getMessage();
    }
}

// 3. PROSES INSERT ATAU UPDATE DATA PR NON-OPS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pr'])) {
    $pr_id              = $_POST['pr_id'] ?? ''; // Jika ada pr_id berarti mode Edit
    $kategori           = trim($_POST['kategori']);
    $status_order       = trim($_POST['status_order']);
    $supplier_id        = trim($_POST['supplier_id']); 
    $delivery_site      = trim($_POST['delivery_site']);
    $pic                = trim($_POST['pic']); 
    $email_pic          = trim($_POST['email_pic']);
    $tanggal_pengajuan  = trim($_POST['tanggal_pengajuan']);
    $tanggal_diinginkan = trim($_POST['tanggal_diinginkan']);
    $catatan            = trim($_POST['catatan']);
    
    $ppn_persen         = floatval($_POST['ppn_persen'] ?? 0);
    $pph_persen         = floatval($_POST['pph_persen'] ?? 0);

    $departemen         = $_SESSION['department'] ?? 'ICT / HRGA';
    $user_id            = $_SESSION['user_id'] ?? NULL;
    $tipe_pengadaan     = 'Non-Ops';

    $item_keterangan    = $_POST['item_keterangan'] ?? [];
    $item_spesifikasi   = $_POST['item_spesifikasi'] ?? [];
    $item_qty           = $_POST['item_qty'] ?? [];
    $item_satuan        = $_POST['item_satuan'] ?? [];
    $item_harga         = $_POST['item_harga'] ?? [];

    if (empty($item_keterangan)) {
        $error_message = "Minimal harus ada 1 item barang yang diinputkan!";
    } else {
        try {
            $pdo->beginTransaction();

            // Hitung Subtotal dari item
            $subtotal_calc = 0;
            for ($i = 0; $i < count($item_keterangan); $i++) {
                if (!empty(trim($item_keterangan[$i]))) {
                    $qty_val  = floatval($item_qty[$i]);
                    $hrg_val  = floatval($item_harga[$i]);
                    $subtotal_calc += ($qty_val * $hrg_val);
                }
            }

            $ppn_calc   = $subtotal_calc * ($ppn_persen / 100);
            $pph_calc   = $subtotal_calc * ($pph_persen / 100);
            $grand_total_calc = $subtotal_calc + $ppn_calc - $pph_calc;

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

            if (!empty($pr_id)) {
                // --- MODE EDIT ---
                $sql_update = "UPDATE purchase_requisitions SET 
                               kategori = :kategori, delivery_site = :delivery_site, supplier_id = :supplier_id, 
                               supplier_name = :supplier_name, tanggal_pengajuan = :tanggal_pengajuan, 
                               tanggal_diinginkan = :tanggal_diinginkan, status_order = :status_order, 
                               pic = :pic, email_pic = :email_pic, subtotal = :subtotal, ppn = :ppn, 
                               pph = :pph, grand_total = :grand_total, catatan = :catatan";
                
                $params_up = [
                    ':kategori'           => $kategori,
                    ':delivery_site'       => $delivery_site,
                    ':supplier_id'         => $supplier_id,
                    ':supplier_name'       => $supplier_id,
                    ':tanggal_pengajuan'  => $tanggal_pengajuan,
                    ':tanggal_diinginkan' => $tanggal_diinginkan,
                    ':status_order'        => $status_order,
                    ':pic'                 => $pic,
                    ':email_pic'           => $email_pic,
                    ':subtotal'            => $subtotal_calc,
                    ':ppn'                 => $ppn_calc,
                    ':pph'                 => $pph_calc,
                    ':grand_total'         => $grand_total_calc,
                    ':catatan'             => $catatan,
                    ':id'                  => $pr_id
                ];

                if ($lampiran_name) {
                    $sql_update .= ", lampiran_file = :lampiran_file";
                    $params_up[':lampiran_file'] = $lampiran_name;
                }
                $sql_update .= " WHERE id = :id AND status_approval = 'Pending'";

                $stmt_up = $pdo->prepare($sql_update);
                $stmt_up->execute($params_up);

                // Hapus item lama lalu masukkan kembali item baru
                $pdo->prepare("DELETE FROM purchase_requisition_items WHERE pr_id = :pr_id")->execute([':pr_id' => $pr_id]);
                $target_pr_id = $pr_id;
                $success_message = "PR Non-Ops berhasil diperbarui!";
            } else {
                // --- MODE TAMBAH BARU ---
                $bulanRomawi = array("", "I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
                $bln = $bulanRomawi[(int) date('m', strtotime($tanggal_pengajuan))];
                $thn = date('Y', strtotime($tanggal_pengajuan));

                $stmt_count = $pdo->query("SELECT COUNT(*) FROM purchase_requisitions WHERE tipe_pengadaan = 'Non-Ops'");
                $next_no = $stmt_count->fetchColumn() + 1;
                $nomor_pr = sprintf("%03d", $next_no) . "/PR-NONOPS/" . $bln . "/" . $thn;

                $sql_header = "INSERT INTO purchase_requisitions 
                               (nomor_pr, kategori, tipe_pengadaan, departemen, delivery_site, supplier_id, supplier_name, tanggal_pengajuan, tanggal_diinginkan, status_order, user_id, pic, email_pic, subtotal, ppn, pph, grand_total, catatan, lampiran_file, status_approval) 
                               VALUES 
                               (:nomor_pr, :kategori, :tipe_pengadaan, :departemen, :delivery_site, :supplier_id, :supplier_name, :tanggal_pengajuan, :tanggal_diinginkan, :status_order, :user_id, :pic, :email_pic, :subtotal, :ppn, :pph, :grand_total, :catatan, :lampiran_file, 'Pending')";
                
                $stmt_h = $pdo->prepare($sql_header);
                $stmt_h->execute([
                    ':nomor_pr'           => $nomor_pr,
                    ':kategori'           => $kategori,
                    ':tipe_pengadaan'      => $tipe_pengadaan,
                    ':departemen'          => $departemen,
                    ':delivery_site'       => $delivery_site,
                    ':supplier_id'         => $supplier_id,
                    ':supplier_name'       => $supplier_id,
                    ':tanggal_pengajuan'  => $tanggal_pengajuan,
                    ':tanggal_diinginkan' => $tanggal_diinginkan,
                    ':status_order'        => $status_order,
                    ':user_id'             => $user_id,
                    ':pic'                 => $pic,
                    ':email_pic'           => $email_pic,
                    ':subtotal'            => $subtotal_calc,
                    ':ppn'                 => $ppn_calc,
                    ':pph'                 => $pph_calc,
                    ':grand_total'         => $grand_total_calc,
                    ':catatan'             => $catatan,
                    ':lampiran_file'       => $lampiran_name
                ]);

                $target_pr_id = $pdo->lastInsertId();
                $success_message = "PR Non-Ops berhasil diajukan dengan Nomor: " . $nomor_pr;
            }

            // Insert Item Detail
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
                        ':pr_id'          => $target_pr_id,
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
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}

// 3. AMBIL DATA SUPPLIER
try {
    $stmt_supp = $pdo->query("SELECT * FROM supplier ORDER BY Company_Name ASC");
    $list_suppliers = $stmt_supp->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $list_suppliers = [];
}

// 4. FILTER & SEARCH
$search     = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

$query = "SELECT * FROM purchase_requisitions WHERE tipe_pengadaan = 'Non-Ops'";
$params = [];

if (!empty($search)) {
    $query .= " AND (nomor_pr LIKE :search OR kategori LIKE :search OR catatan LIKE :search OR delivery_site LIKE :search OR supplier_name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND tanggal_pengajuan BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date']   = $end_date;
}

$query .= " ORDER BY id DESC";

try {
    $stmt_list = $pdo->prepare($query);
    $stmt_list->execute($params);
    $list_pr = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $list_pr = [];
}

$default_pic = $_SESSION['name'] ?? $_SESSION['username'] ?? 'ADMINISTRATOR';
?>

    <!-- CSS Tambahan -->
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
            color: #28a745;
            font-weight: 600;
        }
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
        .item-list-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 12px;
            margin-top: 8px;
            margin-bottom: 8px;
            border-left: 3px solid #28a745;
        }
        .financial-summary-box {
            background: #f1f8f5;
            border: 1px dashed #28a745;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
        }
    </style>

    <!-- Panggil Topbar & Sidebar -->
    <?php include 'includes/topbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <!-- CONTAINER UTAMA PROCUREMENT NON-OPS -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-box text-success mr-2"></i>Procurement - Non Ops</h5>
                <small class="text-muted">Pengadaan barang umum, ATK, inventaris kantor, & pendukung non-operasional.</small>
            </div>
            <button class="btn btn-sm btn-success shadow-sm" onclick="openAddModal()">
                <i class="fas fa-plus mr-1"></i> Buat PR Baru
            </button>
        </div>

        <!-- FORM FILTER & PENCARIAN -->
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
            <div class="card-body p-3">
                <form action="procurement-non-ops" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-secondary mb-1">Cari No. PR / Vendor / Site</label>
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
                            <a href="procurement-non-ops" class="btn btn-secondary btn-sm" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- CARD VIEW DAFTAR PR NON-OPS -->
        <div class="row">
            <?php if (!empty($list_pr)): ?>
                <?php foreach ($list_pr as $row): ?>
                    <?php 
                        $stmt_items = $pdo->prepare("SELECT * FROM purchase_requisition_items WHERE pr_id = :pr_id");
                        $stmt_items->execute([':pr_id' => $row['id']]);
                        $pr_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card pr-card p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-success" style="font-size: 14px;"><?= htmlspecialchars($row['nomor_pr']); ?></span>
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
                                <div><i class="fas fa-store mr-1 text-muted"></i> <strong>Vendor:</strong> <?= htmlspecialchars($row['supplier_name'] ?? '-'); ?></div>
                                <div><i class="fas fa-map-marker-alt mr-1 text-muted"></i> <strong>Delivery Site:</strong> <?= htmlspecialchars($row['delivery_site'] ?? '-'); ?></div>
                                <div><i class="far fa-calendar-alt mr-1 text-muted"></i> <strong>Tgl Diinginkan:</strong> <?= date('d/m/Y', strtotime($row['tanggal_diinginkan'] ?? $row['tanggal_pengajuan'])); ?></div>
                            </div>

                            <!-- Kotak Rincian Item -->
                            <div class="item-list-box">
                                <div class="font-weight-bold text-dark mb-1"><i class="fas fa-list-ul mr-1 text-success"></i> Rincian Barang:</div>
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

                            <!-- Ringkasan Finansial dalam Card -->
                            <div class="bg-light p-2 rounded mb-2" style="font-size: 12px;">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Subtotal:</span>
                                    <span>Rp <?= number_format($row['subtotal'] ?? 0, 0, ',', '.'); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">PPN:</span>
                                    <span>Rp <?= number_format($row['ppn'] ?? 0, 0, ',', '.'); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">PPh:</span>
                                    <span>Rp <?= number_format($row['pph'] ?? 0, 0, ',', '.'); ?></span>
                                </div>
                                <div class="d-flex justify-content-between font-weight-bold text-dark border-top pt-1 mt-1">
                                    <span>Grand Total:</span>
                                    <span class="text-success">Rp <?= number_format($row['grand_total'] ?? 0, 0, ',', '.'); ?></span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <small class="text-muted" style="font-size: 11px;"><i class="far fa-user mr-1"></i> PIC: <?= htmlspecialchars($row['pic']); ?> (<?= htmlspecialchars($row['email_pic'] ?? '-'); ?>)</small>
                                <div>
                                    <a href="detail-pr-non-ops?token=<?= urlencode(encrypt_id($row['id'])); ?>" class="btn btn-sm btn-info text-white py-1 px-2" title="Detail" style="font-size: 11px;"><i class="fas fa-eye mr-1"></i> Detail</a>
                                    
                                    <?php if ($row['status_approval'] === 'Pending'): ?>
                                        <button class="btn btn-sm btn-warning text-dark py-1 px-2" title="Edit" style="font-size: 11px;" onclick='openEditModal(<?= json_encode($row); ?>, <?= json_encode($pr_items); ?>)'>
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger py-1 px-2" title="Reject" style="font-size: 11px;" onclick="confirmReject(<?= $row['id']; ?>)">
                                            <i class="fas fa-ban mr-1"></i> Reject
                                        </button>
                                    <?php endif; ?>

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
                        <p class="text-muted mb-0">Tidak ada data pengajuan PR Non-Ops yang ditemukan.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL FORM TAMBAH / EDIT PR NON-OPS -->
    <div class="modal fade" id="modalTambahPRNonOps" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h6 class="modal-title font-weight-bold" id="modalTitle"><i class="fas fa-file-alt mr-2"></i>Form Purchase Requisition - Non Ops</h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light" style="max-height: 80vh; overflow-y: auto;">
                    <form action="procurement-non-ops" method="POST" enctype="multipart/form-data" id="formPRNonOps">
                        <input type="hidden" name="submit_pr" value="1">
                        <input type="hidden" name="pr_id" id="prIdInput" value="">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <select class="form-control" name="kategori" id="kategoriInput" required>
                                        <option value="" disabled selected></option>
                                        <option value="Kategori - 1 (< 1 Jt)">Kategori - 1 (&lt; 1 Jt - Petty Cash)</option>
                                        <option value="Kategori - 2 (ATK & Inventaris)">Kategori - 2 (ATK & Inventaris Kantor)</option>
                                        <option value="Kategori - 3 (Pengadaan Khusus)">Kategori - 3 (Pengadaan Khusus / Aset)</option>
                                    </select>
                                    <label>Kategori Anggaran</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <select class="form-control" name="supplier_id" id="supplierInput" required>
                                        <option value="" disabled selected></option>
                                        <?php foreach ($list_suppliers as $supp): ?>
                                            <option value="<?= htmlspecialchars($supp['Company_Name']); ?>">
                                                <?= htmlspecialchars($supp['Company_Name']); ?> (<?= htmlspecialchars($supp['Supplier_ID'] ?? ''); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label>Pilih Vendor / Supplier</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <input type="text" class="form-control" name="delivery_site" id="deliverySiteInput" required placeholder=" ">
                                    <label>Delivery Site (Lokasi Pengiriman)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <select class="form-control" name="status_order" id="statusOrderInput" required>
                                        <option value="Normal" selected>Normal</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                    <label>Status Order</label>
                                </div>
                            </div>
                        </div>

                        <!-- INPUT PIC & EMAIL -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <input type="text" class="form-control" name="pic" id="picInput" value="<?= htmlspecialchars($default_pic); ?>" required placeholder=" ">
                                    <label>PIC (Penanggung Jawab)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <input type="email" class="form-control" name="email_pic" id="emailPicInput" required placeholder=" ">
                                    <label>Form Email PIC / Pemohon</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <input type="date" class="form-control" name="tanggal_pengajuan" id="tglPengajuanInput" value="<?= date('Y-m-d'); ?>" required placeholder=" ">
                                    <label>Tanggal Pengajuan</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <input type="date" class="form-control" name="tanggal_diinginkan" id="tglDiinginkanInput" value="<?= date('Y-m-d', strtotime('+7 days')); ?>" required placeholder=" ">
                                    <label>Tanggal Diinginkan</label>
                                </div>
                            </div>
                        </div>

                        <!-- CONTAINER ITEM DINAMIS -->
                        <hr>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="font-weight-bold text-dark mb-0" style="font-size: 13px;"><i class="fas fa-list mr-1"></i> Daftar Item Barang / Material</label>
                            <button type="button" class="btn btn-success btn-sm px-2 py-1" id="addRow" style="font-size: 11px;">
                                <i class="fas fa-plus mr-1"></i> Tambah Item
                            </button>
                        </div>

                        <div id="itemContainer">
                            <div class="card p-2 mb-2 item-row shadow-sm border">
                                <div class="form-row align-items-center">
                                    <div class="col-md-4 mb-2">
                                        <input type="text" class="form-control form-control-sm item-ket" name="item_keterangan[]" placeholder="Nama Barang / Keterangan" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <input type="text" class="form-control form-control-sm item-spec" name="item_spesifikasi[]" placeholder="Spesifikasi">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <input type="number" step="any" class="form-control form-control-sm calc-trigger item-qty" name="item_qty[]" placeholder="Qty" required>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <input type="text" class="form-control form-control-sm item-sat" name="item_satuan[]" placeholder="Satuan (Pcs/Box)" required>
                                    </div>
                                    <div class="col-md-1 mb-2 text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row" style="display:none;"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="number" step="any" class="form-control form-control-sm calc-trigger item-price" name="item_harga[]" placeholder="Estimasi Harga Satuan (Rp)" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KOTAK PENGATURAN PAJAK & RINGKASAN -->
                        <div class="financial-summary-box mb-3">
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <label class="small font-weight-bold text-secondary mb-1">Tarif PPN (%)</label>
                                    <select class="form-control form-control-sm calc-trigger" name="ppn_persen" id="ppnPersen">
                                        <option value="0">0% (Tanpa PPN)</option>
                                        <option value="11" selected>11% (Standar)</option>
                                        <option value="12">12% (Tarif Baru)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small font-weight-bold text-secondary mb-1">Tarif PPh (%)</label>
                                    <select class="form-control form-control-sm calc-trigger" name="pph_persen" id="pphPersen">
                                        <option value="0" selected>0% (Tanpa PPh)</option>
                                        <option value="2">2% (PPh Pasal 23)</option>
                                        <option value="3">3%</option>
                                        <option value="4">4%</option>
                                    </select>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between text-secondary" style="font-size: 12px;">
                                <span>Subtotal Barang:</span>
                                <span id="lblSubtotal" class="font-weight-bold text-dark">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between text-secondary" style="font-size: 12px;">
                                <span>Nominal PPN:</span>
                                <span id="lblPpn" class="font-weight-bold text-dark">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between text-secondary" style="font-size: 12px;">
                                <span>Nominal PPh (Potongan):</span>
                                <span id="lblPph" class="font-weight-bold text-danger">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between font-weight-bold text-success border-top pt-1 mt-1" style="font-size: 14px;">
                                <span>Grand Total:</span>
                                <span id="lblGrandTotal">Rp 0</span>
                            </div>
                        </div>

                        <div class="form-label-group">
                            <textarea class="form-control" name="catatan" id="catatanInput" rows="2" placeholder=" "></textarea>
                            <label>Catatan / Keperluan Umum (Opsional)</label>
                        </div>

                        <div class="form-group mb-2">
                            <label class="font-weight-bold small text-secondary mb-1">Lampiran (Foto / RAB / Drawing)</label>
                            <input type="file" name="lampiran_file" class="form-control-file form-control-file-sm">
                        </div>

                        <button type="submit" class="btn btn-success btn-block btn-sm font-weight-bold mt-4" id="submitBtnText">
                            <i class="fas fa-save mr-1"></i> Simpan & Ajukan PR Non-Ops
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
        if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);

        // Fungsi Buka Modal untuk Add Baru
        function openAddModal() {
            document.getElementById('formPRNonOps').reset();
            document.getElementById('prIdInput').value = '';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-file-alt mr-2"></i>Form Purchase Requisition - Non Ops (Baru)';
            document.getElementById('submitBtnText').innerHTML = '<i class="fas fa-save mr-1"></i> Simpan & Ajukan PR Non-Ops';
            
            // Reset item container ke 1 baris kosong
            const container = document.getElementById('itemContainer');
            container.innerHTML = `
                <div class="card p-2 mb-2 item-row shadow-sm border">
                    <div class="form-row align-items-center">
                        <div class="col-md-4 mb-2"><input type="text" class="form-control form-control-sm item-ket" name="item_keterangan[]" placeholder="Nama Barang / Keterangan" required></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm item-spec" name="item_spesifikasi[]" placeholder="Spesifikasi"></div>
                        <div class="col-md-2 mb-2"><input type="number" step="any" class="form-control form-control-sm calc-trigger item-qty" name="item_qty[]" placeholder="Qty" required></div>
                        <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm item-sat" name="item_satuan[]" placeholder="Satuan (Pcs/Box)" required></div>
                        <div class="col-md-1 mb-2 text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row" style="display:none;"><i class="fas fa-trash"></i></button></div>
                        <div class="col-md-12"><input type="number" step="any" class="form-control form-control-sm calc-trigger item-price" name="item_harga[]" placeholder="Estimasi Harga Satuan (Rp)" required></div>
                    </div>
                </div>`;
            hitungFinansial();
            $('#modalTambahPRNonOps').modal('show');
        }

        // Fungsi Buka Modal untuk Edit Data
        function openEditModal(row, items) {
            document.getElementById('formPRNonOps').reset();
            document.getElementById('prIdInput').value = row.id;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Purchase Requisition: ' + row.nomor_pr;
            document.getElementById('submitBtnText').innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Update Perubahan PR';

            // Isi field header
            document.getElementById('kategoriInput').value = row.kategori;
            document.getElementById('supplierInput').value = row.supplier_name;
            document.getElementById('deliverySiteInput').value = row.delivery_site;
            document.getElementById('statusOrderInput').value = row.status_order;
            document.getElementById('picInput').value = row.pic;
            document.getElementById('emailPicInput').value = row.email_pic;
            document.getElementById('tglPengajuanInput').value = row.tanggal_pengajuan;
            document.getElementById('tglDiinginkanInput').value = row.tanggal_diinginkan;
            document.getElementById('catatanInput').value = row.catatan;

            // Hitung persentase PPN & PPh jika ada
            if(row.subtotal > 0) {
                let ppnRate = Math.round((row.ppn / row.subtotal) * 100);
                let pphRate = Math.round((row.pph / row.subtotal) * 100);
                document.getElementById('ppnPersen').value = ppnRate;
                document.getElementById('pphPersen').value = pphRate;
            }

            // Render ulang item barang
            const container = document.getElementById('itemContainer');
            container.innerHTML = '';
            
            items.forEach((it, index) => {
                const showDel = index > 0 ? 'inline-block' : 'none';
                const htmlRow = `
                    <div class="card p-2 mb-2 item-row shadow-sm border">
                        <div class="form-row align-items-center">
                            <div class="col-md-4 mb-2"><input type="text" class="form-control form-control-sm item-ket" name="item_keterangan[]" value="${it.keterangan || ''}" placeholder="Nama Barang / Keterangan" required></div>
                            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm item-spec" name="item_spesifikasi[]" value="${it.spesifikasi || ''}" placeholder="Spesifikasi"></div>
                            <div class="col-md-2 mb-2"><input type="number" step="any" class="form-control form-control-sm calc-trigger item-qty" name="item_qty[]" value="${it.qty || ''}" placeholder="Qty" required></div>
                            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm item-sat" name="item_satuan[]" value="${it.satuan || ''}" placeholder="Satuan (Pcs/Box)" required></div>
                            <div class="col-md-1 mb-2 text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row" style="display:${showDel};"><i class="fas fa-trash"></i></button></div>
                            <div class="col-md-12"><input type="number" step="any" class="form-control form-control-sm calc-trigger item-price" name="item_harga[]" value="${it.harga_estimasi || ''}" placeholder="Estimasi Harga Satuan (Rp)" required></div>
                        </div>
                    </div>`;
                container.insertAdjacentHTML('beforeend', htmlRow);
            });

            hitungFinansial();
            $('#modalTambahPRNonOps').modal('show');
        }

        // Konfirmasi Reject dengan SweetAlert2
        function confirmReject(id) {
            Swal.fire({
                title: 'Konfirmasi Reject',
                text: 'Apakah Anda yakin ingin mereject dokumen PR ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Reject',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'procurement-non-ops?action=reject&id=' + id;
                }
            });
        }

        // Fungsi Kalkulasi Keuangan Real-Time
        function hitungFinansial() {
            let subtotal = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                let qty = parseFloat(row.querySelector('.item-qty').value) || 0;
                let price = parseFloat(row.querySelector('.item-price').value) || 0;
                subtotal += (qty * price);
            });

            let ppnPersen = parseFloat(document.getElementById('ppnPersen').value) || 0;
            let pphPersen = parseFloat(document.getElementById('pphPersen').value) || 0;

            let ppnNominal = subtotal * (ppnPersen / 100);
            let pphNominal = subtotal * (pphPersen / 100);
            let grandTotal = subtotal + ppnNominal - pphNominal;

            const formatRupiah = (angka) => {
                return 'Rp ' + angka.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            };

            document.getElementById('lblSubtotal').innerText = formatRupiah(subtotal);
            document.getElementById('lblPpn').innerText = formatRupiah(ppnNominal);
            document.getElementById('lblPph').innerText = '- ' + formatRupiah(pphNominal);
            document.getElementById('lblGrandTotal').innerText = formatRupiah(grandTotal);
        }

        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('calc-trigger') || e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) {
                hitungFinansial();
            }
        });

        document.getElementById('ppnPersen').addEventListener('change', hitungFinansial);
        document.getElementById('pphPersen').addEventListener('change', hitungFinansial);

        document.getElementById('addRow').addEventListener('click', function() {
            const container = document.getElementById('itemContainer');
            const newRow = container.querySelector('.item-row').cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => input.value = '');
            newRow.querySelector('.remove-row').style.display = 'inline-block';
            container.appendChild(newRow);
            hitungFinansial();
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-row')) {
                const row = e.target.closest('.item-row');
                const totalRows = document.querySelectorAll('.item-row').length;
                if (totalRows > 1) {
                    row.remove();
                    hitungFinansial();
                } else {
                    Swal.fire('Info', 'Minimal harus ada 1 item barang!', 'info');
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
                        window.location.href = 'logout';
                    }
                });
            });
        }

        <?php if (!empty($success_message)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= addslashes($success_message); ?>',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.href = 'procurement-non-ops';
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
