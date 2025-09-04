-- Add favorites table
CREATE TABLE IF NOT EXISTS favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_favorite (user_id, room_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Add reviews table
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Add social_logins table
CREATE TABLE IF NOT EXISTS social_logins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  provider VARCHAR(50) NOT NULL,
  provider_user_id VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_provider_user (provider, provider_user_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add notifications table
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message TEXT NOT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add analytics table
CREATE TABLE IF NOT EXISTS analytics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_type VARCHAR(100) NOT NULL,
  event_data JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add terms_accepted field to users table
ALTER TABLE users
ADD COLUMN terms_accepted BOOLEAN DEFAULT FALSE;

-- Add indexes for filtering/sorting
CREATE INDEX idx_rooms_location ON rooms(location);
CREATE INDEX idx_rooms_price_per_night ON rooms(price_per_night);
CREATE INDEX idx_rooms_capacity ON rooms(capacity);
