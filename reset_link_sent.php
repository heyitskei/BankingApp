<!DOCTYPE html>
<html lang="en">
<head>
    <title>Password Reset Link Sent</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .center-container {
            text-align: center;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
        }

        p {
            color: #555;
            margin-bottom: 20px;
        }

        button {
            background-color: white;
            color: #333;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button:hover {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="center-container">
        <h1>Password Reset Link Sent</h1>
        <p>An email with instructions to reset your password has been sent to your email address.</p>
        <form method="post" action="start.php">
            <button type="submit">Log Out</button>
        </form>
    </div>
</body>
</html>
