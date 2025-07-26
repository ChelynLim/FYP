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
  <title>View Suppliers</title>
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

      --primary-color-dark: #D4B483;
      --primary-color-hover-dark: #BBA15D;
      --text-color-dark-mode: #E6E1D3;
      --card-bg-dark: #1B263B;
      --shadow-color-dark: rgba(212, 180, 131, 0.6);
      --btn-bg-dark: #B38B47;
      --btn-hover-bg-dark: #8A6B32;
      --table-head-bg-dark: #324A66;
    }

    body {
      font-family: 'Roboto Slab', serif;
      background-color: var(--card-bg-light);
      color: var(--text-color-dark);
      padding-top: 70px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    body.dark-mode {
      background-color: var(--card-bg-dark);
      color: var(--text-color-dark-mode);
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
    }

    body.dark-mode .card {
      background-color: var(--card-bg-dark);
      box-shadow: 0 4px 15px var(--shadow-color-dark);
    }

    table thead {
      background-color: var(--table-head-bg-light);
    }

    body.dark-mode table thead {
      background-color: var(--table-head-bg-dark);
    }

    tbody tr:hover {
      background-color: var(--primary-color-hover);
      color: white;
    }

    body.dark-mode tbody tr:hover {
      background-color: var(--btn-hover-bg-dark);
    }

    .btn-primary, .btn-success {
      background-color: var(--btn-bg-light);
      border: none;
    }

    .btn-primary:hover, .btn-success:hover {
      background-color: var(--btn-hover-bg-light);
    }

    body.dark-mode .btn-primary, body.dark-mode .btn-success {
      background-color: var(--btn-bg-dark);
    }

    body.dark-mode .btn-primary:hover, body.dark-mode .btn-success:hover {
      background-color: var(--btn-hover-bg-dark);
    }

    .pagination .page-link {
      color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .pagination .page-item.active .page-link {
      background-color: var(--primary-color);
      color: white;
    }

    body.dark-mode .pagination .page-link {
      color: var(--primary-color-dark);
      border-color: var(--primary-color-dark);
    }

    body.dark-mode .pagination .page-item.active .page-link {
      background-color: var(--primary-color-dark);
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
  <div class="container mt-4">
    <h2>Supplier List</h2>
    <a href="add_supplier.php" class="btn btn-success mb-3">➕ Add New Supplier</a>

    <?php
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $countQuery = "SELECT COUNT(*) AS total FROM suppliers WHERE name LIKE ? OR email LIKE ?";
    $stmt = $conn->prepare($countQuery);
    $searchTerm = "%$search%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $totalRows = $stmt->get_result()->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $limit);

    $query = "SELECT * FROM suppliers WHERE name LIKE ? OR email LIKE ?";
    if ($sort === 'name' || $sort === 'email') {
        $query .= " ORDER BY $sort ASC";
    }
    $query .= " LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    ?>

    <form method="GET" class="row g-2 mb-3">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>" />
      </div>
      <div class="col-md-2">
        <select name="sort" class="form-select">
          <option value="">Sort by</option>
          <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name</option>
          <option value="email" <?= $sort == 'email' ? 'selected' : '' ?>>Email</option>
        </select>
      </div>
      <input type="hidden" name="page" value="1" />
      <div class="col-md-2">
        <button class="btn btn-primary" type="submit">🔍 Search</button>
      </div>
    </form>

    <div class="card">
      <table class="table table-bordered table-hover mb-0">
        <thead>
          <tr>
            <th>Name</th>
            <th>Contact Person</th>
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
            <td><?= htmlspecialchars($row['contact_person']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td>
              <a href="edit_supplier.php?id=<?= $row['supplier_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
              <a href="delete_supplier.php?id=<?= $row['supplier_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <nav>
      <ul class="pagination mt-3">
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

  <button id="darkModeToggle" aria-label="Toggle dark/light mode" title="Toggle Dark/Light Mode">
    <i class="bi bi-moon-fill"></i>
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