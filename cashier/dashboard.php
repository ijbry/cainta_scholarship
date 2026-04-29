<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'cashier') {
    header("Location: ../login.php");
    exit();
}

// Search scholar
$scholar = null;
if(isset($_GET['scholar_id']) && $_GET['scholar_id']) {
    $stmt = $pdo->prepare("SELECT s.*, a.status as app_status FROM students s LEFT JOIN applications a ON s.student_id = a.scholar_id AND a.status = 'approved' WHERE s.student_id = ?");
    $stmt->execute([$_GET['scholar_id']]);
    $scholar = $stmt->fetch();
}

// Get all approved scholars for search
$scholars = $pdo->query("
    SELECT s.student_id, s.first_name, s.last_name, s.barangay
    FROM students s
    JOIN applications a ON s.student_id = a.scholar_id
    WHERE a.status = 'approved'
    ORDER BY s.last_name ASC
")->fetchAll();

// Pending disbursements count
$pending_count = $pdo->query("SELECT COUNT(*) FROM disbursements WHERE status='pending'")->fetchColumn();

// Recent disbursements
$transactions = $pdo->query("
    SELECT d.*, s.first_name, s.last_name, s.barangay
    FROM disbursements d
    LEFT JOIN students s ON d.scholar_id = s.student_id
    ORDER BY d.created_at DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier | Cainta Scholarship</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f4f8; }
        .topbar {
            background: #1A3A6B; padding: 12px 24px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .topbar-brand { color: white; font-size: 16px; font-weight: 600; }
        .topbar-right { color: rgba(255,255,255,0.8); font-size: 13px; }
        .main-content { padding: 24px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .scholar-card {
            background: linear-gradient(135deg, #1A3A6B, #2E75B6);
            color: white; border-radius: 12px; padding: 20px; margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="topbar">
    <span class="topbar-brand"><i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship — Cashier Counter</span>
    <div class="topbar-right d-flex align-items-center gap-3">
        <a href="../admin/disbursements.php" class="btn btn-warning btn-sm position-relative">
            <i class="bi bi-cash-stack me-1"></i> Disbursements
            <?php if($pending_count > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $pending_count ?>
            </span>
            <?php endif; ?>
        </a>
        <span><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['full_name']) ?></span>
        <a href="../logout.php" class="btn btn-sm btn-outline-light">
            <i class="bi bi-box-arrow-left me-1"></i>Logout
        </a>
    </div>
</div>

<div class="main-content">
    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>
        <?= $_GET['success'] == 'released' ? 'Allowance released successfully!' : 'Action completed successfully!' ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if($pending_count > 0): ?>
    <div class="alert alert-warning d-flex align-items-center justify-content-between">
        <div>
            <i class="bi bi-exclamation-circle me-1"></i>
            <strong><?= $pending_count ?></strong> pending disbursement<?= $pending_count > 1 ? 's' : '' ?> waiting to be released.
        </div>
        <a href="../admin/disbursements.php" class="btn btn-warning btn-sm">
            <i class="bi bi-cash-stack me-1"></i> Release Now
        </a>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- LEFT: Scholar Lookup -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-search me-1"></i> Scholar Lookup</h6>
                    <form method="GET">
                        <div class="input-group mb-3">
                            <select name="scholar_id" class="form-select">
                                <option value="">Search scholar...</option>
                                <?php foreach($scholars as $s): ?>
                                <option value="<?= $s['student_id'] ?>"
                                    <?= (isset($_GET['scholar_id']) && $_GET['scholar_id'] == $s['student_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?> — <?= $s['barangay'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Find
                            </button>
                        </div>
                    </form>

                    <?php if($scholar): ?>
                    <div class="scholar-card">
                        <div style="font-size:18px; font-weight:600;"><?= htmlspecialchars($scholar['last_name'] . ', ' . $scholar['first_name']) ?></div>
                        <div style="opacity:0.8; font-size:13px;">ID: <?= $scholar['student_id'] ?> · <?= htmlspecialchars($scholar['barangay']) ?></div>
                        <div class="mt-2"><span class="badge bg-success">Approved Scholar</span></div>
                    </div>

                    <?php
                    // Get this scholar's pending disbursements
                    $scholar_disb = $pdo->prepare("SELECT * FROM disbursements WHERE scholar_id=? ORDER BY created_at DESC");
                    $scholar_disb->execute([$scholar['student_id']]);
                    $scholar_disbursements = $scholar_disb->fetchAll();
                    ?>
                    <?php if(!empty($scholar_disbursements)): ?>
                    <h6 class="fw-bold mb-2">Disbursements for this Scholar</h6>
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr><th>School Year</th><th>Sem</th><th>Amount</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach($scholar_disbursements as $sd): ?>
                        <tr>
                            <td><?= $sd['school_year'] ?></td>
                            <td><?= $sd['semester'] ?></td>
                            <td>₱<?= number_format($sd['amount'], 2) ?></td>
                            <td>
                                <span class="badge <?= $sd['status']=='released' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= ucfirst($sd['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($sd['status'] == 'pending'): ?>
                                <a href="../admin/disbursements.php?release=<?= $sd['disbursement_id'] ?>"
                                    class="btn btn-sm btn-success"
                                    onclick="return confirm('Release ₱<?= number_format($sd['amount'],2) ?> for this scholar?')">
                                    <i class="bi bi-check-circle me-1"></i> Release
                                </a>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:12px;"><i class="bi bi-check2-all"></i> Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="text-center text-muted py-3" style="font-size:13px;">
                        <i class="bi bi-info-circle me-1"></i> No disbursements found for this scholar.
                    </div>
                    <?php endif; ?>

                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-person-badge fs-3 d-block mb-2"></i>
                        Search for a scholar to view their disbursements
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: Recent Disbursements -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-1"></i> Recent Transactions</h6>
                    <?php if(empty($transactions)): ?>
                    <div class="text-center text-muted py-4">No transactions yet.</div>
                    <?php else: ?>
                    <?php foreach($transactions as $t): ?>
                    <div style="border-bottom: 1px solid #f0f0f0; padding: 10px 0;">
                        <div style="font-size:13px; font-weight:500;">
                            <?= htmlspecialchars($t['last_name'] . ', ' . $t['first_name']) ?>
                        </div>
                        <div style="font-size:12px; color:#666;">
                            ₱<?= number_format($t['amount'], 2) ?> —
                            <span class="badge <?= $t['status']=='released' ? 'bg-success' : 'bg-warning text-dark' ?>" style="font-size:10px;">
                                <?= ucfirst($t['status']) ?>
                            </span>
                        </div>
                        <div style="font-size:11px; color:#aaa;">
                            <?= $t['school_year'] ?> · <?= $t['semester'] ?> Sem
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>