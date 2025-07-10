<?php
// store_books.php
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

// Fetch books for this store (using store_book and book tables)
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
<html>
<head>
    <title><?php echo htmlspecialchars($store['name']); ?> - Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .book-card { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Books in <?php echo htmlspecialchars($store['name']); ?></h1>
        <a href="homepage.php" class="btn btn-secondary mb-4">&larr; Back to Stores</a>
        <div class="row">
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <div class="col-md-4">
                        <div class="card book-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title'] ?? 'Book'); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($book['author'] ?? ''); ?></p>
                                <p class="card-text"><small class="text-muted">ISBN: <?php echo htmlspecialchars($book['isbn'] ?? ''); ?></small></p>
                                <p class="card-text">Price: <?php echo isset($book['price']) ? htmlspecialchars($book['price']) : 'N/A'; ?></p>
                                <p class="card-text">Stock: <?php echo isset($book['stock']) ? (int)$book['stock'] : 0; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No books found in this store.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
