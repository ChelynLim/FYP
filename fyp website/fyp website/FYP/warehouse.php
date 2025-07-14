<?php
// warehouse.php
session_start();
require_once 'db_connect.php';

// Fetch deliveries (all statuses)
$deliveries = [];
$sql = "SELECT d.delivery_id, st.name AS store, b.title AS book, d.quantity, d.date_received, d.status, dp.name AS delivery_person, dp.contact_number, dp.email
        FROM deliveries d
        JOIN store st ON d.store_id = st.store_id
        JOIN book b ON d.book_id = b.book_id
        LEFT JOIN delivery_person dp ON d.delivery_person_id = dp.delivery_person_id
        ORDER BY d.date_received DESC, d.delivery_id DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $deliveries[] = $row;
    }
}

// Fetch stock per store
$store_stock = [];
$sql = "SELECT st.name AS store, b.title AS book, sb.stock
        FROM store_book sb
        JOIN store st ON sb.store_id = st.store_id
        JOIN book b ON sb.book_id = b.book_id
        ORDER BY st.name, b.title";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $store_stock[] = $row;
    }
}

// Fetch warehouse stock
$warehouse_stock = [];
$sql = "SELECT title, warehouse_stock FROM book ORDER BY title";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $warehouse_stock[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Warehouse Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container py-5">
    <h1 class="mb-4">Warehouse Management</h1>
    <h3>Deliveries</h3>
    <?php if (isset($_POST['update_status']) && isset($_POST['delivery_id'], $_POST['new_status'])) {
        $delivery_id = intval($_POST['delivery_id']);
        $new_status = $_POST['new_status'];
        if (in_array($new_status, ['pending', 'delivered', 'failed'])) {
            require 'db_connect.php';
            $stmt = $conn->prepare("UPDATE deliveries SET status = ? WHERE delivery_id = ?");
            $stmt->bind_param('si', $new_status, $delivery_id);
            $stmt->execute();
            $stmt->close();
            $conn->close();
            echo '<div class="alert alert-success">Status updated.</div>';
            echo '<meta http-equiv="refresh" content="1">'; // Refresh to show update
        }
    }
    if (isset($_POST['mark_complete']) && isset($_POST['delivery_id'])) {
        $delivery_id = intval($_POST['delivery_id']);
        require 'db_connect.php';
        $stmt = $conn->prepare("UPDATE deliveries SET status = 'delivered', date_received = NOW() WHERE delivery_id = ?");
        $stmt->bind_param('i', $delivery_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        echo '<div class="alert alert-success">Delivery marked as complete.</div>';
        echo '<meta http-equiv="refresh" content="1">';
    }
    if (isset($_POST['delete_delivery']) && isset($_POST['delivery_id'])) {
        $delivery_id = intval($_POST['delivery_id']);
        require 'db_connect.php';
        // Fetch delivery details
        $stmt = $conn->prepare("SELECT book_id, store_id, quantity, status FROM deliveries WHERE delivery_id = ?");
        $stmt->bind_param('i', $delivery_id);
        $stmt->execute();
        $stmt->bind_result($book_id, $store_id, $quantity, $status);
        if ($stmt->fetch()) {
            $stmt->close();
            // Return stock to warehouse
            $stmt = $conn->prepare("UPDATE book SET warehouse_stock = warehouse_stock + ? WHERE book_id = ?");
            $stmt->bind_param('ii', $quantity, $book_id);
            $stmt->execute();
            $stmt->close();
            // Remove from store_book
            $stmt = $conn->prepare("UPDATE store_book SET stock = stock - ? WHERE store_id = ? AND book_id = ?");
            $stmt->bind_param('iii', $quantity, $store_id, $book_id);
            $stmt->execute();
            $stmt->close();
            // Optionally, delete store_book row if stock is 0 or less
            $stmt = $conn->prepare("DELETE FROM store_book WHERE store_id = ? AND book_id = ? AND stock <= 0");
            $stmt->bind_param('ii', $store_id, $book_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt->close();
        }
        // Delete the delivery
        $stmt = $conn->prepare("DELETE FROM deliveries WHERE delivery_id = ?");
        $stmt->bind_param('i', $delivery_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        echo '<div class="alert alert-success">Delivery deleted and stock reverted.</div>';
        echo '<meta http-equiv="refresh" content="1">';
    }
    ?>
    <table class="table table-bordered table-striped">
        <thead><tr><th>ID</th><th>Store</th><th>Book</th><th>Quantity</th><th>Date Received</th><th>Delivery Person</th><th>Contact</th><th>Email</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($deliveries as $d): ?>
            <tr>
                <td><?= $d['delivery_id'] ?></td>
                <td><?= htmlspecialchars($d['store']) ?></td>
                <td><?= htmlspecialchars($d['book']) ?></td>
                <td><?= $d['quantity'] ?></td>
                <td><?= $d['date_received'] ?? '-' ?></td>
                <td><?= htmlspecialchars($d['delivery_person'] ?? '-') ?></td>
                <td><?= htmlspecialchars($d['contact_number'] ?? '-') ?></td>
                <td><?= htmlspecialchars($d['email'] ?? '-') ?></td>
                <td><?= ucfirst($d['status']) ?></td>
                <td>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="delivery_id" value="<?= $d['delivery_id'] ?>">
                    <input type="hidden" name="mark_complete" value="1">
                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this delivery as complete?')">Mark as Complete</button>
                  </form>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="delivery_id" value="<?= $d['delivery_id'] ?>">
                    <select name="new_status" class="form-select form-select-sm d-inline w-auto">
                      <option value="pending" <?= $d['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                      <option value="delivered" <?= $d['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                      <option value="failed" <?= $d['status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                  </form>
                  <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this delivery?');">
                    <input type="hidden" name="delivery_id" value="<?= $d['delivery_id'] ?>">
                    <button type="submit" name="delete_delivery" class="btn btn-sm btn-danger">Delete</button>
                  </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($deliveries)): ?><tr><td colspan="10">No deliveries found.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <h3 class="mt-5">Book Transfer</h3>
    <table class="table table-bordered table-striped">
        <thead><tr><th>Store</th><th>Book</th><th>Stock</th></tr></thead>
        <tbody>
        <?php foreach ($store_stock as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['store']) ?></td>
                <td><?= htmlspecialchars($s['book']) ?></td>
                <td><?= $s['stock'] ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($store_stock)): ?><tr><td colspan="3">No store stock found.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <h3 class="mt-5">Warehouse Stock</h3>
    <table class="table table-bordered table-striped">
        <thead><tr><th>Book</th><th>Warehouse Stock</th></tr></thead>
        <tbody>
        <?php foreach ($warehouse_stock as $w): ?>
            <tr>
                <td><?= htmlspecialchars($w['title']) ?></td>
                <td><?= $w['warehouse_stock'] ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($warehouse_stock)): ?><tr><td colspan="2">No warehouse stock found.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
