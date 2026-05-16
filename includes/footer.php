<?php
declare(strict_types=1);
?>
</main>
<footer class="greek-footer">
    <div class="footer-content" style="max-width: 1200px; margin: 0 auto;">
        <div class="grid grid-3" style="text-align: left; margin-bottom: 1.5rem;">
            <div>
                <h4 style="color: var(--gold-light); font-size: 1.2rem; margin-bottom: 1rem;">ΡΓΚ «ΑΦΡΟΔΙΤΗ»</h4>
                <p style="font-size: 1rem; opacity: 0.9; line-height: 1.6;">
                    Ресторанно-гостиничный комплекс «Афродита» — атмосфера греческого уюта и комфорта в самом сердце Белореченска.
                </p>
            </div>
            <div>
                <h4 style="color: var(--gold-light); font-size: 1.2rem; margin-bottom: 1rem;">Контакты</h4>
                <p style="font-size: 1rem; opacity: 0.9; line-height: 1.8;">
                    <i class="fas fa-map-marker-alt" style="width: 20px; color: var(--gold);"></i> Адрес: 
                    <a href="https://yandex.ru/maps/?text=Белореченск, улица Мира, 65/1" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       style="color: var(--marble-white); text-decoration: underline;">
                        г. Белореченск, ул. Мира, 65/1
                    </a><br>
                    <i class="fas fa-phone" style="width: 20px; color: var(--gold);"></i> Телефон: 
                    <a href="tel:+78615533070" style="color: var(--marble-white); text-decoration: underline;">
                        +7 (86155) 3-30-70
                    </a><br>
                    <i class="fas fa-phone" style="width: 20px; color: var(--gold); visibility: hidden;"></i> Доп.: 
                    <a href="tel:+79186811128" style="color: var(--marble-white); text-decoration: underline;">
                        +7 (918) 681-11-28
                    </a><br>
                    <i class="fas fa-envelope" style="width: 20px; color: var(--gold);"></i> Email: afrodita@rgk-afrodita.ru<br>
                    <i class="fas fa-clock" style="width: 20px; color: var(--gold);"></i> Режим работы: Круглосуточно
                    </p>
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
            © <?= date('Y') ?> Ресторанно-гостиничный комплекс «Афродита» — Добро пожаловать в мир греческого гостеприимства
        </p>
        <p style="font-family: 'Philosopher', sans-serif; font-size: 0.9rem; margin: 0.5rem 0 0 0; opacity: 0.7;">
            Разработано с любовью к традициям гостеприимства
        </p>
    </div>
</footer>
</body>
</html>