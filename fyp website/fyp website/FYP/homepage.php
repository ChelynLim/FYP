<?php
// homepage.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'db_connect.php';

// Fetch all store from the database
$stores = [];
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $sql = "SELECT * FROM store WHERE name LIKE ? OR address LIKE ?";
    $param = "%$search%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $stores[] = $row;
    }
    $stmt->close();
} else {
    $sql = "SELECT * FROM store";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stores[] = $row;
        }
    }
}

// Handle add store form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_store'])) {
    $store_name = trim($_POST['store_name'] ?? '');
    $store_address = trim($_POST['store_address'] ?? '');
    if ($store_name && $store_address) {
        require_once 'db_connect.php'; // Reconnect to DB
        $stmt = $conn->prepare("INSERT INTO store (name, address) VALUES (?, ?)");
        $stmt->bind_param('ss', $store_name, $store_address);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header('Location: homepage.php');
        exit();
    } else {
        echo '<div class="alert alert-danger">All fields are required to add a store.</div>';
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Homepage - Stores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .store-card { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container py-5">
        <h1 class="mb-4">All Stores</h1>
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addStoreModal">Add Store</button>
        <!-- Add Store Modal -->
        <div class="modal fade" id="addStoreModal" tabindex="-1" aria-labelledby="addStoreModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header">
                  <h5 class="modal-title" id="addStoreModalLabel">Add New Store</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Store Name</label>
                    <input type="text" name="store_name" class="form-control" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" name="store_address" class="form-control" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" name="add_store" class="btn btn-primary">Add Store</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <form method="GET" class="mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control" placeholder="Search stores by name or address..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                </div>
            </div>
        </form>
        <div class="row">
            <?php if (count($stores) > 0): ?>
                <?php foreach ($stores as $store): ?>
                    <div class="col-md-4">
                        <a href="store_books.php?id=<?php echo urlencode($store['store_id']); ?>" style="text-decoration:none;color:inherit;">
                            <div class="card store-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($store['name'] ?? 'Store'); ?></h5>
                                    <?php if (!empty($store['description'])): ?>
                                        <p class="card-text"><?php echo htmlspecialchars($store['description']); ?></p>
                                    <?php endif; ?>
                                    <p class="card-text"><strong>Address:</strong> <?php echo htmlspecialchars($store['address'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No stores found.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>