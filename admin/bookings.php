<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['user']) || (string)($_SESSION['role'] ?? '') !== 'admin') {
    app_redirect('login.php');
}

require_once __DIR__ . '/../config/db.php';

$pageTitle = 'Бронирования';

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $newStatus = trim($_POST['new_status'] ?? '');
    
    $allowedStatuses = ['confirmed', 'checked_in', 'checked_out', 'cancelled'];
    
    if ($bookingId > 0 && in_array($newStatus, $allowedStatuses)) {
        $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $bookingId]);
        
        header('Location: ' . BASE_URL . 'admin/bookings.php?success=1');
        exit;
    }
}

$stmt = $pdo->query(
    "SELECT b.id, b.booking_number, b.check_in, b.check_out, b.guest_count, b.total_price, b.status,
            g.first_name, g.last_name, g.phone, g.email,
            r.number AS room_number
     FROM bookings b
     JOIN guests g ON g.id = b.guest_id
     JOIN rooms r ON r.id = b.room_id
     ORDER BY b.id DESC
     LIMIT 100"
);
$bookings = $stmt->fetchAll();

$statusLabels = [
    'confirmed' => 'Подтверждено',
    'checked_in' => 'Заселён',
    'checked_out' => 'Выехал',
    'cancelled' => 'Отменено'
];

$statusColors = [
    'confirmed' => 'var(--success-bg); color: var(--success)',
    'checked_in' => 'var(--info-bg); color: var(--info)',
    'checked_out' => 'var(--marble-light); color: var(--text-muted)',
    'cancelled' => 'var(--error-bg); color: var(--error)'
];

require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="marble-card">
        <h1><i class="fas fa-calendar-alt" style="margin-right: 0.5rem;"></i> Бронирования</h1>
        <div class="divider"></div>

        <?php if (isset($_GET['success'])): ?>
            <div class="flash-message flash-success">Статус бронирования обновлён!</div>
        <?php endif; ?>

        <?php if (!$bookings): ?>
            <p class="text-center" style="color: var(--text-muted); font-size: 1.2rem; padding: 3rem 0;">
                Бронирований пока нет
            </p>
        <?php else: ?>
            <table class="greek-table">
                <thead>
                    <tr>
                        <th>№ бронирования</th>
                        <th>Гость</th>
                        <th>Комната</th>
                        <th>Даты</th>
                        <th>Гостей</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($booking['booking_number'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td>
                            <?= htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name'], ENT_QUOTES, 'UTF-8') ?><br>
                            <small style="color: var(--text-muted);"><?= htmlspecialchars($booking['phone'], ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td>№ <?= htmlspecialchars((string)$booking['room_number'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <?= htmlspecialchars($booking['check_in'], ENT_QUOTES, 'UTF-8') ?> — <br>
                            <?= htmlspecialchars($booking['check_out'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td><?= htmlspecialchars((string)$booking['guest_count'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><strong><?= number_format((float)$booking['total_price'], 0, ',', ' ') ?> ₽</strong></td>
                        <td>
                            <span style="padding: 0.3rem 0.8rem; border-radius: 12px; background: <?= $statusColors[$booking['status']] ?? 'var(--info-bg); color: var(--info)' ?>">
                                <?= htmlspecialchars($statusLabels[$booking['status']] ?? $booking['status'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                                <select name="new_status" class="form-select" style="padding: 0.4rem 0.6rem; font-size: 0.85rem; margin-bottom: 0.3rem;">
                                    <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                                        <option value="<?= htmlspecialchars($statusKey) ?>" <?= $booking['status'] === $statusKey ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($statusLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="btn-greek" style="padding: 0.3rem 0.8rem; font-size: 0.8rem; width: 100%;">
                                    Применить
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
