<?php
session_start();
include('../includes/db_connect.php');


if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'buyer' && $_SESSION['user_type'] !== 'both')) {
    header("Location: ../login.php?type=buyer");
    exit();
}


$listingId = $_GET['listing_id'] ?? 0;
$stmt = $pdo->prepare("SELECT p.*, u.name AS farmer_name FROM produce_listings p JOIN users u ON p.farmer_id = u.id WHERE p.id = ?");
$stmt->execute([$listingId]);
$listing = $stmt->fetch();

if (!$listing) {
    die("Invalid listing");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = $_POST['quantity'];
    $offerPrice = $_POST['offer_price'];
    $message = $_POST['message'];
    
    try {
       
        $stmt = $pdo->prepare("
            INSERT INTO purchase_requests 
            (listing_id, buyer_id, crop_name, quantity, offer_price, message, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $listingId,
            $_SESSION['user_id'],
            $listing['crop_name'],
            $quantity,
            $offerPrice,
            $message
        ]);
        
        
        header("Location: dashboard.php?offer_sent=1");
        exit();
    } catch (PDOException $e) {
        $error = "Failed to submit offer: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Offer | Agritech Market Match</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a6f28;
            --secondary-color: #6b8c42;
            --accent-color: #f5a623;
            --light-color: #f8f9fa;
            --dark-color: #333;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f5f7f2;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .content {
            padding: 30px;
        }

        .listing-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .farmer-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .farmer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--secondary-color);
        }

        .farmer-details h3 {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .farmer-details p {
            font-size: 0.9rem;
            color: #666;
        }

        .listing-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            background-color: rgba(74, 111, 40, 0.05);
            padding: 12px;
            border-radius: var(--border-radius);
        }

        .detail-item h4 {
            font-size: 0.8rem;
            color: var(--primary-color);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-item p {
            font-size: 1rem;
            font-weight: 500;
        }

        .offer-form {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: #f9f9f9;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 111, 40, 0.2);
            background-color: white;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #3a5a20;
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            margin-right: 10px;
        }

        .btn-outline:hover {
            background-color: rgba(74, 111, 40, 0.1);
        }

        .error-message {
            color: var(--danger-color);
            background-color: rgba(220, 53, 69, 0.1);
            padding: 12px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            font-size: 1.2rem;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }
            
            .listing-details {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column-reverse;
                gap: 15px;
            }
            
            .btn-outline, .btn-primary {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .farmer-info {
                flex-direction: column;
                text-align: center;
            }
            
            .farmer-details {
                text-align: center;
            }
        }

        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(74, 111, 40, 0.3);
            border-radius: 50%;
            border-top: 3px solid var(--primary-color);
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Make an Offer</h1>
            <p>Submit your purchase request to the farmer</p>
        </div>
        
        <div class="content">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <div class="listing-card">
                <div class="farmer-info">
                    <img src="<?php echo !empty($listing['farmer_photo']) ? '../uploads/' . htmlspecialchars($listing['farmer_photo']) : '../assets/default-profile.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($listing['farmer_name']); ?>" 
                         class="farmer-avatar">
                    <div class="farmer-details">
                        <h3><?php echo htmlspecialchars($listing['farmer_name']); ?></h3>
                        <p><?php echo htmlspecialchars($listing['location']); ?></p>
                    </div>
                </div>
                
                <div class="listing-details">
                    <div class="detail-item">
                        <h4>Crop Name</h4>
                        <p><?php echo htmlspecialchars($listing['crop_name']); ?></p>
                    </div>
                    <div class="detail-item">
                        <h4>Available Quantity</h4>
                        <p><?php echo htmlspecialchars($listing['quantity']); ?> kg</p>
                    </div>
                    <div class="detail-item">
                        <h4>Listed Price</h4>
                        <p>Ksh <?php echo number_format($listing['price'], 2); ?> per kg</p>
                    </div>
                    <div class="detail-item">
                        <h4>Harvest Date</h4>
                        <p><?php echo date('M j, Y', strtotime($listing['harvest_date'] ?? '2023-12-15')); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="offer-form">
                <form method="POST">
                    <div class="form-group">
                        <label for="quantity">Quantity You Want</label>
                        <input type="text" id="quantity" name="quantity" class="form-control" 
                               placeholder="e.g., 100kg or 5 bags" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="offer_price">Your Offer Price (per unit, Ksh)</label>
                        <input type="number" id="offer_price" name="offer_price" class="form-control" 
                               step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message to Farmer (optional)</label>
                        <textarea id="message" name="message" class="form-control" 
                                  placeholder="Add any special requests or notes for the farmer..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Offer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Form submission loading animation
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';
            submitBtn.disabled = true;
        });

        // Price suggestion based on listed price
        document.getElementById('offer_price').addEventListener('focus', function() {
            if (!this.value) {
                const listedPrice = <?php echo $listing['price']; ?>;
                this.value = (listedPrice * 0.95).toFixed(2); // Suggest 5% below listed price
            }
        });
    </script>
</body>
</html>