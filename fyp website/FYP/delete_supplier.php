<?php include 'db_connect.php';
include 'navbar.php';

$id = $_GET['id'];
$conn->query("DELETE FROM suppliers WHERE supplier_id = $id");

header("Location: view_suppliers.php");
?>
