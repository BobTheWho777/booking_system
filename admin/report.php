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

$pageTitle = 'Отчёт по загрузке';

$start = date('Y-m-01');
$end = date('Y-m-t');

$stmt = $pdo->prepare(
    "SELECT
        (SELECT COUNT(*) FROM rooms) AS total_rooms,
        (SELECT COUNT(*) FROM bookings WHERE status IN ('confirmed', 'checked_in') AND check_in <= ? AND check_out >= ?) AS occupied"
);
$stmt->execute([$end, $start]);
$report = $stmt->fetch() ?: ['total_rooms' => 0, 'occupied' => 0];

$totalRooms = (int)$report['total_rooms'];
$occupied = (int)$report['occupied'];
$available = $totalRooms - $occupied;
$occupancy = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 1) : 0.0;

$stmt = $pdo->prepare(
    "SELECT COUNT(*) AS count, COALESCE(SUM(total_price), 0) AS total
     FROM bookings
     WHERE status IN ('confirmed', 'checked_in') AND check_in >= ? AND check_in <= ?"
);
$stmt->execute([$start, $end]);
$monthly = $stmt->fetch() ?: ['count' => 0, 'total' => 0];

require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="marble-card">
        <h1><i class="fas fa-chart-bar" style="margin-right: 0.5rem;"></i> Отчёт по загрузке</h1>
        <div class="divider"></div>
        
        <div class="grid grid-3" style="margin-bottom: 3rem;">
            <div class="marble-card" style="text-align: center; border: 2px solid var(--aegean-blue);">
                <h3 style="color: var(--aegean-dark); margin-bottom: 0.5rem;">Всего комнат</h3>
                <p style="font-size: 3rem; font-weight: 700; color: var(--aegean-dark); margin: 0;"><?= $totalRooms ?></p>
            </div>
            <div class="marble-card" style="text-align: center; border: 2px solid var(--success);">
                <h3 style="color: var(--success); margin-bottom: 0.5rem;">Занято сейчас</h3>
                <p style="font-size: 3rem; font-weight: 700; color: var(--success); margin: 0;"><?= $occupied ?></p>
            </div>
            <div class="marble-card" style="text-align: center; border: 2px solid var(--gold);">
                <h3 style="color: var(--gold-dark); margin-bottom: 0.5rem;">Процент загрузки</h3>
                <p style="font-size: 3rem; font-weight: 700; color: var(--gold-dark); margin: 0;"><?= $occupancy ?>%</p>
            </div>
        </div>

        <div class="divider"></div>

        <h3 style="text-align: center; margin-bottom: 2rem;">Статистика за текущий месяц</h3>
        <div class="grid grid-2">
            <div class="marble-card" style="text-align: center;">
                <h3 style="color: var(--aegean-dark);">Бронирований в этом месяце</h3>
                <p style="font-size: 2.5rem; font-weight: 700; color: var(--aegean-dark);"><?= (int)$monthly['count'] ?></p>
            </div>
            <div class="marble-card" style="text-align: center;">
                <h3 style="color: var(--gold-dark);">Общий доход</h3>
                <p style="font-size: 2.5rem; font-weight: 700; color: var(--gold-dark);"><?= number_format((float)$monthly['total'], 0, ',', ' ') ?> ₽</p>
            </div>
        </div>

        <?php if ($totalRooms > 0): ?>
            <div class="divider"></div>
            <h3 style="text-align: center; margin-bottom: 1rem;">Визуализация загрузки</h3>
            <div style="background: var(--marble-light); border-radius: 8px; height: 40px; overflow: hidden; position: relative;">
                <div style="background: linear-gradient(90deg, var(--success) 0%, var(--gold) 100%); height: 100%; width: <?= $occupancy ?>%; transition: width 1s ease;"></div>
            </div>
            <p style="text-align: center; margin-top: 0.5rem; color: var(--text-muted);">
                Свободно: <?= $available ?> комнат | Занято: <?= $occupied ?> комнат
            </p>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
