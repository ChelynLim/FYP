<?php
// all_books.php
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
    if ($title && $author && $isbn && $price !== '') {
        $stmt = $conn->prepare("INSERT INTO book (title, author, isbn, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssd', $title, $author, $isbn, $price);
        if (!$stmt->execute()) {
            $add_error = 'Failed to add book.';
        }
        $stmt->close();
        // Refresh to clear POST and show new book
        header('Location: all_books.php');
        exit();
    } else {
        $add_error = 'All fields are required.';
    }
}

// Fetch all books
$books = [];
$sql = "SELECT * FROM book ORDER BY book_id DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}
// Do not close the connection here; close it at the very end after all DB usage
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .book-card { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container py-5">
        <h1 class="mb-4">All Books</h1>
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addBookModal">Add Book</button>
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
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Author</label>
                    <input type="text" name="author" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">ISBN</label>
                    <input type="text" name="isbn" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
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
        <div class="row">
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <div class="col-md-4">
                        <div class="card book-card h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="transfer_book.php?book_id=<?php echo urlencode($book['book_id']); ?>">
                                        <?php echo htmlspecialchars($book['title'] ?? 'Book'); ?>
                                    </a>
                                </h5>
                                <p class="card-text">Author: <?php echo htmlspecialchars($book['author'] ?? ''); ?></p>
                                <p class="card-text">ISBN: <?php echo htmlspecialchars($book['isbn'] ?? ''); ?></p>
                                <p class="card-text">Price: <?php echo isset($book['price']) ? htmlspecialchars($book['price']) : 'N/A'; ?></p>
                                <p class="card-text">Warehouse Stock: <?php echo isset($book['warehouse_stock']) ? (int)$book['warehouse_stock'] : 100; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No books found.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Now close the connection at the very end
$conn->close();
?>
