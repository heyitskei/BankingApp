<?php
session_start();

$user_id = '';
$password = '';
$error = '';

// Handle logout
if (isset($_GET['logout'])) {
    // Clear the sessions and redirect to login.php
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session

    // Redirect to start.php
    header("Location: start.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : '';
    $password = isset($_POST["password"]) ? $_POST["password"] : '';

    if ($user_id && $password) {
        // Establish a MySQL database connection
        $server = "localhost"; 
        $username = "root";
        $db_password = "sql123";
        $database = "banking_app";

        $conn = new mysqli($server, $username, $db_password, $database);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare and execute a query to check the entered credentials
        $sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $hashedPassword = $row["password"];

            if (password_verify($password, $hashedPassword)) {
                // Password matches, set the session data
                $_SESSION['user_id'] = $user_id;

                // Record the session in the sessions table
                $session_key = session_id();
                $insert_sql = "INSERT INTO sessions (user_id, session_key, login_time) VALUES (?, ?, CURRENT_TIMESTAMP)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ss", $user_id, $session_key);
                $insert_stmt->execute();
                $insert_stmt->close();

                // Close the database connection
                $stmt->close();
                $conn->close();

                // Redirect to the login page
                header("Location: login.php");
                exit;
            } else {
                // Password does not match
                $error = "Please check your Account Number and Password.";
            }
        } else {
            // User not found in the database
            $error = "No user found with this Account Number. Please sign up first.";
        }

        // Close the database connection
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Banking App Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script type="text/javascript">
        function validateInputsAndSubmit() {
            var user_id = document.getElementById("user_id").value.trim();
            var password = document.getElementById("password").value.trim();
            var errorContainer = document.getElementById("errorContainer");

            if (!user_id || !password) {
                errorContainer.innerHTML = "Please fill in both Account Number and Password.";
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="login-container">
        <form id="loginForm" method="post" action="start.php" onsubmit="return validateInputsAndSubmit()">
            <h1>Banking App Login</h1>
            <input type="text" id="user_id" name="user_id" placeholder="Account Number" value="<?php echo $user_id; ?>">
            <input type="password" id="password" name="password" placeholder="Password" value="<?php echo $password; ?>">
            <div id="errorContainer" class="error" style="color: red;">
                <?php
                if (!empty($error)) {
                    echo $error;
                }
                ?>
            </div>
            <table class="button-container">
                <tr>
                    <td><button id="loginButton" type="submit">Login</button></td>
                </tr>
                <tr>
                    <td class = "divisionLine"> or </td>
                </tr>
                <tr>
                    <td><button id="signupButton" type="submit"><a id="signupButton" href="signup.php">Signup</button></td>
                </tr>
                <tr>
                    <td><br/>Forgot your password? <a id="forgotPasswordButton" href="forgot_password.php">Reset it</a></td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>