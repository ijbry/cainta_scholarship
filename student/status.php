<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['student_id'])) {
    header("Location: ../student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get all applications
$apps = $pdo->prepare("SELECT * FROM applications WHERE scholar_id = ? ORDER BY submitted_at DESC");
$apps->execute([$student_id]);
$applications = $apps->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Status | Cainta Scholarship</title>
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
        .main-content { padding: 24px; max-width: 900px; margin: 0 auto; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .badge-pending { background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 20px; font-size: 13px; }
        .badge-approved { background: #d1e7dd; color: #0f5132; padding: 6px 12px; border-radius: 20px; font-size: 13px; }
        .badge-rejected { background: #f8d7da; color: #842029; padding: 6px 12px; border-radius: 20px; font-size: 13px; }
        .badge-for_review { background: #cfe2ff; color: #084298; padding: 6px 12px; border-radius: 20px; font-size: 13px; }
        .badge-incomplete { background: #f8d7da; color: #842029; padding: 6px 12px; border-radius: 20px; font-size: 13px; }
        .timeline { position: relative; padding-left: 30px; }
        .timeline::before {
            content: ''; position: absolute; left: 8px; top: 0; bottom: 0;
            width: 2px; background: #dee2e6;
        }
        .timeline-item { position: relative; margin-bottom: 24px; }
        .timeline-dot {
            position: absolute; left: -26px; width: 16px; height: 16px;
            border-radius: 50%; top: 2px; border: 2px solid white;
        }
    </style>
</head>
<body>
<div class="topnav">
    <a href="dashboard.php" class="topnav-brand">
        <i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship Portal
    </a>
    <div class="topnav-links">
        <a href="dashboard.php">Home</a>
        <a href="application.php">My Application</a>
        <a href="status.php" class="active">Status</a>
        <a href="disbursements.php">Disbursements</a>
        <a href="../student_logout.php">Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="mb-4">
        <h5 class="mb-0 fw-bold">Application Status</h5>
        <small class="text-muted">Track your scholarship application progress</small>
    </div>

    <?php if(empty($applications)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-file-earmark-x fs-3 text-muted d-block mb-3"></i>
            <p class="text-muted mb-3">You haven't submitted an application yet.</p>
            <a href="application.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Apply Now
            </a>
        </div>
    </div>
    <?php else: ?>
    <?php foreach($applications as $app): ?>
    <div class="card mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h6 class="fw-bold mb-1"><?= $app['school_year'] ?> — <?= $app['semester'] ?> Semester</h6>
                    <small class="text-muted">Submitted: <?= date('F d, Y', strtotime($app['submitted_at'])) ?></small>
                </div>
                <span class="badge-<?= $app['status'] ?>">
                    <?= ucfirst(str_replace('_', ' ', $app['status'])) ?>
                </span>
            </div>

            <?php if($app['remarks']): ?>
            <div class="alert alert-info mb-4">
                <i class="bi bi-chat-left-text me-1"></i>
                <strong>Message from Scholarship Office:</strong><br>
                <?= htmlspecialchars($app['remarks']) ?>
            </div>
            <?php endif; ?>

            <!-- Timeline -->
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot" style="background: #1A3A6B;"></div>
                    <div class="fw-500" style="font-size:14px;">Application Submitted</div>
                    <div class="text-muted" style="font-size:13px;"><?= date('F d, Y', strtotime($app['submitted_at'])) ?></div>
                    <div class="text-muted" style="font-size:12px;">Your application has been received by the scholarship office.</div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot" style="background: <?= in_array($app['status'], ['for_review','approved','rejected','incomplete']) ? '#0d6efd' : '#dee2e6' ?>;"></div>
                    <div style="font-size:14px; color: <?= in_array($app['status'], ['for_review','approved','rejected','incomplete']) ? '#000' : '#aaa' ?>; font-weight:500;">
                        Document Verification
                    </div>
                    <div style="font-size:12px; color:#aaa;">Scholarship office reviews submitted documents.</div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot" style="background: <?= in_array($app['status'], ['approved','rejected']) ? ($app['status']=='approved'?'#198754':'#dc3545') : '#dee2e6' ?>;"></div>
                    <div style="font-size:14px; color: <?= in_array($app['status'], ['approved','rejected']) ? '#000' : '#aaa' ?>; font-weight:500;">
                        <?php if($app['status'] == 'approved'): ?>
                            Application Approved ✅
                        <?php elseif($app['status'] == 'rejected'): ?>
                            Application Rejected ❌
                        <?php elseif($app['status'] == 'incomplete'): ?>
                            Incomplete — Please resubmit ⚠️
                        <?php else: ?>
                            Final Decision
                        <?php endif; ?>
                    </div>
                    <div style="font-size:12px; color:#aaa;">
                        <?php if($app['status'] == 'approved'): ?>
                            Congratulations! Your application has been approved.
                        <?php elseif($app['status'] == 'rejected'): ?>
                            We regret to inform you that your application was not approved.
                        <?php else: ?>
                            Pending final decision from the scholarship committee.
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($app['status'] == 'approved'): ?>
                <div class="timeline-item">
                    <div class="timeline-dot" style="background: #198754;"></div>
                    <div style="font-size:14px; font-weight:500; color:#198754;">Allowance Disbursement</div>
                    <div style="font-size:12px; color:#aaa;">Your allowance will be released by the cashier.</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>