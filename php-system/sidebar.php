<div class="sidebar p-4">
    <div class="d-flex align-items-center gap-3 mb-5 px-2">
        <div class="p-2 bg-primary rounded-3 shadow-lg shadow-primary/20">
            <i class="bi bi-stars text-white fs-4"></i>
        </div>
        <div>
            <h4 class="text-white mb-0 fw-bold">Impact MEAL</h4>
            <span class="text-secondary small fw-bold">v1.0.0</span>
        </div>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
            <i class="bi bi-speedometer2 me-3"></i> Dashboard
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : ''; ?>" href="projects.php">
            <i class="bi bi-folder me-3"></i> Projects
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'indicators.php' ? 'active' : ''; ?>" href="indicators.php">
            <i class="bi bi-target me-3"></i> Indicators
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'monitoring.php' ? 'active' : ''; ?>" href="monitoring.php">
            <i class="bi bi-clipboard-data me-3"></i> Monitoring
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'gis.php' ? 'active' : ''; ?>" href="gis.php">
            <i class="bi bi-map me-3"></i> GIS View
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'infographics.php' ? 'active' : ''; ?>" href="infographics.php">
            <i class="bi bi-stars me-3"></i> Infographics
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
            <i class="bi bi-file-earmark-text me-3"></i> Reports
        </a>
        <div class="mt-5 pt-5 border-top border-secondary opacity-25"></div>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="settings.php">
            <i class="bi bi-gear me-3"></i> Settings
        </a>
        <a class="nav-link text-danger" href="logout.php">
            <i class="bi bi-box-arrow-right me-3"></i> Sign Out
        </a>
    </nav>
    <div class="mt-auto pt-5 px-2">
        <div class="p-3 bg-white bg-opacity-5 rounded-2xl">
            <div class="d-flex align-items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white fw-bold">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                </div>
                <div class="overflow-hidden">
                    <p class="text-white mb-0 small fw-bold text-truncate"><?php echo $_SESSION['username']; ?></p>
                    <p class="text-secondary mb-0 x-small text-uppercase tracking-widest" style="font-size: 9px;"><?php echo $_SESSION['role']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
