<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login_json();
$user = current_user();

$booking_id = (int)($_POST['booking_id'] ?? 0);
$method = trim($_POST['method'] ?? '');
$reference = trim($_POST['reference'] ?? '');

if (!$booking_id || $method === '') {
    echo json_encode(['success'=>false,'message'=>'Missing fields']);
    exit;
}

try {
    $st = $pdo->prepare('SELECT user_id FROM bookings WHERE id=?');
    $st->execute([$booking_id]);
    $b = $st->fetch();
    if (!$b) { echo json_encode(['success'=>false,'message'=>'Booking not found']); exit; }
    if ($b['user_id'] != $user['id'] && !is_manager_like($user)) {
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Forbidden']);
        exit;
    }

    $receiptPath = null;
    if (!empty($_FILES['receipt']['name'])) {
        $f = $_FILES['receipt'];
        if ($f['error'] !== UPLOAD_ERR_OK) { throw new RuntimeException('Upload error'); }
        if ($f['size'] > 5*1024*1024) { throw new RuntimeException('File too large'); }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($f['tmp_name']);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','application/pdf'=>'pdf'];
        if (!isset($allowed[$mime])) { throw new RuntimeException('Invalid file type'); }
        $dir = __DIR__ . '/../uploads/payments';
        if (!is_dir($dir)) mkdir($dir,0755,true);
        $name = bin2hex(random_bytes(16)).'.'.$allowed[$mime];
        $dest = $dir.'/'.$name;
        move_uploaded_file($f['tmp_name'],$dest);
        $receiptPath = 'uploads/payments/'.$name;
    }

    $st = $pdo->prepare('UPDATE bookings SET payment_method=?, payment_reference=?, payment_receipt_path=?, payment_status="pending" WHERE id=?');
    $st->execute([$method,$reference,$receiptPath,$booking_id]);
    echo json_encode(['success'=>true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
