<?php
require_once 'db.php';
$database = new Database();
$db = $database->getConnection();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = uniqid();
    $name = $_POST['name'];
    $target = $_POST['target'];
    $unit = $_POST['unit'];
    $category = $_POST['category'];
    
    $stmt = $db->prepare("INSERT INTO indicators (id, name, target, unit, category, actual, achievedPercentage, gap, status) VALUES (?, ?, ?, ?, ?, 0, 0, ?, 'behind')");
    $stmt->execute([$id, $name, $target, $unit, $category, $target]);
    header("Location: indicators.php");
    exit;
}

// Fetch Indicators
$stmt = $db->prepare("SELECT * FROM indicators");
$stmt->execute();
$indicators = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact MEAL - Indicators</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .sidebar { height: 100vh; background-color: #0f172a; color: #94a3b8; }
        .nav-link { color: #94a3b8; border-radius: 8px; margin-bottom: 4px; }
        .nav-link.active { background-color: #4f46e5; color: white; }
        .card { border-radius: 16px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .status-on-track { background-color: #ecfdf5; color: #059669; }
        .status-at-risk { background-color: #fffbeb; color: #d97706; }
        .status-behind { background-color: #fef2f2; color: #dc2626; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-4 d-none d-md-block">
                <div class="d-flex align-items-center gap-2 mb-5">
                    <div class="p-2 bg-primary rounded-3">
                        <i class="bi bi-stars text-white"></i>
                    </div>
                    <h4 class="text-white mb-0">Impact MEAL</h4>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a class="nav-link active" href="indicators.php"><i class="bi bi-target me-2"></i> Indicators</a>
                    <a class="nav-link" href="monitoring.php"><i class="bi bi-clipboard-data me-2"></i> Monitoring</a>
                    <a class="nav-link" href="gis.php"><i class="bi bi-map me-2"></i> GIS View</a>
                    <a class="nav-link" href="reports.php"><i class="bi bi-file-earmark-text me-2"></i> Reports</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Indicator Management</h2>
                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addIndicatorModal"><i class="bi bi-plus-lg me-2"></i> Add Indicator</button>
                </div>

                <!-- Indicators Table -->
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr class="text-muted">
                                    <th>Indicator Name</th>
                                    <th>Category</th>
                                    <th>Target</th>
                                    <th>Actual</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($indicators as $ind): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $ind['name']; ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo $ind['category']; ?></span></td>
                                    <td><?php echo $ind['target']; ?></td>
                                    <td><?php echo $ind['actual']; ?></td>
                                    <td>
                                        <div class="progress" style="height: 8px; width: 100px;">
                                            <div class="progress-bar bg-primary" style="width: <?php echo $ind['achievedPercentage']; ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?php echo $ind['achievedPercentage']; ?>%</small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $ind['status']; ?>">
                                            <?php echo str_replace('-', ' ', $ind['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Indicator Modal -->
    <div class="modal fade" id="addIndicatorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Indicator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Indicator Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="Outreach">Outreach</option>
                                <option value="Capacity Building">Capacity Building</option>
                                <option value="Accountability">Accountability</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Target</label>
                                <input type="number" name="target" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" name="unit" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Indicator</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
