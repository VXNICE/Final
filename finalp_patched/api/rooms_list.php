<?php
// api/rooms_list.php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../includes/db.php';

$loc = trim($_GET['location'] ?? '');
$min = $_GET['min'] ?? '';
$max = $_GET['max'] ?? '';

$where = [];
$vals  = [];

if ($loc !== '') { $where[] = 'r.location = ?'; $vals[] = $loc; }
if ($min !== '' && is_numeric($min)) { $where[] = 'r.price_per_night >= ?'; $vals[] = (float)$min; }
if ($max !== '' && is_numeric($max)) { $where[] = 'r.price_per_night <= ?'; $vals[] = (float)$max; }

$sql = "SELECT
          r.id,
          r.title,
          r.location,
          r.location_link,
          r.capacity,
          r.description,
          r.price_per_night AS price,
          r.image_path      AS image
        FROM rooms r";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY r.created_at DESC";

try {
  $st = $pdo->prepare($sql);
  $st->execute($vals);
  $rows = $st->fetchAll();
  echo json_encode(['success'=>true, 'rooms'=>$rows]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false, 'message'=>'Error: '.$e->getMessage()]);
}
