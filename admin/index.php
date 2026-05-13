<?php
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();
admin_page_start('Панель управления', 'dashboard');
?>
<div class="grid grid-3">
    <a href="<?= h(BASE_URL) ?>admin/rooms.php" style="text-decoration: none; color: inherit;">
        <div class="marble-card" style="text-align: center; transition: all 0.3s;">
            <h3 style="color: var(--aegean-dark);"><i class="fas fa-door-open" style="margin-right: 0.5rem;"></i> Комнаты</h3>
            <p style="color: var(--text-secondary);">Управление номерным фондом</p>
        </div>
    </a>
    <a href="<?= h(BASE_URL) ?>admin/room_types.php" style="text-decoration: none; color: inherit;">
        <div class="marble-card" style="text-align: center; transition: all 0.3s;">
            <h3 style="color: var(--aegean-dark);"><i class="fas fa-list" style="margin-right: 0.5rem;"></i> Типы номеров</h3>
            <p style="color: var(--text-secondary);">Категории и классификация</p>
        </div>
    </a>
    <a href="<?= h(BASE_URL) ?>admin/guests.php" style="text-decoration: none; color: inherit;">
        <div class="marble-card" style="text-align: center; transition: all 0.3s;">
            <h3 style="color: var(--aegean-dark);"><i class="fas fa-users" style="margin-right: 0.5rem;"></i> Гости</h3>
            <p style="color: var(--text-secondary);">База данных гостей</p>
        </div>
    </a>
    <a href="<?= h(BASE_URL) ?>admin/bookings.php" style="text-decoration: none; color: inherit;">
        <div class="marble-card" style="text-align: center; transition: all 0.3s;">
            <h3 style="color: var(--aegean-dark);"><i class="fas fa-calendar-alt" style="margin-right: 0.5rem;"></i> Бронирования</h3>
            <p style="color: var(--text-secondary);">Управление бронями</p>
        </div>
    </a>
    <a href="<?= h(BASE_URL) ?>admin/report.php" style="text-decoration: none; color: inherit;">
        <div class="marble-card" style="text-align: center; transition: all 0.3s;">
            <h3 style="color: var(--aegean-dark);"><i class="fas fa-chart-bar" style="margin-right: 0.5rem;"></i> Отчёты</h3>
            <p style="color: var(--text-secondary);">Аналитика и статистика</p>
        </div>
    </a>
</div>
<?php admin_page_end(); ?>
