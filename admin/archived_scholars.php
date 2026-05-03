<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle restore
if(isset($_GET['restore'])) {
    $id = $_GET['restore'];
    $pdo->prepare("UPDATE scholars SET is_archived=0, archived_at=NULL, archive_reason=NULL WHERE scholar_id=?")->execute([$id]);
    header("Location: scholars.php?success=restored");
    exit();
}

// Handle permanent delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Get scholar email first
    $scholar = $pdo->prepare("SELECT email FROM scholars WHERE scholar_id = ?");
    $scholar->execute([$id]);
    $sch = $scholar->fetch();

    if($sch) {
        // Delete their application so they can apply again
        $pdo->prepare("
            DELETE FROM applications
            WHERE scholar_id = (
                SELECT student_id FROM students WHERE email = ?
            )
        ")->execute([$sch['email']]);
    }

    // Now delete from scholars
    $pdo->prepare("DELETE FROM scholars WHERE scholar_id=?")->execute([$id]);
    header("Location: archived_scholars.php?success=deleted");
    exit();
}

// Get archived scholars
$scholars = $pdo->query("SELECT * FROM scholars WHERE is_archived=1 ORDER BY archived_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Scholars | Cainta Scholarship</title>
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
        .archive-banner {
            background: linear-gradient(135deg, #f0a500, #e09400);
            color: white; border-radius: 12px; padding: 16px 20px;
            margin-bottom: 24px; display: flex; align-items: center; gap: 12px;
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
        <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart"></i> Reports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-gear"></i> Users</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 20px;">
        <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="topbar">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-archive me-2"></i>Archived Scholars</h5>
            <small class="text-muted">View and restore archived scholar records</small>
        </div>
        <a href="scholars.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Scholars
        </a>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>
        <?= $_GET['success'] == 'deleted' ? 'Scholar permanently deleted!' : 'Scholar restored successfully!' ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="archive-banner">
        <i class="bi bi-archive fs-4"></i>
        <div>
            <div style="font-weight:600;">Archive Storage</div>
            <div style="font-size:13px; opacity:0.85;">
                <?= count($scholars) ?> archived scholar(s) — Records are safely stored and can be restored anytime.
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if(empty($scholars)): ?>
            <div class="text-center py-5">
                <i class="bi bi-archive fs-3 text-muted d-block mb-3"></i>
                <p class="text-muted">No archived scholars yet.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Barangay</th>
                            <th>School</th>
                            <th>Course</th>
                            <th>Reason Archived</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($scholars as $i => $s): ?>
                        <tr style="opacity: 0.85;">
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></strong></td>
                            <td><?= htmlspecialchars($s['barangay']) ?></td>
                            <td><?= htmlspecialchars($s['school']) ?></td>
                            <td><?= htmlspecialchars($s['course']) ?></td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    <?= htmlspecialchars($s['archive_reason'] ?? 'No reason') ?>
                                </span>
                            </td>
                            <td><?= $s['archived_at'] ? date('M d, Y', strtotime($s['archived_at'])) : '—' ?></td>
                            <td>
                                <a href="archived_scholars.php?restore=<?= $s['scholar_id'] ?>"
                                    class="btn btn-sm btn-success"
                                    onclick="return confirm('Restore this scholar?')">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                </a>
                                <a href="archived_scholars.php?delete=<?= $s['scholar_id'] ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Permanently delete? This cannot be undone!')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../chatbot_widget.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>