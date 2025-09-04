<?php
header('Content-Type: application/json');
include '../includes/db.php';

$type = isset($_GET['type']) ? $_GET['type'] : null;         // house or condo
$location = isset($_GET['location']) ? $_GET['location'] : null;
$owner_id = isset($_GET['owner_id']) ? $_GET['owner_id'] : null;

$query = "SELECT rooms.*, users.fullname AS owner_name 
          FROM rooms 
          JOIN users ON rooms.owner_id = users.id 
          WHERE 1=1";

$params = [];
$types = "";

// Add filters if provided
if ($type) {
    $query .= " AND type = ?";
    $params[] = $type;
    $types .= "s";
}

if ($location) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}

if ($owner_id) {
    $query .= " AND owner_id = ?";
    $params[] = $owner_id;
    $types .= "i";
}

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$rooms = [];

while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

echo json_encode(["success" => true, "rooms" => $rooms]);
?>
