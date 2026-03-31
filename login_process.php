<?php
session_start();
require_once 'includes/db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if(empty($username) || empty($password)) {
        header("Location: login.php?error=1");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        switch($user['role']) {
            case 'admin':
                header("Location: admin/dashboard.php");
                break;
            case 'officer':
                header("Location: officer/dashboard.php");
                break;
            case 'cashier':
                header("Location: cashier/dashboard.php");
                break;
            default:
                header("Location: login.php?error=1");
        }
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>