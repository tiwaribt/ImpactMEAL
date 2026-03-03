<?php
require_once 'header.php';
require_once 'sidebar.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $id = uniqid();
        $source = $_POST['source'];
        $content = $_POST['content'];
        $date = $_POST['date'];
        $sentiment = $_POST['sentiment'];
        $respondentType = $_POST['respondentType'];
        
        $stmt = $db->prepare("INSERT INTO qualitative_feedback (id, source, content, date, sentiment, respondentType) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $source, $content, $date, $sentiment, $respondentType]);
    } elseif ($_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $source = $_POST['source'];
        $content = $_POST['content'];
        $date = $_POST['date'];
        $sentiment = $_POST['sentiment'];
        $respondentType = $_POST['respondentType'];
        
        $stmt = $db->prepare("UPDATE qualitative_feedback SET source = ?, content = ?, date = ?, sentiment = ?, respondentType = ? WHERE id = ?");
        $stmt->execute([$source, $content, $date, $sentiment, $respondentType, $id]);
    } elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM qualitative_feedback WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: feedback.php");
    exit;
}

// Fetch Feedback
$stmt = $db->prepare("SELECT * FROM qualitative_feedback ORDER BY date DESC");
$stmt->execute();
$feedbacks = $stmt->fetchAll();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-slate-900 mb-1">Qualitative Feedback</h2>
            <p class="text-slate-500 small mb-0">Accountability and community response monitoring</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addFeedbackModal">
            <i class="bi bi-plus-lg me-2"></i> Add Feedback
        </button>
    </div>

    <div class="row g-4">
        <?php foreach ($feedbacks as $fb): ?>
        <div class="col-md-6">
            <div class="card border-0 p-4 h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="w-10 h-10 rounded-circle bg-slate-100 d-flex align-items-center justify-center text-slate-600 fw-bold">
                            <?php echo strtoupper(substr($fb['source'], 0, 1)); ?>
                        </div>
                        <div>
                            <p class="mb-0 small fw-bold text-slate-900"><?php echo $fb['source']; ?></p>
                            <p class="mb-0 x-small text-slate-500"><?php echo $fb['respondentType']; ?></p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <?php 
                        $sentimentClass = $fb['sentiment'] == 'positive' ? 'bg-success' : ($fb['sentiment'] == 'negative' ? 'bg-danger' : 'bg-warning');
                        ?>
                        <span class="badge badge-pill <?php echo $sentimentClass; ?> bg-opacity-10 <?php echo str_replace('bg-', 'text-', $sentimentClass); ?>">
                            <?php echo $fb['sentiment']; ?>
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                <li><a class="dropdown-item small" href="#" onclick='editFeedback(<?php echo json_encode($fb); ?>)'>Edit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this feedback?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $fb['id']; ?>">
                                        <button type="submit" class="dropdown-item small text-danger">Delete</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <p class="text-slate-600 small mb-4 italic">"<?php echo $fb['content']; ?>"</p>
                <div class="mt-auto pt-3 border-top border-slate-50 d-flex justify-content-between align-items-center">
                    <span class="x-small text-slate-400 fw-bold text-uppercase tracking-widest">
                        <i class="bi bi-calendar3 me-1"></i> <?php echo date('M d, Y', strtotime($fb['date'])); ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Feedback Modal -->
<div class="modal fade" id="addFeedbackModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-xl">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">New Feedback Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Source / Respondent Name</label>
                        <input type="text" name="source" class="form-control bg-light border-0 rounded-3 p-3" placeholder="e.g. Community Leader, Beneficiary" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Feedback Content</label>
                        <textarea name="content" class="form-control bg-light border-0 rounded-3 p-3" rows="4" placeholder="What was the feedback?" required></textarea>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Date</label>
                            <input type="date" name="date" class="form-control bg-light border-0 rounded-3 p-3" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Respondent Type</label>
                            <select name="respondentType" class="form-select bg-light border-0 rounded-3 p-3" required>
                                <option value="Beneficiary">Beneficiary</option>
                                <option value="Partner">Partner</option>
                                <option value="Staff">Staff</option>
                                <option value="Community Member">Community Member</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Sentiment</label>
                        <div class="d-flex gap-3 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sentiment" value="positive" id="sentPos" checked>
                                <label class="form-check-label small" for="sentPos">Positive</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sentiment" value="neutral" id="sentNeu">
                                <label class="form-check-label small" for="sentNeu">Neutral</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sentiment" value="negative" id="sentNeg">
                                <label class="form-check-label small" for="sentNeg">Negative</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Feedback Modal -->
<div class="modal fade" id="editFeedbackModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-xl">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Edit Feedback Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Source / Respondent Name</label>
                        <input type="text" name="source" id="edit_source" class="form-control bg-light border-0 rounded-3 p-3" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Feedback Content</label>
                        <textarea name="content" id="edit_content" class="form-control bg-light border-0 rounded-3 p-3" rows="4" required></textarea>
                    </div>
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Date</label>
                            <input type="date" name="date" id="edit_date" class="form-control bg-light border-0 rounded-3 p-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Respondent Type</label>
                            <select name="respondentType" id="edit_respondentType" class="form-select bg-light border-0 rounded-3 p-3" required>
                                <option value="Beneficiary">Beneficiary</option>
                                <option value="Partner">Partner</option>
                                <option value="Staff">Staff</option>
                                <option value="Community Member">Community Member</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-slate-500 x-small fw-bold text-uppercase tracking-widest">Sentiment</label>
                        <div class="d-flex gap-3 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sentiment" value="positive" id="edit_sentPos">
                                <label class="form-check-label small" for="edit_sentPos">Positive</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sentiment" value="neutral" id="edit_sentNeu">
                                <label class="form-check-label small" for="edit_sentNeu">Neutral</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sentiment" value="negative" id="edit_sentNeg">
                                <label class="form-check-label small" for="edit_sentNeg">Negative</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editFeedback(fb) {
    document.getElementById('edit_id').value = fb.id;
    document.getElementById('edit_source').value = fb.source;
    document.getElementById('edit_content').value = fb.content;
    document.getElementById('edit_date').value = fb.date;
    document.getElementById('edit_respondentType').value = fb.respondentType;
    
    if (fb.sentiment === 'positive') document.getElementById('edit_sentPos').checked = true;
    else if (fb.sentiment === 'neutral') document.getElementById('edit_sentNeu').checked = true;
    else if (fb.sentiment === 'negative') document.getElementById('edit_sentNeg').checked = true;
    
    new bootstrap.Modal(document.getElementById('editFeedbackModal')).show();
}
</script>

<?php require_once 'footer.php'; ?>
