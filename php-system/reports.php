<?php
require_once 'db.php';
$database = new Database();
$db = $database->getConnection();

// Fetch Indicators for reports
$stmt = $db->prepare("SELECT * FROM indicators");
$stmt->execute();
$indicators = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact MEAL - Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .sidebar { height: 100vh; background-color: #0f172a; color: #94a3b8; }
        .nav-link { color: #94a3b8; border-radius: 8px; margin-bottom: 4px; }
        .nav-link.active { background-color: #4f46e5; color: white; }
        .card { border-radius: 16px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .report-preview { background-color: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); min-height: 800px; }
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
                    <a class="nav-link" href="index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a class="nav-link" href="indicators.php"><i class="bi bi-target me-2"></i> Indicators</a>
                    <a class="nav-link" href="monitoring.php"><i class="bi bi-clipboard-data me-2"></i> Monitoring</a>
                    <a class="nav-link" href="gis.php"><i class="bi bi-map me-2"></i> GIS View</a>
                    <a class="nav-link active" href="reports.php"><i class="bi bi-file-earmark-text me-2"></i> Reports</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Reporting Module</h2>
                    <div class="btn-group">
                        <button class="btn btn-white border rounded-pill px-4 me-2" onclick="exportPDF()"><i class="bi bi-file-pdf me-2"></i> Export PDF</button>
                        <button class="btn btn-primary rounded-pill px-4"><i class="bi bi-plus-lg me-2"></i> Generate AI Report</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="report-preview" id="reportContent">
                            <div class="text-center mb-5">
                                <h3 class="fw-bold mb-1">Impact MEAL - Status Report</h3>
                                <p class="text-muted">Generated on <?php echo date('M d, Y'); ?></p>
                            </div>

                            <h5 class="fw-bold mb-4 border-bottom pb-2">1. Indicator Performance Summary</h5>
                            <table class="table table-bordered mb-5" id="reportTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Indicator</th>
                                        <th>Target</th>
                                        <th>Actual</th>
                                        <th>% Achieved</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($indicators as $ind): ?>
                                    <tr>
                                        <td><?php echo $ind['name']; ?></td>
                                        <td><?php echo $ind['target']; ?></td>
                                        <td><?php echo $ind['actual']; ?></td>
                                        <td><?php echo $ind['achievedPercentage']; ?>%</td>
                                        <td><?php echo strtoupper(str_replace('-', ' ', $ind['status'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <h5 class="fw-bold mb-4 border-bottom pb-2">2. Key Insights & Analysis</h5>
                            <p class="text-muted mb-4">
                                Based on the current monitoring data, the program is showing significant progress in outreach activities. 
                                However, capacity building indicators are currently flagged as "At Risk" due to delays in training sessions.
                            </p>

                            <h5 class="fw-bold mb-4 border-bottom pb-2">3. Recommendations</h5>
                            <ul class="text-muted">
                                <li>Accelerate training schedules for the next quarter.</li>
                                <li>Conduct a mid-term review for indicators currently behind target.</li>
                                <li>Enhance data collection frequency in remote locations.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4">
                            <h5 class="fw-bold mb-4">Report Settings</h5>
                            <div class="mb-3">
                                <label class="form-label">Report Type</label>
                                <select class="form-select">
                                    <option>Monthly Status Report</option>
                                    <option>Quarterly Impact Assessment</option>
                                    <option>Annual MEAL Summary</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Range</label>
                                <div class="input-group">
                                    <input type="date" class="form-control">
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" checked>
                                <label class="form-check-label">Include GIS Data</label>
                            </div>
                            <button class="btn btn-primary w-full py-2 rounded-xl">Update Preview</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            doc.setFontSize(20);
            doc.text("Impact MEAL - Status Report", 105, 20, { align: "center" });
            
            doc.setFontSize(10);
            doc.text(`Generated on ${new Date().toLocaleDateString()}`, 105, 28, { align: "center" });
            
            doc.autoTable({
                html: '#reportTable',
                startY: 40,
                theme: 'grid',
                headStyles: { fillColor: [79, 70, 229] }
            });
            
            doc.save("ImpactMEAL_Report.pdf");
        }
    </script>
</body>
</html>
