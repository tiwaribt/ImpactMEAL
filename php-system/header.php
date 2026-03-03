<?php
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --bg-main: #f8fafc;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
        }
        body { background-color: var(--bg-main); font-family: 'Inter', sans-serif; }
        .sidebar { height: 100vh; background-color: var(--sidebar-bg); color: var(--sidebar-text); position: fixed; width: 260px; z-index: 1000; }
        .main-content { margin-left: 260px; padding: 40px; min-height: 100vh; }
        .nav-link { color: var(--sidebar-text); border-radius: 12px; margin-bottom: 4px; padding: 12px 16px; transition: all 0.2s; font-weight: 500; }
        .nav-link:hover { background-color: rgba(255, 255, 255, 0.05); color: white; }
        .nav-link.active { background-color: var(--primary); color: white; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }
        .card { border-radius: 24px; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s; }
        .card:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .btn-primary { background-color: var(--primary); border: none; border-radius: 12px; padding: 10px 20px; font-weight: 600; }
        .btn-primary:hover { background-color: var(--primary-dark); }
        .badge-pill { border-radius: 9999px; padding: 6px 12px; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
        .table thead th { background-color: #f1f5f9; border-bottom: none; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 16px; }
        .table tbody td { padding: 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
    </style>
</head>
<body>
