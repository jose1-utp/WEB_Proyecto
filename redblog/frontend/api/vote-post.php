<?php
// frontend/api/vote-post.php - VERSIÓN SIMPLIFICADA Y CORREGIDA
session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

// Obtener datos JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$post_id = $data['post_id'] ?? null;
$vote_type = $data['vote_type'] ?? null;

if (!$post_id || !$vote_type) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

// Incluir modelos
include_once '../../backend/config/database.php';
include_once '../../backend/models/Post.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $post = new Post($db);
    $post->id = $post_id;
    
    // Usar el método vote del modelo
    $result = $post->vote($_SESSION['user']['id'], $vote_type);
    
    if ($result['success']) {
        echo json_encode([
            "success" => true, 
            "message" => "Voto registrado",
            "upvotes" => $result['upvotes'],
            "downvotes" => $result['downvotes']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => $result['message'] ?? "Error al votar"]);
    }
} catch(Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>