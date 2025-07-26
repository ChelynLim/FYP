<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$admin_username = $_SESSION['admin'];

// Get role of current logged-in admin
$stmt = $conn->prepare("SELECT role FROM admins WHERE username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Only allow access to super admins
if (!$admin || $admin['role'] !== 'super') {
    header("Location: access_denied.php");
    exit;
}

// Now safe to include navbar, after all redirects
include 'navbar.php';

$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        $message = "✅ Admin account created successfully.";
    } else {
        $message = "❌ Failed to create admin (maybe username already exists).";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admin-card {
            max-width: 400px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="admin-card">
        <h4 class="text-center mb-4">Create New Admin</h4>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="normal">Normal</option>
                    <option value="super">Super</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Create Admin</button>
        </form>
    </div>
</div>

<footer class="text-center mt-5">
  <p>&copy; <?= date('Y'); ?> Inkventory. All rights reserved.</p>

</body>
</html>