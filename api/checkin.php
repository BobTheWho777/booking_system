<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['user']) || (string)($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Требуется вход в админку'], JSON_UNESCAPED_UNICODE);
    exit;
}

require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не разрешён'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$bookingId = (int)($input['bookingId'] ?? 0);

if ($bookingId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректный ID бронирования'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare("UPDATE bookings SET status = 'checked_in' WHERE id = ?");
$stmt->execute([$bookingId]);

echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
