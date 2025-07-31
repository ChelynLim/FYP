<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'db_connect.php';

// Handle delete store form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_store_id'])) {
    $delete_store_id = (int)$_POST['delete_store_id'];

    // Optional: Check if store has dependent records before deleting
    // For simplicity, just delete here
    $stmt = $conn->prepare("DELETE FROM store WHERE store_id = ?");
    $stmt->bind_param('i', $delete_store_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to prevent form resubmission
    header('Location: store.php');
    exit();
}

// Handle add store form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_store'])) {
    $store_name = trim($_POST['store_name'] ?? '');
    $store_address = trim($_POST['store_address'] ?? '');
    if ($store_name && $store_address) {
        $stmt = $conn->prepare("INSERT INTO store (name, address) VALUES (?, ?)");
        $stmt->bind_param('ss', $store_name, $store_address);
        $stmt->execute();
        $stmt->close();
        header('Location: store.php');
        exit();
    } else {
        $error = 'All fields are required to add a store.';
    }
}

// Handle edit store form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_store'])) {
    $edit_store_id = (int)($_POST['edit_store_id'] ?? 0);
    $edit_store_name = trim($_POST['edit_store_name'] ?? '');
    $edit_store_address = trim($_POST['edit_store_address'] ?? '');

    if ($edit_store_id > 0 && $edit_store_name !== '' && $edit_store_address !== '') {
        $stmt = $conn->prepare("UPDATE store SET name = ?, address = ? WHERE store_id = ?");
        $stmt->bind_param('ssi', $edit_store_name, $edit_store_address, $edit_store_id);
        $stmt->execute();
        $stmt->close();

        header('Location: store.php');
        exit();
    } else {
        $error = 'All fields are required to edit a store.';
    }
}

// Fetch all stores from the database
$stores = [];
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $sql = "SELECT * FROM store WHERE name LIKE ? OR address LIKE ?";
    $param = "%$search%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $stores[] = $row;
    }
    $stmt->close();
} else {
    $sql = "SELECT * FROM store";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stores[] = $row;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Stores</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  
  <style>
@import url('https://fonts.googleapis.com/css2?family=Roboto+Slab&display=swap');

:root {
  /* Light theme colors */
  --primary-color: #8B5E3C; /* leather brown */
  --primary-color-hover: #6E4A2C;
  --text-color-dark: #3C2F2F; /* dark brown text */
  --background-light: #fdf6e3; /* parchment */
  --card-bg-light: #fff8e7;
  --shadow-color-light: rgba(139, 94, 60, 0.2);
  --btn-bg-light: #8B5E3C;
  --btn-hover-bg-light: #6E4A2C;
  --modal-bg-light: #fff8e7;

  /* Danger colors for Delete button */
  --danger-color-light: #A83232; /* deep leather red */
  --danger-hover-light: #7F2626;

  /* Dark theme colors */
  --primary-color-dark: #D4B483;
  --primary-color-hover-dark: #BBA15D;
  --text-color-light: #E6E1D3;
  --background-dark: #1B263B;
  --card-bg-dark: #2A3A62;
  --shadow-color-dark: rgba(212, 180, 131, 0.6);
  --btn-bg-dark: #B38B47;
  --btn-hover-bg-dark: #8A6B32;
  --modal-bg-dark: #2A3A62;

  /* Danger colors dark mode */
  --danger-color-dark: #D25A5A;
  --danger-hover-dark: #A44242;
}

body {
  font-family: 'Roboto Slab', serif;
  background-color: var(--background-light);
  color: var(--text-color-dark);
  padding-top: 70px;
  transition: background-color 0.3s ease, color 0.3s ease;
  min-height: 100vh;
}
body.dark-mode {
  background-color: var(--background-dark);
  color: var(--text-color-light);
}

h1 {
  color: var(--primary-color);
  font-weight: 700;
  text-align: center;
  margin-bottom: 2rem;
  transition: color 0.3s ease;
}
body.dark-mode h1 {
  color: var(--primary-color-dark);
}

/* Button Styles */
.btn-primary, .btn-success {
  background-color: var(--btn-bg-light) !important;
  color: var(--background-light) !important;
  border: none !important;
  transition: background-color 0.3s ease;
}
.btn-primary:hover, .btn-success:hover {
  background-color: var(--btn-hover-bg-light) !important;
  color: var(--background-light) !important;
}
body.dark-mode .btn-primary, body.dark-mode .btn-success {
  background-color: var(--btn-bg-dark) !important;
  color: var(--background-dark) !important;
}
body.dark-mode .btn-primary:hover, body.dark-mode .btn-success:hover {
  background-color: var(--btn-hover-bg-dark) !important;
  color: var(--background-dark) !important;
}

/* Danger button */
.btn-danger {
  background-color: var(--danger-color-light) !important;
  color: var(--background-light) !important;
  border: none !important;
  transition: background-color 0.3s ease;
}
.btn-danger:hover {
  background-color: var(--danger-hover-light) !important;
  color: var(--background-light) !important;
}
body.dark-mode .btn-danger {
  background-color: var(--danger-color-dark) !important;
  color: var(--background-dark) !important;
}
body.dark-mode .btn-danger:hover {
  background-color: var(--danger-hover-dark) !important;
  color: var(--background-dark) !important;
}

.btn-outline-primary {
  color: var(--btn-bg-light);
  border-color: var(--btn-bg-light);
  transition: color 0.3s ease, border-color 0.3s ease;
}
.btn-outline-primary:hover {
  background-color: var(--btn-bg-light);
  color: var(--background-light);
}
body.dark-mode .btn-outline-primary {
  color: var(--btn-bg-dark);
  border-color: var(--btn-bg-dark);
}
body.dark-mode .btn-outline-primary:hover {
  background-color: var(--btn-bg-dark);
  color: var(--background-dark);
}

.form-control {
  border-radius: 0.5rem;
  transition: background-color 0.3s ease, color 0.3s ease;
  background-color: var(--background-light);
  color: var(--text-color-dark);
  border: 1px solid var(--btn-bg-light);
}
.form-control::placeholder {
  color: var(--btn-bg-light);
}
body.dark-mode .form-control {
  background-color: var(--card-bg-dark);
  color: var(--text-color-light);
  border: 1px solid var(--btn-bg-dark);
}
body.dark-mode .form-control::placeholder {
  color: var(--btn-bg-dark);
}

.card {
  background-color: var(--card-bg-light);
  box-shadow: 0 4px 15px var(--shadow-color-light);
  border-radius: 1rem;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
  color: inherit;
}
.card:hover {
  box-shadow: 0 6px 20px var(--primary-color);
  color: var(--primary-color);
  cursor: pointer;
}
body.dark-mode .card {
  background-color: var(--card-bg-dark);
  box-shadow: 0 4px 15px var(--shadow-color-dark);
}
body.dark-mode .card:hover {
  box-shadow: 0 6px 20px var(--primary-color-dark);
  color: var(--primary-color-dark);
  cursor: pointer;
}

/* Modal styling */
.modal-content {
  background-color: var(--modal-bg-light);
  border-radius: 1rem;
  transition: background-color 0.3s ease;
  color: var(--text-color-dark);
}
body.dark-mode .modal-content {
  background-color: var(--modal-bg-dark);
  color: var(--text-color-light);
}

/* Modal header */
.modal-header {
  border-bottom: none;
}

/* Modal title */
.modal-title {
  color: var(--primary-color);
  transition: color 0.3s ease;
}
body.dark-mode .modal-title {
  color: var(--primary-color-dark);
}

/* Modal buttons */
.btn-secondary {
  background-color: #777;
  border: none;
  transition: background-color 0.3s ease;
  color: white;
}
.btn-secondary:hover {
  background-color: #555;
}
body.dark-mode .btn-secondary {
  background-color: #444;
}
body.dark-mode .btn-secondary:hover {
  background-color: #222;
}

/* Alert message */
.alert-danger {
  max-width: 600px;
  margin: 1rem auto;
  border-radius: 0.5rem;
  background-color: #f8d7da;
  color: #842029;
}
body.dark-mode .alert-danger {
  background-color: #742a2a;
  color: #f8d7da;
}

/* Link styling inside cards */
.card a {
  text-decoration: none;
  color: inherit;
  transition: color 0.3s ease;
}
.card a:hover {
  color: var(--primary-color);
}
body.dark-mode .card a:hover {
  color: var(--primary-color-dark);
}

/* Dark mode toggle button */
#darkModeToggle {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  z-index: 1050;
  background-color: var(--btn-bg-light);
  color: var(--background-light);
  border: none;
  border-radius: 50%;
  width: 48px;
  height: 48px;
  font-size: 1.5rem;
  box-shadow: 0 0 10px var(--btn-bg-light);
  cursor: pointer;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
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
  <?php include 'navbar.php'; ?>

  <div class="container py-5">
    <h1>All Stores</h1>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addStoreModal">➕ Add Store</button>

    <!-- Add Store Modal -->
    <div class="modal fade" id="addStoreModal" tabindex="-1" aria-labelledby="addStoreModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="addStoreModalLabel">Add New Store</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="store_name" class="form-label">Store Name</label>
                <input type="text" name="store_name" id="store_name" class="form-control" required />
              </div>
              <div class="mb-3">
                <label for="store_address" class="form-label">Address</label>
                <input type="text" name="store_address" id="store_address" class="form-control" required />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="add_store" class="btn btn-primary">Add Store</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Store Modal -->
    <div class="modal fade" id="editStoreModal" tabindex="-1" aria-labelledby="editStoreModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST" id="editStoreForm">
            <div class="modal-header">
              <h5 class="modal-title" id="editStoreModalLabel">Edit Store</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="edit_store_id" id="edit_store_id" />
              <div class="mb-3">
                <label for="edit_store_name" class="form-label">Store Name</label>
                <input type="text" name="edit_store_name" id="edit_store_name" class="form-control" required />
              </div>
              <div class="mb-3">
                <label for="edit_store_address" class="form-label">Address</label>
                <input type="text" name="edit_store_address" id="edit_store_address" class="form-control" required />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="edit_store" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <form method="GET" class="mb-4">
      <div class="row g-2 align-items-end">
        <div class="col-md-10">
          <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search stores by name or address..."
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
          />
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-outline-primary w-100">Search</button>
        </div>
      </div>
    </form>

    <div class="row">
      <?php if (count($stores) > 0): ?>
        <?php foreach ($stores as $store): ?>
          <div class="col-md-4 mb-4">
            <div class="card store-card h-100 d-flex flex-column">
              <a href="store_books.php?id=<?= urlencode($store['store_id']) ?>" style="text-decoration:none; color:inherit; flex-grow: 1;">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($store['name'] ?? 'Store') ?></h5>
                  <?php if (!empty($store['description'])): ?>
                    <p class="card-text"><?= htmlspecialchars($store['description']) ?></p>
                  <?php endif; ?>
                  <p class="card-text"><strong>Address:</strong> <?= htmlspecialchars($store['address'] ?? 'N/A') ?></p>
                </div>
              </a>

              <div class="card-footer bg-transparent border-0 d-flex justify-content-end gap-2">
                <!-- Edit button triggers modal -->
                <button
                  type="button"
                  class="btn btn-primary btn-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#editStoreModal"
                  data-store-id="<?= (int)$store['store_id'] ?>"
                  data-store-name="<?= htmlspecialchars($store['name'], ENT_QUOTES) ?>"
                  data-store-address="<?= htmlspecialchars($store['address'], ENT_QUOTES) ?>"
                  title="Edit Store"
                >
                  <i class="bi bi-pencil-square"></i> Edit
                </button>

                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this store?');" class="m-0">
                  <input type="hidden" name="delete_store_id" value="<?= (int)$store['store_id'] ?>" />
                  <button type="submit" class="btn btn-danger btn-sm" title="Delete Store">
                    <i class="bi bi-trash"></i> Delete
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center">No stores found.</p>
      <?php endif; ?>
    </div>
  </div>

  <footer class="text-center mt-5">
    <p>&copy; <?= date('Y'); ?> Inkventory. All rights reserved.</p>
  </footer>

  <!-- Dark mode toggle button -->
  <button id="darkModeToggle" aria-label="Toggle dark/light mode" title="Toggle Dark/Light Mode">
    <i class="bi bi-brightness-high-fill"></i>
  </button>

  <!-- Bootstrap JS -->
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

    // Populate Edit Store Modal fields dynamically
    const editStoreModal = document.getElementById('editStoreModal');
    editStoreModal.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget; // Button that triggered the modal
      const storeId = button.getAttribute('data-store-id');
      const storeName = button.getAttribute('data-store-name');
      const storeAddress = button.getAttribute('data-store-address');

      // Populate modal form fields
      document.getElementById('edit_store_id').value = storeId;
      document.getElementById('edit_store_name').value = storeName;
      document.getElementById('edit_store_address').value = storeAddress;
    });
  </script>
</body>
</html>
