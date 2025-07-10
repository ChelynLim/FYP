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
  <title>Add Supplier</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-4">


<h2>Add Supplier</h2>
<form action="add_supplier.php" method="POST" class="p-4 border rounded shadow-sm">
  <div class="mb-3">
    <label class="form-label">Supplier Name</label>
    <input type="text" class="form-control" name="name" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Contact Person</label>
    <input type="text" class="form-control" name="contact_person">
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
  <button type="submit" class="btn btn-primary">Add Supplier</button>
  <a href="view_suppliers.php" class="btn btn-secondary">View Suppliers</a>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $_POST['name'], $_POST['contact_person'], $_POST['phone'], $_POST['email'], $_POST['address']);
    $stmt->execute();
    echo "<div class='alert alert-success mt-3'>Supplier added successfully.</div>";
}
?>
  </div> <!-- Close container -->
</body>

</html>
