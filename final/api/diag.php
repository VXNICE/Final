<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

try {
  $db = $pdo->query("SELECT DATABASE()")->fetchColumn();
  $roles = (int)$pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
  $userRole = (int)$pdo->query("SELECT id FROM roles WHERE role_name='user' LIMIT 1")->fetchColumn();
  echo json_encode([
    'current_db'   => $db,
    'roles_count'  => $roles,
    'user_role_id' => $userRole
  ], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
  echo json_encode(['error' => $e->getMessage()]);
}
