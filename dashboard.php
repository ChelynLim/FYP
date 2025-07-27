<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once 'db_connect.php';

// Get counts
$supplierCount = $conn->query("SELECT COUNT(*) FROM suppliers")->fetch_row()[0];
$customerCount = $conn->query("SELECT COUNT(*) FROM customers")->fetch_row()[0];
$storeCount = $conn->query("SELECT COUNT(*) FROM store")->fetch_row()[0];
$deliveryPersonCount = $conn->query("SELECT COUNT(*) FROM delivery_person")->fetch_row()[0];
$bookCount = $conn->query("SELECT COUNT(*) FROM book")->fetch_row()[0];

// Get recent entries
$recentCustomers = $conn->query("SELECT * FROM customers ORDER BY customer_id DESC LIMIT 5");
$recentSuppliers = $conn->query("SELECT * FROM suppliers ORDER BY supplier_id DESC LIMIT 5");
$recentStores = $conn->query("SELECT * FROM store ORDER BY store_id DESC LIMIT 5");
$recentDeliveryPersons = $conn->query("SELECT * FROM delivery_person ORDER BY delivery_person_id DESC LIMIT 5");
$recentBooks = $conn->query("SELECT * FROM book ORDER BY book_id DESC LIMIT 5");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Slab&display=swap');

    :root {
      --primary-color: #8B5E3C;
      --primary-color-hover: #6E4A2C;
      --text-color-dark: #3C2F2F;
      --card-bg-light: #fdf6e3;
      --shadow-color-light: rgba(139, 94, 60, 0.2);
      --btn-bg-light: #8B5E3C;
      --btn-hover-bg-light: #6E4A2C;
      --table-head-bg-light: #e9dcc6;
    }

    body {
      padding-top: 70px;
      font-family: 'Roboto Slab', serif;
      background-color: var(--card-bg-light);
      color: var(--text-color-dark);
      transition: background-color 0.3s ease, color 0.3s ease;
      min-height: 100vh;
    }

    body.dark-mode {
      --primary-color: #D4B483;
      --primary-color-hover: #BBA15D;
      --text-color-dark: #E6E1D3;
      --card-bg-dark: #1B263B;
      --shadow-color-dark: rgba(212, 180, 131, 0.6);
      --btn-bg-dark: #B38B47;
      --btn-hover-bg-dark: #8A6B32;
      --table-head-bg-dark: #324A66;

      background: linear-gradient(135deg, #1B263B, #121C2F);
      color: var(--text-color-dark);
    }

    h2, .section-title {
      color: var(--primary-color);
      font-weight: 700;
    }

    .custom-summary-card {
      background-color: var(--card-bg-light);
      color: var(--text-color-dark);
      box-shadow: 0 4px 15px var(--shadow-color-light);
      border: none;
      transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
    }

    .custom-summary-card:hover {
      background-color: var(--primary-color-hover);
      color: white;
      box-shadow: 0 6px 25px var(--shadow-color-light);
    }

    body.dark-mode .custom-summary-card {
      background-color: var(--card-bg-dark);
      color: var(--text-color-dark);
      box-shadow: 0 4px 15px var(--shadow-color-dark);
    }

    body.dark-mode .custom-summary-card:hover {
      background-color: var(--btn-hover-bg-dark);
      color: white;
      box-shadow: 0 6px 25px var(--shadow-color-dark);
    }

    .custom-summary-card .card-body {
      text-align: center;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .summary-cards i {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
    }

    .summary-cards h6 {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .summary-cards p {
      font-size: 1.5rem;
      font-weight: 700;
      margin: 0;
    }

    table {
      background-color: var(--card-bg-light);
      color: var(--text-color-dark);
      border-radius: 0.8rem;
      box-shadow: 0 4px 15px var(--shadow-color-light);
    }

    table thead {
      background-color: var(--table-head-bg-light);
    }

    table tbody tr:hover {
      background-color: var(--primary-color-hover);
      color: white;
    }

    body.dark-mode table {
      background-color: var(--card-bg-dark);
      color: var(--text-color-dark);
      box-shadow: 0 4px 15px var(--shadow-color-dark);
    }

    body.dark-mode table thead {
      background-color: var(--table-head-bg-dark);
    }

    body.dark-mode table tbody tr:hover {
      background-color: var(--btn-hover-bg-dark);
      color: white;
    }

    .container {
      max-width: 1100px;
    }

    #darkModeToggle {
      position: fixed;
      bottom: 1rem;
      right: 1rem;
      z-index: 1050;
      background-color: var(--btn-bg-light);
      color: white;
      border: none;
      border-radius: 50%;
      width: 48px;
      height: 48px;
      font-size: 1.5rem;
      box-shadow: 0 0 10px var(--btn-bg-light);
      cursor: pointer;
    }

    #darkModeToggle:hover {
      background-color: var(--btn-hover-bg-light);
    }

    body.dark-mode #darkModeToggle {
      background-color: var(--btn-bg-dark);
      box-shadow: 0 0 10px var(--btn-bg-dark);
    }

    body.dark-mode #darkModeToggle:hover {
      background-color: var(--btn-hover-bg-dark);
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
  <h2>Overview</h2>

  <!-- Summary Cards -->
  <div class="row g-3 summary-cards mb-5">
    <?php
      $summary = [
        ['label' => 'Suppliers', 'count' => $supplierCount, 'icon' => 'bi-truck'],
        ['label' => 'Customers', 'count' => $customerCount, 'icon' => 'bi-people'],
        ['label' => 'Stores', 'count' => $storeCount, 'icon' => 'bi-shop'],
        ['label' => 'Delivery Persons', 'count' => $deliveryPersonCount, 'icon' => 'bi-person-bounding-box'],
        ['label' => 'Books', 'count' => $bookCount, 'icon' => 'bi-book'],
      ];

      foreach ($summary as $item): ?>
        <div class="col-6 col-md-4 col-lg-2 d-flex">
          <div class="card custom-summary-card w-100">
            <div class="card-body">
              <i class="bi <?= $item['icon'] ?>"></i>
              <h6><?= $item['label'] ?></h6>
              <p><?= $item['count'] ?></p>
            </div>
          </div>
        </div>
    <?php endforeach; ?>
  </div>

  <!-- Recent Customers & Suppliers -->
  <div class="row mb-4">
    <div class="col-md-6">
      <h4 class="section-title">🧍 Recent Customers</h4>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle rounded-4">
          <thead class="table-light">
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
    </div>

    <div class="col-md-6">
      <h4 class="section-title">📦 Recent Suppliers</h4>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle rounded-4">
          <thead class="table-light">
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
  </div>

  <!-- Recent Stores -->
  <div class="mb-4">
    <h4 class="section-title">🏬 Recent Stores</h4>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle rounded-4">
        <thead class="table-light">
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

  <!-- Recent Books -->
  <div class="mb-4">
    <h4 class="section-title">📚 Recent Books</h4>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle rounded-4">
        <thead class="table-light">
          <tr><th>Name</th><th>Author</th><th>ISBN</th></tr>
        </thead>
        <tbody>
        <?php while($row = $recentBooks->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['author']) ?></td>
            <td><?= htmlspecialchars($row['isbn']) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Delivery Persons -->
  <div class="mb-4">
    <h4 class="section-title">🚚 Recent Delivery Persons</h4>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle rounded-4">
        <thead class="table-light">
          <tr><th>Name</th><th>Contact Number</th><th>Email</th></tr>
        </thead>
        <tbody>
        <?php while($row = $recentDeliveryPersons->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['contact_number']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<footer class="text-center mt-5">
  <p>&copy; <?= date("Y") ?> Inkventory. All rights reserved.</p>
</footer>

<!-- Dark/Light Mode Toggle Button -->
<button id="darkModeToggle" aria-label="Toggle dark/light mode" title="Toggle Dark/Light Mode">
  <i class="bi bi-moon-fill"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const darkModeToggle = document.getElementById('darkModeToggle');
  const icon = darkModeToggle.querySelector('i');

  function updateIcon(isLight) {
    icon.className = isLight ? 'bi bi-moon-fill' : 'bi bi-brightness-high-fill';
  }

  if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
    updateIcon(false);
  } else {
    document.body.classList.remove('dark-mode');
    updateIcon(true);
  }

  darkModeToggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    const isLight = !document.body.classList.contains('dark-mode');
    updateIcon(isLight);
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
  });
</script>

</body>
</html>
