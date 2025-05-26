<?php

session_start();
include('../includes/db_connect.php');
include('../includes/functions.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'farmer') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}


$verify = $pdo->prepare("
    SELECT r.id 
    FROM purchase_requests r
    JOIN produce_listings p ON r.listing_id = p.id
    WHERE r.id = ? AND p.farmer_id = ? AND r.status = 'pending'
");
$verify->execute([$_GET['id'], $_SESSION['user_id']]);
$request = $verify->fetch();

if (!$request) {
    header("Location: dashboard.php?error=invalid_request");
    exit();
}

// Update request status to rejected
$stmt = $pdo->prepare("
    UPDATE purchase_requests 
    SET status = 'rejected' 
    WHERE id = ?
");
$stmt->execute([$_GET['id']]);

header("Location: dashboard.php?success=request_rejected");
exit();
?>