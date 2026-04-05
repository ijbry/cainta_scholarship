<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'cashier') {
    header("Location: ../login.php");
    exit();
}

// Handle release transaction
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['release_items'])) {
    $scholar_id = $_POST['scholar_id'];
    $items = $_POST['items'] ?? [];
    $quantities = $_POST['quantities'] ?? [];

    foreach($items as $index => $item_id) {
        $qty = $quantities[$index] ?? 1;
        if($qty > 0) {
            // Deduct from inventory
            $pdo->prepare("UPDATE inventory_items SET quantity = quantity - ? WHERE item_id = ? AND quantity >= ?")->execute([$qty, $item_id, $qty]);
            // Record transaction
            $pdo->prepare("INSERT INTO inventory_transactions (item_id, scholar_id, quantity, type, processed_by) VALUES (?, ?, ?, 'OUT', ?)")->execute([$item_id, $scholar_id, $qty, $_SESSION['user_id']]);
        }
    }
    header("Location: dashboard.php?success=1&scholar_id=" . $scholar_id);
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

// Get inventory items
$items = $pdo->query("SELECT * FROM inventory_items WHERE quantity > 0 ORDER BY item_name ASC")->fetchAll();

// Recent transactions
$transactions = $pdo->query("
    SELECT t.*, i.item_name, i.unit, s.first_name, s.last_name
    FROM inventory_transactions t
    JOIN inventory_items i ON t.item_id = i.item_id
    LEFT JOIN students s ON t.scholar_id = s.student_id
    WHERE t.type = 'OUT'
    ORDER BY t.processed_at DESC
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
        .item-card {
            border: 1px solid #dee2e6; border-radius: 8px; padding: 12px;
            cursor: pointer; transition: all 0.2s;
        }
        .item-card:hover { border-color: #1A3A6B; background: #f0f4ff; }
        .item-card.selected { border-color: #1A3A6B; background: #e8f0fe; }
    </style>
</head>
<body>
<div class="topbar">
    <span class="topbar-brand"><i class="bi bi-mortarboard-fill me-2"></i>Cainta Scholarship — Cashier Counter</span>
    <div class="topbar-right">
        <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['full_name']) ?>
        <a href="../logout.php" class="btn btn-sm btn-outline-light ms-3">
            <i class="bi bi-box-arrow-left me-1"></i>Logout
        </a>
    </div>
</div>

<div class="main-content">
    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i> Items released successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- LEFT: Scholar Lookup + Items -->
        <div class="col-md-7">
            <div class="card mb-4">
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
                        <div class="mt-2">
                            <span class="badge bg-success">Approved Scholar</span>
                        </div>
                    </div>

                    <!-- Items to Release -->
                    <form method="POST">
                        <input type="hidden" name="release_items" value="1">
                        <input type="hidden" name="scholar_id" value="<?= $scholar['student_id'] ?>">

                        <h6 class="fw-bold mb-3"><i class="bi bi-box-seam me-1"></i> Select Items to Release</h6>
                        <?php if(empty($items)): ?>
                        <div class="text-muted text-center py-3">No items available in inventory.</div>
                        <?php else: ?>
                        <div class="row g-2 mb-3">
                            <?php foreach($items as $i => $item): ?>
                            <div class="col-md-6">
                                <div class="item-card" onclick="toggleItem(<?= $i ?>)">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                                name="items[]" value="<?= $item['item_id'] ?>"
                                                id="item_<?= $i ?>">
                                        <label class="form-check-label w-100" for="item_<?= $i ?>">
                                            <strong><?= htmlspecialchars($item['item_name']) ?></strong>
                                            <div style="font-size:12px; color:#666;"><?= $item['category'] ?> · Stock: <?= $item['quantity'] ?> <?= $item['unit'] ?></div>
                                        </label>
                                    </div>
                                    <div class="mt-2">
                                        <input type="number" name="quantities[]"
                                                class="form-control form-control-sm"
                                                value="1" min="1" max="<?= $item['quantity'] ?>"
                                                placeholder="Qty">
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg"
                                    onclick="return confirm('Confirm release of selected items?')">
                                <i class="bi bi-check-circle me-2"></i>Confirm Release & Record
                            </button>
                        </div>
                        <?php endif; ?>
                    </form>
                    <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-person-badge fs-3 d-block mb-2"></i>
                        Search for a scholar to begin
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT: Recent Transactions -->
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
                            <?= htmlspecialchars($t['item_name']) ?> — <?= $t['quantity'] ?> <?= $t['unit'] ?>
                        </div>
                        <div style="font-size:11px; color:#aaa;">
                            <?= date('M d, Y h:i A', strtotime($t['processed_at'])) ?>
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
<script>
function toggleItem(index) {
    const checkbox = document.getElementById('item_' + index);
    checkbox.checked = !checkbox.checked;
}
</script>
</body>
</html>