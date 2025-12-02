<?php
// frontend/api/add-comment.php
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

include_once '../../backend/config/database.php';
include_once '../../backend/models/Comment.php';

$database = new Database();
$db = $database->getConnection();
$comment = new Comment($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'] ?? null;
    $parent_id = $_POST['parent_id'] ?? null;
    $content = $_POST['content'] ?? null;
    
    if (!$post_id || !$content) {
        echo json_encode(["success" => false, "message" => "Faltan datos"]);
        exit;
    }
    
    $comment->post_id = $post_id;
    $comment->user_id = $_SESSION['user']['id'];
    $comment->parent_id = $parent_id ?: null;
    $comment->content = $content;
    
    if ($comment->create()) {
        echo json_encode(["success" => true, "message" => "Comentario publicado"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al publicar comentario"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>