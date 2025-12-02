<?php
// frontend/api/delete-user.php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["success" => false, "message" => "ID de usuario requerido"]);
    exit;
}

// No permitir eliminar el propio usuario
if ($user_id == $_SESSION['user']['id']) {
    echo json_encode(["success" => false, "message" => "No puedes eliminar tu propia cuenta"]);
    exit;
}

include_once '../../backend/config/database.php';
include_once '../../backend/models/User.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    
    $result = $user->deleteUser($user_id);
    
    if ($result['success']) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '../pages/admin.php');
        exit;
    } else {
        echo json_encode(["success" => false, "message" => $result['message']]);
    }
} catch(Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>