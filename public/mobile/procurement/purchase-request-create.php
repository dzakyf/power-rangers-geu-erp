<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

$success_message = '';
$error_message   = '';

$current_username = $_SESSION['username'] ?? $_SESSION['name'] ?? 'admin';
$current_user_id  = $_SESSION['user_id'] ?? null;

$userData = null;
if (isset($pdo) && ($pdo instanceof PDO)) {
    try {
        if (!empty($current_user_id)) {
            $stmtU = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmtU->execute([':id' => $current_user_id]);
            $userData = $stmtU->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        if (!$userData) {
            $stmtU = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmtU->execute([':username' => $current_username]);
            $userData = $stmtU->fetch(PDO::FETCH_ASSOC) ?: null;
        }
    } catch (PDOException $e) {
        error_log('Gagal mengambil data user: ' . $e->getMessage());
    }
}

$userLocation   = trim((string)($userData['Location'] ?? ($_SESSION['location'] ?? 'GEU-HO')));
$userDepartment = trim((string)($userData['Department'] ?? ($_SESSION['department'] ?? '')));
$userPicDefault = trim((string)($userData['Name'] ?? $current_username));
$userPhoneDef   = trim((string)($userData['Contact'] ?? ''));
$userEmailDef   = trim((string)($userData['email'] ?? ''));

// Generate Nomor PR otomatis (selaras dengan pola PR/GEU-HO/YYYYMM-XXXX)
$generated_pr_no = 'PR/GEU-HO/' . date('Ym') . '-0001';
if (isset($pdo) && ($pdo instanceof PDO)) {
    try {
        $stmtLast = $pdo->query("SELECT ID FROM purchase_request ORDER BY ID DESC LIMIT 1");
        $lastPr   = $stmtLast->fetch(PDO::FETCH_ASSOC);
        $nextId   = $lastPr ? ((int)$lastPr['ID'] + 1) : 1;
        $generated_pr_no = 'PR/GEU-HO/' . date('Ym') . '-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
    } catch (PDOException $e) {
        error_log('Gagal generate nomor PR: ' . $e->getMessage());
    }
}

// Ambil master product_code & department
$materials   = [];
$departments = [];
if (isset($pdo) && ($pdo instanceof PDO)) {
    try {
        $stmtMat = $pdo->query("
            SELECT Product_Code, Description, Specification, Unit_Name, Main_Category, Category
            FROM product_code
            WHERE UPPER(COALESCE(status_Muncul, 'ACTIVE')) = 'ACTIVE'
              AND Description IS NOT NULL
              AND TRIM(Description) != ''
            ORDER BY Description ASC
        ");
        $materials = $stmtMat->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        try {
            $materials = $pdo->query("SELECT Product_Code, Description, Specification, Unit_Name, Main_Category, Category FROM product_code ORDER BY Description ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e2) {
            $materials = [];
        }
    }

    try {
        $departments = $pdo->query("SELECT * FROM department ORDER BY Department ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        $departments = [];
    }
}

function evaluateConditionRule(array $cond, array $prHeader): bool
{
    $field = trim((string)($cond['field'] ?? ''));
    $op    = trim((string)($cond['operator'] ?? '='));
    $val   = $cond['value'] ?? '';

    if ($field === '') {
        return true;
    }

    $budget       = (float)($prHeader['Estimated_Budget'] ?? 0);
    $suppliesType = strtoupper(trim((string)($prHeader['Supplies_Type'] ?? 'NON-OPS')));
    $mainCategory = strtoupper(trim((string)($prHeader['Category'] ?? 'NON ASSET')));
    $subCategory  = strtoupper(trim((string)($prHeader['Sub_Category'] ?? 'BARANG')));
    $dept         = trim((string)($prHeader['Department'] ?? ''));
    $location     = trim((string)($prHeader['Location'] ?? 'GEU-HO'));
    $procCategory = trim((string)($prHeader['Procurement_Category'] ?? 'Rutin'));
    $urgency      = strtoupper(trim((string)($prHeader['Urgency'] ?? 'NORMAL')));

    if (in_array($field, ['Estimated_Budget', 'Amount'], true)) {
        $lhs = $budget;
        $rhs = is_numeric($val) ? (float)$val : 0.0;

        switch ($op) {
            case '<=': return $lhs <= $rhs;
            case '>=': return $lhs >= $rhs;
            case '<':  return $lhs <  $rhs;
            case '>':  return $lhs >  $rhs;
            case '!=': return abs($lhs - $rhs) > 0.0001;
            case '=':
            case '==':
            default:   return abs($lhs - $rhs) <= 0.0001;
        }
    }

    $map = [
        'Supplies_Type'        => $suppliesType,
        'Category'             => $mainCategory,
        'Sub_Category'         => $subCategory,
        'Department'           => strtoupper($dept),
        'Location'             => strtoupper($location),
        'Procurement_Category' => strtoupper($procCategory),
        'Urgency'              => $urgency,
    ];

    $lhsStr = $map[$field] ?? strtoupper(trim((string)($prHeader[$field] ?? '')));
    $rhsStr = strtoupper(trim((string)$val));

    if ($op === '!=') {
        return $lhsStr !== $rhsStr;
    }

    return $lhsStr === $rhsStr;
}

// Helper mencocokkan & membuat approval_request + task Stage 1 dari approval_policy & approval_policy_step
function createApprovalRequestForPR(PDO $pdo, int $prId, array $prHeader, string $actorUsername, ?int $actorUserId): void
{
    $budget       = (float)($prHeader['Estimated_Budget'] ?? 0);
    $suppliesType = strtoupper(trim((string)($prHeader['Supplies_Type'] ?? 'NON-OPS')));
    $mainCategory = strtoupper(trim((string)($prHeader['Category'] ?? 'NON ASSET')));
    $subCategory  = strtoupper(trim((string)($prHeader['Sub_Category'] ?? 'BARANG')));
    $dept         = trim((string)($prHeader['Department'] ?? ''));
    $location     = trim((string)($prHeader['Location'] ?? 'GEU-HO'));

    $policy = null;
    try {
        $stmtPol = $pdo->query("
            SELECT * FROM approval_policy
            WHERE UPPER(COALESCE(Status, '')) = 'ACTIVE'
              AND UPPER(COALESCE(Request_Type, 'PROCUREMENT')) = 'PROCUREMENT'
            ORDER BY ID ASC
        ");
        $policies = $stmtPol->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $candidates = [];

        foreach ($policies as $pol) {
            $hasMin = isset($pol['Min_Amount']) && $pol['Min_Amount'] !== null && $pol['Min_Amount'] !== '';
            $hasMax = isset($pol['Max_Amount']) && $pol['Max_Amount'] !== null && $pol['Max_Amount'] !== '' && (float)$pol['Max_Amount'] > 0;

            $minAmt = $hasMin ? (float)$pol['Min_Amount'] : 0.0;
            $maxAmt = $hasMax ? (float)$pol['Max_Amount'] : PHP_FLOAT_MAX;

            if ($budget < $minAmt || $budget > $maxAmt) {
                continue;
            }

            if (!empty($pol['Location']) && strcasecmp(trim((string)$pol['Location']), $location) !== 0) {
                continue;
            }

            if (!empty($pol['Department']) && strcasecmp(trim((string)$pol['Department']), $dept) !== 0) {
                continue;
            }

            $condMatched = true;
            $condCount   = 0;

            if (!empty($pol['Conditions'])) {
                $conds = json_decode((string)$pol['Conditions'], true);
                if (is_array($conds)) {
                    foreach ($conds as $c) {
                        if (!is_array($c)) continue;
                        $condCount++;
                        if (!evaluateConditionRule($c, $prHeader)) {
                            $condMatched = false;
                            break;
                        }
                    }
                }
            }

            if (!$condMatched) {
                continue;
            }

            $score = 0;
            if ($hasMin) $score += 10;
            if ($hasMax) $score += 10;
            if (!empty($pol['Location'])) $score += 5;
            if (!empty($pol['Department'])) $score += 5;
            $score += ($condCount * 3);

            $candidates[] = [
                'score'  => $score,
                'policy' => $pol,
            ];
        }

        if (!empty($candidates)) {
            usort($candidates, function ($a, $b) {
                if ($a['score'] !== $b['score']) {
                    return $b['score'] <=> $a['score'];
                }
                return ((int)($a['policy']['ID'] ?? 0)) <=> ((int)($b['policy']['ID'] ?? 0));
            });
            $policy = $candidates[0]['policy'];
        }
    } catch (PDOException $e) {
        error_log('Gagal mencari approval_policy: ' . $e->getMessage());
    }

    if (!$policy) {
        return;
    }

    $steps = [];
    try {
        $stmtSteps = $pdo->prepare("
            SELECT * FROM approval_policy_step
            WHERE Policy_ID = :policy_id
            ORDER BY Stage_Order ASC, Level_Number ASC, ID ASC
        ");
        $stmtSteps->execute([':policy_id' => $policy['ID']]);
        $steps = $stmtSteps->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        error_log('Gagal mengambil approval_policy_step: ' . $e->getMessage());
    }

    $snapshot = json_encode([
        'Policy'      => $policy,
        'Steps'       => $steps,
        'Captured_At' => date('Y-m-d H:i:s'),
    ], JSON_UNESCAPED_UNICODE);

    $context = json_encode([
        'Request_Type'         => 'PROCUREMENT',
        'Location'             => $location,
        'Company_ID'           => 'GEU',
        'Department'           => $dept,
        'Amount'               => $budget,
        'Estimated_Budget'     => $budget,
        'Supplies_Type'        => $suppliesType,
        'Category'             => $mainCategory,
        'Sub_Category'         => $subCategory,
        'Procurement_Category' => $prHeader['Procurement_Category'] ?? 'Rutin',
        'Priority_Status'      => strtoupper((string)($prHeader['Urgency'] ?? 'NORMAL')),
    ], JSON_UNESCAPED_UNICODE);

    $firstStep  = $steps[0] ?? null;
    $startStage = $firstStep ? (int)($firstStep['Stage_Order'] ?? 1) : 1;
    $startLevel = $firstStep && isset($firstStep['Level_Number']) ? (int)$firstStep['Level_Number'] : 3;

    $stmtReq = $pdo->prepare("
        INSERT INTO approval_request (
            Status,
            Requester_Id,
            Description,
            Request_Type,
            Map_To,
            Current_Stage,
            Current_Level,
            Submitted_At,
            Created_At,
            Updated_At,
            Request_No,
            Policy_ID,
            Policy_Snapshot,
            Context
        ) VALUES (
            'IN_PROGRESS',
            :requester_id,
            :description,
            'PROCUREMENT',
            :map_to,
            :current_stage,
            :current_level,
            NOW(),
            NOW(),
            NOW(),
            :request_no,
            :policy_id,
            :policy_snapshot,
            :context
        )
    ");

    $descText = "PR [{$suppliesType} - {$mainCategory}] No: {$prHeader['PR_No']}";
    $stmtReq->execute([
        ':requester_id'    => $actorUserId ?? 0,
        ':description'     => $descText,
        ':map_to'          => (string)$prId,
        ':current_stage'   => $startStage,
        ':current_level'   => $startLevel,
        ':request_no'      => $prHeader['PR_No'],
        ':policy_id'       => $policy['ID'],
        ':policy_snapshot' => $snapshot,
        ':context'         => $context,
    ]);

    $requestId = (int)$pdo->lastInsertId();

    $pdo->prepare("UPDATE purchase_request SET Approval_Request_ID = :req_id WHERE ID = :pr_id")
        ->execute([':req_id' => $requestId, ':pr_id' => $prId]);

    if ($firstStep) {
        $posId   = isset($firstStep['Position_ID']) && $firstStep['Position_ID'] !== null ? (int)$firstStep['Position_ID'] : null;
        $posCode = isset($firstStep['Position_Code']) && trim((string)$firstStep['Position_Code']) !== '' ? trim((string)$firstStep['Position_Code']) : null;
        $posName = isset($firstStep['Label']) && trim((string)$firstStep['Label']) !== '' ? trim((string)$firstStep['Label']) : ($posCode ?? 'Approver');

        if (!$posCode && !$posId) {
            try {
                $targetDept = !empty($firstStep['Department']) ? trim((string)$firstStep['Department']) : $dept;
                if ($targetDept !== '') {
                    $stmtMp = $pdo->prepare("
                        SELECT ID, Position_Code, Position_Name
                        FROM master_position
                        WHERE UPPER(Department) = UPPER(:dept)
                        ORDER BY Level_Number DESC, ID ASC
                        LIMIT 1
                    ");
                    $stmtMp->execute([':dept' => $targetDept]);
                    $mpRow = $stmtMp->fetch(PDO::FETCH_ASSOC);
                    if ($mpRow) {
                        $posId   = (int)$mpRow['ID'];
                        $posCode = $mpRow['Position_Code'] ?? null;
                        $posName = $mpRow['Position_Name'] ?? $posName;
                    }
                }
            } catch (PDOException $e) {
                // Abaikan bila tabel master_position tidak tersedia
            }
        }

        $stmtTask = $pdo->prepare("
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
        $stmtTask->execute([
            ':request_id'    => $requestId,
            ':stage_order'   => $startStage,
            ':level_number'  => $startLevel,
            ':position_id'   => $posId,
            ':position_code' => $posCode,
            ':position_name' => $posName,
            ':department'    => !empty($firstStep['Department']) ? $firstStep['Department'] : $dept,
            ':approval_mode' => !empty($firstStep['Approval_Mode']) ? $firstStep['Approval_Mode'] : 'ALL',
            ':quorum'        => isset($firstStep['Quorum']) && $firstStep['Quorum'] !== null ? (int)$firstStep['Quorum'] : null,
        ]);
    }
}

// 2. PROSES SIMPAN PURCHASE REQUEST BARU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_pr'])) {
    $pr_no                = trim((string)($_POST['pr_no'] ?? ''));
    $pr_date              = trim((string)($_POST['pr_date'] ?? date('Y-m-d')));
    $department           = trim((string)($_POST['department'] ?? ''));
    $supplies_type        = strtoupper(trim((string)($_POST['supplies_type'] ?? 'NON-OPS')));
    $main_category        = strtoupper(trim((string)($_POST['main_category'] ?? 'NON ASSET')));
    $proc_category        = trim((string)($_POST['procurement_category'] ?? 'Rutin'));
    $sub_category         = strtoupper(trim((string)($_POST['sub_category'] ?? 'BARANG')));
    $urgency              = trim((string)($_POST['urgency'] ?? 'Normal'));
    $hope_date            = trim((string)($_POST['hope_date'] ?? ''));
    $cost_center          = trim((string)($_POST['cost_center'] ?? ''));
    $pic_name             = trim((string)($_POST['pic_name'] ?? $userPicDefault));
    $pic_phone            = trim((string)($_POST['pic_phone'] ?? ''));
    $pic_email            = trim((string)($_POST['pic_email'] ?? ''));
    $site_destination     = trim((string)($_POST['site_destination'] ?? ''));
    $note                 = trim((string)($_POST['note'] ?? ''));

    $prod_codes   = $_POST['product_code'] ?? [];
    $prod_descs   = $_POST['product_desc'] ?? [];
    $prod_specs   = $_POST['specification'] ?? [];
    $prod_qtys    = $_POST['qty'] ?? [];
    $prod_units   = $_POST['unit'] ?? [];
    $prod_prices  = $_POST['price'] ?? [];

    try {
        if ($pr_no === '' || $pr_date === '' || $department === '') {
            throw new Exception("Nomor PR, Tanggal PR, dan Department wajib diisi.");
        }

        $validItems = [];
        $calcBudget = 0.0;

        foreach ($prod_codes as $idx => $code) {
            $codeClean = trim((string)$code);
            if ($codeClean === '') {
                continue;
            }
            $qty   = (float)($prod_qtys[$idx] ?? 1);
            $price = (float)($prod_prices[$idx] ?? 0);
            if ($qty <= 0) {
                throw new Exception("Qty produk {$codeClean} harus lebih dari 0.");
            }
            $calcBudget += ($qty * $price);

            $validItems[] = [
                'product_code'  => $codeClean,
                'product_desc'  => trim((string)($prod_descs[$idx] ?? '')),
                'specification' => trim((string)($prod_specs[$idx] ?? '')),
                'qty'           => $qty,
                'unit'          => trim((string)($prod_units[$idx] ?? 'PCS')),
                'price'         => $price,
                'file_index'    => $idx,
            ];
        }

        if (empty($validItems)) {
            throw new Exception("Minimal harus ada 1 rincian produk yang dipilih.");
        }

        $estimatedBudget = $calcBudget > 0 ? $calcBudget : (float)($_POST['estimated_budget'] ?? 0);

        $pdo->beginTransaction();

        // 1. Insert header purchase_request
        $stmtPr = $pdo->prepare("
            INSERT INTO purchase_request (
                Location,
                Supplies_Type,
                Urgency,
                PR_No,
                PR_Date,
                Hope_Date,
                Department,
                Cost_Center,
                Category,
                Sub_Category,
                Procurement_Category,
                PIC,
                PIC_Phone,
                PIC_Email,
                PIC_Address,
                Estimated_Budget,
                Note,
                Status,
                Created_By,
                Created_Date
            ) VALUES (
                :location,
                :supplies_type,
                :urgency,
                :pr_no,
                :pr_date,
                :hope_date,
                :department,
                :cost_center,
                :category,
                :sub_category,
                :procurement_category,
                :pic,
                :pic_phone,
                :pic_email,
                :pic_address,
                :estimated_budget,
                :note,
                'PENDING',
                :created_by,
                NOW()
            )
        ");

        $stmtPr->execute([
            ':location'             => $userLocation,
            ':supplies_type'        => $supplies_type,
            ':urgency'              => $urgency,
            ':pr_no'                => $pr_no,
            ':pr_date'              => $pr_date,
            ':hope_date'            => $hope_date !== '' ? $hope_date : null,
            ':department'           => $department,
            ':cost_center'          => $cost_center !== '' ? $cost_center : null,
            ':category'             => $main_category,
            ':sub_category'         => $sub_category,
            ':procurement_category' => $proc_category,
            ':pic'                  => $pic_name !== '' ? $pic_name : null,
            ':pic_phone'            => $pic_phone !== '' ? $pic_phone : null,
            ':pic_email'            => $pic_email !== '' ? $pic_email : null,
            ':pic_address'          => $site_destination !== '' ? $site_destination : null,
            ':estimated_budget'     => $estimatedBudget,
            ':note'                 => $note !== '' ? $note : null,
            ':created_by'           => $current_username,
        ]);

        $prId = (int)$pdo->lastInsertId();

        // 1.1 Simpan history note ke pr_notes_history
        if ($note !== '') {
            $stmtNote = $pdo->prepare("
                INSERT INTO pr_notes_history (
                    PR_No,
                    Action_Status,
                    Note,
                    Created_By,
                    Created_Date
                ) VALUES (
                    :pr_no,
                    'CREATED PR',
                    :note,
                    :created_by,
                    NOW()
                )
            ");
            $stmtNote->execute([
                ':pr_no'      => $pr_no,
                ':note'       => $note,
                ':created_by' => $current_username,
            ]);
        }

        // 2. Insert rincian multi-produk ke purchase_request_dtl
        $stmtDtl = $pdo->prepare("
            INSERT INTO purchase_request_dtl (
                PR_No,
                Product_Code,
                Product_Desc,
                Specification,
                Qty,
                Unit,
                Default_Price,
                Attachment
            ) VALUES (
                :pr_no,
                :product_code,
                :product_desc,
                :specification,
                :qty,
                :unit,
                :default_price,
                :attachment
            )
        ");

        foreach ($validItems as $item) {
            $attachmentPath = null;
            $fIdx = $item['file_index'];

            if (
                isset($_FILES['attachment']['name'][$fIdx]) &&
                $_FILES['attachment']['error'][$fIdx] === UPLOAD_ERR_OK
            ) {
                $origName = (string)$_FILES['attachment']['name'][$fIdx];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowedExt = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];

                if (in_array($ext, $allowedExt, true)) {
                    $fileName = time() . '_' . uniqid() . '.' . $ext;
                    $destDir  = __DIR__ . '/uploads/pr_attachments';
                    if (!is_dir($destDir)) {
                        @mkdir($destDir, 0777, true);
                    }
                    $destFull = $destDir . '/' . $fileName;
                    if (move_uploaded_file($_FILES['attachment']['tmp_name'][$fIdx], $destFull)) {
                        $attachmentPath = 'uploads/pr_attachments/' . $fileName;
                    }
                }
            }

            $stmtDtl->execute([
                ':pr_no'         => $pr_no,
                ':product_code'  => $item['product_code'],
                ':product_desc'  => $item['product_desc'],
                ':specification' => $item['specification'],
                ':qty'           => $item['qty'],
                ':unit'          => $item['unit'],
                ':default_price' => $item['price'],
                ':attachment'    => $attachmentPath,
            ]);
        }

        // 3. Buat approval_request & task Stage 1 sesuai policy aktif
        createApprovalRequestForPR($pdo, $prId, [
            'PR_No'                => $pr_no,
            'Location'             => $userLocation,
            'Department'           => $department,
            'Supplies_Type'        => $supplies_type,
            'Category'             => $main_category,
            'Sub_Category'         => $sub_category,
            'Procurement_Category' => $proc_category,
            'Urgency'              => $urgency,
            'Estimated_Budget'     => $estimatedBudget,
        ], $current_username, $current_user_id ? (int)$current_user_id : null);

        $pdo->commit();
        $success_message = "Purchase Request {$pr_no} berhasil disimpan!";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_message = "Gagal menyimpan PR: " . $e->getMessage();
    }
}
?>

<!-- Panggil Topbar & Sidebar -->
<?php include 'includes/topbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    .select2-container {
        width: 100% !important;
    }
    .select2-container .select2-selection--single {
        height: 31px !important;
        padding: 2px 6px;
        font-size: 12px;
        border: 1px solid #ced4da;
        border-radius: .2rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 25px;
        padding-left: 2px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px;
    }
    .select2-search__field {
        font-size: 12px !important;
        padding: 4px 6px !important;
    }
</style>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-plus-circle text-success mr-2"></i>Form Pembuatan Purchase Request (PR) Baru</h5>
            <small class="text-muted">Buat dokumen PR baru beserta rincian material & alur approval.</small>
        </div>
    </div>

    <form action="purchase-request-create" method="POST" enctype="multipart/form-data" id="formCreatePr">
        <input type="hidden" name="simpan_pr" value="1">

        <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
            <div class="card-header bg-success text-white py-2" style="border-radius: 10px 10px 0 0;">
                <span class="font-weight-bold small"><i class="fas fa-file-alt mr-1"></i> Informasi Header PR</span>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Nomor PR</label>
                        <input type="text" class="form-control form-control-sm bg-light font-weight-bold" name="pr_no" value="<?= htmlspecialchars($generated_pr_no); ?>" readonly required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Tanggal PR *</label>
                        <input type="date" class="form-control form-control-sm" name="pr_date" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Department (Pembebanan Budget) *</label>
                        <select class="form-control form-control-sm dept-select" id="departmentSelect" name="department" required>
                            <option value="">-- Ketik / Pilih Department --</option>
                            <?php foreach ($departments as $deptRow): ?>
                                <?php
                                    $dCode = trim((string)($deptRow['Department_Code'] ?? $deptRow['Department'] ?? ''));
                                    $dName = trim((string)($deptRow['Department'] ?? $dCode));
                                    $selVal = $dCode !== '' ? $dCode : $dName;
                                    $isSel = ($userDepartment !== '' && strcasecmp($userDepartment, $selVal) === 0) ? 'selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($selVal); ?>" <?= $isSel; ?>>
                                    <?= htmlspecialchars($dName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-danger mb-1">Jenis Pengadaan (SK Approval) *</label>
                        <select class="form-control form-control-sm border-danger" name="supplies_type" id="suppliesType" required>
                            <option value="NON-OPS" selected>B. Procurement Non-OPS</option>
                            <option value="OPS">A. Procurement OPS</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Kategori Anggaran (Main Category)</label>
                        <select class="form-control form-control-sm" name="main_category" id="mainCategory">
                            <option value="NON ASSET" selected>NON ASSET</option>
                            <option value="ASSET">ASSET</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Kategori Pengadaan</label>
                        <select class="form-control form-control-sm" name="procurement_category">
                            <option value="Rutin" selected>Rutin</option>
                            <option value="Non-Rutin">Non-Rutin</option>
                            <option value="Project">Project</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Sub Kategori (Category)</label>
                        <select class="form-control form-control-sm" name="sub_category" id="subCategory">
                            <option value="BARANG" selected>BARANG</option>
                            <option value="JASA">JASA</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Tingkat Urgensi PR</label>
                        <select class="form-control form-control-sm" name="urgency">
                            <option value="Normal" selected>Normal</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Hope Date (Target Kedatangan)</label>
                        <input type="date" class="form-control form-control-sm" name="hope_date" value="<?= date('Y-m-d', strtotime('+7 days')); ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Cost Center</label>
                        <input type="text" class="form-control form-control-sm" name="cost_center" placeholder="Contoh: CC-HO-01 / Project Site...">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">PIC (Penanggung Jawab)</label>
                        <input type="text" class="form-control form-control-sm" name="pic_name" value="<?= htmlspecialchars($userPicDefault); ?>" placeholder="Nama PIC...">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Phone PIC</label>
                        <input type="text" class="form-control form-control-sm" name="pic_phone" value="<?= htmlspecialchars($userPhoneDef); ?>" placeholder="No. Telepon...">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Email PIC</label>
                        <input type="email" class="form-control form-control-sm" name="pic_email" value="<?= htmlspecialchars($userEmailDef); ?>" placeholder="email@domain.com...">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Site Tujuan</label>
                        <input type="text" class="form-control form-control-sm" name="site_destination" placeholder="Contoh: Site Bekasi...">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold text-secondary mb-1">Estimated Budget (Rp)</label>
                        <input type="text" class="form-control form-control-sm bg-light font-weight-bold text-success" id="estimatedBudgetDisplay" value="0" readonly>
                        <input type="hidden" name="estimated_budget" id="estimatedBudgetInput" value="0">
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-secondary mb-1">Catatan / Note Baru</label>
                    <textarea class="form-control form-control-sm" name="note" rows="2" placeholder="Tulis catatan atau instruksi tambahan untuk PR ini..."></textarea>
                </div>
            </div>
        </div>

        <!-- RINCIAN MULTI PRODUK -->
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <span class="font-weight-bold small text-success"><i class="fas fa-boxes mr-1"></i> Daftar Rincian Material / Multi Produk</span>
                <button type="button" class="btn btn-sm btn-success font-weight-bold" id="btnAddItem">
                    <i class="fas fa-plus mr-1"></i> Tambah Item Produk
                </button>
            </div>
            <div class="card-body p-2" id="itemsContainer">
                <div class="border rounded p-2 mb-2 bg-light item-row">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">Product Code *</label>
                            <select class="form-control form-control-sm product-code-select" name="product_code[]" required>
                                <option value="">-- Ketik untuk mencari produk --</option>
                                <?php foreach ($materials as $mat): ?>
                                    <option
                                        value="<?= htmlspecialchars($mat['Product_Code']); ?>"
                                        data-desc="<?= htmlspecialchars($mat['Description'] ?? ''); ?>"
                                        data-spec="<?= htmlspecialchars($mat['Specification'] ?? '-'); ?>"
                                        data-unit="<?= htmlspecialchars($mat['Unit_Name'] ?? 'PCS'); ?>"
                                        data-main="<?= htmlspecialchars($mat['Main_Category'] ?? 'NON ASSET'); ?>"
                                        data-sub="<?= htmlspecialchars($mat['Category'] ?? 'BARANG'); ?>"
                                    >
                                        <?= htmlspecialchars($mat['Product_Code']); ?> - <?= htmlspecialchars($mat['Description'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">Nama Item / Deskripsi</label>
                            <input type="text" class="form-control form-control-sm item-desc" name="product_desc[]" placeholder="Otomatis..." required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">Spesifikasi (Spec)</label>
                            <input type="text" class="form-control form-control-sm item-spec" name="specification[]" placeholder="Otomatis / isi manual...">
                        </div>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-6 col-md-2 mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">Qty *</label>
                            <input type="number" step="any" min="1" class="form-control form-control-sm item-qty" name="qty[]" value="1" required>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">Satuan</label>
                            <input type="text" class="form-control form-control-sm bg-white item-unit" name="unit[]" value="PCS" readonly>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">Est. Price (Rp)</label>
                            <input type="number" step="any" min="0" class="form-control form-control-sm item-price" name="price[]" value="0">
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">Subtotal (Rp)</label>
                            <input type="text" class="form-control form-control-sm bg-white font-weight-bold item-subtotal" value="0" readonly>
                        </div>
                        <div class="col-9 col-md-3 mb-2">
                            <label class="small font-weight-bold text-secondary mb-1">Lampiran</label>
                            <input type="file" class="form-control-file small" name="attachment[]" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        </div>
                        <div class="col-3 col-md-1 mb-2 text-right">
                            <button type="button" class="btn btn-sm btn-danger btn-remove-item" title="Hapus Item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <button type="submit" class="btn btn-success font-weight-bold px-4" id="btnSimpanPr">
                <i class="fas fa-save mr-1"></i> Simpan Purchase Request
            </button>
            <a href="approval-center" class="btn btn-danger font-weight-bold px-3">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Approval Center
            </a>
        </div>
    </form>
</div>

<?php include 'includes/bottombar.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

    function initDeptSelect2() {
        const $dept = $('#departmentSelect');
        if (!$dept.length) return;
        if ($dept.hasClass('select2-hidden-accessible')) {
            $dept.select2('destroy');
        }
        $dept.select2({
            placeholder: '-- Ketik / Pilih Department --',
            allowClear: true,
            width: '100%'
        });
    }

    function initProductSelect2(row) {
        const $sel = $(row).find('.product-code-select');
        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.select2('destroy');
        }
        $(row).find('.select2-container').remove();
        $sel.select2({
            placeholder: '-- Ketik untuk mencari produk --',
            allowClear: true,
            width: '100%'
        });

        $sel.off('select2:select').on('select2:select', function() {
            this.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    function formatRupiah(num) {
        return Number(num || 0).toLocaleString('id-ID');
    }

    function recalculateTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#itemsContainer .item-row').forEach(function(row) {
            const qty   = parseFloat(row.querySelector('.item-qty')?.value || 0);
            const price = parseFloat(row.querySelector('.item-price')?.value || 0);
            const sub   = (isNaN(qty) ? 0 : qty) * (isNaN(price) ? 0 : price);
            grandTotal += sub;

            const subInput = row.querySelector('.item-subtotal');
            if (subInput) {
                subInput.value = formatRupiah(sub);
            }
        });

        document.getElementById('estimatedBudgetDisplay').value = formatRupiah(grandTotal);
        document.getElementById('estimatedBudgetInput').value = grandTotal;
    }

    function bindRowEvents(row) {
        initProductSelect2(row);

        const select = row.querySelector('.product-code-select');
        if (select) {
            $(select).off('change.prAutoFill').on('change.prAutoFill', function() {
                const opt = this.options[this.selectedIndex];
                if (!opt || !opt.value) return;

                const descInput = row.querySelector('.item-desc');
                const specInput = row.querySelector('.item-spec');
                const unitInput = row.querySelector('.item-unit');

                if (descInput) descInput.value = opt.getAttribute('data-desc') || '';
                if (specInput) specInput.value = opt.getAttribute('data-spec') || '-';
                if (unitInput) unitInput.value = opt.getAttribute('data-unit') || 'PCS';

                const mainCat = opt.getAttribute('data-main');
                const subCat  = opt.getAttribute('data-sub');
                if (mainCat && document.getElementById('mainCategory')) {
                    document.getElementById('mainCategory').value = mainCat.toUpperCase();
                }
                if (subCat && document.getElementById('subCategory')) {
                    document.getElementById('subCategory').value = subCat.toUpperCase();
                }
            });
        }

        row.querySelectorAll('.item-qty, .item-price').forEach(function(inp) {
            inp.addEventListener('input', recalculateTotals);
        });

        const removeBtn = row.querySelector('.btn-remove-item');
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                const rows = document.querySelectorAll('#itemsContainer .item-row');
                if (rows.length <= 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Minimal 1 Item',
                        text: 'PR harus memiliki minimal satu rincian item produk.'
                    });
                    return;
                }
                const $sel = $(row).find('.product-code-select');
                if ($sel.hasClass('select2-hidden-accessible')) {
                    $sel.select2('destroy');
                }
                row.remove();
                recalculateTotals();
            });
        }
    }

    $(document).ready(function() {
        initDeptSelect2();
        document.querySelectorAll('#itemsContainer .item-row').forEach(bindRowEvents);
        recalculateTotals();
    });

    document.getElementById('btnAddItem').addEventListener('click', function() {
        const container = document.getElementById('itemsContainer');
        const firstRow  = container.querySelector('.item-row');

        const $firstSel = $(firstRow).find('.product-code-select');
        if ($firstSel.hasClass('select2-hidden-accessible')) {
            $firstSel.select2('destroy');
        }
        $(firstRow).find('.select2-container').remove();

        const clone = firstRow.cloneNode(true);

        clone.querySelectorAll('input').forEach(function(inp) {
            if (inp.classList.contains('item-qty')) {
                inp.value = '1';
            } else if (inp.classList.contains('item-price') || inp.classList.contains('item-subtotal')) {
                inp.value = '0';
            } else if (inp.classList.contains('item-unit')) {
                inp.value = 'PCS';
            } else if (inp.type === 'file') {
                inp.value = '';
            } else {
                inp.value = '';
            }
        });

        const sel = clone.querySelector('.product-code-select');
        if (sel) sel.selectedIndex = 0;

        container.appendChild(clone);
        initProductSelect2(firstRow);
        bindRowEvents(clone);
        recalculateTotals();
    });

    <?php if (!empty($success_message)): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: <?= json_encode($success_message, JSON_UNESCAPED_UNICODE); ?>,
            confirmButtonColor: '#28a745'
        }).then(() => {
            window.location.href = 'approval-center';
        });
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: <?= json_encode($error_message, JSON_UNESCAPED_UNICODE); ?>,
            confirmButtonColor: '#d33'
        });
    <?php endif; ?>
</script>

</body>
</html>
