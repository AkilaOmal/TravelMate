CREATE DATABASE IF NOT EXISTS travelmate_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE travelmate_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS places (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    country VARCHAR(100) NOT NULL,
    location VARCHAR(150) NOT NULL DEFAULT '',
    category ENUM('Beach', 'Mountain', 'Historical', 'Nature', 'City', 'Other') NOT NULL,
    short_description VARCHAR(500) NOT NULL,
    full_description TEXT NOT NULL,
    travel_tips TEXT NULL,
    image VARCHAR(1000) NULL,
    status ENUM('pending', 'approved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_places_status_created (status, created_at),
    KEY idx_places_category (category),
    KEY idx_places_user (user_id),
    CONSTRAINT fk_places_user FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    subject VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_messages_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO places (user_id, name, country, location, category, short_description, full_description, travel_tips, image, status)
SELECT NULL, 'Hallstatt', 'Austria', 'Salzkammergut', 'Mountain',
       'A lakeside village framed by Alpine peaks and peaceful water.',
       'Hallstatt is a small lakeside village known for its traditional architecture, mountain scenery, and deep connection to salt-mining history. Walk along the waterfront, explore the old town, and take in the views at a relaxed pace.',
       'Arrive early for quieter lakeside walks. Check seasonal transport and opening hours before travelling.',
       'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?auto=format&fit=crop&w=1200&q=85', 'approved'
WHERE NOT EXISTS (SELECT 1 FROM places WHERE name = 'Hallstatt' AND country = 'Austria');

INSERT INTO places (user_id, name, country, location, category, short_description, full_description, travel_tips, image, status)
SELECT NULL, 'Kyoto', 'Japan', 'Kansai', 'Historical',
       'A city of temple gardens, historic lanes, and thoughtful traditions.',
       'Kyoto offers a rich mix of historic temples, quiet gardens, neighbourhood streets, and seasonal beauty. Give yourself time to explore beyond the busiest spots and enjoy the city respectfully.',
       'Use public transport where practical, follow signs at sacred sites, and visit popular areas early in the day.',
       'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1200&q=85', 'approved'
WHERE NOT EXISTS (SELECT 1 FROM places WHERE name = 'Kyoto' AND country = 'Japan');

INSERT INTO places (user_id, name, country, location, category, short_description, full_description, travel_tips, image, status)
SELECT NULL, 'Ubud', 'Indonesia', 'Bali', 'Nature',
       'A green inland escape surrounded by rice terraces and local arts.',
       'Ubud is a cultural town in the heart of Bali, surrounded by lush landscapes, rice terraces, and small villages. Visitors can explore local crafts, markets, and nature at a slower pace.',
       'Dress respectfully at temples, bring water for walks, and support local guides and businesses.',
       'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1200&q=85', 'approved'
WHERE NOT EXISTS (SELECT 1 FROM places WHERE name = 'Ubud' AND country = 'Indonesia');
