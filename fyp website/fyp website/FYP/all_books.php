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
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    if ($title && $author && $isbn && $price !== '' && $quantity >= 0) {
        $stmt = $conn->prepare("INSERT INTO book (title, author, isbn, price, warehouse_stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssdi', $title, $author, $isbn, $price, $quantity);
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
        // Delete from store_book first to satisfy foreign key constraint
        $stmt = $conn->prepare("DELETE FROM store_book WHERE book_id = ?");
        $stmt->bind_param('i', $book_id);
        $stmt->execute();
        $stmt->close();
        // Now delete from book
        $stmt = $conn->prepare("DELETE FROM book WHERE book_id = ?");
        $stmt->bind_param('i', $book_id);
        $stmt->execute();
        $stmt->close();
        header('Location: all_books.php');
        exit();
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
                  <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="0" value="0" required>
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
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateQuantityModal<?php echo $book['book_id']; ?>">Update Quantity</button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <button type="submit" name="delete_book" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                                <!-- Update Quantity Modal -->
                                <div class="modal fade" id="updateQuantityModal<?php echo $book['book_id']; ?>" tabindex="-1" aria-labelledby="updateQuantityModalLabel<?php echo $book['book_id']; ?>" aria-hidden="true">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <form method="POST">
                                        <div class="modal-header">
                                          <h5 class="modal-title" id="updateQuantityModalLabel<?php echo $book['book_id']; ?>">Update Warehouse Quantity</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                          <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                          <div class="mb-3">
                                            <label class="form-label">New Quantity</label>
                                            <input type="number" name="new_quantity" class="form-control" min="0" value="<?php echo isset($book['warehouse_stock']) ? (int)$book['warehouse_stock'] : 0; ?>" required>
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
