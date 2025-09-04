<?php

declare(strict_types=1);
header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../includes/db.php'; 

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = $_POST;

$login = trim((string)($input['email'] ?? ''));   
$pass  = (string)($input['password'] ?? '');

if ($login === '' || $pass === '') {
  http_response_code(400);
  echo json_encode(['success'=>false, 'message'=>'Please enter your email/username and password.']);
  exit;
}

try {
 
  $currentDb = (string)$pdo->query("SELECT DATABASE()")->fetchColumn();

  
  $loginLower = mb_strtolower($login);


  $sql = "SELECT u.id, u.name, u.email, u.username,
                 u.password_hash, u.password, u.role_id,
                 r.role_name
          FROM users u
          LEFT JOIN roles r ON r.id = u.role_id
          WHERE LOWER(u.email) = ? OR LOWER(u.username) = ?
          LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute([$loginLower, $loginLower]);
  $u = $st->fetch();

  if (!$u) {
    http_response_code(401);
    echo json_encode([
      'success'=>false,
      'message'=>"Account not found for '{$login}'. DB: {$currentDb}"
    ]);
    exit;
  }

 
  $ok = false;
  if (!empty($u['password_hash'])) {
    $ok = password_verify($pass, $u['password_hash']);
  }
  if (!$ok && !empty($u['password'])) {
    $ok = hash_equals((string)$u['password'], $pass);
  }

  if (!$ok) {
    http_response_code(401);
    echo json_encode(['success'=>false, 'message'=>'Incorrect password.']);
    exit;
  }


  $_SESSION['user'] = [
    'id'        => (int)$u['id'],
    'name'      => (string)($u['name'] ?: $u['username'] ?: $u['email']),
    'email'     => (string)$u['email'],
    'role_id'   => (int)$u['role_id'],
    'role_name' => (string)($u['role_name'] ?? ''),
  ];
  session_regenerate_id(true);

  echo json_encode(['success'=>true, 'user'=>$_SESSION['user']]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success'=>false, 'message'=>'Error: '.$e->getMessage()]);
}
