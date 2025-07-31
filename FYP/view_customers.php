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
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>View Customers</title>
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
  --btn-bg-light: #B38B47;          /* Updated primary btn background */
  --btn-hover-bg-light: #8A6B32;    /* Updated primary btn hover */
  --table-head-bg-light: #e9dcc6;

  --primary-color-dark: #D4B483;
  --primary-color-hover-dark: #BBA15D;
  --text-color-light: #E6E1D3;
  --card-bg-dark: #1B263B;
  --shadow-color-dark: rgba(212, 180, 131, 0.6);
  --btn-bg-dark: #D4B483;           /* Dark mode primary btn bg */
  --btn-hover-bg-dark: #BBA15D;     /* Dark mode primary btn hover */
  --table-head-bg-dark: #324A66;

  --btn-danger-bg-light: #C5725B;   /* Danger btn bg */
  --btn-danger-hover-bg-light: #9F4E3C; /* Danger btn hover */

  --btn-danger-bg-dark: #A3523A;
  --btn-danger-hover-bg-dark: #823B27;
}

body {
  font-family: 'Roboto Slab', serif;
  background-color: var(--card-bg-light);
  color: var(--text-color-dark);
  transition: background-color 0.3s ease, color 0.3s ease;
  padding-top: 70px;
  min-height: 100vh;
}

body.dark-mode {
  background-color: var(--card-bg-dark);
  color: var(--text-color-light);
}

h2 {
  text-align: center;
  margin-bottom: 2rem;
  color: var(--primary-color);
  font-weight: 700;
}

body.dark-mode h2 {
  color: var(--primary-color-dark);
}

.card {
  background-color: var(--card-bg-light);
  border-radius: 1rem;
  box-shadow: 0 4px 15px var(--shadow-color-light);
  padding: 1rem;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

body.dark-mode .card {
  background-color: var(--card-bg-dark);
  box-shadow: 0 4px 15px var(--shadow-color-dark);
}

table {
  background-color: transparent;
  color: inherit;
}

table thead {
  background-color: var(--table-head-bg-light);
  color: var(--text-color-dark);
}

tbody tr:hover {
  background-color: var(--primary-color-hover);
  color: white;
  cursor: pointer;
}

body.dark-mode table thead {
  background-color: var(--table-head-bg-dark);
  color: var(--text-color-light);
}

body.dark-mode tbody tr:hover {
  background-color: var(--btn-hover-bg-dark);
  color: white;
}

.form-control, .form-select {
  border-radius: 0.5rem;
}

/* Updated button styles for Leather & Ink theme */

.btn-primary, .btn-success {
  background-color: var(--btn-bg-light);
  border: none;
  transition: background-color 0.3s ease;
  color: white;
}

.btn-primary:hover, .btn-success:hover {
  background-color: var(--btn-hover-bg-light);
  color: white;
}

body.dark-mode .btn-primary, 
body.dark-mode .btn-success {
  background-color: var(--btn-bg-dark);
  color: #1B263B; /* dark text for light button */
}

body.dark-mode .btn-primary:hover, 
body.dark-mode .btn-success:hover {
  background-color: var(--btn-hover-bg-dark);
  color: #1B263B;
}

.btn-danger {
  background-color: var(--btn-danger-bg-light);
  border: none;
  color: white;
  transition: background-color 0.3s ease;
}

.btn-danger:hover {
  background-color: var(--btn-danger-hover-bg-light);
  color: white;
}

body.dark-mode .btn-danger {
  background-color: var(--btn-danger-bg-dark);
  color: white;
}

body.dark-mode .btn-danger:hover {
  background-color: var(--btn-danger-hover-bg-dark);
  color: white;
}

.pagination .page-link {
  color: var(--primary-color);
  background-color: transparent;
  border: 1px solid var(--primary-color);
}

.pagination .page-link:hover {
  background-color: var(--primary-color-hover);
  color: white;
}

.pagination .page-item.active .page-link {
  background-color: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

body.dark-mode .pagination .page-link {
  color: var(--primary-color-dark);
  border-color: var(--primary-color-dark);
}

body.dark-mode .pagination .page-link:hover {
  background-color: var(--primary-color-hover-dark);
  color: white;
}

body.dark-mode .pagination .page-item.active .page-link {
  background-color: var(--primary-color-dark);
  color: white;
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
  box-shadow: 0 0 15px var(--btn-hover-bg-light);
}

body.dark-mode #darkModeToggle {
  background-color: var(--btn-bg-dark);
  box-shadow: 0 0 10px var(--btn-bg-dark);
}

body.dark-mode #darkModeToggle:hover {
  background-color: var(--btn-hover-bg-dark);
  box-shadow: 0 0 15px var(--btn-hover-bg-dark);
}
  </style>
</head>
<body>

<div class="container mt-4">
  <h2>Customer List</h2>
  <a href="add_customer.php" class="btn btn-success mb-3">➕ Add New Customer</a>

  <?php
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? '';
    $page = (int)($_GET['page'] ?? 1);
    if ($page < 1) $page = 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $countQuery = "SELECT COUNT(*) AS total FROM customers WHERE name LIKE ? OR email LIKE ?";
    $stmt = $conn->prepare($countQuery);
    $searchTerm = "%$search%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $totalRows = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $totalPages = max(1, ceil($totalRows / $limit));

    $query = "SELECT * FROM customers WHERE name LIKE ? OR email LIKE ?";
    if ($sort === 'name' || $sort === 'email') {
        $query .= " ORDER BY $sort ASC";
    }
    $query .= " LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
  ?>

  <!-- Filter form -->
  <form method="GET" class="row g-2 mb-4">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2">
      <select name="sort" class="form-select">
        <option value="">Sort by</option>
        <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name</option>
        <option value="email" <?= $sort == 'email' ? 'selected' : '' ?>>Email</option>
      </select>
    </div>
    <input type="hidden" name="page" value="1">
    <div class="col-md-2">
      <button class="btn btn-primary" type="submit">🔍 Search</button>
    </div>
  </form>

  <!-- Table inside card -->
  <div class="card mb-4">
    <table class="table table-bordered table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Name</th>
          <th>Phone</th>
          <th>Email</th>
          <th>Address</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['phone']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['address']) ?></td>
          <td>
            <a href="edit_customer.php?id=<?= $row['customer_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
            <a href="delete_customer.php?id=<?= $row['customer_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <nav>
    <ul class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
          <a class="page-link" href="?search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
</div>

<footer class="text-center mt-5">
  <p>&copy; <?= date('Y'); ?> Inkventory. All rights reserved.</p>
</footer>

<!-- Toggle Button -->
<button id="darkModeToggle" aria-label="Toggle dark/light mode" title="Toggle Dark/Light Mode">
  <i class="bi bi-brightness-high-fill"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const toggle = document.getElementById('darkModeToggle');
  const icon = toggle.querySelector('i');

  function updateIcon(isLight) {
    icon.className = isLight ? 'bi bi-moon-fill' : 'bi bi-brightness-high-fill';
  }

  if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
    updateIcon(false);
  } else {
    updateIcon(true);
  }

  toggle.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    const isLight = !document.body.classList.contains('dark-mode');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    updateIcon(isLight);
  });
</script>

</body>
</html>

