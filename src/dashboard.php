<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

// Auto logout after 2  minutes of inactivity
$timeoutSeconds = 120; // 2 minutes
if (!empty ($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeoutSeconds)) {
    // Session has expired due to inactivity
    $_SESSION = [];
    session_destroy();
    setcookie (session_name(), '', time() - 3600, '/'); // Expire the session cookie
    header('Location: login.php?message=Session expired. Please log in again.');
    exit();
}

// Protect the dashboard page from unauthorized access to login.php
if (empty($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}


// Update last activity time
$_SESSION['last_activity'] = time(); 

// Sanitize username for output, ENT_QUOTES to prevent XSS attacks, and UTF-8 encoding to avoid broken characters
$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); 

// Retrieve and sanitize the last login time from the cookie, read the cookie value and display it on the dashboard page, ?? null to handle the case where the cookie is not set (first login)
$lastLoginCookie = htmlspecialchars($_COOKIE['last_login'] ?? 'This is your first login.', ENT_QUOTES, 'UTF-8');

// Retrieve and sanitize the previous login time from the session, read the session value and display it on the dashboard page
// check if the session key exists, if true sanitize the value, if false set it to null
$previousLogin = isset($_SESSION['previous_login_display']) // check if session variable is set
    ? htmlspecialchars($_SESSION['previous_login_display'], ENT_QUOTES, 'UTF-8')
    : null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 40px;
        }

        .card {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
        }

        .box {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .logout-button {
            display: inline-block;
            padding: 10px 20px;
            background: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .logout-button:hover {
            background: #d32f2f;
        }

        .note {
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Welcome, <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>!</h2>
        <p class="muted">You are logged in via PHP session.</p>

        <div class="box">
            <strong>Last Login Time:</strong><br>
            <?php
            if ($previousLogin) {
             echo htmlspecialchars($previousLogin, ENT_QUOTES, 'UTF-8');
            } else {
            echo htmlspecialchars($lastLoginCookie, ENT_QUOTES, 'UTF-8');
            }
        ?>
        </div>
        
        <a class="logout-button" href="logout.php">Log Out</a>
        <p class="note">Session auto-logout after 2 minutes of inactivity.</p>
    </div>
</body>
</html>
