<?php
session_start();

if (!empty($_SESSION['username'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
?>