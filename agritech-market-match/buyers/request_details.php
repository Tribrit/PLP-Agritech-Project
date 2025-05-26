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
    SELECT r.*, p.price AS listing_price, u.name AS farmer_name, u.phone AS farmer_phone,
           u.location AS farmer_location, p.quantity AS available_quantity
    FROM purchase_requests r
    JOIN produce_listings p ON r.listing_id = p.id
    JOIN users u ON p.farmer_id = u.id
    WHERE r.id = ? AND r.buyer_id = ?
");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$request = $stmt->fetch();

if (!$request) {
    header("Location: dashboard.php?error=request_not_found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details | AgriMarket</title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #388E3C;
            --accent-color: #8BC34A;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --medium-gray: #e0e0e0;
            --dark-gray: #757575;
            --white: #ffffff;
            --box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-gray);
            color: var(--text-color);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        h2 {
            color: var(--secondary-color);
            margin: 20px 0 15px;
            font-size: 1.5rem;
        }
        
        .back-btn {
            display: inline-block;
            background-color: var(--medium-gray);
            color: var(--text-color);
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .back-btn:hover {
            background-color: var(--dark-gray);
            color: white;
        }
        
        .request-card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-group {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark-gray);
            display: block;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1.1rem;
        }
        
        .price-highlight {
            font-size: 1.3rem;
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .status-pending {
            background-color: #FFF3E0;
            color: #E65100;
        }
        
        .status-accepted {
            background-color: #E8F5E9;
            color: var(--secondary-color);
        }
        
        .status-rejected {
            background-color: #FFEBEE;
            color: #C62828;
        }
        
        .status-completed {
            background-color: #E3F2FD;
            color: #1565C0;
        }
        
        .action-btns {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        
        .btn-danger {
            background-color: #E53935;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #C62828;
        }
        
        .btn-secondary {
            background-color: var(--medium-gray);
            color: var(--text-color);
        }
        
        .btn-secondary:hover {
            background-color: var(--dark-gray);
            color: white;
        }
        
        .farmer-contact {
            background-color: var(--light-gray);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .farmer-contact a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .farmer-contact a:hover {
            text-decoration: underline;
        }
        
        .additional-info {
            margin-top: 30px;
        }
        
        .info-card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 20px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: var(--accent-color);
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--primary-color);
        }
        
        .timeline-date {
            font-size: 0.9rem;
            color: var(--dark-gray);
        }
        
        .timeline-content {
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            h2 {
                font-size: 1.3rem;
            }
            
            .request-details {
                grid-template-columns: 1fr;
            }
            
            .action-btns {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>AgriMarket</h1>
        </div>
    </header>
    
    <div class="container">
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="request-card">
            <h2><?php echo htmlspecialchars($request['crop_name']); ?> Purchase Request</h2>
            
            <div class="request-details">
                <div class="detail-group">
                    <span class="detail-label">Farmer Name</span>
                    <span class="detail-value"><?php echo htmlspecialchars($request['farmer_name']); ?></span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Farmer Location</span>
                    <span class="detail-value"><?php echo htmlspecialchars($request['farmer_location']); ?></span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Original Price</span>
                    <span class="detail-value price-highlight">Ksh <?php echo number_format($request['listing_price'], 2); ?></span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Your Offer Price</span>
                    <span class="detail-value price-highlight">Ksh <?php echo number_format($request['offer_price'], 2); ?></span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Quantity Requested</span>
                    <span class="detail-value"><?php echo htmlspecialchars($request['quantity']); ?> kg (Available: <?php echo htmlspecialchars($request['available_quantity']); ?> kg)</span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </span>
                </div>
            </div>
            
            <?php if (!empty($request['message'])): ?>
                <div class="detail-group">
                    <span class="detail-label">Your Message</span>
                    <p><?php echo htmlspecialchars($request['message']); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="farmer-contact">
                <h3>Farmer Contact Information</h3>
                <p>You can contact the farmer directly to discuss this transaction:</p>
                <p><strong>Phone:</strong> <a href="tel:<?php echo htmlspecialchars($request['farmer_phone']); ?>"><?php echo htmlspecialchars($request['farmer_phone']); ?></a></p>
            </div>
            
            <div class="action-btns">
                <?php if ($request['status'] == 'pending'): ?>
                    <a href="cancel_request.php?id=<?php echo $request['id']; ?>" class="btn btn-danger">Cancel Request</a>
                <?php elseif ($request['status'] == 'accepted'): ?>
                    <a href="confirm_order.php?id=<?php echo $request['id']; ?>" class="btn btn-primary">Confirm Delivery</a>
                <?php endif; ?>
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
        
        <div class="additional-info">
            <div class="info-card">
                <h3>Transaction Timeline</h3>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date"><?php echo date('M j, Y g:i a', strtotime($request['created_at'])); ?></div>
                        <div class="timeline-content">Request submitted</div>
                    </div>
                    <?php if ($request['status'] != 'pending'): ?>
                        <div class="timeline-item">
                            <div class="timeline-date"><?php echo date('M j, Y g:i a', strtotime($request['updated_at'])); ?></div>
                            <div class="timeline-content">Request <?php echo $request['status']; ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="info-card">
                <h3>Tips for Successful Transactions</h3>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>Always verify the product quality before making payment</li>
                    <li>Communicate clearly about delivery/pickup arrangements</li>
                    <li>Report any issues immediately through the platform</li>
                    <li>Complete the transaction confirmation after receiving goods</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>