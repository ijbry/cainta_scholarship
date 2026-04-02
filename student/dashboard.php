<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['student_id'])) {
    header("Location: ../student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student info
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Get latest application
$app = $pdo->prepare("SELECT * FROM applications WHERE scholar_id = ? ORDER BY submitted_at DESC LIMIT 1");
$app->execute([$student_id]);
$application = $app->fetch();

// Get disbursements
$disb = $pdo->prepare("SELECT * FROM disbursements WHERE scholar_id = ? ORDER BY created_at DESC");
$disb->execute([$student_id]);
$disbursements = $disb->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Cainta Scholarship</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; }
        .topnav {
            background: #1A3A6B; padding: 12px 24px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .topnav-brand { color: white; font-size: 16px; font-weight: 600; text-decoration: none; }
        .topnav-links a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; margin-left: 20px; }
        .topnav-links a:hover { color: white; }
        .topnav-links a.active { color: white; font-weight: 500; }
        .main-content { padding: 24px; max-width: 1100px; margin: 0 auto; }
        .welcome-banner {
            background: linear-gradient(135deg, #1A3A6B, #2E75B6);
            color: white; border-radius: 12px; padding: 24px; margin-bottom: 24px;
        }
        .stat-card {
            background: white; border-radius: 12px; padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-left: 4px solid;
        }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d1e7dd; color: #0f5132; }
        .badge-rejected { background: #f8d7da; color: #842029; }
        .badge-for_review { background: #cfe2ff; color: #084298; }
        .badge-incomplete { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>

<!-- Top Navigation -->
<div class="topnav">
    <a href="dashboard.php" class="topnav-brand">
        <i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship Portal
    </a>
    <div class="topnav-links">
        <a href="dashboard.php" class="active">Home</a>
        <a href="application.php">My Application</a>
        <a href="status.php">Status</a>
        <a href="disbursements.php">Disbursements</a>
        <a href="../student_logout.php">Logout</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">

    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h5 class="mb-1">Good day, <?= htmlspecialchars($student['first_name']) ?>! 👋</h5>
        <p class="mb-0 opacity-75">
            <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?> —
            <?= htmlspecialchars($student['barangay']) ?>
        </p>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card" style="border-color: #1A3A6B;">
                <div style="font-size:13px; color:#666;">Application Status</div>
                <?php if($application): ?>
                <span class="badge badge-<?= $application['status'] ?> mt-1" style="font-size:14px;">
                    <?= ucfirst(str_replace('_', ' ', $application['status'])) ?>
                </span>
                <?php else: ?>
                <div style="font-size:14px; font-weight:600; color:#1A3A6B; margin-top:4px;">No Application Yet</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-color: #198754;">
                <div style="font-size:13px; color:#666;">Total Disbursed</div>
                <div style="font-size:22px; font-weight:700; color:#198754;">
                    ₱<?= number_format(array_sum(array_column($disbursements, 'amount')), 2) ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-color: #f0a500;">
                <div style="font-size:13px; color:#666;">Member Since</div>
                <div style="font-size:16px; font-weight:600; color:#f0a500; margin-top:4px;">
                    <?= date('M Y', strtotime($student['created_at'])) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Application Status -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title fw-bold mb-3">
                        <i class="bi bi-file-earmark-text me-1"></i> My Application
                    </h6>
                    <?php if($application): ?>
                    <table class="table table-sm">
                        <tr><td class="text-muted">School Year</td><td><?= $application['school_year'] ?></td></tr>
                        <tr><td class="text-muted">Semester</td><td><?= $application['semester'] ?> Semester</td></tr>
                        <tr><td class="text-muted">GWA</td><td><?= $application['gwa'] ?></td></tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td><span class="badge badge-<?= $application['status'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $application['status'])) ?>
                            </span></td>
                        </tr>
                        <tr><td class="text-muted">Submitted</td><td><?= date('M d, Y', strtotime($application['submitted_at'])) ?></td></tr>
                    </table>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-plus fs-3 text-muted d-block mb-2"></i>
                        <p class="text-muted mb-3">You haven't submitted an application yet.</p>
                        <a href="application.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Apply Now
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Disbursement History -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title fw-bold mb-3">
                        <i class="bi bi-cash-stack me-1"></i> Disbursement History
                    </h6>
                    <?php if(empty($disbursements)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-cash fs-3 text-muted d-block mb-2"></i>
                        <p class="text-muted">No disbursements yet.</p>
                    </div>
                    <?php else: ?>
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr><th>Period</th><th>Amount</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($disbursements as $d): ?>
                            <tr>
                                <td><?= $d['semester'] ?> Sem <?= $d['school_year'] ?></td>
                                <td>₱<?= number_format($d['amount'], 2) ?></td>
                                <td>
                                    <span class="badge <?= $d['status']=='released'?'bg-success':'bg-warning text-dark' ?>">
                                        <?= ucfirst($d['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>