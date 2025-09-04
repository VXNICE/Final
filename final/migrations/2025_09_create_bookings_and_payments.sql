-- Migration: create bookings table with payment fields
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  guests INT NOT NULL DEFAULT 1,
  extras JSON NULL,
  notes TEXT NULL,
  status_id TINYINT NOT NULL DEFAULT 2,
  payment_method VARCHAR(50) NULL,
  payment_reference VARCHAR(255) NULL,
  payment_receipt_path VARCHAR(255) NULL,
  payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX(room_id), INDEX(user_id), INDEX(status_id), INDEX(start_date), INDEX(end_date)
);

-- add payment columns if missing
ALTER TABLE bookings
  ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NULL,
  ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS payment_receipt_path VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid';
