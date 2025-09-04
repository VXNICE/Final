<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login_json();
$user = current_user();

try {
    if (is_manager_like($user)) {
        $sql = "SELECT b.*, u.name AS user_name, r.title AS room_title FROM bookings b
                JOIN users u ON b.user_id=u.id
                JOIN rooms r ON b.room_id=r.id
                ORDER BY b.created_at DESC";
        $st = $pdo->query($sql);
    } else {
        $st = $pdo->prepare("SELECT b.*, r.title AS room_title FROM bookings b JOIN rooms r ON b.room_id=r.id WHERE b.user_id=? ORDER BY b.created_at DESC");
        $st->execute([$user['id']]);
    }
    $rows = $st->fetchAll();
    echo json_encode(['success'=>true,'bookings'=>$rows]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
