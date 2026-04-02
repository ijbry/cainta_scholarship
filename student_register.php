<?php
session_start();
require_once 'includes/db.php';

if(isset($_SESSION['student_id'])) {
    header("Location: student/dashboard.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $middle_name = trim($_POST['middle_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $contact_no = trim($_POST['contact_no']);
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $barangay = $_POST['barangay'];
    $address = trim($_POST['address']);

    if(empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif(strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if email already exists
        $check = $pdo->prepare("SELECT student_id FROM students WHERE email = ?");
        $check->execute([$email]);
        if($check->fetch()) {
            $error = 'Email address is already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO students 
                (first_name, last_name, middle_name, email, password, contact_no, birthdate, gender, barangay, address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $first_name, $last_name, $middle_name, $email,
                $hashed, $contact_no, $birthdate, $gender, $barangay, $address
            ]);
            $success = 'Account created successfully! You can now login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | Cainta Scholarship</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', sans-serif; }
        .register-wrapper {
            min-height: 100vh; display: flex;
            align-items: center; justify-content: center; padding: 30px 15px;
        }
        .register-card {
            background: white; border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 36px; width: 100%; max-width: 680px;
        }
        .register-header {
            background: #1A3A6B; color: white;
            border-radius: 12px; padding: 20px;
            text-align: center; margin-bottom: 28px;
        }
        .register-header h4 { margin: 0; font-size: 18px; font-weight: 600; }
        .register-header p { margin: 4px 0 0; font-size: 13px; opacity: 0.8; }
        .section-title {
            font-size: 12px; font-weight: 600; color: #1A3A6B;
            text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 2px solid #e8f0fe; padding-bottom: 6px; margin-bottom: 16px;
        }
        .form-label { font-size: 13px; font-weight: 500; color: #444; }
        .form-control, .form-select {
            border-radius: 8px; font-size: 14px;
            padding: 9px 12px; border: 1px solid #dde1e7;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1A3A6B;
            box-shadow: 0 0 0 3px rgba(26,58,107,0.1);
        }
        .btn-register {
            background: #1A3A6B; color: white; border: none;
            border-radius: 8px; padding: 11px; font-size: 15px;
            font-weight: 500; width: 100%; transition: background 0.2s;
        }
        .btn-register:hover { background: #14305a; color: white; }
    </style>
</head>
<body>
<div class="register-wrapper">
    <div class="register-card">
        <div class="register-header">
            <h4><i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship Program</h4>
            <p>Student Registration — Municipality of Cainta, Rizal</p>
        </div>

        <?php if($error): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-1"></i><?= $error ?></div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i><?= $success ?>
            <a href="student_login.php" class="alert-link">Click here to login</a>
        </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Personal Information -->
            <p class="section-title"><i class="bi bi-person me-1"></i> Personal Information</p>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" required
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" required
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control"
                           value="<?= htmlspecialchars($_POST['middle_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Birthdate <span class="text-danger">*</span></label>
                    <input type="date" name="birthdate" class="form-control" required
                           value="<?= $_POST['birthdate'] ?? '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select gender</option>
                        <option value="Male" <?= ($_POST['gender'] ?? '')==='Male'?'selected':'' ?>>Male</option>
                        <option value="Female" <?= ($_POST['gender'] ?? '')==='Female'?'selected':'' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_no" class="form-control" placeholder="09XXXXXXXXX"
                           value="<?= htmlspecialchars($_POST['contact_no'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Barangay <span class="text-danger">*</span></label>
                    <select name="barangay" class="form-select" required>
                        <option value="">Select barangay</option>
                        <?php
                        $barangays = ['Brgy. Sto. Nino','Brgy. San Andres','Brgy. San Isidro','Brgy. San Juan','Brgy. San Roque','Brgy. Kalinaw','Brgy. Dayap','Brgy. Dela Paz'];
                        foreach($barangays as $b): ?>
                        <option value="<?= $b ?>" <?= ($_POST['barangay'] ?? '')===$b?'selected':'' ?>><?= $b ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Home Address <span class="text-danger">*</span></label>
                    <input type="text" name="address" class="form-control" required
                           value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                </div>
            </div>

            <!-- Account Information -->
            <p class="section-title"><i class="bi bi-shield-lock me-1"></i> Account Information</p>
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required
                           placeholder="This will be your username"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required
                           placeholder="Minimum 6 characters">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" required
                           placeholder="Re-enter your password">
                </div>
            </div>

            <button type="submit" class="btn-register mb-3">
                <i class="bi bi-person-plus me-2"></i>Create Account
            </button>

            <p class="text-center text-muted" style="font-size: 13px;">
                Already have an account? <a href="student_login.php">Login here</a>
            </p>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
