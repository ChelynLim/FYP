<?php
include 'db_connect.php';

$id = $_GET['id'] ?? null;

if ($id !== null) {
    $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Redirect before any output
header("Location: view_suppliers.php");
exit;
?>

