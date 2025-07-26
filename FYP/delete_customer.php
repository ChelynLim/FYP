<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);  // ensure id is an integer

    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Failed to prepare statement: " . $conn->error;
        exit;
    }
}

header("Location: view_customers.php");
exit();
?>
