<?php
include('includes/db_connect.php');

// Simulate API fetch (replace with real API/scraping)
$cropPrices = [
    ['Maize', rand(40, 60), 'Nairobi'],
    ['Tomatoes', rand(100, 150), 'Kisumu'],
    ['Beans', rand(70, 90), 'Mombasa']
];

// Update DB
$stmt = $pdo->prepare("INSERT INTO market_prices (crop_name, price, market_location) VALUES (?, ?, ?)");
foreach ($cropPrices as $price) {
    $stmt->execute($price);
}

echo "Prices updated!";
?>