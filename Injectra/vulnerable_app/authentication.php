<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Intentionally vulnerable input
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Allow stacked queries (important change: added ;)
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password';";

    // Show executed query
    echo "<div style='font-family:monospace; background:#111; color:#0f0; padding:15px; margin:20px;'>";
    echo "<strong>Executed Query:</strong><br>";
    echo htmlspecialchars($sql);
    echo "</div>";

    // 🔥 Use multi_query instead of query
    $login_success = false;

    if (mysqli_multi_query($conn, $sql)) {

        do {
            if ($result = mysqli_store_result($conn)) {

                if (mysqli_num_rows($result) > 0) {
                    $login_success = true;

                    echo "<div style='background:#e8f5e9; padding:20px; margin:20px; border-radius:8px;'>";
                    echo "<h2 style='color:green;'>✅ Login Successful!</h2>";
                    echo "<h3>Retrieved Rows from Database:</h3>";
                    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse; width:100%;'>";
                    echo "<tr style='background:#4CAF50; color:white;'><th>Username</th><th>Password</th></tr>";

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['password']) . "</td>";
                        echo "</tr>";
                    }

                    echo "</table>";
                    echo "</div>";
                }

                mysqli_free_result($result);
            }

        } while (mysqli_next_result($conn));
        if (!$login_success) {
            echo "<div style='background:#ffebee; padding:20px; margin:20px; border-radius:8px;'>";
            echo "<h2 style='color:red;'>❌ Login Failed!</h2>";
            echo "<p>No matching user found.</p>";
            echo "</div>";
        }

    } else {
        echo "<div style='background:#fee; color:red; padding:15px; margin:20px;'>";
        echo "SQL Error: " . mysqli_error($conn);
        echo "</div>";
    }

    echo "<div style='margin:20px;'><a href='index.php'>← Back to Login</a></div>";

} else {
    header("Location: index.php");
    exit();
}
?>