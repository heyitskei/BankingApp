<?php
session_start();

function generateRandomToken() {
    return bin2hex(random_bytes(32));
}

// Variable to store error message
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_email = isset($_POST["user_email"]) ? $_POST["user_email"] : '';

    if ($user_email) {
        // Establish a MySQL database connection
        $server = "localhost";
        $username = "root";
        $db_password = "sql123";
        $database = "banking_app";

        $conn = new mysqli($server, $username, $db_password, $database);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the email exists in the database
        $check_email_sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt_check_email = $conn->prepare($check_email_sql);
        $stmt_check_email->bind_param("s", $user_email);
        $stmt_check_email->execute();
        $result_check_email = $stmt_check_email->get_result();

        if ($result_check_email->num_rows > 0) {
            // User with the provided email exists
            $row = $result_check_email->fetch_assoc();
            $user_id = $row['user_id'];

            // Generate a unique token
            $reset_token = generateRandomToken();

            // Store the token in the database
            $store_token_sql = "UPDATE users SET reset_token = ? WHERE user_id = ?";
            $stmt_store_token = $conn->prepare($store_token_sql);
            $stmt_store_token->bind_param("si", $reset_token, $user_id);
            $stmt_store_token->execute();

            // Close the database connection
            $stmt_check_email->close();
            $stmt_store_token->close();
            $conn->close();

            // Redirect to the page indicating that the reset link was sent
            header("Location: reset_link_sent.php");
            exit;            
        } else {
            $error_message = "No user found with this email address.";
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Banking App - Forgot Password</title>
    <link rel="stylesheet" type="text/css" href="style.css">

    <script type="text/javascript">
        function validateForm() {
            var userEmail = document.getElementById("user_email").value.trim();
            var errorContainer = document.getElementById("errorContainer");

            if (userEmail === "") {
                errorContainer.innerHTML = "Please enter your email.";
                return false;
            } else {
                errorContainer.innerHTML = "";
                return true;
            }
        }
    </script>
</head>
<body>
    <div class="login-container-forgotPass">
        <form id="forgotPasswordForm" method="post" action="forgot_password.php" onsubmit="return validateForm()">
        <div class="forgotPasswordContainer">
            <h1>Forgot Password</h1>
            <div>
                <input type="text" id="user_email" name="user_email" placeholder="Email">
            </div>
            <div>
                <!-- Display error message with the added style below the button -->
                <p id="errorContainer" class="error-message"><?php echo $error_message; ?></p>
            </div>
            <div>
                <button id="resetPasswordButton" type="submit">Reset Password</button>
            </div>
            <div>
                <br/>Remember your password? <a id="rememberPasswordButton" href="login.php">Log in</a>
            </div>
        </div>
        </form>
    </div>
</body>
</html>


