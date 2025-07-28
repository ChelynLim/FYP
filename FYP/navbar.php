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

<style>
  /* Deep Navy & Brass Dark Theme & Clean Styles */

  nav.navbar {
    background-color: #0b1a33 !important; /* deep navy */
    box-shadow: 0 2px 6px rgba(186, 153, 74, 0.5); /* subtle brass glow */
    font-family: 'Georgia', serif;
  }

  nav.navbar .navbar-brand,
  nav.navbar .nav-link,
  nav.navbar .navbar-toggler-icon {
    color: #ba994a !important; /* brass */
    transition: color 0.3s ease;
  }

  nav.navbar .navbar-brand:hover,
  nav.navbar .nav-link:hover,
  nav.navbar .nav-link:focus {
    color: #f0d87d !important; /* lighter brass on hover */
  }

  nav.navbar .navbar-toggler {
    border-color: #ba994a !important;
  }

  nav.navbar .navbar-toggler-icon {
    filter:70%) saturate(500%) hue-rotate(35deg) brightness(100%) contrast(90%);
  }

  nav.navbar .dropdown-menu {
    background-color: #142a55; /* slightly lighter deep navy */
    border: 1px solid #ba994a;
  }

  nav.navbar .dropdown-item {
    color: #ba994a;
  }

  nav.navbar .dropdown-item:hover,
  nav.navbar .dropdown-item:focus {
    background-color: #f0d87d; /* light brass */
    color: #0b1a33; /* dark navy text */
  }

  nav.navbar .nav-link.disabled {
    color: #ba994a !important;
    opacity: 0.8;
  }

  nav.navbar .badge.bg-success {
    background-color: #9ca65f !important; /* olive brass */
    color: #0b1a33 !important;
  }

  nav.navbar .badge.bg-secondary {
    background-color: #7e6e40 !important; /* muted brass */
    color: #0b1a33 !important;
  }

  nav.navbar .nav-link.text-danger {
    color: #e07a5f !important;
    font-weight: 600;
    transition: color 0.3s ease;
  }

  nav.navbar .nav-link.text-danger:hover,
  nav.navbar .nav-link.text-danger:focus {
    color: #f4a261 !important;
  }
</style>

<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold fs-4" href="dashboard.php">Inkventory</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarNav" aria-controls="navbarNav"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">

      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="create_admin.php">Create Admin</a>
        </li>

        <!-- Customers Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="customersDropdown" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            Customers
          </a>
          <ul class="dropdown-menu" aria-labelledby="customersDropdown">
            <li><a class="dropdown-item" href="view_customers.php">View Customers</a></li>
            <li><a class="dropdown-item" href="add_customer.php">Add Customer</a></li>
          </ul>
        </li>

        <!-- Suppliers Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="suppliersDropdown" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            Suppliers
          </a>
          <ul class="dropdown-menu" aria-labelledby="suppliersDropdown">
            <li><a class="dropdown-item" href="view_suppliers.php">View Suppliers</a></li>
            <li><a class="dropdown-item" href="add_supplier.php">Add Supplier</a></li>
          </ul>
        </li>

        <!-- Inventory Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="inventoryDropdown" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            Inventory
          </a>
          <ul class="dropdown-menu" aria-labelledby="inventoryDropdown">
            <li><a class="dropdown-item" href="all_books.php">Books</a></li>
            <li><a class="dropdown-item" href="store.php">Store</a></li>
            <li><a class="dropdown-item" href="warehouse.php">Warehouse</a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="delivery_person.php">Delivery Personnel</a>
        </li>

        <!-- Audits Dropdown - Only visible to super admins -->
        <?php if (isset($admin_role) && $admin_role === 'super'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="auditsDropdown" role="button"
              data-bs-toggle="dropdown" aria-expanded="false">
              Audits
            </a>
            <ul class="dropdown-menu" aria-labelledby="auditsDropdown">
              <li><a class="dropdown-item" href="view_audit_logs.php">View Audits</a></li>
              <li><a class="dropdown-item" href="add_audit.php">Add Audit</a></li>
            </ul>
          </li>
        <?php endif; ?>

      </ul>

      <ul class="navbar-nav align-items-center">
        <?php if (isset($admin_username)): ?>
          <li class="nav-item me-3">
            <span class="nav-link disabled">
              Welcome, <strong><?= htmlspecialchars($admin_username) ?></strong>
              <span class="badge bg-<?= $admin_role === 'super' ? 'success' : 'secondary' ?> ms-2">
                <?= $admin_role === 'super' ? 'Super Admin' : 'Admin' ?>
              </span>
            </span>
          </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
      </ul>

    </div>
  </div>
</nav>
