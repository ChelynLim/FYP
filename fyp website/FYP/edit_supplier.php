<?php 
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';
include 'navbar.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM suppliers WHERE supplier_id = $id");
$supplier = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Supplier</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-4">
<h2>Edit Supplier</h2>
<form action="update_supplier.php" method="POST" class="p-4 border rounded shadow-sm">
  <input type="hidden" name="supplier_id" value="<?php echo $supplier['supplier_id']; ?>">
  <div class="mb-3">
    <label class="form-label">Supplier Name</label>
    <input type="text" class="form-control" name="name" value="<?php echo $supplier['name']; ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Contact Person</label>
    <input type="text" class="form-control" name="contact_person" value="<?php echo $supplier['contact_person']; ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Phone</label>
    <input type="text" class="form-control" name="phone" value="<?php echo $supplier['phone']; ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" name="email" value="<?php echo $supplier['email']; ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Address</label>
    <textarea class="form-control" name="address"><?php echo $supplier['address']; ?></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Update Supplier</button>
  <a href="view_suppliers.php" class="btn btn-secondary">Back</a>
</form>
  </div> <!-- Close container -->
</body>

</html>
