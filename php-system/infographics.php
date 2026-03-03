<?php
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Indicators for infographics
$stmt = $db->prepare("SELECT * FROM indicators ORDER BY achievedPercentage DESC");
$stmt->execute();
$indicators = $stmt->fetchAll();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-slate-900 mb-1">Impact Infographics</h2>
            <p class="text-slate-500 small mb-0">Visualizing key performance indicators and project impact</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-white border rounded-pill px-4" onclick="window.print()">
                <i class="bi bi-printer me-2"></i> Print Infographic
            </button>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#customizeInfographicModal">
                <i class="bi bi-palette me-2"></i> Customize
            </button>
        </div>
    </div>

    <!-- Infographic Grid -->
    <div class="row g-4 mb-5">
        <?php foreach ($indicators as $idx => $ind): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 p-4 h-100 overflow-hidden position-relative" style="background: <?php echo $idx % 2 == 0 ? '#ffffff' : '#f8fafc'; ?>;">
                <div class="position-relative z-1">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="w-10 h-10 bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-center">
                            <i class="bi bi-graph-up-arrow text-primary"></i>
                        </div>
                        <span class="badge badge-pill bg-indigo-50 text-indigo-600"><?php echo $ind['category']; ?></span>
                    </div>
                    <h5 class="fw-bold text-slate-900 mb-1"><?php echo $ind['name']; ?></h5>
                    <p class="text-slate-500 x-small fw-bold text-uppercase tracking-widest mb-4"><?php echo $ind['unit']; ?></p>
                    
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="display-6 fw-bold text-slate-900 mb-0"><?php echo number_format($ind['actual']); ?></h2>
                        <span class="text-slate-400 fw-medium">/ <?php echo number_format($ind['target']); ?></span>
                    </div>

                    <div class="progress mb-3" style="height: 12px; border-radius: 999px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo min($ind['achievedPercentage'], 100); ?>%; border-radius: 999px;"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-1">
                            <?php if ($ind['achievedPercentage'] >= 100): ?>
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span class="text-success small fw-bold">Target Achieved</span>
                            <?php else: ?>
                                <span class="text-slate-500 x-small fw-bold text-uppercase tracking-widest"><?php echo $ind['achievedPercentage']; ?>% Progress</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 x-small text-slate-400 fw-bold text-uppercase tracking-widest">Gap</p>
                            <p class="mb-0 small fw-bold text-slate-900"><?php echo number_format($ind['gap']); ?></p>
                        </div>
                    </div>
                </div>
                <!-- Decorative element -->
                <div class="position-absolute bottom-0 end-0 opacity-5" style="transform: translate(20%, 20%);">
                    <i class="bi bi-stars" style="font-size: 8rem;"></i>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Large Impact Card -->
    <div class="card border-0 bg-slate-900 text-white p-5 rounded-5 mb-5 overflow-hidden position-relative">
        <div class="row align-items-center position-relative z-1">
            <div class="col-lg-7">
                <h6 class="text-primary fw-bold text-uppercase tracking-widest mb-3">Overall Project Impact</h6>
                <h1 class="display-4 fw-bold mb-4">Empowering Communities Through Sustainable Change</h1>
                <p class="text-slate-400 lead mb-5">Our multi-sectoral approach has reached thousands of beneficiaries across Nepal, focusing on youth empowerment and accountability.</p>
                <div class="row g-4">
                    <div class="col-md-4">
                        <h2 class="fw-bold mb-1">85%</h2>
                        <p class="text-slate-500 x-small fw-bold text-uppercase tracking-widest">Average Achievement</p>
                    </div>
                    <div class="col-md-4">
                        <h2 class="fw-bold mb-1">12+</h2>
                        <p class="text-slate-500 x-small fw-bold text-uppercase tracking-widest">Active Districts</p>
                    </div>
                    <div class="col-md-4">
                        <h2 class="fw-bold mb-1">4.2/5</h2>
                        <p class="text-slate-500 x-small fw-bold text-uppercase tracking-widest">Satisfaction Index</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="p-5 bg-white bg-opacity-5 rounded-5 backdrop-blur-md border border-white border-opacity-10 text-center">
                    <div class="mb-4">
                        <canvas id="impactRadar" width="300" height="300"></canvas>
                    </div>
                    <p class="mb-0 x-small fw-bold text-uppercase tracking-widest text-slate-400">Sector Performance Radar</p>
                </div>
            </div>
        </div>
        <div class="position-absolute top-0 end-0 w-50 h-100 bg-primary opacity-10 blur-[120px] rounded-circle" style="transform: translate(30%, -30%);"></div>
    </div>
</div>

<script>
    // Impact Radar Chart
    const radarCtx = document.getElementById('impactRadar').getContext('2d');
    new Chart(radarCtx, {
        type: 'radar',
        data: {
            labels: ['Outreach', 'Capacity', 'Accountability', 'Infrastructure', 'Health', 'Education'],
            datasets: [{
                label: 'Performance',
                data: [85, 72, 91, 65, 88, 79],
                fill: true,
                backgroundColor: 'rgba(99, 102, 241, 0.2)',
                borderColor: '#6366f1',
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#6366f1'
            }]
        },
        options: {
            scales: {
                r: {
                    angleLines: { color: 'rgba(255, 255, 255, 0.1)' },
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    pointLabels: { color: '#94a3b8', font: { size: 10, weight: 'bold' } },
                    ticks: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>

<?php require_once 'footer.php'; ?>
