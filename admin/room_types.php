<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_layout.php';
require_admin();

function types_redirect(string $type, string $message): void
{
    flash_set($type, $message);
    header('Location: room_types.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM rooms WHERE type_id = ?');
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            types_redirect('error', 'Нельзя удалить тип номера, потому что он используется в комнатах.');
        }

        $stmt = $pdo->prepare('DELETE FROM room_types WHERE id = ?');
        $stmt->execute([$id]);
        types_redirect('success', 'Тип номера удалён.');
    }

    if (isset($_POST['save_type'])) {
        $id = trim($_POST['id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $capacity <= 0) {
            types_redirect('error', 'Заполни название и вместимость.');
        }

        if ($id !== '') {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM room_types WHERE name = ? AND id <> ?');
            $stmt->execute([$name, $id]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM room_types WHERE name = ?');
            $stmt->execute([$name]);
        }

        if ((int)$stmt->fetchColumn() > 0) {
            types_redirect('error', 'Тип номера с таким названием уже существует.');
        }

        if ($id !== '') {
            $stmt = $pdo->prepare('UPDATE room_types SET name = ?, capacity = ?, description = ? WHERE id = ?');
            $stmt->execute([$name, $capacity, $description, $id]);
            types_redirect('success', 'Тип номера обновлён.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO room_types (name, capacity, description) VALUES (?, ?, ?)');
            $stmt->execute([$name, $capacity, $description]);
            types_redirect('success', 'Тип номера добавлен.');
        }
    }
}

$editType = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM room_types WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editType = $stmt->fetch();
}

$stmt = $pdo->query('
    SELECT rt.*, COUNT(r.id) AS rooms_count
    FROM room_types rt
    LEFT JOIN rooms r ON r.type_id = rt.id
    GROUP BY rt.id
    ORDER BY rt.name
');
$types = $stmt->fetchAll();

admin_page_start('Типы номеров', 'room_types');
?>

<div style="margin-bottom: 2rem;">
    <h3><?= $editType ? 'Редактировать тип' : 'Добавить новый тип номера' ?></h3>
    <form method="post">
        <input type="hidden" name="id" value="<?= h($editType['id'] ?? '') ?>">
        <div class="grid grid-2">
            <div class="form-group">
                <label class="form-label">Название типа</label>
                <input name="name" value="<?= h($editType['name'] ?? '') ?>" class="form-input" required placeholder="Например: Стандарт">
            </div>
            <div class="form-group">
                <label class="form-label">Вместимость (чел.)</label>
                <input name="capacity" type="number" min="1" value="<?= h($editType['capacity'] ?? '') ?>" class="form-input" required placeholder="2">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Описание</label>
            <textarea name="description" class="form-textarea" placeholder="Описание типа номера..."><?= h($editType['description'] ?? '') ?></textarea>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button class="btn-greek" type="submit" name="save_type"><?= $editType ? 'Сохранить изменения' : 'Добавить тип' ?></button>
            <?php if ($editType): ?>
                <a class="btn-greek btn-outline" href="room_types.php">Отмена</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="divider"></div>

<h3>Список типов номеров</h3>
<table class="greek-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Вместимость</th>
            <th>Описание</th>
            <th>Комнат</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($types as $type): ?>
            <tr>
                <td><?= h($type['id']) ?></td>
                <td><strong><?= h($type['name']) ?></strong></td>
                <td><?= h($type['capacity']) ?> чел.</td>
                <td><?= h($type['description'] ?: '—') ?></td>
                <td><span style="padding: 0.3rem 0.8rem; border-radius: 12px; background: var(--info-bg); color: var(--info);"><?= h($type['rooms_count']) ?></span></td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <a class="btn-greek btn-outline" href="?edit=<?= h($type['id']) ?>" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Редактировать</a>
                        <form method="post" onsubmit="return confirm('Удалить тип номера?');" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= h($type['id']) ?>">
                            <button class="btn-greek btn-danger" style="padding: 0.5rem 1rem; font-size: 0.9rem;" type="submit" <?= ((int)$type['rooms_count'] > 0) ? 'disabled' : '' ?>>Удалить</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$types): ?>
            <tr><td colspan="6" class="text-center" style="padding: 2rem; color: var(--text-muted);">Типов номеров пока нет. Добавьте первый тип!</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php admin_page_end(); ?>
