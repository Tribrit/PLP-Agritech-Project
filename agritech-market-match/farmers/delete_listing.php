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

try {
    $pdo->beginTransaction();
    

    $deleteRequests = $pdo->prepare("
        DELETE FROM purchase_requests 
        WHERE listing_id = ? AND status != 'completed'
    ");
    $deleteRequests->execute([$_GET['id']]);
    

    $deleteListing = $pdo->prepare("
        DELETE FROM produce_listings 
        WHERE id = ? AND farmer_id = ?
    ");
    $deleteListing->execute([$_GET['id'], $_SESSION['user_id']]);
    
    $pdo->commit();
    
    header("Location: dashboard.php?success=listing_deleted");
    exit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: dashboard.php?error=cannot_delete_listing");
    exit();
}
?>