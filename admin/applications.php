<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if(isset($_POST['update_status'])) {
    $app_id = $_POST['application_id'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];
    
    $stmt = $pdo->prepare("UPDATE applications SET status=?, remarks=? WHERE application_id=?");
    $stmt->execute([$status, $remarks, $app_id]);

    // Send email notification if approved, rejected or incomplete
    if(in_array($status, ['approved', 'rejected', 'incomplete'])) {
        $student = $pdo->prepare("
            SELECT s.email, s.first_name, s.last_name 
            FROM applications a 
            JOIN students s ON a.scholar_id = s.student_id 
            WHERE a.application_id = ?
        ");
        $student->execute([$app_id]);
        $std = $student->fetch();
        
        if($std) {
            require_once '../includes/mailer.php';
            $name = $std['first_name'] . ' ' . $std['last_name'];
            sendStatusEmail($std['email'], $name, $status, $remarks);
        }
    }

    header("Location: applications.php?success=1");
    exit();
}

// Filter
$filter = $_GET['filter'] ?? 'all';
if($filter != 'all') {
    $stmt = $pdo->prepare("
        SELECT a.*, s.first_name, s.last_name, s.email, s.contact_no
        FROM applications a
        JOIN students s ON a.scholar_id = s.student_id
        WHERE a.status = ?
        ORDER BY a.submitted_at DESC
    ");
    $stmt->execute([$filter]);
} else {
    $stmt = $pdo->query("
        SELECT a.*, s.first_name, s.last_name, s.email, s.contact_no
        FROM applications a
        JOIN students s ON a.scholar_id = s.student_id
        ORDER BY a.submitted_at DESC
    ");
}
$applications = $stmt->fetchAll();

// Count per status
$counts = $pdo->query("SELECT status, COUNT(*) as cnt FROM applications GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications | Cainta Scholarship</title>
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
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d1e7dd; color: #0f5132; }
        .badge-rejected { background: #f8d7da; color: #842029; }
        .badge-for_review { background: #cfe2ff; color: #084298; }
        .badge-incomplete { background: #f8d7da; color: #842029; }
        .filter-btn { border-radius: 20px; font-size: 13px; padding: 5px 14px; }
        .filter-btn.active { background: #1A3A6B; color: white; border-color: #1A3A6B; }
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
        <a href="applications.php" class="nav-link active"><i class="bi bi-file-earmark-text"></i> Applications</a>
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
            <h5 class="mb-0 fw-bold">Applications</h5>
            <small class="text-muted">Review and process scholarship applications</small>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i> Application status updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Filter Buttons -->
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a href="applications.php" class="btn btn-outline-secondary filter-btn <?= $filter=='all'?'active':'' ?>">
            All <span class="badge bg-secondary ms-1"><?= array_sum($counts) ?></span>
        </a>
        <a href="applications.php?filter=pending" class="btn btn-outline-warning filter-btn <?= $filter=='pending'?'active':'' ?>">
            Pending <span class="badge bg-warning text-dark ms-1"><?= $counts['pending'] ?? 0 ?></span>
        </a>
        <a href="applications.php?filter=for_review" class="btn btn-outline-primary filter-btn <?= $filter=='for_review'?'active':'' ?>">
            For Review <span class="badge bg-primary ms-1"><?= $counts['for_review'] ?? 0 ?></span>
        </a>
        <a href="applications.php?filter=approved" class="btn btn-outline-success filter-btn <?= $filter=='approved'?'active':'' ?>">
            Approved <span class="badge bg-success ms-1"><?= $counts['approved'] ?? 0 ?></span>
        </a>
        <a href="applications.php?filter=rejected" class="btn btn-outline-danger filter-btn <?= $filter=='rejected'?'active':'' ?>">
            Rejected <span class="badge bg-danger ms-1"><?= $counts['rejected'] ?? 0 ?></span>
        </a>
        <a href="applications.php?filter=incomplete" class="btn btn-outline-secondary filter-btn <?= $filter=='incomplete'?'active':'' ?>">
            Incomplete <span class="badge bg-secondary ms-1"><?= $counts['incomplete'] ?? 0 ?></span>
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Applicant</th>
                            <th>Contact</th>
                            <th>School Year</th>
                            <th>Semester</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($applications)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-file-earmark fs-3 d-block mb-2"></i>
                                No applications found.
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
                            <td><?= htmlspecialchars($app['contact_no']) ?></td>
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
                                    onclick="viewApplication(<?= htmlspecialchars(json_encode($app)) ?>)">
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
                <div id="appDocuments"></div>
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
                        <label class="form-label fw-bold">Remarks / Notes</label>
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
function viewApplication(app) {
    document.getElementById('modal_app_id').value = app.application_id;
    document.getElementById('modal_status').value = app.status;
    document.getElementById('modal_remarks').value = app.remarks || '';

    let details = `
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Applicant Name</div>
        <div class="fw-bold">${app.last_name}, ${app.first_name}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Email</div>
        <div>${app.email}</div></div>
        <div class="col-md-4"><div class="text-muted" style="font-size:12px;">School Year</div>
        <div>${app.school_year}</div></div>
        <div class="col-md-4"><div class="text-muted" style="font-size:12px;">Semester</div>
        <div>${app.semester} Semester</div></div>
        <div class="col-md-4"><div class="text-muted" style="font-size:12px;">Status</div>
        <div><span class="badge badge-${app.status}">${app.status.replace('_', ' ')}</span></div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Father's Name</div>
        <div>${app.father_name || 'N/A'}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Father's Occupation</div>
        <div>${app.father_occupation || 'N/A'}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Mother's Name</div>
        <div>${app.mother_name || 'N/A'}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Mother's Occupation</div>
        <div>${app.mother_occupation || 'N/A'}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Submitted</div>
        <div>${app.submitted_at}</div></div>
    `;
    document.getElementById('appDetails').innerHTML = details;

    let modal = new bootstrap.Modal(document.getElementById('reviewModal'));
    modal.show();
}
</script>
</body>
</html>