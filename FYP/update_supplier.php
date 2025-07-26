<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("UPDATE suppliers SET name=?, contact_person=?, phone=?, email=?, address=? WHERE supplier_id=?");
    $stmt->bind_param("sssssi", $_POST['name'], $_POST['contact_person'], $_POST['phone'], $_POST['email'], $_POST['address'], $_POST['supplier_id']);
    $stmt->execute();

    // Redirect BEFORE any HTML or includes
    header("Location: view_suppliers.php");
    exit;
}

// Only include navbar if needed below
include 'navbar.php';
?>
