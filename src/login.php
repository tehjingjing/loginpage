<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');

$user = [
    'alice' => 'pass123',
    'bob' => 'hunter2'
];

// Redirect to dashboard if already logged in
if (!empty($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit();
}


// Initialize error message variable
$error = '';

// lock the login form for 30seconds after 3 failed attempts
$lockedOut = false;

if (isset($_SESSION['lockout_until']) && time() < $_SESSION['lockout_until']) {
    $lockedOut = true;
    $secondLeft = $_SESSION['lockout_until'] - time();
    $error = "Too many failed attempts. Please try again in $secondLeft seconds.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$lockedOut) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $rememberMe = isset($_POST['rememberMe']);

    if (isset($user[$username]) && hash_equals($user[$username], $password)) {
        // prevent session fixation by regenerating the session ID upon successful login
        session_regenerate_id(true); 

        // Successful login, reset failed attempts, store session
        unset($_SESSION['failed_attempts']);
        unset($_SESSION['lockout_until']);
        $_SESSION['username'] = $username;
        $_SESSION['last_activity'] = time(); // Update last activity time

        // Cookie and session management for last login time and remember username
        $previousLogin = $_COOKIE['last_login'] ?? null;
        setcookie('last_login', date('Y-m-d H:i:s'), time() + (86400 * 30), "/"); 

        if ($previousLogin !== null) {
        $_SESSION['previous_login_display'] = $previousLogin; 
        } else {
        // Force a friendly indicator into the session so it doesn't fall back to the raw cookie
        $_SESSION['previous_login_display'] = 'This is your first login.';
        }

        // 'Rememeber My Username' cookie functionality (does not store password)
        if ($rememberMe) {
            setcookie('remember_username', $username, time() + (86400 * 30), "/"); // 30 days expiration
        } else {
            setcookie('remember_username', '', time() - 3600, '/'); // Expire the cookie
        }

        header('Location: dashboard.php');
        exit();
    } else {
        // Failed login attempt, increment failed attempts
        $_SESSION['failed_attempts'] = ($_SESSION['failed_attempts'] ?? 0) + 1;
        if ($_SESSION['failed_attempts'] >= 3) {
            $_SESSION['lockout_until'] = time() + 30; // Lock out for 30 seconds
            $lockedOut = true;
            $error = "Too many failed attempts. Please try again in 30 seconds.";
        } else {
            $remaining = 3 - $_SESSION['failed_attempts'];
            $error = "Invalid username or password. You have $remaining attempt(s) left.";
        }
    }
}

$rememberedUsername = htmlspecialchars($_COOKIE['remember_username'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, Helvetica, sans-serif;
        }

        body{
            background:#f7f8fc;
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        .container{
            position:relative;
        }

        .login-box{
           width:500px;
           background:#fff;
           padding:40px 22px;
           border-radius:4px;
           box-shadow:0 5px 18px rgba(0,0,0,.05);
        }

        .login-box h1{
          text-align:center;
          margin-bottom:25px;
          color:#28324d;
          font-size:42px;
        }

        .form-group{
          margin-bottom:18px;
        }

        .form-group label{
           display:block;
           margin-bottom:8px;
           color:#2f3856;
           font-size:15px;
           font-weight:600;
        }

        .input-box{
          position:relative;
          width:100%;
        }

        .input-box input{
          width:100%;
          height:48px;
          border:1px solid #dfe3ec;
          border-radius:6px;
          padding:0 45px 0 15px;
          font-size:15px;
          outline:none;
          transition:.3s;
        }

        .input-box input:focus{
          border-color:#5a6df0;
        }

        .input-box i{
           position:absolute;
           right:15px;
           top:50%;
           transform:translateY(-50%);
           color:#7b8094;
           cursor:pointer;
        }

        .remember{
          margin: 15px 0 20px;
        }

        .remember label{
          display:flex;
          align-items:center;
          gap:8px;
          color:#2f3856;
          font-size:16px;
        }

        .remember input{
           accent-color:#556be9;
           width:16px;
           height:16px;
        }

        button{
           width:100%;
           height:45px;
           border:none;
           background:#556be9;
           color:#fff;
           font-size:17px;
           border-radius:5px;
           cursor:pointer;
           transition:.3s;
           margin-top:30px;
        }

        button:hover{
           background:#4459db;
        }

        .error{
            color: #dc3545
        }

        @media(max-width:550px){

        .login-box{
           width:90%;
        }

        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
          <h1>Log In</h1>
            <?php if (!empty($_GET['message'])): ?>
                <div class="alert-message" style="background-color: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #fca5a5; font-size: 0.9rem;">
                    <?= htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
               <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        <form method="post" action="login.php">
            <div class="form-group">
                <label>Username:</label>
                <div class="input-box">
                 <input type="text" name="username" value="<?php echo $rememberedUsername; ?>" <?php echo $lockedOut ? 'disabled': '';?>>
                </div> 
            </div>

            <div class="form-group">
                <label>Password: </label>
                <div class="input-box">
                  <input type="password" name="password" <?php echo $lockedOut ? 'disabled': ''; ?>>
                </div>
            </div>
            <div class='checkbox'>
                    <input type="checkbox" name="rememberMe" <?php echo $rememberedUsername ? 'checked' : ''; ?> <?php echo $lockedOut ? 'disabled': '';?>> Remember Me
            </div>
            <button type="submit" <?php echo $lockedOut ? '' : ''; ?>>Log In</button>
        </form>
    </div>
</body>
</html>