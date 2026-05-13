<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$pageTitle = $pageTitle ?? 'Мини-гостиница «Олимп»';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <nav class="greek-nav">
        <div class="nav-container">
            <a href="<?= htmlspecialchars(app_url('index.php'), ENT_QUOTES, 'UTF-8') ?>" class="nav-brand">ΟΛΥΜΠΟΣ</a>
            <div class="nav-links">
                <a href="<?= htmlspecialchars(app_url('index.php'), ENT_QUOTES, 'UTF-8') ?>" class="nav-link">Главная</a>
                <a href="<?= htmlspecialchars(app_url('about.php'), ENT_QUOTES, 'UTF-8') ?>" class="nav-link">О нас</a>
                <a href="<?= htmlspecialchars(app_url('booking.php'), ENT_QUOTES, 'UTF-8') ?>" class="nav-link">Бронирование</a>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="<?= htmlspecialchars(app_url('admin/index.php'), ENT_QUOTES, 'UTF-8') ?>" class="nav-link">Админ-панель</a>
                    <a href="<?= htmlspecialchars(app_url('logout.php'), ENT_QUOTES, 'UTF-8') ?>" class="nav-link" style="color: #F4E4BC;">Выйти</a>
                <?php else: ?>
                    <a href="<?= htmlspecialchars(app_url('login.php'), ENT_QUOTES, 'UTF-8') ?>" class="nav-link">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main>