<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['user','owner','admin']);

$maxMB = (int)($_ENV['MAX_UPLOAD_MB'] ?? 5);
$maxBytes = $maxMB * 1024 * 1024;

if (empty($_FILES['file']) || empty($_POST['booking_id'])) {
  http_response_code(422);
  echo json_encode(['error' => 'File and booking_id required']);
  exit;
}

$bookingId = (int)$_POST['booking_id'];
$f = $_FILES['file'];
if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > $maxBytes) {
  http_response_code(422);
  echo json_encode(['error' => 'Upload error or file too large']);
  exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($f['tmp_name']);
$allowed = ['image/jpeg','image/png','application/pdf'];
if (!in_array($mime, $allowed, true)) {
  http_response_code(415);
  echo json_encode(['error' => 'Unsupported file type']);
  exit;
}

$ext = $mime === 'application/pdf' ? 'pdf' : ($mime === 'image/png' ? 'png' : 'jpg');
$dir = __DIR__ . '/../uploads';
if (!is_dir($dir)) mkdir($dir, 0755, true);
$basename = bin2hex(random_bytes(16)) . '.' . $ext;
$dest = $dir . '/' . $basename;
move_uploaded_file($f['tmp_name'], $dest);

$stmt = $pdo->prepare('INSERT INTO receipts (booking_id,file_path,mime_type) VALUES (?,?,?)');
$stmt->execute([$bookingId, 'uploads/' . $basename, $mime]);

echo json_encode(['ok' => true, 'path' => 'uploads/' . $basename]);
