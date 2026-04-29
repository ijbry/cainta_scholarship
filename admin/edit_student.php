<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if(!$id) { header("Location: students.php"); exit(); }

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if(!$s) { header("Location: students.php"); exit(); }

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("UPDATE students SET
        first_name=?, last_name=?, middle_name=?, birthdate=?,
        gender=?, contact_no=?, barangay=?, address=?, email=?
        WHERE student_id=?");
    $stmt->execute([
        $_POST['first_name'], $_POST['last_name'], $_POST['middle_name'],
        $_POST['birthdate'], $_POST['gender'], $_POST['contact_no'],
        $_POST['barangay'], $_POST['address'], $_POST['email'], $id
    ]);
    header("Location: students.php?success=updated");
    exit();
}

$barangays = ['Brgy. San Andres','Brgy. San Isidro','Brgy. San Juan','Brgy. San Roque','Brgy. Santa Rosa','Brgy. Santo Domingo','Brgy. Santo Niño'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student | Cainta Scholarship</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; }
        .sidebar {
            width: 240px; min-height: 100vh; background: #1A3A6B;
            position: fixed; top: 0; left: 0; padding-top: 20px; z-index: 100;
        }
        .sidebar-brand {
            color: white; font-size: 15px; font-weight: 600;
            padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 10px;
        }
        .sidebar-brand small { display: block; font-size: 11px; opacity: 0.7; font-weight: 400; }
        .nav-link {
            color: rgba(255,255,255,0.75); padding: 10px 20px; font-size: 14px;
            display: flex; align-items: center; gap: 10px;
        }
        .nav-link:hover, .nav-link.active {
            color: white; background: rgba(255,255,255,0.1); border-left: 3px solid #fff;
        }
        .main-content { margin-left: 240px; padding: 24px; }
        .topbar {
            background: white; border-radius: 12px; padding: 14px 20px;
            margin-bottom: 24px; display: flex; justify-content: space-between;
            align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .section-title {
            font-size: 13px; font-weight: 600; color: #1A3A6B;
            border-bottom: 2px solid #1A3A6B; padding-bottom: 6px; margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship
        <small>Admin Panel</small>
    </div>
    <nav>
        <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="scholars.php" class="nav-link"><i class="bi bi-people"></i> Scholars</a>
        <a href="students.php" class="nav-link active"><i class="bi bi-person-lines-fill"></i> Students</a>
        <a href="applications.php" class="nav-link"><i class="bi bi-file-earmark-text"></i> Applications</a>
        <a href="disbursements.php" class="nav-link"><i class="bi bi-cash-stack"></i> Disbursements</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart"></i> Reports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-gear"></i> Users</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 20px;">
        <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="topbar">
        <div>
            <h5 class="mb-0 fw-bold">Edit Student</h5>
            <small class="text-muted">Update student information</small>
        </div>
        <a href="students.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Students
        </a>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST">
                <p class="section-title"><i class="bi bi-person me-1"></i> Personal Information</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control"
                                value="<?= htmlspecialchars($s['last_name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control"
                                value="<?= htmlspecialchars($s['first_name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control"
                                value="<?= htmlspecialchars($s['middle_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Birthdate</label>
                        <input type="date" name="birthdate" class="form-control"
                                value="<?= $s['birthdate'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="Male" <?= $s['gender']=='Male'?'selected':'' ?>>Male</option>
                            <option value="Female" <?= $s['gender']=='Female'?'selected':'' ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" name="contact_no" class="form-control"
                                inputmode="numeric" pattern="[0-9]*" maxlength="11"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                value="<?= htmlspecialchars($s['contact_no'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($s['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Barangay <span class="text-danger">*</span></label>
                        <select name="barangay" class="form-select" required>
                            <option value="">Select barangay</option>
                            <?php foreach($barangays as $b): ?>
                            <option value="<?= $b ?>" <?= $s['barangay']==$b?'selected':'' ?>><?= $b ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Home Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($s['address'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="students.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Update Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../chatbot_widget.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>