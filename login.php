<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $pass = (string)($_POST['password'] ?? '');

    if ($username === '' || $pass === '') {
        $error = 'Введите логин и пароль';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            app_redirect('admin/index.php');
        }

        $error = 'Неверный логин или пароль';
    }
}

$pageTitle = 'Вход в систему';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="marble-card" style="max-width: 450px; margin: 3rem auto;">
        <div class="text-center">
            <h1 style="color: var(--aegean-dark);">ΒΑΣΙΛΕΊΑ</h1>
            <p style="font-size: 1.2rem; color: var(--text-secondary);">Вход в панель управления</p>
        </div>
        <div class="divider"></div>

        <?php if ($error !== ''): ?>
            <div class="flash-message flash-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label class="form-label" for="username">Имя пользователя</label>
                <input id="username" type="text" name="username" required class="form-input" placeholder="Введите логин">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Пароль</label>
                <input id="password" type="password" name="password" required class="form-input" placeholder="Введите пароль">
            </div>

            <button type="submit" class="btn-greek" style="width: 100%;">
                Войти
            </button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
