<?php
// transfer_book.php
session_start();
require_once 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check user session (adjust if you want admin/user)
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get and validate book_id
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
if ($book_id <= 0) {
    die('Invalid book ID.');
}

// Fetch book info (title and current warehouse stock)
$stmt = $conn->prepare('SELECT title, warehouse_stock FROM book WHERE book_id = ?');
$stmt->bind_param('i', $book_id);
$stmt->execute();
$stmt->bind_result($book_title, $current_stock);
if (!$stmt->fetch()) {
    die('Book not found.');
}
$stmt->close();

// Fetch all stores (store_id, name)
$stores = [];
$store_result = $conn->query('SELECT store_id, name FROM store ORDER BY name');
if ($store_result) {
    while ($row = $store_result->fetch_assoc()) {
        $stores[] = $row;
    }
}

// Fetch all delivery persons (delivery_person_id, name)
$delivery_persons = [];
$dp_result = $conn->query('SELECT delivery_person_id, name FROM delivery_person ORDER BY name');
if ($dp_result) {
    while ($row = $dp_result->fetch_assoc()) {
        $delivery_persons[] = $row;
    }
}

$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_id = intval($_POST['store_id'] ?? 0);
    $delivery_person_id = intval($_POST['delivery_person_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);

    if ($store_id > 0 && $delivery_person_id > 0 && $quantity > 0) {
        if ($current_stock >= $quantity) {
            // Decrement warehouse stock
            $stmt = $conn->prepare('UPDATE book SET warehouse_stock = warehouse_stock - ? WHERE book_id = ?');
            $stmt->bind_param('ii', $quantity, $book_id);
            if (!$stmt->execute()) {
                $alert = '<div class="alert alert-danger">Failed to update warehouse stock.</div>';
            }
            $stmt->close();

            // Insert new delivery record with status 'pending'
            $stmt = $conn->prepare(
                'INSERT INTO deliveries (book_id, store_id, quantity, status, delivery_person_id, date_received)
                 VALUES (?, ?, ?, ?, ?, NULL)'
            );
            $status = 'pending';
            $stmt->bind_param('iiisi', $book_id, $store_id, $quantity, $status, $delivery_person_id);
            if ($stmt->execute()) {
                // Success - redirect or show message
                header('Location: all_books.php');
                exit();
            } else {
                $alert = '<div class="alert alert-danger">Failed to create delivery record.</div>';
            }
            $stmt->close();
        } else {
            $alert = '<div class="alert alert-danger">Not enough stock in warehouse.</div>';
        }
    } else {
        $alert = '<div class="alert alert-danger">Please select a store, delivery person, and enter a valid quantity.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Transfer Book: <?= htmlspecialchars($book_title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    :root {
      --primary-color: #8B5E3C;
      --primary-color-hover: #6E4A2C;
      --light-bg: #fdf6e3;
      --light-text: #3C2F2F;
      --light-card-bg: #fff7eb;
      --light-border-color: #8B5E3C;

      --dark-bg: #1B263B;
      --dark-text: #E6E1D3;
      --dark-card-bg: #2a354d;
      --dark-border-color: #d4b483;

      --danger-color: #a33e3e;
      --danger-hover: #7d2c2c;

      --success-color: #3e7d3e;
      --success-hover: #2c5d2c;
    }

    body {
      font-family: 'Roboto Slab', serif;
      background-color: var(--light-bg);
      color: var(--light-text);
      min-height: 100vh;
      padding-top: 60px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    body.dark-mode {
      background-color: var(--dark-bg);
      color: var(--dark-text);
    }

    h2 {
      color: var(--primary-color);
      font-weight: 700;
      transition: color 0.3s ease;
      margin-bottom: 1.5rem;
    }
    body.dark-mode h2 {
      color: var(--dark-border-color);
    }

    .container {
      max-width: 600px;
      margin: auto;
      background-color: var(--light-card-bg);
      border: 2px solid var(--light-border-color);
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 2px 8px rgb(139 94 60 / 0.25);
      transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }
    body.dark-mode .container {
      background-color: var(--dark-card-bg);
      border-color: var(--dark-border-color);
      box-shadow: 0 2px 12px rgb(211 180 131 / 0.3);
      color: var(--dark-text);
    }

    label {
      font-weight: 600;
      color: var(--primary-color);
      transition: color 0.3s ease;
    }
    body.dark-mode label {
      color: var(--dark-border-color);
    }

    input.form-control,
    select.form-select {
      border-radius: 0.5rem;
      border: 2px solid var(--light-border-color);
      color: var(--light-text);
      background-color: var(--light-bg);
      transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }
    input.form-control::placeholder {
      color: #8B7D72;
      transition: color 0.3s ease;
    }
    input.form-control:focus,
    select.form-select:focus {
      border-color: var(--primary-color-hover);
      box-shadow: 0 0 6px var(--primary-color-hover);
      background-color: var(--light-bg);
      color: var(--light-text);
      outline: none;
    }
    body.dark-mode input.form-control,
    body.dark-mode select.form-select {
      background-color: var(--dark-card-bg);
      border-color: var(--dark-border-color);
      color: var(--dark-text);
    }
    body.dark-mode input.form-control::placeholder {
      color: #b7aa8f;
    }
    body.dark-mode input.form-control:focus,
    body.dark-mode select.form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 6px var(--primary-color);
      background-color: var(--dark-card-bg);
      color: var(--dark-text);
      outline: none;
    }

    small {
      color: #5a4a3a; /* Light mode stock text color */
      transition: color 0.3s ease;
    }
    body.dark-mode small {
      color: #e6e1d3; /* Light text for dark mode */
    }

    .alert {
      padding: 1rem;
      border-radius: 0.5rem;
      margin-bottom: 1.5rem;
      font-weight: 600;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #842029;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    body.dark-mode .alert-danger {
      background-color: #661b1e;
      color: #f8d7da;
    }

    .btn-primary {
      background-color: var(--primary-color);
      border: none;
      color: var(--light-bg);
      border-radius: 0.5rem;
      transition: background-color 0.3s ease;
    }
    .btn-primary:hover {
      background-color: var(--primary-color-hover);
      color: var(--light-bg);
    }

    .btn-secondary {
      background-color: #999;
      border: none;
      color: var(--light-bg);
      border-radius: 0.5rem;
      transition: background-color 0.3s ease;
    }
    .btn-secondary:hover {
      background-color: #777;
      color: var(--light-bg);
    }

    /* Dark Mode Toggle Button */
    #darkModeToggle {
      position: fixed;
      bottom: 1rem;
      right: 1rem;
      z-index: 1050;
      background-color: var(--primary-color);
      color: var(--light-bg);
      border: none;
      border-radius: 50%;
      width: 48px;
      height: 48px;
      font-size: 1.5rem;
      box-shadow: 0 0 10px var(--primary-color);
      cursor: pointer;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }
    #darkModeToggle:hover {
      background-color: var(--primary-color-hover);
      box-shadow: 0 0 15px var(--primary-color-hover);
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Transfer Book: <?= htmlspecialchars($book_title) ?></h2>

  <?= $alert ?>

  <form method="post" novalidate>
    <div class="mb-3">
      <label for="store_id" class="form-label">Select Store</label>
      <select name="store_id" id="store_id" class="form-select" required>
        <option value="">Choose store</option>
        <?php foreach ($stores as $store): ?>
          <option value="<?= $store['store_id'] ?>"><?= htmlspecialchars($store['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="delivery_person_id" class="form-label">Select Delivery Person</label>
      <select name="delivery_person_id" id="delivery_person_id" class="form-select" required>
        <option value="">Choose delivery person</option>
        <?php foreach ($delivery_persons as $dp): ?>
          <option value="<?= $dp['delivery_person_id'] ?>"><?= htmlspecialchars($dp['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="quantity" class="form-label">Quantity</label>
      <input
        type="number"
        name="quantity"
        id="quantity"
        class="form-control"
        min="1"
        max="<?= (int)$current_stock ?>"
        value="1"
        required
      />
      <small>Current warehouse stock: <?= (int)$current_stock ?></small>
    </div>

    <button type="submit" class="btn btn-primary">Create Delivery</button>
    <a href="all_books.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

  <footer class="text-center mt-5">
  <p>&copy; <?= date('Y'); ?> Inkventory. All rights reserved.</p>

<!-- Dark Mode Toggle Button -->
<button id="darkModeToggle" aria-label="Toggle Dark/Light Mode" title="Toggle Dark/Light Mode">
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