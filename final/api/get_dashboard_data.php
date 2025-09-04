<?php
header('Content-Type: application/json');
include '../includes/db.php';

$role = $_GET['role'] ?? '';
$user_id = $_GET['user_id'] ?? 0;

$total_rooms = 0;
$total_bookings = 0;
$pending_approvals = 0;
$recent_bookings = [];

try {
    if ($role === 'owner') {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM rooms WHERE owner_id = ?");
        $stmt->bind_param("i", $user_id);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM rooms");
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $total_rooms = $result['total'];

    
    if ($role === 'guest') {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE guest_id = ?");
        $stmt->bind_param("i", $user_id);
    } elseif ($role === 'owner') {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE r.owner_id = ?");
        $stmt->bind_param("i", $user_id);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings");
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $total_bookings = $result['total'];

    
    if ($role === 'manager') {
        $stmt = $conn->prepare("SELECT COUNT(*) as pending FROM bookings WHERE status = 'waiting_approval'");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $pending_approvals = $result['pending'];
    } elseif ($role === 'owner') {
        $stmt = $conn->prepare("SELECT COUNT(*) as pending FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.status = 'waiting_approval' AND r.owner_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $pending_approvals = $result['pending'];
    }

    // Recent bookings 
    if ($role === 'owner') {
        $stmt = $conn->prepare("SELECT b.*, r.name as room_name, u.name as guest_name FROM bookings b JOIN rooms r ON b.room_id = r.id JOIN users u ON b.guest_id = u.id WHERE r.owner_id = ? ORDER BY b.created_at DESC LIMIT 5");
        $stmt->bind_param("i", $user_id);
    } else {
        $stmt = $conn->prepare("SELECT b.*, r.name as room_name, u.name as guest_name FROM bookings b JOIN rooms r ON b.room_id = r.id JOIN users u ON b.guest_id = u.id ORDER BY b.created_at DESC LIMIT 5");
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $recent_bookings[] = [
            "guest_name" => $row['guest_name'],
            "room_name" => $row['room_name'],
            "booking_date" => $row['created_at'],
            "status" => $row['status']
        ];
    }

    echo json_encode([
        "success" => true,
        "total_rooms" => $total_rooms,
        "total_bookings" => $total_bookings,
        "pending_approvals" => $pending_approvals,
        "recent_bookings" => $recent_bookings
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
?>
