CREATE DATABASE IF NOT EXISTS guesthouse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE guesthouse;

CREATE TABLE admins (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    full_name  VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
,,x                                                                                         x                                                                                           
CREATE TABLE rooms (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    description     TEXT,
    capacity        INT NOT NULL DEFAULT 2,
    price_per_night DECIMAL(10,2) NOT NULL,
    image           VARCHAR(255),
    amenities       TEXT,
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE bookings (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    room_id          INT NOT NULL,
    guest_name       VARCHAR(100) NOT NULL,
    guest_email      VARCHAR(150) NOT NULL,
    guest_phone      VARCHAR(20),
    check_in         DATE NOT NULL,
    check_out        DATE NOT NULL,
    num_guests       INT NOT NULL DEFAULT 1,
    total_price      DECIMAL(10,2) NOT NULL,
    status           ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    special_requests TEXT,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE blocked_dates (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    room_id      INT NOT NULL,
    blocked_date DATE NOT NULL,
    reason       VARCHAR(255),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE gallery (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    caption    VARCHAR(255),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
    setting_key   VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
);

INSERT INTO admins (username, password, full_name) VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Site Administrator'
);

INSERT INTO settings (setting_key, setting_value) VALUES
    ('site_name',      'The Grand Guesthouse'),
    ('site_email',     'info@grandguesthouse.com'),
    ('site_phone',     '+27 12 345 6789'),
    ('site_address',   '123 Fake Street, Johannesburg'),
    ('check_in_time',  '14:00'),
    ('check_out_time', '10:00');

INSERT INTO rooms (name, description, capacity, price_per_night, amenities) VALUES
    ('Standard Room', 'Cozy and comfortable room with all the essentials.', 2, 650.00, 'WiFi,TV,Air Conditioning,En-suite Bathroom'),
    ('Deluxe Double',  'Spacious room with a king-size bed and garden view.', 2, 950.00, 'WiFi,TV,Air Conditioning,Mini Fridge,En-suite Bathroom,Garden View'),
    ('Family Suite',   'Perfect for families with separate sleeping areas.', 4, 1400.00, 'WiFi,TV,Air Conditioning,Mini Fridge,En-suite Bathroom,Kitchenette,Lounge Area');