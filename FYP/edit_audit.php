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

include 'navbar.php';

$store_ids = [];
$store_id_to_name = [];
$store_stmt = $conn->prepare("SELECT store_id, name FROM store ORDER BY store_id");
$store_stmt->execute();
$store_result = $store_stmt->get_result();
while ($store = $store_result->fetch_assoc()) {
    $store_ids[$store['name']] = $store['store_id'];
    $store_id_to_name[$store['store_id']] = $store['name'];
}
$store_stmt->close();

$audit_id = intval($_GET['audit_id'] ?? 0);
if (!$audit_id) {
    die("Invalid audit ID.");
}

$result = $conn->prepare("SELECT * FROM audit_logs WHERE audit_id = ?");
$result->bind_param("i", $audit_id);
$result->execute();
$res = $result->get_result();
$audit_log = $res->fetch_assoc();
$result->close();
if (!$audit_log) {
    die("Audit not found.");
}

$store_of_audit_id = intval($audit_log['store_of_audit']);
$store_of_audit = $store_id_to_name[$store_of_audit_id] ?? '';

$stmt = $conn->prepare(
    "SELECT 
        b.book_id, b.title, sb.stock AS actual_stock, 
        IFNULL(ast.counted_stock, '') AS counted_stock 
     FROM book b 
     JOIN store_book sb ON b.book_id = sb.book_id AND sb.store_id = ? 
     LEFT JOIN audit_stock ast ON ast.book_id = b.book_id AND ast.audit_id = ?"
);
$stmt->bind_param("ii", $store_of_audit_id, $audit_id);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['audit_id'])) {
    $store_of_audit_name = $_POST['store_of_audit'] ?? '';
    $auditor_name = trim($_POST['auditor_name'] ?? '');
    $audit_date = $_POST['audit_date'] ?? '';

    if (!isset($store_ids[$store_of_audit_name])) {
        $message = '<div class="alert alert-danger mb-3">Invalid store selected.</div>';
    } elseif (!$auditor_name) {
        $message = '<div class="alert alert-danger mb-3">Auditor name is required.</div>';
    } elseif (!isset($_POST['counted_stock']) || !is_array($_POST['counted_stock'])) {
        $message = '<div class="alert alert-danger mb-3">Please fill in all counted stock.</div>';
    } else {
        $store_id = $store_ids[$store_of_audit_name];

        // Fetch actual stock for validation
        $stmt = $conn->prepare(
            "SELECT b.book_id, sb.stock 
             FROM book b 
             JOIN store_book sb ON b.book_id = sb.book_id 
             WHERE sb.store_id = ?"
        );
        $stmt->bind_param("i", $store_id);
        $stmt->execute();
        $books_actual = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $actual_stock_map = [];
        foreach ($books_actual as $bk) {
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
            "UPDATE audit_logs SET auditor_name = ?, store_of_audit = ?, audit_date = ?, audit_status = ? WHERE audit_id = ?"
        );
        $stmt->bind_param("sissi", $auditor_name, $store_id, $audit_date, $audit_status, $audit_id);

        if ($stmt->execute()) {
            $stmt->close();

            foreach ($_POST['counted_stock'] as $book_id => $counted) {
                $book_id_int = intval($book_id);

                $check = $conn->prepare("SELECT COUNT(*) AS cnt FROM audit_stock WHERE audit_id = ? AND book_id = ?");
                $check->bind_param("ii", $audit_id, $book_id_int);
                $check->execute();
                $res = $check->get_result();
                $row = $res->fetch_assoc();
                $check->close();

                if ($row['cnt'] > 0) {
                    $upd = $conn->prepare(
                        "UPDATE audit_stock SET counted_stock = ? WHERE audit_id = ? AND book_id = ?"
                    );
                    $upd->bind_param("iii", $counted, $audit_id, $book_id_int);
                    $upd->execute();
                    $upd->close();
                } else {
                    $ins = $conn->prepare(
                        "INSERT INTO audit_stock (audit_id, book_id, store_id, counted_stock) VALUES (?, ?, ?, ?)"
                    );
                    $ins->bind_param("iiii", $audit_id, $book_id_int, $store_id, $counted);
                    $ins->execute();
                    $ins->close();
                }
            }

            $store_of_audit_id = $store_id;
            $store_of_audit = $store_of_audit_name;
            $stmt = $conn->prepare(
                "SELECT 
                    b.book_id, b.title, sb.stock AS actual_stock, 
                    IFNULL(ast.counted_stock, '') AS counted_stock 
                 FROM book b 
                 JOIN store_book sb ON b.book_id = sb.book_id AND sb.store_id = ? 
                 LEFT JOIN audit_stock ast ON ast.book_id = b.book_id AND ast.audit_id = ?"
            );
            $stmt->bind_param("ii", $store_of_audit_id, $audit_id);
            $stmt->execute();
            $books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $message = '<div class="alert alert-success mb-3">Audit updated successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger mb-3">Failed to update audit log.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Audit</title>
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
        <h1 class="m-0"><i class="bi bi-pencil"></i> Edit Audit</h1>
        <a href="view_audit_logs.php" class="btn btn-primary btn-sm"><i class="bi bi-list-check"></i> View Audits</a>
    </div>

    <?= $message ?? '' ?>

    <div class="card shadow-sm">
        <div class="card-header"><i class="bi bi-chevron-right"></i> Audit Details</div>
        <div class="card-body">
            <form method="post" action="edit_audit.php?audit_id=<?= $audit_id ?>" id="editAuditForm" autocomplete="off">
                <input type="hidden" name="audit_id" value="<?= $audit_id ?>" />

                <div class="mb-3">
                    <label for="auditor_name" class="form-label">Auditor Name</label>
                    <input type="text" name="auditor_name" id="auditor_name" value="<?= htmlspecialchars($audit_log['auditor_name']) ?>" class="form-control" required />
                </div>

                <div class="mb-3">
                    <label for="audit_date" class="form-label">Audit Date</label>
                    <input type="date" name="audit_date" id="audit_date" value="<?= htmlspecialchars($audit_log['audit_date']) ?>" class="form-control" required />
                </div>

                <div class="mb-3">
                    <label for="store_of_audit" class="form-label">Store</label>
                    <select name="store_of_audit" id="store_of_audit" class="form-select" required>
                        <?php foreach ($store_ids as $store_name => $store_id): ?>
                            <option value="<?= htmlspecialchars($store_name) ?>" <?= ($store_of_audit === $store_name) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($store_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($books): ?>
                    <label class="form-label">Audit Progress</label>
                    <div class="progress" id="progressContainer">
                        <div class="progress-bar" id="progressBar" style="width: 0%;">0%</div>
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
                                    <td><?= (int)$book['actual_stock'] ?></td>
                                    <td>
                                        <input
                                            type="number"
                                            name="counted_stock[<?= $book['book_id'] ?>]"
                                            value="<?= htmlspecialchars($book['counted_stock']) ?>"
                                            min="0"
                                            class="form-control counted-stock-input"
                                            data-actual="<?= $book['actual_stock'] ?>"
                                            required
                                        >
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No books found for this store.</p>
                <?php endif; ?>

                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Update Audit</button>
            </form>
        </div>
    </div>
</div>

<script>
function updateProgress() {
    const inputs = document.querySelectorAll('.counted-stock-input');
    let matchedCount = 0;
    inputs.forEach(input => {
        const actual = input.dataset.actual;
        const counted = input.value;
        if(counted !== '' && counted === actual) {
            matchedCount++;
        }
    });
    const total = inputs.length;
    const percent = total === 0 ? 0 : Math.round((matchedCount / total) * 100);
    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = percent + '%';
    progressBar.textContent = percent + '%';

    if (percent === 100) {
        progressBar.classList.remove('bg-warning');
        progressBar.classList.add('bg-success');
    } else {
        progressBar.classList.remove('bg-success');
        progressBar.classList.add('bg-warning');
    }
}

document.querySelectorAll('.counted-stock-input').forEach(input => {
    input.addEventListener('input', updateProgress);
});

updateProgress();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
