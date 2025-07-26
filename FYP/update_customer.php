<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("UPDATE customers SET name=?, phone=?, email=?, address=? WHERE customer_id=?");
    if ($stmt) {
        $stmt->bind_param("ssssi", $_POST['name'], $_POST['phone'], $_POST['email'], $_POST['address'], $_POST['customer_id']);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: view_customers.php");
        exit();
    } else {
        echo "Failed to prepare statement: " . $conn->error;
    }
}
?>
