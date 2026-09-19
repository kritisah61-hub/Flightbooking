<?php
require 'db.php';

$flight_id = $_GET['flight_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT seat_no 
    FROM bookings 
    WHERE flight_id = ? 
    AND status != 'Cancelled'
");
$stmt->execute([$flight_id]);

$seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($seats);