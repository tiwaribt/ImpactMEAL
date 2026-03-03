<?php
require_once 'header.php';
require_once 'sidebar.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id = uniqid();
    $name = $_POST['name'];
    $description = $_POST['description'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    
    $stmt = $db->prepare("INSERT INTO projects (id, name, description, startDate, endDate) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id, $name, $description, $startDate, $endDate]);
    header("Location: projects.php");
    exit;
}

// Fetch Projects
$stmt = $db->prepare("SELECT * FROM projects ORDER BY created_at DESC");
$stmt->execute();
$projects = $stmt->fetchAll();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-slate-900 mb-1">Project Management</h2>
            <p class="text-slate-500 small mb-0">Manage and track all active projects</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addProjectModal">
            <i class="bi bi-plus-lg me-2"></i> Add Project
        </button>
    </div>

    <div class="row g-4">
        <?php foreach ($projects as $project): ?>
        <div class="col-md-4">
            <div class="card h-100 p-4">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="w-12 h-12 bg-primary bg-opacity-10 rounded-4 d-flex align-items-center justify-center">
                        <i class="bi bi-folder2-open text-primary fs-4"></i>
                    </div>
                    <span class="badge badge-pill bg-success bg-opacity-10 text-success"><?php echo $project['status']; ?></span>
                </div>
                <h5 class="fw-bold text-slate-900 mb-2"><?php echo $project['name']; ?></h5>
                <p class="text-slate-500 small mb-4 line-clamp-2"><?php echo $project['description']; ?></p>
                
                <div class="mt-auto pt-4 border-top border-slate-100">
                    <div class="d-flex justify-content-between align-items-center text-slate-500 x-small fw-bold text-uppercase tracking-widest">
                        <span><i class="bi bi-calendar3 me-2"></i> <?php echo date('M d, Y', strtotime($project['startDate'])); ?></span>
                        <span><i class="bi bi-arrow-right me-2"></i> <?php echo date('M d, Y', strtotime($project['endDate'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-xl">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">New Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Project Name</label>
                        <input type="text" name="name" class="form-control bg-light border-0 rounded-3 p-3" placeholder="e.g. Youth Empowerment Program" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Description</label>
                        <textarea name="description" class="form-control bg-light border-0 rounded-3 p-3" rows="3" placeholder="Brief project overview..."></textarea>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Start Date</label>
                            <input type="date" name="startDate" class="form-control bg-light border-0 rounded-3 p-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">End Date</label>
                            <input type="date" name="endDate" class="form-control bg-light border-0 rounded-3 p-3">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
