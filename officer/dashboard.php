<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'officer') {
    header("Location: ../login.php");
    exit();
}

// Handle status update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $app_id = $_POST['application_id'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];
    $stmt = $pdo->prepare("UPDATE applications SET status=?, remarks=? WHERE application_id=?");
    $stmt->execute([$status, $remarks, $app_id]);
    header("Location: dashboard.php?success=1");
    exit();
}

// Get stats
$total_pending = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='pending'")->fetchColumn();
$total_review = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='for_review'")->fetchColumn();
$total_approved = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='approved'")->fetchColumn();
$total_rejected = $pdo->query("SELECT COUNT(*) FROM applications WHERE status='rejected'")->fetchColumn();

// Get all applications
$applications = $pdo->query("
    SELECT a.*, s.first_name, s.last_name, s.email, s.contact_no, s.barangay
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
    <title>Officer Dashboard | Cainta Scholarship</title>
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
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship
        <small>Officer Panel</small>
    </div>
    <nav>
        <a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="topbar">
        <div>
            <h5 class="mb-0 fw-bold">Officer Dashboard</h5>
            <small class="text-muted">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</small>
        </div>
        <div class="text-muted" style="font-size:13px;">
            <i class="bi bi-calendar3 me-1"></i><?= date('F d, Y') ?>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i> Application status updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #f0a500;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-hourglass me-1"></i>Pending</div>
                <div style="font-size:26px; font-weight:700; color:#f0a500;"><?= $total_pending ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #0d6efd;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-eye me-1"></i>For Review</div>
                <div style="font-size:26px; font-weight:700; color:#0d6efd;"><?= $total_review ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #198754;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-check-circle me-1"></i>Approved</div>
                <div style="font-size:26px; font-weight:700; color:#198754;"><?= $total_approved ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-color: #dc3545;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-x-circle me-1"></i>Rejected</div>
                <div style="font-size:26px; font-weight:700; color:#dc3545;"><?= $total_rejected ?></div>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">All Applications</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Applicant</th>
                            <th>Barangay</th>
                            <th>School Year</th>
                            <th>Semester</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($applications)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-file-earmark fs-3 d-block mb-2"></i>
                                No applications yet.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($applications as $i => $app): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($app['last_name'] . ', ' . $app['first_name']) ?></strong>
                                <div style="font-size:12px; color:#666;"><?= htmlspecialchars($app['email']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($app['barangay']) ?></td>
                            <td><?= $app['school_year'] ?></td>
                            <td><?= $app['semester'] ?> Sem</td>
                            <td><?= date('M d, Y', strtotime($app['submitted_at'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $app['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $app['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                    onclick="reviewApp(<?= htmlspecialchars(json_encode($app)) ?>)">
                                    <i class="bi bi-eye"></i> Review
                                </button>
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

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#1A3A6B; color:white;">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>Review Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3" id="appDetails"></div>
                <hr>
                <form method="POST">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="application_id" id="modal_app_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Update Status</label>
                        <select name="status" id="modal_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="for_review">For Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="incomplete">Incomplete</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea name="remarks" id="modal_remarks" class="form-control" rows="3"
                                    placeholder="Add remarks for the applicant..."></textarea>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Decision
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function reviewApp(app) {
    document.getElementById('modal_app_id').value = app.application_id;
    document.getElementById('modal_status').value = app.status;
    document.getElementById('modal_remarks').value = app.remarks || '';

    let details = `
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Applicant</div>
        <div class="fw-bold">${app.last_name}, ${app.first_name}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Email</div>
        <div>${app.email}</div></div>
        <div class="col-md-4"><div class="text-muted" style="font-size:12px;">Barangay</div>
        <div>${app.barangay}</div></div>
        <div class="col-md-4"><div class="text-muted" style="font-size:12px;">School Year</div>
        <div>${app.school_year}</div></div>
        <div class="col-md-4"><div class="text-muted" style="font-size:12px;">Semester</div>
        <div>${app.semester} Semester</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Father</div>
        <div>${app.father_name || 'N/A'} — ${app.father_occupation || 'N/A'}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Mother</div>
        <div>${app.mother_name || 'N/A'} — ${app.mother_occupation || 'N/A'}</div></div>
    `;
    document.getElementById('appDetails').innerHTML = details;
    new bootstrap.Modal(document.getElementById('reviewModal')).show();
}
</script>
</body>
</html>