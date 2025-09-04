<?php
header('Content-Type: application/json');
include '../includes/db.php';

$role = $_GET['role'] ?? '';
$user_id = $_GET['user_id'] ?? 0;

try {
    if ($role === 'owner') {
        $stmt = $conn->prepare("SELECT b.*, r.name as room_name, u.name as guest_name
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            JOIN users u ON b.guest_id = u.id
            WHERE b.status = 'waiting_approval' AND r.owner_id = ?");
        $stmt->bind_param("i", $user_id);
    } else {
        // Manager or admin: show all pending
        $stmt = $conn->prepare("SELECT b.*, r.name as room_name, u.name as guest_name
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            JOIN users u ON b.guest_id = u.id
            WHERE b.status = 'waiting_approval'");
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }

    echo json_encode(["success" => true, "bookings" => $bookings]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
