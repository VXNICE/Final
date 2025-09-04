<?php
header('Content-Type: application/json');
include '../includes/db.php';

try {
  $result = $conn->query("SELECT id, name, email, role, status FROM users ORDER BY id DESC");
  $users = [];

  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }

  echo json_encode(["success" => true, "users" => $users]);
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
