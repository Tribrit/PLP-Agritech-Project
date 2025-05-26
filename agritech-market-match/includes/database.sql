CREATE DATABASE agritech_market;
USE agritech_market;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    location VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('farmer', 'buyer', 'both') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE market_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    market_location VARCHAR(100),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produce_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    crop_name VARCHAR(50) NOT NULL,
    quantity VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(id)
);

CREATE TABLE purchase_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    buyer_id INT NOT NULL,
    crop_name VARCHAR(50) NOT NULL,
    quantity VARCHAR(50) NOT NULL,
    offer_price DECIMAL(10,2) NOT NULL,
    message TEXT,
    status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES produce_listings(id),
    FOREIGN KEY (buyer_id) REFERENCES users(id)
);

INSERT INTO market_prices (crop_name, price, market_location) VALUES 
    ('Maize', 50.00, 'Nairobi'),
    ('Tomatoes', 120.00, 'Kisumu'),
    ('Beans', 80.00, 'Mombasa');