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
  <title>Add Supplier</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Slab&display=swap');

    :root {
      /* Leather & Ink Light Theme Vars */
      --primary-color: #8B5E3C;
      --primary-color-hover: #6E4A2C;
      --text-color-dark: #3C2F2F;
      --card-bg-light: #fdf6e3;
      --shadow-color-light: rgba(139, 94, 60, 0.2);
      --btn-bg-light: #8B5E3C;
      --btn-hover-bg-light: #6E4A2C;

      /* Leather & Ink Dark Theme Vars */
      --primary-color-dark: #D4B483;
      --primary-color-hover-dark: #BBA15D;
      --text-color-dark-mode: #E6E1D3;
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
      min-height: 100vh;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    body.dark-mode {
      background: linear-gradient(135deg, #1B263B, #121C2F);
      color: var(--text-color-dark-mode);
    }

    h2 {
      color: var(--primary-color);
      font-weight: 700;
      margin-bottom: 1.5rem;
      text-align: center;
    }

    body.dark-mode h2 {
      color: var(--primary-color-dark);
    }

    form {
      background-color: var(--card-bg-light);
      padding: 2rem;
      border-radius: 0.8rem;
      box-shadow: 0 4px 15px var(--shadow-color-light);
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      max-width: 600px;
      margin: 0 auto 3rem auto;
    }

    body.dark-mode form {
      background-color: var(--card-bg-dark);
      box-shadow: 0 4px 15px var(--shadow-color-dark);
    }

    label {
      font-weight: 600;
      color: var(--text-color-dark);
      transition: color 0.3s ease;
    }

    body.dark-mode label {
      color: var(--text-color-dark-mode);
    }

    .form-control, .form-select, textarea {
      border-radius: 0.5rem;
      border: 1px solid var(--primary-color);
      transition: border-color 0.3s ease, background-color 0.3s ease, color 0.3s ease;
      color: var(--text-color-dark);
      background-color: var(--card-bg-light);
    }

    body.dark-mode .form-control, 
    body.dark-mode .form-select,
    body.dark-mode textarea {
      border-color: var(--primary-color-dark);
      background-color: var(--card-bg-dark);
      color: var(--text-color-dark-mode);
    }

    .btn-primary {
      background-color: var(--btn-bg-light);
      border: none;
      transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
      background-color: var(--btn-hover-bg-light);
    }

    body.dark-mode .btn-primary {
      background-color: var(--btn-bg-dark);
    }

    body.dark-mode .btn-primary:hover {
      background-color: var(--btn-hover-bg-dark);
    }

    .btn-secondary {
      background-color: #777;
      border: none;
      transition: background-color 0.3s ease;
    }

    .btn-secondary:hover {
      background-color: #555;
    }

    /* Dark/Light Mode Toggle Button */
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

    /* Alert styling */
    .alert {
      max-width: 600px;
      margin: 1rem auto;
      font-weight: 600;
      border-radius: 0.5rem;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Add Supplier</h2>

    <form action="add_supplier.php" method="POST" novalidate>
      <div class="mb-3">
        <label for="name" class="form-label">Supplier Name</label>
        <input type="text" id="name" name="name" class="form-control" required />
      </div>
      <div class="mb-3">
        <label for="contact_person" class="form-label">Contact Person</label>
        <input type="text" id="contact_person" name="contact_person" class="form-control" />
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" id="phone" name="phone" class="form-control" />
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-control" />
      </div>
      <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea id="address" name="address" class="form-control" rows="3"></textarea>
      </div>

      <button type="submit" class="btn btn-primary">Add Supplier</button>
      <a href="view_suppliers.php" class="btn btn-secondary">View Suppliers</a>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $_POST['name'], $_POST['contact_person'], $_POST['phone'], $_POST['email'], $_POST['address']);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Supplier added successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error adding supplier: " . htmlspecialchars($stmt->error) . "</div>";
        }
    }
    ?>
  </div>

  <footer class="text-center mt-5">
  <p>&copy; <?= date('Y'); ?> Inkventory. All rights reserved.</p>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Dark/Light Mode Toggle Button -->
  <button id="darkModeToggle" aria-label="Toggle dark/light mode" title="Toggle Dark/Light Mode">
    <i class="bi bi-moon-fill"></i>
  </button>

  <script>
    const darkModeToggle = document.getElementById('darkModeToggle');
    const icon = darkModeToggle.querySelector('i');

    function updateIcon(isLight) {
      icon.className = isLight ? 'bi bi-moon-fill' : 'bi bi-brightness-high-fill';
    }

    // Initialize mode from localStorage
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

