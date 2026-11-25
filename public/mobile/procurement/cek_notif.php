<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) { echo '[]'; exit; }

$stmt = $pdo->prepare("
    SELECT ID, Title, Content, Topic, Created_At
    FROM notification
    WHERE Recipient_Id = :uid
      AND Read_At IS NULL
      AND (Expires_At IS NULL OR Expires_At > NOW())
    ORDER BY ID ASC
    LIMIT 5
");
$stmt->execute(['uid' => $user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tandai sudah dibaca supaya tidak muncul lagi
if ($rows) {
    $ids = array_column($rows, 'ID');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $pdo->prepare("UPDATE notification SET Read_At = NOW() WHERE ID IN ($in)")->execute($ids);
}

echo json_encode($rows);