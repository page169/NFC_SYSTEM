<?php
session_start();
include './db.php';

// Debugging: Print session variables to check their values
error_log("Attempts: " . ($_SESSION['attempts'] ?? 'None'));
error_log("Lockout: " . ($_SESSION['lockout'] ?? 'None'));

// Initialize variables
$username = '';
$password = '';
$errorMessage = '';
$maxAttempts = 3; // Maximum allowed attempts
$lockoutTime = 30; // Lockout duration in seconds

// Check if the user is locked out
if (isset($_SESSION['lockout']) && time() < $_SESSION['lockout']) {
    $remainingTime = $_SESSION['lockout'] - time();
    $errorMessage = "Account is locked. Please try again after " . ceil($remainingTime / 60) . " seconds.";
} else {
    // Reset lockout if the time has passed
    unset($_SESSION['lockout']);
}

// Initialize attempts if not already set
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($errorMessage)) {
    $username = trim($_POST['username']); // Trim to avoid unnecessary spaces
    $password = trim($_POST['password']);

    // Prepare the SQL statement
    $query = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) { // Check if preparation was successful
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Reset login attempts on success
                unset($_SESSION['attempts']);
                unset($_SESSION['lockout']);

                // Store user information in session
                $_SESSION['id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                $sql = "INSERT INTO system_logs (`admin_uid`, `activity`)
                VALUES ('" . $user['id'] . "', '" . $user['username'] . " logged in')";

                if (!$conn->query($sql) === TRUE) {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                    $errorMessage = "";
                }

                // Redirect to the dashboard or any other page after successful login
                header("Location: admin/admin_dashboard.php");
                exit();
            } else {
                $errorMessage = "Incorrect password.";
            }
        } else {
            $errorMessage = "User not found.";
        }
    } else {
        $errorMessage = "Database error: Unable to prepare statement.";
    }

    // Handle failed login attempts
    $_SESSION['attempts']++;

    if ($_SESSION['attempts'] >= $maxAttempts) {
        $_SESSION['lockout'] = time() + $lockoutTime; // Set lockout time
        $errorMessage = "Too many failed attempts. Account is locked for 30 seconds.";
    } else {
        $remainingAttempts = $maxAttempts - $_SESSION['attempts'];
        $errorMessage .= " You have $remainingAttempts attempt(s) left.";
    }

    // Debugging: Log session attempts and lockout time
    error_log("Attempts after failure: " . $_SESSION['attempts']);
    error_log("Lockout set to: " . ($_SESSION['lockout'] ?? 'None'));
}

// Close the connection if not needed anymore
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TPC Security and Monitoring System</title>
    <!-- Site favicon -->
    <link rel="icon" type="image/png" href="asset/layout/log.png" sizes="16x16">
    <link rel="icon" type="image/png" href="asset/layout/log.png" sizes="32x32">
    <link rel="icon" type="image/png" href="asset/layout/log.png" sizes="48x48">
    <link rel="shortcut icon" href="asset/layout/log.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="asset/login.css">
    
    <style>
        .main-body {
            position: relative;
            overflow: hidden;
            min-height: calc(100vh - 71px);
        }

        .main-body::before {
            content: '';
            position: absolute;
            inset: 0;

            background-image: url('asset/images/logo.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;

            filter: blur(3px);
            opacity: 0.15;

            z-index: 0;
        }

        .main-body>* {
            position: relative;
            z-index: 1;
        }
    </style>

<body class="main-body">
    <div class="main-container">
        <div class="login-logo">
            <img src="asset/images/logo.png" alt="Logo" /> <!-- Adjust the logo path as needed -->
            <div class="footer-text">
                TPC Security and Monitoring System
            </div>
        </div>

        <div class="login-box">
            <div class="login-box-body">
                <p id="error-message" class="alert <?php echo $errorMessage ? '' : 'hidden'; ?>">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </p>

                <p class="login-box-msg">Welcome to Admin Panel!</p>

                <form id="login-form" action="" method="post">
                    <div class="form-group has-feedback">
                        <input type="text" id="username" class="form-control" name="username" required placeholder="Username">
                        <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    </div>

                    <div class="form-group has-feedback">
                        <input type="password" id="password" class="form-control" name="password" required placeholder="Password">
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <button type="submit" class="btn">Log In</button>
                        </div>
                    </div>
                </form>

                <div class="social-auth-links text-center">
                    <!-- Add social auth links if needed -->
                </div>
            </div>
        </div>
    </div>
</body>

</html>