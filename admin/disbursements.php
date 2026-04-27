<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle add disbursement
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_disbursement'])) {
    $scholar_id = $_POST['scholar_id'];
    $school_year = $_POST['school_year'];
    $semester = $_POST['semester'];
    $amount = $_POST['amount'] === 'custom' ? $_POST['custom_amount'] : $_POST['amount'];
    $stmt = $pdo->prepare("INSERT INTO disbursements (scholar_id, school_year, semester, amount, status, released_by) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->execute([$scholar_id, $school_year, $semester, $amount, $_SESSION['user_id']]);
    header("Location: disbursements.php?success=added");
    exit();
}

// Handle release
if(isset($_GET['release'])) {
    $id = $_GET['release'];
    $stmt = $pdo->prepare("UPDATE disbursements SET status='released', released_at=NOW() WHERE disbursement_id=?");
    $stmt->execute([$id]);
    header("Location: disbursements.php?success=released");
    exit();
}

// Get all disbursements
$disbursements = $pdo->query("
    SELECT d.*, s.first_name, s.last_name, s.barangay,
            u.full_name as released_by_name
    FROM disbursements d
    LEFT JOIN students s ON d.scholar_id = s.student_id
    LEFT JOIN users u ON d.released_by = u.user_id
    ORDER BY d.created_at DESC
")->fetchAll();

// Get approved students for dropdown
$students = $pdo->query("
    SELECT DISTINCT s.student_id, s.first_name, s.last_name, s.barangay
    FROM students s
    JOIN applications a ON s.student_id = a.scholar_id
    WHERE a.status = 'approved'
    ORDER BY s.last_name ASC
")->fetchAll();

// Stats
$total_released = $pdo->query("SELECT SUM(amount) FROM disbursements WHERE status='released'")->fetchColumn();
$total_pending = $pdo->query("SELECT COUNT(*) FROM disbursements WHERE status='pending'")->fetchColumn();
$total_scholars_disbursed = $pdo->query("SELECT COUNT(DISTINCT scholar_id) FROM disbursements WHERE status='released'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disbursements | Cainta Scholarship</title>
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
        <a href="applications.php" class="nav-link"><i class="bi bi-file-earmark-text"></i> Applications</a>
        <a href="disbursements.php" class="nav-link active"><i class="bi bi-cash-stack"></i> Disbursements</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart"></i> Reports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-gear"></i> Users</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 20px;">
        <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="topbar">
        <div>
            <h5 class="mb-0 fw-bold">Disbursements</h5>
            <small class="text-muted">Manage scholar allowance releases</small>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-1"></i> Add Disbursement
        </button>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>
        <?= $_GET['success'] == 'added' ? 'Disbursement added successfully!' : 'Allowance released successfully!' ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card" style="border-color: #198754;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-cash me-1"></i>Total Released</div>
                <div style="font-size:26px; font-weight:700; color:#198754;">₱<?= number_format($total_released ?? 0, 2) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-color: #f0a500;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-hourglass me-1"></i>Pending Release</div>
                <div style="font-size:26px; font-weight:700; color:#f0a500;"><?= $total_pending ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-color: #1A3A6B;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-people me-1"></i>Scholars Paid</div>
                <div style="font-size:26px; font-weight:700; color:#1A3A6B;"><?= $total_scholars_disbursed ?></div>
            </div>
        </div>
    </div>

    <!-- Disbursements Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Scholar</th>
                            <th>Barangay</th>
                            <th>School Year</th>
                            <th>Semester</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Released At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($disbursements)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-cash-stack fs-3 d-block mb-2"></i>
                                No disbursements yet.
                            </td>
                        </tr>
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
                            <td>
                                <?= $d['released_at'] ? date('M d, Y', strtotime($d['released_at'])) : '—' ?>
                            </td>
                            <td>
                                <?php if($d['status'] == 'pending'): ?>
                                <a href="disbursements.php?release=<?= $d['disbursement_id'] ?>"
                                    class="btn btn-sm btn-success"
                                    onclick="return confirm('Release allowance for <?= htmlspecialchars($d['first_name']) ?>?')">
                                    <i class="bi bi-check-circle me-1"></i> Release
                                </a>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:13px;">Released</span>
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

<!-- Add Disbursement Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#1A3A6B; color:white;">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Add Disbursement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="add_disbursement" value="1">
                    <div class="mb-3">
                        <label class="form-label">Scholar <span class="text-danger">*</span></label>
                        <select name="scholar_id" class="form-select" required>
                            <option value="">Select approved scholar</option>
                            <?php foreach($students as $st): ?>
                            <option value="<?= $st['student_id'] ?>">
                                <?= htmlspecialchars($st['last_name'] . ', ' . $st['first_name']) ?> — <?= $st['barangay'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">School Year <span class="text-danger">*</span></label>
                        <select name="school_year" class="form-select" required>
                            <option value="2025-2026">2025-2026</option>
                            <option value="2026-2027">2026-2027</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select" required>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="amount"
                                        id="amount1" value="2500" checked required>
                                <label class="form-check-label" for="amount1">
                                    ₱2,500.00 — Standard Allowance
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="amount"
                                        id="amount2" value="5000" required>
                                <label class="form-check-label" for="amount2">
                                    ₱5,000.00 — Special Allowance
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="amount"
                                        id="amount3" value="custom" required>
                                <label class="form-check-label" for="amount3">
                                    Custom Amount
                                </label>
                            </div>
                            <div id="custom-amount-div" style="display:none;">
                                <div class="input-group mt-1">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" id="custom-amount-input"
                                            name="custom_amount" class="form-control"
                                            placeholder="Enter custom amount" min="1">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../chatbot_widget.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('input[name="amount"]').forEach(radio => {
    radio.addEventListener('change', function () {
        const customDiv = document.getElementById('custom-amount-div');
        const customInput = document.getElementById('custom-amount-input');

        if (this.value === 'custom') {
            customDiv.style.display = 'block';
            customInput.required = true;
        } else {
            customDiv.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    });
});

// Reset modal state when closed
document.getElementById('addModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('amount1').checked = true;
    document.getElementById('custom-amount-div').style.display = 'none';
    document.getElementById('custom-amount-input').required = false;
    document.getElementById('custom-amount-input').value = '';
});

</script>
</body>
</html>