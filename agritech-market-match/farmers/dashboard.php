<?php
session_start();
include('../includes/db_connect.php');


if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'farmer' && $_SESSION['user_type'] !== 'both')) {
    header("Location: ../index.php");
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM produce_listings WHERE farmer_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$listings = $stmt->fetchAll();


$prices = $pdo->query("SELECT * FROM market_prices ORDER BY updated_at DESC LIMIT 5")->fetchAll();


$requests = $pdo->prepare("
    SELECT r.*, u.name AS buyer_name, u.phone AS buyer_phone
    FROM purchase_requests r
    JOIN users u ON r.buyer_id = u.id
    WHERE r.listing_id IN (
        SELECT id FROM produce_listings WHERE farmer_id = ?
    )
    ORDER BY r.created_at DESC
");
$requests->execute([$_SESSION['user_id']]);
$buyerRequests = $requests->fetchAll();


$completed = $pdo->prepare("
    SELECT r.*, u.name AS buyer_name
    FROM purchase_requests r
    JOIN users u ON r.buyer_id = u.id
    WHERE r.listing_id IN (
        SELECT id FROM produce_listings WHERE farmer_id = ?
    ) AND r.status = 'completed'
");
$completed->execute([$_SESSION['user_id']]);
$completedOrders = $completed->fetchAll();


$totalListings = count($listings);
$activeRequests = 0;
$completedSales = 0;
$totalRevenue = 0;

foreach ($buyerRequests as $request) {
    if ($request['status'] === 'pending' || $request['status'] === 'accepted') {
        $activeRequests++;
    }
}

foreach ($completedOrders as $order) {
    $completedSales++;
    $totalRevenue += $order['offer_price'] * $order['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard | Agritech Market Match</title>
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
            background-color: var(--gray-light);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 15px;
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

        .user-name {
            font-weight: 500;
        }

        .user-location {
            font-size: 0.8rem;
            color: var(--text-medium);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .user-location i {
            font-size: 0.9rem;
        }

        /* Main Layout */
        .main-layout {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--white);
            box-shadow: var(--shadow-sm);
            padding: 20px 0;
            transition: var(--transition);
        }

        .sidebar-menu {
            padding: 0 15px;
        }

        .menu-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            color: var(--text-light);
            margin: 20px 0 10px;
            padding-left: 10px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: var(--border-radius-sm);
            margin-bottom: 5px;
            transition: var(--transition);
            font-weight: 500;
            color: var(--text-medium);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        .menu-item:hover {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        .menu-item.active {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .menu-item.active i {
            color: var(--white);
        }

        .menu-badge {
            margin-left: auto;
            background-color: var(--secondary-color);
            color: var(--white);
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 25px;
            background-color: var(--gray-light);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-title {
            font-size: 1.8rem;
            color: var(--primary-dark);
            font-weight: 600;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-family: inherit;
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

        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--white);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-dark);
            box-shadow: var(--shadow-sm);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.primary {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        .stat-icon.secondary {
            background-color: rgba(255, 152, 0, 0.2);
            color: var(--secondary-dark);
        }

        .stat-icon.accent {
            background-color: rgba(139, 195, 74, 0.2);
            color: var(--accent-color);
        }

        .stat-title {
            font-size: 0.9rem;
            color: var(--text-medium);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-change.positive {
            color: var(--primary-dark);
        }

        .stat-change.negative {
            color: #f44336;
        }

        /* Section Styles */
        .section {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 25px;
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray-medium);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .section-actions {
            display: flex;
            gap: 10px;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 500;
            text-align: left;
            padding: 12px 15px;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--gray-medium);
            color: var(--text-medium);
        }

        tr:hover td {
            background-color: var(--light-color);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #FFF3E0;
            color: #E65100;
        }

        .status-accepted {
            background-color: #E8F5E9;
            color: #2E7D32;
        }

        .status-rejected {
            background-color: #FFEBEE;
            color: #C62828;
        }

        .status-completed {
            background-color: #E3F2FD;
            color: #1565C0;
        }

        .action-links {
            display: flex;
            gap: 10px;
        }

        .action-link {
            padding: 5px 10px;
            border-radius: var(--border-radius-sm);
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .action-link.view {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        .action-link.view:hover {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .action-link.edit {
            background-color: #E3F2FD;
            color: #1976D2;
        }

        .action-link.edit:hover {
            background-color: #1976D2;
            color: var(--white);
        }

        .action-link.delete {
            background-color: #FFEBEE;
            color: #D32F2F;
        }

        .action-link.delete:hover {
            background-color: #D32F2F;
            color: var(--white);
        }

        .action-link.accept {
            background-color: #E8F5E9;
            color: #2E7D32;
        }

        .action-link.accept:hover {
            background-color: #2E7D32;
            color: var(--white);
        }

        .action-link.reject {
            background-color: #FFF3E0;
            color: #E65100;
        }

        .action-link.reject:hover {
            background-color: #E65100;
            color: var(--white);
        }

        .action-link.contact {
            background-color: #E0F7FA;
            color: #00838F;
        }

        .action-link.contact:hover {
            background-color: #00838F;
            color: var(--white);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--gray-medium);
            margin-bottom: 15px;
        }

        .empty-state p {
            margin-bottom: 20px;
        }

        /* Market Prices Widget */
        .market-prices {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .price-card {
            background-color: var(--white);
            border-radius: var(--border-radius-sm);
            padding: 15px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--accent-color);
            transition: var(--transition);
        }

        .price-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .price-crop {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--primary-dark);
        }

        .price-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--secondary-dark);
            margin-bottom: 5px;
        }

        .price-market {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .price-updated {
            font-size: 0.7rem;
            color: var(--text-light);
        }

        /* Footer */
        .footer {
            background-color: var(--white);
            padding: 20px 0;
            text-align: center;
            color: var(--text-medium);
            font-size: 0.9rem;
            border-top: 1px solid var(--gray-medium);
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .main-layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 10px 0;
            }
            
            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .menu-group {
                display: flex;
                gap: 5px;
            }
            
            .menu-title {
                display: none;
            }
            
            .menu-item {
                white-space: nowrap;
                margin-bottom: 0;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .section-actions {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .header-container {
                flex-direction: column;
                gap: 15px;
                padding: 15px 0;
            }
            
            .user-profile {
                flex-direction: column;
                text-align: center;
            }
            
            .action-links {
                flex-direction: column;
                gap: 5px;
            }
            
            .action-link {
                text-align: center;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }

        /* Additional UI Elements */
        .progress-bar {
            height: 6px;
            background-color: var(--gray-medium);
            border-radius: 3px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 3px;
            transition: width 0.5s ease;
        }

        .tag {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 500;
            background-color: var(--gray-medium);
            color: var(--text-medium);
        }

        .tag.organic {
            background-color: #E8F5E9;
            color: #2E7D32;
        }

        .tag.premium {
            background-color: #FFF3E0;
            color: #E65100;
        }

        .divider {
            height: 1px;
            background-color: var(--gray-medium);
            margin: 20px 0;
        }

        /* Tooltip */
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltip-text {
            visibility: hidden;
            width: 120px;
            background-color: var(--text-dark);
            color: var(--white);
            text-align: center;
            border-radius: var(--border-radius-sm);
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.8rem;
        }

        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        /* Modal (hidden by default) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: var(--white);
            margin: 10% auto;
            padding: 25px;
            border-radius: var(--border-radius);
            width: 80%;
            max-width: 600px;
            box-shadow: var(--shadow-lg);
            animation: fadeIn 0.3s;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray-medium);
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-dark);
        }

        .close-modal {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-light);
            cursor: pointer;
            transition: var(--transition);
        }

        .close-modal:hover {
            color: var(--text-dark);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-medium);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--gray-medium);
            border-radius: var(--border-radius-sm);
            font-family: inherit;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }

        /* Notification Bell */
        .notification-bell {
            position: relative;
            cursor: pointer;
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #F44336;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Weather Widget */
        .weather-widget {
            background: linear-gradient(135deg, #64B5F6, #1976D2);
            color: white;
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .weather-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .weather-icon {
            font-size: 2.5rem;
        }

        .weather-temp {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .weather-details {
            font-size: 0.9rem;
        }

        .weather-location {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* Responsive Table */
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--gray-medium);
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            font-weight: 500;
            color: var(--text-medium);
            transition: var(--transition);
        }

        .tab.active {
            color: var(--primary-dark);
            border-bottom-color: var(--primary-color);
        }

        .tab:hover:not(.active) {
            color: var(--primary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Calendar Widget */
        .calendar-widget {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            padding: 15px;
            margin-bottom: 20px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .calendar-title {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .calendar-nav {
            display: flex;
            gap: 10px;
        }

        .calendar-nav-btn {
            background-color: var(--gray-light);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .calendar-nav-btn:hover {
            background-color: var(--gray-medium);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-day-header {
            font-size: 0.8rem;
            text-align: center;
            color: var(--text-light);
            font-weight: 500;
            padding: 5px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            cursor: pointer;
            border-radius: 50%;
            transition: var(--transition);
        }

        .calendar-day:hover {
            background-color: var(--gray-light);
        }

        .calendar-day.today {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }

        .calendar-day.other-month {
            color: var(--gray-medium);
        }

        .calendar-day.event {
            position: relative;
        }

        .calendar-day.event::after {
            content: '';
            position: absolute;
            bottom: 3px;
            left: 50%;
            transform: translateX(-50%);
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background-color: var(--secondary-color);
        }

        /* Market Trends Chart Placeholder */
        .chart-placeholder {
            background-color: var(--white);
            border-radius: var(--border-radius);
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .quick-action {
            background-color: var(--white);
            border-radius: var(--border-radius-sm);
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            cursor: pointer;
        }

        .quick-action:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .quick-action i {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .quick-action-title {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .quick-actions {
                grid-template-columns: 1fr 1fr;
            }
            
            .market-prices {
                grid-template-columns: 1fr;
            }
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
                <div class="notification-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">3</span>
                </div>
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="user-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($_SESSION['user_location']); ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="main-layout">
        <aside class="sidebar">
            <nav class="sidebar-menu">
                <div class="menu-group">
                    <a href="dashboard.php" class="menu-item active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="post_produce.php" class="menu-item">
                        <i class="fas fa-plus-circle"></i>
                        <span>Sell Produce</span>
                    </a>
                    <a href="listings.php" class="menu-item">
                        <i class="fas fa-list-ul"></i>
                        <span>My Listings</span>
                        <?php if ($totalListings > 0): ?>
                            <span class="menu-badge"><?php echo $totalListings; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <div class="menu-title">Market</div>
                <div class="menu-group">
                    <a href="market_prices.php" class="menu-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Market Prices</span>
                    </a>
                    <a href="buyers.php" class="menu-item">
                        <i class="fas fa-users"></i>
                        <span>Buyers</span>
                    </a>
                    <a href="trends.php" class="menu-item">
                        <i class="fas fa-chart-pie"></i>
                        <span>Market Trends</span>
                    </a>
                </div>
                
                <div class="menu-title">Transactions</div>
                <div class="menu-group">
                    <a href="requests.php" class="menu-item">
                        <i class="fas fa-handshake"></i>
                        <span>Buyer Requests</span>
                        <?php if ($activeRequests > 0): ?>
                            <span class="menu-badge"><?php echo $activeRequests; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="orders.php" class="menu-item">
                        <i class="fas fa-shopping-basket"></i>
                        <span>Orders</span>
                    </a>
                    <a href="payments.php" class="menu-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Payments</span>
                    </a>
                </div>
                
                <div class="menu-title">Account</div>
                <div class="menu-group">
                    <a href="profile.php" class="menu-item">
                        <i class="fas fa-user-cog"></i>
                        <span>Profile</span>
                    </a>
                    <a href="settings.php" class="menu-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="../logout.php" class="menu-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title">Farmer Dashboard</h1>
                <div class="btn-group">
                    <a href="post_produce.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Sell Produce
                    </a>
                    <?php if ($_SESSION['user_type'] === 'both'): ?>
                        <a href="../buyers/dashboard.php" class="btn btn-outline">
                            <i class="fas fa-exchange-alt"></i> Switch to Buyer
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Active Listings</div>
                            <div class="stat-value"><?php echo $totalListings; ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> 12% from last month
                            </div>
                        </div>
                        <div class="stat-icon primary">
                            <i class="fas fa-leaf"></i>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min($totalListings * 10, 100); ?>%"></div>
                    </div>
                </div>

                <div class="stat-card fade-in delay-1">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Active Requests</div>
                            <div class="stat-value"><?php echo $activeRequests; ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> 5 new today
                            </div>
                        </div>
                        <div class="stat-icon secondary">
                            <i class="fas fa-handshake"></i>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min($activeRequests * 20, 100); ?>%"></div>
                    </div>
                </div>

                <div class="stat-card fade-in delay-2">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Completed Sales</div>
                            <div class="stat-value"><?php echo $completedSales; ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> 3 this week
                            </div>
                        </div>
                        <div class="stat-icon accent">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min($completedSales * 15, 100); ?>%"></div>
                    </div>
                </div>

                <div class="stat-card fade-in delay-3">
                    <div class="stat-header">
                        <div>
                            <div class="stat-title">Total Revenue</div>
                            <div class="stat-value">Ksh <?php echo number_format($totalRevenue); ?></div>
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i> 18% from last month
                            </div>
                        </div>
                        <div class="stat-icon secondary">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(($totalRevenue / 10000) * 10, 100); ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Weather and Quick Actions Row -->
            <div class="grid-row" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Weather Widget -->
                <div class="weather-widget fade-in">
                    <div class="weather-info">
                        <div class="weather-icon">
                            <i class="fas fa-sun"></i>
                        </div>
                        <div>
                            <div class="weather-temp">24°C</div>
                            <div class="weather-details">Sunny, Humidity 65%</div>
                            <div class="weather-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($_SESSION['user_location']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="weather-forecast">
                        <div style="text-align: right;">
                            <div style="font-size: 0.9rem; margin-bottom: 5px;">Next 3 Days</div>
                            <div style="display: flex; gap: 15px; justify-content: flex-end;">
                                <div style="text-align: center;">
                                    <div style="font-size: 0.8rem;">Tue</div>
                                    <i class="fas fa-cloud-sun" style="font-size: 1.2rem; margin: 5px 0;"></i>
                                    <div style="font-size: 0.9rem; font-weight: 500;">26°C</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 0.8rem;">Wed</div>
                                    <i class="fas fa-cloud-rain" style="font-size: 1.2rem; margin: 5px 0;"></i>
                                    <div style="font-size: 0.9rem; font-weight: 500;">22°C</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 0.8rem;">Thu</div>
                                    <i class="fas fa-cloud" style="font-size: 1.2rem; margin: 5px 0;"></i>
                                    <div style="font-size: 0.9rem; font-weight: 500;">24°C</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <div class="quick-action fade-in">
                        <i class="fas fa-calendar-alt"></i>
                        <div class="quick-action-title">Market Days</div>
                    </div>
                    <div class="quick-action fade-in delay-1">
                        <i class="fas fa-book"></i>
                        <div class="quick-action-title">Farming Tips</div>
                    </div>
                    <div class="quick-action fade-in delay-2">
                        <i class="fas fa-truck"></i>
                        <div class="quick-action-title">Logistics</div>
                    </div>
                </div>
            </div>

            <!-- Your Produce Listings Section -->
            <section class="section fade-in">
                <div class="section-header">
                    <h2 class="section-title">Your Produce Listings</h2>
                    <div class="section-actions">
                        <a href="post_produce.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Listing
                        </a>
                        <a href="listings.php" class="btn btn-outline">
                            <i class="fas fa-list"></i> View All
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <?php if ($listings): ?>

<table class="table">
    <thead>
        <tr>
            <th>Crop</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Location</th>
            <th>Status</th>
            <th>Date Posted</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($listings as $listing): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($listing['crop_name']); ?></strong>
                    <?php if (isset($listing['organic']) && $listing['organic']): ?>
                        <span class="tag organic">Organic</span>
                    <?php endif; ?>
                    <?php if (isset($listing['premium']) && $listing['premium']): ?>
                        <span class="tag premium">Premium</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($listing['quantity']); ?> kg</td>
                <td><strong>Ksh <?php echo htmlspecialchars($listing['price']); ?></strong> /kg</td>
                <td><?php echo htmlspecialchars($listing['location']); ?></td>
                <td>
                    <?php if (isset($listing['status'])): ?>
                        <span class="status-badge <?php 
                            echo $listing['status'] === 'available' ? 'status-accepted' : 
                                 ($listing['status'] === 'sold' ? 'status-completed' : 'status-pending'); 
                        ?>">
                            <?php echo ucfirst($listing['status']); ?>
                        </span>
                    <?php else: ?>
                        <span class="status-badge status-pending">Available</span>
                    <?php endif; ?>
                </td>
                <td><?php echo date('M j, Y', strtotime($listing['created_at'])); ?></td>
                <td>
                    <div class="action-links">
                        <a href="edit_listing.php?id=<?php echo $listing['id']; ?>" class="action-link edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_listing.php?id=<?php echo $listing['id']; ?>" 
                           class="action-link delete"
                           onclick="return confirm('Are you sure you want to delete this listing?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-leaf"></i>
                            <p>You haven't listed any produce yet.</p>
                            <a href="post_produce.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Your First Listing
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Buyer Requests Section -->
            <section class="section fade-in delay-1">
                <div class="section-header">
                    <h2 class="section-title">Buyer Requests</h2>
                    <div class="section-actions">
                        <a href="requests.php" class="btn btn-outline">
                            <i class="fas fa-list"></i> View All
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <?php if ($buyerRequests): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Buyer</th>
                                    <th>Crop</th>
                                    <th>Requested Qty</th>
                                    <th>Offer Price</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($buyerRequests as $request): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['buyer_name']); ?></strong>
                                            <div class="text-small"><?php echo htmlspecialchars($request['buyer_phone']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['crop_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['quantity']); ?> kg</td>
                                        <td><strong>Ksh <?php echo htmlspecialchars($request['offer_price']); ?></strong> /kg</td>
                                        <td><?php echo date('M j', strtotime($request['created_at'])); ?></td>
                                        <td>
                                            <span class="status-badge <?php 
                                                echo $request['status'] === 'pending' ? 'status-pending' : 
                                                     ($request['status'] === 'accepted' ? 'status-accepted' : 
                                                     ($request['status'] === 'rejected' ? 'status-rejected' : 'status-completed')); 
                                            ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-links">
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <a href="accept_request.php?id=<?php echo $request['id']; ?>" class="action-link accept">
                                                        <i class="fas fa-check"></i> Accept
                                                    </a>
                                                    <a href="reject_request.php?id=<?php echo $request['id']; ?>" class="action-link reject">
                                                        <i class="fas fa-times"></i> Reject
                                                    </a>
                                                <?php endif; ?>
                                                <a href="tel:<?php echo htmlspecialchars($request['buyer_phone']); ?>" class="action-link contact">
                                                    <i class="fas fa-phone"></i> Contact
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-handshake"></i>
                            <p>No buyer requests yet. Your listings will appear to buyers soon!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Completed Orders Section -->
            <section class="section fade-in delay-2">
                <div class="section-header">
                    <h2 class="section-title">Completed Orders</h2>
                    <div class="section-actions">
                        <a href="orders.php" class="btn btn-outline">
                            <i class="fas fa-list"></i> View All
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <?php if ($completedOrders): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Crop</th>
                                    <th>Buyer</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
                                    <th>Completed On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($completedOrders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['crop_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['quantity']); ?> kg</td>
                                        <td><strong>Ksh <?php echo number_format($order['offer_price'] * $order['quantity']); ?></strong></td>
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div class="action-links">
                                                <a href="#" class="action-link view">
                                                    <i class="fas fa-eye"></i> Details
                                                </a>
                                                <a href="tel:<?php echo htmlspecialchars($order['buyer_phone'] ?? ''); ?>" class="action-link contact">
                                                    <i class="fas fa-phone"></i> Contact
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>No completed orders yet. When you complete transactions, they'll appear here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Market Prices and Calendar Row -->
            <div class="grid-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Market Prices Section -->
                <section class="section fade-in">
                    <div class="section-header">
                        <h2 class="section-title">Latest Market Prices</h2>
                        <div class="section-actions">
                            <a href="market_prices.php" class="btn btn-outline">
                                <i class="fas fa-chart-line"></i> View Trends
                            </a>
                        </div>
                    </div>

                    <div class="market-prices">
                        <?php foreach ($prices as $price): ?>
                            <div class="price-card">
                                <div class="price-crop"><?php echo htmlspecialchars($price['crop_name']); ?></div>
                                <div class="price-value">Ksh <?php echo htmlspecialchars($price['price']); ?></div>
                                <div class="price-market"><?php echo htmlspecialchars($price['market_location']); ?></div>
                                <div class="price-updated">Updated <?php echo date('M j, H:i', strtotime($price['updated_at'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Calendar Widget -->
                <section class="section fade-in delay-1">
                    <div class="section-header">
                        <h2 class="section-title">Market Calendar</h2>
                        <div class="section-actions">
                            <a href="#" class="btn btn-outline">
                                <i class="fas fa-calendar-plus"></i> Add Event
                            </a>
                        </div>
                    </div>

                    <div class="calendar-widget">
                        <div class="calendar-header">
                            <div class="calendar-title">June 2023</div>
                            <div class="calendar-nav">
                                <button class="calendar-nav-btn">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="calendar-nav-btn">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <div class="calendar-grid">
                            <div class="calendar-day-header">Sun</div>
                            <div class="calendar-day-header">Mon</div>
                            <div class="calendar-day-header">Tue</div>
                            <div class="calendar-day-header">Wed</div>
                            <div class="calendar-day-header">Thu</div>
                            <div class="calendar-day-header">Fri</div>
                            <div class="calendar-day-header">Sat</div>

                            <div class="calendar-day other-month">28</div>
                            <div class="calendar-day other-month">29</div>
                            <div class="calendar-day other-month">30</div>
                            <div class="calendar-day other-month">31</div>
                            <div class="calendar-day">1</div>
                            <div class="calendar-day">2</div>
                            <div class="calendar-day">3</div>

                            <div class="calendar-day">4</div>
                            <div class="calendar-day">5</div>
                            <div class="calendar-day event">6</div>
                            <div class="calendar-day">7</div>
                            <div class="calendar-day">8</div>
                            <div class="calendar-day">9</div>
                            <div class="calendar-day">10</div>

                            <div class="calendar-day">11</div>
                            <div class="calendar-day">12</div>
                            <div class="calendar-day event">13</div>
                            <div class="calendar-day">14</div>
                            <div class="calendar-day">15</div>
                            <div class="calendar-day today">16</div>
                            <div class="calendar-day">17</div>

                            <div class="calendar-day">18</div>
                            <div class="calendar-day">19</div>
                            <div class="calendar-day event">20</div>
                            <div class="calendar-day">21</div>
                            <div class="calendar-day">22</div>
                            <div class="calendar-day">23</div>
                            <div class="calendar-day">24</div>

                            <div class="calendar-day">25</div>
                            <div class="calendar-day">26</div>
                            <div class="calendar-day">27</div>
                            <div class="calendar-day">28</div>
                            <div class="calendar-day">29</div>
                            <div class="calendar-day">30</div>
                            <div class="calendar-day other-month">1</div>
                        </div>
                    </div>
                </section>
            </div>

<!-- Market Trends Section -->
<section class="section fade-in delay-2">
    <div class="section-header">
        <h2 class="section-title"><i class="fas fa-chart-line"></i> Market Trends</h2>
        <div class="section-actions">
            <select id="trendPeriod" class="form-control" style="width: auto; display: inline-block;">
                <option value="7">Last 7 Days</option>
                <option value="30" selected>Last 30 Days</option>
                <option value="90">Last 3 Months</option>
            </select>
        </div>
    </div>

    <div class="chart-container" style="position: relative; height:300px; width:100%">
        <canvas id="marketTrendsChart"></canvas>
    </div>
    
    <div class="trend-summary">
        <div class="trend-card">
            <div class="trend-icon" style="background-color: rgba(75, 192, 192, 0.2);">
                <i class="fas fa-seedling" style="color: #4bc0c0;"></i>
            </div>
            <div>
                <h4>Maize</h4>
                <p class="trend-value">Ksh 2,450 <span class="trend-up">↑ 5.2%</span></p>
            </div>
        </div>
        <div class="trend-card">
            <div class="trend-icon" style="background-color: rgba(255, 99, 132, 0.2);">
                <i class="fas fa-apple-alt" style="color: #ff6384;"></i>
            </div>
            <div>
                <h4>Tomatoes</h4>
                <p class="trend-value">Ksh 180 <span class="trend-down">↓ 2.1%</span></p>
            </div>
        </div>
        <div class="trend-card">
            <div class="trend-icon" style="background-color: rgba(54, 162, 235, 0.2);">
                <i class="fas fa-coffee" style="color: #36a2eb;"></i>
            </div>
            <div>
                <h4>Coffee</h4>
                <p class="trend-value">Ksh 3,150 <span class="trend-up">↑ 8.7%</span></p>
            </div>
        </div>
    </div>
</section>

<!-- Add this to your CSS -->
<style>
    .chart-container {
        background: white;
        border-radius: var(--border-radius);
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: var(--box-shadow);
    }
    
    .trend-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .trend-card {
        background: white;
        border-radius: var(--border-radius);
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: var(--box-shadow);
    }
    
    .trend-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .trend-value {
        font-weight: bold;
        margin-top: 5px;
    }
    
    .trend-up {
        color: var(--success-color);
        font-size: 0.9rem;
    }
    
    .trend-down {
        color: var(--danger-color);
        font-size: 0.9rem;
    }
</style>

<!-- Add these scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chart with sample data (replace with real API call)
    const ctx = document.getElementById('marketTrendsChart').getContext('2d');
    const marketChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: generateDateLabels(30),
            datasets: [
                {
                    label: 'Maize (Ksh/bag)',
                    data: generateRandomData(30, 2200, 2500),
                    borderColor: '#4bc0c0',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Tomatoes (Ksh/kg)',
                    data: generateRandomData(30, 150, 200),
                    borderColor: '#ff6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Coffee (Ksh/kg)',
                    data: generateRandomData(30, 2800, 3200),
                    borderColor: '#36a2eb',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false,
                },
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return 'Ksh ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Period selector functionality
    document.getElementById('trendPeriod').addEventListener('change', function() {
        const days = parseInt(this.value);
        marketChart.data.labels = generateDateLabels(days);
        marketChart.data.datasets.forEach(dataset => {
            dataset.data = generateRandomData(days, 
                dataset.label.includes('Maize') ? 2200 : 
                dataset.label.includes('Tomatoes') ? 150 : 2800,
                dataset.label.includes('Maize') ? 2500 : 
                dataset.label.includes('Tomatoes') ? 200 : 3200
            );
        });
        marketChart.update();
    });

    // Helper functions
    function generateDateLabels(days) {
        const labels = [];
        for (let i = days; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        }
        return labels;
    }

    function generateRandomData(count, min, max) {
        const data = [];
        let lastValue = (min + max) / 2;
        for (let i = 0; i < count; i++) {
            const change = (Math.random() - 0.5) * (max - min) * 0.1;
            lastValue = Math.min(max, Math.max(min, lastValue + change));
            data.push(Math.round(lastValue));
        }
        return data;
    }

    // use real API data 
    /*
    async function fetchMarketData() {
        try {
            const response = await fetch('https://api.fao.org/prices?commodity=maize,tomatoes,coffee&country=kenya');
            const data = await response.json();
            // Process API data and update chart
            marketChart.data.datasets[0].data = data.maize_prices;
            marketChart.data.datasets[1].data = data.tomato_prices;
            marketChart.data.datasets[2].data = data.coffee_prices;
            marketChart.update();
        } catch (error) {
            console.error('Error fetching market data:', error);
        }
    }
    fetchMarketData();
    */
});
</script>
        </main>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Agritech Market Match Platform. All rights reserved.</p>
            <p>Helping farmers connect with buyers since 2023</p>
        </div>
    </footer>

    <!-- Modal for Notification Details -->
    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Notifications</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div style="padding: 15px; border-bottom: 1px solid var(--gray-medium);">
                    <div style="font-weight: 500; margin-bottom: 5px;">New buyer request for your maize</div>
                    <div style="font-size: 0.9rem; color: var(--text-light);">2 hours ago</div>
                </div>
                <div style="padding: 15px; border-bottom: 1px solid var(--gray-medium);">
                    <div style="font-weight: 500; margin-bottom: 5px;">Market price update: Tomatoes increased by 12%</div>
                    <div style="font-size: 0.9rem; color: var(--text-light);">5 hours ago</div>
                </div>
                <div style="padding: 15px;">
                    <div style="font-weight: 500; margin-bottom: 5px;">Your listing for avocados was featured</div>
                    <div style="font-size: 0.9rem; color: var(--text-light);">1 day ago</div>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-outline">Mark All as Read</button>
                <button class="btn btn-primary">View All Notifications</button>
            </div>
        </div>
    </div>

    <script>
        // Notification bell click handler
        document.querySelector('.notification-bell').addEventListener('click', function() {
            document.getElementById('notificationModal').style.display = 'block';
        });

        // Close modal when X is clicked
        document.querySelector('.close-modal').addEventListener('click', function() {
            document.getElementById('notificationModal').style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('notificationModal')) {
                document.getElementById('notificationModal').style.display = 'none';
            }
        });

        // Simple animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-in');
            
            const fadeInOnScroll = function() {
                fadeElements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;
                    
                    if (elementTop < windowHeight - 100) {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }
                });
            };
            
            // Set initial state
            fadeElements.forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            });
            
            // Check on load
            fadeInOnScroll();
            
            // Check on scroll
            window.addEventListener('scroll', fadeInOnScroll);
        });

        // Responsive menu toggle for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.createElement('div');
            menuToggle.className = 'menu-toggle';
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            menuToggle.style.display = 'none';
            menuToggle.style.position = 'fixed';
            menuToggle.style.bottom = '20px';
            menuToggle.style.right = '20px';
            menuToggle.style.backgroundColor = 'var(--primary-color)';
            menuToggle.style.color = 'white';
            menuToggle.style.width = '50px';
            menuToggle.style.height = '50px';
            menuToggle.style.borderRadius = '50%';
            menuToggle.style.display = 'flex';
            menuToggle.style.alignItems = 'center';
            menuToggle.style.justifyContent = 'center';
            menuToggle.style.fontSize = '1.2rem';
            menuToggle.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
            menuToggle.style.zIndex = '90';
            menuToggle.style.cursor = 'pointer';
            document.body.appendChild(menuToggle);

            const sidebar = document.querySelector('.sidebar');
            
            menuToggle.addEventListener('click', function() {
                sidebar.style.transform = sidebar.style.transform === 'translateX(-100%)' ? 
                    'translateX(0)' : 'translateX(-100%)';
            });

            function checkScreenSize() {
                if (window.innerWidth <= 992) {
                    sidebar.style.position = 'fixed';
                    sidebar.style.top = '0';
                    sidebar.style.left = '0';
                    sidebar.style.height = '100vh';
                    sidebar.style.zIndex = '100';
                    sidebar.style.transform = 'translateX(-100%)';
                    sidebar.style.transition = 'transform 0.3s ease';
                    menuToggle.style.display = 'flex';
                } else {
                    sidebar.style.position = 'static';
                    sidebar.style.transform = 'none';
                    menuToggle.style.display = 'none';
                }
            }

            window.addEventListener('resize', checkScreenSize);
            checkScreenSize();
        });
    </script>
</body>
</html>