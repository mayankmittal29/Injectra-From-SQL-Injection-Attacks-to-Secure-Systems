<?php
$host = "localhost";
$dbname = "lab5_hashed";
$user = "root";
$password = "";

try {
    // Using PDO for prepared statements support
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);

    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable emulated prepares — forces real prepared statements
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // Never show real error to user — log it instead
    error_log("DB Connection Error: " . $e->getMessage());
    die("Service unavailable. Please try again later.");
}
?>
