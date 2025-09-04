<?php
// api/login_client.php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../includes/db.php';

$emailOrUser = trim($_POST['email'] ?? '');   // form field name
$password    = (string)($_POST['password'] ?? '');

if ($emailOrUser === '' || $password === '') {
  echo json_encode(['success' => false, 'message' => 'Please enter your email/username and password.']);
  exit;
}

try {
  // Read columns from `users`
  $colsStmt = $pdo->prepare("
      SELECT LOWER(COLUMN_NAME) AS c
      FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
  ");
  $colsStmt->execute();
  $columns = array_map(fn($r) => $r['c'], $colsStmt->fetchAll(PDO::FETCH_ASSOC));
  $has = fn($c) => in_array(strtolower($c), $columns, true);

  // Detect columns
  $emailCol = null;
  foreach (['email','email_address','user_email','username'] as $c) { if ($has($c)) { $emailCol = $c; break; } }
  if (!$emailCol) { echo json_encode(['success'=>false,'message'=>"No email/username column found in users table."]); exit; }

  $passCol = null;
  foreach (['password_hash','pass_hash','password','passwd'] as $c) { if ($has($c)) { $passCol = $c; break; } }
  if (!$passCol) { echo json_encode(['success'=>false,'message'=>"No password column found in users table."]); exit; }

  $nameCol = null;
  foreach (['name','full_name','client_name','user_name','username'] as $c) { if ($has($c)) { $nameCol = $c; break; } }

  $roleCol = null;
  foreach (['role','user_role'] as $c) { if ($has($c)) { $roleCol = $c; break; } }

  // Build SELECT without referencing missing columns
  $selectName = $nameCol ? "`$nameCol`" : "''";
  $selectRole = $roleCol ? "`$roleCol`" : "'user'";

  $sql = "SELECT id,
                 `$emailCol` AS email_like,
                 `$passCol`  AS pass_value,
                 $selectName AS display_name,
                 $selectRole AS role
          FROM users
          WHERE `$emailCol` = ?
          LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$emailOrUser]);
  $user = $stmt->fetch();

  if (!$user) { echo json_encode(['success'=>false,'message'=>'Account not found.']); exit; }

  $stored = (string)$user['pass_value'];
  // Works with hashed or legacy plain text
  $ok = password_verify($password, $stored) || hash_equals($stored, $password);

  if (!$ok) { echo json_encode(['success'=>false,'message'=>'Incorrect password.']); exit; }

  $_SESSION['user'] = [
    'id'    => (int)$user['id'],
    'name'  => (string)$user['display_name'],
    'email' => (string)$user['email_like'],
    'role'  => (string)$user['role'],
  ];
  echo json_encode(['success'=>true,'message'=>'Logged in!','user'=>$_SESSION['user']]);

} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
