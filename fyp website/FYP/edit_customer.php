<?php 
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';
include 'navbar.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM customers WHERE customer_id = $id");
$customer = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Customer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-4">
<h2>Edit Customer</h2>
<form action="update_customer.php" method="POST" class="p-4 border rounded shadow-sm">
  <input type="hidden" name="customer_id" value="<?php echo $customer['customer_id']; ?>">
  <div class="mb-3">
    <label class="form-label">Customer Name</label>
    <input type="text" class="form-control" name="name" value="<?php echo $customer['name']; ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Phone</label>
    <input type="text" class="form-control" name="phone" value="<?php echo $customer['phone']; ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" name="email" value="<?php echo $customer['email']; ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Address</label>
    <textarea class="form-control" name="address"><?php echo $customer['address']; ?></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Update Customer</button>
  <a href="view_customers.php" class="btn btn-secondary">Back</a>
</form>

</div> <!-- Close container -->
</body>
</html>
