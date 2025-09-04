<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['owner','admin']);
$d = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id = (int)($d['id'] ?? 0);
$action = $d['action'] ?? 'approve';
if (!$id || !in_array($action, ['approve','decline','cancel'], true)) {
  http_response_code(422);
  echo json_encode(['error' => 'Invalid input']);
  exit;
}
$status = $action === 'approve' ? 'approved' : ($action === 'decline' ? 'declined' : 'cancelled');
$stmt = $pdo->prepare('UPDATE bookings SET status=? WHERE id=?');
$stmt->execute([$status, $id]);
echo json_encode(['ok' => true]);
