<?php
session_start();
require_once 'db_connect.php';

// Check super admin access
$admin_username = $_SESSION['admin'] ?? '';
if (!$admin_username) {
    header("Location: access_denied.php");
    exit();
}
$stmt = $conn->prepare("SELECT role FROM admins WHERE username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();
if (!$admin || $admin['role'] !== 'super') {
    header("Location: access_denied.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['audit_id'])) {
    $audit_id = intval($_POST['audit_id']);

    // Delete audit_stock (child records)
    $stmt = $conn->prepare("DELETE FROM audit_stock WHERE audit_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $audit_id);
    $stmt->execute();
    $stmt->close();

    // Delete audit_logs (parent record)
    $stmt = $conn->prepare("DELETE FROM audit_logs WHERE audit_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $audit_id);
    $stmt->execute();
    $stmt->close();

    header("Location: view_audit_logs.php");
    exit();
} else {
    header("Location: view_audit_logs.php");
    exit();
}
?>
