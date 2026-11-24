<?php
// Sesuaikan path relatif ke file koneksi yang mendefinisikan $pdo
$dbConfigPath = __DIR__ . '../db.php'; // ubah sesuai lokasi file koneksi Anda
if (!isset($pdo) && file_exists($dbConfigPath)) {
    require_once $dbConfigPath;
}

// Ambil identifier user login (sesuaikan dengan yang disimpan saat login)
$recipientId = $_SESSION['user_id']
    ?? $_SESSION['id']
    ?? $_SESSION['username']
    ?? '';

$notifications = [];
$unreadCount   = 0;

if (!empty($recipientId) && isset($pdo) && ($pdo instanceof PDO)) {
    $sql = "SELECT ID, Title, Content, Created_At, Read_At, Topic, Metadata, Status
            FROM notification
            WHERE Recipient_Id = :recipient_id
              AND (Status IS NULL OR Status != 'DELETED')
            ORDER BY Created_At DESC, ID DESC
            LIMIT 10";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':recipient_id' => $recipientId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($notifications as $n) {
        if (empty($n['Read_At']) && strtoupper((string)($n['Status'] ?? '')) !== 'READ') {
            $unreadCount++;
        }
    }
}

function format_notif_time($datetime) {
    if (empty($datetime)) return '-';
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit yang lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam yang lalu';
    return date('d M Y H:i', $ts);
}
?>

<!-- Navbar Atas -->
    <nav class="navbar navbar-custom navbar-expand">
        <button class="btn btn-link text-white p-0 mr-3" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        <span class="navbar-brand mx-auto">GEU</span>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fas fa-ellipsis-v"></i></a>
            </li>
        </ul>
    </nav>

    <!-- Topbar Sub -->
    <div class="topbar-sub">
        <div>
            <span class="font-weight-bold" style="font-size: 12px;">Procurement Flow</span>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge badge-light text-dark px-2 py-1 mr-3" style="font-size: 10px;">Active Module</span>
            
            <!-- Ikon Lonceng Dropdown Notifikasi -->
<div class="dropdown">
    <a href="#" class="text-white position-relative" id="notifDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell fa-lg"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="badge badge-danger" style="position: absolute; top: -5px; right: -6px; font-size: 8px; width: 7px; height: 7px; padding: 0; border-radius: 50%;"></span>
        <?php endif; ?>
    </a>
    <div class="dropdown-menu dropdown-menu-right notification-dropdown" aria-labelledby="notifDropdown">
        <div class="notification-header">
            <span>Notifikasi Sistem</span>
            <span class="badge badge-primary badge-pill"><?= (int)$unreadCount; ?> Baru</span>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="notification-item text-center text-muted">
                Tidak ada notifikasi.
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif):
                $meta = !empty($notif['Metadata']) ? json_decode($notif['Metadata'], true) : [];
                $targetUrl = is_array($meta) && !empty($meta['url']) ? $meta['url'] : '#';
                $isUnread = empty($notif['Read_At']) && strtoupper((string)($notif['Status'] ?? '')) !== 'READ';
            ?>
                <a href="<?= htmlspecialchars($targetUrl); ?>"
                   class="notification-item"
                   style="<?= $isUnread ? 'background-color: #f3f0ff; font-weight: 600;' : ''; ?>">
                    <strong><?= htmlspecialchars($notif['Title'] ?? 'Notifikasi'); ?></strong>
                    <?php if (!empty($notif['Content'])): ?>
                        <div><?= htmlspecialchars($notif['Content']); ?></div>
                    <?php endif; ?>
                    <span class="time">
                        <i class="far fa-clock mr-1"></i> <?= htmlspecialchars(format_notif_time($notif['Created_At'] ?? null)); ?>
                    </span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
        </div>
    </div>