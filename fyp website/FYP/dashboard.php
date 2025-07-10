<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';
include 'navbar.php';

// Get counts
$supplierCount = $conn->query("SELECT COUNT(*) FROM suppliers")->fetch_row()[0];
$customerCount = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0];
$storeCount = $conn->query("SELECT COUNT(*) FROM store")->fetch_row()[0];

// Get recent customers
$recentCustomers = $conn->query("SELECT * FROM customers ORDER BY customer_id DESC LIMIT 5");

// Get recent suppliers
$recentSuppliers = $conn->query("SELECT * FROM suppliers ORDER BY supplier_id DESC LIMIT 5");

// Get recent stores
$recentStores = $conn->query("SELECT * FROM store ORDER BY store_id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-4">

<h2 class="mb-4">📊 Admin Dashboard</h2>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Suppliers</h5>
                <p class="card-text fs-3"><?= $supplierCount ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Customers</h5>
                <p class="card-text fs-3"><?= $customerCount ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Stores</h5>
                <p class="card-text fs-3"><?= $storeCount ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Customers -->
<div class="row">
    <div class="col-md-6">
        <h4>🧍 Recent Customers</h4>
        <table class="table table-bordered">
            <thead>
                <tr><th>Name</th><th>Email</th></tr>
            </thead>
            <tbody>
                <?php while($row = $recentCustomers->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Suppliers -->
    <div class="col-md-6">
        <h4>📦 Recent Suppliers</h4>
        <table class="table table-bordered">
            <thead>
                <tr><th>Name</th><th>Email</th></tr>
            </thead>
            <tbody>
                <?php while($row = $recentSuppliers->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Stores -->
<div class="row mb-4">
    <div class="col-md-12">
        <h4>🏬 Recent Stores</h4>
        <table class="table table-bordered">
            <thead>
                <tr><th>Name</th><th>Address</th></tr>
            </thead>
            <tbody>
                <?php while($row = $recentStores->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</div> <!-- Close container -->
</body>
</html>
