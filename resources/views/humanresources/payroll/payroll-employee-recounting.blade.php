<?php

use Illuminate\Support\Facades\Auth;
use App\Services\Payroll\PayrollCalculationService;

$mainId = isset($_GET['Main_ID']) ? $_GET['Main_ID'] : null;
$currentUser = Auth::user() ? Auth::user()->username : 'SYSTEM';

if (!$mainId) {
    echo "<div class='alert alert-warning'>Parameter Main_ID tidak ditemukan.</div>";
    return;
}

try {
    $calcService = app(PayrollCalculationService::class);
    $result = $calcService->recountSalaryGroup($mainId, date('Y-m'), $currentUser);
?>
    <div class="alert alert-success border-0 rounded-4 shadow-sm p-4 mt-3">
        <h5 class="fw-bold text-success mb-2">
            <i class="fa fa-check-circle me-1"></i> Recounting Payroll Berhasil Selesai
        </h5>
        <p class="mb-2 text-muted">Kalkulasi telah disinkronkan dengan data presensi terkini (Alpha/Unpaid, Keterlambatan), BPJS TK (2%), BPJS Kesehatan (1% basis UMK & cap 120k), serta PPh 21 skema TER (PP 58/2023) / Pasal 17 UU HPP.</p>
        <div class="row g-2 mt-2">
            <div class="col-md-3">
                <div class="card p-2 bg-white border-0 shadow-sm text-center">
                    <small class="text-muted">Total Karyawan</small>
                    <span class="fw-bold fs-5 text-dark"><?= count($result['employees']) ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-2 bg-white border-0 shadow-sm text-center">
                    <small class="text-muted">Total PPh 21 (TER / Ps. 17)</small>
                    <span class="fw-bold fs-5 text-danger">Rp <?= number_format($result['total_tax'], 0, ',', '.') ?></span>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card p-2 bg-white border-0 shadow-sm text-center">
                    <small class="text-muted">Total Take Home Pay (THP)</small>
                    <span class="fw-bold fs-5 text-primary">Rp <?= number_format($result['total_thp'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>
    <script>
        if (typeof refreshdata === 'function') {
            refreshdata();
        }
    </script>
<?php
} catch (\Exception $e) {
    echo "<div class='alert alert-danger border-0 rounded-4 shadow-sm p-3 mt-3'>
        <strong>Gagal Recounting:</strong> " . htmlspecialchars($e->getMessage()) . "
    </div>";
}
?>
