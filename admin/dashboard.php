<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get stats
$total_scholars = $pdo->query("SELECT COUNT(*) FROM scholars WHERE status='active'")->fetchColumn();
$total_applications = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='pending'")->fetchColumn();
$total_disbursed = $pdo->query("SELECT SUM(amount) FROM disbursements WHERE status='released'")->fetchColumn();
$low_stock = $pdo->query("SELECT COUNT(*) FROM inventory_items WHERE quantity <= reorder_level")->fetchColumn();

// Recent applications
$recent_apps = $pdo->query("
    SELECT a.*, s.first_name, s.last_name, s.school, s.barangay
    FROM applications a
    JOIN scholars s ON a.scholar_id = s.scholar_id
    ORDER BY a.submitted_at DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Cainta Scholarship</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; }
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #1A3A6B;
            position: fixed;
            top: 0; left: 0;
            padding-top: 20px;
            z-index: 100;
        }
        .sidebar-brand {
            color: white;
            font-size: 15px;
            font-weight: 600;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 10px;
        }
        .sidebar-brand small {
            display: block;
            font-size: 11px;
            opacity: 0.7;
            font-weight: 400;
        }
        .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left: 3px solid #fff;
        }
        .main-content {
            margin-left: 240px;
            padding: 24px;
        }
        .topbar {
            background: white;
            border-radius: 12px;
            padding: 14px 20px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid;
        }
        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin: 4px 0;
        }
        .stat-card .stat-label {
            font-size: 13px;
            color: #666;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d1e7dd; color: #0f5132; }
        .badge-rejected { background: #f8d7da; color: #842029; }
        .badge-for_review { background: #cfe2ff; color: #084298; }
        .badge-incomplete { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship
        <small>Admin Panel</small>
    </div>
    <nav>
        <a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="scholars.php" class="nav-link"><i class="bi bi-people"></i> Scholars</a>
        <a href="applications.php" class="nav-link"><i class="bi bi-file-earmark-text"></i> Applications</a>
        <a href="disbursements.php" class="nav-link"><i class="bi bi-cash-stack"></i> Disbursements</a>
        <a href="inventory.php" class="nav-link"><i class="bi bi-box-seam"></i> Inventory</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart"></i> Reports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-gear"></i> Users</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 20px;">
        <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Topbar -->
    <div class="topbar">
        <div>
            <h5 class="mb-0 fw-600">Dashboard</h5>
            <small class="text-muted">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</small>
        </div>
        <div class="text-muted" style="font-size:13px;">
            <i class="bi bi-calendar3 me-1"></i><?= date('F d, Y') ?>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #1A3A6B;">
                <div class="stat-label"><i class="bi bi-people me-1"></i>Total Scholars</div>
                <div class="stat-value" style="color:#1A3A6B;"><?= $total_scholars ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #f0a500;">
                <div class="stat-label"><i class="bi bi-hourglass-split me-1"></i>Pending Applications</div>
                <div class="stat-value" style="color:#f0a500;"><?= $total_applications ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #198754;">
                <div class="stat-label"><i class="bi bi-cash me-1"></i>Total Disbursed</div>
                <div class="stat-value" style="color:#198754;">₱<?= number_format($total_disbursed ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #dc3545;">
                <div class="stat-label"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock Items</div>
                <div class="stat-value" style="color:#dc3545;"><?= $low_stock ?></div>
            </div>
        </div>
    </div>

    <!-- Recent Applications -->
    <div class="card">
        <div class="card-body">
            <h6 class="card-title fw-bold mb-3">Recent Applications</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                        <!-- <th>Barangay</th> -->
                            <th>School</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recent_apps)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No applications yet.</td></tr>
                        <?php else: ?>
                        <?php foreach($recent_apps as $app): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($app['last_name'] . ', ' . $app['first_name']) ?></strong></td>
                            <td><?= htmlspecialchars($app['barangay']) ?></td>
                            <td><?= htmlspecialchars($app['school']) ?></td>
                            <td><?= $app['semester'] ?> Sem <?= $app['school_year'] ?></td>
                            <td><span class="badge badge-<?= $app['status'] ?>"><?= ucfirst(str_replace('_', ' ', $app['status'])) ?></span></td>
                            <td><?= date('M d, Y', strtotime($app['submitted_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
    <?php include '../chatbot_widget.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>