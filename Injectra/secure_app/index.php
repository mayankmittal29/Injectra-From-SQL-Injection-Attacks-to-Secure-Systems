<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure App</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #1a2e1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { width: 100%; padding: 20px; display: flex; justify-content: center; }
        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        h2 { text-align: center; color: #333; margin-bottom: 10px; font-size: 24px; }
        .secure-badge {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; color: #555; font-weight: bold; font-size: 14px; }
        .form-group input {
            width: 100%; padding: 12px; border: 1px solid #ddd;
            border-radius: 6px; font-size: 15px; transition: border 0.3s;
        }
        .form-group input:focus { outline: none; border-color: #27ae60; }
        .btn {
            width: 100%; padding: 13px; background: #27ae60; color: white;
            border: none; border-radius: 6px; font-size: 16px; cursor: pointer;
        }
        .btn:hover { background: #219a52; }
        .error {
            background: #ffebee; color: #c62828; padding: 10px;
            border-radius: 5px; text-align: center; margin-bottom: 15px; font-size: 14px;
        }
        .success {
            background: #e8f5e9; color: #2e7d32; padding: 10px;
            border-radius: 5px; text-align: center; margin-bottom: 15px; font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h2>Login (Secure)</h2>
            <p class="secure-badge">🔒 This app is protected against SQL Injection</p>

            <?php if (isset($_GET['error'])): ?>
                <p class="error">Invalid username or password.</p>
            <?php endif; ?>

            <?php if (isset($_GET['logout'])): ?>
                <p class="success">Logged out successfully.</p>
            <?php endif; ?>

            <form action="authentication.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" maxlength="50" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" maxlength="50" autocomplete="off">
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
