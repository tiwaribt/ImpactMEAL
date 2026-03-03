<?php
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Indicators for dropdown
$stmt = $db->prepare("SELECT id, name, unit FROM indicators");
$stmt->execute();
$indicators = $stmt->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $id = uniqid();
        $indicatorId = $_POST['indicatorId'];
        $date = $_POST['date'];
        $value = $_POST['value'];
        $location = $_POST['location'];
        $notes = $_POST['notes'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        
        $disaggregation = [
            'male' => (float)$_POST['male'],
            'female' => (float)$_POST['female'],
            'youth' => (float)$_POST['youth']
        ];
        
        $stmt = $db->prepare("INSERT INTO monitoring_entries (id, indicatorId, date, value, location, notes, latitude, longitude, disaggregation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $indicatorId, $date, $value, $location, $notes, $latitude, $longitude, json_encode($disaggregation)]);
        
        // Update indicator actual value
        $stmt = $db->prepare("UPDATE indicators SET actual = actual + ? WHERE id = ?");
        $stmt->execute([$value, $indicatorId]);
        
        recalculateIndicator($db, $indicatorId);
    } elseif ($_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $indicatorId = $_POST['indicatorId'];
        $date = $_POST['date'];
        $value = $_POST['value'];
        $location = $_POST['location'];
        $notes = $_POST['notes'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        
        $disaggregation = [
            'male' => (float)$_POST['male'],
            'female' => (float)$_POST['female'],
            'youth' => (float)$_POST['youth']
        ];

        // Get old value to calculate difference
        $stmt = $db->prepare("SELECT value FROM monitoring_entries WHERE id = ?");
        $stmt->execute([$id]);
        $oldValue = $stmt->fetchColumn();
        $diff = $value - $oldValue;
        
        $stmt = $db->prepare("UPDATE monitoring_entries SET indicatorId = ?, date = ?, value = ?, location = ?, notes = ?, latitude = ?, longitude = ?, disaggregation = ? WHERE id = ?");
        $stmt->execute([$indicatorId, $date, $value, $location, $notes, $latitude, $longitude, json_encode($disaggregation), $id]);
        
        // Update indicator actual value
        $stmt = $db->prepare("UPDATE indicators SET actual = actual + ? WHERE id = ?");
        $stmt->execute([$diff, $indicatorId]);
        
        recalculateIndicator($db, $indicatorId);
    } elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        
        // Get value and indicatorId before deleting
        $stmt = $db->prepare("SELECT value, indicatorId FROM monitoring_entries WHERE id = ?");
        $stmt->execute([$id]);
        $entry = $stmt->fetch();
        
        if ($entry) {
            $stmt = $db->prepare("DELETE FROM monitoring_entries WHERE id = ?");
            $stmt->execute([$id]);
            
            // Update indicator actual value
            $stmt = $db->prepare("UPDATE indicators SET actual = actual - ? WHERE id = ?");
            $stmt->execute([$entry['value'], $entry['indicatorId']]);
            
            recalculateIndicator($db, $entry['indicatorId']);
        }
    }
    header("Location: monitoring.php");
    exit;
}

function recalculateIndicator($db, $indicatorId) {
    $stmt = $db->prepare("SELECT actual, target FROM indicators WHERE id = ?");
    $stmt->execute([$indicatorId]);
    $ind = $stmt->fetch();
    
    if ($ind) {
        $achieved = ($ind['actual'] / $ind['target']) * 100;
        $gap = $ind['target'] - $ind['actual'];
        $status = $achieved >= 90 ? 'on-track' : ($achieved >= 70 ? 'at-risk' : 'behind');
        
        $stmt = $db->prepare("UPDATE indicators SET achievedPercentage = ?, gap = ?, status = ? WHERE id = ?");
        $stmt->execute([$achieved, $gap, $status, $indicatorId]);
    }
}

// Fetch Monitoring Entries
$stmt = $db->prepare("SELECT m.*, i.name as indicatorName, i.unit FROM monitoring_entries m JOIN indicators i ON m.indicatorId = i.id ORDER BY m.date DESC");
$stmt->execute();
$entries = $stmt->fetchAll();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-slate-900 mb-1">Monitoring Data</h2>
            <p class="text-slate-500 small mb-0">Record and track field monitoring activities</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addEntryModal">
            <i class="bi bi-plus-lg me-2"></i> Add Entry
        </button>
    </div>

    <div class="card border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Indicator</th>
                        <th>Value</th>
                        <th>Location</th>
                        <th>Disaggregation (M/F/Y)</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td class="fw-bold text-slate-900"><?php echo date('M d, Y', strtotime($entry['date'])); ?></td>
                        <td>
                            <div class="fw-bold text-slate-900"><?php echo $entry['indicatorName']; ?></div>
                        </td>
                        <td class="fw-bold text-primary"><?php echo $entry['value']; ?> <?php echo $entry['unit']; ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt text-danger"></i>
                                <span class="small fw-medium"><?php echo $entry['location']; ?></span>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $dis = json_decode($entry['disaggregation'], true);
                            ?>
                            <div class="d-flex gap-2">
                                <span class="badge bg-blue-50 text-blue-600 x-small fw-bold border border-blue-100"><?php echo $dis['male'] ?? 0; ?> M</span>
                                <span class="badge bg-pink-50 text-pink-600 x-small fw-bold border border-pink-100"><?php echo $dis['female'] ?? 0; ?> F</span>
                                <span class="badge bg-indigo-50 text-indigo-600 x-small fw-bold border border-indigo-100"><?php echo $dis['youth'] ?? 0; ?> Y</span>
                            </div>
                        </td>
                        <td class="text-slate-500 small"><?php echo $entry['notes']; ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-sm rounded-3" onclick='editEntry(<?php echo json_encode($entry); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this entry?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Entry Modal -->
<div class="modal fade" id="addEntryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-xl">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">New Monitoring Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Indicator</label>
                            <select name="indicatorId" class="form-select bg-light border-0 rounded-3 p-3" required>
                                <option value="">Select Indicator</option>
                                <?php foreach ($indicators as $ind): ?>
                                <option value="<?php echo $ind['id']; ?>"><?php echo $ind['name']; ?> (<?php echo $ind['unit']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Date</label>
                            <input type="date" name="date" class="form-control bg-light border-0 rounded-3 p-3" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Value</label>
                            <input type="number" step="0.01" name="value" class="form-control bg-light border-0 rounded-3 p-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Location</label>
                            <input type="text" name="location" class="form-control bg-light border-0 rounded-3 p-3" placeholder="e.g. Kathmandu, Nepal" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Disaggregation</label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-blue-50 border-0 text-blue-600 fw-bold">M</span>
                                    <input type="number" name="male" class="form-control bg-light border-0 rounded-end-3 p-3" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-pink-50 border-0 text-pink-600 fw-bold">F</span>
                                    <input type="number" name="female" class="form-control bg-light border-0 rounded-end-3 p-3" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-indigo-50 border-0 text-indigo-600 fw-bold">Y</span>
                                    <input type="number" name="youth" class="form-control bg-light border-0 rounded-end-3 p-3" value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control bg-light border-0 rounded-3 p-3" placeholder="27.7172">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control bg-light border-0 rounded-3 p-3" placeholder="85.3240">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Notes</label>
                        <textarea name="notes" class="form-control bg-light border-0 rounded-3 p-3" rows="3" placeholder="Additional details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Entry Modal -->
<div class="modal fade" id="editEntryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-xl">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Edit Monitoring Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Indicator</label>
                            <select name="indicatorId" id="edit_indicatorId" class="form-select bg-light border-0 rounded-3 p-3" required>
                                <option value="">Select Indicator</option>
                                <?php foreach ($indicators as $ind): ?>
                                <option value="<?php echo $ind['id']; ?>"><?php echo $ind['name']; ?> (<?php echo $ind['unit']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Date</label>
                            <input type="date" name="date" id="edit_date" class="form-control bg-light border-0 rounded-3 p-3" required>
                        </div>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Value</label>
                            <input type="number" step="0.01" name="value" id="edit_value" class="form-control bg-light border-0 rounded-3 p-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Location</label>
                            <input type="text" name="location" id="edit_location" class="form-control bg-light border-0 rounded-3 p-3" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Disaggregation</label>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-blue-50 border-0 text-blue-600 fw-bold">M</span>
                                    <input type="number" name="male" id="edit_male" class="form-control bg-light border-0 rounded-end-3 p-3">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-pink-50 border-0 text-pink-600 fw-bold">F</span>
                                    <input type="number" name="female" id="edit_female" class="form-control bg-light border-0 rounded-end-3 p-3">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-indigo-50 border-0 text-indigo-600 fw-bold">Y</span>
                                    <input type="number" name="youth" id="edit_youth" class="form-control bg-light border-0 rounded-end-3 p-3">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Latitude</label>
                            <input type="number" step="any" name="latitude" id="edit_latitude" class="form-control bg-light border-0 rounded-3 p-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Longitude</label>
                            <input type="number" step="any" name="longitude" id="edit_longitude" class="form-control bg-light border-0 rounded-3 p-3">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Notes</label>
                        <textarea name="notes" id="edit_notes" class="form-control bg-light border-0 rounded-3 p-3" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editEntry(entry) {
    document.getElementById('edit_id').value = entry.id;
    document.getElementById('edit_indicatorId').value = entry.indicatorId;
    document.getElementById('edit_date').value = entry.date;
    document.getElementById('edit_value').value = entry.value;
    document.getElementById('edit_location').value = entry.location;
    document.getElementById('edit_latitude').value = entry.latitude;
    document.getElementById('edit_longitude').value = entry.longitude;
    document.getElementById('edit_notes').value = entry.notes;
    
    const dis = JSON.parse(entry.disaggregation);
    document.getElementById('edit_male').value = dis.male || 0;
    document.getElementById('edit_female').value = dis.female || 0;
    document.getElementById('edit_youth').value = dis.youth || 0;
    
    new bootstrap.Modal(document.getElementById('editEntryModal')).show();
}
</script>

<?php require_once 'footer.php'; ?>
