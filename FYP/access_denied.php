<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Slab&display=swap');

        :root {
            --primary-color: #8B5E3C; /* leather brown */
            --primary-color-hover: #6E4A2C;
            --text-color-dark: #3C2F2F; /* dark brown text */
            --background-light: #fdf6e3; /* parchment */
            --card-bg-light: #fff8e7;
            --shadow-color-light: rgba(139, 94, 60, 0.2);
            --btn-bg-light: #8B5E3C;
            --btn-hover-bg-light: #6E4A2C;
        }

        body {
            font-family: 'Roboto Slab', serif;
            background-color: var(--background-light);
            color: var(--text-color-dark);
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .error-card {
            background-color: var(--card-bg-light);
            padding: 2.5rem 3rem;
            border-radius: 1rem;
            box-shadow: 0 4px 15px var(--shadow-color-light);
            text-align: center;
            max-width: 400px;
            width: 100%;
            user-select: none;
        }

        .error-card h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 3rem;
        }

        .error-card p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .btn-secondary {
            background-color: var(--btn-bg-light);
            border: none;
            color: var(--card-bg-light);
            font-weight: 600;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background-color: var(--btn-hover-bg-light);
            color: var(--card-bg-light);
            text-decoration: none;
        }

        /* Icon styling */
        .error-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">⛔</div>
        <h1>Access Denied</h1>
        <p>You do not have permission to access this page.</p>
        <a href="dashboard.php" class="btn btn-secondary">Return to Dashboard</a>
    </div>
</body>
</html>
