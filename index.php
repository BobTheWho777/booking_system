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

    const featureIcons = {
        'area': 'fa-ruler-combined',
        'wifi': 'fa-wifi',
        'ac': 'fa-snowflake',
        'fridge': 'fa-ice-cream',
        'tv': 'fa-tv',
        'balcony': 'fa-home',
        'sea_view': 'fa-water',
        'jacuzzi': 'fa-hot-tub',
        'living_room': 'fa-couch'
    };

    results.innerHTML = data.map(room => {
        const images = room.images || [];
        const hasImages = images.length > 0;
        const features = room.features || [];
        
        let sliderHtml = '';
        if (hasImages) {
            sliderHtml = `
                <div class="room-slider" style="position: relative; margin-bottom: 1rem; border-radius: 8px; overflow: hidden;" data-room-id="${room.id}">
                    <div class="slider-track" style="display: flex; transition: transform 0.3s ease;">
                        ${images.map((img, idx) => `
                            <div class="slide" style="min-width: 100%; position: relative;">
                                <img src="${BASE_URL}uploads/rooms/${img}" alt="Фото номера" style="width: 100%; height: 220px; object-fit: cover; cursor: pointer;" onclick="openLightbox('${room.number}', ${idx}, ${JSON.stringify(images).replace(/"/g, '&quot;')})">
                            </div>
                        `).join('')}
                    </div>
                    ${images.length > 1 ? `
                        <button class="slider-prev" onclick="moveSlide(this, -1)" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 0.5rem; border-radius: 50%; cursor: pointer; font-size: 1.2rem;">&#10094;</button>
                        <button class="slider-next" onclick="moveSlide(this, 1)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 0.5rem; border-radius: 50%; cursor: pointer; font-size: 1.2rem;">&#10095;</button>
                    ` : ''}
                    ${images.length > 1 ? `
                        <div class="slider-dots" style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); display: flex; gap: 0.5rem;">
                            ${images.map((_, idx) => `<span class="dot" data-index="${idx}" style="width: 8px; height: 8px; border-radius: 50%; background: ${idx === 0 ? 'white' : 'rgba(255,255,255,0.5)'}; cursor: pointer;" onclick="goToSlide(this.closest('.room-slider'), ${idx})"></span>`).join('')}
                        </div>
                    ` : ''}
                </div>
            `;
        } else {
            sliderHtml = `<div style="margin-bottom: 1rem; height: 220px; background: linear-gradient(135deg, var(--marble-light) 0%, var(--marble-dark) 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 1.2rem;"><i class="fas fa-image" style="margin-right: 0.5rem;"></i>Нет фото</div>`;
        }
        
        // Формируем HTML характеристик
        let featuresHtml = '';
        if (features.length > 0) {
            featuresHtml = `<div class="room-features">${features.map(f => {
                const iconClass = featureIcons[f.feature_code] || 'fa-check';
                return `<span class="room-feature"><i class="fas ${iconClass}"></i>${f.feature_value}</span>`;
            }).join('')}</div>`;
        }
        
        const statusClass = `room-status-${room.status}`;
        
        return `
            <article class="marble-card" style="padding: 1.2rem;">
                ${sliderHtml}
                <div class="flex-between" style="margin-bottom: 0.8rem;">
                    <h3 style="margin: 0; font-size: 1.3rem; line-height: 1.3; color: var(--aegean-dark);">№ ${room.number}</h3>
                    <span class="room-status-badge ${statusClass}">${statusLabels[room.status] || room.status}</span>
                </div>
                <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 0.5rem; font-weight: 600;">${room.type_name}</p>
                ${featuresHtml}
                <div style="margin-top: 1rem; padding-top: 0.8rem; border-top: 1px solid var(--marble-medium);">
                    <p style="font-size: 1.4rem; font-weight: 700; color: var(--aegean-dark); margin-bottom: 0.3rem;">
                        ${Number(room.price).toLocaleString('ru-RU')} ₽
                        <span class="price-per-day">/ сутки</span>
                    </p>
                </div>
                <a style="display: block; text-align: center; text-decoration: none; font-size: 1.05rem; padding: 0.7rem 0.5rem; width: 100%; background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%); color: white; border-radius: 4px; font-family: system-ui, -apple-system, sans-serif; font-weight: 600; box-shadow: var(--shadow-sm); line-height: 1.3; margin-top: 0.8rem;"
                   href="${BASE_URL}booking.php?roomId=${encodeURIComponent(room.id)}&checkin=${encodeURIComponent(checkin)}&checkout=${encodeURIComponent(checkout)}&guests=${encodeURIComponent(guests)}">
                   Забронировать
                </a>
            </article>
        `;
    }).join('');
}

form.addEventListener('submit', (event) => {
    event.preventDefault();
    searchRooms();
});

// Функции слайдера
function moveSlide(slider, direction) {
    const track = slider.querySelector('.slider-track');
    const slides = slider.querySelectorAll('.slide');
    const dots = slider.querySelectorAll('.dot');
    const currentIndex = parseInt(track.dataset.currentIndex || 0);
    let newIndex = currentIndex + direction;
    
    if (newIndex < 0) newIndex = slides.length - 1;
    if (newIndex >= slides.length) newIndex = 0;
    
    track.style.transform = `translateX(-${newIndex * 100}%)`;
    track.dataset.currentIndex = newIndex;
    
    // Обновляем точки
    dots.forEach((dot, idx) => {
        dot.style.background = idx === newIndex ? 'white' : 'rgba(255,255,255,0.5)';
    });
}

function goToSlide(slider, index) {
    const track = slider.querySelector('.slider-track');
    const dots = slider.querySelectorAll('.dot');
    
    track.style.transform = `translateX(-${index * 100}%)`;
    track.dataset.currentIndex = index;
    
    dots.forEach((dot, idx) => {
        dot.style.background = idx === index ? 'white' : 'rgba(255,255,255,0.5)';
    });
}

// Лайтбокс для просмотра изображений
let lightbox = null;
function openLightbox(roomNumber, startIndex, images) {
    if (lightbox) return;
    
    lightbox = document.createElement('div');
    lightbox.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; display: flex; flex-direction: column; justify-content: center; align-items: center;';
    
    lightbox.innerHTML = `
        <button onclick="closeLightbox()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 2rem; cursor: pointer;">&times;</button>
        <h2 style="color: white; margin-bottom: 1rem; font-family: 'Cormorant Garamond', serif;">Номер ${roomNumber}</h2>
        <div style="position: relative; max-width: 90%; max-height: 80%;">
            <img id="lightbox-img" src="${BASE_URL}uploads/rooms/${images[startIndex]}" style="max-width: 100%; max-height: 80vh; object-fit: contain;" alt="Фото номера">
            <button onclick="moveLightboxSlide(-1)" style="position: absolute; left: -50px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; padding: 1rem; border-radius: 50%; cursor: pointer; font-size: 1.5rem;">&#10094;</button>
            <button onclick="moveLightboxSlide(1)" style="position: absolute; right: -50px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.2); color: white; border: none; padding: 1rem; border-radius: 50%; cursor: pointer; font-size: 1.5rem;">&#10095;</button>
        </div>
        <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
            ${images.map((_, idx) => `<span onclick="setLightboxSlide(${idx})" style="width: 12px; height: 12px; border-radius: 50%; background: ${idx === startIndex ? 'white' : 'rgba(255,255,255,0.3)'}; cursor: pointer;"></span>`).join('')}
        </div>
    `;
    
    lightbox.dataset.currentIndex = startIndex;
    lightbox.dataset.images = JSON.stringify(images);
    
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    if (!lightbox) return;
    lightbox.remove();
    lightbox = null;
    document.body.style.overflow = '';
}

function moveLightboxSlide(direction) {
    if (!lightbox) return;
    
    const images = JSON.parse(lightbox.dataset.images);
    let currentIndex = parseInt(lightbox.dataset.currentIndex || 0);
    let newIndex = currentIndex + direction;
    
    if (newIndex < 0) newIndex = images.length - 1;
    if (newIndex >= images.length) newIndex = 0;
    
    document.getElementById('lightbox-img').src = BASE_URL + 'uploads/rooms/' + images[newIndex];
    lightbox.dataset.currentIndex = newIndex;
    
    // Обновляем точки
    const dots = lightbox.querySelectorAll('[onclick^="setLightboxSlide"]');
    dots.forEach((dot, idx) => {
        dot.style.background = idx === newIndex ? 'white' : 'rgba(255,255,255,0.3)';
    });
}

function setLightboxSlide(index) {
    if (!lightbox) return;
    
    const images = JSON.parse(lightbox.dataset.images);
    document.getElementById('lightbox-img').src = BASE_URL + 'uploads/rooms/' + images[index];
    lightbox.dataset.currentIndex = index;
    
    // Обновляем точки
    const dots = lightbox.querySelectorAll('[onclick^="setLightboxSlide"]');
    dots.forEach((dot, idx) => {
        dot.style.background = idx === index ? 'white' : 'rgba(255,255,255,0.3)';
    });
}

searchRooms();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
