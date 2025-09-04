<?php
$host = "127.0.0.1";
$dbname = "stayfind3nf_db";  
$user = "root";
$pass = "";
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
]);
