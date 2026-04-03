<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['student_id'])) {
    header("Location: ../student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$existing = $pdo->prepare("SELECT * FROM applications WHERE scholar_id = ? AND status IN ('pending','for_review','approved') ORDER BY submitted_at DESC LIMIT 1");
$existing->execute([$student_id]);
$has_application = $existing->fetch();

$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && !$has_application) {
    $school_year = $_POST['school_year'];
    $semester = $_POST['semester'];
    $barangay = $_POST['barangay'];
    $birthdate = $_POST['birthdate'];
    $father_name = $_POST['father_name'];
    $father_occupation = $_POST['father_occupation'];
    $mother_name = $_POST['mother_name'];
    $mother_occupation = $_POST['mother_occupation'];
    $school = $_POST['school'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];

    $stmt = $pdo->prepare("INSERT INTO applications 
        (scholar_id, school_year, semester, father_name, father_occupation, mother_name, mother_occupation, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$student_id, $school_year, $semester, $father_name, $father_occupation, $mother_name, $mother_occupation]);
    $application_id = $pdo->lastInsertId();

    // Save extra info as document records
    $doc_stmt = $pdo->prepare("INSERT INTO documents (application_id, document_type, file_path) VALUES (?, ?, ?)");
    $doc_stmt->execute([$application_id, 'barangay', $barangay]);
    $doc_stmt->execute([$application_id, 'birthdate', $birthdate]);
    $doc_stmt->execute([$application_id, 'school', $school]);
    $doc_stmt->execute([$application_id, 'course', $course]);
    $doc_stmt->execute([$application_id, 'year_level', $year_level]);

    // Handle file uploads
    $upload_dir = '../uploads/';
    $doc_types = ['grade_slip', 'enrollment_receipt', 'enrollment_form'];

    foreach($doc_types as $doc_type) {
        if(isset($_FILES[$doc_type]) && $_FILES[$doc_type]['error'] == 0) {
            $ext = pathinfo($_FILES[$doc_type]['name'], PATHINFO_EXTENSION);
            $filename = $doc_type . '_' . $student_id . '_' . time() . '.' . $ext;
            if(move_uploaded_file($_FILES[$doc_type]['tmp_name'], $upload_dir . $filename)) {
                $doc_stmt2 = $pdo->prepare("INSERT INTO documents (application_id, document_type, file_path) VALUES (?, ?, ?)");
                $doc_stmt2->execute([$application_id, $doc_type, $filename]);
            }
        }
    }

    $success = 'Application submitted successfully! The scholarship office will review your application.';
    $existing2 = $pdo->prepare("SELECT * FROM applications WHERE scholar_id = ? ORDER BY submitted_at DESC LIMIT 1");
    $existing2->execute([$student_id]);
    $has_application = $existing2->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Application | Cainta Scholarship</title>
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
        .section-title {
            font-size: 13px; font-weight: 600; color: #1A3A6B;
            border-bottom: 2px solid #e8f0fe; padding-bottom: 6px; margin-bottom: 16px;
        }
        .status-timeline { position: relative; padding-left: 30px; }
        .status-timeline::before {
            content: ''; position: absolute; left: 8px; top: 0; bottom: 0;
            width: 2px; background: #dee2e6;
        }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .timeline-dot {
            position: absolute; left: -26px; width: 14px; height: 14px;
            border-radius: 50%; top: 3px;
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d1e7dd; color: #0f5132; }
        .badge-rejected { background: #f8d7da; color: #842029; }
        .badge-for_review { background: #cfe2ff; color: #084298; }
        .badge-incomplete { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>
<div class="topnav">
    <a href="dashboard.php" class="topnav-brand">
        <i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship Portal
    </a>
    <div class="topnav-links">
        <a href="dashboard.php">Home</a>
        <a href="application.php" class="active">My Application</a>
        <a href="status.php">Status</a>
        <a href="disbursements.php">Disbursements</a>
        <a href="../student_logout.php">Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="mb-4">
        <h5 class="mb-0 fw-bold">My Application</h5>
        <small class="text-muted">Submit and track your scholarship application</small>
    </div>

    <?php if($success): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-1"></i><?= $success ?>
    </div>
    <?php endif; ?>

    <?php if($has_application): ?>
    <div class="card mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-check me-1"></i> Application Details</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="text-muted" style="font-size:12px;">School Year</div>
                    <div><?= $has_application['school_year'] ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted" style="font-size:12px;">Semester</div>
                    <div><?= $has_application['semester'] ?> Semester</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted" style="font-size:12px;">Father's Name</div>
                    <div><?= htmlspecialchars($has_application['father_name']) ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted" style="font-size:12px;">Mother's Name</div>
                    <div><?= htmlspecialchars($has_application['mother_name']) ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted" style="font-size:12px;">Status</div>
                    <span class="badge badge-<?= $has_application['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $has_application['status'])) ?>
                    </span>
                </div>
            </div>

            <?php if($has_application['remarks']): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-1"></i>
                <strong>Remarks:</strong> <?= htmlspecialchars($has_application['remarks']) ?>
            </div>
            <?php endif; ?>

            <h6 class="fw-bold mb-3 mt-4"><i class="bi bi-clock-history me-1"></i> Application Timeline</h6>
            <div class="status-timeline">
                <div class="timeline-item">
                    <div class="timeline-dot" style="background: #1A3A6B;"></div>
                    <div style="font-size:13px; font-weight:500;">Application Submitted</div>
                    <div style="font-size:12px; color:#666;"><?= date('F d, Y', strtotime($has_application['submitted_at'])) ?></div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot" style="background: <?= in_array($has_application['status'], ['for_review','approved','rejected']) ? '#0d6efd' : '#dee2e6' ?>;"></div>
                    <div style="font-size:13px; font-weight:500; color: <?= in_array($has_application['status'], ['for_review','approved','rejected']) ? '#000' : '#aaa' ?>">Under Review</div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot" style="background: <?= in_array($has_application['status'], ['approved','rejected']) ? ($has_application['status']=='approved'?'#198754':'#dc3545') : '#dee2e6' ?>;"></div>
                    <div style="font-size:13px; font-weight:500; color: <?= in_array($has_application['status'], ['approved','rejected']) ? '#000' : '#aaa' ?>">
                        <?= $has_application['status']=='approved' ? 'Approved ✅' : ($has_application['status']=='rejected' ? 'Rejected ❌' : 'Decision Pending') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <div class="card">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data">

                <!-- 1. Personal Information -->
                <p class="section-title"><i class="bi bi-person me-1"></i> Personal Information</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Birthdate <span class="text-danger">*</span></label>
                        <input type="date" name="birthdate" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Barangay <span class="text-danger">*</span></label>
                        <select name="barangay" class="form-select" required>
                            <option value="">Select your barangay</option>
                            <option>Brgy. San Andres</option>
                            <option>Brgy. San Isidro</option>
                            <option>Brgy. San Juan</option>
                            <option>Brgy. San Roque</option>
                            <option>Brgy. Santa Rosa</option>
                            <option>Brgy. Santo Domingo</option>
                            <option>Brgy. Santo Niño</option>
                        </select>
                    </div>
                </div>

                <!-- 2. Family Information -->
                <p class="section-title"><i class="bi bi-people me-1"></i> Family Information</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Father's Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="father_name" class="form-control" required
                               placeholder="e.g. Juan Dela Cruz">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Father's Occupation <span class="text-danger">*</span></label>
                        <input type="text" name="father_occupation" class="form-control" required
                               placeholder="e.g. Driver, Carpenter">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mother's Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="mother_name" class="form-control" required
                               placeholder="e.g. Maria Dela Cruz">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mother's Occupation <span class="text-danger">*</span></label>
                        <input type="text" name="mother_occupation" class="form-control" required
                               placeholder="e.g. Housewife, Teacher">
                    </div>
                </div>

                <!-- 3. Academic Information -->
                <p class="section-title"><i class="bi bi-book me-1"></i> Academic Information</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">School / University <span class="text-danger">*</span></label>
                        <input type="text" name="school" class="form-control" required
                               placeholder="e.g. PLM, PUP, STI College">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Course <span class="text-danger">*</span></label>
                        <input type="text" name="course" class="form-control" required
                               placeholder="e.g. BSIT, BSCS, BSN">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Year Level <span class="text-danger">*</span></label>
                        <select name="year_level" class="form-select" required>
                            <option value="">Select year level</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                            <option value="5">5th Year</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">School Year <span class="text-danger">*</span></label>
                        <select name="school_year" class="form-select" required>
                            <option value="">Select school year</option>
                            <option value="2025-2026">2025-2026</option>
                            <option value="2026-2027">2026-2027</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-select" required>
                            <option value="">Select semester</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>
                </div>

                <!-- 4. Required Documents -->
                <p class="section-title"><i class="bi bi-paperclip me-1"></i> Required Documents</p>
                <p class="text-muted" style="font-size:13px;">Upload clear photos or scanned copies (JPG, PNG, or PDF — max 5MB each)</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Latest Grade Slip / Transcript <span class="text-danger">*</span></label>
                        <input type="file" name="grade_slip" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">School Enrollment Receipt <span class="text-danger">*</span></label>
                        <input type="file" name="enrollment_receipt" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Enrollment Form <span class="text-danger">*</span></label>
                        <input type="file" name="enrollment_form" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i>
                    By submitting this application, you certify that all information provided is true and correct.
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i> Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>