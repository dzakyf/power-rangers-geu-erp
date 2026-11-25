<?php
// 1. Panggil Header (Session check, CSS, & Koneksi Database)
include 'includes/header.php';
require_once 'db.php'; 

$success_message = '';
$error_message = '';

// Ambil data user yang sedang login dari session untuk mapping Emp_ID & Username
$current_user_session = $_SESSION['username'] ?? $_SESSION['name'] ?? 'ADMIN';
$current_emp_id = 'EMP001'; 
$current_username = $current_user_session;
$session_department = $_SESSION['department'] ?? 'IT DIVISION - 1110 - ICT';

try {
    $stmt_user_info = $pdo->prepare("SELECT * FROM users WHERE username = :uname OR name = :uname LIMIT 1");
    $stmt_user_info->execute([':uname' => $current_user_session]);
    $user_row = $stmt_user_info->fetch(PDO::FETCH_ASSOC);
    if ($user_row) {
        $current_emp_id = $user_row['Emp_ID'] ?? $user_row['id'] ?? 'EMP001';
        $current_username = $user_row['username'] ?? $current_user_session;
        if (!empty($user_row['Department'])) {
            $session_department = $user_row['Department'];
        }
    }
} catch (Exception $e) {}

// 2. PROSES REJECT / CANCEL PR
if (isset($_GET['action']) && $_GET['action'] === 'reject' && isset($_GET['id'])) {
    $pr_id = intval($_GET['id']);
    try {
        $stmt_check = $pdo->prepare("SELECT PR_No, Status FROM purchase_request WHERE ID = :id");
        $stmt_check->execute([':id' => $pr_id]);
        $pr_data = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($pr_data && (strcasecmp($pr_data['Status'], 'APPROVAL_UPPER') === 0 || strcasecmp($pr_data['Status'], 'Pending') === 0)) {
            $pr_no = $pr_data['PR_No'];

            $stmt_rej = $pdo->prepare("UPDATE purchase_request SET Status = 'Cancel' WHERE ID = :id");
            $stmt_rej->execute([':id' => $pr_id]);

            $stmt_log = $pdo->prepare("INSERT INTO purchase_request_log 
                (PR_No, Status, Action_By, Action_Date, Action_Status, Note) 
                VALUES (:pr_no, 'Cancel', :action_by, NOW(), 'Cancel', 'Dokumen PR di-reject oleh user')");
            $stmt_log->execute([
                ':pr_no'     => $pr_no,
                ':action_by' => $current_username
            ]);

            $success_message = "Dokumen PR berhasil di-reject/cancel.";
        } else {
            $error_message = "Hanya dokumen dengan status APPROVAL_UPPER/Pending yang dapat di-reject!";
        }
    } catch (Exception $e) {
        $error_message = "Gagal melakukan reject: " . $e->getMessage();
    }
}

// 3. PROSES INSERT ATAU UPDATE DATA PR NON-OPS (Tanpa Input Vendor di Awal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_pr'])) {
    $pr_id              = $_POST['pr_id'] ?? ''; 
    $category           = trim($_POST['kategori']);
    $sub_category       = trim($_POST['sub_category']); 
    $priority_status    = trim($_POST['status_order']); 
    $department_input   = trim($_POST['department']); 
    $location           = trim($_POST['delivery_site']);
    $user_request_name  = trim($_POST['pic']); 
    $pic_address        = trim($_POST['pic_address']);
    $pic_phone          = trim($_POST['pic_phone']);
    $user_request_email = trim($_POST['email_pic']);
    $pr_date            = trim($_POST['tanggal_pengajuan']);
    $remark             = trim($_POST['catatan']);
    
    $supplies_type      = 'Non-Ops';

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

            $estimated_budget = 0;
            for ($i = 0; $i < count($item_keterangan); $i++) {
                if (!empty(trim($item_keterangan[$i]))) {
                    $qty_val  = floatval($item_qty[$i]);
                    $hrg_val  = floatval($item_harga[$i]);
                    $estimated_budget += ($qty_val * $hrg_val);
                }
            }

            $attachment_name = NULL;
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
                        $attachment_name = $dest_path;
                    }
                }
            }

            if (!empty($pr_id)) {
                // --- MODE EDIT ---
                $stmt_old = $pdo->prepare("SELECT PR_No FROM purchase_request WHERE ID = :id");
                $stmt_old->execute([':id' => $pr_id]);
                $pr_no = $stmt_old->fetchColumn();

                $sql_update = "UPDATE purchase_request SET 
                               Category = :category, Sub_Category = :sub_category, Location = :location, 
                               Department = :department, User_Request = :user_request, User_Request_Name = :user_request_name, 
                               PR_Date = :pr_date, Estimated_Budget = :estimated_budget, Priority_Status = :priority_status, 
                               PIC = :pic, PIC_Address = :pic_address, PIC_Phone = :pic_phone, Remark = :remark
                               WHERE ID = :id AND Status IN ('Pending', 'APPROVAL_UPPER')";
                
                $stmt_up = $pdo->prepare($sql_update);
                $stmt_up->execute([
                    ':category'         => $category,
                    ':sub_category'     => $sub_category,
                    ':location'         => 'GEU-HO',
                    ':department'       => $department_input,
                    ':user_request'     => $current_emp_id,
                    ':user_request_name'=> $current_username,
                    ':pr_date'          => $pr_date,
                    ':estimated_budget' => $estimated_budget,
                    ':priority_status'  => $priority_status,
                    ':pic'              => $user_request_name,
                    ':pic_address'      => $pic_address,
                    ':pic_phone'        => $pic_phone,
                    ':remark'           => $remark,
                    ':id'               => $pr_id
                ]);

                $pdo->prepare("DELETE FROM purchase_request_dtl WHERE PR_No = :pr_no")->execute([':pr_no' => $pr_no]);
                $target_pr_no = $pr_no;
                
                $stmt_log = $pdo->prepare("INSERT INTO purchase_request_log 
                    (PR_No, Location, Category, Sub_Category, PIC, PR_Date, Department, Status, Note, Action_By, Action_Date, Action_Status) 
                    VALUES (:pr_no, :location, :category, :sub_category, :pic, :pr_date, :department, 'APPROVAL_UPPER', :note, :action_by, NOW(), 'Edit')");
                $stmt_log->execute([
                    ':pr_no'        => $target_pr_no,
                    ':location'     => 'GEU-HO',
                    ':category'     => $category,
                    ':sub_category' => $sub_category,
                    ':pic'          => $user_request_name,
                    ':pr_date'      => $pr_date,
                    ':department'   => $department_input,
                    ':note'         => $remark,
                    ':action_by'    => $current_username
                ]);

                $success_message = "PR Non-Ops berhasil diperbarui!";
            } else {
                // --- MODE TAMBAH BARU ---
                $bulanRomawi = array("", "I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
                $bln = $bulanRomawi[(int) date('m', strtotime($pr_date))];
                $thn = date('Y', strtotime($pr_date));

                $stmt_count = $pdo->query("SELECT COUNT(*) FROM purchase_request WHERE Supplies_Type = 'Non-Ops'");
                $next_no = $stmt_count->fetchColumn() + 1;
                $target_pr_no = sprintf("%03d", $next_no) . "/PR-NONOPS/" . $bln . "/" . $thn;

                $sql_header = "INSERT INTO purchase_request 
                               (PR_No, Category, Sub_Category, Supplies_Type, Department, Location, User_Request, User_Request_Name, PR_Date, Priority_Status, Created_By, PIC, PIC_Address, PIC_Phone, Estimated_Budget, Remark, Status) 
                               VALUES 
                               (:pr_no, :category, :sub_category, :supplies_type, :department, :location, :user_request, :user_request_name, :pr_date, :priority_status, :created_by, :pic, :pic_address, :pic_phone, :estimated_budget, :remark, 'PENDING')";
                
                $stmt_h = $pdo->prepare($sql_header);
                $stmt_h->execute([
                    ':pr_no'           => $target_pr_no,
                    ':category'        => $category,
                    ':sub_category'    => $sub_category,
                    ':supplies_type'   => $supplies_type,
                    ':department'      => $department_input,
                    ':location'        => 'GEU-HO',
                    ':user_request'    => $current_emp_id,
                    ':user_request_name'=> $current_username,
                    ':pr_date'         => $pr_date,
                    ':priority_status' => $priority_status,
                    ':created_by'      => $current_username,
                    ':pic'             => $user_request_name,
                    ':pic_address'     => $pic_address,
                    ':pic_phone'       => $pic_phone,
                    ':estimated_budget'=> $estimated_budget,
                    ':remark'          => $remark
                ]);

                $stmt_log = $pdo->prepare("INSERT INTO purchase_request_log 
                    (PR_No, Location, Category, Sub_Category, PIC, PR_Date, Department, Status, Note, Action_By, Action_Date, Action_Status) 
                    VALUES (:pr_no, :location, :category, :sub_category, :pic, :pr_date, :department, 'APPROVAL_UPPER', :note, :action_by, NOW(), 'Created')");
                $stmt_log->execute([
                    ':pr_no'        => $target_pr_no,
                    ':location'     => 'GEU-HO',
                    ':category'     => $category,
                    ':sub_category' => $sub_category,
                    ':pic'          => $user_request_name,
                    ':pr_date'      => $pr_date,
                    ':department'   => $department_input,
                    ':note'         => $remark,
                    ':action_by'    => $current_username
                ]);

                $success_message = "PR Non-Ops berhasil diajukan dengan Nomor: " . $target_pr_no;
            }

            // Insert Item Detail ke purchase_request_dtl
            $sql_item = "INSERT INTO purchase_request_dtl 
                         (PR_No, Purchase_Category, Purchase_Type, Category, Product_Desc, Specification, Qty, Unit, Default_Price, Prices, Reference, Attachment) 
                         VALUES 
                         (:pr_no, :purchase_category, :purchase_type, :category, :product_desc, :specification, :qty, :unit, :default_price, :prices, :reference, :attachment)";
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
                        ':pr_no'             => $target_pr_no,
                        ':purchase_category' => $sub_category, 
                        ':purchase_type'     => $supplies_type,     
                        ':category'          => $category,
                        ':product_desc'      => $ket,
                        ':specification'     => $spec,
                        ':qty'               => $qty,
                        ':unit'              => $sat,
                        ':default_price'     => $hrg,
                        ':prices'            => $total,
                        ':reference'         => $remark ?: '-',     
                        ':attachment'        => $attachment_name ?? '' 
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

// 5. AMBIL LIST DEPARTEMEN
$list_departments = [
    "DIRECTOR - 0000 - DIR",
    "OPERATION DIVISION - 1100 - OPS",
    "IT DIVISION - 1110 - ICT",
    "GENERAL AFFAIR - 2210 - GEA",
    "CORPORATE SERVICE DIVISION - 1100 - CSV",
    "COMMERCIAL AND SUSTAINABILITY - 2310 - CNS"
];
try {
    $stmt_dept = $pdo->query("SELECT DISTINCT Department FROM users WHERE Department IS NOT NULL AND Department != '' ORDER BY Department ASC");
    $dept_db = $stmt_dept->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($dept_db)) {
        $list_departments = array_unique(array_merge($list_departments, $dept_db));
    }
} catch (Exception $e) {}

// 6. FILTER & SEARCH
$search     = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

$query = "SELECT * FROM purchase_request WHERE Supplies_Type = 'Non-Ops'";
$params = [];

if (!empty($search)) {
    $query .= " AND (PR_No LIKE :search OR Category LIKE :search OR Remark LIKE :search OR Location LIKE :search OR Department LIKE :search)";
    $params[':search'] = '%' . $search . '%';
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
                            <label class="small font-weight-bold text-secondary mb-1">Cari No. PR / Site</label>
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
                        $stmt_items = $pdo->prepare("SELECT * FROM purchase_request_dtl WHERE PR_No = :pr_no");
                        $stmt_items->execute([':pr_no' => $row['PR_No']]);
                        $pr_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                        $attachment_file = !empty($pr_items[0]['Attachment']) ? $pr_items[0]['Attachment'] : null;
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card pr-card p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-success" style="font-size: 14px;"><?= htmlspecialchars($row['PR_No']); ?></span>
                                <?php 
                                    $status = $row['Status'];
                                    if (strcasecmp($status, 'APPROVED') === 0) {
                                        echo '<span class="badge badge-success px-2 py-1" style="font-size: 11px;">APPROVED</span>';
                                    } elseif (strcasecmp($status, 'PENDING') === 0 || strcasecmp($status, 'Pending') === 0) {
                                        echo '<span class="badge badge-warning text-dark px-2 py-1" style="font-size: 11px;">PENDING</span>';
                                    } elseif (strcasecmp($status, 'Hold') === 0) {
                                        echo '<span class="badge badge-secondary px-2 py-1" style="font-size: 11px;">Hold</span>';
                                    } else {
                                        echo '<span class="badge badge-danger px-2 py-1" style="font-size: 11px;">' . htmlspecialchars($status) . '</span>';
                                    }
                                ?>
                            </div>
                            <div class="mb-2 text-secondary" style="font-size: 12px;">
                                <div><i class="fas fa-tag mr-1 text-muted"></i> <strong>Kategori:</strong> <?= htmlspecialchars($row['Category']); ?> (<?= htmlspecialchars($row['Sub_Category'] ?? '-'); ?>)</div>
                                <div><i class="fas fa-building mr-1 text-muted"></i> <strong>Dept:</strong> <?= htmlspecialchars($row['Department'] ?? '-'); ?></div>
                                <div><i class="fas fa-map-marker-alt mr-1 text-muted"></i> <strong>Delivery Site:</strong> <?= htmlspecialchars($row['Location'] ?? '-'); ?></div>
                                <div><i class="far fa-calendar-alt mr-1 text-muted"></i> <strong>Tgl Pengajuan:</strong> <?= date('d/m/Y', strtotime($row['PR_Date'])); ?></div>
                            </div>

                            <!-- Kotak Rincian Item -->
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

                            <!-- Ringkasan Finansial dalam Card -->
                            <div class="bg-light p-2 rounded mb-2" style="font-size: 12px;">
                                <div class="d-flex justify-content-between font-weight-bold text-dark">
                                    <span>Estimated Budget / Total:</span>
                                    <span class="text-success">Rp <?= number_format($row['Estimated_Budget'] ?? 0, 0, ',', '.'); ?></span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <small class="text-muted" style="font-size: 11px;"><i class="far fa-user mr-1"></i> PIC: <?= htmlspecialchars($row['PIC']); ?></small>
                                <div>
                                   
                                    
                                    <?php if (strcasecmp($row['Status'], 'APPROVAL_UPPER') === 0 || strcasecmp($row['Status'], 'Pending') === 0): ?>
                                        <button class="btn btn-sm btn-warning text-dark py-1 px-2" title="Edit" style="font-size: 11px;" onclick='openEditModal(<?= json_encode($row); ?>, <?= json_encode($pr_items); ?>)'>
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger py-1 px-2" title="Reject" style="font-size: 11px;" onclick="confirmReject(<?= $row['ID']; ?>)">
                                            <i class="fas fa-ban mr-1"></i> Reject
                                        </button>
                                    <?php endif; ?>

                                    <?php if (!empty($attachment_file)): ?>
                                        <a href="<?= htmlspecialchars($attachment_file); ?>" target="_blank" class="btn btn-sm btn-secondary py-1 px-2" title="Lampiran" style="font-size: 11px;"><i class="fas fa-paperclip"></i></a>
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
                        
                        <!-- PILIHAN DEPARTEMEN -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-label-group">
                                    <select class="form-control" name="department" id="departmentInput" required>
                                        <option value="" disabled selected></option>
                                        <?php foreach ($list_departments as $dept): ?>
                                            <option value="<?= htmlspecialchars($dept); ?>" <?= ($dept === $session_department) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($dept); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label>Departemen (Pembebanan Budget)</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <select class="form-control" name="kategori" id="kategoriInput" required>
                                        <option value="" disabled selected></option>
                                        <option value="ASSET">ASSET</option>
                                        <option value="NON ASSET">NON ASSET</option>
                                    </select>
                                    <label>Kategori Anggaran</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-label-group">
                                    <select class="form-control" name="sub_category" id="subCategoryInput" required>
                                        <option value="" disabled selected></option>
                                        <option value="BARANG">BARANG</option>
                                        <option value="JASA">JASA</option>
                                    </select>
                                    <label>Sub Category (Barang / Jasa)</label>
                                </div>
                            </div>
                        </div>

                        <!-- INPUT LOKASI PENGIRIMAN (Form Vendor Dihilangkan) -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-label-group">
                                    <input type="text" class="form-control" name="delivery_site" id="deliverySiteInput" required placeholder=" ">
                                    <label>Delivery Site (Lokasi Pengiriman)</label>
                                </div>
                            </div>
                        </div>

                        <!-- INPUT PIC, PHONE & ADDRESS -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-label-group">
                                    <input type="text" class="form-control" name="pic" id="picInput" value="<?= htmlspecialchars($default_pic); ?>" required placeholder=" ">
                                    <label>PIC (Penanggung Jawab)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-label-group">
                                    <input type="text" class="form-control" name="pic_phone" id="picPhoneInput" required placeholder=" ">
                                    <label>PIC Phone (No. Telp)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-label-group">
                                    <input type="email" class="form-control" name="email_pic" id="emailPicInput" required placeholder=" ">
                                    <label>Email Pemohon</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-label-group">
                                    <input type="text" class="form-control" name="pic_address" id="picAddressInput" required placeholder=" ">
                                    <label>PIC Address (Alamat PIC)</label>
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
                                    <select class="form-control" name="status_order" id="statusOrderInput" required>
                                        <option value="NORMAL" selected>NORMAL</option>
                                        <option value="URGENT">URGENT</option>
                                    </select>
                                    <label>Status Order (Priority)</label>
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
            document.getElementById('prIdInput').value = row.ID;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Purchase Requisition: ' + row.PR_No;
            document.getElementById('submitBtnText').innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Update Perubahan PR';

            document.getElementById('departmentInput').value = row.Department || '';
            document.getElementById('kategoriInput').value = row.Category;
            document.getElementById('subCategoryInput').value = row.Sub_Category || '';
            document.getElementById('deliverySiteInput').value = row.Location;
            document.getElementById('statusOrderInput').value = row.Priority_Status || 'NORMAL';
            document.getElementById('picInput').value = row.PIC;
            document.getElementById('picPhoneInput').value = row.PIC_Phone || '';
            document.getElementById('picAddressInput').value = row.PIC_Address || '';
            document.getElementById('tglPengajuanInput').value = row.PR_Date;
            document.getElementById('catatanInput').value = row.Remark;

            const container = document.getElementById('itemContainer');
            container.innerHTML = '';
            
            items.forEach((it, index) => {
                const showDel = index > 0 ? 'inline-block' : 'none';
                const htmlRow = `
                    <div class="card p-2 mb-2 item-row shadow-sm border">
                        <div class="form-row align-items-center">
                            <div class="col-md-4 mb-2"><input type="text" class="form-control form-control-sm item-ket" name="item_keterangan[]" value="${it.Product_Desc || ''}" placeholder="Nama Barang / Keterangan" required></div>
                            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm item-spec" name="item_spesifikasi[]" value="${it.Specification || ''}" placeholder="Spesifikasi"></div>
                            <div class="col-md-2 mb-2"><input type="number" step="any" class="form-control form-control-sm calc-trigger item-qty" name="item_qty[]" value="${it.Qty || ''}" placeholder="Qty" required></div>
                            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm item-sat" name="item_satuan[]" value="${it.Unit || ''}" placeholder="Satuan (Pcs/Box)" required></div>
                            <div class="col-md-1 mb-2 text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row" style="display:${showDel};"><i class="fas fa-trash"></i></button></div>
                            <div class="col-md-12"><input type="number" step="any" class="form-control form-control-sm calc-trigger item-price" name="item_harga[]" value="${it.Default_Price || ''}" placeholder="Estimasi Harga Satuan (Rp)" required></div>
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