<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php'; 
include 'navbar.php';
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Customer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-4">
<h2>Add Customer</h2>
<form action="add_customer.php" method="POST" class="p-4 border rounded shadow-sm">
  <div class="mb-3">
    <label class="form-label">Customer Name</label>
    <input type="text" class="form-control" name="name" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Phone</label>
    <input type="text" class="form-control" name="phone">
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" name="email">
  </div>
  <div class="mb-3">
    <label class="form-label">Address</label>
    <textarea class="form-control" name="address"></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Add Customer</button>
  <a href="view_customers.php" class="btn btn-secondary">View Customers</a>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $_POST['name'], $_POST['phone'], $_POST['email'], $_POST['address']);
    $stmt->execute();
    echo "<div class='alert alert-success mt-3'>Customer added successfully.</div>";
}
?>
  </div> <!-- Close container -->
</body>
</html>
