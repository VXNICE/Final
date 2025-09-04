<?php
declare(strict_types=1);
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
  require_role(['user','owner','admin']);
  $d = json_decode(file_get_contents('php://input'), true) ?? $_POST;
  
  $checkin = $d['checkin'] ?? null;
  $checkout = $d['checkout'] ?? null;
  $guests = (int)($d['guests'] ?? 0);
  $payment_method = $d['paymentMethod'] ?? 'banktransfer'; // Default payment method

  if (!$checkin || !$checkout || !$guests) {
    echo json_encode(['success' => false, 'message' => 'Please provide valid check-in, check-out dates, and number of guests.']);
    exit;
  }

  // Assuming room_id and user_id are passed as part of the form data
  $room_id = (int)($d['room_id'] ?? 0);
  $user_id = (int)$_SESSION['user']['id'];

  if (!$room_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Room or user data missing.']);
    exit;
  }

  // Check if the room is already booked
  $stmt = $pdo->prepare("SELECT is_booked FROM rooms WHERE id = ?");
  $stmt->execute([$room_id]);
  $room = $stmt->fetch();

  if ($room && $room['is_booked'] == 1) {
    echo json_encode(['success' => false, 'message' => 'The room is already booked.']);
    exit;
  }

  // Insert booking data into the database
  try {
    // Insert booking data
    $sql = "INSERT INTO bookings (user_id, room_id, checkin, checkout, guests, payment_method, status_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $status = 1; // Pending status
    $stmt->execute([$user_id, $room_id, $checkin, $checkout, $guests, $payment_method, $status]);

    // Mark room as booked
    $update_room_sql = "UPDATE rooms SET is_booked = 1 WHERE id = ?";
    $update_stmt = $pdo->prepare($update_room_sql);
    $update_stmt->execute([$room_id]);

    echo json_encode(['success' => true, 'message' => 'Booking confirmed!']);
  } catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error processing booking: ' . $e->getMessage()]);
  }
}
