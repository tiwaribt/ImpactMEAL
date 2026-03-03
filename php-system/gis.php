<?php
require_once 'header.php';
require_once 'sidebar.php';

// Fetch Monitoring Entries with coordinates
$stmt = $db->prepare("SELECT m.*, i.name as indicatorName, i.unit FROM monitoring_entries m JOIN indicators i ON m.indicatorId = i.id WHERE m.latitude IS NOT NULL AND m.longitude IS NOT NULL");
$stmt->execute();
$points = $stmt->fetchAll();
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-slate-900 mb-1">GIS Monitoring View</h2>
            <p class="text-slate-500 small mb-0">Geospatial distribution of project activities in Nepal</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-white border rounded-pill px-4" onclick="resetMap()">
                <i class="bi bi-crosshair me-2"></i> Reset View
            </button>
        </div>
    </div>

    <div class="card border-0 overflow-hidden" style="height: 600px;">
        <div id="map" style="height: 100%; width: 100%;"></div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-md-4">
            <div class="card p-4">
                <h6 class="fw-bold text-slate-900 mb-3">Map Legend</h6>
                <div class="space-y-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="w-4 h-4 rounded-circle bg-primary"></div>
                        <span class="text-slate-600 small">Activity Location</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="w-4 h-4 rounded-circle bg-success bg-opacity-20 border border-success"></div>
                        <span class="text-slate-600 small">High Impact Area</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card p-4">
                <h6 class="fw-bold text-slate-900 mb-3">Recent Locations</h6>
                <div class="row g-3">
                    <?php foreach (array_slice($points, 0, 4) as $point): ?>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3 p-2 rounded-3 hover-bg-light cursor-pointer" onclick="zoomTo(<?php echo $point['latitude']; ?>, <?php echo $point['longitude']; ?>)">
                            <div class="w-10 h-10 bg-primary bg-opacity-10 rounded-3 d-flex align-items-center justify-center">
                                <i class="bi bi-geo-alt text-primary"></i>
                            </div>
                            <div>
                                <p class="mb-0 small fw-bold text-slate-900"><?php echo $point['location']; ?></p>
                                <p class="mb-0 x-small text-slate-500"><?php echo $point['indicatorName']; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize map centered on Nepal
    const map = L.map('map').setView([28.3949, 84.1240], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const points = <?php echo json_encode($points); ?>;
    const markers = [];

    points.forEach(point => {
        const marker = L.marker([point.latitude, point.longitude])
            .addTo(map)
            .bindPopup(`
                <div class="p-2">
                    <h6 class="fw-bold mb-1">${point.indicatorName}</h6>
                    <p class="mb-2 small text-muted">${point.location}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary">${point.value} ${point.unit}</span>
                        <span class="x-small text-muted">${new Date(point.date).toLocaleDateString()}</span>
                    </div>
                </div>
            `);
        markers.push(marker);
    });

    function resetMap() {
        map.setView([28.3949, 84.1240], 7);
    }

    function zoomTo(lat, lng) {
        map.setView([lat, lng], 12);
    }
</script>

<?php require_once 'footer.php'; ?>
