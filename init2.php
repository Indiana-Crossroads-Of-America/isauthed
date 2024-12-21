<?php
session_start();

// Include the Composer autoloader for dotenv
require_once 'vendor/autoload.php';

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable('/var/www');
$dotenv->load();
echo "<script>console.log('Environment variables loaded successfully \\b Onyx: ONLINE');</script>";

// Check if the user is not logged in
if (!isset($_SESSION['username'])) {
    // Store the current URL in the session to redirect after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: /webapp");  // Redirect to login page if not authenticated
    exit();
}

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
    // Store the current URL in the session to redirect after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: /webapp");  // Redirect to login page if not authenticated
    exit();
}

// Check if the session ID matches the one stored at login
if ($_SESSION['session_id'] !== session_id()) {
    // If session IDs do not match, log the user out
    session_destroy();
    header("Location: /webapp");  // Redirect to login page
    exit();
}

// Fetch user data from the database
$username = $_SESSION['username'];
$stmt = $mysqli->prepare("SELECT role, First_Login FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 0) {
    // If no user is found with the provided username, deny access
    echo '<div class="access-denied" style="color: red; text-align: center; font-size: 20px; margin-bottom: 20px;">
        Access denied. No such user found. Please contact support if you believe this is a mistake.
    </div>';
    exit();
}

$row = $result->fetch_assoc();
$user_role = $row['role'];
$first_login = $row['First_Login'];

// Redirect user to reset password page if First_Login = 1
if ($first_login == 1) {
    header("Location: /webapp/reset-password");
    exit();
}

// Print the fetched role and username to the browser console
echo "<script>console.log('Scoped Permission Level: " . htmlspecialchars($user_role) . "');</script>";
echo "<script>console.log('Logged In Username: " . htmlspecialchars($username) . "');</script>";

// Define allowed roles for different pages or areas
$allowed_roles = ['member', 'admin', 'owner']; // List of roles that are allowed to access this page

// You can add more granular access checks here based on your use case
switch (strtolower($user_role)) {
    case 'owner':
        // Owner has full access to everything
        break;
    case 'admin':
        // Admin has some restricted access
        break;
    case 'member':
        // Members have limited access
        break;
    default:
        // If the role does not match allowed roles, deny access
        echo '<div class="access-denied" style="color: #D8000C; text-align: center; font-size: 18px; margin: 40px auto; padding: 30px; max-width: 600px; border: 2px solid #D8000C; border-radius: 10px; background-color: #F8D7DA;">
                <p><strong>Username:</strong> <span style="font-weight: bold;">' . htmlspecialchars($username) . '</span> | <strong>Permissions Scope:</strong> <strong>' . htmlspecialchars($user_role) . '</strong></p>
                <hr>
                <h2 style="margin-bottom: 20px;">Access Denied</h2>
                <p>WebApp URL: <strong>\DB\BCRP\Scope\Permission\forms</strong>.</p> 
                <p>You do not have the necessary permissions to view this page. Please submit a support ticket and include your Username, the permissions listed at the top of the page, and the URL of this page.</p>
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <form action="logout.php" method="post">
                    <button type="submit" style="padding: 12px 25px; font-size: 16px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s;">
                        Logout
                    </button>
                </form>
            </div>';
        exit();
}

// If the user has an allowed role, continue with the page logic
// For example, allow them to view or perform actions on the page



?>
