<?php
require_once 'db.php';
$database = new Database();
$db = $database->getConnection();

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

// Fetch indicators for charts
$stmt = $db->prepare("SELECT name, target, actual, achievedPercentage FROM indicators");
$stmt->execute();
$indicators = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact MEAL - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .sidebar { height: 100vh; background-color: #0f172a; color: #94a3b8; }
        .nav-link { color: #94a3b8; border-radius: 8px; margin-bottom: 4px; }
        .nav-link.active { background-color: #4f46e5; color: white; }
        .card { border-radius: 16px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-card { padding: 24px; }
        .stat-value { font-size: 2rem; font-weight: 700; color: #1e293b; }
        .stat-label { font-size: 0.875rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
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
                    <a class="nav-link active" href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a class="nav-link" href="indicators.php"><i class="bi bi-target me-2"></i> Indicators</a>
                    <a class="nav-link" href="monitoring.php"><i class="bi bi-clipboard-data me-2"></i> Monitoring</a>
                    <a class="nav-link" href="gis.php"><i class="bi bi-map me-2"></i> GIS View</a>
                    <a class="nav-link" href="reports.php"><i class="bi bi-file-earmark-text me-2"></i> Reports</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Dashboard Overview</h2>
                    <button class="btn btn-primary rounded-pill px-4"><i class="bi bi-plus-lg me-2"></i> New Entry</button>
                </div>

                <!-- Stats -->
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="stat-label">Total Indicators</div>
                            <div class="stat-value"><?php echo $totalIndicators; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card border-start border-success border-4">
                            <div class="stat-label">On Track</div>
                            <div class="stat-value text-success"><?php echo $onTrack; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card border-start border-warning border-4">
                            <div class="stat-label">At Risk</div>
                            <div class="stat-value text-warning"><?php echo $atRisk; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card border-start border-danger border-4">
                            <div class="stat-label">Behind</div>
                            <div class="stat-value text-danger"><?php echo $behind; ?></div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-4">Achievement Progress</h5>
                            <canvas id="achievementChart" height="300"></canvas>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-4">Status Distribution</h5>
                            <canvas id="statusChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Achievement Chart
        const ctxAch = document.getElementById('achievementChart').getContext('2d');
        new Chart(ctxAch, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($indicators, 'name')); ?>,
                datasets: [{
                    label: 'Target',
                    data: <?php echo json_encode(array_column($indicators, 'target')); ?>,
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderColor: 'rgba(79, 70, 229, 0.3)',
                    borderWidth: 1
                }, {
                    label: 'Actual',
                    data: <?php echo json_encode(array_column($indicators, 'actual')); ?>,
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });

        // Status Chart
        const ctxStat = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStat, {
            type: 'doughnut',
            data: {
                labels: ['On Track', 'At Risk', 'Behind'],
                datasets: [{
                    data: [<?php echo "$onTrack, $atRisk, $behind"; ?>],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
</body>
</html>
