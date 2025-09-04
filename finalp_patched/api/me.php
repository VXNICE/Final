<?php
declare(strict_types=1);
header('Content-Type: application/json');
session_start();
echo json_encode(['user' => $_SESSION['user'] ?? null]);
