<?php
// frontend/pages/delete-post.php - NUEVO ARCHIVO
session_start();
include_once '../config.php';

if(!isset($_SESSION['user'])) {
    header('Location: ' . url('/pages/login.php'));
    exit;
}

// Incluir modelos
include_once '../../backend/config/database.php';
include_once '../../backend/models/Post.php';

$error = '';
$success = '';

// Obtener ID del post
$post_id = $_GET['id'] ?? null;
if(!$post_id) {
    header('Location: ' . url('/index.php'));
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $post = new Post($db);
    $post->id = $post_id;
    
    // Cargar el post
    if(!$post->readOne()) {
        $error = 'Post no encontrado';
    }
    
    // Verificar permisos
    if($post->author_id != $_SESSION['user']['id'] && $_SESSION['user']['role'] != 'admin') {
        $error = 'No tienes permisos para eliminar este post';
    }
} catch(Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}

// Procesar eliminación
if($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    try {
        if($post->delete()) {
            $success = '¡Post eliminado exitosamente!';
            header('Refresh: 2; URL=' . url('/index.php'));
        } else {
            $error = 'Error al eliminar el post';
        }
    } catch(Exception $e) {
        $error = 'Error del sistema: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Post - RedBlog</title>
    <link rel="stylesheet" href="<?php echo asset('/css/style.css'); ?>">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <div class="form-container">
            <div class="form">
                <h2 class="form-title">Eliminar Publicación</h2>
                
                <?php if($error): ?>
                    <div class="error-message">
                        ❌ <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="success-message">
                        ✅ <?php echo $success; ?>
                        <p>Redirigiendo al inicio...</p>
                    </div>
                <?php else: ?>
                
                <?php if(!$error): ?>
                <div class="error-message" style="text-align: center;">
                    <h3>¿Estás seguro de que quieres eliminar este post?</h3>
                    <p><strong>"<?php echo htmlspecialchars($post->title); ?>"</strong></p>
                    <p>Esta acción no se puede deshacer.</p>
                </div>
                
                <form method="POST" action="">
                    <button type="submit" class="form-button" style="background-color: var(--error-text);">
                        Sí, eliminar post
                    </button>
                    
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="<?php echo url('/index.php'); ?>" class="text-link">← Cancelar y volver al inicio</a>
                    </div>
                </form>
                <?php endif; ?>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="<?php echo asset('/js/script.js'); ?>"></script>
</body>
</html>