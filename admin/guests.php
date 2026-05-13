<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

function guests_redirect(string $type, string $message): void
{
    flash_set($type, $message);
    header('Location: guests.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE guest_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            guests_redirect('error', 'Нельзя удалить гостя, потому что у него есть бронирования.');
        }

        $stmt = $pdo->prepare('DELETE FROM guests WHERE id = ?');
        $stmt->execute([$id]);
        guests_redirect('success', 'Гость удалён.');
    }

    if (isset($_POST['save_guest'])) {
        $id = trim($_POST['id'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $passport = trim($_POST['passport'] ?? '');

        if ($first_name === '' || $last_name === '') {
            guests_redirect('error', 'Имя и фамилия обязательны.');
        }

        if ($id !== '') {
            $stmt = $pdo->prepare('UPDATE guests SET first_name = ?, last_name = ?, phone = ?, email = ?, passport = ? WHERE id = ?');
            $stmt->execute([$first_name, $last_name, $phone ?: null, $email ?: null, $passport ?: null, $id]);
            guests_redirect('success', 'Гость обновлён.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO guests (first_name, last_name, phone, email, passport) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$first_name, $last_name, $phone ?: null, $email ?: null, $passport ?: null]);
            guests_redirect('success', 'Гость добавлен.');
        }
    }
}

$editGuest = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM guests WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editGuest = $stmt->fetch();
}

$stmt = $pdo->query('
    SELECT g.*, COUNT(b.id) AS bookings_count
    FROM guests g
    LEFT JOIN bookings b ON b.guest_id = g.id
    GROUP BY g.id
    ORDER BY g.last_name, g.first_name
');
$guests = $stmt->fetchAll();

admin_page_start('Управление гостями', 'guests');
?>

<div style="margin-bottom: 2rem;">
    <h3><?= $editGuest ? 'Редактировать гостя' : 'Добавить нового гостя' ?></h3>
    <form method="post">
        <input type="hidden" name="id" value="<?= h($editGuest['id'] ?? '') ?>">
        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-label">Имя</label>
                <input name="first_name" value="<?= h($editGuest['first_name'] ?? '') ?>" class="form-input" required placeholder="Иван">
            </div>
            <div class="form-group">
                <label class="form-label">Фамилия</label>
                <input name="last_name" value="<?= h($editGuest['last_name'] ?? '') ?>" class="form-input" required placeholder="Иванов">
            </div>
            <div class="form-group">
                <label class="form-label">Телефон</label>
                <input name="phone" value="<?= h($editGuest['phone'] ?? '') ?>" class="form-input" placeholder="+7 (999) 123-45-67">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input name="email" type="email" value="<?= h($editGuest['email'] ?? '') ?>" class="form-input" placeholder="example@mail.ru">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Паспортные данные</label>
            <input name="passport" value="<?= h($editGuest['passport'] ?? '') ?>" class="form-input" placeholder="Серия и номер паспорта">
        </div>
        <div style="display: flex; gap: 1rem;">
            <button class="btn-greek" type="submit" name="save_guest"><?= $editGuest ? 'Сохранить изменения' : 'Добавить гостя' ?></button>
            <?php if ($editGuest): ?>
                <a class="btn-greek btn-outline" href="guests.php">Отмена</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="divider"></div>

<h3>Список гостей</h3>
<table class="greek-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Фамилия Имя</th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Паспорт</th>
            <th>Бронирований</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($guests as $guest): ?>
            <tr>
                <td><?= h($guest['id']) ?></td>
                <td><strong><?= h($guest['last_name'] . ' ' . $guest['first_name']) ?></strong></td>
                <td><?= h($guest['phone'] ?: '—') ?></td>
                <td><?= h($guest['email'] ?: '—') ?></td>
                <td><?= h($guest['passport'] ?: '—') ?></td>
                <td><span style="padding: 0.3rem 0.8rem; border-radius: 12px; background: var(--info-bg); color: var(--info);"><?= h($guest['bookings_count']) ?></span></td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <a class="btn-greek btn-outline" href="?edit=<?= h($guest['id']) ?>" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Редактировать</a>
                        <form method="post" onsubmit="return confirm('Удалить гостя?');" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= h($guest['id']) ?>">
                            <button class="btn-greek btn-danger" style="padding: 0.5rem 1rem; font-size: 0.9rem;" type="submit" <?= ((int)$guest['bookings_count'] > 0) ? 'disabled' : '' ?>>Удалить</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$guests): ?>
            <tr><td colspan="7" class="text-center" style="padding: 2rem; color: var(--text-muted);">Гостей пока нет. Добавьте первого гостя!</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php admin_page_end(); ?>
