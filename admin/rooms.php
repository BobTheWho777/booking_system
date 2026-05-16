<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../includes/room_images.php';
require_admin();

$statuses = [
    'available'   => 'Свободен',
    'booked'      => 'Забронирован',
    'maintenance' => 'На ремонте',
    'occupied'    => 'Занят'
];

function rooms_redirect(string $type, string $message): void
{
    flash_set($type, $message);
    header('Location: rooms.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        
        $stmt = $pdo->prepare('SELECT image_path FROM room_images WHERE room_id = ?');
        $stmt->execute([$id]);
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

        delete_room_images($images);
        
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

        // Один запрос вместо двух веток if/else
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM rooms WHERE number = ? AND id != ?');
        $stmt->execute([$number, (int)($id ?: 0)]);

        if ((int)$stmt->fetchColumn() > 0) {
            rooms_redirect('error', 'Комната с таким номером уже существует.');
        }

        try {
            $pdo->beginTransaction();

            if ($id !== '') {
                $stmt = $pdo->prepare('UPDATE rooms SET number = ?, type_id = ?, price = ?, status = ?, description = ? WHERE id = ?');
                $stmt->execute([$number, $type_id, $price, $status, $description, $id]);

                upload_room_images($pdo, (int)$id, $_FILES['images'] ?? []);

                $pdo->commit();
                rooms_redirect('success', 'Комната обновлена.');
            }

            $stmt = $pdo->prepare('INSERT INTO rooms (number, type_id, price, status, description) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$number, $type_id, $price, $status, $description]);

            $newRoomId = (int)$pdo->lastInsertId();

            upload_room_images($pdo, $newRoomId, $_FILES['images'] ?? []);

            $pdo->commit();
            rooms_redirect('success', 'Комната добавлена.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            rooms_redirect('error', $e->getMessage());
        }
    }
}

$editRoom = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editRoom = $stmt->fetch();

    if ($editRoom) {
        $stmt = $pdo->prepare(
            'SELECT image_path FROM room_images WHERE room_id = ? ORDER BY sort_order, id'
        );

        $stmt->execute([$editRoom['id']]);
        $editRoom['images'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
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

$stmt = $pdo->query(
    'SELECT room_id, image_path FROM room_images ORDER BY sort_order, id'
);

$roomImages = [];

foreach ($stmt->fetchAll() as $image) {
    $roomImages[$image['room_id']][] = $image['image_path'];
}

foreach ($rooms as &$room) {
    $room['images'] = $roomImages[$room['id']] ?? [];
}
unset($room);

admin_page_start('Управление комнатами', 'rooms');
?>
<div style="margin-bottom: 2rem;">
    <h3><?= $editRoom ? 'Редактировать комнату' : 'Добавить новую комнату' ?></h3>
    <form method="post" enctype="multipart/form-data">
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
        <div class="form-group">
            <label class="form-label">Изображения комнаты</label>
            <input type="file" name="images[]" class="form-input" multiple accept="image/*">
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.5rem;">Можно выбрать несколько файлов. Поддерживаются форматы: JPG, PNG, GIF.</p>
            <?php if ($editRoom && !empty($editRoom['images'])): ?>
                <div style="margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem;">
                    <?php foreach ($editRoom['images'] as $img): ?>
                        <div style="position: relative;">
                            <img src="<?= BASE_URL ?>uploads/rooms/<?= h($img) ?>" alt="Фото комнаты" style="width: 100%; height: 80px; object-fit: cover; border-radius: 4px;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button class="btn-greek" type="submit" name="save_room"><?= $editRoom ? 'Сохранить изменения' : 'Добавить комнату' ?></button>
            <?php if ($editRoom): ?>
                <a class="btn-greek btn-outline" href="rooms.php">Отмена</a>
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
            <th>Фото</th>
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
                <td>
                    <?php
                    $badgeStyle = match ($room['status']) {
                        'available'   => 'var(--success-bg); color: var(--success)',
                        'maintenance' => 'var(--warning-bg, #fff3cd); color: #856404',
                        default       => 'var(--error-bg); color: var(--error)'
                    };
                    ?>
                    <span style="padding: 0.3rem 0.8rem; border-radius: 12px; background: <?= $badgeStyle ?>">
                        <?= h($statuses[$room['status']] ?? $room['status']) ?>
                    </span>
                </td>
                <td>
                    <?php if (!empty($room['images'])): ?>
                        <div style="display: flex; gap: 0.3rem;">
                            <?php foreach (array_slice($room['images'], 0, 3) as $img): ?>
                                <img src="<?= BASE_URL ?>uploads/rooms/<?= h($img) ?>" alt="Фото" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <?php endforeach; ?>
                            <?php if (count($room['images']) > 3): ?>
                                <span style="display: flex; align-items: center; font-size: 0.85rem; color: var(--text-muted);">+<?= count($room['images']) - 3 ?></span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span style="color: var(--text-muted); font-size: 0.9rem;">Нет фото</span>
                    <?php endif; ?>
                </td>
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
            <tr><td colspan="8" class="text-center" style="padding: 2rem; color: var(--text-muted);">Комнат пока нет. Добавьте первую комнату!</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php admin_page_end(); ?>