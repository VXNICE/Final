<?php
// includes/auth.php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function current_user() {
  return $_SESSION['user'] ?? null;
}
function is_manager_like(?array $u): bool {
  $r = strtolower($u['role_name'] ?? '');
  return in_array($r, ['admin','owner','manager'], true);
}
function require_login_json() {
  if (empty($_SESSION['user'])) { http_response_code(401); echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
}
