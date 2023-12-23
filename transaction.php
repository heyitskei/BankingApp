<?php
session_start();

// Check if the user clicked on the logout button
if (isset($_POST['logout'])) {
    // Database connection parameters
    $server = "localhost";
    $username = "root";
    $db_password = "sql123";
    $database = "banking_app";

    // Establish a MySQL database connection
    $conn = new mysqli($server, $username, $db_password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Clear the sessions and update the logout_time in sessions table
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if ($user_id !== null) {
        $updateLogoutTimeSQL = "UPDATE sessions SET logout_time = CURRENT_TIMESTAMP WHERE user_id = ?";
        $stmtLogout = $conn->prepare($updateLogoutTimeSQL);
        $stmtLogout->bind_param("i", $user_id);
        $stmtLogout->execute();
        $stmtLogout->close();
    }

    // Unset all session variables
    session_unset();

    // Destroy the session
    session_destroy();

    // Close the database connection
    $conn->close();

    header("Location: start.php");
    exit;
}

if (isset($_SESSION['accountType'])) {
    $accountType = $_SESSION['accountType'];
} else {
    header("Location: start.php");
    exit;
}

// Database connection parameters
$server = "localhost";
$username = "root";
$db_password = "sql123";
$database = "banking_app";

// Define account types
$accountTypes = ['Chequing', 'Savings', 'Loan']; 

// Establish a MySQL database connection
$conn = new mysqli($server, $username, $db_password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the account balance for the selected account
$sql = "SELECT balance FROM accounts WHERE account_type = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $accountType, $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($currentBalance);
$stmt->fetch();
$stmt->close();

// Check if the service is set
if (isset($_POST['service'])) {
    $service = $_POST['service'];
} else {
    $service = 'Deposit'; // Default service
}

// Initialize the error messages
$amountError = "";
$transferError = "";

// Prepare statement for inserting transactions
$insertTransactionSql = "INSERT INTO transactions (account_id, transaction_type, amount, description) VALUES (?, ?, ?, ?)";
$stmtInsertTransaction = $conn->prepare($insertTransactionSql);
$stmtInsertTransaction->bind_param("isds", $_SESSION['account_id'], $service, $amount, $description);

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['amount'])) {
        $amount = floatval($_POST['amount']);      

        if ($service == 'Deposit') {
            if ($amount < 0) {
                $amountError = "Amount must not be negative.";
            } else {
                $currentBalance += $amount;

                // Update the balance in the database
                $updateBalanceSql = "UPDATE accounts SET balance = ? WHERE account_type = ? AND user_id = ?";
                $stmtUpdate = $conn->prepare($updateBalanceSql);
                $stmtUpdate->bind_param("dss", $currentBalance, $accountType, $_SESSION['user_id']);
                $stmtUpdate->execute();
                $stmtUpdate->close();

                // Insert a new transaction record
                $description = "Deposit Recorded"; 
                $stmtInsertTransaction->execute();
            }
        } elseif ($service == 'Withdraw') {
            if ($amount < 0) {
                $amountError = "Amount must not be negative.";
            } elseif ($currentBalance >= $amount) {
                $currentBalance -= $amount;

                // Update the balance in the database
                $updateBalanceSql = "UPDATE accounts SET balance = ? WHERE account_type = ? AND user_id = ?";
                $stmtUpdate = $conn->prepare($updateBalanceSql);
                $stmtUpdate->bind_param("dss", $currentBalance, $accountType, $_SESSION['user_id']);
                $stmtUpdate->execute();
                $stmtUpdate->close();

                // Insert a new transaction record
                $description = "Withdrawal Recorded";
                $stmtInsertTransaction->execute();
            } else {
                $amountError = "Insufficient balance for withdrawal.";
            }
        } elseif ($service == 'Transfer') {
            if ($amount < 0) {
                $amountError = "Amount must not be negative.";
            } elseif (isset($_POST['to_account'])) {
                $toAccount = $_POST['to_account'];

                // Retrieve the balance of the target account
                $sql = "SELECT balance FROM accounts WHERE account_type = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $toAccount, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->bind_result($toAccountBalance);
                $stmt->fetch();
                $stmt->close();

                if ($toAccountBalance !== null) {
                    if ($currentBalance >= $amount) {
                        $currentBalance -= $amount;
                        $toAccountBalance += $amount;

                        // Update the balances in the database
                        $updateBalanceSqlSource = "UPDATE accounts SET balance = ? WHERE account_type = ? AND user_id = ?";
                        $stmtUpdateSource = $conn->prepare($updateBalanceSqlSource);
                        $stmtUpdateSource->bind_param("dss", $currentBalance, $accountType, $_SESSION['user_id']);
                        $stmtUpdateSource->execute();
                        $stmtUpdateSource->close();

                        $updateBalanceSqlTarget = "UPDATE accounts SET balance = ? WHERE account_type = ? AND user_id = ?";
                        $stmtUpdateTarget = $conn->prepare($updateBalanceSqlTarget);
                        $stmtUpdateTarget->bind_param("dss", $toAccountBalance, $toAccount, $_SESSION['user_id']);
                        $stmtUpdateTarget->execute();
                        $stmtUpdateTarget->close();

                        // Insert a new transaction record
                        $description = "Transfer Recorded"; 
                        $stmtInsertTransaction->execute();
                    } else {
                        $transferError = "Insufficient balance for transfer.";
                    }
                } else {
                    $transferError = "Invalid target account for transfer.";
                }
            } else {
                $transferError = "Transfer target account not provided.";
            }
        }

        // Update the current balance in the session
        $_SESSION['balances'][$accountType] = $currentBalance;
    }
}

// Close the transactions statement if it's set
if (isset($stmtInsertTransaction)) {
    $stmtInsertTransaction->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Transaction</title>
    <link rel="stylesheet" type="text/css" href="style-account.css">
</head>
<body>
    <table>
    <div id = "top">
        <tr class = "accountTypeTitle">
            <td>
                <h1 style="font-size: 30px;"><?php echo $accountType; ?> Account</h1>
            </td>
            <tr class = "menuButton">
                <td>
                    <form method="post" action="accounts.php">
                        <input type="hidden" name="account_type" value="<?php echo $accountType; ?>">
                        <button id ="backButton" type="submit">Back</button>
                    </form>
                </td>
                <td id ="blankButton">
                </td>
                <td>
                    <div class="navbar">
                        <form method="post">
                            <button id="logoutButton" name="logout" type="submit">LogOut</button>
                        </form>
                    </div>
                </td>     

            </tr>

        </tr>
    </div>

    <div id=box>
        <div id = "currentB">

            <tr>
                <td id ="middlePart" colspan="2">
                    <p id ="currentB">Your Current Balance: <span id="realAmount">$ <?php echo $currentBalance; ?></span></p>
                    <h2><?php echo $service; ?> Service</h2>
                </td>
            </tr>
        </div>
        <tr>
            <td id ="phpPart">
                <form method="post">
                    <?php
                    if ($service == 'Deposit' || $service == 'Withdraw' || $service == 'Transfer') {
                        echo "<input id ='typeofAccount' type='hidden' name='service' value='$service'><br>";
                        echo "Amount: <input type='number' id ='inputAmount' name='amount' step='0.01' required><br>";
                        if ($service == 'Transfer') {
                            // Dropdown menu for selecting the target account
                            echo "<div id ='transferSel'>Transfer to Account: <select name='to_account' required>";
                            foreach ($accountTypes as $type) {
                                if ($type !== $accountType) {
                                    echo "<option value='$type'>$type</option>";
                                }
                            }
                            echo "</select></div><br>";
                        }
                        echo "<br><button id='submitButton' type='submit'>Submit</button><br>";
                    } else {
                        echo "Invalid service request.";
                    }
                    ?>
                </form>
                <div class="error-message">
                    <?php
                    echo $amountError;
                    echo $transferError;
                    ?>
                </div>
            </td>
        </tr>
                </div>
    </table>
</body>
</html>

