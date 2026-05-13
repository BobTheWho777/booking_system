<?php
declare(strict_types=1);
?>
</main>
<footer class="greek-footer">
    <div class="footer-content" style="max-width: 1200px; margin: 0 auto;">
        <div class="grid grid-3" style="text-align: left; margin-bottom: 1.5rem;">
            <div>
                <h4 style="color: var(--gold-light); font-size: 1.2rem; margin-bottom: 1rem;">ΜΙΝΙ-ΞΕΝΟΔΟΧΕΊΟ «ΌΛΥΜΠΟΣ»</h4>
                <p style="font-size: 1rem; opacity: 0.9; line-height: 1.6;">
                    Мини-гостиница «Олимп» — это уютное заведение, вдохновлённое величием древнегреческой цивилизации.
                </p>
            </div>
            <div>
                <h4 style="color: var(--gold-light); font-size: 1.2rem; margin-bottom: 1rem;">Контакты</h4>
                <p style="font-size: 1rem; opacity: 0.9; line-height: 1.8;">
                    📍 Адрес: ул. Олимпийская, д. 1<br>
                    📞 Телефон: +7 (999) 123-45-67<br>
                    ✉️ Email: info@olymp-hotel.ru<br>
                    🕒 Режим работы: Круглосуточно
                </p>
            </div>
            <div>
                <h4 style="color: var(--gold-light); font-size: 1.2rem; margin-bottom: 1rem;">Навигация</h4>
                <p style="font-size: 1rem; opacity: 0.9; line-height: 1.8;">
                    <a href="<?= htmlspecialchars(app_url('index.php'), ENT_QUOTES, 'UTF-8') ?>" style="color: var(--marble-white); text-decoration: none;">Главная</a><br>
                    <a href="<?= htmlspecialchars(app_url('about.php'), ENT_QUOTES, 'UTF-8') ?>" style="color: var(--marble-white); text-decoration: none;">О нас</a><br>
                    <a href="<?= htmlspecialchars(app_url('booking.php'), ENT_QUOTES, 'UTF-8') ?>" style="color: var(--marble-white); text-decoration: none;">Бронирование</a><br>
                    <a href="<?= htmlspecialchars(app_url('login.php'), ENT_QUOTES, 'UTF-8') ?>" style="color: var(--marble-white); text-decoration: none;">Войти</a>
                </p>
            </div>
        </div>
        <div class="divider" style="border-color: var(--gold); opacity: 0.5;"></div>
        <p style="font-family: 'Philosopher', sans-serif; font-size: 1rem; margin: 0; opacity: 0.9;">
            © <?= date('Y') ?> Мини-гостиница «Олимп» — Добро пожаловать в дом богов
        </p>
        <p style="font-family: 'Philosopher', sans-serif; font-size: 0.9rem; margin: 0.5rem 0 0 0; opacity: 0.7;">
            Разработано с любовью к древнегреческой культуре
        </p>
    </div>
</footer>
</body>
</html>
