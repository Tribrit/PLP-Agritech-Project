<?php
session_start();
include('../includes/db_connect.php');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'buyer' && $_SESSION['user_type'] !== 'both')) {
    header("Location: ../index.php");
    exit();
}

// Get buyer's location
$buyerLocation = $_SESSION['user_location'] ?? 'Nairobi';

// Initialize filter variables
$countyFilter = $_GET['county'] ?? '';
$cropFilter = $_GET['crop'] ?? '';

// Build the base query for listings
$query = "
    SELECT p.*, u.name AS farmer_name, u.phone AS farmer_phone 
    FROM produce_listings p
    JOIN users u ON p.farmer_id = u.id
    WHERE (p.location LIKE CONCAT('%', ?, '%') OR u.location LIKE CONCAT('%', ?, '%'))
";

// Initialize parameters array with buyer location
$params = [$buyerLocation, $buyerLocation];

// Add county filter if selected
if (!empty($countyFilter)) {
    $query .= " AND (p.location LIKE CONCAT('%', ?, '%') OR u.location LIKE CONCAT('%', ?, '%'))";
    $params[] = $countyFilter;
    $params[] = $countyFilter;
}

// Add crop filter if selected
if (!empty($cropFilter)) {
    $query .= " AND p.crop_name = ?";
    $params[] = $cropFilter;
}

// Complete the query with sorting
$query .= " ORDER BY p.created_at DESC";

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$listings = $stmt->fetchAll();

// Fetch buyer's previous requests (unchanged)
$requests = $pdo->prepare("
    SELECT r.*, p.crop_name, u.name AS farmer_name
    FROM purchase_requests r
    JOIN produce_listings p ON r.listing_id = p.id
    JOIN users u ON p.farmer_id = u.id
    WHERE r.buyer_id = ?
    ORDER BY r.created_at DESC
");
$requests->execute([$_SESSION['user_id']]);
$myRequests = $requests->fetchAll();

// Define counties and crops for dropdowns
$counties = [
    'Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika', 'Meru', 'Nyeri', 
    'Machakos', 'Kakamega', 'Kisii', 'Kericho', 'Bungoma', 'Busia', 'Kiambu', 
    "Murang'a", 'Kirinyaga', 'Nyandarua', 'Nandi', 'Uasin Gishu', 'Trans Nzoia', 
    'West Pokot', 'Baringo', 'Laikipia', 'Narok', 'Kajiado', 'Kilifi', 'Kwale', 
    'Taita Taveta', 'Tana River', 'Lamu', 'Garissa', 'Wajir', 'Mandera', 'Marsabit', 
    'Isiolo', 'Tharaka-Nithi', 'Embu', 'Kitui', 'Makueni', 'Nyamira', 'Homa Bay', 
    'Migori', 'Siaya', 'Vihiga', 'Bomet', 'Turkana', 'Samburu', 'Elgeyo-Marakwet'
];

$crops = ['Maize', 'Beans', 'Wheat', 'Tomatoes', 'Potatoes', 'Coffee', 'Tea', 'Avocado', 'Mangoes', 'Bananas'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard | Agritech Market Match</title>
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
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background-color: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100%;
            overflow-y: auto;
            z-index: 100;
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 20px;
            background-color: var(--primary-color);
            color: white;
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--dark-color);
            text-decoration: none;
            transition: var(--transition);
            border-left: 4px solid transparent;
        }

        .menu-item:hover, .menu-item.active {
            background-color: rgba(74, 111, 40, 0.1);
            border-left: 4px solid var(--primary-color);
            color: var(--primary-color);
        }

        .menu-item i {
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .menu-item span {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .sidebar-footer {
            padding: 20px;
            text-align: center;
            margin-top: auto;
            border-top: 1px solid #eee;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
            transition: var(--transition);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 25px;
        }

        .header-title h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .header-title p {
            font-size: 0.9rem;
            color: #666;
        }

        .user-profile {
            display: flex;
            align-items: center;
        }

        .user-profile img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid var(--primary-color);
        }

        .user-info h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .user-info p {
            font-size: 0.8rem;
            color: #666;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--danger-color);
            cursor: pointer;
            font-size: 0.9rem;
            margin-left: 15px;
            display: flex;
            align-items: center;
        }

        .logout-btn i {
            margin-right: 5px;
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .card-icon.primary {
            background-color: var(--primary-color);
        }

        .card-icon.secondary {
            background-color: var(--secondary-color);
        }

        .card-icon.accent {
            background-color: var(--accent-color);
        }

        .card-icon.info {
            background-color: var(--info-color);
        }

        .card-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .card-description {
            font-size: 0.85rem;
            color: #666;
        }

        /* Filter Section */
        .filter-section {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            margin-bottom: 25px;
        }

        .filter-section h2 {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .filter-section h2 i {
            margin-right: 10px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 111, 40, 0.2);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
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
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: rgba(74, 111, 40, 0.1);
        }

        .btn-accent {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-accent:hover {
            background-color: #e6951a;
        }

        /* Table Section */
        .table-section {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            margin-bottom: 25px;
            overflow-x: auto;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.3rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }

        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover td {
            background-color: rgba(74, 111, 40, 0.05);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .user-info-cell {
            display: flex;
            align-items: center;
        }

        .user-name {
            font-weight: 500;
        }

        .user-location {
            font-size: 0.8rem;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #b58a00;
        }

        .status-accepted {
            background-color: rgba(40, 167, 69, 0.2);
            color: #1e7e34;
        }

        .status-rejected {
            background-color: rgba(220, 53, 69, 0.2);
            color: #c82333;
        }

        .status-completed {
            background-color: rgba(23, 162, 184, 0.2);
            color: #117a8b;
        }

        .action-links {
            display: flex;
            gap: 10px;
        }

        .action-link {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
        }

        .action-link i {
            margin-right: 5px;
            font-size: 0.8rem;
        }

        .action-link.view {
            background-color: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
        }

        .action-link.view:hover {
            background-color: rgba(23, 162, 184, 0.2);
        }

        .action-link.call {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .action-link.call:hover {
            background-color: rgba(40, 167, 69, 0.2);
        }

        .action-link.offer {
            background-color: rgba(245, 166, 35, 0.1);
            color: var(--accent-color);
        }

        .action-link.offer:hover {
            background-color: rgba(245, 166, 35, 0.2);
        }

        .action-link.cancel {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .action-link.cancel:hover {
            background-color: rgba(220, 53, 69, 0.2);
        }

        .action-link.confirm {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .action-link.confirm:hover {
            background-color: rgba(40, 167, 69, 0.2);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 0.9rem;
            color: #999;
            margin-bottom: 20px;
        }

        /* Market Trends Section */
        .market-trends {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            margin-bottom: 25px;
        }

        .trend-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .trend-item:last-child {
            border-bottom: none;
        }

        .trend-crop {
            font-weight: 500;
        }

        .trend-price {
            font-weight: 600;
            color: var(--primary-color);
        }

        .trend-change {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 10px;
        }

        .trend-up {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .trend-down {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .trend-neutral {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 0.8rem;
            color: #666;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        /* Responsive Styles */
        @media (max-width: 1200px) {
            .sidebar {
                width: 250px;
            }
            .main-content {
                margin-left: 250px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1000;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .menu-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .user-profile {
                margin-top: 15px;
            }
            .dashboard-cards {
                grid-template-columns: 1fr 1fr;
            }
            .filter-form {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            .action-links {
                flex-direction: column;
                gap: 5px;
            }
            .action-link {
                width: 100%;
                justify-content: center;
            }
        }

        /* Menu Toggle Button */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary-color);
            cursor: pointer;
            margin-right: 15px;
        }

        @media (max-width: 992px) {
            .menu-toggle {
                display: block;
            }
        }

        /* Overlay for mobile menu */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Switch View Button */
        .switch-view-btn {
            background-color: var(--secondary-color);
            color: white;
            padding: 8px 15px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            transition: var(--transition);
        }

        .switch-view-btn:hover {
            background-color: #5a7a36;
            color: white;
        }

        .switch-view-btn i {
            margin-right: 8px;
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -60px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.8rem;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
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
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>AgriMatch</h2>
                <p>Connecting Farmers & Buyers</p>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="market_prices.php" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Market Prices</span>
                </a>
                <a href="available_produce.php" class="menu-item">
                    <i class="fas fa-shopping-basket"></i>
                    <span>Available Produce</span>
                </a>
                <a href="my_requests.php" class="menu-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>My Requests</span>
                    <?php if (count($myRequests) > 0): ?>
                        <span class="notification-badge"><?php echo count($myRequests); ?></span>
                    <?php endif; ?>
                </a>
                <a href="favorites.php" class="menu-item">
                    <i class="fas fa-heart"></i>
                    <span>Favorite Farmers</span>
                </a>
                <a href="messages.php" class="menu-item">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <span class="notification-badge">3</span>
                </a>
                <a href="settings.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <?php if ($_SESSION['user_type'] === 'both'): ?>
                    <a href="../farmers/dashboard.php" class="menu-item">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Switch to Farmer</span>
                    </a>
                <?php endif; ?>
            </div>
            <div class="sidebar-footer">
                <p>&copy; <?php echo date('Y'); ?> AgriMatch</p>
            </div>
        </div>

        <!-- Overlay for mobile menu -->
        <div class="overlay" id="overlay"></div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="header-title">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Buyer Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                </div>
                <div class="user-profile">
                    <img src="../assets/default-profile.jpg" alt="Profile Picture">
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($_SESSION['user_name']); ?></h4>
                        <p><?php echo htmlspecialchars($buyerLocation); ?></p>
                    </div>
                    <button class="logout-btn" onclick="window.location.href='../logout.php'">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Available Listings</h3>
                        <div class="card-icon primary">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo count($listings); ?></div>
                    <p class="card-description">Produce listings near you</p>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Active Requests</h3>
                        <div class="card-icon secondary">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                    <div class="card-value"><?php echo count($myRequests); ?></div>
                    <p class="card-description">Your purchase requests</p>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Favorite Farmers</h3>
                        <div class="card-icon accent">
                            <i class="fas fa-heart"></i>
                        </div>
                    </div>
                    <div class="card-value">8</div>
                    <p class="card-description">Trusted farmers you follow</p>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Unread Messages</h3>
                        <div class="card-icon info">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <div class="card-value">3</div>
                    <p class="card-description">New messages from farmers</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <h2><i class="fas fa-filter"></i> Filter Listings</h2>
                <form method="GET" action="" class="filter-form">
                    <div class="form-group">
                        <label for="county">County</label>
                        <select name="county" id="county" class="form-control">
                            <option value="">All Counties</option>
                            <?php foreach ($counties as $county): ?>
                                <option value="<?php echo htmlspecialchars($county); ?>"
                                    <?php echo ($countyFilter === $county ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($county); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="crop">Crop Type</label>
                        <select name="crop" id="crop" class="form-control">
                            <option value="">All Crops</option>
                            <?php foreach ($crops as $crop): ?>
                                <option value="<?php echo htmlspecialchars($crop); ?>"
                                    <?php echo ($cropFilter === $crop ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($crop); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <a href="dashboard.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </form>
            </div>

            <!-- Available Produce Section -->
            <div class="table-section">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-shopping-basket"></i> Available Produce</h2>
                    <div>
                        <?php if ($_SESSION['user_type'] === 'both'): ?>
                            <a href="../farmers/dashboard.php" class="switch-view-btn">
                                <i class="fas fa-exchange-alt"></i> Switch to Farmer View
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($listings): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Farmer</th>
                                    <th>Crop</th>
                                    <th>Quantity</th>
                                    <th>Price (Ksh)</th>
                                    <th>Location</th>
                                    <th>Posted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listings as $listing): ?>
                                    <tr>
                                        <td>
                                            <div class="user-info-cell">
                                                <img src="<?php echo !empty($listing['farmer_photo']) ? '../uploads/' . htmlspecialchars($listing['farmer_photo']) : '../assets/default-profile.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($listing['farmer_name']); ?>" 
                                                     class="user-avatar">
                                                <div>
                                                    <div class="user-name"><?php echo htmlspecialchars($listing['farmer_name']); ?></div>
                                                    <div class="user-location"><?php echo htmlspecialchars($listing['location']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($listing['crop_name']); ?></td>
                                        <td><?php echo htmlspecialchars($listing['quantity']); ?> kg</td>
                                        <td><?php echo number_format($listing['price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($listing['location']); ?></td>
                                        <td><?php echo date('M j', strtotime($listing['created_at'])); ?></td>
                                        <td>
                                            <div class="action-links">
                                                <a href="tel:<?php echo htmlspecialchars($listing['farmer_phone']); ?>" 
                                                   class="action-link call" 
                                                   title="Call Farmer">
                                                    <i class="fas fa-phone"></i> Call
                                                </a>
                                                <a href="make_offer.php?listing_id=<?php echo $listing['id']; ?>" 
                                                   class="action-link offer" 
                                                   title="Make Offer">
                                                    <i class="fas fa-handshake"></i> Offer
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-basket"></i>
                        <h3>No Produce Listings Found</h3>
                        <p>There are currently no produce listings matching your filters.</p>
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i> Reset Filters
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Your Purchase Requests Section -->
            <div class="table-section">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-clipboard-list"></i> Your Purchase Requests</h2>
                </div>
                
                <?php if ($myRequests): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Crop</th>
                                    <th>Farmer</th>
                                    <th>Quantity</th>
                                    <th>Your Offer</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myRequests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['crop_name']); ?></td>
                                        <td>
                                            <div class="user-info-cell">
                                                <img src="<?php echo !empty($request['farmer_photo']) ? '../uploads/' . htmlspecialchars($request['farmer_photo']) : '../assets/default-profile.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($request['farmer_name']); ?>" 
                                                     class="user-avatar">
                                                <div class="user-name"><?php echo htmlspecialchars($request['farmer_name']); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['quantity']); ?> kg</td>
                                        <td>Ksh <?php echo number_format($request['offer_price'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $request['status']; ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j', strtotime($request['created_at'])); ?></td>
                                        <td>
                                            <div class="action-links">
                                                <a href="request_details.php?id=<?php echo $request['id']; ?>" 
                                                   class="action-link view" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i> Details
                                                </a>
                                                <?php if ($request['status'] == 'pending'): ?>
                                                    <a href="cancel_request.php?id=<?php echo $request['id']; ?>" 
                                                       class="action-link cancel" 
                                                       title="Cancel Request">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </a>
                                                <?php elseif ($request['status'] == 'accepted'): ?>
                                                    <a href="confirm_order.php?id=<?php echo $request['id']; ?>" 
                                                       class="action-link confirm" 
                                                       title="Confirm Delivery">
                                                        <i class="fas fa-check"></i> Confirm
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>No Purchase Requests Yet</h3>
                        <p>You haven't made any purchase requests yet. Browse available produce to make your first request.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Market Trends Section -->
            <div class="market-trends">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-chart-line"></i> Current Market Trends</h2>
                </div>
                
                <div class="trend-item">
                    <span class="trend-crop">Maize</span>
                    <div>
                        <span class="trend-price">Ksh 2,500/bag</span>
                        <span class="trend-change trend-up">+5%</span>
                    </div>
                </div>
                <div class="trend-item">
                    <span class="trend-crop">Beans</span>
                    <div>
                        <span class="trend-price">Ksh 3,200/bag</span>
                        <span class="trend-change trend-down">-2%</span>
                    </div>
                </div>
                <div class="trend-item">
                    <span class="trend-crop">Tomatoes</span>
                    <div>
                        <span class="trend-price">Ksh 80/kg</span>
                        <span class="trend-change trend-up">+12%</span>
                    </div>
                </div>
                <div class="trend-item">
                    <span class="trend-crop">Potatoes</span>
                    <div>
                        <span class="trend-price">Ksh 50/kg</span>
                        <span class="trend-change trend-neutral">0%</span>
                    </div>
                </div>
                <div class="trend-item">
                    <span class="trend-crop">Avocado</span>
                    <div>
                        <span class="trend-price">Ksh 30/piece</span>
                        <span class="trend-change trend-up">+8%</span>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 15px;">
                    <a href="market_prices.php" class="btn btn-accent">
                        <i class="fas fa-chart-bar"></i> View Full Market Report
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> AgriMatch - Market Match Platform. All rights reserved.</p>
                <p>Helping small-scale farmers connect with buyers since 2023</p>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });

        // Responsive adjustments
        function handleResize() {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        }

        window.addEventListener('resize', handleResize);

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltips = document.querySelectorAll('.tooltip');
            tooltips.forEach(tooltip => {
                tooltip.addEventListener('mouseenter', function() {
                    const tooltipText = this.querySelector('.tooltiptext');
                    tooltipText.style.visibility = 'visible';
                    tooltipText.style.opacity = '1';
                });
                tooltip.addEventListener('mouseleave', function() {
                    const tooltipText = this.querySelector('.tooltiptext');
                    tooltipText.style.visibility = 'hidden';
                    tooltipText.style.opacity = '0';
                });
            });
        });

        // Simulate loading for demo purposes
        function simulateLoading(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="loading-spinner"></span> Processing...';
            button.disabled = true;
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 1500);
        }

        // Add click event to all buttons with loading class
        document.querySelectorAll('.btn-loading').forEach(button => {
            button.addEventListener('click', function() {
                simulateLoading(this);
            });
        });
    </script>
</body>
</html>


