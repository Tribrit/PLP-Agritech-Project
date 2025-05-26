<?php
session_start();
include('../includes/db_connect.php');
include('../includes/functions.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'buyer') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT r.*, p.price AS listing_price, u.name AS farmer_name, u.phone AS farmer_phone, u.location AS farmer_location
    FROM purchase_requests r
    JOIN produce_listings p ON r.listing_id = p.id
    JOIN users u ON p.farmer_id = u.id
    WHERE r.id = ? AND r.buyer_id = ? AND r.status = 'accepted'
");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$request = $stmt->fetch();

if (!$request) {
    header("Location: dashboard.php?error=request_not_found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        $update = $pdo->prepare("
            UPDATE purchase_requests 
            SET status = 'completed' 
            WHERE id = ? AND buyer_id = ? AND status = 'accepted'
        ");
        $update->execute([$_GET['id'], $_SESSION['user_id']]);
        
        $markSold = $pdo->prepare("
            UPDATE produce_listings 
            SET quantity = '0' 
            WHERE id = ?
        ");
        $markSold->execute([$request['listing_id']]);
        
        $pdo->commit();
        
        header("Location: dashboard.php?success=order_completed");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: confirm_order.php?id={$_GET['id']}&error=update_failed");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Order Completion | Agritech Market Match</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Base Styles */
        :root {
            --primary-color: #4CAF50;
            --primary-dark: #388E3C;
            --primary-light: #C8E6C9;
            --secondary-color: #FF9800;
            --secondary-dark: #F57C00;
            --accent-color: #8BC34A;
            --dark-color: #2E7D32;
            --light-color: #F1F8E9;
            --text-dark: #333;
            --text-medium: #555;
            --text-light: #777;
            --white: #fff;
            --gray-light: #f5f5f5;
            --gray-medium: #e0e0e0;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 8px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 16px rgba(0,0,0,0.1);
            --border-radius: 8px;
            --border-radius-sm: 4px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background-color: var(--light-color);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            height: 40px;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .logo-text span {
            color: var(--secondary-color);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            padding: 40px 0;
            min-height: calc(100vh - 130px);
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title {
            font-size: 2rem;
            color: var(--primary-dark);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: var(--text-medium);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Order Confirmation Styles */
        .confirmation-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .confirmation-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-medium);
        }

        .confirmation-title {
            font-size: 1.5rem;
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        .confirmation-subtitle {
            color: var(--text-medium);
        }

        .order-details {
            margin-bottom: 30px;
        }

        .detail-card {
            background-color: var(--gray-light);
            border-radius: var(--border-radius-sm);
            padding: 20px;
            margin-bottom: 20px;
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .detail-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .detail-badge {
            background-color: var(--accent-color);
            color: var(--white);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }

        .detail-label {
            width: 150px;
            font-weight: 500;
            color: var(--text-medium);
        }

        .detail-value {
            flex: 1;
        }

        .farmer-contact {
            background-color: var(--primary-light);
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            border-radius: var(--border-radius-sm);
            margin-top: 20px;
        }

        .confirmation-form {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-medium);
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-check-input {
            margin-right: 10px;
            width: 20px;
            height: 20px;
            accent-color: var(--primary-color);
        }

        .form-check-label {
            font-weight: 500;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: inherit;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: var(--shadow-sm);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: var(--primary-light);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            border-radius: var(--border-radius-sm);
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert-danger {
            background-color: #FFEBEE;
            color: #C62828;
            border-left: 4px solid #F44336;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <div class="logo">
                <img src="https://via.placeholder.com/40x40" alt="Agritech Market Match Logo">
                <span class="logo-text">Agri<span>Match</span></span>
            </div>
            <div class="user-profile">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="container">
            <div class="page-header fade-in">
                <h1 class="page-title">Order Confirmation</h1>
                <p class="page-subtitle">Please confirm you've received your agricultural produce as agreed</p>
            </div>

            <div class="confirmation-container fade-in" style="animation-delay: 0.2s">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Error processing your confirmation. Please try again.
                    </div>
                <?php endif; ?>

                <div class="confirmation-header">
                    <h2 class="confirmation-title">Order #<?php echo htmlspecialchars($_GET['id']); ?></h2>
                    <p class="confirmation-subtitle">Please review the details below before confirming</p>
                </div>

                <div class="order-details">
                    <div class="detail-card">
                        <div class="detail-header">
                            <h3 class="detail-title">Product Information</h3>
                            <span class="detail-badge">Accepted</span>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Crop Name:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($request['crop_name']); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Quantity:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($request['quantity']); ?> kg</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Agreed Price:</div>
                            <div class="detail-value">Ksh <?php echo number_format($request['offer_price'], 2); ?> (<?php echo number_format($request['offer_price'] / $request['quantity'], 2); ?>/kg)</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Original Price:</div>
                            <div class="detail-value">Ksh <?php echo number_format($request['listing_price'], 2); ?>/kg</div>
                        </div>
                    </div>

                    <div class="detail-card">
                        <div class="detail-header">
                            <h3 class="detail-title">Farmer Details</h3>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Farmer Name:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($request['farmer_name']); ?></div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Location:</div>
                            <div class="detail-value"><?php echo htmlspecialchars($request['farmer_location']); ?></div>
                        </div>
                        <div class="farmer-contact">
                            <div class="detail-row">
                                <div class="detail-label">Contact Phone:</div>
                                <div class="detail-value">
                                    <a href="tel:<?php echo htmlspecialchars($request['farmer_phone']); ?>" style="color: var(--primary-dark); font-weight: 500;">
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($request['farmer_phone']); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" class="confirmation-form">
                    <div class="form-check">
                        <input type="checkbox" name="confirm" id="confirm" class="form-check-input" required>
                        <label for="confirm" class="form-check-label">
                            I confirm I have received this order in the exact quantity and quality as described above
                        </label>
                    </div>

                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Confirm Order Completion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer" style="background-color: var(--white); padding: 20px 0; text-align: center; color: var(--text-medium); font-size: 0.9rem;">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Agritech Market Match Platform. All rights reserved.</p>
            <p>Helping farmers connect with buyers since 2023</p>
        </div>
    </footer>

    <script>
        // Simple animation on load
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-in');
            
            fadeElements.forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, 100);
            });
        });
    </script>
</body>
</html>