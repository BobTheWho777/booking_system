<?php
declare(strict_types=1);

$pageTitle = 'Мини-гостиница «Олимп»';
require __DIR__ . '/includes/header.php';
?>
<section class="hero-section">
    <h1>ΜΙΝΙ-ΞΕΝΟΔΟΧΕΊΟ «ΌΛΥΜΠΟΣ»</h1>
    <p style="font-size: 1.5rem; font-style: italic; font-family: 'Cormorant Garamond', serif;">Забронируйте номер за 30 секунд — добро пожаловать в дом богов</p>
</section>

<div class="container">
    <div class="marble-card" style="margin-bottom: 3rem; max-width: 900px; margin-left: auto; margin-right: auto;">
        <h3 class="text-center" style="margin-bottom: 2rem;">Поиск номеров</h3>
        <form id="searchForm">
            <div class="grid grid-3" style="margin-bottom: 1.5rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="checkin">Дата заезда</label>
                    <input type="date" id="checkin" name="checkin" class="form-input" required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="checkout">Дата выезда</label>
                    <input type="date" id="checkout" name="checkout" class="form-input" required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="guests">Количество гостей</label>
                    <select id="guests" name="guests" class="form-select">
                        <option value="1">1 гость</option>
                        <option value="2" selected>2 гостя</option>
                        <option value="3">3 гостя</option>
                        <option value="4">4 гостя</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-greek" style="width: 100%;">
                Найти номера
            </button>
        </form>
    </div>

    <div id="results" class="grid grid-2"></div>
</div>

<script>
const BASE_URL = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
const results = document.getElementById('results');
const form = document.getElementById('searchForm');

const today = new Date();
const yyyy = today.getFullYear();
const mm = String(today.getMonth() + 1).padStart(2, '0');
const dd = String(today.getDate()).padStart(2, '0');
document.getElementById('checkin').value = `${yyyy}-${mm}-${dd}`;
const tomorrow = new Date(today);
tomorrow.setDate(today.getDate() + 1);
document.getElementById('checkout').value = `${tomorrow.getFullYear()}-${String(tomorrow.getMonth() + 1).padStart(2, '0')}-${String(tomorrow.getDate()).padStart(2, '0')}`;

async function searchRooms() {
    const checkin = document.getElementById('checkin').value;
    const checkout = document.getElementById('checkout').value;
    const guests = document.getElementById('guests').value;

    results.innerHTML = '<p class="text-center" style="color: var(--text-muted); font-size: 1.2rem; grid-column: 1 / -1;">Загружаю номера…</p>';

    const response = await fetch(`${BASE_URL}api/rooms.php?checkin=${encodeURIComponent(checkin)}&checkout=${encodeURIComponent(checkout)}&guests=${encodeURIComponent(guests)}`);
    const data = await response.json();

    if (!response.ok || data.error) {
        results.innerHTML = `<p class="text-center" style="color: var(--error); font-size: 1.2rem; grid-column: 1 / -1;">${data.error || 'Не удалось загрузить номера'}</p>`;
        return;
    }

    if (!Array.isArray(data) || data.length === 0) {
        results.innerHTML = '<p class="text-center" style="color: var(--text-muted); font-size: 1.2rem; grid-column: 1 / -1;">Свободных номеров нет</p>';
        return;
    }

    // Перевод статусов
    const statusLabels = {
        'available': 'Свободен',
        'booked': 'Забронирован',
        'occupied': 'Занят',
        'maintenance': 'На ремонте'
    };

    results.innerHTML = data.map(room => `
        <article class="marble-card" style="padding: 1rem;">
            <div class="flex-between" style="margin-bottom: 0.6rem;">
                <h3 style="margin: 0; font-size: 1.15rem; line-height: 1.3;">№ ${room.number}</h3>
                <span style="padding: 0.2rem 0.5rem; border-radius: 8px; background: var(--success-bg); color: var(--success); font-size: 0.75rem; white-space: nowrap;">${statusLabels[room.status] || room.status}</span>
            </div>
            <p style="color: var(--text-secondary); font-size: 1.05rem; margin-bottom: 0.35rem;">${room.type_name}</p>
            <p style="font-size: 1.25rem; font-weight: 700; color: var(--aegean-dark); margin-bottom: 0.8rem;">${Number(room.price).toLocaleString('ru-RU')} ₽</p>
            <a style="display: block; text-align: center; text-decoration: none; font-size: 1rem; padding: 0.6rem 0.3rem; width: 100%; background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%); color: white; border-radius: 4px; font-family: system-ui, -apple-system, sans-serif; font-weight: 600; box-shadow: var(--shadow-sm); line-height: 1.3;"
               href="${BASE_URL}booking.php?roomId=${encodeURIComponent(room.id)}&checkin=${encodeURIComponent(checkin)}&checkout=${encodeURIComponent(checkout)}&guests=${encodeURIComponent(guests)}">
               Забронировать
            </a>
        </article>
    `).join('');
}

form.addEventListener('submit', (event) => {
    event.preventDefault();
    searchRooms();
});

searchRooms();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
