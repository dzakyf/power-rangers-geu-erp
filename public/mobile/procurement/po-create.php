<?php
// 1. Panggil Header & Koneksi Database
include 'includes/header.php';
require_once 'db.php';

$success_message = '';
$error_message = '';
$current_username = $_SESSION['username'] ?? $_SESSION['name'] ?? 'ADMIN';

// Tangkap parameter QTN yang dipilih dari matriks QTN sebelumnya
$selected_qtn = $_GET['qtn'] ?? '';

// 2. PROSES TERBITKAN PO & SIMPAN SEMUA ITEM KE TABEL DETAIL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_po'])) {
    $qtn_no      = trim($_POST['qtn_no']);
    $pr_no       = trim($_POST['pr_no']);
    $supplier_id = trim($_POST['supplier_id']);
    $po_date     = trim($_POST['po_date']);

    try {
        $pdo->beginTransaction();

        // A. Ambil data header PR & QTN untuk melengkapi kolom purchase_order
        $stmt_pr = $pdo->prepare("SELECT * FROM purchase_request WHERE PR_No = :pr_no LIMIT 1");
        $stmt_pr->execute([':pr_no' => $pr_no]);
        $pr_data = $stmt_pr->fetch(PDO::FETCH_ASSOC);

        // Ambil SEMUA item barang dari quotation berdasarkan QTN_No
        $stmt_qtn_all = $pdo->prepare("SELECT * FROM quotation WHERE QTN_No = :qtn");
        $stmt_qtn_all->execute([':qtn' => $selected_qtn]);
        $qtn_items = $stmt_qtn_all->fetchAll(PDO::FETCH_ASSOC);

        if (!$pr_data || empty($qtn_items)) {
            throw new Exception("Data PR atau item Quotation tidak ditemukan di database.");
        }

        // B. Buat Nomor PO unik menggunakan mekanisme penguncian/max ID agar tidak kembar
        $bulanRomawi = array("", "I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
        $bln = $bulanRomawi[(int) date('m', strtotime($po_date))];
        $thn = date('Y', strtotime($po_date));

        $stmt_max = $pdo->query("SELECT MAX(ID) FROM purchase_order");
        $max_id = (int)$stmt_max->fetchColumn() + 1;
        $target_po_no = "PO/" . $bln . "/" . $thn . "/" . sprintf("%03d", $max_id);

        // C. Hitung total finansial dari seluruh item secara akurat
        $subtotal = 0;
        foreach ($qtn_items as $qi) {
            $subtotal += ($qi['Qty'] * $qi['Offered_Price']);
        }
        $ppn_val  = $subtotal * 0.11; 
        $pph_val  = 0; 
        $total    = $subtotal + $ppn_val - $pph_val;

        // D. Insert ke Header `purchase_order`
        $sql_po = "INSERT INTO purchase_order 
                   (PO_No, PR_No, Location, Process_By, Purchase_Type, Purchase_Category, Purchase_Sub_Category, 
                    PO_Date, Department, PIC_Address, PIC_Phone, PIC, Supplier, Subtotal, PPN, PPH, Total, Status, User_Trx, Date_Trx) 
                   VALUES 
                   (:po_no, :pr_no, :location, :process_by, :purchase_type, :purchase_category, :purchase_sub_category, 
                    :po_date, :department, :pic_address, :pic_phone, :pic, :supplier, :subtotal, :ppn, :pph, :total, 'ISSUED', :user_trx, NOW())";
        
        $stmt_ins_po = $pdo->prepare($sql_po);
        $stmt_ins_po->execute([
            ':po_no'                 => $target_po_no,
            ':pr_no'                 => $pr_no,
            ':location'              => $pr_data['Location'] ?? 'HO',
            ':process_by'            => 'MOBILE SYSTEM',
            ':purchase_type'         => $pr_data['Supplies_Type'] ?? 'Non-Ops',
            ':purchase_category'     => $pr_data['Category'] ?? 'GENERAL',
            ':purchase_sub_category' => $pr_data['Sub_Category'] ?? 'BARANG',
            ':po_date'               => $po_date,
            ':department'            => $pr_data['Department'] ?? 'ICT / HRGA',
            ':pic_address'           => $pr_data['PIC_Address'] ?? '-',
            ':pic_phone'             => $pr_data['PIC_Phone'] ?? '-',
            ':pic'                   => $pr_data['PIC'] ?? 'ADMIN',
            ':supplier'              => $supplier_id,
            ':subtotal'              => $subtotal,
            ':ppn'                   => $ppn_val,
            ':pph'                   => $pph_val,
            ':total'                 => $total,
            ':user_trx'              => $current_username
        ]);

        // E. Insert SEMUA item barang ke Detail `purchase_order_dtl` melalui perulangan (looping)
        $sql_podtl = "INSERT INTO purchase_order_dtl 
                      (PO_No, PR_No, Category, Sub_Category, Purchase_Type, Product_Desc, Specification, Qty, Unit, Price, Subtotal, PPN, Total) 
                      VALUES 
                      (:po_no, :pr_no, :category, :sub_category, :purchase_type, :product_desc, :specification, :qty, :unit, :price, :subtotal, :ppn, :total)";
        
        $stmt_ins_dtl = $pdo->prepare($sql_podtl);

        foreach ($qtn_items as $item) {
            $item_subtotal = $item['Qty'] * $item['Offered_Price'];
            $item_ppn      = $item_subtotal * 0.11;
            $item_total    = $item_subtotal + $item_ppn;

            $stmt_ins_dtl->execute([
                ':po_no'         => $target_po_no,
                ':pr_no'         => $pr_no,
                ':category'      => $pr_data['Category'] ?? 'GENERAL',
                ':sub_category'  => $pr_data['Sub_Category'] ?? 'BARANG',
                ':purchase_type' => $pr_data['Supplies_Type'] ?? 'Non-Ops',
                ':product_desc'  => $item['Product_Desc'],
                ':specification' => $item['Specification'] ?? '-',
                ':qty'           => $item['Qty'],
                ':unit'          => $item['Unit'],
                ':price'         => $item['Offered_Price'],
                ':subtotal'      => $item_subtotal,
                ':ppn'           => $item_ppn,
                ':total'         => $item_total
            ]);
        }

        // F. Update nomor PO, QTN_No & Status di tabel utama `purchase_request`
        $stmt_up_pr = $pdo->prepare("UPDATE purchase_request SET PO_No = :po_no, QTN_No = :qtn_no, Status = 'APPROVED' WHERE PR_No = :pr_no");
        $stmt_up_pr->execute([
            ':po_no'  => $target_po_no,
            ':qtn_no' => $selected_qtn,
            ':pr_no'  => $pr_no
        ]);

        // G. Update status di tabel `quotation` menjadi SELECTED
        $stmt_up_qtn = $pdo->prepare("UPDATE quotation SET Status = 'SELECTED' WHERE QTN_No = :qtn_no");
        $stmt_up_qtn->execute([':qtn_no' => $selected_qtn]);

        $pdo->commit();
        $success_message = "Purchase Order (PO) berhasil diterbitkan dengan Nomor: " . $target_po_no;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Gagal menerbitkan PO: " . $e->getMessage();
    }
}

// 3. Ambil Data QTN dan Rincian Item yang dipilih untuk ditampilkan di form konfirmasi
$qtn_item = null;
$qtn_details = [];
$subtotal_val = 0;
$ppn_val = 0;
$grand_total = 0;
$last_price_val = 0;

if (!empty($selected_qtn)) {
    try {
        $stmt_q = $pdo->prepare("SELECT q.*, s.Company_Name FROM quotation q LEFT JOIN supplier s ON q.Supplier_ID = s.Supplier_ID WHERE q.QTN_No = :qtn LIMIT 1");
        $stmt_q->execute([':qtn' => $selected_qtn]);
        $qtn_item = $stmt_q->fetch(PDO::FETCH_ASSOC);

        if ($qtn_item) {
            $stmt_items = $pdo->prepare("SELECT * FROM quotation WHERE QTN_No = :qtn");
            $stmt_items->execute([':qtn' => $selected_qtn]);
            $qtn_details = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

            foreach ($qtn_details as $item) {
                $subtotal_val += ($item['Qty'] * $item['Offered_Price']);
            }

            $ppn_val = $subtotal_val * 0.11;
            $grand_total = $subtotal_val + $ppn_val;

            $stmt_lp = $pdo->prepare("SELECT Default_Price FROM purchase_request_dtl WHERE Product_Desc = :desc AND PR_No != :pr_no ORDER BY ID DESC LIMIT 1");
            $stmt_lp->execute([
                ':desc'  => $qtn_item['Product_Desc'],
                ':pr_no' => $qtn_item['PR_No']
            ]);
            $last_price_val = floatval($stmt_lp->fetchColumn() ?: 0);
        }
    } catch (Exception $e) {}
}

// 4. Ambil Daftar PO yang sudah diterbitkan
try {
    $stmt_po_list = $pdo->query("SELECT po.*, s.Company_Name FROM purchase_order po LEFT JOIN supplier s ON po.Supplier = s.Supplier_ID ORDER BY po.ID DESC");
    $list_po = $stmt_po_list->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $list_po = [];
}
?>

<!-- Panggil Topbar & Sidebar -->
<?php include 'includes/topbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-file-contract text-success mr-2"></i>Purchase Order (PO) & Final Process</h5>
            <small class="text-muted">Pilih vendor pemenang dari QTN, tinjau item, perhitungan PPN, dan terbitkan PO resmi.</small>
        </div>
    </div>

    <!-- FORM TERBITKAN PO BERDASARKAN QTN -->
    <?php if (!empty($selected_qtn) && $qtn_item): ?>
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-success text-white py-2" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h6 class="mb-0 font-weight-bold" style="font-size: 13px;"><i class="fas fa-check-circle mr-2"></i>Konfirmasi Penerbitan PO untuk QTN: <?= htmlspecialchars($selected_qtn); ?></h6>
            </div>
            <div class="card-body bg-light">
                <form action="po-create?qtn=<?= urlencode($selected_qtn); ?>" method="POST" id="formPoCreate">
                    <input type="hidden" name="submit_po" value="1">
                    <input type="hidden" name="qtn_no" value="<?= htmlspecialchars($qtn_item['QTN_No']); ?>">
                    <input type="hidden" name="pr_no" value="<?= htmlspecialchars($qtn_item['PR_No']); ?>">
                    <input type="hidden" name="supplier_id" value="<?= htmlspecialchars($qtn_item['Supplier_ID']); ?>">

                    <div class="row mb-3" style="font-size: 12px;">
                        <div class="col-md-6">
                            <div><strong>Nomor PR Terkait:</strong> <?= htmlspecialchars($qtn_item['PR_No']); ?></div>
                            <div><strong>Vendor Pemenang:</strong> <span class="text-success font-weight-bold"><?= htmlspecialchars($qtn_item['Company_Name'] ?? $qtn_item['Supplier_ID']); ?></span></div>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <div class="text-primary font-weight-bold">Last Price Sebelumnya: Rp <?= number_format($last_price_val, 0, ',', '.'); ?></div>
                        </div>
                    </div>

                    <!-- TABEL RINCIAN ITEM BARANG -->
                    <label class="font-weight-bold text-dark small mb-1"><i class="fas fa-list-ul mr-1 text-success"></i> Rincian Item Barang / Jasa:</label>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm bg-white text-center" style="font-size: 12px;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nama Item</th>
                                    <th>Spesifikasi</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th>Harga Satuan (Rp)</th>
                                    <th>Total (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($qtn_details as $det): ?>
                                    <?php $item_total = $det['Qty'] * $det['Offered_Price']; ?>
                                    <tr>
                                        <td class="text-left"><?= htmlspecialchars($det['Product_Desc']); ?></td>
                                        <td class="text-left"><?= htmlspecialchars($det['Specification'] ?? '-'); ?></td>
                                        <td><?= $det['Qty']; ?></td>
                                        <td><?= htmlspecialchars($det['Unit']); ?></td>
                                        <td class="text-right">Rp <?= number_format($det['Offered_Price'], 0, ',', '.'); ?></td>
                                        <td class="text-right font-weight-bold">Rp <?= number_format($item_total, 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- KOTAK PERHITUNGAN FINANSIAL -->
                    <div class="bg-white p-3 rounded border mb-3 shadow-sm" style="font-size: 12px; max-width: 400px; margin-left: auto;">
                        <div class="d-flex justify-content-between mb-1 text-secondary">
                            <span>Subtotal:</span>
                            <span class="font-weight-bold text-dark">Rp <?= number_format($subtotal_val, 0, ',', '.'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-secondary">
                            <span>PPN (11%):</span>
                            <span class="font-weight-bold text-dark">Rp <?= number_format($ppn_val, 0, ',', '.'); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-secondary border-bottom pb-1">
                            <span>PPh (0%):</span>
                            <span class="font-weight-bold text-danger">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between font-weight-bold text-success pt-1" style="font-size: 14px;">
                            <span>Grand Total:</span>
                            <span>Rp <?= number_format($grand_total, 0, ',', '.'); ?></span>
                        </div>
                    </div>

                    <hr class="my-2">
                    <div class="form-group mb-3" style="max-width: 300px;">
                        <label class="small font-weight-bold text-secondary">Tanggal Terbit PO</label>
                        <input type="date" class="form-control form-control-sm" name="po_date" value="<?= date('Y-m-d'); ?>" required>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch" style="gap: 10px;">
                        <button type="button" id="btnSubmitPo" class="btn btn-success btn-sm font-weight-bold py-2 shadow-sm">
                            <i class="fas fa-paper-plane mr-1"></i> Terbitkan PO Resmi Sekarang
                        </button>
                        <a href="qtn-create" class="btn btn-secondary btn-sm font-weight-bold py-2 text-center">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke QTN
                        </a>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info py-2 shadow-sm" style="font-size: 13px; border-radius: 8px;">
            <i class="fas fa-info-circle mr-1"></i> Silakan pilih vendor dari menu <strong>QTN & Matriks Harga</strong> (klik tombol <em>Terbitkan PO</em> pada peringkat terbaik) untuk menerbitkan PO.
        </div>
    <?php endif; ?>

    <!-- TABEL DAFTAR PO -->
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header bg-white py-2" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
            <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 13px;"><i class="fas fa-list mr-2 text-success"></i>Daftar Purchase Order (PO) Aktif</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm text-center" style="font-size: 12px;">
                    <thead class="thead-light">
                        <tr>
                            <th>No. PO</th>
                            <th>No. PR</th>
                            <th>Vendor / Supplier</th>
                            <th>Tgl PO</th>
                            <th>Subtotal (Rp)</th>
                            <th>PPN (Rp)</th>
                            <th>Total (Rp)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($list_po)): ?>
                            <?php foreach ($list_po as $po): ?>
                                <tr>
                                    <td class="font-weight-bold text-success"><?= htmlspecialchars($po['PO_No']); ?></td>
                                    <td><?= htmlspecialchars($po['PR_No']); ?></td>
                                    <td class="text-left"><?= htmlspecialchars($po['Company_Name'] ?? $po['Supplier']); ?></td>
                                    <td><?= date('d/m/Y', strtotime($po['PO_Date'])); ?></td>
                                    <td class="text-right">Rp <?= number_format($po['Subtotal'], 0, ',', '.'); ?></td>
                                    <td class="text-right">Rp <?= number_format($po['PPN'], 0, ',', '.'); ?></td>
                                    <td class="text-right font-weight-bold">Rp <?= number_format($po['Total'], 0, ',', '.'); ?></td>
                                    <td><span class="badge badge-success"><?= htmlspecialchars($po['Status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">Nilai PO belum tersedia.</td>
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

    // Konfirmasi SweetAlert2 sebelum Terbitkan PO
    document.getElementById('btnSubmitPo')?.addEventListener('click', function() {
        Swal.fire({
            title: 'Terbitkan PO Resmi?',
            text: 'Apakah Anda yakin ingin menerbitkan Purchase Order ini? Dokumen yang diproses akan dikunci.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Terbitkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formPoCreate').submit();
            }
        });
    });

    <?php if (!empty($success_message)): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= addslashes($success_message); ?>', confirmButtonColor: '#28a745' }).then(() => { window.location.href = 'po-create'; });
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= addslashes($error_message); ?>', confirmButtonColor: '#d33' });
    <?php endif; ?>
</script>
</body>
</html>