<?php
// 1. Panggil Header (Proteksi Session & CSS)
include 'includes/header.php';
require_once 'db.php';

// Ambil ringkasan statistik terkini dari database
try {
    $total_non_ops = $pdo->query("SELECT COUNT(*) FROM purchase_request WHERE Supplies_Type = 'Non-Ops'")->fetchColumn();
    $total_pending = $pdo->query("SELECT COUNT(*) FROM purchase_request WHERE Status IN ('Pending', 'APPROVAL_UPPER')")->fetchColumn();
    $total_approved = $pdo->query("SELECT COUNT(*) FROM purchase_request WHERE Status IN ('Approved', 'APPROVED')")->fetchColumn();
    $total_po = $pdo->query("SELECT COUNT(*) FROM purchase_order")->fetchColumn();
} catch (Exception $e) {
    $total_non_ops = 0;
    $total_pending = 0;
    $total_approved = 0;
    $total_po = 0;
}
?>

    <!-- 2. Panggil Topbar -->
    <?php include 'includes/topbar.php'; ?>

    <!-- 3. Panggil Sidebar Drawer -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- 4. CONTAINER / KONTEN UTAMA DASHBOARD PROCUREMENT -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="text-dark font-weight-bold mb-0"><i class="fas fa-tachometer-alt text-success mr-2"></i>Dashboard Procurement</h5>
                <small class="text-muted">Pusat kendali alur pengadaan barang (PR, Approval, RFQ, QTN, hingga PO).</small>
            </div>
        </div>

        <!-- KOTAK STATISTIK RINGKAS -->
        <div class="row mb-3">
            <div class="col-md-3 mb-2">
                <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 10px;">
                    <div class="d-flex align-items-center">
                        <div class="bg-light-success p-3 rounded text-success mr-3" style="font-size: 20px;"><i class="fas fa-file-invoice"></i></div>
                        <div>
                            <span class="text-muted small">Total PR</span>
                            <h4 class="font-weight-bold mb-0 text-dark"><?= $total_non_ops; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 10px;">
                    <div class="d-flex align-items-center">
                        <div class="bg-light-warning p-3 rounded text-warning mr-3" style="font-size: 20px;"><i class="fas fa-clock"></i></div>
                        <div>
                            <span class="text-muted small">Pending / Approval</span>
                            <h4 class="font-weight-bold mb-0 text-dark"><?= $total_pending; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 10px;">
                    <div class="d-flex align-items-center">
                        <div class="bg-light-info p-3 rounded text-info mr-3" style="font-size: 20px;"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <span class="text-muted small">Disetujui (Approved)</span>
                            <h4 class="font-weight-bold mb-0 text-dark"><?= $total_approved; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 10px;">
                    <div class="d-flex align-items-center">
                        <div class="bg-light-primary p-3 rounded text-primary mr-3" style="font-size: 20px;"><i class="fas fa-file-contract"></i></div>
                        <div>
                            <span class="text-muted small">PO Terbit</span>
                            <h4 class="font-weight-bold mb-0 text-dark"><?= $total_po; ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MENU UTAMA SESUAI FLOWCHART PROCUREMENT -->
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background-color: #fafbfc;">
            <div class="card-body">
                <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-sitemap mr-2 text-success"></i>Alur Modul Pengadaan (Procurement Flow)</h6>
                
                <div class="row">
                    <!-- 1. Procurement Ops & Non-Ops -->
                    <div class="col-md-4 mb-3">
                        <div class="card border shadow-sm p-3 h-100 bg-white" style="border-radius: 10px;">
                            <h6 class="font-weight-bold text-success"><i class="fas fa-boxes mr-2"></i>1. Buat PR (Purchase Requisition)</h6>
                            <p class="text-muted small mb-3">Input pengajuan barang operasional atau non-operasional lengkap dengan rincian item & lampiran.</p>
                            <div class="mt-auto">
                                <a href="procurement-non-ops.php" class="btn btn-success btn-sm mb-1 btn-block"><i class="fas fa-plus mr-1"></i> Buka Menu PR Non-Ops</a>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Approval Center -->
                    <div class="col-md-4 mb-3">
                        <div class="card border shadow-sm p-3 h-100 bg-white" style="border-radius: 10px;">
                            <h6 class="font-weight-bold text-warning"><i class="fas fa-tasks mr-2"></i>2. Approval Center</h6>
                            <p class="text-muted small mb-3">Pusat persetujuan dokumen PR oleh manajemen dengan filter rentang tanggal & catatan keputusan.</p>
                            <div class="mt-auto">
                                <a href="approval-center" class="btn btn-warning btn-sm text-dark mb-1 btn-block"><i class="fas fa-clipboard-check mr-1"></i> Buka Approval Center</a>
                            </div>
                        </div>
                    </div>

                    <!-- 3. RFQ (Request for Quotation) -->
                    <div class="col-md-4 mb-3">
                        <div class="card border shadow-sm p-3 h-100 bg-white" style="border-radius: 10px;">
                            <h6 class="font-weight-bold text-primary"><i class="fas fa-file-invoice-dollar mr-2"></i>3. RFQ (Multi Vendor)</h6>
                            <p class="text-muted small mb-3">Kirimkan permintaan penawaran harga ke banyak vendor berdasarkan PR yang sudah disetujui.</p>
                            <div class="mt-auto">
                                <a href="rfq-create" class="btn btn-primary btn-sm mb-1 btn-block"><i class="fas fa-paper-plane mr-1"></i> Kelola RFQ Vendor</a>
                            </div>
                        </div>
                    </div>

                    <!-- 4. QTN & Matriks Harga -->
                    <div class="col-md-4 mb-3">
                        <div class="card border shadow-sm p-3 h-100 bg-white" style="border-radius: 10px;">
                            <h6 class="font-weight-bold text-info"><i class="fas fa-chart-line mr-2"></i>4. QTN & Matriks Harga</h6>
                            <p class="text-muted small mb-3">Input penawaran harga dari vendor dan analisis matriks harga terendah secara otomatis.</p>
                            <div class="mt-auto">
                                <a href="qtn-create" class="btn btn-info btn-sm text-white mb-1 btn-block"><i class="fas fa-list-ol mr-1"></i> Buka Matriks Harga QTN</a>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Purchase Order (PO) -->
                    <div class="col-md-4 mb-3">
                        <div class="card border shadow-sm p-3 h-100 bg-white" style="border-radius: 10px;">
                            <h6 class="font-weight-bold text-dark"><i class="fas fa-file-contract mr-2"></i>5. Terbitkan PO Resmi</h6>
                            <p class="text-muted small mb-3">Pilih vendor pemenang dari matriks QTN, tinjau kalkulasi PPN, dan terbitkan Purchase Order.</p>
                            <div class="mt-auto">
                                <a href="po-create" class="btn btn-dark btn-sm mb-1 btn-block"><i class="fas fa-check-circle mr-1"></i> Buat & Kelola PO</a>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Tracking & Monitor -->
                    <div class="col-md-4 mb-3">
                        <div class="card border shadow-sm p-3 h-100 bg-white" style="border-radius: 10px;">
                            <h6 class="font-weight-bold text-secondary"><i class="fas fa-shipping-fast mr-2"></i>6. Tracking & Monitoring</h6>
                            <p class="text-muted small mb-3">Pantau seluruh riwayat alur kerja dokumen dari awal pengajuan PR hingga PO diterbitkan.</p>
                            <div class="mt-auto">
                                <a href="tracking" class="btn btn-secondary btn-sm mb-1 btn-block"><i class="fas fa-search mr-1"></i> Tracking Dokumen</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- 5. Panggil Bottom Bar -->
    <?php include 'includes/bottombar.php'; ?>

    <!-- jQuery, Bootstrap JS, & SweetAlert2 JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JavaScript Interaksi Global -->
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

        // SweetAlert2 Konfirmasi Logout
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