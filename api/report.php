<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../config/db.php';

$type = $_GET['type'] ?? 'occupancy';

if ($type !== 'occupancy') {
    http_response_code(400);
    echo json_encode(['error' => 'Неподдерживаемый тип отчёта'], JSON_UNESCAPED_UNICODE);
    exit;
}

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

$stmt = $pdo->prepare(
    "SELECT
        (SELECT COUNT(*) FROM rooms) AS total_rooms,
        (SELECT COUNT(*) FROM bookings WHERE status IN ('confirmed', 'checked_in') AND check_in <= ? AND check_out >= ?) AS occupied"
);
$stmt->execute([$end, $start]);

echo json_encode($stmt->fetch() ?: ['total_rooms' => 0, 'occupied' => 0], JSON_UNESCAPED_UNICODE);
