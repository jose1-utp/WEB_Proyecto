<?php
// frontend/api/delete-comment.php
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

// Obtener datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$comment_id = $data['comment_id'] ?? null;

if (!$comment_id) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

// Incluir modelos
include_once '../../backend/config/database.php';
include_once '../../backend/models/Comment.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $comment = new Comment($db);
    $comment->id = $comment_id;
    
    // Verificar que el comentario existe
    if (!$comment->readOne()) {
        echo json_encode(["success" => false, "message" => "Comentario no encontrado"]);
        exit;
    }
    
    // Verificar permisos (solo autor o admin)
    if ($_SESSION['user']['id'] != $comment->user_id && $_SESSION['user']['role'] != 'admin') {
        echo json_encode(["success" => false, "message" => "No tienes permisos para eliminar este comentario"]);
        exit;
    }
    
    // Eliminar comentario
    if ($comment->delete()) {
        echo json_encode([
            "success" => true, 
            "message" => "Comentario eliminado"
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al eliminar"]);
    }
} catch(Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>