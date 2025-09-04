<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../includes/db.php';

$user = $_SESSION['user'] ?? null;
$role = strtolower($user['role_name'] ?? '');
if (!$user || !in_array($role, ['admin','owner','manager'], true)) {
  http_response_code(403);
  echo json_encode(['success'=>false,'message'=>'Not allowed']); exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }

try {
  if ($role !== 'admin') {
    $own = $pdo->prepare("SELECT owner_id FROM rooms WHERE id=?");
    $own->execute([$id]);
    $owner_id = (int)($own->fetchColumn() ?? 0);
    if ($owner_id !== (int)$user['id']) {
      http_response_code(403);
      echo json_encode(['success'=>false,'message'=>'You can only delete your own rooms']);
      exit;
    }
  }

  $st = $pdo->prepare("DELETE FROM rooms WHERE id=?");
  $st->execute([$id]);

  echo json_encode(['success'=>true]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
