<?php
$host = 'localhost';
$db   = 'agritech_market';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}












//  (for testing)
$pdo->exec("INSERT INTO market_prices (crop_name, price, market_location) VALUES 
    ('Maize', 50.00, 'Nairobi'),
    ('Tomatoes', 120.00, 'Kisumu'),
    ('Beans', 80.00, 'Mombasa')
    ON DUPLICATE KEY UPDATE price=VALUES(price)");

?>