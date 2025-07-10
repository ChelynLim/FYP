<?php
// transfer_book.php
require_once 'db_connect.php';

$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
if ($book_id <= 0) {
    die('Invalid book ID.');
}

// Fetch book info
$stmt = $conn->prepare('SELECT * FROM book WHERE book_id = ?');
$stmt->bind_param('i', $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
if (!$book) {
    die('Book not found.');
}

// Fetch stores (example: assuming a 'store' table exists)
$stores = [];
$store_result = $conn->query('SELECT store_id, name FROM store');
if ($store_result) {
    while ($row = $store_result->fetch_assoc()) {
        $stores[] = $row;
    }
}

// Fetch delivery persons
$delivery_persons = [];
$dp_result = $conn->query('SELECT delivery_person_id, name FROM delivery_person');
if ($dp_result) {
    while ($row = $dp_result->fetch_assoc()) {
        $delivery_persons[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_id = intval($_POST['store_id'] ?? 0);
    $delivery_person_id = intval($_POST['delivery_person_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    if ($store_id > 0 && $delivery_person_id > 0 && $quantity > 0) {
        // Check current warehouse stock
        $stmt = $conn->prepare('SELECT warehouse_stock FROM book WHERE book_id = ?');
        $stmt->bind_param('i', $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $current_stock = $row ? (int)$row['warehouse_stock'] : 0;
        $stmt->close();
        if ($current_stock >= $quantity) {
            // Decrease warehouse stock
            $stmt = $conn->prepare('UPDATE book SET warehouse_stock = warehouse_stock - ? WHERE book_id = ?');
            $stmt->bind_param('ii', $quantity, $book_id);
            $stmt->execute();
            $stmt->close();
            // Add to store_book (insert or update)
            $stmt = $conn->prepare('SELECT stock FROM store_book WHERE store_id = ? AND book_id = ?');
            $stmt->bind_param('ii', $store_id, $book_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // Update existing
                $stmt2 = $conn->prepare('UPDATE store_book SET stock = stock + ? WHERE store_id = ? AND book_id = ?');
                $stmt2->bind_param('iii', $quantity, $store_id, $book_id);
                $stmt2->execute();
                $stmt2->close();
            } else {
                // Insert new
                $stmt2 = $conn->prepare('INSERT INTO store_book (store_id, book_id, stock) VALUES (?, ?, ?)');
                $stmt2->bind_param('iii', $store_id, $book_id, $quantity);
                $stmt2->execute();
                $stmt2->close();
            }
            $stmt->close();
            // Insert a new delivery record for this transfer
            $stmt = $conn->prepare('INSERT INTO deliveries (book_id, store_id, quantity, status, delivery_person_id, date_received) VALUES (?, ?, ?, ?, ?, NULL)');
            $status = 'pending';
            $stmt->bind_param('iiisi', $book_id, $store_id, $quantity, $status, $delivery_person_id);
            $stmt->execute();
            if ($stmt->error) {
                echo '<div class="alert alert-danger">SQL Error: ' . htmlspecialchars($stmt->error) . '</div>';
            }
            $stmt->close();
            // Redirect to all_books.php
            header('Location: all_books.php');
            exit();
        } else {
            echo '<div class="alert alert-danger">Not enough stock in warehouse.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Please select a store, delivery person, and quantity.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transfer Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Transfer Book: <?php echo htmlspecialchars($book['title']); ?></h2>
    <form method="post">
        <div class="mb-3">
            <label for="store_id" class="form-label">Select Store</label>
            <select name="store_id" id="store_id" class="form-select" required>
                <option value="">Choose store</option>
                <?php foreach ($stores as $store): ?>
                    <option value="<?php echo $store['store_id']; ?>"><?php echo htmlspecialchars($store['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="delivery_person_id" class="form-label">Select Delivery Person</label>
            <select name="delivery_person_id" id="delivery_person_id" class="form-select" required>
                <option value="">Choose delivery person</option>
                <?php foreach ($delivery_persons as $dp): ?>
                    <option value="<?php echo $dp['delivery_person_id']; ?>"><?php echo htmlspecialchars($dp['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
        </div>
        <button type="submit" class="btn btn-primary">Transfer</button>
        <a href="all_books.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
<?php $conn->close(); ?>
