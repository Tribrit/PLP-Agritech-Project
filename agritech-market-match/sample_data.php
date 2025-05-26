<?php
include('includes/db_connect.php');

// Sample farmers
$farmers = [
    ['John Kamau', 'john@example.com', '0721000001', 'Nairobi', 'farmer'],
    ['Mary Wanjiku', 'mary@example.com', '0721000002', 'Kiambu', 'farmer'],
    ['Peter Mwangi', 'peter@example.com', '0721000003', 'Nakuru', 'farmer'],
    ['Grace Akinyi', 'grace@example.com', '0721000004', 'Kisumu', 'farmer'],
    ['James Mutua', 'james@example.com', '0721000005', 'Machakos', 'farmer'],
    ['Sarah Wambui', 'sarah@example.com', '0721000006', 'Meru', 'farmer']
];

// Sample buyers
$buyers = [
    ['Nakumatt Supermarket', 'nakumatt@example.com', '0722000001', 'Nairobi', 'buyer'],
    ['Tuskys Supermarket', 'tuskys@example.com', '0722000002', 'Nairobi', 'buyer'],
    ['Kisumu Market', 'kisumu@example.com', '0722000003', 'Kisumu', 'buyer'],
    ['Nakuru Wholesalers', 'nakuru@example.com', '0722000004', 'Nakuru', 'buyer'],
    ['Mombasa Exporters', 'mombasa@example.com', '0722000005', 'Mombasa', 'buyer']
];

// Sample produce listings
$produce = [
    [1, 'Maize', '1000kg', 50, 'Nairobi'],
    [1, 'Beans', '500kg', 80, 'Nairobi'],
    [2, 'Tomatoes', '200kg', 120, 'Kiambu'],
    [2, 'Potatoes', '1000kg', 40, 'Kiambu'],
    [3, 'Wheat', '5000kg', 60, 'Nakuru'],
    [4, 'Fish', '300kg', 200, 'Kisumu'],
    [5, 'Avocado', '2000kg', 30, 'Machakos'],
    [6, 'Coffee', '1000kg', 300, 'Meru']
];

// Insert sample data
try {
    // Insert farmers
    foreach ($farmers as $farmer) {
        $password = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, location, password, user_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$farmer[0], $farmer[1], $farmer[2], $farmer[3], $password, $farmer[4]]);
    }
    
    // Insert buyers
    foreach ($buyers as $buyer) {
        $password = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, location, password, user_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$buyer[0], $buyer[1], $buyer[2], $buyer[3], $password, $buyer[4]]);
    }
    
    // Insert produce listings
    foreach ($produce as $item) {
        $stmt = $pdo->prepare("INSERT INTO produce_listings (farmer_id, crop_name, quantity, price, location) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute($item);
    }
    
    echo "Sample data inserted successfully!";
} catch (PDOException $e) {
    echo "Error inserting sample data: " . $e->getMessage();
}