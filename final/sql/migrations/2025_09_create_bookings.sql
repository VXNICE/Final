-- migrations/2025_09_create_bookings.sql
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  guests INT NOT NULL DEFAULT 1,
  extras JSON NULL,
  notes TEXT NULL,
  status_id TINYINT NOT NULL DEFAULT 2, -- 1=confirmed, 2=pending, 3=declined, 4=cancelled
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX(room_id),
  INDEX(user_id),
  INDEX(status_id),
  INDEX(start_date),
  INDEX(end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
