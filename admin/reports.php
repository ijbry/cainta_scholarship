<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get stats for reports
$total_scholars = $pdo->query("SELECT COUNT(*) FROM scholars WHERE status='active'")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_applications = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$approved = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='approved'")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='pending'")->fetchColumn();
$rejected = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='rejected'")->fetchColumn();
$total_disbursed = $pdo->query("SELECT SUM(amount) FROM disbursements WHERE status='released'")->fetchColumn();
$total_inventory = $pdo->query("SELECT COUNT(*) FROM inventory_items")->fetchColumn();
$low_stock = $pdo->query("SELECT COUNT(*) FROM inventory_items WHERE quantity <= reorder_level")->fetchColumn();

// Get applications by barangay
$by_barangay = $pdo->query("
    SELECT s.barangay, COUNT(*) as total,
    SUM(CASE WHEN a.status='approved' THEN 1 ELSE 0 END) as approved
    FROM applications a
    JOIN students s ON a.scholar_id = s.student_id
    GROUP BY s.barangay
    ORDER BY total DESC
")->fetchAll();

// Get recent disbursements
$disbursements = $pdo->query("
    SELECT d.*, s.first_name, s.last_name, s.barangay
    FROM disbursements d
    LEFT JOIN students s ON d.scholar_id = s.student_id
    ORDER BY d.created_at DESC
    LIMIT 10
")->fetchAll();

// Get all applications for full report
$all_apps = $pdo->query("
    SELECT a.*, s.first_name, s.last_name, s.barangay, s.email, s.contact_no
    FROM applications a
    JOIN students s ON a.scholar_id = s.student_id
    ORDER BY a.submitted_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Cainta Scholarship</title>
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
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
        }
    </style>
</head>
<body>
<div class="sidebar no-print">
    <div class="sidebar-brand">
        <i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship
        <small>Admin Panel</small>
    </div>
    <nav>
        <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="scholars.php" class="nav-link"><i class="bi bi-people"></i> Scholars</a>
        <a href="applications.php" class="nav-link"><i class="bi bi-file-earmark-text"></i> Applications</a>
        <a href="disbursements.php" class="nav-link"><i class="bi bi-cash-stack"></i> Disbursements</a>
        <a href="inventory.php" class="nav-link"><i class="bi bi-box-seam"></i> Inventory</a>
        <a href="reports.php" class="nav-link active"><i class="bi bi-bar-chart"></i> Reports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-gear"></i> Users</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 20px;">
        <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="topbar no-print">
        <div>
            <h5 class="mb-0 fw-bold">Reports</h5>
            <small class="text-muted">Scholarship program summary and analytics</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Report Header (visible when printed) -->
    <div class="text-center mb-4 d-none d-print-block">
        <h4 class="fw-bold">Municipality of Cainta — Scholarship Program</h4>
        <h5>Scholarship Program Report</h5>
        <p class="text-muted">Generated: <?= date('F d, Y h:i A') ?></p>
        <hr>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #1A3A6B;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-people me-1"></i>Registered Students</div>
                <div style="font-size:26px; font-weight:700; color:#1A3A6B;"><?= $total_students ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #0d6efd;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-file-earmark me-1"></i>Total Applications</div>
                <div style="font-size:26px; font-weight:700; color:#0d6efd;"><?= $total_applications ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #198754;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-check-circle me-1"></i>Approved</div>
                <div style="font-size:26px; font-weight:700; color:#198754;"><?= $approved ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #198754;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-cash me-1"></i>Total Disbursed</div>
                <div style="font-size:26px; font-weight:700; color:#198754;">₱<?= number_format($total_disbursed ?? 0, 2) ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Applications by Status -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart me-1"></i> Applications by Status</h6>
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr><th>Status</th><th>Count</th><th>Percentage</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge badge-approved">Approved</span></td>
                                <td><?= $approved ?></td>
                                <td><?= $total_applications > 0 ? round(($approved/$total_applications)*100, 1) : 0 ?>%</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-pending">Pending</span></td>
                                <td><?= $pending ?></td>
                                <td><?= $total_applications > 0 ? round(($pending/$total_applications)*100, 1) : 0 ?>%</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-rejected">Rejected</span></td>
                                <td><?= $rejected ?></td>
                                <td><?= $total_applications > 0 ? round(($rejected/$total_applications)*100, 1) : 0 ?>%</td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td>Total</td>
                                <td><?= $total_applications ?></td>
                                <td>100%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Applications by Barangay -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-geo-alt me-1"></i> Applications by Barangay</h6>
                    <?php if(empty($by_barangay)): ?>
                    <p class="text-muted text-center py-3">No data available.</p>
                    <?php else: ?>
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr><th>Barangay</th><th>Applications</th><th>Approved</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($by_barangay as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['barangay']) ?></td>
                                <td><?= $b['total'] ?></td>
                                <td><?= $b['approved'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- All Applications Table -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-table me-1"></i> Complete Application List</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Barangay</th>
                            <th>School Year</th>
                            <th>Semester</th>
                            <th>Father</th>
                            <th>Mother</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($all_apps)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-3">No applications yet.</td></tr>
                        <?php else: ?>
                        <?php foreach($all_apps as $i => $app): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($app['last_name'] . ', ' . $app['first_name']) ?></strong></td>
                            <td><?= htmlspecialchars($app['barangay']) ?></td>
                            <td><?= $app['school_year'] ?></td>
                            <td><?= $app['semester'] ?> Sem</td>
                            <td><?= htmlspecialchars($app['father_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($app['mother_name'] ?? 'N/A') ?></td>
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

    <!-- Disbursement Report -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-cash-stack me-1"></i> Disbursement Report</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Scholar</th>
                            <th>Barangay</th>
                            <th>School Year</th>
                            <th>Semester</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Released</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($disbursements)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-3">No disbursements yet.</td></tr>
                        <?php else: ?>
                        <?php foreach($disbursements as $i => $d): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($d['last_name'] . ', ' . $d['first_name']) ?></strong></td>
                            <td><?= htmlspecialchars($d['barangay']) ?></td>
                            <td><?= $d['school_year'] ?></td>
                            <td><?= $d['semester'] ?> Sem</td>
                            <td><strong>₱<?= number_format($d['amount'], 2) ?></strong></td>
                            <td>
                                <span class="badge <?= $d['status']=='released' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= ucfirst($d['status']) ?>
                                </span>
                            </td>
                            <td><?= $d['released_at'] ? date('M d, Y', strtotime($d['released_at'])) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-2">
                <strong>Total Released: ₱<?= number_format($total_disbursed ?? 0, 2) ?></strong>
            </div>
        </div>
    </div>

    <!-- Inventory Report -->
    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-box-seam me-1"></i> Inventory Report</h6>
            <?php
            $inventory = $pdo->query("SELECT * FROM inventory_items ORDER BY item_name ASC")->fetchAll();
            ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>#</th><th>Item</th><th>Category</th><th>Unit</th><th>Stock</th><th>Reorder Level</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if(empty($inventory)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No inventory items yet.</td></tr>
                        <?php else: ?>
                        <?php foreach($inventory as $i => $item): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= htmlspecialchars($item['unit']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= $item['reorder_level'] ?></td>
                            <td>
                                <?php if($item['quantity'] == 0): ?>
                                <span class="badge bg-danger">Out of Stock</span>
                                <?php elseif($item['quantity'] <= $item['reorder_level']): ?>
                                <span class="badge bg-warning text-dark">Low Stock</span>
                                <?php else: ?>
                                <span class="badge bg-success">In Stock</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>