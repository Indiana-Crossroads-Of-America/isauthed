<?php
session_start();

// Include the Composer autoloader for dotenv
require_once 'vendor/autoload.php';

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable('/var/www');
$dotenv->load();
echo "<script>console.log('Environment variables loaded successfully \\b Onyx: ONLINE');</script>";

// Get MySQL credentials from the .env file
$mysqli = new mysqli(
    $_ENV['DB_HOST'], 
    $_ENV['DB_USER'], 
    $_ENV['DB_PASSWORD'], 
    $_ENV['DB_NAME']
);

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: /");  // Redirect to login page if not authenticated
    exit();
}

// Check if the session ID matches the one stored at login
if ($_SESSION['session_id'] !== session_id()) {
    // If session IDs do not match, log the user out
    session_destroy();
    header("Location: /");  // Redirect to login page
    exit();
}

// Fetch user role from the database
$username = $_SESSION['username'];
$stmt = $mysqli->prepare("SELECT role FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists and has the correct role
if ($result->num_rows === 0 || $result->fetch_assoc()['role'] !== 'ADMIN') {
    // If user is not an ADMIN, block access with styled message
    echo '<div class="access-denied">Access denied..</div>';
    exit();  // Optionally, you can redirect to another page
}

// Welcome message for authenticated user
?>
