<?php
// php/indicators.php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM indicators");
    echo json_encode($stmt->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sql = "INSERT INTO indicators (id, name, target, actual, unit, category, trend, status, gap, achieved_percentage) 
            VALUES (:id, :name, :target, :actual, :unit, :category, :trend, :status, :gap, :achieved_percentage)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    echo json_encode(['status' => 'success']);
}
?>
