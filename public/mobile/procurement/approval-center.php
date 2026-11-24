<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

$success_message = '';
$error_message   = '';

$current_username = $_SESSION['username'] ?? $_SESSION['name'] ?? 'MANAGER_APPROVAL';
$current_user_id  = $_SESSION['user_id'] ?? null;

// Ambil posisi user langsung dari tabel users dengan fallback ke $_SESSION
$userPositionRaw = trim((string)($_SESSION['position'] ?? $_SESSION['role'] ?? ''));

if (isset($pdo) && ($pdo instanceof PDO)) {
    try {
        if (!empty($current_user_id)) {
            $stmtUser = $pdo->prepare("SELECT Position, username, Name FROM users WHERE id = :id LIMIT 1");
            $stmtUser->execute([':id' => $current_user_id]);
            $uRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmtUser = $pdo->prepare("SELECT Position, username, Name FROM users WHERE username = :username LIMIT 1");
            $stmtUser->execute([':username' => $current_username]);
            $uRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
        }

        if ($uRow && isset($uRow['Position']) && trim((string)$uRow['Position']) !== '') {
            $userPositionRaw = trim((string)$uRow['Position']);
        }
    } catch (PDOException $e) {
        error_log('Gagal mengambil posisi user: ' . $e->getMessage());
    }
}

$currentUserCtx = [
    'username' => $current_username,
    'position' => strtoupper($userPositionRaw),
];

// Helper untuk mengecek apakah posisi user yang login cocok dengan task aktif
function canUserApproveTask(?array $activeTask, array $userCtx): bool
{
    if (!$activeTask) {
        return false;
    }

    $uPos = strtoupper(trim((string)($userCtx['position'] ?? '')));
    if ($uPos === '') {
        return false;
    }

    $taskPosId   = strtoupper(trim((string)($activeTask['Position_ID'] ?? '')));
    $taskPosCode = strtoupper(trim((string)($activeTask['Position_Code'] ?? '')));
    $taskPosName = strtoupper(trim((string)($activeTask['Position_Name'] ?? '')));

    if ($taskPosName !== '' && $uPos === $taskPosName) {
        return true;
    }

    if ($taskPosCode !== '' && $uPos === $taskPosCode) {
        return true;
    }

    if ($taskPosId !== '' && $uPos === $taskPosId) {
        return true;
    }

    return false;
}

// Helper untuk mengambil data approval_request & task PENDING aktif dari PR
function getPrApprovalContext(PDO $pdo, array $prRow, bool $forUpdate = false): array
{
    $reqRow = null;

    if (!empty($prRow['Approval_Request_ID'])) {
        $sql = $forUpdate
            ? "SELECT * FROM approval_request WHERE ID = :req_id LIMIT 1 FOR UPDATE"
            : "SELECT * FROM approval_request WHERE ID = :req_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':req_id' => $prRow['Approval_Request_ID']]);
        $reqRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    if (!$reqRow && !empty($prRow['PR_No'])) {
        $sql = $forUpdate
            ? "SELECT * FROM approval_request WHERE Request_No = :pr_no ORDER BY ID DESC LIMIT 1 FOR UPDATE"
            : "SELECT * FROM approval_request WHERE Request_No = :pr_no ORDER BY ID DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pr_no' => $prRow['PR_No']]);
        $reqRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    $activeTask = null;
    if ($reqRow) {
        $sqlTask = $forUpdate
            ? "SELECT * FROM approval_request_task
               WHERE Request_ID = :req_id
                 AND UPPER(COALESCE(Status, '')) = 'PENDING'
               ORDER BY Stage_Order ASC, Level_Number ASC, ID ASC
               LIMIT 1 FOR UPDATE"
            : "SELECT * FROM approval_request_task
               WHERE Request_ID = :req_id
                 AND UPPER(COALESCE(Status, '')) = 'PENDING'
               ORDER BY Stage_Order ASC, Level_Number ASC, ID ASC
               LIMIT 1";
        $stmtTask = $pdo->prepare($sqlTask);
        $stmtTask->execute([':req_id' => $reqRow['ID']]);
        $activeTask = $stmtTask->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    return [
        'request'     => $reqRow,
        'active_task' => $activeTask,
    ];
}

// Helper untuk mengambil daftar riwayat task approval pada request terkait
function getPrApprovalHistory(PDO $pdo, ?array $reqRow): array
{
    if (!$reqRow || empty($reqRow['ID'])) {
        return [];
    }

    $stmt = $pdo->prepare("
        SELECT *
        FROM approval_request_task
        WHERE Request_ID = :req_id
        ORDER BY Stage_Order ASC, Level_Number ASC, ID ASC
    ");
    $stmt->execute([':req_id' => $reqRow['ID']]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

// Helper untuk mencari & membuat task tahap berikutnya dari Policy_Snapshot['Steps']
function generateNextTaskFromSnapshot(PDO $pdo, array $reqRow, int $completedStageOrder): ?array
{
    $snapshotJson = $reqRow['Policy_Snapshot'] ?? '';
    if (!is_string($snapshotJson) || trim($snapshotJson) === '') {
        return null;
    }

    $decoded = json_decode($snapshotJson, true);
    if (!is_array($decoded) || empty($decoded['Steps']) || !is_array($decoded['Steps'])) {
        return null;
    }

    $steps = $decoded['Steps'];
    usort($steps, function ($a, $b) {
        $sa = (int)($a['Stage_Order'] ?? 0);
        $sb = (int)($b['Stage_Order'] ?? 0);
        if ($sa !== $sb) {
            return $sa <=> $sb;
        }
        $la = (int)($a['Level_Number'] ?? 0);
        $lb = (int)($b['Level_Number'] ?? 0);
        if ($la !== $lb) {
            return $la <=> $lb;
        }
        return ((int)($a['ID'] ?? 0)) <=> ((int)($b['ID'] ?? 0));
    });

    $nextStep = null;
    foreach ($steps as $step) {
        $stepStage = (int)($step['Stage_Order'] ?? 0);
        if ($stepStage > $completedStageOrder) {
            $nextStep = $step;
            break;
        }
    }

    if (!$nextStep) {
        return null;
    }

    $nextStageOrder  = (int)($nextStep['Stage_Order'] ?? ($completedStageOrder + 1));
    $nextLevelNumber = isset($nextStep['Level_Number']) && $nextStep['Level_Number'] !== null
        ? (int)$nextStep['Level_Number']
        : (int)($reqRow['Current_Level'] ?? 1);
    $posId           = isset($nextStep['Position_ID']) && $nextStep['Position_ID'] !== null
        ? (int)$nextStep['Position_ID']
        : null;
    $posCode         = isset($nextStep['Position_Code']) && trim((string)$nextStep['Position_Code']) !== ''
        ? trim((string)$nextStep['Position_Code'])
        : null;
    $posName         = isset($nextStep['Label']) && trim((string)$nextStep['Label']) !== ''
        ? trim((string)$nextStep['Label'])
        : ($posCode ?? 'Approver');
    $dept            = isset($nextStep['Department']) && trim((string)$nextStep['Department']) !== ''
        ? trim((string)$nextStep['Department'])
        : ($reqRow['Department'] ?? null);
    $approvalMode    = isset($nextStep['Approval_Mode']) && trim((string)$nextStep['Approval_Mode']) !== ''
        ? trim((string)$nextStep['Approval_Mode'])
        : 'ALL';
    $quorum          = isset($nextStep['Quorum']) && $nextStep['Quorum'] !== null
        ? (int)$nextStep['Quorum']
        : null;

    // Cegah duplikasi task pada Stage_Order yang sama jika sudah ada
    $stmtCheck = $pdo->prepare("
        SELECT * FROM approval_request_task
        WHERE Request_ID = :req_id
          AND Stage_Order = :stage_order
        ORDER BY ID DESC
        LIMIT 1
    ");
    $stmtCheck->execute([
        ':req_id'      => $reqRow['ID'],
        ':stage_order' => $nextStageOrder,
    ]);
    $existingTask = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if ($existingTask) {
        return $existingTask;
    }

    $stmtInsert = $pdo->prepare("
        INSERT INTO approval_request_task (
            Request_ID,
            Stage_Order,
            Level_Number,
            Position_ID,
            Position_Code,
            Position_Name,
            Department,
            Approval_Mode,
            Quorum,
            Status,
            Is_Self_Approval,
            Created_At,
            Updated_At
        ) VALUES (
            :request_id,
            :stage_order,
            :level_number,
            :position_id,
            :position_code,
            :position_name,
            :department,
            :approval_mode,
            :quorum,
            'PENDING',
            0,
            NOW(),
            NOW()
        )
    ");
    $stmtInsert->execute([
        ':request_id'    => $reqRow['ID'],
        ':stage_order'   => $nextStageOrder,
        ':level_number'  => $nextLevelNumber,
        ':position_id'   => $posId,
        ':position_code' => $posCode,
        ':position_name' => $posName,
        ':department'    => $dept,
        ':approval_mode' => $approvalMode,
        ':quorum'        => $quorum,
    ]);

    $newTaskId = (int)$pdo->lastInsertId();
    $stmtNew   = $pdo->prepare("SELECT * FROM approval_request_task WHERE ID = :id LIMIT 1");
    $stmtNew->execute([':id' => $newTaskId]);

    return $stmtNew->fetch(PDO::FETCH_ASSOC) ?: null;
}

// 2. PROSES UPDATE STATUS APPROVAL BERJENJANG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_approval'])) {
    $pr_id           = intval($_POST['pr_id']);
    $status_approval = trim($_POST['status_approval']); // Approved, Hold, Cancel
    $komentar        = trim($_POST['komentar_approval']);
    $actor_name      = $_SESSION['username'] ?? $_SESSION['name'] ?? $current_username;

    try {
        $pdo->beginTransaction();

        $stmt_pr = $pdo->prepare("SELECT * FROM purchase_request WHERE ID = :id FOR UPDATE");
        $stmt_pr->execute([':id' => $pr_id]);
        $pr_data = $stmt_pr->fetch(PDO::FETCH_ASSOC);

        if (!$pr_data) {
            throw new Exception("Dokumen PR tidak ditemukan.");
        }

        $pr_no           = $pr_data['PR_No'];
        $final_pr_status = $status_approval;
        $note_text       = $komentar ?: ('Approval decision updated to ' . $status_approval);

        $ctx         = getPrApprovalContext($pdo, $pr_data, true);
        $req_row     = $ctx['request'];
        $active_task = $ctx['active_task'];

        if ($req_row) {
            if (!$active_task) {
                throw new Exception("Tidak ada task approval PENDING aktif pada dokumen PR ini.");
            }

            if (!canUserApproveTask($active_task, $currentUserCtx)) {
                $requiredPos = $active_task['Position_Name'] ?? ($active_task['Position_Code'] ?? 'Posisi terkait');
                $myPosLabel  = $userPositionRaw !== '' ? $userPositionRaw : 'Tidak ada posisi';
                throw new Exception("Akses ditolak: tahap ini khusus untuk posisi {$requiredPos} (posisi Anda: {$myPosLabel}).");
            }

            $req_id         = $req_row['ID'];
            $completedStage = (int)($active_task['Stage_Order'] ?? ($req_row['Current_Stage'] ?? 1));

            if (strcasecmp($status_approval, 'Approved') === 0) {
                $pdo->prepare("
                    UPDATE approval_request_task
                    SET Status = 'APPROVED',
                        Action = 'APPROVED',
                        Acted_By = :acted_by,
                        Acted_At = NOW(),
                        Notes = :notes,
                        Updated_At = NOW()
                    WHERE ID = :task_id
                ")->execute([
                    ':acted_by' => $actor_name,
                    ':notes'    => $note_text,
                    ':task_id'  => $active_task['ID'],
                ]);

                // Cek apakah masih ada task PENDING pada stage/level berikutnya di tabel
                $stmt_next = $pdo->prepare("
                    SELECT * FROM approval_request_task
                    WHERE Request_ID = :req_id
                      AND UPPER(COALESCE(Status, '')) = 'PENDING'
                    ORDER BY Stage_Order ASC, Level_Number ASC, ID ASC
                    LIMIT 1
                ");
                $stmt_next->execute([':req_id' => $req_id]);
                $next_task = $stmt_next->fetch(PDO::FETCH_ASSOC);

                // Jika belum ada task PENDING berikutnya di tabel, coba generate dari Policy_Snapshot['Steps']
                if (!$next_task) {
                    $next_task = generateNextTaskFromSnapshot($pdo, $req_row, $completedStage);
                }

                if ($next_task) {
                    $final_pr_status = 'APPROVAL_UPPER';
                    $pdo->prepare("
                        UPDATE approval_request
                        SET Status = 'IN_PROGRESS',
                            Current_Stage = :stage,
                            Current_Level = :level,
                            Updated_At = NOW()
                        WHERE ID = :req_id
                    ")->execute([
                        ':stage'  => $next_task['Stage_Order'] ?? ($completedStage + 1),
                        ':level'  => $next_task['Level_Number'] ?? ($req_row['Current_Level'] ?? 1),
                        ':req_id' => $req_id,
                    ]);
                } else {
                    // Seluruh tahapan approval sudah selesai
                    $final_pr_status = 'APPROVED';
                    $pdo->prepare("
                        UPDATE approval_request
                        SET Status = 'APPROVED',
                            Completed_At = NOW(),
                            Updated_At = NOW()
                        WHERE ID = :req_id
                    ")->execute([':req_id' => $req_id]);
                }
            } elseif (strcasecmp($status_approval, 'Cancel') === 0 || strcasecmp($status_approval, 'REJECTED') === 0) {
                $pdo->prepare("
                    UPDATE approval_request_task
                    SET Status = 'REJECTED',
                        Action = 'REJECTED',
                        Acted_By = :acted_by,
                        Acted_At = NOW(),
                        Notes = :notes,
                        Updated_At = NOW()
                    WHERE ID = :task_id
                ")->execute([
                    ':acted_by' => $actor_name,
                    ':notes'    => $note_text,
                    ':task_id'  => $active_task['ID'],
                ]);

                $pdo->prepare("
                    UPDATE approval_request
                    SET Status = 'REJECTED',
                        Cancelled_At = NOW(),
                        Updated_At = NOW()
                    WHERE ID = :req_id
                ")->execute([':req_id' => $req_id]);

                $final_pr_status = 'Cancel';
            } elseif (strcasecmp($status_approval, 'Hold') === 0) {
                $pdo->prepare("
                    UPDATE approval_request_task
                    SET Held_At = NOW(),
                        Held_Reason = :reason,
                        Notes = :notes,
                        Updated_At = NOW()
                    WHERE ID = :task_id
                ")->execute([
                    ':reason'  => $note_text,
                    ':notes'   => $note_text,
                    ':task_id' => $active_task['ID'],
                ]);

                $pdo->prepare("
                    UPDATE approval_request
                    SET Updated_At = NOW()
                    WHERE ID = :req_id
                ")->execute([':req_id' => $req_id]);

                $final_pr_status = 'Hold';
            }
        }

        // Update status di tabel utama purchase_request
        $stmt_up = $pdo->prepare("UPDATE purchase_request SET Status = :status WHERE ID = :id");
        $stmt_up->execute([
            ':status' => $final_pr_status,
            ':id'     => $pr_id,
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
            ':status'        => $final_pr_status,
            ':note'          => $note_text,
            ':action_by'     => $actor_name,
            ':action_status' => $final_pr_status,
        ]);

        $pdo->commit();
        $success_message = "Status PR " . $pr_no . " berhasil diperbarui menjadi " . $final_pr_status . "!";
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

$query  = "SELECT * FROM purchase_request WHERE 1=1";
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
            <small class="text-muted">
                Posisi Anda: <strong><?= htmlspecialchars($userPositionRaw !== '' ? $userPositionRaw : '-'); ?></strong>
            </small>
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
                    $stmt_items = $pdo->prepare("SELECT * FROM purchase_request_dtl WHERE PR_No = :pr_no");
                    $stmt_items->execute([':pr_no' => $row['PR_No']]);
                    $pr_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

                    $appCtx          = getPrApprovalContext($pdo, $row, false);
                    $reqInfo         = $appCtx['request'];
                    $activeTask      = $appCtx['active_task'];
                    $approvalHistory = getPrApprovalHistory($pdo, $reqInfo);

                    $stageLabel = '-';
                    if ($activeTask) {
                        $stageOrder = $activeTask['Stage_Order'] ?? '-';
                        $posName    = $activeTask['Position_Name'] ?? ($activeTask['Position_Code'] ?? '');
                        $stageLabel = "Stage {$stageOrder}" . ($posName ? " ({$posName})" : '');
                    } elseif ($reqInfo) {
                        $stageLabel = "Stage " . ($reqInfo['Current_Stage'] ?? '-') . " [" . ($reqInfo['Status'] ?? '-') . "]";
                    }

                    $canApproveThisTask = canUserApproveTask($activeTask, $currentUserCtx);

                    $status_val  = $row['Status'];
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
                            <div><strong>Tahap Approval:</strong> <span class="text-info font-weight-bold"><?= htmlspecialchars($stageLabel); ?></span></div>
                        </div>

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

                        <!-- Kotak Riwayat Approval -->
                        <div class="bg-light p-2 rounded mb-2" style="font-size: 11.5px; border-left: 3px solid #17a2b8;">
                            <div class="font-weight-bold text-dark mb-1"><i class="fas fa-history mr-1 text-info"></i>Riwayat Approval:</div>
                            <?php if (!empty($approvalHistory)): ?>
                                <ul class="pl-3 mb-0 text-secondary">
                                    <?php foreach ($approvalHistory as $hist): ?>
                                        <?php
                                            $hStage   = htmlspecialchars((string)($hist['Stage_Order'] ?? '-'));
                                            $hPos     = htmlspecialchars((string)($hist['Position_Name'] ?: ($hist['Position_Code'] ?? 'Approver')));
                                            $hStatus  = strtoupper(trim((string)($hist['Status'] ?? 'PENDING')));
                                            $hActor   = trim((string)($hist['Acted_By'] ?? ''));
                                            $hActedAt = trim((string)($hist['Acted_At'] ?? ''));
                                            $hNotes   = trim((string)($hist['Notes'] ?? ''));

                                            $hBadge = 'badge-secondary';
                                            if ($hStatus === 'APPROVED' || $hStatus === 'AUTO_APPROVED') {
                                                $hBadge = 'badge-success';
                                            } elseif ($hStatus === 'PENDING') {
                                                $hBadge = 'badge-warning text-dark';
                                            } elseif ($hStatus === 'REJECTED' || $hStatus === 'CANCELLED') {
                                                $hBadge = 'badge-danger';
                                            }
                                        ?>
                                        <li class="mb-1">
                                            <strong>Stage <?= $hStage; ?></strong> (<?= $hPos; ?>)
                                            <span class="badge <?= $hBadge; ?>" style="font-size: 10px;"><?= htmlspecialchars($hStatus); ?></span>
                                            <?php if ($hActor !== '' && $hActor !== '0'): ?>
                                                - oleh <strong><?= htmlspecialchars($hActor); ?></strong>
                                            <?php endif; ?>
                                            <?php if ($hActedAt !== ''): ?>
                                                <span class="text-muted">(<?= date('d/m/Y H:i', strtotime($hActedAt)); ?>)</span>
                                            <?php endif; ?>
                                            <?php if ($hNotes !== '' && $hStatus !== 'PENDING'): ?>
                                                <div class="text-muted font-italic">Catatan: "<?= htmlspecialchars($hNotes); ?>"</div>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-muted font-italic">Belum ada riwayat task approval.</div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <small class="text-muted font-weight-bold">Total: Rp <?= number_format($row['Estimated_Budget'] ?? 0, 0, ',', '.'); ?></small>

                            <?php if (strcasecmp($status_val, 'Approved') !== 0 && strcasecmp($status_val, 'APPROVED') !== 0 && strcasecmp($status_val, 'Cancel') !== 0): ?>
                                <?php if ($canApproveThisTask): ?>
                                    <button class="btn btn-sm btn-success px-3 py-1 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalApp<?= $row['ID']; ?>" style="font-size: 11px;">
                                        <i class="fas fa-check-circle mr-1"></i> Review & Approve
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted font-italic" style="font-size: 11px;">
                                        <i class="fas fa-user-clock mr-1"></i> Menunggu <?= htmlspecialchars($activeTask['Position_Name'] ?? ($activeTask['Position_Code'] ?? 'Approver')); ?>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted font-italic" style="font-size: 11px;"><i class="fas fa-lock mr-1"></i> Selesai / Dikunci</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ($canApproveThisTask): ?>
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

                                    <div class="mb-2 p-2 bg-white rounded border small">
                                        <strong>Tahap Aktif:</strong> <?= htmlspecialchars($stageLabel); ?>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-secondary">Pilih Keputusan</label>
                                        <select class="form-control form-control-sm" name="status_approval" required>
                                            <option value="Approved">Approved (Setujui Tahap Ini)</option>
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
                <?php endif; ?>
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
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn  = document.getElementById('sidebarClose');
    const sidebar   = document.getElementById('sidebarDrawer');
    const overlay   = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        if (sidebar) sidebar.classList.toggle('active');
        if (overlay) overlay.classList.toggle('active');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
    if (closeBtn)  closeBtn.addEventListener('click', toggleSidebar);
    if (overlay)   overlay.addEventListener('click', toggleSidebar);

    document.getElementById('filterForm').addEventListener('submit', function() {
        const btnFilter = document.getElementById('btnFilter');
        btnFilter.disabled = true;
        btnFilter.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    });

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