<?php
session_start();
require_once '../includes/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle add item
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $stmt = $pdo->prepare("INSERT INTO inventory_items (item_name, category, unit, quantity, reorder_level) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['item_name'], $_POST['category'], $_POST['unit'], $_POST['quantity'], $_POST['reorder_level']]);
    header("Location: inventory.php?success=added");
    exit();
}

// Handle restock
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restock'])) {
    $stmt = $pdo->prepare("UPDATE inventory_items SET quantity = quantity + ? WHERE item_id = ?");
    $stmt->execute([$_POST['add_quantity'], $_POST['item_id']]);
    header("Location: inventory.php?success=restocked");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM inventory_items WHERE item_id = ?")->execute([$_GET['delete']]);
    header("Location: inventory.php?success=deleted");
    exit();
}

// Get all items
$items = $pdo->query("SELECT * FROM inventory_items ORDER BY item_name ASC")->fetchAll();

// Stats
$total_items = count($items);
$low_stock = array_filter($items, fn($i) => $i['quantity'] <= $i['reorder_level']);
$out_of_stock = array_filter($items, fn($i) => $i['quantity'] == 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory | Cainta Scholarship</title>
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
        <a href="disbursements.php" class="nav-link"><i class="bi bi-cash-stack"></i> Disbursements</a>
        <a href="inventory.php" class="nav-link active"><i class="bi bi-box-seam"></i> Inventory</a>
        <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart"></i> Reports</a>
        <a href="users.php" class="nav-link"><i class="bi bi-person-gear"></i> Users</a>
        <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 20px;">
        <a href="../logout.php" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </nav>
</div>

<div class="main-content">
    <div class="topbar">
        <div>
            <h5 class="mb-0 fw-bold">Inventory</h5>
            <small class="text-muted">Manage school supplies and vouchers</small>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-1"></i> Add Item
        </button>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>
        <?php
        if($_GET['success'] == 'added') echo 'Item added successfully!';
        elseif($_GET['success'] == 'restocked') echo 'Stock updated successfully!';
        elseif($_GET['success'] == 'deleted') echo 'Item deleted successfully!';
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card" style="border-color: #1A3A6B;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-box-seam me-1"></i>Total Items</div>
                <div style="font-size:26px; font-weight:700; color:#1A3A6B;"><?= $total_items ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-color: #f0a500;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock</div>
                <div style="font-size:26px; font-weight:700; color:#f0a500;"><?= count($low_stock) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card" style="border-color: #dc3545;">
                <div style="font-size:13px; color:#666;"><i class="bi bi-x-circle me-1"></i>Out of Stock</div>
                <div style="font-size:26px; font-weight:700; color:#dc3545;"><?= count($out_of_stock) ?></div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Stock</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($items)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
                                No items in inventory yet.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($items as $i => $item): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= htmlspecialchars($item['unit']) ?></td>
                            <td>
                                <strong class="<?= $item['quantity'] == 0 ? 'text-danger' : ($item['quantity'] <= $item['reorder_level'] ? 'text-warning' : 'text-success') ?>">
                                    <?= $item['quantity'] ?>
                                </strong>
                            </td>
                            <td><?= $item['reorder_level'] ?></td>
                            <td>
                                <?php if($item['quantity'] == 0): ?>
                                <span class="badge bg-danger">Out of Stock</span>
                                <?php elseif($item['quantity'] <= $item['reorder_level']): ?>
                                <span class="badge bg-warning text-dark">Low Stock</span>
                                <?php else: ?>
                                <span class="badge bg-success">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                    onclick="restockItem(<?= $item['item_id'] ?>, '<?= htmlspecialchars($item['item_name']) ?>')">
                                    <i class="bi bi-plus-circle"></i> Restock
                                </button>
                                <a href="inventory.php?delete=<?= $item['item_id'] ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Delete this item?')">
                                    <i class="bi bi-trash"></i>
                                </a>
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

<!-- Add Item Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#1A3A6B; color:white;">
                <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>Add New Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="add_item" value="1">
                    <div class="mb-3">
                        <label class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" required
                                placeholder="e.g. Notebook, Ballpen Set">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">Select category</option>
                            <option value="School Supplies">School Supplies</option>
                            <option value="Voucher">Voucher</option>
                            <option value="Allowance">Allowance</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <input type="text" name="unit" class="form-control" required
                                placeholder="e.g. pieces, sets, packs">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Initial Stock <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" required min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reorder Level <span class="text-danger">*</span></label>
                            <input type="number" name="reorder_level" class="form-control" required min="0" value="10">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#198754; color:white;">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Restock Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="restock" value="1">
                    <input type="hidden" name="item_id" id="restock_item_id">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" id="restock_item_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" name="add_quantity" class="form-control" required min="1">
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus-circle me-1"></i> Add Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function restockItem(id, name) {
    document.getElementById('restock_item_id').value = id;
    document.getElementById('restock_item_name').value = name;
    new bootstrap.Modal(document.getElementById('restockModal')).show();
}
</script>
</body>
</html>