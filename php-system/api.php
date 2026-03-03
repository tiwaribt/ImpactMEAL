<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'db.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$resource = array_shift($request);
$id = array_shift($request);

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit;
}

switch ($resource) {
    case 'indicators':
        handleIndicators($method, $db, $id);
        break;
    case 'monitoring':
        handleMonitoring($method, $db, $id);
        break;
    case 'feedback':
        handleFeedback($method, $db, $id);
        break;
    default:
        http_response_code(404);
        echo json_encode(["message" => "Resource not found"]);
        break;
}

function handleIndicators($method, $db, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $db->prepare("SELECT * FROM indicators WHERE id = ?");
                $stmt->execute([$id]);
                $result = $stmt->fetch();
            } else {
                $stmt = $db->prepare("SELECT * FROM indicators");
                $stmt->execute();
                $result = $stmt->fetchAll();
            }
            echo json_encode($result);
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"));
            $stmt = $db->prepare("INSERT INTO indicators (id, name, target, unit, category, actual, achievedPercentage, gap, status) VALUES (?, ?, ?, ?, ?, 0, 0, ?, 'behind')");
            if ($stmt->execute([$data->id, $data->name, $data->target, $data->unit, $data->category, $data->target])) {
                echo json_encode(["status" => "success"]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
            }
            break;
        case 'PUT':
            $data = json_decode(file_get_contents("php://input"));
            $stmt = $db->prepare("UPDATE indicators SET name = ?, target = ?, unit = ?, category = ? WHERE id = ?");
            if ($stmt->execute([$data->name, $data->target, $data->unit, $data->category, $id])) {
                recalculateIndicator($db, $id);
                echo json_encode(["status" => "success"]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
            }
            break;
        case 'DELETE':
            $stmt = $db->prepare("DELETE FROM indicators WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(["status" => "success"]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error"]);
            }
            break;
    }
}

function handleMonitoring($method, $db, $id) {
    switch ($method) {
        case 'GET':
            $stmt = $db->prepare("SELECT * FROM monitoring_entries");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;
        case 'POST':
            $data = json_decode(file_get_contents("php://input"));
            $db->beginTransaction();
            try {
                $stmt = $db->prepare("INSERT INTO monitoring_entries (id, indicatorId, date, value, location, notes, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$data->id, $data->indicatorId, $data->date, $data->value, $data->location, $data->notes, $data->latitude ?? null, $data->longitude ?? null]);
                
                $stmt = $db->prepare("UPDATE indicators SET actual = actual + ? WHERE id = ?");
                $stmt->execute([$data->value, $data->indicatorId]);
                
                recalculateIndicator($db, $data->indicatorId);
                $db->commit();
                echo json_encode(["status" => "success"]);
            } catch (Exception $e) {
                $db->rollBack();
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
            break;
        // Add PUT and DELETE similarly...
    }
}

function recalculateIndicator($db, $indicatorId) {
    $stmt = $db->prepare("SELECT * FROM indicators WHERE id = ?");
    $stmt->execute([$indicatorId]);
    $ind = $stmt->fetch();
    
    $achieved = round(($ind['actual'] / $ind['target']) * 100);
    $gap = $ind['target'] - $ind['actual'];
    $status = $achieved >= 90 ? 'on-track' : ($achieved >= 70 ? 'at-risk' : 'behind');
    
    $stmt = $db->prepare("UPDATE indicators SET achievedPercentage = ?, gap = ?, status = ? WHERE id = ?");
    $stmt->execute([$achieved, $gap, $status, $indicatorId]);
}

function handleFeedback($method, $db, $id) {
    // Implementation for feedback...
}
?>
