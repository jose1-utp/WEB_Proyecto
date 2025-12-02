<?php
// frontend/pages/edit-post.php - NUEVO ARCHIVO
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
        $error = 'No tienes permisos para editar este post';
    }
} catch(Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}

// Procesar actualización
if($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $theme = $_POST['theme'] ?? '';

    if(empty($title) || empty($content) || empty($theme)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        try {
            $post->title = $title;
            $post->content = $content;
            $post->theme = $theme;

            if($post->update()) {
                $success = '¡Post actualizado exitosamente!';
                header('Refresh: 2; URL=' . url('/index.php'));
            } else {
                $error = 'Error al actualizar el post';
            }
        } catch(Exception $e) {
            $error = 'Error del sistema: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Post - RedBlog</title>
    <link rel="stylesheet" href="<?php echo asset('/css/style.css'); ?>">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="container">
        <div class="form-container">
            <div class="form">
                <h2 class="form-title">Editar Publicación</h2>
                
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
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title">Título:</label>
                        <input type="text" id="title" name="title" class="form-input" 
                               placeholder="Escribe un título llamativo" required 
                               value="<?php echo htmlspecialchars($post->title); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="theme">Tema:</label>
                        <select id="theme" name="theme" class="form-input" required>
                            <option value="">Selecciona un tema</option>
                            <?php
                            $themes = [
                                'Ciencia y Tecnologia', 'Libros', 'Programacion', 'Videojuegos', 
                                'Musica', 'Cine y Television', 'Deportes', 'Otros'
                            ];
                            foreach($themes as $theme_option) {
                                $selected = $theme_option == $post->theme ? 'selected' : '';
                                echo "<option value='$theme_option' $selected>" . ucfirst($theme_option) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Contenido:</label>
                        <textarea id="content" name="content" class="form-input" 
                                  placeholder="Escribe el contenido de tu publicación..." 
                                  rows="8" required><?php echo htmlspecialchars($post->content); ?></textarea>
                    </div>

                    <button type="submit" class="form-button">
                        Actualizar
                    </button>
                    
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="<?php echo url('/index.php'); ?>" class="text-link">← Volver al inicio</a>
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