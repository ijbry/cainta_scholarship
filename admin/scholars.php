<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Get scholar email first
    $scholar = $pdo->prepare("SELECT email FROM scholars WHERE scholar_id = ?");
    $scholar->execute([$id]);
    $sch = $scholar->fetch();

    if($sch) {
        // Get student_id from email
        $student = $pdo->prepare("SELECT student_id FROM students WHERE email = ?");
        $student->execute([$sch['email']]);
        $std = $student->fetch();

        if($std) {
            // Delete documents first
            $pdo->prepare("
                DELETE FROM documents WHERE application_id IN (
                    SELECT application_id FROM applications WHERE scholar_id = ?
                )
            ")->execute([$std['student_id']]);

            // Delete disbursements
            $pdo->prepare("DELETE FROM disbursements WHERE scholar_id = ?")->execute([$std['student_id']]);

            // Delete applications completely
            $pdo->prepare("DELETE FROM applications WHERE scholar_id = ?")->execute([$std['student_id']]);
        }
    }

    // Delete from scholars
    $pdo->prepare("DELETE FROM scholars WHERE scholar_id = ?")->execute([$id]);

    header("Location: scholars.php?success=deleted");
    exit();
}

// Handle archive
if(isset($_GET['archive'])) {
    $id = $_GET['archive'];
    $reason = $_GET['reason'] ?? 'No reason provided';

    // Get scholar email
    $scholar = $pdo->prepare("SELECT email FROM scholars WHERE scholar_id = ?");
    $scholar->execute([$id]);
    $sch = $scholar->fetch();

    if($sch) {
        // Get student_id
        $student = $pdo->prepare("SELECT student_id FROM students WHERE email = ?");
        $student->execute([$sch['email']]);
        $std = $student->fetch();

        if($std) {
            // Delete documents first
            $pdo->prepare("
                DELETE FROM documents WHERE application_id IN (
                    SELECT application_id FROM applications WHERE scholar_id = ?
                )
            ")->execute([$std['student_id']]);

            // Delete disbursements
            $pdo->prepare("DELETE FROM disbursements WHERE scholar_id = ?")->execute([$std['student_id']]);

            // Delete applications so student can reapply
            $pdo->prepare("DELETE FROM applications WHERE scholar_id = ?")->execute([$std['student_id']]);
        }
    }

    $pdo->prepare("UPDATE scholars SET is_archived=1, archived_at=NOW(), archive_reason=? WHERE scholar_id=?")->execute([$reason, $id]);
    header("Location: scholars.php?success=archived");
    exit();
}

// Get active scholars only
$search = $_GET['search'] ?? '';
if($search) {
    $stmt = $pdo->prepare("SELECT * FROM scholars WHERE is_archived=0 AND (first_name LIKE ? OR last_name LIKE ? OR barangay LIKE ?) ORDER BY last_name ASC");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM scholars WHERE is_archived=0 ORDER BY last_name ASC");
}
$scholars = $stmt->fetchAll();

// Count archived
$archived_count = $pdo->query("SELECT COUNT(*) FROM scholars WHERE is_archived=1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholars | Cainta Scholarship</title>
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
            <h5 class="mb-0 fw-bold">Scholars</h5>
            <small class="text-muted">Manage all registered scholars</small>
        </div>
        <div class="d-flex gap-2">
            <a href="archived_scholars.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-archive me-1"></i> Archived
                <?php if($archived_count > 0): ?>
                <span class="badge bg-secondary ms-1"><?= $archived_count ?></span>
                <?php endif; ?>
            </a>
            <a href="add_scholar.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Add New Scholar
            </a>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>
        <?php
        if($_GET['success'] == 'added') echo 'Scholar added successfully!';
        elseif($_GET['success'] == 'updated') echo 'Scholar updated successfully!';
        elseif($_GET['success'] == 'deleted') echo 'Scholar deleted successfully!';
        elseif($_GET['success'] == 'archived') echo 'Scholar archived successfully!';
        elseif($_GET['success'] == 'restored') echo 'Scholar restored successfully!';
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group" style="max-width: 400px;">
                    <input type="text" name="search" class="form-control"
                            placeholder="Search by name or barangay..."
                            value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if($search): ?>
                    <a href="scholars.php" class="btn btn-outline-danger"><i class="bi bi-x"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Barangay</th>
                            <th>School</th>
                            <th>Course</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($scholars)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-people fs-3 d-block mb-2"></i>
                                No scholars found.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($scholars as $i => $s): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></strong>
                                <?php if($s['middle_name']): ?>
                                <small class="text-muted"><?= htmlspecialchars($s['middle_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s['barangay']) ?></td>
                            <td><?= htmlspecialchars($s['school']) ?></td>
                            <td><?= htmlspecialchars($s['course']) ?></td>
                            <td><?= htmlspecialchars($s['contact_no']) ?></td>
                            <td>
                                <span class="badge <?= $s['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($s['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_scholar.php?id=<?= $s['scholar_id'] ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-warning"
                                    onclick="archiveScholar(<?= $s['scholar_id'] ?>, '<?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>')">
                                    <i class="bi bi-archive"></i>
                                </button>
                                <a href="scholars.php?delete=<?= $s['scholar_id'] ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Permanently delete this scholar?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted">Total: <?= count($scholars) ?> active scholar(s)</small>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#f0a500; color:white;">
                <h5 class="modal-title"><i class="bi bi-archive me-2"></i>Archive Scholar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to archive <strong id="archive-name"></strong>.</p>
                <p class="text-muted" style="font-size:13px;">Archived scholars are hidden from the main list but can be restored anytime.</p>
                <div class="mb-3">
                    <label class="form-label">Reason for archiving <span class="text-danger">*</span></label>
                    <select id="archive-reason-select" class="form-select mb-2" onchange="handleReasonSelect(this)">
                        <option value="">Select reason</option>
                        <option value="Graduated">Graduated</option>
                        <option value="Transferred school">Transferred school</option>
                        <option value="Dropped out">Dropped out</option>
                        <option value="Exceeded scholarship period">Exceeded scholarship period</option>
                        <option value="Voluntarily withdrew">Voluntarily withdrew</option>
                        <option value="Other">Other (specify)</option>
                    </select>
                    <input type="text" id="archive-reason-other" class="form-control"
                            placeholder="Specify reason..." style="display:none;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmArchive()">
                    <i class="bi bi-archive me-1"></i> Archive Scholar
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../chatbot_widget.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let archiveId = null;

function archiveScholar(id, name) {
    archiveId = id;
    document.getElementById('archive-name').textContent = name;
    document.getElementById('archive-reason-select').value = '';
    document.getElementById('archive-reason-other').style.display = 'none';
    new bootstrap.Modal(document.getElementById('archiveModal')).show();
}

function handleReasonSelect(sel) {
    const other = document.getElementById('archive-reason-other');
    other.style.display = sel.value === 'Other' ? 'block' : 'none';
}

function confirmArchive() {
    const select = document.getElementById('archive-reason-select');
    const other = document.getElementById('archive-reason-other');
    let reason = select.value === 'Other' ? other.value : select.value;
    if(!reason) { alert('Please select a reason for archiving.'); return; }
    window.location.href = 'scholars.php?archive=' + archiveId + '&reason=' + encodeURIComponent(reason);
}
</script>
</body>
</html>