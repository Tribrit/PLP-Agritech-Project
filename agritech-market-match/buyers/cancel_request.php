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
    UPDATE purchase_requests 
    SET status = 'rejected' 
    WHERE id = ? AND buyer_id = ? AND status = 'pending'
");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);

header("Location: dashboard.php?success=request_canceled");
exit();
?>