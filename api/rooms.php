<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/../config/app.php';
require __DIR__ . '/../config/db.php';

$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';
$guests = (int)($_GET['guests'] ?? 1);

if ($checkin === '' || $checkout === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Укажите даты'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strtotime($checkin) === false || strtotime($checkout) === false || strtotime($checkout) <= strtotime($checkin)) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректные даты'], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "
    SELECT
        r.id,
        r.number,
        r.status,
        rt.name AS type_name,
        r.price,
        rt.capacity,
        r.description
    FROM rooms r
    JOIN room_types rt ON rt.id = r.type_id
    WHERE r.status = 'available'
      AND rt.capacity >= ?
      AND r.id NOT IN (
          SELECT room_id
          FROM bookings
          WHERE status IN ('confirmed', 'checked_in')
            AND ((check_in <= ? AND check_out > ?) OR (check_in < ? AND check_out >= ?))
      )
    ORDER BY r.number
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$guests, $checkout, $checkin, $checkout, $checkin]);

$rooms = $stmt->fetchAll();

// Добавляем изображения и характеристики к каждой комнате
foreach ($rooms as &$room) {
    $stmtImg = $pdo->prepare('SELECT image_path FROM room_images WHERE room_id = ? ORDER BY sort_order, id');
    $stmtImg->execute([$room['id']]);
    $room['images'] = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
    
    $stmtFeat = $pdo->prepare('SELECT feature_code, feature_value FROM room_features WHERE room_id = ? ORDER BY sort_order, id LIMIT 4');
    $stmtFeat->execute([$room['id']]);
    $room['features'] = $stmtFeat->fetchAll();
}

echo json_encode($rooms, JSON_UNESCAPED_UNICODE);
