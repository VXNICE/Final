<?php
declare(strict_types=1);
ini_set('display_errors', 1); error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php'; 

try {
  $db = $pdo->query('SELECT DATABASE() AS db')->fetch();
  $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
  echo "DB: " . ($db['db'] ?? '(none)') . "<br>";
  echo "Tables: " . implode(', ', $tables);
} catch (Throwable $e) {
  echo "FAILED: " . $e->getMessage();
}
