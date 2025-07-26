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
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Customer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

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

      --primary-color-dark: #D4B483;
      --primary-color-hover-dark: #BBA15D;
      --text-color-light: #E6E1D3;
      --card-bg-dark: #1B263B;
      --shadow-color-dark: rgba(212, 180, 131, 0.6);
      --btn-bg-dark: #B38B47;
      --btn-hover-bg-dark: #8A6B32;
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
      background: linear-gradient(135deg, #1B263B, #121C2F);
      color: var(--text-color-light);
    }

    h2 {
      text-align: center;
      margin-bottom: 2rem;
      color: var(--primary-color);
      font-weight: 700;
    }

    form {
      background-color: var(--card-bg-light);
      box-shadow: 0 4px 15px var(--shadow-color-light);
    }

    body.dark-mode form {
      background-color: var(--card-bg-dark);
      box-shadow: 0 4px 15px var(--shadow-color-dark);
    }

    label {
      font-weight: 600;
    }

    .btn-primary {
      background-color: var(--btn-bg-light);
      border: none;
    }

    .btn-primary:hover {
      background-color: var(--btn-hover-bg-light);
    }

    .btn-secondary {
      background-color: #aaa;
      border: none;
    }

    body.dark-mode .btn-primary {
      background-color: var(--btn-bg-dark);
    }

    body.dark-mode .btn-primary:hover {
      background-color: var(--btn-hover-bg-dark);
    }

    /* Toggle Button */
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
    <h2>✏️ Edit Customer</h2>
    <form action="update_customer.php" method="POST" class="p-4 border rounded shadow-sm">
      <input type="hidden" name="customer_id" value="<?= $customer['customer_id'] ?>">
      <div class="mb-3">
        <label class="form-label">Customer Name</label>
        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($customer['email']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea class="form-control" name="address"><?= htmlspecialchars($customer['address']) ?></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Update Customer</button>
      <a href="view_customers.php" class="btn btn-secondary">Back</a>
    </form>
  </div>

  <footer class="text-center mt-5">
  <p>&copy; <?= date('Y'); ?> Inkventory. All rights reserved.</p>

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