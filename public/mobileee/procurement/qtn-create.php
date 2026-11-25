<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

$success_message = '';
$error_message = '';
$current_username = $_SESSION['username'] ?? $_SESSION['name'] ?? 'ADMIN';

// Tangkap parameter RFQ jika dikirim dari halaman sebelumnya
$selected_rfq = $_GET['rfq'] ?? '';

// 2. PROSES SIMPAN PENAWARAN QTN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_qtn'])) {
    $rfq_no        = trim($_POST['rfq_no']);
    $pr_no         = trim($_POST['pr_no']);
    $supplier_id   = trim($_POST['supplier_id']);
    $item_desc     = $_POST['item_desc'] ?? [];
    $item_spec     = $_POST['item_spec'] ?? [];
    $item_qty      = $_POST['item_qty'] ?? [];
    $item_unit     = $_POST['item_unit'] ?? [];
    $item_price    = $_POST['item_price'] ?? [];

    try {
        $pdo->beginTransaction();

        // Buat Nomor QTN otomatis
        $bulanRomawi = array("", "I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
        $bln = $bulanRomawi[(int) date('m')];
        $thn = date('Y');

        $stmt_count = $pdo->query("SELECT COUNT(*) FROM quotation");
        $next_no = $stmt_count->fetchColumn() + 1;
        $target_qtn_no = "QTN/" . $bln . "/" . $thn . "/" . sprintf("%03d", $next_no);

        // Insert setiap item penawaran ke tabel quotation
        $stmt_ins = $pdo->prepare("INSERT INTO quotation (QTN_No, RFQ_No, PR_No, Supplier_ID, Product_Desc, Specification, Qty, Unit, Offered_Price, Status) VALUES (:qtn_no, :rfq_no, :pr_no, :supplier_id, :desc, :spec, :qty, :unit, :price, 'PENDING')");

        for ($i = 0; $i < count($item_desc); $i++) {
            if (!empty(trim($item_desc[$i]))) {
                $stmt_ins->execute([
                    ':qtn_no'      => $target_qtn_no,
                    ':rfq_no'      => $rfq_no,
                    ':pr_no'       => $pr_no,
                    ':supplier_id' => $supplier_id,
                    ':desc'        => trim($item_desc[$i]),
                    ':spec'        => trim($item_spec[$i]),
                    ':qty'         => floatval($item_qty[$i]),
                    ':unit'        => trim($item_unit[$i]),
                    ':price'       => floatval($item_price[$i])
                ]);
            }
        }

        // Update status RFQ menjadi RESPONDED
        $stmt_up_rfq = $pdo->prepare("UPDATE rfq SET Status = 'RESPONDED' WHERE RFQ_No = :rfq_no");
        $stmt_up_rfq->execute([':rfq_no' => $rfq_no]);

        // Update kolom QTN_No di tabel purchase_request
        $stmt_up_pr = $pdo->prepare("UPDATE purchase_request SET QTN_No = :qtn_no WHERE PR_No = :pr_no");
        $stmt_up_pr->execute([
            ':qtn_no' => $target_qtn_no,
            ':pr_no'  => $pr_no
        ]);

        // Perbarui Matriks Harga (Rank_Order) otomatis
        $pdo->exec("SET @r:=0; UPDATE quotation SET Rank_Order = (@r:=@r+1) WHERE PR_No = '$pr_no' ORDER BY Offered_Price ASC;");

        $pdo->commit();
        $success_message = "Data Quotation (QTN) berhasil disimpan dan Matriks Harga diperbarui!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Gagal menyimpan QTN: " . $e->getMessage();
    }
}

// 3. Ambil Data Detail RFQ & PR (Termasuk Estimated Budget)
$rfq_data = null;
$pr_items = [];
$estimated_budget_pr = 0;

if (!empty($selected_rfq)) {
    try {
        $stmt_rfq = $pdo->prepare("SELECT r.*, s.Company_Name FROM rfq r LEFT JOIN supplier s ON r.Supplier_ID = s.Supplier_ID WHERE r.RFQ_No = :rfq LIMIT 1");
        $stmt_rfq->execute([':rfq' => $selected_rfq]);
        $rfq_data = $stmt_rfq->fetch(PDO::FETCH_ASSOC);

        if ($rfq_data) {
            // Ambil Estimated Budget dari tabel purchase_request
            $stmt_pr_info = $pdo->prepare("SELECT Estimated_Budget FROM purchase_request WHERE PR_No = :pr_no LIMIT 1");
            $stmt_pr_info->execute([':pr_no' => $rfq_data['PR_No']]);
            $estimated_budget_pr = floatval($stmt_pr_info->fetchColumn() ?: 0);

            // Ambil item PR
            $stmt_items = $pdo->prepare("SELECT * FROM purchase_request_dtl WHERE PR_No = :pr_no");
            $stmt_items->execute([':pr_no' => $rfq_data['PR_No']]);
            $pr_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {}
}

// 4. Ambil Daftar Semua QTN untuk Matriks Harga
try {
    $stmt_matrix = $pdo->query("SELECT q.*, s.Company_Name FROM quotation q LEFT JOIN supplier s ON q.Supplier_ID = s.Supplier_ID ORDER BY q.PR_No ASC, q.Offered_Price ASC");
    $list_quotations = $stmt_matrix->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $list_quotations = [];
}
?>

<!-- Styling Tambahan -->
<style>
    .financial-summary-box {
        background: #f1f8f5;
        border: 1px dashed #28a745;
        border-radius: 8px;
        padding: 12px;
        font-size: 13px;
        max-width: 400px;
        margin-left: auto;
    }
</style>

<!-- Panggil Topbar & Sidebar -->
<?php include 'includes/topbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-chart-line text-success mr-2"></i>QTN & Matriks Harga</h5>
            <small class="text-muted">Input penawaran harga dari vendor dan analisis matriks harga terendah.</small>
        </div>
        <?php if (!empty($selected_rfq) && $rfq_data): ?>
            <span class="badge badge-success px-2 py-1 font-weight-bold shadow-sm" style="font-size: 11px;">RFQ: <?= htmlspecialchars($selected_rfq); ?></span>
        <?php endif; ?>
    </div>

    <!-- FORM INPUT QTN BERDASARKAN RFQ -->
    <?php if (!empty($selected_rfq) && $rfq_data): ?>
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-success text-white py-2" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h6 class="mb-0 font-weight-bold" style="font-size: 13px;"><i class="fas fa-edit mr-2"></i>Form Input Penawaran untuk PR: <?= htmlspecialchars($rfq_data['PR_No']); ?></h6>
            </div>
            <div class="card-body bg-light">
                <form action="qtn-create?rfq=<?= urlencode($selected_rfq); ?>" method="POST" id="formQtn">
                    <input type="hidden" name="submit_qtn" value="1">
                    <input type="hidden" name="rfq_no" value="<?= htmlspecialchars($rfq_data['RFQ_No']); ?>">
                    <input type="hidden" name="pr_no" value="<?= htmlspecialchars($rfq_data['PR_No']); ?>">
                    <input type="hidden" name="supplier_id" value="<?= htmlspecialchars($rfq_data['Supplier_ID']); ?>">

                    <div class="row mb-2" style="font-size: 12px;">
                        <div class="col-md-6">
                            <div><strong>Vendor:</strong> <span class="text-dark font-weight-bold"><?= htmlspecialchars($rfq_data['Company_Name'] ?? $rfq_data['Supplier_ID']); ?></span></div>
                            <div><strong>Tanggal RFQ:</strong> <?= date('d/m/Y', strtotime($rfq_data['RFQ_Date'])); ?></div>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <div class="text-primary font-weight-bold" style="font-size: 13px;">
                                <i class="fas fa-wallet mr-1"></i> Est. Budget PR: Rp <?= number_format($estimated_budget_pr, 0, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                    <hr class="my-2">

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm bg-white mb-3" style="font-size: 12px;">
                            <thead class="thead-light text-center">
                                <tr>
                                    <th>Nama Item / Deskripsi</th>
                                    <th>Spesifikasi</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th>Harga Penawaran Satuan (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($pr_items)): ?>
                                    <?php foreach ($pr_items as $idx => $it): ?>
                                        <tr class="item-row">
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="item_desc[]" value="<?= htmlspecialchars($it['Product_Desc']); ?>" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="item_spec[]" value="<?= htmlspecialchars($it['Specification']); ?>">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm text-center item-qty" name="item_qty[]" value="<?= $it['Qty']; ?>" readonly style="width: 60px;">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-center" name="item_unit[]" value="<?= htmlspecialchars($it['Unit']); ?>" readonly style="width: 70px;">
                                            </td>
                                            <td>
                                                <input type="number" step="any" class="form-control form-control-sm font-weight-bold text-success item-price calc-trigger" name="item_price[]" placeholder="Nominal harga..." required>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">Tidak ada item dalam PR ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- KOTAK PENGATURAN PAJAK & PERHITUNGAN OTOMATIS -->
                    <div class="financial-summary-box mb-3">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label class="small font-weight-bold text-secondary mb-1">Tarif PPN (%)</label>
                                <select class="form-control form-control-sm calc-trigger" id="ppnPersen">
                                    <option value="0">0% (Tanpa PPN)</option>
                                    <option value="11" selected>11% (Standar)</option>
                                    <option value="12">12% (Tarif Baru)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="small font-weight-bold text-secondary mb-1">Tarif PPh (%)</label>
                                <select class="form-control form-control-sm calc-trigger" id="pphPersen">
                                    <option value="0" selected>0% (Tanpa PPh)</option>
                                    <option value="2">2% (PPh Pasal 23)</option>
                                    <option value="3">3%</option>
                                    <option value="4">4%</option>
                                </select>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between text-secondary" style="font-size: 12px;">
                            <span>Subtotal Penawaran:</span>
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

                    <!-- TOMBOL AKSI -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch mt-3" style="gap: 10px;">
                        <button type="submit" class="btn btn-success btn-sm font-weight-bold py-2 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Simpan Penawaran & Susun Matriks
                        </button>
                        <a href="rfq-create" class="btn btn-secondary btn-sm font-weight-bold py-2 text-center">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke RFQ
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info py-2 shadow-sm" style="font-size: 13px; border-radius: 8px;">
            <i class="fas fa-info-circle mr-1"></i> Silakan pilih RFQ dari menu <strong>RFQ</strong> untuk mulai memasukkan data penawaran harga vendor.
        </div>
    <?php endif; ?>

    <!-- TABEL MATRIKS HARGA -->
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header bg-white py-2" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
            <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 13px;"><i class="fas fa-list-ol mr-2 text-success"></i>Matriks Harga Penawaran Vendor</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm text-center" style="font-size: 12px;">
                    <thead class="thead-light">
                        <tr>
                            <th>Rank</th>
                            <th>No. QTN</th>
                            <th>No. PR</th>
                            <th>Vendor / Supplier</th>
                            <th>Nama Item</th>
                            <th>Qty</th>
                            <th>Harga Satuan (Rp)</th>
                            <th>Total (Rp)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($list_quotations)): ?>
                            <?php foreach ($list_quotations as $qtn): ?>
                                <?php 
                                    $total_val = $qtn['Qty'] * $qtn['Offered_Price'];
                                    $badge_rank = $qtn['Rank_Order'] == 1 ? 'badge-success' : 'badge-secondary';
                                ?>
                                <tr>
                                    <td><span class="badge <?= $badge_rank; ?> px-2 py-1 font-weight-bold" style="font-size: 11px;">Rank #<?= $qtn['Rank_Order']; ?></span></td>
                                    <td class="font-weight-bold"><?= htmlspecialchars($qtn['QTN_No']); ?></td>
                                    <td><?= htmlspecialchars($qtn['PR_No']); ?></td>
                                    <td><?= htmlspecialchars($qtn['Company_Name'] ?? $qtn['Supplier_ID']); ?></td>
                                    <td class="text-left"><?= htmlspecialchars($qtn['Product_Desc']); ?></td>
                                    <td><?= $qtn['Qty']; ?> <?= htmlspecialchars($qtn['Unit']); ?></td>
                                    <td class="text-right text-success font-weight-bold">Rp <?= number_format($qtn['Offered_Price'], 0, ',', '.'); ?></td>
                                    <td class="text-right font-weight-bold">Rp <?= number_format($total_val, 0, ',', '.'); ?></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($qtn['Status']); ?></span></td>
                                    <td>
                                        
                                            <?php if (strcasecmp($qtn['Status'], 'SELECTED') === 0): ?>
                                                <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 10px;">
                                                    <i class="fas fa-check-circle mr-1"></i> Terpilih / Selesai
                                                </span>
                                            <?php else: ?>
                                                <a href="po-create?qtn=<?= urlencode($qtn['QTN_No']); ?>" class="btn btn-sm btn-primary py-1 px-2 font-weight-bold shadow-sm" style="font-size: 10px;">
                                                    <i class="fas fa-check mr-1"></i> Terbitkan PO
                                                </a>
                                            <?php endif; ?>
                                        
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3">Belum ada penawaran harga QTN yang dimasukkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Panggil Bottom Bar -->
<?php include 'includes/bottombar.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Skrip Sidebar Mobile
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

    // Fungsi Kalkulasi Keuangan Otomatis QTN Real-Time
    function hitungFinansialQTN() {
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
        document.getElementById('lblPph').innerText = '- ' + formatRupiah(ppphNominal);
        document.getElementById('lblGrandTotal').innerText = formatRupiah(grandTotal);
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('calc-trigger')) {
            hitungFinansialQTN();
        }
    });

    document.getElementById('ppnPersen')?.addEventListener('change', hitungFinansialQTN);
    document.getElementById('pphPersen')?.addEventListener('change', hitungFinansialQTN);

    <?php if (!empty($success_message)): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= addslashes($success_message); ?>', confirmButtonColor: '#28a745' }).then(() => { window.location.href = 'qtn-create'; });
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= addslashes($error_message); ?>', confirmButtonColor: '#d33' });
    <?php endif; ?>
</script>
</body>
</html>