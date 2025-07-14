<?php include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("UPDATE customers SET name=?, phone=?, email=?, address=? WHERE customer_id=?");
    $stmt->bind_param("ssssi", $_POST['name'], $_POST['phone'], $_POST['email'], $_POST['address'], $_POST['customer_id']);
    $stmt->execute();
    header("Location: view_customers.php");
}
?>
