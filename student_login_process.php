<?php
session_start();
require_once 'includes/db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $student = $stmt->fetch();

    if($student && password_verify($password, $student['password'])) {
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
        $_SESSION['student_email'] = $student['email'];
        header("Location: student/dashboard.php");
        exit();
    } else {
        header("Location: student_login.php?error=1");
        exit();
    }
} else {
    header("Location: student_login.php");
    exit();
}
?>