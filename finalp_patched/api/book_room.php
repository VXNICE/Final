<?php
header('Content-Type: application/json');
include '../includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

$room_id = $data['room_id'];
$guest_id = $data['guest_id'];
$checkin = $data['checkin'];
$checkout = $data['checkout'];
$guests = $data['guests'];
$status = 'pending';

$sql = "INSERT INTO bookings (room_id, guest_id, checkin, checkout, guests, status) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissis", $room_id, $guest_id, $checkin, $checkout, $guests, $status);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Room booked. Awaiting approval."]);
} else {
    echo json_encode(["success" => false, "message" => "Booking failed"]);
}
?>
