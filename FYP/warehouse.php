<?php
session_start();
require_once 'db_connect.php';

// Initialize message variables
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Handle update status form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $delivery_id = filter_var($_POST['delivery_id'] ?? '', FILTER_VALIDATE_INT);
    $new_status = trim($_POST['new_status'] ?? '');

    if ($delivery_id && in_array($new_status, ['pending', 'delivered', 'failed'])) {
        // Get current delivery info
        $stmt_current = $conn->prepare("SELECT status, quantity, store_id, book_id FROM deliveries WHERE delivery_id = ?");
        $stmt_current->bind_param('i', $delivery_id);
        $stmt_current->execute();
        $stmt_current->bind_result($current_status, $quantity, $store_id, $book_id);
        $stmt_current->fetch();
        $stmt_current->close();

        if ($current_status === 'delivered' && ($new_status === 'pending' || $new_status === 'failed')) {
            // Revert delivered to pending/failed: update status, clear date_received, decrease store stock
            $stmt = $conn->prepare("UPDATE deliveries SET status = ?, date_received = NULL WHERE delivery_id = ?");
            $stmt->bind_param('si', $new_status, $delivery_id);
            $success = $stmt->execute();
            $stmt->close();

            if ($success) {
                // Decrement stock, but not below 0
                $stmt_stock = $conn->prepare("UPDATE store_book SET stock = GREATEST(stock - ?, 0) WHERE store_id = ? AND book_id = ?");
                $stmt_stock->bind_param('iii', $quantity, $store_id, $book_id);
                $stmt_stock->execute();
                $stmt_stock->close();

                $_SESSION['message'] = 'Delivery status reverted. Store stock adjusted and date received cleared.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Failed to update delivery status.';
                $_SESSION['message_type'] = 'danger';
            }
        } else if ($new_status === 'delivered') {
            // Change to delivered: update status, date_received and increase store stock
            $stmt = $conn->prepare("UPDATE deliveries SET status = ?, date_received = NOW() WHERE delivery_id = ?");
            $stmt->bind_param('si', $new_status, $delivery_id);
            $success = $stmt->execute();

            if ($success) {
                // Add stock or insert new record if missing
                $stmt_details = $conn->prepare("SELECT book_id, store_id, quantity FROM deliveries WHERE delivery_id = ?");
                $stmt_details->bind_param('i', $delivery_id);
                $stmt_details->execute();
                $stmt_details->bind_result($book_id2, $store_id2, $quantity2);
                if ($stmt_details->fetch()) {
                    $stmt_details->close();

                    $stmt_update = $conn->prepare("UPDATE store_book SET stock = stock + ? WHERE store_id = ? AND book_id = ?");
                    $stmt_update->bind_param('iii', $quantity2, $store_id2, $book_id2);
                    $stmt_update->execute();

                    if ($stmt_update->affected_rows === 0) {
                        $stmt_insert = $conn->prepare("INSERT INTO store_book (store_id, book_id, stock) VALUES (?, ?, ?)");
                        $stmt_insert->bind_param('iii', $store_id2, $book_id2, $quantity2);
                        $stmt_insert->execute();
                        $stmt_insert->close();
                    }
                    $stmt_update->close();
                }
                $_SESSION['message'] = 'Delivery marked as delivered. Stock updated and date received set.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error updating status: ' . $stmt->error;
                $_SESSION['message_type'] = 'danger';
            }
            $stmt->close();
        } else {
            // Other status changes (pending <-> failed) just update status
            $stmt = $conn->prepare("UPDATE deliveries SET status = ? WHERE delivery_id = ?");
            $stmt->bind_param('si', $new_status, $delivery_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Delivery status updated.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error updating status: ' . $stmt->error;
                $_SESSION['message_type'] = 'danger';
            }
            $stmt->close();
        }
    } else {
        $_SESSION['message'] = 'Invalid data for status update.';
        $_SESSION['message_type'] = 'warning';
    }
    header('Location: warehouse.php');
    exit();
}

// Handle mark complete form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_complete'])) {
    $delivery_id = filter_var($_POST['delivery_id'] ?? '', FILTER_VALIDATE_INT);

    if ($delivery_id) {
        // Fetch delivery details
        $stmt = $conn->prepare("SELECT book_id, store_id, quantity, status FROM deliveries WHERE delivery_id = ?");
        $stmt->bind_param('i', $delivery_id);
        $stmt->execute();
        $stmt->bind_result($book_id, $store_id, $quantity, $current_status);
        $fetched = $stmt->fetch();
        $stmt->close();

        if ($fetched && $current_status !== 'delivered') {
            // Update delivery status and date_received
            $stmt = $conn->prepare("UPDATE deliveries SET status = 'delivered', date_received = NOW() WHERE delivery_id = ?");
            $stmt->bind_param('i', $delivery_id);
            $stmt->execute();
            $stmt->close();

            // Check if store_book entry exists
            $stmt = $conn->prepare("SELECT stock FROM store_book WHERE store_id = ? AND book_id = ?");
            $stmt->bind_param('ii', $store_id, $book_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // Update existing stock
                $stmt_update = $conn->prepare("UPDATE store_book SET stock = stock + ? WHERE store_id = ? AND book_id = ?");
                $stmt_update->bind_param('iii', $quantity, $store_id, $book_id);
            } else {
                // Insert new stock record
                $stmt_update = $conn->prepare("INSERT INTO store_book (store_id, book_id, stock) VALUES (?, ?, ?)");
                $stmt_update->bind_param('iii', $store_id, $book_id, $quantity);
            }
            $stmt_update->execute();
            $stmt_update->close();

            $_SESSION['message'] = 'Delivery marked as complete and stock updated in store.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Delivery not found or already completed.';
            $_SESSION['message_type'] = 'warning';
        }
    } else {
        $_SESSION['message'] = 'Invalid delivery ID.';
        $_SESSION['message_type'] = 'warning';
    }
    header('Location: warehouse.php');
    exit();
}

// Handle delete delivery
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_delivery'])) {
    $delivery_id = filter_var($_POST['delivery_id'] ?? '', FILTER_VALIDATE_INT);

    if ($delivery_id) {
        $stmt = $conn->prepare("SELECT book_id, store_id, quantity, status FROM deliveries WHERE delivery_id = ?");
        $stmt->bind_param('i', $delivery_id);
        $stmt->execute();
        $stmt->bind_result($book_id, $store_id, $quantity, $status);
        $fetched_delivery = $stmt->fetch();
        $stmt->close();

        if ($fetched_delivery) {
            // Always return stock to warehouse
            $stmt = $conn->prepare("UPDATE book SET warehouse_stock = warehouse_stock + ? WHERE book_id = ?");
            $stmt->bind_param('ii', $quantity, $book_id);
            $stmt->execute();
            $stmt->close();

            if ($status === 'delivered') {
                // Remove stock from store_book, no negative stock
                $stmt = $conn->prepare("UPDATE store_book SET stock = GREATEST(stock - ?, 0) WHERE store_id = ? AND book_id = ?");
                $stmt->bind_param('iii', $quantity, $store_id, $book_id);
                $stmt->execute();
                $stmt->close();
            }

            // Delete delivery record
            $stmt = $conn->prepare("DELETE FROM deliveries WHERE delivery_id = ?");
            $stmt->bind_param('i', $delivery_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Delivery deleted successfully. Stock returned to warehouse.';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error deleting delivery.';
                $_SESSION['message_type'] = 'danger';
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = 'Delivery not found.';
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = 'Invalid delivery ID.';
        $_SESSION['message_type'] = 'warning';
    }
    header('Location: warehouse.php');
    exit();
}

// Handle delete store stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_store_stock'])) {
    $store_name = trim($_POST['store_name'] ?? '');
    $book_title = trim($_POST['book_title'] ?? '');

    if ($store_name && $book_title) {
        $stmt = $conn->prepare("SELECT st.store_id, b.book_id FROM store st JOIN book b WHERE st.name = ? AND b.title = ?");
        $stmt->bind_param('ss', $store_name, $book_title);
        $stmt->execute();
        $stmt->bind_result($store_id, $book_id);
        $found = $stmt->fetch();
        $stmt->close();

        if ($found) {
            // Prevent deletion if deliveries exist
            $stmt = $conn->prepare("SELECT COUNT(*) FROM deliveries WHERE store_id = ? AND book_id = ?");
            $stmt->bind_param('ii', $store_id, $book_id);
            $stmt->execute();
            $stmt->bind_result($delivery_count);
            $stmt->fetch();
            $stmt->close();

            if ($delivery_count > 0) {
                $_SESSION['message'] = 'Cannot delete store stock because deliveries exist for this book-store combination. Please delete the deliveries first.';
                $_SESSION['message_type'] = 'warning';
            } else {
                $stmt = $conn->prepare("DELETE FROM store_book WHERE store_id = ? AND book_id = ?");
                $stmt->bind_param('ii', $store_id, $book_id);
                if ($stmt->execute()) {
                    $_SESSION['message'] = 'Store stock deleted successfully.';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Error deleting store stock.';
                    $_SESSION['message_type'] = 'danger';
                }
                $stmt->close();
            }
        } else {
            $_SESSION['message'] = 'Store or book not found.';
            $_SESSION['message_type'] = 'warning';
        }
    }
    header('Location: warehouse.php');
    exit();
}

// Fetch deliveries
$deliveries = [];
$sql = "SELECT d.delivery_id, st.name AS store, b.title AS book, d.quantity, d.date_received, d.status, dp.name AS delivery_person, dp.contact_number, dp.email
        FROM deliveries d
        JOIN store st ON d.store_id = st.store_id
        JOIN book b ON d.book_id = b.book_id
        LEFT JOIN delivery_person dp ON d.delivery_person_id = dp.delivery_person_id
        ORDER BY d.date_received DESC, d.delivery_id DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $deliveries[] = $row;
}

// Fetch store stock
$store_stock = [];
$sql = "SELECT st.name AS store, b.title AS book, sb.stock, sb.store_id, sb.book_id,
               (SELECT COUNT(*) FROM deliveries d WHERE d.store_id = sb.store_id AND d.book_id = sb.book_id) AS delivery_count
        FROM store_book sb
        JOIN store st ON sb.store_id = st.store_id
        JOIN book b ON sb.book_id = b.book_id
        ORDER BY st.name, b.title";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $store_stock[] = $row;
}

// Fetch warehouse stock
$warehouse_stock = [];
$sql = "SELECT title, warehouse_stock FROM book ORDER BY title";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $warehouse_stock[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Warehouse Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    
    <!-- Roboto Slab Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab&display=swap" rel="stylesheet" />
    
    <style>
    :root {
      --primary-color: #8B5E3C;
      --primary-color-hover: #6E4A2C;
      --light-bg: #fdf6e3;
      --light-text: #3C2F2F;
      --light-card-bg: #fff7eb;
      --dark-bg: #1B263B;
      --dark-text: #E6E1D3;
      --dark-card-bg: #2a354d;
      --danger-color: #a33e3e;
      --danger-hover: #7d2c2c;
      --success-color: #3e7d3e;
      --success-hover: #2c5d2c;
      --warning-color: #a37d3e;
      --warning-hover: #7d5d2c;
      --info-color: #3e5a7d;
      --info-hover: #2c3f5d;
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

    h1, h3 {
      color: var(--primary-color);
      font-weight: bold;
      transition: color 0.3s ease;
    }
    body.dark-mode h1, body.dark-mode h3 {
      color: #d4b483;
    }

    table {
      background-color: var(--light-card-bg);
      color: var(--light-text);
      transition: background 0.3s ease, color 0.3s ease;
    }

    body.dark-mode table {
      background-color: var(--dark-card-bg);
      color: var(--dark-text);
    }

    thead {
      background-color: var(--primary-color);
      color: var(--light-bg);
    }

    body.dark-mode thead {
      background-color: var(--primary-color-hover);
      color: var(--dark-bg);
    }

    .table-bordered {
      border-color: var(--primary-color);
    }

    body.dark-mode .table-bordered {
      border-color: var(--primary-color-hover);
    }

    .btn {
      font-family: 'Roboto Slab', serif;
      border-radius: 0.5rem;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .btn-secondary {
      background-color: var(--primary-color);
      border: none;
      color: var(--light-bg);
    }
    .btn-secondary:hover {
      background-color: var(--primary-color-hover);
      color: var(--light-bg);
    }

    body.dark-mode .btn-secondary {
      background-color: var(--primary-color);
      color: var(--light-bg);
    }
    body.dark-mode .btn-secondary:hover {
      background-color: var(--primary-color-hover);
    }

    .btn-danger {
      background-color: var(--danger-color);
      border: none;
      color: var(--light-bg);
    }
    .btn-danger:hover {
      background-color: var(--danger-hover);
      color: var(--light-bg);
    }

    .btn-success {
      background-color: var(--success-color);
      border: none;
      color: var(--light-bg);
    }
    .btn-success:hover {
      background-color: var(--success-hover);
      color: var(--light-bg);
    }

    .btn-primary {
      background-color: var(--info-color);
      border: none;
      color: var(--light-bg);
    }
    .btn-primary:hover {
      background-color: var(--info-hover);
      color: var(--light-bg);
    }

    .btn[disabled], .btn:disabled {
      opacity: 0.65;
      cursor: not-allowed !important;
    }

    /* Tooltip fix for dark mode */
    .tooltip-inner {
      background-color: var(--primary-color);
      color: var(--light-bg);
    }
    .bs-tooltip-top .arrow::before {
      border-top-color: var(--primary-color);
    }

    /* Dark Mode Toggle */
    #darkModeToggle {
      position: fixed;
      bottom: 1rem;
      right: 1rem;
      z-index: 1050;
      background-color: var(--primary-color);
      color: var(--light-bg);
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
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container py-5">
    <h1 class="mb-4">Warehouse Management</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <h3 class="mb-3">Deliveries</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Store</th>
                <th>Book</th>
                <th>Quantity</th>
                <th>Date Received</th>
                <th>Delivery Person</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($deliveries as $d): ?>
            <tr>
                <td><?= $d['delivery_id'] ?></td>
                <td><?= htmlspecialchars($d['store']) ?></td>
                <td><?= htmlspecialchars($d['book']) ?></td>
                <td><?= $d['quantity'] ?></td>
                <td><?= $d['date_received'] ?: '-' ?></td>
                <td><?= htmlspecialchars($d['delivery_person'] ?? '-') ?></td>
                <td><?= htmlspecialchars($d['contact_number'] ?? '-') ?></td>
                <td><?= htmlspecialchars($d['email'] ?? '-') ?></td>
                <td><?= ucfirst($d['status']) ?></td>
                <td>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="delivery_id" value="<?= $d['delivery_id'] ?>">
                        <!-- Complete button enabled unless status already delivered -->
                        <button type="submit" name="mark_complete" class="btn btn-sm btn-success" <?= $d['status'] === 'delivered' ? 'disabled' : '' ?> onclick="return confirm('Mark this delivery as complete?')">Complete</button>
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
                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this delivery and revert stock?');">
                        <input type="hidden" name="delivery_id" value="<?= $d['delivery_id'] ?>">
                        <button type="submit" name="delete_delivery" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($deliveries)): ?>
            <tr><td colspan="10">No deliveries found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <h3 class="mt-5">Book Stock in Stores</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Store</th>
                <th>Book</th>
                <th>Stock</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($store_stock as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['store']) ?></td>
                <td><?= htmlspecialchars($s['book']) ?></td>
                <td><?= $s['stock'] ?></td>
                <td>
                    <?php if ($s['delivery_count'] > 0): ?>
                        <button class="btn btn-sm btn-secondary" disabled data-bs-toggle="tooltip" data-bs-placement="top" title="Cannot delete because deliveries exist. Please delete the deliveries first.">In Use</button>
                    <?php else: ?>
                        <form method="POST" onsubmit="return confirm('Delete this book from store stock?');" class="d-inline">
                            <input type="hidden" name="delete_store_stock" value="1">
                            <input type="hidden" name="store_name" value="<?= htmlspecialchars($s['store']) ?>">
                            <input type="hidden" name="book_title" value="<?= htmlspecialchars($s['book']) ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($store_stock)): ?>
            <tr><td colspan="4">No store stock found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <h3 class="mt-5">Warehouse Stock</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>Book</th><th>Warehouse Stock</th></tr>
        </thead>
        <tbody>
        <?php foreach ($warehouse_stock as $w): ?>
            <tr>
                <td><?= htmlspecialchars($w['title']) ?></td>
                <td><?= $w['warehouse_stock'] ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($warehouse_stock)): ?>
            <tr><td colspan="2">No warehouse stock found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

  <footer class="text-center mt-5">
  <p>&copy; <?= date('Y'); ?> Inkventory. All rights reserved.</p>

<!-- Dark Mode Toggle Button -->
<button id="darkModeToggle" aria-label="Toggle Dark/Light Mode" title="Toggle Dark/Light Mode">
    <i class="bi bi-brightness-high-fill"></i>
</button>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Initialize Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})

// Dark Mode Toggle Logic
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
