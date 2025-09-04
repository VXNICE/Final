<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login_json();
$user = current_user();

$room_id = (int)($_POST['room_id'] ?? 0);
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$guests = (int)($_POST['guests'] ?? 1);
$extras = $_POST['extras'] ?? '[]';
$notes = trim($_POST['notes'] ?? '');

if (!$room_id || !$start_date || !$end_date) {
    echo json_encode(['success'=>false,'message'=>'Missing required fields']);
    exit;
}
if (strtotime($end_date) < strtotime($start_date)) {
    echo json_encode(['success'=>false,'message'=>'End date must be on or after start date']);
    exit;
}

// availability check
try {
    $st = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id=? AND status_id IN (1,2) AND NOT (end_date < ? OR start_date > ?)");
    $st->execute([$room_id, $start_date, $end_date]);
    if ($st->fetchColumn() > 0) {
        echo json_encode(['success'=>false,'message'=>'Room not available for selected dates']);
        exit;
    }
    $st = $pdo->prepare("INSERT INTO bookings (room_id,user_id,start_date,end_date,guests,extras,notes,status_id,payment_status) VALUES (?,?,?,?,?,?,?,2,'unpaid')");
    $st->execute([$room_id,$user['id'],$start_date,$end_date,$guests,$extras,$notes]);
    $id = (int)$pdo->lastInsertId();
    echo json_encode(['success'=>true,'booking_id'=>$id]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
