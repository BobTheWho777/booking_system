# Установка изображений комнат

## 1. Создайте таблицу в базе данных

Выполните SQL-запрос из файла `database/migrations/add_room_images.sql`:

```sql
CREATE TABLE IF NOT EXISTS room_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 2. Папка для загрузок

Папка `uploads/rooms/` уже создана. Убедитесь, что веб-сервер имеет права на запись в эту папку.

## 3. Как использовать

### В админ-панели:
1. Перейдите в раздел "Комнаты" (`admin/rooms.php`)
2. Добавьте новую комнату или отредактируйте существующую
3. В поле "Изображения комнаты" выберите один или несколько файлов (JPG, PNG, GIF)
4. Сохраните комнату

### На главной странице:
- Изображения отображаются в виде слайдера в карточке каждой комнаты
- Можно переключаться между фото стрелками или точками внизу
- При клике на изображение открывается полноэкранный просмотр (lightbox)

## 4. Безопасность

В папках `uploads/` и `uploads/rooms/` созданы файлы `.htaccess`, которые:
- Запрещают выполнение PHP-скриптов
- Разрешают доступ только к изображениям
