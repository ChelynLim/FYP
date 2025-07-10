<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php'; 
include 'navbar.php';
?>

<!DOCTYPE html>
<html>
<head>
  <title>View Suppliers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-4">


<h2>Supplier List</h2>
<a href="add_supplier.php" class="btn btn-success mb-3">Add New Supplier</a>

<?php
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Count matching suppliers
$countQuery = "SELECT COUNT(*) AS total FROM suppliers WHERE name LIKE ? OR email LIKE ?";
$stmt = $conn->prepare($countQuery);
$searchTerm = "%$search%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$totalRows = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Fetch paginated suppliers
$query = "SELECT * FROM suppliers WHERE name LIKE ? OR email LIKE ?";
if ($sort === 'name' || $sort === 'email') {
    $query .= " ORDER BY $sort ASC";
}
$query .= " LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<form method="GET" class="row g-2 mb-3">
  <div class="col-md-4">
    <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
  </div>
  <div class="col-md-2">
    <select name="sort" class="form-select">
      <option value="">Sort by</option>
      <option value="name" <?php if ($sort == 'name') echo 'selected'; ?>>Name</option>
      <option value="email" <?php if ($sort == 'email') echo 'selected'; ?>>Email</option>
    </select>
  </div>
  <input type="hidden" name="page" value="1">
  <div class="col-md-2">
    <button class="btn btn-primary" type="submit">Search</button>
  </div>
</form>

<table class="table table-bordered table-hover">
  <thead class="table-light">
    <tr>
      <th>Name</th>
      <th>Contact Person</th>
      <th>Phone</th>
      <th>Email</th>
      <th>Address</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['name'] ?></td>
      <td><?= $row['contact_person'] ?></td>
      <td><?= $row['phone'] ?></td>
      <td><?= $row['email'] ?></td>
      <td><?= $row['address'] ?></td>
      <td>
        <a href='edit_supplier.php?id=<?= $row['supplier_id'] ?>' class='btn btn-sm btn-primary'>Edit</a>
        <a href='delete_supplier.php?id=<?= $row['supplier_id'] ?>' class='btn btn-sm btn-danger' onclick='return confirm("Are you sure?")'>Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<!-- Pagination links -->
<nav>
  <ul class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
      <a class="page-link"
         href="?search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>&page=<?= $i ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>
  </ul>
</nav>

  </div> <!-- Close container -->
</body>

</html>
