<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'db_connect.php';

// Handle book addition
$add_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    if ($title && $author && $isbn && $price !== '' && $quantity >= 0) {
        $stmt = $conn->prepare("INSERT INTO book (title, author, isbn, price, warehouse_stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssdi', $title, $author, $isbn, $price, $quantity);
        if (!$stmt->execute()) {
            $add_error = 'Failed to add book.';
        }
        $stmt->close();
        header('Location: all_books.php');
        exit();
    } else {
        $add_error = 'All fields are required.';
    }
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $book_id = intval($_POST['book_id'] ?? 0);
    $new_quantity = intval($_POST['new_quantity'] ?? 0);
    if ($book_id > 0) {
        $stmt = $conn->prepare("UPDATE book SET warehouse_stock = ? WHERE book_id = ?");
        $stmt->bind_param('ii', $new_quantity, $book_id);
        $stmt->execute();
        $stmt->close();
        header('Location: all_books.php');
        exit();
    }
}

// Handle delete book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book'])) {
    $book_id = intval($_POST['book_id'] ?? 0);
    if ($book_id > 0) {
        $stmt = $conn->prepare("DELETE FROM store_book WHERE book_id = ?");
        $stmt->bind_param('i', $book_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM book WHERE book_id = ?");
        $stmt->bind_param('i', $book_id);
        $stmt->execute();
        $stmt->close();
        header('Location: all_books.php');
        exit();
    }
}

// Fetch books with optional search
$search_keyword = trim($_GET['search'] ?? '');
$books = [];

if ($search_keyword !== '') {
    $stmt = $conn->prepare("SELECT * FROM book WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? ORDER BY book_id DESC");
    $search_term = '%' . $search_keyword . '%';
    $stmt->bind_param('sss', $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM book ORDER BY book_id DESC");
}

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>All Books</title>

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
  </style>
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="container py-5">
    <h1>All Books</h1>

    <?php if ($add_error): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($add_error) ?></div>
    <?php endif; ?>

    <!-- Add Book Button -->
    <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addBookModal">➕ Add Book</button>

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1" aria-labelledby="addBookModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="addBookModalLabel">Add New Book</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" id="title" class="form-control" required />
              </div>
              <div class="mb-3">
                <label for="author" class="form-label">Author</label>
                <input type="text" name="author" id="author" class="form-control" required />
              </div>
              <div class="mb-3">
                <label for="isbn" class="form-label">ISBN</label>
                <input type="text" name="isbn" id="isbn" class="form-control" required />
              </div>
              <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" name="price" id="price" class="form-control" required />
              </div>
              <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" min="0" value="0" required />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="add_book" class="btn btn-primary">Add Book</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Search Form -->
    <form method="GET" class="mb-4">
      <div class="row g-2 align-items-end">
        <div class="col-md-10">
          <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search books by title, author, or ISBN..."
            value="<?= htmlspecialchars($search_keyword) ?>"
          />
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-outline-primary w-100">Search</button>
        </div>
      </div>
    </form>

    <!-- Books Grid -->
    <div class="row">
      <?php if (count($books) > 0): ?>
        <?php foreach ($books as $book): ?>
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">
                  <a href="transfer_book.php?book_id=<?= urlencode($book['book_id']) ?>">
                    <?= htmlspecialchars($book['title'] ?? 'Book') ?>
                  </a>
                </h5>
                <p class="card-text mb-1"><strong>Author:</strong> <?= htmlspecialchars($book['author'] ?? '') ?></p>
                <p class="card-text mb-1"><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn'] ?? '') ?></p>
                <p class="card-text mb-1"><strong>Price:</strong> <?= isset($book['price']) ? htmlspecialchars($book['price']) : 'N/A' ?></p>
                <p class="card-text"><strong>Warehouse Stock:</strong> <?= isset($book['warehouse_stock']) ? (int)$book['warehouse_stock'] : 0 ?></p>
                <div class="mt-auto d-flex flex-wrap gap-2">
                  <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#updateQuantityModal<?= $book['book_id'] ?>"
                  >Update Quantity</button>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this book?');">
                    <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>" />
                    <button type="submit" name="delete_book" class="btn btn-secondary btn-sm">Delete</button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Update Quantity Modal -->
            <div
              class="modal fade"
              id="updateQuantityModal<?= $book['book_id'] ?>"
              tabindex="-1"
              aria-labelledby="updateQuantityModalLabel<?= $book['book_id'] ?>"
              aria-hidden="true"
            >
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="POST">
                    <div class="modal-header">
                      <h5 class="modal-title" id="updateQuantityModalLabel<?= $book['book_id'] ?>">Update Warehouse Quantity</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>" />
                      <div class="mb-3">
                        <label for="new_quantity_<?= $book['book_id'] ?>" class="form-label">New Quantity</label>
                        <input
                          type="number"
                          id="new_quantity_<?= $book['book_id'] ?>"
                          name="new_quantity"
                          class="form-control"
                          min="0"
                          value="<?= (int)$book['warehouse_stock'] ?>"
                          required
                        />
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" name="update_quantity" class="btn btn-primary">Update</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center">No books found.</p>
      <?php endif; ?>
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