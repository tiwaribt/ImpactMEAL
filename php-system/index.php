<?php
require_once 'header.php';
require_once 'sidebar.php';

// Fetch summary stats
$stmt = $db->prepare("SELECT COUNT(*) as count FROM indicators");
$stmt->execute();
$totalIndicators = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM indicators WHERE status = 'on-track'");
$stmt->execute();
$onTrack = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM indicators WHERE status = 'at-risk'");
$stmt->execute();
$atRisk = $stmt->fetch()['count'];

$stmt = $db->prepare("SELECT COUNT(*) as count FROM indicators WHERE status = 'behind'");
$stmt->execute();
$behind = $stmt->fetch()['count'];

// Fetch total reach (sum of actuals for outreach category)
$stmt = $db->prepare("SELECT SUM(actual) as total FROM indicators WHERE category = 'Outreach'");
$stmt->execute();
$totalReach = $stmt->fetch()['total'] ?: 0;

// Fetch disaggregation from monitoring entries
$stmt = $db->prepare("SELECT disaggregation FROM monitoring_entries WHERE disaggregation IS NOT NULL");
$stmt->execute();
$entries = $stmt->fetchAll();

$male = 0; $female = 0; $youth = 0;
foreach ($entries as $entry) {
    $data = json_decode($entry['disaggregation'], true);
    $male += $data['male'] ?? 0;
    $female += $data['female'] ?? 0;
    $youth += $data['youth'] ?? 0;
}

// Fetch indicators for charts
$stmt = $db->prepare("SELECT name, target, actual, achievedPercentage FROM indicators LIMIT 10");
$stmt->execute();
$indicators = $stmt->fetchAll();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-slate-900 mb-1">Dashboard Overview</h2>
            <p class="text-slate-500 small mb-0">Real-time monitoring and evaluation insights</p>
        </div>
        <div class="d-flex gap-3">
            <button class="btn btn-white border rounded-pill px-4" onclick="window.print()">
                <i class="bi bi-download me-2"></i> Export PDF
            </button>
            <a href="monitoring.php" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-lg me-2"></i> New Entry
            </a>
        </div>
    </div>

    <!-- Bento Grid Stats -->
    <div class="row g-4 mb-5">
        <!-- Main KPI -->
        <div class="col-lg-4">
            <div class="card h-100 bg-primary text-white p-4 border-0 overflow-hidden position-relative">
                <div class="position-relative z-1">
                    <p class="text-white text-opacity-75 small fw-bold text-uppercase tracking-widest mb-2">Total Direct Reach</p>
                    <h1 class="display-4 fw-bold mb-4"><?php echo number_format($totalReach); ?></h1>
                    <div class="d-flex gap-3">
                        <div class="bg-white bg-opacity-10 backdrop-blur-md p-3 rounded-4 border border-white border-opacity-10 flex-fill">
                            <p class="text-white text-opacity-50 x-small fw-bold text-uppercase mb-1">Male</p>
                            <p class="h5 fw-bold mb-0"><?php echo number_format($male); ?></p>
                        </div>
                        <div class="bg-white bg-opacity-10 backdrop-blur-md p-3 rounded-4 border border-white border-opacity-10 flex-fill">
                            <p class="text-white text-opacity-50 x-small fw-bold text-uppercase mb-1">Female</p>
                            <p class="h5 fw-bold mb-0"><?php echo number_format($female); ?></p>
                        </div>
                    </div>
                </div>
                <i class="bi bi-people position-absolute bottom-0 end-0 text-white text-opacity-10" style="font-size: 10rem; transform: translate(20%, 20%) rotate(15deg);"></i>
            </div>
        </div>

        <!-- Secondary Stats -->
        <div class="col-lg-8">
            <div class="row g-4 h-100">
                <div class="col-md-4">
                    <div class="card h-100 p-4">
                        <div class="w-12 h-12 bg-success bg-opacity-10 rounded-4 d-flex align-items-center justify-center mb-4">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                        <p class="text-slate-500 x-small fw-bold text-uppercase tracking-widest mb-1">On Track</p>
                        <h2 class="fw-bold mb-0"><?php echo $onTrack; ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 p-4">
                        <div class="w-12 h-12 bg-warning bg-opacity-10 rounded-4 d-flex align-items-center justify-center mb-4">
                            <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                        </div>
                        <p class="text-slate-500 x-small fw-bold text-uppercase tracking-widest mb-1">At Risk</p>
                        <h2 class="fw-bold mb-0"><?php echo $atRisk; ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 p-4">
                        <div class="w-12 h-12 bg-danger bg-opacity-10 rounded-4 d-flex align-items-center justify-center mb-4">
                            <i class="bi bi-arrow-down-circle text-danger fs-4"></i>
                        </div>
                        <p class="text-slate-500 x-small fw-bold text-uppercase tracking-widest mb-1">Behind</p>
                        <h2 class="fw-bold mb-0"><?php echo $behind; ?></h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 p-4 bg-slate-900 text-white border-0">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-10 rounded-4 d-flex align-items-center justify-center">
                                <i class="bi bi-target text-white fs-4"></i>
                            </div>
                            <span class="badge bg-primary rounded-pill">Active</span>
                        </div>
                        <p class="text-slate-400 x-small fw-bold text-uppercase tracking-widest mb-1">Total Indicators</p>
                        <h2 class="fw-bold mb-0"><?php echo $totalIndicators; ?></h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 p-4 border-0" style="background: linear-gradient(135deg, #fef2f2 0%, #fff7ed 100%);">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="w-12 h-12 bg-rose-500 bg-opacity-10 rounded-4 d-flex align-items-center justify-center">
                                <i class="bi bi-chat-left-text text-rose-500 fs-4"></i>
                            </div>
                        </div>
                        <p class="text-slate-500 x-small fw-bold text-uppercase tracking-widest mb-1">Feedback Collected</p>
                        <h2 class="fw-bold mb-0">128</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Indicator Performance</h5>
                    <select class="form-select form-select-sm w-auto border-0 bg-light rounded-pill px-3">
                        <option>Last 30 Days</option>
                        <option>Last 6 Months</option>
                        <option>All Time</option>
                    </select>
                </div>
                <div style="height: 350px;">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card p-4 h-100">
                <h5 class="fw-bold mb-4">Gender Breakdown</h5>
                <div style="height: 250px;">
                    <canvas id="genderChart"></canvas>
                </div>
                <div class="mt-4 space-y-3">
                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 hover-bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <div class="w-3 h-3 rounded-circle bg-primary"></div>
                            <span class="text-slate-600 small fw-medium">Male</span>
                        </div>
                        <span class="fw-bold text-slate-900"><?php echo number_format($male); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 hover-bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <div class="w-3 h-3 rounded-circle bg-info"></div>
                            <span class="text-slate-600 small fw-medium">Female</span>
                        </div>
                        <span class="fw-bold text-slate-900"><?php echo number_format($female); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 hover-bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <div class="w-3 h-3 rounded-circle bg-warning"></div>
                            <span class="text-slate-600 small fw-medium">Youth</span>
                        </div>
                        <span class="fw-bold text-slate-900"><?php echo number_format($youth); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Performance Chart
    const perfCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(perfCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($indicators, 'name')); ?>,
            datasets: [{
                label: 'Actual',
                data: <?php echo json_encode(array_column($indicators, 'actual')); ?>,
                backgroundColor: '#6366f1',
                borderRadius: 8,
                barThickness: 20
            }, {
                label: 'Target',
                data: <?php echo json_encode(array_column($indicators, 'target')); ?>,
                backgroundColor: '#e2e8f0',
                borderRadius: 8,
                barThickness: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 6 } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } },
                y: { grid: { borderDash: [5, 5], drawBorder: false }, ticks: { font: { size: 10 } } }
            }
        }
    });

    // Gender Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female', 'Youth'],
            datasets: [{
                data: [<?php echo "$male, $female, $youth"; ?>],
                backgroundColor: ['#6366f1', '#0ea5e9', '#f59e0b'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>

<?php require_once 'footer.php'; ?>
