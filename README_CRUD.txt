CRUD bundle for hotel_booking_system

Files included:
- config/config.php
- includes/auth.php
- includes/admin_layout.php
- admin/index.php
- admin/rooms.php
- admin/room_types.php
- admin/guests.php

Notes:
- BASE_URL is set for the folder /is-4/hotel_booking_system/.
- The CRUD matches the current database schema:
  - rooms: number, type_id, price, status, description
  - room_types: name, capacity, description
  - guests: first_name, last_name, phone, email, passport
- Deletion is blocked when foreign keys are in use.
