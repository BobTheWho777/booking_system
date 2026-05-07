<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не разрешён'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректный JSON'], JSON_UNESCAPED_UNICODE);
    exit;
}

$roomId = (int)($input['roomId'] ?? 0);
$checkIn = (string)($input['checkIn'] ?? '');
$checkOut = (string)($input['checkOut'] ?? '');
$guestCount = (int)($input['guestCount'] ?? 0);
$guest = $input['guest'] ?? [];

// Валидация обязательных полей
foreach (['firstName', 'lastName', 'phone', 'email'] as $field) {
    if (trim((string)($guest[$field] ?? '')) === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Заполните все обязательные поля'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Базовая проверка входных данных
if ($roomId <= 0 || $guestCount <= 0 || strtotime($checkIn) === false || strtotime($checkOut) === false || strtotime($checkOut) <= strtotime($checkIn)) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректные данные бронирования'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Блокировка комнаты на время транзакции
    $roomStmt = $pdo->prepare(
        "SELECT r.id, r.status, r.price, rt.capacity
        FROM rooms r
        JOIN room_types rt ON rt.id = r.type_id
        WHERE r.id = ?
        FOR UPDATE"
    );
    $roomStmt->execute([$roomId]);
    $room = $roomStmt->fetch();

    if (!$room || $room['status'] !== 'available') {
        throw new RuntimeException('Выбранный номер недоступен');
    }

    if ((int)$room['capacity'] < $guestCount) {
        throw new RuntimeException('Номер не вмещает такое количество гостей');
    }

    // 2. Проверка пересечений по датам
    $conflictStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM bookings
        WHERE room_id = ?
        AND status IN ('confirmed', 'checked_in')
        AND ((check_in <= ? AND check_out > ?) OR (check_in < ? AND check_out >= ?))"
    );
    $conflictStmt->execute([$roomId, $checkOut, $checkIn, $checkOut, $checkIn]);

    if ((int)$conflictStmt->fetchColumn() > 0) {
        throw new RuntimeException('На выбранные даты номер уже занят');
    }

    // 3. Создание гостя
    $guestData = [
        'firstName' => trim((string)$guest['firstName']),
        'lastName'  => trim((string)$guest['lastName']),
        'phone'     => trim((string)$guest['phone']),
        'email'     => trim((string)$guest['email']),
        'passport'  => trim((string)($guest['passport'] ?? '')),
    ];

    $guestStmt = $pdo->prepare(
        "INSERT INTO guests (first_name, last_name, phone, email, passport)
        VALUES (?, ?, ?, ?, ?)"
    );
    $guestStmt->execute([
        $guestData['firstName'],
        $guestData['lastName'],
        $guestData['phone'],
        $guestData['email'],
        $guestData['passport'],
    ]);
    $guestId = (int)$pdo->lastInsertId();

    // 4. Расчёт стоимости и создание бронирования
    $days = max(1, (new DateTime($checkIn))->diff(new DateTime($checkOut))->days);
    $totalPrice = $days * (float)$room['price'];
    $bookingNumber = date('Ymd') . '-' . random_int(1000, 9999);

    $bookingStmt = $pdo->prepare(
        "INSERT INTO bookings
        (booking_number, guest_id, room_id, check_in, check_out, guest_count, total_price, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')"
    );
    $bookingStmt->execute([
        $bookingNumber,
        $guestId,
        $roomId,
        $checkIn,
        $checkOut,
        $guestCount,
        $totalPrice,
    ]);

    $pdo->commit();
    echo json_encode(['success' => true, 'bookingNumber' => $bookingNumber], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}