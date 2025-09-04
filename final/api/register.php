<?php
// api/register.php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../includes/db.php'; // ensure $dbname = "stayfind3nf_db"

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');
$phone    = trim($_POST['phone'] ?? '');

/* removed the 6+ chars rule; only require non-empty password */
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
  echo json_encode(['success'=>false,'message'=>'Please enter a name, valid email, and a password.']);
  exit;
}

try {
  /* 1) Make sure the "roles" table and 'user' role exist (outside a transaction) */
  $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
      id INT PRIMARY KEY AUTO_INCREMENT,
      role_name VARCHAR(32) NOT NULL UNIQUE
    ) ENGINE=InnoDB");
  $pdo->exec("INSERT IGNORE INTO roles (role_name) VALUES ('user')");

  /* 2) Only the INSERT is transactional */
  $pdo->beginTransaction();

  // block duplicate email
  $dup = $pdo->prepare("SELECT 1 FROM users WHERE email=? LIMIT 1");
  $dup->execute([$email]);
  if ($dup->fetch()) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'Email already registered.']);
    exit;
  }

  $hash = password_hash($password, PASSWORD_DEFAULT);

  // insert with subquery to guarantee valid role_id
  $st = $pdo->prepare("
    INSERT INTO users (name, email, phone, password_hash, role_id, created_at)
    SELECT ?, ?, ?, ?, r.id, NOW()
    FROM roles r
    WHERE r.role_name='user'
    LIMIT 1
  ");
  $st->execute([$name, $email, $phone, $hash]);

  if ($st->rowCount() !== 1) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    throw new RuntimeException('Could not resolve role_id for role=user.');
  }

  $pdo->commit();
  echo json_encode(['success'=>true,'message'=>'Registration successful. Please log in.']);
} catch (Throwable $e) {
  if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
