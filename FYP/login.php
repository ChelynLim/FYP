<?php
session_start();
if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db_connect.php';

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin'] = $username;
            $_SESSION['user_id'] = $row['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Admin not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Login - Book System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Slab&display=swap');

    :root {
      /* Leather & Ink Light Theme */
      --primary-color-light: #8B5E3C;
      --primary-hover-light: #6E4A2C;
      --text-color-light: #3C2F2F;
      --bg-light: #fdf6e3;
      --form-bg-light: #fffdf6;
      --shadow-light: rgba(139, 94, 60, 0.2);
      --btn-bg-light: #8B5E3C;
      --btn-hover-light: #6E4A2C;

      /* Deep Navy & Brass Dark Theme */
      --primary-color-dark: #D4B483;
      --primary-hover-dark: #BBA15D;
      --text-color-dark: #E6E1D3;
      --bg-dark-start: #1B263B;
      --bg-dark-end: #121C2F;
      --form-bg-dark: #2A2E44;
      --shadow-dark: rgba(212, 180, 131, 0.6);
      --btn-bg-dark: #B38B47;
      --btn-hover-dark: #8A6B32;
    }

    /* Ensure full height for flex layout */
    html, body {
      height: 100%;
      margin: 0;
      font-family: 'Roboto Slab', serif;
      background-color: var(--bg-light);
      color: var(--text-color-light);
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    body.dark-mode {
      background: linear-gradient(135deg, var(--bg-dark-start), var(--bg-dark-end));
      color: var(--text-color-dark);
    }

    .page-wrapper {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center; /* center vertically */
      align-items: center; /* center horizontally */
      padding: 2rem;
      box-sizing: border-box;
    }

    .login-box {
      background-color: var(--form-bg-light);
      padding: 2.5rem;
      border-radius: 1rem;
      box-shadow: 0 4px 20px var(--shadow-light);
      max-width: 400px;
      width: 100%;
      transition: background-color 0.3s ease, box-shadow 0.3s ease, color 0.3s ease;
      margin-bottom: auto; /* push footer down */
    }

    body.dark-mode .login-box {
      background-color: var(--form-bg-dark);
      box-shadow: 0 4px 20px var(--shadow-dark);
      color: var(--text-color-dark);
    }

    h3 {
      text-align: center;
      color: var(--primary-color-light);
      margin-bottom: 1.5rem;
      font-weight: 700;
      letter-spacing: 1px;
    }

    body.dark-mode h3 {
      color: var(--primary-color-dark);
    }

    .form-label {
      color: inherit;
      font-weight: 600;
    }

    .form-control {
      font-family: 'Roboto Slab', serif;
      background-color: #fff;
      border: 2px solid #e0d8c3;
      border-radius: 0.5rem;
      transition: all 0.3s ease;
      color: var(--text-color-light);
    }

    .form-control:focus {
      border-color: var(--primary-color-light);
      box-shadow: 0 0 0 0.25rem rgba(139, 94, 60, 0.25);
      outline: none;
    }

    body.dark-mode .form-control {
      background-color: #1f1f2d;
      border-color: #555;
      color: var(--text-color-dark);
    }

    body.dark-mode .form-control:focus {
      border-color: var(--primary-hover-dark);
      box-shadow: 0 0 0 0.25rem rgba(187, 161, 93, 0.25);
      background-color: #1f1f2d;
    }

    .btn-primary {
      background-color: var(--btn-bg-light);
      border: none;
      border-radius: 0.5rem;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: background-color 0.3s ease;
      color: white;
    }

    .btn-primary:hover {
      background-color: var(--btn-hover-light);
      color: var(--text-color-light);
    }

    body.dark-mode .btn-primary {
      background-color: var(--btn-hover-dark);
      color: var(--text-color-dark);
    }

    body.dark-mode .btn-primary:hover {
      background-color: var(--btn-bg-dark);
      color: white;
    }

    .alert-danger {
      background-color: #b00020;
      color: white;
      border-radius: 0.5rem;
      margin-bottom: 1rem;
    }

    body.dark-mode .alert-danger {
      background-color: #cf6679;
      color: #1b1b1b;
    }

    /* Footer styling */
    .footer {
      margin-top: auto; /* push footer to bottom */
      padding: 1rem 0;
      color: var(--text-color-light);
      font-size: 0.9rem;
      width: 100%;
      text-align: center;
    }

    body.dark-mode .footer {
      color: var(--text-color-dark);
    }

    /* Dark mode toggle button */
    #darkModeToggle {
      position: fixed;
      top: 1rem;
      right: 1rem;
      z-index: 999;
      border: none;
      background-color: var(--btn-bg-light);
      color: white;
      border-radius: 50%;
      width: 48px;
      height: 48px;
      font-size: 1.3rem;
      box-shadow: 0 0 10px var(--btn-bg-light);
      transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
    }

    #darkModeToggle:hover {
      background-color: var(--btn-hover-light);
      color: var(--text-color-light);
    }

    body.dark-mode #darkModeToggle {
      background-color: var(--btn-bg-dark);
      color: var(--text-color-dark);
      box-shadow: 0 0 10px var(--btn-bg-dark);
    }

    body.dark-mode #darkModeToggle:hover {
      background-color: var(--btn-hover-dark);
      color: white;
    }
  </style>
</head>
<body>

  <div class="page-wrapper">

    <!-- Dark Mode Toggle -->
    <button id="darkModeToggle" title="Toggle dark mode" aria-label="Toggle dark mode">
      <i class="bi bi-brightness-high-fill"></i>
    </button>

    <!-- Login Box -->
    <div class="login-box">
      <h3>Login</h3>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" novalidate>
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            class="form-control"
            placeholder="Enter username"
            required
            autofocus
            autocomplete="username"
          />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="Enter password"
            required
            autocomplete="current-password"
          />
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>

    <!-- Footer -->
    <footer class="footer">
      <p>&copy; <?= date('Y'); ?> Inkventory. All rights reserved.</p>
    </footer>

  </div>

  <script>
    const toggle = document.getElementById('darkModeToggle');
    const icon = toggle.querySelector('i');
    const body = document.body;

    // Initialize theme on load from localStorage
    if (localStorage.getItem('theme') === 'dark') {
      body.classList.add('dark-mode');
      icon.className = 'bi bi-moon-fill';
    }

    toggle.addEventListener('click', () => {
      body.classList.toggle('dark-mode');
      const isDark = body.classList.contains('dark-mode');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      icon.className = isDark ? 'bi bi-moon-fill' : 'bi bi-brightness-high-fill';
    });
  </script>
</body>
</html>
