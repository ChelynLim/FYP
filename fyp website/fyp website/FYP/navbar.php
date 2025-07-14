<?php
if (isset($_SESSION['admin'])) {
    include 'db_connect.php';
    $admin_username = $_SESSION['admin'];

    $stmt = $conn->prepare("SELECT role FROM admins WHERE username = ?");
    $stmt->bind_param("s", $admin_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $admin_role = $admin['role'] ?? 'normal';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Bookstore Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarNav" aria-controls="navbarNav"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="create_admin.php">Create Admin</a></li>
        <li class="nav-item"><a class="nav-link" href="view_customers.php">Customers</a></li>
        <li class="nav-item"><a class="nav-link" href="view_suppliers.php">Suppliers</a></li>
        <li class="nav-item"><a class="nav-link" href="add_customer.php">Add Customer</a></li>
        <li class="nav-item"><a class="nav-link" href="add_supplier.php">Add Supplier</a></li>
        <li class="nav-item"><a class="nav-link" href="all_books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link" href="homepage.php">Store</a></li>
        <li class="nav-item"><a class="nav-link" href="warehouse.php">Warehouse</a></li>
      </ul>
      <ul class="navbar-nav">
        <?php if (isset($admin_username)): ?>
          <li class="nav-item">
            <a class="nav-link disabled text-light">
              Welcome, <?= htmlspecialchars($admin_username) ?>
              <span class="badge bg-<?= $admin_role === 'super' ? 'success' : 'secondary' ?> ms-1">
                <?= $admin_role === 'super' ? 'Super Admin' : 'Admin' ?>
              </span>
            </a>
          </li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
