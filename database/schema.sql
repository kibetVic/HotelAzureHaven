CREATE DATABASE IF NOT EXISTS azure_haven CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE azure_haven;

DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;

-- 1. Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Rooms Table (Grouped with unit quantity)
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_group VARCHAR(50) NOT NULL UNIQUE,   -- e.g. "Standard Deluxe", "Executive Suite"
    type VARCHAR(50) NOT NULL,                -- Standard / Deluxe / Suite / Presidential / Family
    title VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    capacity INT DEFAULT 2,
    beds INT DEFAULT 1,
    size_sqm INT,
    total_quantity INT DEFAULT 10,            -- Total room units in this group
    image VARCHAR(255),
    amenities TEXT,
    status ENUM('available','maintenance') DEFAULT 'available'
);

-- 3. Bookings Table (updated to track quantity & room_group)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    guest_name VARCHAR(150) NULL,
    guest_email VARCHAR(150) NULL,
    room_id INT NOT NULL,                     -- references rooms.id (group)
    quantity INT NOT NULL DEFAULT 1,          -- number of units booked from that group
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('confirmed','pending','cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 4. Contact Messages Table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(150),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Meals Table
CREATE TABLE IF NOT EXISTS `meals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `image_url` TEXT DEFAULT NULL,
  `status` ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Admin User (Password: AdminPass123!)
INSERT INTO users (name, email, password, phone, role) VALUES
('System Administrator', 'admin@azurehaven.com', '$2y$10$eE5/8B.8QfJ6G2c7S5wK8u3A1fQZzX9nL6yE9fQ8vP4o3r2s1t0uO', '+254 700 276 320', 'admin');

-- 12 Room Groups
INSERT INTO rooms (id, room_group, type, title, description, price, capacity, beds, size_sqm, total_quantity, image, amenities, status) VALUES
(1,  'Standard Deluxe',      'Suite',        'Deluxe Oceanfront Suite',   'Panoramic balcony views with personal king bed and marble bath.',       245.00, 2, 1, 55,  5,  'deluxe.jpg',     'WiFi,AC,TV,Mini-bar,Balcony',                'available'),
(2,  'Coastal Studio',       'Standard',     'Coastal Studio Room',       'Cozy modern space perfect for solo travelers or couples.',              102.00, 2, 1, 30,  10, 'standard.jpg',   'WiFi,AC,TV',                                 'available'),
(3,  'Executive Bay',        'Deluxe',       'Executive Bay View',        'Spacious accommodation equipped with work area and private terrace.',   175.00, 3, 2, 45,  6,  'executive.jpg',  'WiFi,AC,TV,Mini-bar,Terrace',                'available'),
(4,  'Sunset Palm',          'Standard',     'Sunset Palm Haven',         'Relax with floor-to-ceiling windows overlooking tropical palm trees.',  135.00, 2, 1, 35,  8,  'sunset.jpg',     'WiFi,AC,TV,Mini-fridge',                     'available'),
(5,  'Seaside Villa',        'Presidential', 'Seaside Villa Suite',       'Premium multi-room layout with direct resort beach access.',            250.00, 4, 2, 90,  3,  'villa.jpg',      'WiFi,AC,TV,Jacuzzi,Beach-Access',            'available'),
(6,  'Azure Breeze',         'Standard',     'Azure Breeze Chamber',      'Quiet corner suite offering constant ocean breeze and plush seating.',  118.00, 2, 1, 32,  7,  'breeze.jpg',     'WiFi,AC,TV',                                 'available'),
(7,  'Royal Horizon',        'Presidential', 'Royal Horizon Penthouse',   'Top-floor luxury complete with private Jacuzzi and sun loungers.',      240.00, 4, 2, 110, 2,  'penthouse.jpg',  'WiFi,AC,TV,Jacuzzi,Butler,Terrace',          'available'),
(8,  'Garden Coast',         'Standard',     'Garden Coast Retreat',      'Peaceful ground room adjacent to our lush private coastal garden.',     110.00, 2, 1, 34,  8,  'garden.jpg',     'WiFi,AC,TV,Garden-View',                     'available'),
(9,  'Coral Reef Family',    'Family',       'Coral Reef Family Suite',   'Double queen bedding setup tailored for family getaways.',              198.00, 5, 2, 65,  4,  'family.jpg',     'WiFi,AC,TV,Mini-fridge,Sofa',                'available'),
(10, 'Nautical Vista',       'Deluxe',       'Nautical Vista Balcony',    'Elevated room featuring panoramic harbor views and artisan décor.',     162.00, 2, 1, 40,  6,  'nautical.jpg',   'WiFi,AC,TV,Balcony',                         'available'),
(11, 'Serenity Ocean',       'Suite',        'Serenity Ocean Cottage',    'Charming standalone cottage steps away from white sand shoreline.',     215.00, 3, 2, 50,  3,  'cottage.jpg',    'WiFi,AC,TV,Kitchenette,Balcony',             'available'),
(12, 'Minimalist Dune',      'Standard',     'Minimalist Dune Studio',    'Sleek contemporary design emphasizing light, simplicity, and calm.',     105.00, 2, 1, 28,  10, 'dune.jpg',       'WiFi,AC,TV',                                 'available');