# Миграции базы данных

## Порядок применения миграций

После создания основной схемы (`schema.sql`) примените миграции в следующем порядке:

1. **add_room_images.sql** — таблица для изображений комнат
2. **add_room_features.sql** — таблица характеристик номеров и тестовые данные
3. **fix_double_booking.sql** — индекс для предотвращения двойных бронирований

## Применение миграций

```bash
mysql -u username -p database_name < database/migrations/add_room_images.sql
mysql -u username -p database_name < database/migrations/add_room_features.sql
mysql -u username -p database_name < database/migrations/fix_double_booking.sql
```

## Описание миграций

### add_room_images.sql
Создаёт таблицу `room_images` для хранения нескольких фотографий каждой комнаты.

### add_room_features.sql
- Создаёт таблицу `room_features` для характеристик номеров (площадь, Wi-Fi, кондиционер и т.д.)
- Добавляет тестовые характеристики для всех комнат

### fix_double_booking.sql
Добавляет уникальный индекс для предотвращения ситуации, когда одна комната бронируется на одинаковый период времени разными пользователями.
