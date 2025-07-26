<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_delivery_person'])) {
    $name = trim($_POST['dp_name']);
    $contact = trim($_POST['dp_contact']);
    $email = trim($_POST['dp_email']);
    if ($name && $contact && $email) {
        $stmt = $conn->prepare("INSERT INTO delivery_person (name, contact_number, email) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $contact, $email);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = 'Delivery person added.';
    }
    header('Location: delivery_person.php');
    exit();
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_delivery_person'])) {
    $id = intval($_POST['dp_id']);
    $name = trim($_POST['dp_name']);
    $contact = trim($_POST['dp_contact']);
    $email = trim($_POST['dp_email']);
    if ($id && $name && $contact && $email) {
        $stmt = $conn->prepare("UPDATE delivery_person SET name = ?, contact_number = ?, email = ? WHERE delivery_person_id = ?");
        $stmt->bind_param('sssi', $name, $contact, $email, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = 'Delivery person updated.';
    }
    header('Location: delivery_person.php');
    exit();
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_delivery_person'])) {
    $id = intval($_POST['dp_id']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM delivery_person WHERE delivery_person_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = 'Delivery person deleted.';
    }
    header('Location: delivery_person.php');
    exit();
}

// Search
$search_keyword = trim($_GET['search'] ?? '');
$deliveryPersons = [];

if ($search_keyword !== '') {
    $stmt = $conn->prepare("SELECT * FROM delivery_person WHERE name LIKE ? OR email LIKE ? ORDER BY delivery_person_id DESC");
    $search_term = '%' . $search_keyword . '%';
    $stmt->bind_param('ss', $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM delivery_person ORDER BY delivery_person_id DESC");
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $deliveryPersons[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Delivery Persons</title>
  
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

    .btn-primary {
      background-color: var(--btn-bg-light);
      border: none;
      transition: background-color 0.3s ease;
      color: var(--background-light);
    }
    .btn-primary:hover {
      background-color: var(--btn-hover-bg-light);
    }
    body.dark-mode .btn-primary {
      background-color: var(--btn-bg-dark);
      color: var(--background-dark);
    }
    body.dark-mode .btn-primary:hover {
      background-color: var(--btn-hover-bg-dark);
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
      transition: background-color 0.3s ease, box-shadow 0.3s ease, color 0.3s ease;
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

    .modal-content {
      background-color: var(--modal-bg-light);
      border-radius: 1rem;
      transition: background-color 0.3s ease, color 0.3s ease;
      color: var(--text-color-dark);
    }
    body.dark-mode .modal-content {
      background-color: var(--modal-bg-dark);
      color: var(--text-color-light);
    }

    .modal-header {
      border-bottom: none;
    }

    .modal-title {
      color: var(--primary-color);
      transition: color 0.3s ease;
    }
    body.dark-mode .modal-title {
      color: var(--primary-color-dark);
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

    /* Search form grid matching all_books.php */
    form.search-form {
      margin-bottom: 1.5rem;
    }
    form.search-form .row > div {
      padding-left: 0.25rem;
      padding-right: 0.25rem;
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container py-5">
  <h1>Delivery Persons</h1>

  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success text-center"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
  <?php endif; ?>

  <!-- Search -->
  <form method="GET" class="search-form">
    <div class="row g-2 align-items-end">
      <div class="col-md-10">
        <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($search_keyword) ?>">
      </div>
      <div class="col-md-2">
        <button class="btn btn-outline-primary w-100" type="submit">Search</button>
      </div>
    </div>
  </form>

  <!-- Add Button -->
  <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addDeliveryModal">
    <i class="bi bi-plus-lg"></i> Add Delivery Person
  </button>

  <!-- Cards -->
  <div class="row">
    <?php if (count($deliveryPersons) > 0): ?>
      <?php foreach ($deliveryPersons as $person): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 p-3">
            <h5><?= htmlspecialchars($person['name']) ?></h5>
            <p>Contact: <?= htmlspecialchars($person['contact_number']) ?></p>
            <p>Email: <?= htmlspecialchars($person['email']) ?></p>
            <div class="mt-auto d-flex flex-wrap gap-2">
              <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $person['delivery_person_id'] ?>">
                <i class="bi bi-pencil-square"></i> Edit
              </button>
              <form method="POST" class="d-inline" onsubmit="return confirm('Delete this person?');">
                <input type="hidden" name="dp_id" value="<?= $person['delivery_person_id'] ?>">
                <button type="submit" name="delete_delivery_person" class="btn btn-secondary btn-sm">
                  <i class="bi bi-trash"></i> Delete
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?= $person['delivery_person_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $person['delivery_person_id'] ?>" aria-hidden="true">
          <div class="modal-dialog">
            <form method="POST" class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?= $person['delivery_person_id'] ?>">Edit Delivery Person</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="dp_id" value="<?= $person['delivery_person_id'] ?>">
                <div class="mb-3">
                  <label for="dp_name_<?= $person['delivery_person_id'] ?>" class="form-label">Name</label>
                  <input type="text" id="dp_name_<?= $person['delivery_person_id'] ?>" name="dp_name" class="form-control" value="<?= htmlspecialchars($person['name']) ?>" required>
                </div>
                <div class="mb-3">
                  <label for="dp_contact_<?= $person['delivery_person_id'] ?>" class="form-label">Contact</label>
                  <input type="text" id="dp_contact_<?= $person['delivery_person_id'] ?>" name="dp_contact" class="form-control" value="<?= htmlspecialchars($person['contact_number']) ?>" required>
                </div>
                <div class="mb-3">
                  <label for="dp_email_<?= $person['delivery_person_id'] ?>" class="form-label">Email</label>
                  <input type="email" id="dp_email_<?= $person['delivery_person_id'] ?>" name="dp_email" class="form-control" value="<?= htmlspecialchars($person['email']) ?>" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" name="edit_delivery_person" class="btn btn-primary">
                  <i class="bi bi-save"></i> Save
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center">No delivery persons found.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addDeliveryModal" tabindex="-1" aria-labelledby="addDeliveryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addDeliveryModalLabel">Add Delivery Person</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="dp_name_new" class="form-label">Name</label>
          <input type="text" id="dp_name_new" name="dp_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="dp_contact_new" class="form-label">Contact</label>
          <input type="text" id="dp_contact_new" name="dp_contact" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="dp_email_new" class="form-label">Email</label>
          <input type="email" id="dp_email_new" name="dp_email" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="add_delivery_person" class="btn btn-primary">
          <i class="bi bi-plus-lg"></i> Add
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<footer class="text-center mt-5">
  <p>&copy; <?= date('Y'); ?> Inkventory. All rights reserved.</p>

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
</script>
</body>
</html>
