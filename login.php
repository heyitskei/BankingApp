<!DOCTYPE html>
<html lang="en">
<head>
    <title>Banking App - Account Selection</title>
    <link rel="stylesheet" type="text/css" href="style-login.css">
    <script type="text/javascript">
        function logOut() {
            // Redirect to logout.php when the user clicks the logout button
            window.location.href = "logout.php";
        }
    </script>
</head>
<body>

    <div class="center-container">
        <?php
        session_start();

        // Retrieve the user_id from the session
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Check if the user is authenticated
        if ($user_id === null) {
            // Redirect to start.php if the user is not authenticated
            header("Location: start.php");
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

        // Retrieve account information and user's name based on user_id using a JOIN
        $sql = "SELECT accounts.account_type, users.first_name
                FROM accounts
                INNER JOIN users ON accounts.user_id = users.user_id
                WHERE accounts.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Display account selection form and greet the user by name
            $row = $result->fetch_assoc();
            $user_name = $row["first_name"];

            echo '<h1>Welcome, <span style="font-size: 34px; color: #41EDCE;">' . $user_name . '</span></h1>';
            echo '<h2>Select Account</h2>';
            echo '<form id ="selectForm" method="post" action="accounts.php">';
            echo '<select id="accountSelect" name="account_type">';
            //echo '<option value="" selected></option>';

            // Reset the result set pointer to the beginning
            mysqli_data_seek($result, 0);

            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row["account_type"] . '">' . $row["account_type"] . ' Account</option>';
            }

            echo '</select>' ;
            echo '<div><button type="submit" class="submitButton" >Submit</button></div>';
            echo '</form>';
        } else {
            echo 'No accounts found for this user.';
        }

        // Close the database connection
        $stmt->close();
        $conn->close();
        ?>

        <div class="navbar">
            <button id="logoutButton" onclick="logOut()">LogOut</button>
        </div>
    </div>


</body>
</html>

