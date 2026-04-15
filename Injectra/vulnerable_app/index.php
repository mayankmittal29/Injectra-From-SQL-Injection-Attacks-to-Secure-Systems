<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vulnerable App</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h2>Login (Vulnerable)</h2>
            <p class="warning">⚠️ This app is intentionally vulnerable to SQL Injection</p>

            <?php if (isset($_GET['error'])): ?>
                <p class="error">Invalid username or password.</p>
            <?php endif; ?>

            <?php if (isset($_GET['logout'])): ?>
                <p class="success">Logged out successfully.</p>
            <?php endif; ?>

            <form action="authentication.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" autocomplete="off">
                </div>
                <button type="submit" class="btn">Login</button>
            </form>

            <div class="hints">
                <p><strong>Test Credentials:</strong></p>
                <p>Username: user1 | Password: pass1</p>
                <p>Username: admin | Password: admin123</p>
            </div>
        </div>
    </div>
</body>
</html>
