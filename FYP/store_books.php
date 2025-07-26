<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'db_connect.php';

$store_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($store_id <= 0) {
    echo "Invalid store.";
    exit();
}

// Fetch store info
$store_sql = "SELECT * FROM store WHERE store_id = ?";
$store_stmt = $conn->prepare($store_sql);
$store_stmt->bind_param('i', $store_id);
$store_stmt->execute();
$store_result = $store_stmt->get_result();
$store = $store_result->fetch_assoc();
if (!$store) {
    echo "Store not found.";
    exit();
}

// Fetch books
$books = [];
$book_sql = "SELECT b.*, sb.stock 
             FROM store_book sb
             JOIN book b ON sb.book_id = b.book_id
             WHERE sb.store_id = ?";
$book_stmt = $conn->prepare($book_sql);
$book_stmt->bind_param('i', $store_id);
$book_stmt->execute();
$book_result = $book_stmt->get_result();
while ($row = $book_result->fetch_assoc()) {
    $books[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($store['name']) ?> - Books</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Slab&display=swap');

    :root {
      --primary-color: #8B5E3C;
      --primary-color-hover: #6E4A2C;
      --light-bg: #fdf6e3;
      --light-text: #3C2F2F;
      --light-card-bg: #fff7eb;
      --light-muted-text: #6c757d;
      --dark-bg: #1B263B;
      --dark-text: #E6E1D3;
      --dark-card-bg: #2a354d;
      --dark-muted-text: #b0b7c3;
      --btn-bg-light: #8B5E3C;
      --btn-hover-bg-light: #6E4A2C;
      --btn-bg-dark: #B38B47;
      --btn-hover-bg-dark: #8A6B32;
    }

    body {
      font-family: 'Roboto Slab', serif;
      background-color: var(--light-bg);
      color: var(--light-text);
      transition: background 0.3s ease, color 0.3s ease;
      min-height: 100vh;
      padding-top: 60px;
    }

    body.dark-mode {
      background: var(--dark-bg);
      color: var(--dark-text);
    }

    h1 {
      color: var(--primary-color);
      font-weight: bold;
      transition: color 0.3s ease;
    }

    body.dark-mode h1 {
      color: var(--btn-bg-dark);
    }

    .card {
      background-color: var(--light-card-bg);
      border-radius: 0.75rem;
      box-shadow: 0 4px 15px rgba(139, 94, 60, 0.2);
      transition: background 0.3s ease, color 0.3s ease;
      color: inherit;
    }

    body.dark-mode .card {
      background-color: var(--dark-card-bg);
      box-shadow: 0 4px 15px rgba(212, 180, 131, 0.4);
      color: var(--dark-text);
    }

    /* Make card texts dark mode visible */
    .card-title,
    .card-text {
      transition: color 0.3s ease;
    }
    body.dark-mode .card-title,
    body.dark-mode .card-text {
      color: var(--dark-text);
    }

    /* Adjust muted text (like ISBN) */
    .text-muted {
      color: var(--light-muted-text) !important;
      transition: color 0.3s ease;
    }
    body.dark-mode .text-muted {
      color: var(--dark-muted-text) !important;
    }

    .btn-secondary {
      background-color: var(--btn-bg-light);
      border: none;
      color: white;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .btn-secondary:hover {
      background-color: var(--btn-hover-bg-light);
      color: white;
    }

    body.dark-mode .btn-secondary {
      background-color: var(--btn-bg-dark);
      color: white;
    }

    body.dark-mode .btn-secondary:hover {
      background-color: var(--btn-hover-bg-dark);
      color: white;
    }

    /* Dark Mode Toggle */
    #darkModeToggle {
      position: fixed;
      bottom: 1rem;
      right: 1rem;
      z-index: 1050;
      background-color: var(--primary-color);
      color: white;
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

<div class="container py-5">
    <h1 class="mb-4">📚 Books in <?= htmlspecialchars($store['name']) ?></h1>
    <a href="store.php" class="btn btn-secondary mb-4">&larr; Back to Stores</a>
    <div class="row">
        <?php if (count($books) > 0): ?>
            <?php foreach ($books as $book): ?>
                <div class="col-md-4">
                    <div class="card book-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($book['author']) ?></p>
                            <p class="card-text"><small class="text-muted">ISBN: <?= htmlspecialchars($book['isbn']) ?></small></p>
                            <p class="card-text">Price: <?= htmlspecialchars($book['price']) ?></p>
                            <p class="card-text fw-bold">Stock: <?= (int)$book['stock'] ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="alert alert-warning">No books found in this store.</p>
            </div>
        <?php endif; ?>
    </div>
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