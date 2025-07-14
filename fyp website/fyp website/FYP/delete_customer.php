<?php include 'db_connect.php';

$id = $_GET['id'];
$conn->query("DELETE FROM customers WHERE customer_id = $id");

header("Location: view_customers.php");
?>
