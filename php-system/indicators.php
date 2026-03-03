<?php
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Projects for dropdown
$stmt = $db->prepare("SELECT id, name FROM projects");
$stmt->execute();
$projects = $stmt->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id = uniqid();
    $projectId = $_POST['projectId'];
    $name = $_POST['name'];
    $target = $_POST['target'];
    $unit = $_POST['unit'];
    $category = $_POST['category'];
    
    $stmt = $db->prepare("INSERT INTO indicators (id, projectId, name, target, unit, category, actual, achievedPercentage, gap, status) VALUES (?, ?, ?, ?, ?, ?, 0, 0, ?, 'behind')");
    $stmt->execute([$id, $projectId, $name, $target, $unit, $category, $target]);
    header("Location: indicators.php");
    exit;
}

// Fetch Indicators
$stmt = $db->prepare("SELECT i.*, p.name as projectName FROM indicators i LEFT JOIN projects p ON i.projectId = p.id ORDER BY i.lastUpdated DESC");
$stmt->execute();
$indicators = $stmt->fetchAll();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-slate-900 mb-1">Indicators</h2>
            <p class="text-slate-500 small mb-0">Track and manage project performance indicators</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addIndicatorModal">
            <i class="bi bi-plus-lg me-2"></i> Add Indicator
        </button>
    </div>

    <div class="card border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Indicator Name</th>
                        <th>Project</th>
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
                        <td>
                            <div class="fw-bold text-slate-900"><?php echo $ind['name']; ?></div>
                            <small class="text-slate-400 x-small fw-bold text-uppercase tracking-widest"><?php echo $ind['unit']; ?></small>
                        </td>
                        <td>
                            <span class="badge badge-pill bg-slate-100 text-slate-600"><?php echo $ind['projectName'] ?: 'No Project'; ?></span>
                        </td>
                        <td>
                            <span class="badge badge-pill bg-indigo-50 text-indigo-600"><?php echo $ind['category']; ?></span>
                        </td>
                        <td class="fw-bold"><?php echo $ind['target']; ?></td>
                        <td class="fw-bold text-primary"><?php echo $ind['actual']; ?></td>
                        <td style="width: 200px;">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px; border-radius: 999px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo min($ind['achievedPercentage'], 100); ?>%; border-radius: 999px;"></div>
                                </div>
                                <span class="x-small fw-bold text-slate-600"><?php echo $ind['achievedPercentage']; ?>%</span>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $statusClass = $ind['status'] == 'on-track' ? 'bg-success' : ($ind['status'] == 'at-risk' ? 'bg-warning' : 'bg-danger');
                            ?>
                            <span class="badge badge-pill <?php echo $statusClass; ?> bg-opacity-10 <?php echo str_replace('bg-', 'text-', $statusClass); ?>">
                                <?php echo str_replace('-', ' ', $ind['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-sm rounded-3"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-light btn-sm rounded-3 text-danger"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Indicator Modal -->
<div class="modal fade" id="addIndicatorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-xl">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">New Indicator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Project</label>
                        <select name="projectId" class="form-select bg-light border-0 rounded-3 p-3" required>
                            <option value="">Select Project</option>
                            <?php foreach ($projects as $proj): ?>
                            <option value="<?php echo $proj['id']; ?>"><?php echo $proj['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Indicator Name</label>
                        <input type="text" name="name" class="form-control bg-light border-0 rounded-3 p-3" placeholder="e.g. Number of beneficiaries reached" required>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Target Value</label>
                            <input type="number" step="0.01" name="target" class="form-control bg-light border-0 rounded-3 p-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Unit</label>
                            <input type="text" name="unit" class="form-control bg-light border-0 rounded-3 p-3" placeholder="e.g. people, %, hours" required>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Category</label>
                        <select name="category" class="form-select bg-light border-0 rounded-3 p-3" required>
                            <option value="Outreach">Outreach</option>
                            <option value="Capacity Building">Capacity Building</option>
                            <option value="Accountability">Accountability</option>
                            <option value="Infrastructure">Infrastructure</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create Indicator</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
