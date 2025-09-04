<?php
header('Content-Type: application/json');
include '../includes/db.php';

if (!isset($_GET['owner_id'])) {
    echo json_encode(["success" => false, "message" => "Missing owner_id"]);
    exit;
}

$owner_id = $_GET['owner_id'];

$sql = "SELECT * FROM rooms WHERE owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();

$result = $stmt->get_result();
$rooms = [];

while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

echo json_encode(["success" => true, "rooms" => $rooms]);
?>
