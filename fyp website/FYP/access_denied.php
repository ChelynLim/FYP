<!-- access_denied.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .error-card {
            padding: 2rem;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <h1 class="text-danger">⛔ Access Denied</h1>
        <p>You do not have permission to access this page.</p>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Return to Dashboard</a>
    </div>
</body>
</html>
