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

$store_ids_name_map = [
    1 => 'Kinobuniya',
    2 => 'Buni',
    3 => 'Yuni',
    4 => 'Junin',
];

$search_field = $_GET['search_field'] ?? '';
$search_value = trim($_GET['search_value'] ?? '');

$searchable_fields = [
    'audit_id' => 'audit_id',
    'auditor_name' => 'auditor_name',
    'store_name' => 'store_of_audit',
    'store_id' => 'store_of_audit',
    'audit_date' => 'audit_date',
    'audit_status' => 'audit_status',
];

$where_clause = '';
$params = [];
$param_types = '';
$valid_search = false;

if ($search_field && $search_value !== '' && isset($searchable_fields[$search_field])) {
    $valid_search = true;
    if ($search_field === 'store_name') {
        $matching_store_ids = [];
        foreach ($store_ids_name_map as $id => $name) {
            if (stripos($name, $search_value) !== false) {
                $matching_store_ids[] = $id;
            }
        }
        if (count($matching_store_ids) > 0) {
            $placeholders = implode(',', array_fill(0, count($matching_store_ids), '?'));
            $where_clause = "WHERE store_of_audit IN ($placeholders)";
            $param_types = str_repeat('i', count($matching_store_ids));
            $params = $matching_store_ids;
        } else {
            $where_clause = "WHERE 0";
        }
    } elseif ($search_field === 'store_id' || $search_field === 'audit_id') {
        $where_clause = "WHERE " . $searchable_fields[$search_field] . " = ?";
        $param_types = 'i';
        $params[] = (int)$search_value;
    } elseif ($search_field === 'audit_date') {
        $where_clause = "WHERE audit_date LIKE ?";
        $param_types = 's';
        $params[] = $search_value . '%';
    } else {
        $where_clause = "WHERE " . $searchable_fields[$search_field] . " LIKE ?";
        $param_types = 's';
        $params[] = '%' . $search_value . '%';
    }
}

$sql = "SELECT * FROM audit_logs $where_clause ORDER BY audit_date DESC";

$stmt = $conn->prepare($sql);
if ($valid_search && !empty($params)) {
    $refs = [];
    foreach ($params as $key => $val) {
        $refs[$key] = &$params[$key];
    }
    array_unshift($refs, $param_types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>View Audit Logs</title>
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
      --table-head-bg-light: #e9dcc6;

      --primary-color-dark: #D4B483;
      --primary-color-hover-dark: #BBA15D;
      --text-color-light: #E6E1D3;
      --card-bg-dark: #1B263B;
      --shadow-color-dark: rgba(212, 180, 131, 0.6);
      --btn-bg-dark: #B38B47;
      --btn-hover-bg-dark: #8A6B32;
      --table-head-bg-dark: #324A66;
    }

    body {
      font-family: 'Roboto Slab', serif;
      background-color: var(--card-bg-light);
      color: var(--text-color-dark);
      transition: background-color 0.3s ease, color 0.3s ease;
      padding-top: 70px;
      min-height: 100vh;
    }

    body.dark-mode {
      background-color: var(--card-bg-dark);
      color: var(--text-color-light);
    }

    h2 {
      text-align: center;
      margin-bottom: 2rem;
      color: var(--primary-color);
      font-weight: 700;
    }

    body.dark-mode h2 {
      color: var(--primary-color-dark);
    }

    .card {
      background-color: var(--card-bg-light);
      border-radius: 1rem;
      box-shadow: 0 4px 15px var(--shadow-color-light);
      padding: 1rem;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    body.dark-mode .card {
      background-color: var(--card-bg-dark);
      box-shadow: 0 4px 15px var(--shadow-color-dark);
    }

    table {
      background-color: transparent;
      color: inherit;
    }

    table thead {
      background-color: var(--table-head-bg-light);
      color: var(--text-color-dark);
    }

    tbody tr:hover {
      background-color: var(--primary-color-hover);
      color: white;
      cursor: pointer;
    }

    body.dark-mode table thead {
      background-color: var(--table-head-bg-dark);
      color: var(--text-color-light);
    }

    body.dark-mode tbody tr:hover {
      background-color: var(--btn-hover-bg-dark);
      color: white;
    }

    .form-control, .form-select {
      border-radius: 0.5rem;
    }

    .btn-primary, .btn-success {
      background-color: var(--btn-bg-light);
      border: none;
      transition: background-color 0.3s ease;
    }

    .btn-primary:hover, .btn-success:hover {
      background-color: var(--btn-hover-bg-light);
    }

    body.dark-mode .btn-primary, body.dark-mode .btn-success {
      background-color: var(--btn-bg-dark);
    }

    body.dark-mode .btn-primary:hover, body.dark-mode .btn-success:hover {
      background-color: var(--btn-hover-bg-dark);
    }

    .btn-danger {
      background-color: #A3523A;
      border: none;
      color: white;
      transition: background-color 0.3s ease;
    }

    .btn-danger:hover {
      background-color: #823B27;
    }

    body.dark-mode .btn-danger {
      background-color: #C5725B;
    }

    body.dark-mode .btn-danger:hover {
      background-color: #9F4E3C;
    }

    .pagination .page-link {
      color: var(--primary-color);
      background-color: transparent;
      border: 1px solid var(--primary-color);
    }

    .pagination .page-link:hover {
      background-color: var(--primary-color-hover);
      color: white;
    }

    .pagination .page-item.active .page-link {
      background-color: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
    }

    body.dark-mode .pagination .page-link {
      color: var(--primary-color-dark);
      border-color: var(--primary-color-dark);
    }

    body.dark-mode .pagination .page-link:hover {
      background-color: var(--primary-color-hover-dark);
      color: white;
    }

    body.dark-mode .pagination .page-item.active .page-link {
      background-color: var(--primary-color-dark);
      color: white;
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
    <h1 class="mb-4">View Audit Logs</h1>

    <form method="get" action="view_audit_logs.php" class="row g-3 align-items-center search-form">
        <div class="col-auto">
            <label for="search_field" class="col-form-label">Search by:</label>
        </div>
        <div class="col-auto">
            <select name="search_field" id="search_field" class="form-select" required>
                <option value="">-- Select field --</option>
                <?php foreach ($searchable_fields as $key => $value): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= $search_field === $key ? 'selected' : '' ?>>
                        <?= ucfirst(str_replace('_', ' ', $key)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" name="search_value" id="search_value" class="form-control" value="<?= htmlspecialchars($search_value) ?>" required />
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="view_audit_logs.php" class="btn btn-secondary ms-1">Clear</a>
        </div>
    </form>

    <div class="mb-3 text-end">
        <a href="add_audit.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add Audit
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Audit ID</th>
                    <th>Auditor Name</th>
                    <th>Store of Audit</th>
                    <th>Audit Date</th>
                    <th>Audit Status</th>
                    <th style="width: 130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$row['audit_id'] ?></td>
                        <td><?= htmlspecialchars($row['auditor_name']) ?></td>
                        <td><?= htmlspecialchars($store_ids_name_map[(int)$row['store_of_audit']] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['audit_date']) ?></td>
                        <td>
                            <?php if (strtolower($row['audit_status']) === 'success'): ?>
                                <span class="badge bg-success">Success</span>
                            <?php elseif (strtolower($row['audit_status']) === 'fail'): ?>
                                <span class="badge bg-danger">Fail</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($row['audit_status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_audit.php?audit_id=<?= $row['audit_id'] ?>" class="btn btn-sm btn-primary me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="post" action="delete_audit.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this audit?');">
                                <input type="hidden" name="audit_id" value="<?= $row['audit_id'] ?>" />
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No audit logs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
