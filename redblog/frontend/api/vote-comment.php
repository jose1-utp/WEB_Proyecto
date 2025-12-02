<?php
// frontend/api/vote-comment.php
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

// Obtener datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$comment_id = $data['comment_id'] ?? null;
$vote_type = $data['vote_type'] ?? null;

if (!$comment_id || !$vote_type) {
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
    
    // Votar en el comentario
    if ($comment->vote($_SESSION['user']['id'], $vote_type)) {
        // Obtener nuevo score (recargar datos)
        $comment->readOne();
        $new_score = $comment->upvotes - $comment->downvotes;
        
        echo json_encode([
            "success" => true, 
            "message" => "Voto registrado",
            "new_score" => $new_score
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al votar"]);
    }
} catch(Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>