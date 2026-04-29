<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle archive
if(isset($_GET['archive'])) {
    $id = $_GET['archive'];
    $reason = $_GET['reason'] ?? 'No reason provided';
    $pdo->prepare("UPDATE students SET is_archived=1, archived_at=NOW(), archive_reason=? WHERE student_id=?")->execute([$reason, $id]);
    header("Location: students.php?success=archived");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM students WHERE student_id=?")->execute([$id]);
    header("Location: students.php?success=deleted");
    exit();
}

// Get active students
$search = $_GET['search'] ?? '';
if($search) {
    $stmt = $pdo->prepare("
        SELECT s.*, 
        (SELECT status FROM applications WHERE scholar_id = s.student_id ORDER BY submitted_at DESC LIMIT 1) as app_status
        FROM students s
        WHERE s.is_archived=0 AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.email LIKE ? OR s.barangay LIKE ?)
        ORDER BY s.last_name ASC
    ");
    $stmt->execute(["%$search%", "%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("
        SELECT s.*, 
        (SELECT status FROM applications WHERE scholar_id = s.student_id ORDER BY submitted_at DESC LIMIT 1) as app_status
        FROM students s
        WHERE s.is_archived=0
        ORDER BY s.last_name ASC
    ");
}
$students = $stmt->fetchAll();

// Count archived
$archived_count = $pdo->query("SELECT COUNT(*) FROM students WHERE is_archived=1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students | Cainta Scholarship</title>
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
        <a href="students.php" class="nav-link active"><i class="bi bi-person-lines-fill"></i> Students</a>
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
            <h5 class="mb-0 fw-bold">Students</h5>
            <small class="text-muted">Manage all registered student applicants</small>
        </div>
        <div class="d-flex gap-2">
            <a href="archived_students.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-archive me-1"></i> Archived
                <?php if($archived_count > 0): ?>
                <span class="badge bg-secondary ms-1"><?= $archived_count ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>
        <?php
        if($_GET['success'] == 'archived') echo 'Student archived successfully!';
        elseif($_GET['success'] == 'deleted') echo 'Student deleted successfully!';
        elseif($_GET['success'] == 'updated') echo 'Student updated successfully!';
        elseif($_GET['success'] == 'restored') echo 'Student restored successfully!';
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group" style="max-width: 400px;">
                    <input type="text" name="search" class="form-control"
                            placeholder="Search by name, email or barangay..."
                            value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if($search): ?>
                    <a href="students.php" class="btn btn-outline-danger"><i class="bi bi-x"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Barangay</th>
                            <th>Contact</th>
                            <th>Application Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($students)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-people fs-3 d-block mb-2"></i>
                                No students found.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($students as $i => $s): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name']) ?></strong>
                                <?php if($s['middle_name']): ?>
                                <small class="text-muted"><?= htmlspecialchars($s['middle_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:13px;"><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= htmlspecialchars($s['barangay']) ?></td>
                            <td><?= htmlspecialchars($s['contact_no']) ?></td>
                            <td>
                                <?php if($s['app_status']): ?>
                                <span class="badge badge-<?= $s['app_status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $s['app_status'])) ?>
                                </span>
                                <?php else: ?>
                                <span class="badge bg-light text-muted">No application</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:13px;"><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                    onclick="viewStudent(<?= htmlspecialchars(json_encode($s)) ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="edit_student.php?id=<?= $s['student_id'] ?>"
                                    class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-warning"
                                    onclick="archiveStudent(<?= $s['student_id'] ?>, '<?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?>')">
                                    <i class="bi bi-archive"></i>
                                </button>
                                <a href="students.php?delete=<?= $s['student_id'] ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Permanently delete this student?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted">Total: <?= count($students) ?> student(s)</small>
        </div>
    </div>
</div>

<!-- View Student Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#1A3A6B; color:white;">
                <h5 class="modal-title"><i class="bi bi-person me-2"></i>Student Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="studentDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="edit-btn" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit Student
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#f0a500; color:white;">
                <h5 class="modal-title"><i class="bi bi-archive me-2"></i>Archive Student</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>You are about to archive <strong id="archive-name"></strong>.</p>
                <p class="text-muted" style="font-size:13px;">Archived students are hidden from the main list but can be restored anytime.</p>
                <div class="mb-3">
                    <label class="form-label">Reason for archiving <span class="text-danger">*</span></label>
                    <select id="archive-reason-select" class="form-select mb-2" onchange="handleReasonSelect(this)">
                        <option value="">Select reason</option>
                        <option value="No longer a resident of Cainta">No longer a resident of Cainta</option>
                        <option value="Duplicate account">Duplicate account</option>
                        <option value="Inactive for a long time">Inactive for a long time</option>
                        <option value="Request of student">Request of student</option>
                        <option value="Other">Other (specify)</option>
                    </select>
                    <input type="text" id="archive-reason-other" class="form-control"
                            placeholder="Specify reason..." style="display:none;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="confirmArchive()">
                    <i class="bi bi-archive me-1"></i> Archive Student
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../chatbot_widget.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let archiveId = null;

function viewStudent(s) {
    document.getElementById('edit-btn').href = 'edit_student.php?id=' + s.student_id;
    let html = `
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Full Name</div>
        <div class="fw-bold">${s.last_name}, ${s.first_name} ${s.middle_name || ''}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Email</div>
        <div>${s.email}</div></div>
        <div class="col-md-4"><div class="text-muted" style="font-size:12px;">Birthdate</div>
        <div>${s.birthdate || 'N/A'}</div></div>
        <div class="col-md-4"><div class="text-muted" style="font-size:12px;">Gender</div>
        <div>${s.gender || 'N/A'}</div></div>
        <div class="col-md-4"><div class="text-muted" style="font-size:12px;">Contact</div>
        <div>${s.contact_no || 'N/A'}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Barangay</div>
        <div>${s.barangay || 'N/A'}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Address</div>
        <div>${s.address || 'N/A'}</div></div>
        <div class="col-md-6"><div class="text-muted" style="font-size:12px;">Registered</div>
        <div>${s.created_at}</div></div>
    `;
    document.getElementById('studentDetails').innerHTML = html;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function archiveStudent(id, name) {
    archiveId = id;
    document.getElementById('archive-name').textContent = name;
    document.getElementById('archive-reason-select').value = '';
    document.getElementById('archive-reason-other').style.display = 'none';
    new bootstrap.Modal(document.getElementById('archiveModal')).show();
}

function handleReasonSelect(sel) {
    document.getElementById('archive-reason-other').style.display = sel.value === 'Other' ? 'block' : 'none';
}

function confirmArchive() {
    const select = document.getElementById('archive-reason-select');
    const other = document.getElementById('archive-reason-other');
    let reason = select.value === 'Other' ? other.value : select.value;
    if(!reason) { alert('Please select a reason.'); return; }
    window.location.href = 'students.php?archive=' + archiveId + '&reason=' + encodeURIComponent(reason);
}
</script>
</body>
</html>