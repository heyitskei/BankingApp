<?php
session_start();

// Establish a database connection
$server = "localhost";
$username = "root";
$db_password = "sql123";
$database = "banking_app";

$conn = new mysqli($server, $username, $db_password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user clicked on the logout button
if (isset($_POST['logout'])) {
    // Clear the sessions and update the logout_time in sessions table
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($user_id !== null) {
        $updateLogoutTimeSQL = "UPDATE sessions SET logout_time = CURRENT_TIMESTAMP WHERE user_id = ?";
        $stmtLogout = $conn->prepare($updateLogoutTimeSQL);
        $stmtLogout->bind_param("i", $user_id);
        $stmtLogout->execute();
        $stmtLogout->close();
    }

    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: start.php");
    exit;
}

// Retrieve the user_id and selected account type from the session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$selected_account_type = isset($_POST['account_type']) ? $_POST['account_type'] : null;

// Check if the user is authenticated
if ($user_id === null || $selected_account_type === null) {
    echo "USER ID or Account Type NULL";
    exit;
}

// Check if the session is valid
$session_key = session_id();
$checkSessionSQL = "SELECT * FROM sessions WHERE user_id = ? AND session_key = ?";
$stmtCheckSession = $conn->prepare($checkSessionSQL);
$stmtCheckSession->bind_param("is", $user_id, $session_key);
$stmtCheckSession->execute();
$resultCheckSession = $stmtCheckSession->get_result();

if ($resultCheckSession->num_rows === 0) {
    // If session is not valid, redirect to start.php
    header("Location: start.php");
    exit;
}

// Retrieve account_id based on user_id and selected account_type
$sqlAccountId = "SELECT account_id FROM accounts WHERE user_id = ? AND account_type = ?";
$stmtAccountId = $conn->prepare($sqlAccountId);
$stmtAccountId->bind_param("is", $user_id, $selected_account_type);
$stmtAccountId->execute();
$stmtAccountId->bind_result($account_id);
$stmtAccountId->fetch();
$stmtAccountId->close();

// Set the account_id in the session
$_SESSION['account_id'] = $account_id;

// Retrieve account information and user's name based on user_id and selected account_type using a JOIN
$sql = "SELECT accounts.account_type, users.first_name, accounts.balance
        FROM accounts
        INNER JOIN users ON accounts.user_id = users.user_id
        WHERE accounts.user_id = ? AND accounts.account_type = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $selected_account_type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Display account information and features
    $row = $result->fetch_assoc();
    $user_name = $row["first_name"];
    $accountType = $row["account_type"];
    $balance = $row["balance"];

    // Set the account type and balance in the session
    $_SESSION['accountType'] = $accountType;
    $_SESSION['balance'] = $balance;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Account Features</title>
    <link rel="stylesheet" type="text/css" href="style-account.css">
</head>
<body>
    <table id = "accountTable">
    <div id = "top">
        <tr class = "accountTypeTitle">


            <td>
                <h1 style="font-size: 30px;">Account Information</h1>
            </td>
            <tr class = "menuButton">
                <td  colspan="2">
                    <form method="post" action="login.php">
                        <button id ="backButton" type="submit">Back</button>
                    </form>
                </td>
                <td class = "buttonBlank">
                </td>
                <td  colspan="2">
                        <form method="post">
                            <button id="logoutButton" name="logout" type="submit">LogOut</button>
                        </form>
                </td>
            </tr>

        </tr>
        <tr>
            <td colspan="2">
                <h3 id ="accountFeature">Account Features</h3>
            </td>
        </tr>
    </div>



        <tr>
            <td id ="middle">

                    <p>Welcome, <span style="font-size: 20px; color: #41EDCE;"><?= $user_name ?></span></p>

                    <?php
                    // Switch statement for different account types
                    switch ($accountType) {
                        case 'Chequing':
                            echo "<p>You selected a Chequing Account.</p>";
                            echo "<p>Here are the features for Chequing Account:</p>";
                            echo "<ul class = 'list'>";
                            echo "<li>Unlimited Check Writing</li>";
                            echo "<li>ATM Access</li>";
                            echo "<li>Online Banking</li>";
                            echo "</ul>";
                            echo "<p id='balance'>Your Chequing Account Balance: $ <span id ='amountB'>" . $balance . "</span></p>";
                            break;
                        case 'Savings':
                            echo "<p>You selected a Savings Account.</p>";
                            echo "<p>Here are the features for Savings Account:</p>";
                            echo "<ul class = 'list'>";
                            echo "<li>Interest Earnings</li>";
                            echo "<li>Limited Withdrawals</li>";
                            echo "</ul>";
                            echo "<p id='balance'>Your Savings Account Balance: $ <span id ='amountB'>" . $balance . "</span></p>";
                            break;
                        case 'Loan':
                            echo "<p>You selected a Loan Account.</p>";
                            echo "<p>Here are the features for Loan Account:</p>";
                            echo "<ul class = 'list'>";
                            echo "<li>Low-Interest Rates</li>";
                            echo "<li>Flexible Repayment Options</li>";
                            echo "</ul>";
                            echo "<p id='balance'>Your Loan Account Balance: $ <span id ='amountB'>" . $balance . "</span></p>";
                            break;
                        default:
                            echo "<p>Please select a valid account type.</p>";
                            break;
                    }
                    ?>
            </td>

                <td id = "bottom">
                    <h3>Account Services</h3>
                    <form method="post" action="transaction.php">
                        <input type="hidden" name="accountType" value="<?= $accountType ?>">
                        <div class="nav">
                            <table class = "iconButtonTable">
                                <tr id =icon class="center-icons">
                                    <td><img src = "./Image/1.png" class = "accountIcon"></td>
                                    <td><img src = "./Image/2.png" class = "accountIcon"></td>
                                    <td><img src = "./Image/3.png" class = "accountIcon"></td>

                                </tr>
                                <tr>
                                    <td><button type="submit" id ="depositButton" name="service" value="Deposit">Deposit</button></td>
                                    <td><button type="submit" id ="withDrawButton" name="service" value="Withdraw">Withdraw</button></td>
                                    <td><button type="submit" id ="transferButton" name="service" value="Transfer">Transfer</button></td>
                                </tr>
                            </table>
                        </div>
                    </form>
                </td>
  
        </tr>
    </table>
</body>
</html>

<?php
} else {
    echo 'No account found for the selected account type.';
}

// Close the database connection
$stmt->close();
$conn->close();
?>