<?php
require_once __DIR__ . '/auth.php';

function admin_page_start(string $title, string $active = ''): void
{
    require_admin();
    $flash = flash_get();
    ?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> — ΟΛΥΜΠΟΣ</title>
    <link rel="stylesheet" href="<?= h(BASE_URL) ?>assets/css/style.css">
</head>
<body>
<nav class="greek-nav">
    <div class="nav-container">
        <a href="<?= h(BASE_URL) ?>index.php" class="nav-brand">ΟΛΥΜΠΟΣ</a>
        <div class="nav-links">
            <a href="<?= h(BASE_URL) ?>index.php" class="nav-link">На сайт</a>
            <a href="<?= h(BASE_URL) ?>admin/rooms.php" class="nav-link <?= $active === 'rooms' ? 'active' : '' ?>">Комнаты</a>
            <a href="<?= h(BASE_URL) ?>admin/room_types.php" class="nav-link <?= $active === 'room_types' ? 'active' : '' ?>">Типы номеров</a>
            <a href="<?= h(BASE_URL) ?>admin/guests.php" class="nav-link <?= $active === 'guests' ? 'active' : '' ?>">Гости</a>
            <a href="<?= h(BASE_URL) ?>admin/bookings.php" class="nav-link <?= $active === 'bookings' ? 'active' : '' ?>">Бронирования</a>
            <a href="<?= h(BASE_URL) ?>admin/report.php" class="nav-link <?= $active === 'report' ? 'active' : '' ?>">Отчёты</a>
            <a href="<?= h(BASE_URL) ?>admin/logout.php" class="nav-link" style="color: #F4E4BC;">Выйти</a>
        </div>
    </div>
</nav>
<div class="container">
    <?php if ($flash): ?>
        <div class="flash-message flash-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>
    <div class="marble-card">
        <h2><?= h($title) ?></h2>
        <div class="divider"></div>
<?php
}

function admin_page_end(): void
{
    ?>
    </div>
</div>
<footer class="greek-footer">
    <p>© <?= date('Y') ?> Мини-гостиница «Олимп» — Панель управления</p>
</footer>
</body>
</html>
<?php
}
