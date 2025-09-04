-- ===============================================
-- StayFind (3NF) – Minimal Schema WITH Foreign Keys
-- No demo data. Compatible with MariaDB/MySQL (InnoDB)
-- Creates database: stayfind3nf_db
-- ===============================================

CREATE DATABASE IF NOT EXISTS stayfind3nf_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE stayfind3nf_db;

-- Lookups
CREATE TABLE IF NOT EXISTS roles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  role_name VARCHAR(32) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT IGNORE INTO roles (role_name)
VALUES ('guest'),('user'),('owner'),('manager'),('worker'),('admin');

CREATE TABLE IF NOT EXISTS booking_statuses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  status_name VARCHAR(32) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT IGNORE INTO booking_statuses (status_name)
VALUES ('pending'),('awaiting_approval'),('approved'),('rejected'),('cancelled'),('completed');

-- Core
CREATE TABLE IF NOT EXISTS users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE,
  password VARCHAR(255) NULL,         -- legacy/plain (optional)
  password_hash VARCHAR(255) NULL,    -- preferred
  name VARCHAR(120) NULL,
  email VARCHAR(190) UNIQUE,
  phone VARCHAR(30) NULL,
  role_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_role_id (role_id),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rooms (
  id INT PRIMARY KEY AUTO_INCREMENT,
  owner_id INT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  price_per_night DECIMAL(10,2) NULL,
  location VARCHAR(255) NULL,
  capacity INT NULL,
  image_path VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rooms_owner (owner_id),
  INDEX idx_rooms_location (location),
  INDEX idx_rooms_price (price_per_night),
  CONSTRAINT fk_rooms_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
  id INT PRIMARY KEY AUTO_INCREMENT,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_bookings_room (room_id),
  INDEX idx_bookings_user (user_id),
  INDEX idx_bookings_status (status_id),
  CONSTRAINT fk_bookings_room   FOREIGN KEY (room_id)  REFERENCES rooms(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_user   FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_status FOREIGN KEY (status_id) REFERENCES booking_statuses(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS receipts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  booking_id INT NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  mime_type VARCHAR(100) NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_receipts_booking (booking_id),
  CONSTRAINT fk_receipts_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
  id INT PRIMARY KEY AUTO_INCREMENT,
  room_id INT NOT NULL,
  user_id INT NOT NULL,
  booking_id INT NULL,
  rating TINYINT NOT NULL,
  comment TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_reviews_room (room_id),
  INDEX idx_reviews_user (user_id),
  CONSTRAINT fk_reviews_room    FOREIGN KEY (room_id)  REFERENCES rooms(id)    ON DELETE CASCADE,
  CONSTRAINT fk_reviews_user    FOREIGN KEY (user_id)  REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_reviews_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS amenities (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS room_amenities (
  room_id INT NOT NULL,
  amenity_id INT NOT NULL,
  PRIMARY KEY (room_id, amenity_id),
  CONSTRAINT fk_ra_room    FOREIGN KEY (room_id)    REFERENCES rooms(id)      ON DELETE CASCADE,
  CONSTRAINT fk_ra_amenity FOREIGN KEY (amenity_id) REFERENCES amenities(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS room_images (
  id INT PRIMARY KEY AUTO_INCREMENT,
  room_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_room_images_room (room_id),
  CONSTRAINT fk_room_images_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Helpful view
CREATE OR REPLACE VIEW view_rooms_with_rating AS
SELECT r.id, r.title, r.location, r.price_per_night, r.capacity, r.image_path,
       ROUND(AVG(rv.rating),2) AS avg_rating, COUNT(rv.id) AS reviews_count
FROM rooms r
LEFT JOIN reviews rv ON rv.room_id = r.id
GROUP BY r.id;
