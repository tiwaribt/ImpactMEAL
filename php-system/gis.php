<?php
require_once 'db.php';
$database = new Database();
$db = $database->getConnection();

// Fetch Monitoring Entries with coordinates
$stmt = $db->prepare("SELECT m.*, i.name as indicatorName FROM monitoring_entries m JOIN indicators i ON m.indicatorId = i.id WHERE m.latitude IS NOT NULL AND m.longitude IS NOT NULL");
$stmt->execute();
$entries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact MEAL - GIS View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .sidebar { height: 100vh; background-color: #0f172a; color: #94a3b8; }
        .nav-link { color: #94a3b8; border-radius: 8px; margin-bottom: 4px; }
        .nav-link.active { background-color: #4f46e5; color: white; }
        .card { border-radius: 16px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        #map { height: 600px; border-radius: 16px; }
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
                    <a class="nav-link active" href="gis.php"><i class="bi bi-map me-2"></i> GIS View</a>
                    <a class="nav-link" href="reports.php"><i class="bi bi-file-earmark-text me-2"></i> Reports</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">GIS Reporting</h2>
                    <div class="btn-group">
                        <button class="btn btn-white border rounded-pill px-4 me-2"><i class="bi bi-upload me-2"></i> Upload GeoJSON</button>
                        <button class="btn btn-primary rounded-pill px-4"><i class="bi bi-plus-lg me-2"></i> Add Site</button>
                    </div>
                </div>

                <div class="card p-2">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const entries = <?php echo json_encode($entries); ?>;
        const markers = [];

        entries.forEach(entry => {
            const marker = L.marker([entry.latitude, entry.longitude])
                .addTo(map)
                .bindPopup(`
                    <div class="p-2">
                        <h6 class="fw-bold mb-1">${entry.indicatorName}</h6>
                        <p class="text-muted small mb-1"><i class="bi bi-geo-alt"></i> ${entry.location}</p>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-primary">${entry.value}</span>
                            <span class="text-muted small">${entry.date}</span>
                        </div>
                    </div>
                `);
            markers.push(marker);
        });

        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds());
        }
    </script>
</body>
</html>
