<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $address = trim($_POST["address"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validate user input
    $errors = [];

    if (empty($firstName)) {
        $errors["firstName"] = "First name is required.";
    }

    if (empty($lastName)) {
        $errors["lastName"] = "Last name is required.";
    }

    if (empty($address)) {
        $errors["address"] = "Address is required.";
    }

    if (empty($email)) {
        $errors["email"] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Invalid email address.";
    }

    if (empty($password)) {
        $errors["password"] = "Password is required.";
    }

    // If there are validation errors, redirect back to the signup form with errors
    if (!empty($errors)) {
        // Store errors in session to display on the signup form
        $_SESSION["signup_errors"] = $errors;

        // Redirect back to the signup form
        header("Location: signup.php");
        exit;
    }

    // Establish a database connection
    $server = "localhost";
    $username = "root";
    $db_password = "sql123";
    $database = "banking_app";

    $conn = new mysqli($server, $username, $db_password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Concatenate first_name and last_name for the username
    $username = $firstName . $lastName;

    // Hash the password using password_hash
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into the database
    $sql = "INSERT INTO users (username, password, email, first_name, last_name)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $hashedPassword, $email, $firstName, $lastName);
    $stmt->execute();

    // Close the database connection
    $stmt->close();
    $conn->close();

    // Set session variables
    $_SESSION['user_id'] = $username;
    $_SESSION['password'] = $hashedPassword;

    // Redirect to the signup success page
    header("Location: signup_success.php");
    exit;
}

// Unset session variables if they exist (to prevent carrying them over to start.php)
unset($_SESSION['user_id']);
unset($_SESSION['password']);
?>

<!DOCTYPE html>
<head>
    <title>Signup Page</title>
    <link rel="stylesheet" type="text/css" href="style-signup.css">
    <style>
        /* Adjust the password input width */
        input[type="password"] {
            width: 100%;
        }
    </style>
    <script type="text/javascript">
        function validateForm() {
            // Get input values
            var firstName = document.getElementById("firstName").value.trim();
            var lastName = document.getElementById("lastName").value.trim();
            var address = document.getElementById("address").value.trim();
            var email = document.getElementById("email").value.trim();
            var password = document.getElementById("password").value.trim();

            // Get error message elements
            var firstNameError = document.getElementById("firstNameError");
            var lastNameError = document.getElementById("lastNameError");
            var addressError = document.getElementById("addressError");
            var emailError = document.getElementById("emailError");
            var passwordError = document.getElementById("passwordError");

            // Initialize error messages
            firstNameError.textContent = "";
            lastNameError.textContent = "";
            addressError.textContent = "";
            emailError.textContent = "";
            passwordError.textContent = "";

            var isValid = true;

            if (firstName === "") {
                firstNameError.textContent = "First name is required.";
                isValid = false;
            }

            if (lastName === "") {
                lastNameError.textContent = "Last name is required.";
                isValid = false;
            }

            if (address === "") {
                addressError.textContent = "Address is required.";
                isValid = false;
            }

            if (email === "") {
                emailError.textContent = "Email address is required.";
                isValid = false;
            } else if (!isValidEmail(email)) {
                emailError.textContent = "Invalid email address.";
                isValid = false;
            }

            if (password === "") {
                passwordError.textContent = "Password is required.";
                isValid = false;
            }

            return isValid;
        }

        function isValidEmail(email) {
            // Simple email validation function
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        //Function which can go back to the start.php
        function goToStart(){
            window.location.href = 'start.php';

        }
    </script>
</head>
<body>
    <form method="post" action="signup.php" onsubmit="return validateForm()">
    <table class = "signupTable">
            <tr>
            <h1 id = "signupTitle">Sign-up </h1>
           </tr>
            <tr>
                <td id = "firstNameLabel"><label for="firstName">First Name:</label></td>
                <td><input type="text" id="firstName" name="firstName"></td>
                <td class="error-message" id="firstNameError"></td>
            </tr>
            <tr>
                <td id = "lastNameLabel"><label for="lastName">Last Name:</label></td>
                <td><input type="text" id="lastName" name="lastName"></td>
                <td class="error-message" id="lastNameError"></td>
            </tr>
            <tr>
                <td id = "addressLabel"><label for="address">Address:</label></td>
                <td><input type="text" id="address" name="address"></td>
                <td class="error-message" id="addressError"></td>
            </tr>
            <tr>
                <td id = "emailLabel"><label for="email">Email Address:</label></td>
                <td><input type="email" id="email" name="email"></td>
                <td class="error-message" id="emailError"></td>
            </tr>
            <tr>
                <td id = "passwordLabel"><label for="password">Password:</label></td>
                <td><input type="password" id="password" name="password"></td>
                <td class="error-message" id="passwordError"></td>
            </tr>
    </table>
        <div style="text-align: center; margin-top: 20px;">
            <input id = "cancelButton" type="button" value="Cancel" onclick="goToStart()">
            <div class = "blankBetweenButton"> </div>
            <input type="submit" value="Submit" id = submitButton>
        </div>
    </form>
</body>
</html>
