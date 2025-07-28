<?php
session_start();
require_once 'db_connect.php';

// Super admin access control
$admin_username = $_SESSION['admin'] ?? '';
if (!$admin_username) {
    header("Location: access_denied.php");
    exit();
}
$stmt = $conn->prepare("SELECT role FROM admins WHERE username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();
if (!$admin || $admin['role'] !== 'super') {
    header("Location: access_denied.php");
    exit();
}

// Fetch stores from DB
$store_ids = []; // name => id
$store_id_to_name = []; // id => name
$store_stmt = $conn->prepare("SELECT store_id, name FROM store ORDER BY store_id");
$store_stmt->execute();
$store_result = $store_stmt->get_result();
while ($store = $store_result->fetch_assoc()) {
    $store_ids[$store['name']] = $store['store_id'];
    $store_id_to_name[$store['store_id']] = $store['name'];
}
$store_stmt->close();

$books = [];
$selected_store_id = null;
$store_of_audit = null;
$message = '';
$show_books_table = false;

$auditor_name = '';
$audit_date = date('Y-m-d');

// Load books when store selected (but audit form not yet submitted)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['store_of_audit']) && !isset($_POST['audit_submit'])) {
    $store_of_audit = $_POST['store_of_audit'];
    if (isset($store_ids[$store_of_audit])) {
        $selected_store_id = $store_ids[$store_of_audit];
        $stmt = $conn->prepare(
            "SELECT b.book_id, b.title, sb.stock 
             FROM book b 
             JOIN store_book sb ON b.book_id = sb.book_id 
             WHERE sb.store_id = ?"
        );
        $stmt->bind_param("i", $selected_store_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $books = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $show_books_table = true;
    }
    $auditor_name = trim($_POST['auditor_name'] ?? '');
    $audit_date = $_POST['audit_date'] ?? date('Y-m-d');
}

// Handle audit submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['audit_submit'])) {
    $store_of_audit = $_POST['store_of_audit'] ?? null;
    $auditor_name = trim($_POST['auditor_name'] ?? '');
    $audit_date = $_POST['audit_date'] ?? date('Y-m-d');
    if (!isset($store_ids[$store_of_audit])) {
        $message = '<div class="alert alert-danger mb-3">Invalid store selected.</div>';
    } elseif (!$auditor_name) {
        $message = '<div class="alert alert-danger mb-3">Auditor name is required.</div>';
    } elseif (!isset($_POST['counted_stock']) || !is_array($_POST['counted_stock'])) {
        $message = '<div class="alert alert-danger mb-3">Please count all listed book stocks.</div>';
    } else {
        $selected_store_id = $store_ids[$store_of_audit];

        // Fetch actual stocks
        $stmt = $conn->prepare(
            "SELECT b.book_id, b.title, sb.stock 
             FROM book b 
             JOIN store_book sb ON b.book_id = sb.book_id 
             WHERE sb.store_id = ?"
        );
        $stmt->bind_param("i", $selected_store_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $books = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $actual_stock_map = [];
        foreach ($books as $bk) {
            $actual_stock_map[$bk['book_id']] = $bk['stock'];
        }

        $completed = true;
        foreach ($_POST['counted_stock'] as $book_id => $counted) {
            if (!isset($actual_stock_map[$book_id]) || strval($actual_stock_map[$book_id]) !== strval($counted)) {
                $completed = false;
                break;
            }
        }

        $audit_status = $completed ? 'success' : 'fail';

        $stmt = $conn->prepare(
            "INSERT INTO audit_logs (auditor_name, store_of_audit, audit_date, audit_status) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("siss", $_POST['auditor_name'], $selected_store_id, $_POST['audit_date'], $audit_status);
        $stmt->execute();
        $audit_id = $stmt->insert_id;
        $stmt->close();

        if (isset($_POST['counted_stock']) && is_array($_POST['counted_stock'])) {
            foreach ($_POST['counted_stock'] as $book_id => $counted_stock) {
                $ins = $conn->prepare(
                    "INSERT INTO audit_stock (audit_id, book_id, store_id, counted_stock) VALUES (?, ?, ?, ?)"
                );
                $ins->bind_param("iiii", $audit_id, $book_id, $selected_store_id, $counted_stock);
                $ins->execute();
                $ins->close();
            }
            // Redirect to audit logs after successful insert
            header("Location: view_audit_logs.php");
            exit();
        } else {
            $message = '<div class="alert alert-danger mb-3">Failed to add audit log.</div>';
        }
    }
}

// Set values for form input defaults if not set yet
if (empty($auditor_name)) $auditor_name = $admin_username;
if (empty($audit_date)) $audit_date = date('Y-m-d');

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Audit</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto+Slab&display=swap');

    :root {
      --primary-color: #8B5E3C;
      --primary-color-hover: #6E4A2C;
      --text-color-dark: #3C2F2F;
      --card-bg-light: #fdf6e3;
      --shadow-color-light: rgba(139, 94, 60, 0.2);
      --btn-bg-light: #8B5E3C;
      --btn-hover-bg-light: #6E4A2C;

      --primary-color-dark: #D4B483;
      --primary-color-hover-dark: #BBA15D;
      --text-color-light: #E6E1D3;
      --card-bg-dark: #1B263B;
      --shadow-color-dark: rgba(212, 180, 131, 0.6);
      --btn-bg-dark: #B38B47;
      --btn-hover-bg-dark: #8A6B32;
    }

    body {
      padding-top: 70px;
      font-family: 'Roboto Slab', serif;
      background-color: var(--card-bg-light);
      color: var(--text-color-dark);
      transition: background-color 0.3s ease, color 0.3s ease;
      min-height: 100vh;
    }

    body.dark-mode {
      background: linear-gradient(135deg, #1B263B, #121C2F);
      color: var(--text-color-light);
    }

    h2 {
      text-align: center;
      margin-bottom: 2rem;
      color: var(--primary-color);
      font-weight: 700;
    }

    form {
      background-color: var(--card-bg-light);
      box-shadow: 0 4px 15px var(--shadow-color-light);
    }

    body.dark-mode form {
      background-color: var(--card-bg-dark);
      box-shadow: 0 4px 15px var(--shadow-color-dark);
    }

    label {
      font-weight: 600;
    }

    .btn-primary {
      background-color: var(--btn-bg-light);
      border: none;
    }

    .btn-primary:hover {
      background-color: var(--btn-hover-bg-light);
    }

    .btn-secondary {
      background-color: #aaa;
      border: none;
    }

    body.dark-mode .btn-primary {
      background-color: var(--btn-bg-dark);
    }

    body.dark-mode .btn-primary:hover {
      background-color: var(--btn-hover-bg-dark);
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    body.dark-mode .alert-success {
      background-color: #284F36;
      color: #cdeac0;
      border-color: #3e7153;
    }

    /* Toggle Button */
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

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="m-0"><i class="bi bi-clipboard-data"></i> Add Audit</h1>
        <a href="view_audit_logs.php" class="btn btn-primary btn-sm"><i class="bi bi-list-check"></i> View Audits</a>
    </div>

    <?= $message ?? '' ?>

    <div class="card shadow-sm">
        <div class="card-header"><i class="bi bi-chevron-right"></i> Audit Information</div>
        <div class="card-body">
            <form method="post" action="add_audit.php" autocomplete="off" id="auditForm">

                <div class="mb-3">
                    <label for="auditor_name" class="form-label">Auditor Name</label>
                    <input type="text" name="auditor_name" id="auditor_name" value="<?= htmlspecialchars($auditor_name) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="audit_date" class="form-label">Audit Date</label>
                    <input type="date" name="audit_date" id="audit_date" value="<?= htmlspecialchars($audit_date) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="store_of_audit" class="form-label">Store</label>
                    <select name="store_of_audit" id="store_of_audit" class="form-select" required onchange="this.form.submit()">
                        <option value="">-- Select Store --</option>
                        <?php foreach ($store_ids as $store_name => $store_id): ?>
                            <option value="<?= htmlspecialchars($store_name) ?>" <?= ($store_of_audit === $store_name) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($store_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><input type="submit" class="btn btn-outline-secondary btn-sm mt-2" value="Load Books"></noscript>
                </div>

                <?php if (!empty($books)): ?>
                    <div class="mb-3">
                        <label class="form-label">Audit Progress</label>
                        <div class="progress" id="progressContainer">
                            <div class="progress-bar" id="progressBar" style="width: 0%;">0%</div>
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Book Title</th>
                                    <th>Actual Stock</th>
                                    <th>Counted Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><?= htmlspecialchars($book['title']) ?></td>
                                    <td><?= (int)$book['stock'] ?></td>
                                    <td>
                                        <input
                                            type="number"
                                            name="counted_stock[<?= $book['book_id'] ?>]"
                                            min="0"
                                            class="form-control counted-stock-input"
                                            data-actual="<?= $book['stock'] ?>"
                                            required
                                        >
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" name="audit_submit" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Add Audit
                    </button>
                <?php elseif (isset($store_of_audit)): ?>
                    <p class="text-muted">No books found for the selected store.</p>
                <?php endif; ?>

            </form>
        </div>
    </div>
</div>

<script>
// Calculate and update progress bar
function updateProgress() {
    const inputs = document.querySelectorAll('.counted-stock-input');
    let matchedCount = 0;
    inputs.forEach(input => {
        const actual = input.dataset.actual;
        const counted = input.value;
        if (counted !== '' && counted === actual) {
            matchedCount++;
        }
    });
    const total = inputs.length;
    const percent = total === 0 ? 0 : Math.round((matchedCount / total) * 100);
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = percent + '%';
    progressBar.textContent = percent + '%';

    // Color changes if 100% completion
    if (percent === 100) {
        progressBar.classList.remove('bg-warning');
        progressBar.classList.add('bg-success');
    } else {
        progressBar.classList.remove('bg-success');
        progressBar.classList.add('bg-warning');
    }
}

// Attach event listeners to stock inputs
document.querySelectorAll('.counted-stock-input').forEach(input => {
    input.addEventListener('input', updateProgress);
});

// Initial update on page load if inputs exist
updateProgress();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
