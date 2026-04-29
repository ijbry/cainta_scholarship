<?php
session_start();
if(isset($_SESSION['student_id'])) {
    header("Location: student/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | Cainta Scholarship</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', sans-serif; }
        .login-wrapper {
            min-height: 100vh; display: flex;
            align-items: center; justify-content: center;
        }
        .login-card {
            background: white; border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px; width: 100%; max-width: 420px;
        }
        .login-header {
            background: #1A3A6B; color: white;
            border-radius: 12px; padding: 20px;
            text-align: center; margin-bottom: 28px;
        }
        .login-header h4 { margin: 0; font-size: 18px; font-weight: 600; }
        .login-header p { margin: 4px 0 0; font-size: 13px; opacity: 0.8; }
        .form-label { font-size: 13px; font-weight: 500; color: #444; }
        .form-control {
            border-radius: 8px; font-size: 14px;
            padding: 10px 14px; border: 1px solid #dde1e7;
        }
        .form-control:focus {
            border-color: #1A3A6B;
            box-shadow: 0 0 0 3px rgba(26,58,107,0.1);
        }
        .btn-login {
            background: #1A3A6B; color: white; border: none;
            border-radius: 8px; padding: 11px; font-size: 15px;
            font-weight: 500; width: 100%; transition: background 0.2s;
        }
        .btn-login:hover { background: #14305a; color: white; }
        .eye-btn {
            border: 1px solid #dde1e7; border-left: none;
            background: white; border-radius: 0 8px 8px 0;
            padding: 10px 14px; cursor: pointer; color: #666;
        }
        .eye-btn:hover { color: #1A3A6B; }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <h4><i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship Program</h4>
            <p>Student Portal — Municipality of Cainta, Rizal</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle me-1"></i>
            Invalid email or password. Please try again.
        </div>
        <?php endif; ?>

        <?php if(isset($_GET['logout'])): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            You have been logged out successfully.
        </div>
        <?php endif; ?>

        <form action="student_login_process.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-envelope text-secondary"></i>
                    </span>
                    <input type="email" name="email" class="form-control border-start-0"
                            placeholder="Enter your email" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-lock text-secondary"></i>
                    </span>
                    <input type="password" name="password" id="student-password"
                            class="form-control border-start-0 border-end-0"
                            placeholder="Enter your password" required>
                    <button type="button" class="eye-btn" onclick="togglePassword('student-password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-login mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>
        </form>

        <p class="text-center text-muted" style="font-size: 13px;">
            Don't have an account? <a href="student_register.php">Register here</a>
        </p>
        <p class="text-center text-muted mt-2 mb-0" style="font-size: 12px;">
            &copy; <?= date('Y') ?> Municipality of Cainta — Scholarship Office
        </p>
    </div>
</div>

<?php include 'chatbot_widget.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if(input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
</body>
</html>