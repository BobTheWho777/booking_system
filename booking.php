<?php
declare(strict_types=1);

$pageTitle = 'Оформление бронирования';
require __DIR__ . '/includes/header.php';

$roomId = (int)($_GET['roomId'] ?? 0);
$checkin = (string)($_GET['checkin'] ?? '');
$checkout = (string)($_GET['checkout'] ?? '');
$guests = (int)($_GET['guests'] ?? 1);
?>
<div class="container">
    <div class="marble-card" style="max-width: 700px; margin: 3rem auto;">
        <h1 class="text-center">ΚΡΆΤΗΣΙΣ — Оформление бронирования</h1>
        <div class="divider"></div>

        <form id="bookingForm">
            <input type="hidden" id="roomId" value="<?= htmlspecialchars((string)$roomId, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="checkIn" value="<?= htmlspecialchars($checkin, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="checkOut" value="<?= htmlspecialchars($checkout, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="guestCount" value="<?= htmlspecialchars((string)$guests, ENT_QUOTES, 'UTF-8') ?>">

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label" for="firstName">Имя</label>
                    <input id="firstName" class="form-input" required placeholder="Введите имя">
                </div>
                <div class="form-group">
                    <label class="form-label" for="lastName">Фамилия</label>
                    <input id="lastName" class="form-input" required placeholder="Введите фамилию">
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Телефон</label>
                    <input id="phone" class="form-input" required placeholder="+7 (___) ___-__-__">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" type="email" class="form-input" required placeholder="example@mail.ru">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="passport">Паспортные данные (необязательно)</label>
                <input id="passport" class="form-input" placeholder="Серия и номер паспорта">
            </div>

            <button type="submit" class="btn-greek" style="width: 100%;">
                Подтвердить бронирование
            </button>

            <p id="message" class="text-center" style="margin-top: 1.5rem; font-size: 1.1rem;"></p>
        </form>
    </div>
</div>

<script>
const BASE_URL = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
const form = document.getElementById('bookingForm');
const message = document.getElementById('message');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const payload = {
        roomId: Number(document.getElementById('roomId').value),
        checkIn: document.getElementById('checkIn').value,
        checkOut: document.getElementById('checkOut').value,
        guestCount: Number(document.getElementById('guestCount').value),
        guest: {
            firstName: document.getElementById('firstName').value.trim(),
            lastName: document.getElementById('lastName').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            email: document.getElementById('email').value.trim(),
            passport: document.getElementById('passport').value.trim(),
        }
    };

    message.className = 'text-center';
    message.style.color = 'var(--text-muted)';
    message.textContent = 'Отправляю бронирование…';

    const response = await fetch(`${BASE_URL}api/bookings.php`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok || !data.success) {
        message.style.color = 'var(--error)';
        message.textContent = data.error || 'Не удалось оформить бронирование';
        return;
    }

    message.style.color = 'var(--success)';
    message.textContent = `<i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i> Бронирование создано: ${data.bookingNumber}`;
    form.reset();
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
