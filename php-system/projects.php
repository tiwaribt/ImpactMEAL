<?php
require_once 'header.php';
require_once 'sidebar.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $id = uniqid();
        $name = $_POST['name'];
        $description = $_POST['description'];
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        
        $stmt = $db->prepare("INSERT INTO projects (id, name, description, startDate, endDate) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $name, $description, $startDate, $endDate]);
    } elseif ($_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        $status = $_POST['status'];
        
        $stmt = $db->prepare("UPDATE projects SET name = ?, description = ?, startDate = ?, endDate = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $description, $startDate, $endDate, $status, $id]);
    } elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$id]);
    }
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
                    <div class="d-flex gap-2">
                        <span class="badge badge-pill bg-success bg-opacity-10 text-success"><?php echo $project['status']; ?></span>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                <li><a class="dropdown-item small" href="#" onclick='editProject(<?php echo json_encode($project); ?>)'>Edit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this project?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                                        <button type="submit" class="dropdown-item small text-danger">Delete</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
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

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-xl">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Edit Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Project Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control bg-light border-0 rounded-3 p-3" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Description</label>
                        <textarea name="description" id="edit_description" class="form-control bg-light border-0 rounded-3 p-3" rows="3"></textarea>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Start Date</label>
                            <input type="date" name="startDate" id="edit_startDate" class="form-control bg-light border-0 rounded-3 p-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">End Date</label>
                            <input type="date" name="endDate" id="edit_endDate" class="form-control bg-light border-0 rounded-3 p-3">
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Status</label>
                        <select name="status" id="edit_status" class="form-select bg-light border-0 rounded-3 p-3">
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="on-hold">On Hold</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editProject(proj) {
    document.getElementById('edit_id').value = proj.id;
    document.getElementById('edit_name').value = proj.name;
    document.getElementById('edit_description').value = proj.description;
    document.getElementById('edit_startDate').value = proj.startDate;
    document.getElementById('edit_endDate').value = proj.endDate;
    document.getElementById('edit_status').value = proj.status;
    
    new bootstrap.Modal(document.getElementById('editProjectModal')).show();
}
</script>

<?php require_once 'footer.php'; ?>
