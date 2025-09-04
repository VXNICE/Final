<?php
// includes/csrf.php
declare(strict_types=1);

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function verify_csrf(): void {
  $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
  if (!hash_equals($_SESSION['csrf'] ?? '', $header)) {
    http_response_code(419);
    echo json_encode(['error' => 'CSRF token mismatch']);
    exit;
  }
}
