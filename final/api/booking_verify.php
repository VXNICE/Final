<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login_json();
$user = current_user();
if (!is_manager_like($user)) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden']);
    exit;
}

$booking_id = (int)($_POST['booking_id'] ?? 0);
$action = $_POST['action'] ?? '';
if (!$booking_id || !in_array($action, ['approve','decline'], true)) {
    echo json_encode(['success'=>false,'message'=>'Invalid input']);
    exit;
}

try {
    if ($action === 'approve') {
        $st = $pdo->prepare("UPDATE bookings SET payment_status='paid', status_id=1 WHERE id=?");
    } else {
        $st = $pdo->prepare("UPDATE bookings SET payment_status='failed', status_id=3 WHERE id=?");
    }
    $st->execute([$booking_id]);
    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
