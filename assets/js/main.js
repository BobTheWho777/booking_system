// Этот файл можно использовать для дополнительной функциональности
// Основная логика уже встроена в страницы (index.php, booking.php)

document.addEventListener('DOMContentLoaded', function() {
    // Плавная прокрутка для всех якорных ссылок
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});
