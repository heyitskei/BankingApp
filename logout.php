<?php
session_start();

// User_id stored in session after login
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch the latest session record for the user
    $server = "localhost";
    $username = "root";
    $db_password = "sql123";
    $database = "banking_app";

    $conn = new mysqli($server, $username, $db_password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT session_id FROM sessions WHERE user_id = ? ORDER BY login_time DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($session_id);

    // Fetch the result before preparing the next query
    $stmt->fetch();
    $stmt->close();

    if ($session_id) {
        // Update the logout time for the latest session
        $sqlUpdateSession = "UPDATE sessions SET logout_time = CURRENT_TIMESTAMP WHERE session_id = ?";
        $stmtUpdateSession = $conn->prepare($sqlUpdateSession);
        $stmtUpdateSession->bind_param("i", $session_id);
        $stmtUpdateSession->execute();
        $stmtUpdateSession->close();
    }

    // Close the database connection
    $conn->close();
}

// Clear the sessions and redirect to start.php
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session
header("Location: start.php");  // Updated relative path
exit;
?>
