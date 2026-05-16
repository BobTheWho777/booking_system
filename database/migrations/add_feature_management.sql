-- SQL для управления характеристиками через админку
-- Этот файл можно использовать для ручного добавления/изменения характеристик

-- Пример добавления новой характеристики для всех комнат
-- INSERT INTO room_features (room_id, feature_code, feature_value, sort_order)
-- VALUES (1, 'minibar', 'Мини-бар', 5);

-- Пример удаления характеристики
-- DELETE FROM room_features WHERE feature_code = 'old_feature';

-- Пример обновления значения характеристики
-- UPDATE room_features SET feature_value = 'Новое значение' WHERE feature_code = 'wifi';
