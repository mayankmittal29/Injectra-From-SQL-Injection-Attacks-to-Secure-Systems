<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ============================================================
    // DEFENSE 1: Input Validation
    // Reject empty inputs and inputs with suspicious length.
    // Whitelist: only allow alphanumeric + underscore in username.
    // ============================================================
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Check empty
    if (empty($username) || empty($password)) {
        header("Location: index.php?error=1");
        exit();
    }

    // Check length (max 50 chars)
    if (strlen($username) > 50 || strlen($password) > 50) {
        header("Location: index.php?error=1");
        exit();
    }

    // Whitelist: username must only contain letters, numbers, underscore
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        header("Location: index.php?error=1");
        exit();
    }

    // ============================================================
    // DEFENSE 2: Prepared Statements
    // The SQL query structure is fixed before any user input arrives.
    // The ? placeholders are filled in as pure DATA — never as SQL code.
    // Even if attacker types ' OR '1'='1'--, it's treated as a literal string.
    // ============================================================
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // ============================================================
        // DEFENSE 4: Never expose SQL errors
        // Log error server-side only. Show generic message to user.
        // ============================================================
        error_log("Login query error: " . $e->getMessage());
        header("Location: index.php?error=1");
        exit();
    }

    // ============================================================
    // DEFENSE 3: Password Hashing
    // Passwords in DB are stored as bcrypt hashes.
    // password_verify() checks input against the hash.
    // Even if DB is leaked, raw passwords are NOT exposed.
    //
    // NOTE FOR THIS ASSIGNMENT:
    // Since the DB was seeded with plain text passwords (pass1, admin123),
    // we do a plain text check here for demo purposes.
    // In a real app: store hashes using password_hash() and verify with password_verify().
    //
    // To use hashing properly, run this once to insert hashed passwords:
    // INSERT INTO users VALUES ('user1', password_hash('pass1', PASSWORD_BCRYPT));
    // INSERT INTO users VALUES ('admin', password_hash('admin123', PASSWORD_BCRYPT));
    // Then replace the plain check below with: password_verify($password, $user['password'])
    // ============================================================

    if ($user && password_verify($password, $user['password'])) {
        // Login success — only show username, never dump full DB
        $_SESSION['username'] = $user['username'];

        echo "<!DOCTYPE html><html><head><title>Success</title>";
        echo "<style>body{font-family:Arial;background:#1a2e1a;display:flex;align-items:center;justify-content:center;min-height:100vh;}";
        echo ".box{background:#fff;padding:40px;border-radius:10px;text-align:center;max-width:400px;}";
        echo "h2{color:#27ae60;} a{color:#27ae60;}</style></head><body>";
        echo "<div class='box'>";
        echo "<h2>✅ Login Successful!</h2>";
        echo "<p>Welcome, <strong>" . htmlspecialchars($user['username']) . "</strong>!</p>";
        echo "<br><a href='index.php?logout=1'>Logout</a>";
        echo "</div></body></html>";

    } else {
        // Login failed — generic message, no hint about what went wrong
        header("Location: index.php?error=1");
        exit();
    }

} else {
    header("Location: index.php");
    exit();
}
?>
