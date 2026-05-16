-- Добавляем таблицу характеристик номеров
CREATE TABLE IF NOT EXISTS room_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    feature_code VARCHAR(50) NOT NULL,
    feature_value VARCHAR(100),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_room_feature (room_id, feature_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Добавляем тестовые характеристики для комнат
INSERT INTO room_features (room_id, feature_code, feature_value, sort_order) VALUES
-- Комната 101 (Стандарт)
(1, 'area', '18 м²'),
(1, 'wifi', 'Бесплатный Wi-Fi'),
(1, 'ac', 'Кондиционер'),
(1, 'fridge', 'Холодильник'),
-- Комната 102 (Стандарт)
(2, 'area', '18 м²'),
(2, 'wifi', 'Бесплатный Wi-Fi'),
(2, 'ac', 'Кондиционер'),
(2, 'tv', 'Телевизор'),
-- Комната 201 (Делюкс)
(3, 'area', '25 м²'),
(3, 'wifi', 'Бесплатный Wi-Fi'),
(3, 'ac', 'Кондиционер'),
(3, 'balcony', 'Балкон'),
-- Комната 202 (Делюкс)
(4, 'area', '25 м²'),
(4, 'wifi', 'Бесплатный Wi-Fi'),
(4, 'ac', 'Кондиционер'),
(4, 'sea_view', 'Вид на море'),
-- Комната 301 (Люкс)
(5, 'area', '45 м²'),
(5, 'wifi', 'Бесплатный Wi-Fi'),
(5, 'ac', 'Кондиционер'),
(5, 'jacuzzi', 'Джакузи'),
-- Комната 302 (Люкс)
(6, 'area', '45 м²'),
(6, 'wifi', 'Бесплатный Wi-Fi'),
(6, 'ac', 'Кондиционер'),
(6, 'living_room', 'Гостиная'),
-- Комната 103 (Эконом)
(7, 'area', '12 м²'),
(7, 'wifi', 'Бесплатный Wi-Fi'),
(7, 'ac', 'Кондиционер'),
-- Комната 104 (Эконом)
(8, 'area', '12 м²'),
(8, 'wifi', 'Бесплатный Wi-Fi'),
(8, 'ac', 'Кондиционер');
