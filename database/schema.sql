-- ========================================
-- Полная схема базы данных для системы бронирования «Олимп»
-- ========================================

-- 1. Типы номеров
CREATE TABLE IF NOT EXISTS room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    capacity INT NOT NULL DEFAULT 2,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Комнаты
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(20) NOT NULL,
    type_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    status ENUM('available', 'booked', 'occupied', 'maintenance') DEFAULT 'available',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES room_types(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_room_number (number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Гости
CREATE TABLE IF NOT EXISTS guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(150),
    passport VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Бронирования
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_number VARCHAR(50) NOT NULL UNIQUE,
    guest_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guest_count INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE RESTRICT,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Пользователи (администраторы)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Изображения комнат
CREATE TABLE IF NOT EXISTS room_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Характеристики номеров
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

-- ========================================
-- Добавляем тестовые данные
-- ========================================

-- Типы номеров
INSERT INTO room_types (name, capacity, description) VALUES
('Стандарт', 2, 'Уютный номер с двумя односпальными кроватями'),
('Делюкс', 3, 'Просторный номер с двуспальной кроватью и дополнительным местом'),
('Люкс', 4, 'Роскошный номер с отдельной гостиной и спальней'),
('Эконом', 1, 'Компактный номер с одной кроватью');

-- Комнаты
INSERT INTO rooms (number, type_id, price, status, description) VALUES
('101', 1, 3500.00, 'available', 'Стандартный номер на втором этаже'),
('102', 1, 3500.00, 'available', 'Стандартный номер с видом во двор'),
('201', 2, 5500.00, 'available', 'Делюкс номер с балконом'),
('202', 2, 5500.00, 'available', 'Делюкс номер с видом на море'),
('301', 3, 8500.00, 'available', 'Люкс с панорамным видом'),
('302', 3, 8500.00, 'available', 'Люкс с джакузи'),
('103', 4, 2000.00, 'available', 'Экономичный номер для одного гостя'),
('104', 4, 2000.00, 'available', 'Экономичный номер для одного гостя');

-- Администратор
-- Логин: admin
-- Пароль: admin
INSERT INTO users (username, password_hash, role) VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
);

-- Характеристики для комнат
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

-- ========================================
-- Готово!
-- ========================================
