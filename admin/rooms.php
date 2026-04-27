<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

$statuses = ['available' => 'Свободен', 'booked' => 'Забронирован', 'maintenance' => 'На ремонте', 'occupied' => 'Занят'];

function rooms_redirect(string $type, string $message): void
{
    flash_set($type, $message);
    header('Location: ' . BASE_URL . 'admin/rooms.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE room_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            rooms_redirect('error', 'Нельзя удалить комнату, потому что к ней есть бронирования.');
        }

        $stmt = $pdo->prepare('DELETE FROM rooms WHERE id = ?');
        $stmt->execute([$id]);
        rooms_redirect('success', 'Комната удалена.');
    }

    if (isset($_POST['save_room'])) {
        $id = trim($_POST['id'] ?? '');
        $number = trim($_POST['number'] ?? '');
        $type_id = (int)($_POST['type_id'] ?? 0);
        $price = trim($_POST['price'] ?? '');
        $status = trim($_POST['status'] ?? 'available');
        $description = trim($_POST['description'] ?? '');

        if ($number === '' || $type_id <= 0 || $price === '') {
            rooms_redirect('error', 'Заполни номер, тип и цену.');
        }

        if (!array_key_exists($status, $statuses)) {
            $status = 'available';
        }

        if ($id !== '') {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM rooms WHERE number = ? AND id <> ?');
            $stmt->execute([$number, $id]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM rooms WHERE number = ?');
            $stmt->execute([$number]);
        }

        if ((int)$stmt->fetchColumn() > 0) {
            rooms_redirect('error', 'Комната с таким номером уже существует.');
        }

        if ($id !== '') {
            $stmt = $pdo->prepare('UPDATE rooms SET number = ?, type_id = ?, price = ?, status = ?, description = ? WHERE id = ?');
            $stmt->execute([$number, $type_id, $price, $status, $description, $id]);
            rooms_redirect('success', 'Комната обновлена.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO rooms (number, type_id, price, status, description) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$number, $type_id, $price, $status, $description]);
            rooms_redirect('success', 'Комната добавлена.');
        }
    }
}

$editRoom = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editRoom = $stmt->fetch();
}

$stmt = $pdo->query('SELECT id, name, capacity FROM room_types ORDER BY name');
$roomTypes = $stmt->fetchAll();

$stmt = $pdo->query('
    SELECT r.id, r.number, r.price, r.status, r.description, rt.name AS type_name
    FROM rooms r
    JOIN room_types rt ON rt.id = r.type_id
    ORDER BY r.number
');
$rooms = $stmt->fetchAll();

admin_page_start('Управление комнатами', 'rooms');
?>

<div style="margin-bottom: 2rem;">
    <h3><?= $editRoom ? 'Редактировать комнату' : 'Добавить новую комнату' ?></h3>
    <form method="post">
        <input type="hidden" name="id" value="<?= h($editRoom['id'] ?? '') ?>">
        <div class="grid grid-4">
            <div class="form-group">
                <label class="form-label">Номер комнаты</label>
                <input name="number" value="<?= h($editRoom['number'] ?? '') ?>" class="form-input" required placeholder="Например: 101">
            </div>
            <div class="form-group">
                <label class="form-label">Тип</label>
                <select name="type_id" class="form-select" required>
                    <option value="">Выбери тип</option>
                    <?php foreach ($roomTypes as $type): ?>
                        <option value="<?= h($type['id']) ?>" <?= isset($editRoom['type_id']) && (int)$editRoom['type_id'] === (int)$type['id'] ? 'selected' : '' ?>>
                            <?= h($type['name']) ?> (<?= h($type['capacity']) ?> чел.)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Цена за ночь (₽)</label>
                <input name="price" type="number" step="0.01" min="0" value="<?= h($editRoom['price'] ?? '') ?>" class="form-input" required placeholder="5000">
            </div>
            <div class="form-group">
                <label class="form-label">Статус</label>
                <select name="status" class="form-select">
                    <?php foreach ($statuses as $key => $label): ?>
                        <option value="<?= h($key) ?>" <?= ($editRoom['status'] ?? 'available') === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Описание</label>
            <textarea name="description" class="form-textarea" placeholder="Описание комнаты..."><?= h($editRoom['description'] ?? '') ?></textarea>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button class="btn-greek" type="submit" name="save_room"><?= $editRoom ? 'Сохранить изменения' : 'Добавить комнату' ?></button>
            <?php if ($editRoom): ?>
                <a class="btn-greek btn-outline" href="<?= h(BASE_URL) ?>admin/rooms.php">Отмена</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="divider"></div>

<h3>Список комнат</h3>
<table class="greek-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Номер</th>
            <th>Тип</th>
            <th>Цена</th>
            <th>Статус</th>
            <th>Описание</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rooms as $room): ?>
            <tr>
                <td><?= h($room['id']) ?></td>
                <td><strong><?= h($room['number']) ?></strong></td>
                <td><?= h($room['type_name']) ?></td>
                <td><?= h(number_format((float)$room['price'], 2, ',', ' ')) ?> ₽</td>
                <td><span style="padding: 0.3rem 0.8rem; border-radius: 12px; background: <?= $room['status'] === 'available' ? 'var(--success-bg); color: var(--success)' : 'var(--error-bg); color: var(--error)' ?>"><?= h($statuses[$room['status']] ?? $room['status']) ?></span></td>
                <td><?= h($room['description'] ?: '—') ?></td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <a class="btn-greek btn-outline" href="?edit=<?= h($room['id']) ?>" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Редактировать</a>
                        <form method="post" onsubmit="return confirm('Удалить комнату?');" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= h($room['id']) ?>">
                            <button class="btn-greek btn-danger" style="padding: 0.5rem 1rem; font-size: 0.9rem;" type="submit">Удалить</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rooms): ?>
            <tr><td colspan="7" class="text-center" style="padding: 2rem; color: var(--text-muted);">Комнат пока нет. Добавьте первую комнату!</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php admin_page_end(); ?>
