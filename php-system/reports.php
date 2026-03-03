<?php
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Projects for dropdown
$stmt = $db->prepare("SELECT id, name FROM projects");
$stmt->execute();
$projects = $stmt->fetchAll();

// Fetch Indicators for dropdown
$stmt = $db->prepare("SELECT id, name FROM indicators");
$stmt->execute();
$indicators = $stmt->fetchAll();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-slate-900 mb-1">Donor & Custom Reporting</h2>
            <p class="text-slate-500 small mb-0">Generate and export project performance reports</p>
        </div>
        <div class="d-flex gap-3">
            <button class="btn btn-white border rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#uploadTemplateModal">
                <i class="bi bi-upload me-2"></i> Upload Template
            </button>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                <i class="bi bi-file-earmark-text me-2"></i> Generate Report
            </button>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <!-- Donor Templates -->
        <div class="col-md-6">
            <div class="card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Donor Templates</h5>
                    <span class="badge badge-pill bg-primary bg-opacity-10 text-primary">Standard</span>
                </div>
                <div class="space-y-4">
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-light border border-slate-100 hover-bg-white transition-all">
                        <div class="d-flex align-items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-3 d-flex align-items-center justify-center shadow-sm">
                                <i class="bi bi-file-earmark-spreadsheet text-success fs-5"></i>
                            </div>
                            <div>
                                <p class="mb-0 small fw-bold text-slate-900">USAID Standard Report</p>
                                <p class="mb-0 x-small text-slate-500">XLSX • Quarterly Performance</p>
                            </div>
                        </div>
                        <button class="btn btn-light btn-sm rounded-pill px-3 fw-bold x-small">DOWNLOAD</button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-light border border-slate-100 hover-bg-white transition-all">
                        <div class="d-flex align-items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-3 d-flex align-items-center justify-center shadow-sm">
                                <i class="bi bi-file-earmark-spreadsheet text-success fs-5"></i>
                            </div>
                            <div>
                                <p class="mb-0 small fw-bold text-slate-900">UNICEF Monitoring Framework</p>
                                <p class="mb-0 x-small text-slate-500">XLSX • Monthly Update</p>
                            </div>
                        </div>
                        <button class="btn btn-light btn-sm rounded-pill px-3 fw-bold x-small">DOWNLOAD</button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-light border border-slate-100 hover-bg-white transition-all">
                        <div class="d-flex align-items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-3 d-flex align-items-center justify-center shadow-sm">
                                <i class="bi bi-file-earmark-spreadsheet text-success fs-5"></i>
                            </div>
                            <div>
                                <p class="mb-0 small fw-bold text-slate-900">EU Development Fund Template</p>
                                <p class="mb-0 x-small text-slate-500">XLSX • Annual Review</p>
                            </div>
                        </div>
                        <button class="btn btn-light btn-sm rounded-pill px-3 fw-bold x-small">DOWNLOAD</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Templates -->
        <div class="col-md-6">
            <div class="card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Custom Templates</h5>
                    <span class="badge badge-pill bg-indigo-50 text-indigo-600">User Uploaded</span>
                </div>
                <div class="space-y-4">
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-4 bg-light border border-slate-100 hover-bg-white transition-all">
                        <div class="d-flex align-items-center gap-3">
                            <div class="w-10 h-10 bg-white rounded-3 d-flex align-items-center justify-center shadow-sm">
                                <i class="bi bi-file-earmark-spreadsheet text-primary fs-5"></i>
                            </div>
                            <div>
                                <p class="mb-0 small fw-bold text-slate-900">Internal Monthly Dashboard</p>
                                <p class="mb-0 x-small text-slate-500">XLSX • Uploaded by Admin</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-light btn-sm rounded-circle"><i class="bi bi-download"></i></button>
                            <button class="btn btn-light btn-sm rounded-circle text-danger"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center p-5 rounded-4 border-2 border-dashed border-slate-200 bg-slate-50">
                        <div class="text-center">
                            <i class="bi bi-cloud-upload text-slate-300 fs-1 mb-2"></i>
                            <p class="text-slate-400 small mb-0">Drag and drop custom XLSX templates here</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="card border-0 overflow-hidden">
        <div class="p-4 border-bottom border-slate-100 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Generated Reports History</h5>
            <div class="input-group w-auto">
                <span class="input-group-text bg-light border-0"><i class="bi bi-search text-slate-400"></i></span>
                <input type="text" class="form-control bg-light border-0 small" placeholder="Search reports...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Report Name</th>
                        <th>Project</th>
                        <th>Type</th>
                        <th>Generated By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold text-slate-900">Q1 Performance Summary</td>
                        <td>Youth Empowerment</td>
                        <td><span class="badge badge-pill bg-indigo-50 text-indigo-600">Quarterly</span></td>
                        <td>Admin</td>
                        <td class="small text-slate-500">Oct 24, 2023</td>
                        <td><span class="badge badge-pill bg-success bg-opacity-10 text-success">Completed</span></td>
                        <td>
                            <button class="btn btn-light btn-sm rounded-3"><i class="bi bi-download"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold text-slate-900">Monthly Monitoring Update</td>
                        <td>Community Health</td>
                        <td><span class="badge badge-pill bg-blue-50 text-blue-600">Monthly</span></td>
                        <td>MEAL Officer</td>
                        <td class="small text-slate-500">Oct 20, 2023</td>
                        <td><span class="badge badge-pill bg-success bg-opacity-10 text-success">Completed</span></td>
                        <td>
                            <button class="btn btn-light btn-sm rounded-3"><i class="bi bi-download"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Generate Report Modal -->
<div class="modal fade" id="generateReportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-xl">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Generate New Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="api.php?action=generate_report" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Project</label>
                        <select name="projectId" class="form-select bg-light border-0 rounded-3 p-3" required>
                            <option value="all">All Projects</option>
                            <?php foreach ($projects as $proj): ?>
                            <option value="<?php echo $proj['id']; ?>"><?php echo $proj['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Template Type</label>
                        <select name="template" class="form-select bg-light border-0 rounded-3 p-3" required>
                            <option value="usaid">USAID Standard</option>
                            <option value="unicef">UNICEF Monitoring</option>
                            <option value="internal">Internal Dashboard</option>
                        </select>
                    </div>
                    <div class="row g-4 mb-0">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Start Date</label>
                            <input type="date" name="startDate" class="form-control bg-light border-0 rounded-3 p-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">End Date</label>
                            <input type="date" name="endDate" class="form-control bg-light border-0 rounded-3 p-3" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Generate XLSX</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
