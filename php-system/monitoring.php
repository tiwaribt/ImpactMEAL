<?php
require_once 'db.php';
$database = new Database();
$db = $database->getConnection();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = uniqid();
    $indicatorId = $_POST['indicatorId'];
    $date = $_POST['date'];
    $value = $_POST['value'];
    $location = $_POST['location'];
    $notes = $_POST['notes'];
    
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("INSERT INTO monitoring_entries (id, indicatorId, date, value, location, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $indicatorId, $date, $value, $location, $notes]);
        
        $stmt = $db->prepare("UPDATE indicators SET actual = actual + ? WHERE id = ?");
        $stmt->execute([$value, $indicatorId]);
        
        // Recalculate
        $stmt = $db->prepare("SELECT target, actual FROM indicators WHERE id = ?");
        $stmt->execute([$indicatorId]);
        $ind = $stmt->fetch();
        $achieved = round(($ind['actual'] / $ind['target']) * 100);
        $gap = $ind['target'] - $ind['actual'];
        $status = $achieved >= 90 ? 'on-track' : ($achieved >= 70 ? 'at-risk' : 'behind');
        
        $stmt = $db->prepare("UPDATE indicators SET achievedPercentage = ?, gap = ?, status = ? WHERE id = ?");
        $stmt->execute([$achieved, $gap, $status, $indicatorId]);
        
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
    }
    header("Location: monitoring.php");
    exit;
}

// Fetch Indicators for dropdown
$stmt = $db->prepare("SELECT id, name FROM indicators");
$stmt->execute();
$indicators = $stmt->fetchAll();

// Fetch Monitoring Entries
$stmt = $db->prepare("SELECT m.*, i.name as indicatorName FROM monitoring_entries m JOIN indicators i ON m.indicatorId = i.id ORDER BY m.date DESC");
$stmt->execute();
$entries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact MEAL - Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .sidebar { height: 100vh; background-color: #0f172a; color: #94a3b8; }
        .nav-link { color: #94a3b8; border-radius: 8px; margin-bottom: 4px; }
        .nav-link.active { background-color: #4f46e5; color: white; }
        .card { border-radius: 16px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
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
                    <a class="nav-link" href="indicators.php"><i class="bi bi-target me-2"></i> Indicators</a>
                    <a class="nav-link active" href="monitoring.php"><i class="bi bi-clipboard-data me-2"></i> Monitoring</a>
                    <a class="nav-link" href="gis.php"><i class="bi bi-map me-2"></i> GIS View</a>
                    <a class="nav-link" href="reports.php"><i class="bi bi-file-earmark-text me-2"></i> Reports</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Monitoring Data</h2>
                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addEntryModal"><i class="bi bi-plus-lg me-2"></i> Add Entry</button>
                </div>

                <!-- Monitoring Table -->
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr class="text-muted">
                                    <th>Indicator</th>
                                    <th>Value</th>
                                    <th>Location</th>
                                    <th>Date</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entries as $entry): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $entry['indicatorName']; ?></td>
                                    <td><span class="fw-bold text-primary"><?php echo $entry['value']; ?></span></td>
                                    <td><i class="bi bi-geo-alt me-1 text-muted"></i> <?php echo $entry['location']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($entry['date'])); ?></td>
                                    <td><small class="text-muted"><?php echo $entry['notes']; ?></small></td>
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

    <!-- Add Entry Modal -->
    <div class="modal fade" id="addEntryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Monitoring Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Indicator</label>
                            <select name="indicatorId" class="form-select" required>
                                <?php foreach ($indicators as $ind): ?>
                                <option value="<?php echo $ind['id']; ?>"><?php echo $ind['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Value</label>
                                <input type="number" name="value" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Region A" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
