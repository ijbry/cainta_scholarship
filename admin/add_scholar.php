<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO scholars 
        (first_name, last_name, middle_name, birthdate, gender, address, barangay, contact_no, email, school, course, year_level, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    
    $stmt->execute([
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['middle_name'],
        $_POST['birthdate'],
        $_POST['gender'],
        $_POST['address'],
        $_POST['barangay'],
        $_POST['contact_no'],
        $_POST['email'],
        $_POST['school'],
        $_POST['course'],
        $_POST['year_level'],
    ]);
    
    header("Location: scholars.php?success=added");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Scholar | Cainta Scholarship</title>
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
        <a href="scholars.php" class="nav-link active"><i class="bi bi-people"></i> Scholars</a>
        <a href="applications.php" class="nav-link"><i class="bi bi-file-earmark-text"></i> Applications</a>
        <a href="disbursements.php" class="nav-link"><i class="bi bi-cash-stack"></i> Disbursements</a>
        <a href="inventory.php" class="nav-link"><i class="bi bi-box-seam"></i> Inventory</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart"></i> Reports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-gear"></i> Users</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 20px;">
        <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="topbar">
        <div>
            <h5 class="mb-0 fw-bold">Add New Scholar</h5>
            <small class="text-muted">Fill in the scholar's information</small>
        </div>
        <a href="scholars.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Scholars
        </a>
    </div>

    <div class="card">
        <div class="card-body p-4">
            <form method="POST">
                <!-- Personal Information -->
                <p class="section-title"><i class="bi bi-person me-1"></i> Personal Information</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Birthdate <span class="text-danger">*</span></label>
                        <input type="date" name="birthdate" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_no" class="form-control" placeholder="09XXXXXXXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Barangay <span class="text-danger">*</span></label>
                        <select name="barangay" class="form-select" required>
                            <option value="">Select barangay</option>
                            <option>Brgy. Sto. Nino</option>
                            <option>Brgy. San Andres</option>
                            <option>Brgy. San Isidro</option>
                            <option>Brgy. San Juan</option>
                            <option>Brgy. San Roque</option>
                            <option>Brgy. Kalinaw</option>
                            <option>Brgy. Dayap</option>
                            <option>Brgy. Dela Paz</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Home Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="2" required></textarea>
                    </div>
                </div>

                <!-- School Information -->
                <p class="section-title"><i class="bi bi-book me-1"></i> School Information</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">School/University <span class="text-danger">*</span></label>
                        <input type="text" name="school" class="form-control" required placeholder="e.g. PLM, PUP, DLSU">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Course <span class="text-danger">*</span></label>
                        <input type="text" name="course" class="form-control" required placeholder="e.g. BSIT, BSCS">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Year Level <span class="text-danger">*</span></label>
                        <select name="year_level" class="form-select" required>
                            <option value="">Select year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                            <option value="5">5th Year</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="scholars.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Scholar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>