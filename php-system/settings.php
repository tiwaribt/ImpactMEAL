<?php
require_once 'header.php';
require_once 'sidebar.php';

// Only admins can access settings
if ($_SESSION['role'] !== 'admin') {
    echo "<div class='main-content'><div class='alert alert-danger'>Access Denied. Admins only.</div></div>";
    require_once 'footer.php';
    exit;
}

// Fetch Users
$stmt = $db->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll();

// Fetch Projects
$stmt = $db->prepare("SELECT * FROM projects");
$stmt->execute();
$projects = $stmt->fetchAll();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-slate-900 mb-1">System Settings</h2>
            <p class="text-slate-500 small mb-0">Configure projects, users and system parameters</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Tabs Navigation -->
        <div class="col-md-3">
            <div class="card p-3 border-0">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active text-start mb-2" id="v-pills-projects-tab" data-bs-toggle="pill" data-bs-target="#v-pills-projects" type="button" role="tab">
                        <i class="bi bi-folder me-2"></i> Project Names
                    </button>
                    <button class="nav-link text-start mb-2" id="v-pills-users-tab" data-bs-toggle="pill" data-bs-target="#v-pills-users" type="button" role="tab">
                        <i class="bi bi-people me-2"></i> User Management
                    </button>
                    <button class="nav-link text-start mb-2" id="v-pills-system-tab" data-bs-toggle="pill" data-bs-target="#v-pills-system" type="button" role="tab">
                        <i class="bi bi-cpu me-2"></i> System Config
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs Content -->
        <div class="col-md-9">
            <div class="tab-content" id="v-pills-tabContent">
                <!-- Projects Tab -->
                <div class="tab-pane fade show active" id="v-pills-projects" role="tabpanel">
                    <div class="card border-0 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Project Names</h5>
                            <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addProjectModal">Add New</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Project Name</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $project['name']; ?></td>
                                        <td><span class="badge bg-success bg-opacity-10 text-success"><?php echo $project['status']; ?></span></td>
                                        <td class="small text-slate-500"><?php echo date('M d, Y', strtotime($project['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-light btn-sm rounded-circle"><i class="bi bi-pencil"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Users Tab -->
                <div class="tab-pane fade" id="v-pills-users" role="tabpanel">
                    <div class="card border-0 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">User Accounts</h5>
                            <button class="btn btn-primary btn-sm rounded-pill px-3">Invite User</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="w-8 h-8 rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-center fw-bold small">
                                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                </div>
                                                <span class="fw-bold"><?php echo $user['username']; ?></span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-indigo-50 text-indigo-600"><?php echo $user['role']; ?></span></td>
                                        <td class="small text-slate-500"><?php echo $user['email']; ?></td>
                                        <td>
                                            <button class="btn btn-light btn-sm rounded-circle"><i class="bi bi-shield-lock"></i></button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- System Tab -->
                <div class="tab-pane fade" id="v-pills-system" role="tabpanel">
                    <div class="card border-0 p-4">
                        <h5 class="fw-bold mb-4">System Configuration</h5>
                        <form>
                            <div class="mb-4">
                                <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Application Name</label>
                                <input type="text" class="form-control bg-light border-0 p-3" value="Impact MEAL">
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Default Map Center (Lat, Lng)</label>
                                <div class="row g-3">
                                    <div class="col">
                                        <input type="text" class="form-control bg-light border-0 p-3" value="28.3949">
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control bg-light border-0 p-3" value="84.1240">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
