<?php
header('Content-Type: application/json');
include '../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);
$booking_id = $data['id'] ?? null;
$status = $data['status'] ?? '';

if (!$booking_id || !in_array($status, ['approved', 'rejected'])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
